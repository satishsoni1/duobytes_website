<?php
/* Minimal, dependency-free SMTP client (Gmail: smtp.gmail.com, 465 SSL or 587 STARTTLS, App Password) */
declare(strict_types=1);

class Smtp {
    private $fp = null; public array $log = [];
    public function __construct(private string $host, private int $port, private string $secure, private string $user, private string $pass, private int $timeout = 25) {}

    private function read(): string {
        $data = '';
        while (($line = fgets($this->fp, 1024)) !== false) { $data .= $line; if (strlen($line) < 4 || $line[3] === ' ') break; }
        $this->log[] = 'S: ' . trim($data); return $data;
    }
    private function cmd(string $c, array $expect, bool $hide = false): string {
        fwrite($this->fp, $c . "\r\n"); $this->log[] = 'C: ' . ($hide ? '****' : $c);
        $r = $this->read(); $code = (int)substr($r, 0, 3);
        if (!in_array($code, $expect, true)) throw new RuntimeException("SMTP error on '" . ($hide ? 'AUTH' : strtok($c, ' ')) . "': " . trim($r));
        return $r;
    }
    public function connect(): void {
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
        $remote = ($this->secure === 'ssl' ? 'ssl://' : 'tcp://') . $this->host . ':' . $this->port;
        $this->fp = @stream_socket_client($remote, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!$this->fp) throw new RuntimeException("Cannot connect to {$this->host}:{$this->port} – $errstr ($errno)");
        stream_set_timeout($this->fp, $this->timeout);
        $r = $this->read(); if ((int)substr($r, 0, 3) !== 220) throw new RuntimeException('SMTP greeting failed: ' . trim($r));
        $ehlo = gethostname() ?: 'localhost';
        $this->cmd("EHLO $ehlo", [250]);
        if ($this->secure === 'tls') {
            $this->cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($this->fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT))
                throw new RuntimeException('STARTTLS negotiation failed');
            $this->cmd("EHLO $ehlo", [250]);
        }
        if ($this->user !== '') {
            $this->cmd('AUTH LOGIN', [334]);
            $this->cmd(base64_encode($this->user), [334], true);
            $this->cmd(base64_encode($this->pass), [235], true);
        }
    }
    public function send(string $fromEmail, string $toEmail, string $raw): void {
        if (!$this->fp) $this->connect();
        $this->cmd("MAIL FROM:<$fromEmail>", [250]);
        $this->cmd("RCPT TO:<$toEmail>", [250, 251]);
        $this->cmd('DATA', [354]);
        $raw = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $raw));
        fwrite($this->fp, str_replace("\n", "\r\n", $raw) . "\r\n.\r\n");
        $r = $this->read(); if ((int)substr($r, 0, 3) !== 250) throw new RuntimeException('Message rejected: ' . trim($r));
    }
    public function quit(): void { if ($this->fp) { @fwrite($this->fp, "QUIT\r\n"); @fclose($this->fp); $this->fp = null; } }
    public function __destruct() { $this->quit(); }
}

function mime_header(string $s): string { return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s; }

function html_to_text(string $html): string {
    $t = preg_replace('#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ($1)', $html);
    $t = preg_replace('#<(br|/p|/tr|/h[1-6]|/li|/div)[^>]*>#i', "\n", $t);
    $t = preg_replace('#<li[^>]*>#i', '• ', $t);
    $t = html_entity_decode(strip_tags($t), ENT_QUOTES, 'UTF-8');
    return trim(preg_replace("/\n\s*\n\s*\n+/", "\n\n", preg_replace('/[ \t]+/', ' ', $t)));
}

/** Build an RFC 5322 multipart/alternative message */
function build_message(array $o): string {
    $b = 'b' . bin2hex(random_bytes(12));
    $domain = substr(strrchr($o['from_email'], '@'), 1) ?: 'localhost';
    $h = [];
    $h[] = 'Date: ' . date('r');
    $h[] = 'From: ' . mime_header($o['from_name']) . ' <' . $o['from_email'] . '>';
    $h[] = 'To: ' . ($o['to_name'] ? mime_header($o['to_name']) . ' <' . $o['to'] . '>' : '<' . $o['to'] . '>');
    if (!empty($o['reply_to'])) $h[] = 'Reply-To: ' . $o['reply_to'];
    $h[] = 'Subject: ' . mime_header($o['subject']);
    $h[] = 'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $domain . '>';
    $h[] = 'MIME-Version: 1.0';
    if (!empty($o['unsubscribe'])) {
        $h[] = 'List-Unsubscribe: <' . $o['unsubscribe'] . '>, <mailto:' . ($o['reply_to'] ?: $o['from_email']) . '?subject=unsubscribe>';
        $h[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
    }
    $h[] = 'Content-Type: multipart/alternative; boundary="' . $b . '"';
    $text = $o['text'] ?? html_to_text($o['html']);
    $body = "--$b\nContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: base64\n\n" . chunk_split(base64_encode($text), 76, "\n")
          . "--$b\nContent-Type: text/html; charset=UTF-8\nContent-Transfer-Encoding: base64\n\n" . chunk_split(base64_encode($o['html']), 76, "\n")
          . "--$b--\n";
    return implode("\n", $h) . "\n\n" . $body;
}

function smtp_from_settings(): Smtp {
    $secure = setting('smtp_secure', 'ssl');
    return new Smtp(setting('smtp_host', 'smtp.gmail.com'), (int)setting('smtp_port', $secure === 'ssl' ? '465' : '587'), $secure,
        setting('smtp_user'), setting('smtp_pass') ? decrypt_secret(setting('smtp_pass')) : '');
}

/* App password stored encrypted at rest (key derived from a file outside the DB) */
function secret_key(): string {
    $f = DATA_DIR . '/.key';
    if (!file_exists($f)) { file_put_contents($f, bin2hex(random_bytes(32))); @chmod($f, 0600); }
    return hash('sha256', trim(file_get_contents($f)), true);
}
function encrypt_secret(string $plain): string {
    $iv = random_bytes(16);
    return base64_encode($iv . openssl_encrypt($plain, 'aes-256-cbc', secret_key(), OPENSSL_RAW_DATA, $iv));
}
function decrypt_secret(string $enc): string {
    $raw = base64_decode($enc); if (strlen($raw) < 17) return '';
    return (string)openssl_decrypt(substr($raw, 16), 'aes-256-cbc', secret_key(), OPENSSL_RAW_DATA, substr($raw, 0, 16));
}
