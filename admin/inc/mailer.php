<?php
declare(strict_types=1);
require_once __DIR__ . '/smtp.php';

function tpl_vars(array $c, string $campaignSlug = 'email'): array {
    return [
        'name' => trim((string)($c['name'] ?? '')), 'institution' => trim((string)($c['institution'] ?? '')),
        'city' => trim((string)($c['city'] ?? '')), 'email' => (string)($c['email'] ?? ''),
        'sender_name' => setting('from_name', 'DuoBytes Technologies'), 'sender_phone' => setting('sender_phone', '+91 90000 00000'), 'sender_tel' => preg_replace('/[^0-9+]/', '', (string)setting('sender_phone', '+919000000000')),
        'sender_email' => setting('reply_to') ?: setting('smtp_user'), 'site_url' => site_url(),
        'campaign' => $campaignSlug, 'unsubscribe_url' => !empty($c['email']) ? unsub_link($c['email']) : '#',
    ];
}
/** Replace {{key}} / {{key|fallback}}. $html=true escapes values for HTML. */
function render_tpl(string $s, array $vars, bool $html = true): string {
    $out = preg_replace_callback('/\{\{\s*([a-z_]+)\s*(?:\|([^}]*))?\}\}/i', function ($m) use ($vars, $html) {
        $v = $vars[strtolower($m[1])] ?? '';
        if ($v === '') $v = $m[2] ?? '';
        return $html && !in_array(strtolower($m[1]), ['site_url', 'unsubscribe_url'], true) ? e($v) : $v;
    }, $s);
    if (!$html) $out = trim(preg_replace(['/\s+([,.!?:])/', '/[,\s]+$/', '/\s{2,}/'], ['$1', '', ' '], $out));
    return $out;
}

function message_for(array $campaign, array $row, bool $pixel = true): array {
    $vars = tpl_vars($row, $campaign['slug'] ?: slugify($campaign['name']));
    $html = render_tpl($campaign['html'], $vars, true);
    if ($pixel && !empty($row['token'])) {
        $img = '<img src="' . admin_url() . '/o.php?t=' . $row['token'] . '" width="1" height="1" alt="" style="display:block;border:0">';
        $html = stripos($html, '</body>') !== false ? str_ireplace('</body>', $img . '</body>', $html) : $html . $img;
    }
    return ['subject' => render_tpl($campaign['subject'], $vars, false), 'html' => $html];
}

function send_mail(Smtp $smtp, string $to, string $toName, string $subject, string $html, bool $unsub = true): void {
    $from = setting('from_email') ?: setting('smtp_user');
    $raw = build_message(['from_email' => $from, 'from_name' => setting('from_name', 'DuoBytes Technologies'),
        'to' => $to, 'to_name' => $toName, 'reply_to' => setting('reply_to'), 'subject' => $subject, 'html' => $html,
        'unsubscribe' => $unsub ? unsub_link($to) : '']);
    $smtp->send(setting('smtp_user') ?: $from, $to, $raw);
}

/** Send the next queued email of a campaign. Returns status array for the UI. */
function send_next(int $cid): array {
    $c = q('SELECT * FROM ' . t('campaigns') . ' WHERE id=?', [$cid])->fetch();
    if (!$c) return ['error' => 'Campaign not found'];
    $limit = (int)setting('daily_limit', '400');
    $res = ['last' => null];
    if ($c['status'] !== 'running') { $res['stopped'] = $c['status']; }
    elseif (sent_today() >= $limit) {
        q('UPDATE ' . t('campaigns') . " SET status='paused' WHERE id=?", [$cid]);
        $res['stopped'] = 'limit';
    } else {
        // claim one row atomically
        $row = null;
        for ($i = 0; $i < 3 && !$row; $i++) {
            $cand = q('SELECT * FROM ' . t('sends') . " WHERE campaign_id=? AND status='queued' ORDER BY id LIMIT 1", [$cid])->fetch();
            if (!$cand) break;
            if (q('UPDATE ' . t('sends') . " SET status='sending' WHERE id=? AND status='queued'", [$cand['id']])->rowCount()) $row = $cand;
        }
        if (!$row) {
            q('UPDATE ' . t('campaigns') . " SET status='completed', finished_at=NOW() WHERE id=?", [$cid]);
            $res['stopped'] = 'completed';
        } elseif (q('SELECT 1 FROM ' . t('unsubscribes') . ' WHERE email=?', [strtolower($row['email'])])->fetchColumn()) {
            q('UPDATE ' . t('sends') . " SET status='skipped', error='Unsubscribed' WHERE id=?", [$row['id']]);
            $res['last'] = ['email' => $row['email'], 'status' => 'skipped'];
        } else {
            $m = message_for($c, $row);
            try {
                $smtp = smtp_from_settings();
                send_mail($smtp, $row['email'], $row['name'] ?? '', $m['subject'], $m['html']);
                $smtp->quit();
                q('UPDATE ' . t('sends') . " SET status='sent', sent_at=NOW(), error=NULL WHERE id=?", [$row['id']]);
                $res['last'] = ['email' => $row['email'], 'status' => 'sent'];
            } catch (Throwable $ex) {
                $msg = substr($ex->getMessage(), 0, 480);
                q('UPDATE ' . t('sends') . " SET status='failed', error=? WHERE id=?", [$msg, $row['id']]);
                $res['last'] = ['email' => $row['email'], 'status' => 'failed', 'error' => $msg];
                // account-level problems: stop the campaign instead of burning through the list
                if (preg_match('/5\.4\.5|quota|limit exceeded|535|534|Username and Password|Cannot connect|greeting failed|STARTTLS/i', $msg)) {
                    q('UPDATE ' . t('sends') . " SET status='queued', error=NULL WHERE id=?", [$row['id']]);
                    q('UPDATE ' . t('campaigns') . " SET status='paused' WHERE id=?", [$cid]);
                    $res['stopped'] = 'error'; $res['error'] = $msg;
                }
            }
        }
    }
    return array_merge($res, campaign_stats($cid), ['today' => sent_today(), 'limit' => $limit]);
}

function campaign_stats(int $cid): array {
    $s = ['queued' => 0, 'sending' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
    foreach (q('SELECT status, COUNT(*) n FROM ' . t('sends') . ' WHERE campaign_id=? GROUP BY status', [$cid]) as $r) $s[$r['status']] = (int)$r['n'];
    $s['opened'] = (int)q('SELECT COUNT(*) FROM ' . t('sends') . ' WHERE campaign_id=? AND opened_at IS NOT NULL', [$cid])->fetchColumn();
    $s['total'] = array_sum(array_intersect_key($s, array_flip(['queued', 'sending', 'sent', 'failed', 'skipped'])));
    $s['status'] = (string)q('SELECT status FROM ' . t('campaigns') . ' WHERE id=?', [$cid])->fetchColumn();
    return $s;
}
