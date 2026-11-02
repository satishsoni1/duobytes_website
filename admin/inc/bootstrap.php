<?php
/* DuoBytes Admin – bootstrap: config, session, MySQL, auth, helpers */
declare(strict_types=1);
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', '0');

define('ADMIN_DIR', dirname(__DIR__));
define('DATA_DIR', ADMIN_DIR . '/data');
define('CONFIG_FILE', ADMIN_DIR . '/config.php');
if (!is_dir(DATA_DIR)) { @mkdir(DATA_DIR, 0750, true); }
if (!file_exists(DATA_DIR . '/.htaccess')) { @file_put_contents(DATA_DIR . '/.htaccess', "Require all denied\nDeny from all\n"); }

$CONFIG = file_exists(CONFIG_FILE) ? (require CONFIG_FILE) : null;

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_name('DBADMIN');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'secure' => $secure, 'samesite' => 'Lax']);
    session_start();
}

function configured(): bool { global $CONFIG; return is_array($CONFIG) && !empty($CONFIG['db_name']); }

function db_connect(array $c): PDO {
    $dsn = "mysql:host={$c['db_host']};port=" . ($c['db_port'] ?? 3306) . ";dbname={$c['db_name']};charset=utf8mb4";
    return new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+05:30'"]);
}

function db(): PDO {
    static $pdo = null; global $CONFIG;
    if ($pdo) return $pdo;
    if (!configured()) { if (PHP_SAPI !== 'cli') { header('Location: setup.php'); exit; } throw new RuntimeException('Not configured'); }
    $pdo = db_connect($CONFIG);
    $p = $CONFIG['db_prefix'] ?? 'db_';
    $ver = 0;
    try { $ver = (int)$pdo->query("SELECT v FROM {$p}settings WHERE k='schema_version'")->fetchColumn(); } catch (PDOException $e) {}
    if ($ver < 1) migrate($pdo, $p);
    return $pdo;
}
/** table name with prefix */
function t(string $name): string { global $CONFIG; return ($CONFIG['db_prefix'] ?? 'db_') . $name; }

function migrate(PDO $pdo, string $p): void {
    $o = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS {$p}users(id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(64) NOT NULL UNIQUE, pass VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, last_login DATETIME NULL) $o;
    CREATE TABLE IF NOT EXISTS {$p}settings(k VARCHAR(64) PRIMARY KEY, v TEXT) $o;
    CREATE TABLE IF NOT EXISTS {$p}login_attempts(id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45), at INT, INDEX(ip)) $o;
    CREATE TABLE IF NOT EXISTS {$p}lists(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, segment VARCHAR(60),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP) $o;
    CREATE TABLE IF NOT EXISTS {$p}contacts(id INT AUTO_INCREMENT PRIMARY KEY, list_id INT NOT NULL, email VARCHAR(191) NOT NULL,
        name VARCHAR(150), institution VARCHAR(200), city VARCHAR(100), phone VARCHAR(40), extra TEXT,
        UNIQUE KEY uq_list_email(list_id,email), FOREIGN KEY(list_id) REFERENCES {$p}lists(id) ON DELETE CASCADE) $o;
    CREATE TABLE IF NOT EXISTS {$p}templates(id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(80) UNIQUE, name VARCHAR(150) NOT NULL,
        product VARCHAR(60), subject VARCHAR(255) NOT NULL, html MEDIUMTEXT NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) $o;
    CREATE TABLE IF NOT EXISTS {$p}campaigns(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, template_id INT NULL, list_id INT NULL,
        subject VARCHAR(255), html MEDIUMTEXT, slug VARCHAR(80), status VARCHAR(20) DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, started_at DATETIME NULL, finished_at DATETIME NULL,
        FOREIGN KEY(template_id) REFERENCES {$p}templates(id) ON DELETE SET NULL, FOREIGN KEY(list_id) REFERENCES {$p}lists(id) ON DELETE SET NULL) $o;
    CREATE TABLE IF NOT EXISTS {$p}sends(id INT AUTO_INCREMENT PRIMARY KEY, campaign_id INT NOT NULL, contact_id INT NULL, email VARCHAR(191) NOT NULL,
        name VARCHAR(150), institution VARCHAR(200), city VARCHAR(100),
        status VARCHAR(20) DEFAULT 'queued', error VARCHAR(500), token CHAR(24), sent_at DATETIME NULL, opened_at DATETIME NULL, opens INT DEFAULT 0,
        UNIQUE KEY uq_camp_email(campaign_id,email), INDEX ix_status(campaign_id,status), INDEX ix_sent(sent_at), INDEX ix_token(token),
        FOREIGN KEY(campaign_id) REFERENCES {$p}campaigns(id) ON DELETE CASCADE) $o;
    CREATE TABLE IF NOT EXISTS {$p}unsubscribes(email VARCHAR(191) PRIMARY KEY, reason VARCHAR(255), at DATETIME DEFAULT CURRENT_TIMESTAMP) $o;
    ");
    if (!(int)$pdo->query("SELECT COUNT(*) FROM {$p}templates")->fetchColumn()) {
        require_once __DIR__ . '/default_templates.php';
        $st = $pdo->prepare("INSERT INTO {$p}templates(slug,name,product,subject,html) VALUES(?,?,?,?,?)");
        foreach (default_templates() as $tp) $st->execute([$tp['slug'], $tp['name'], $tp['product'], $tp['subject'], $tp['html']]);
    }
    $pdo->exec("INSERT INTO {$p}settings(k,v) VALUES('schema_version','1') ON DUPLICATE KEY UPDATE v='1'");
}

function write_config(array $c): bool {
    $php = "<?php\n// DuoBytes Admin – generated by setup. Keep this file out of git.\nreturn " . var_export($c, true) . ";\n";
    return file_put_contents(CONFIG_FILE, $php) !== false;
}

function setting(string $k, $default = '') {
    static $cache = null;
    if ($cache === null || $k === '__reset') { $cache = []; foreach (db()->query('SELECT k,v FROM ' . t('settings')) as $r) $cache[$r['k']] = $r['v']; }
    return $cache[$k] ?? $default;
}
function set_setting(string $k, string $v): void {
    db()->prepare('INSERT INTO ' . t('settings') . '(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)')->execute([$k, $v]);
    setting('__reset');
}
function app_secret(): string {
    $s = setting('app_secret');
    if (!$s) { $s = bin2hex(random_bytes(32)); set_setting('app_secret', $s); }
    return $s;
}

/* ---- auth ---- */
function has_admin(): bool { return (int)db()->query('SELECT COUNT(*) FROM ' . t('users'))->fetchColumn() > 0; }
function current_user(): ?string { return !empty($_SESSION['uid']) ? ($_SESSION['uname'] ?? null) : null; }
function require_login(): void {
    if (!configured() || !has_admin()) redirect('setup.php');
    if (empty($_SESSION['uid'])) redirect('login.php');
    if (time() - ($_SESSION['last'] ?? 0) > 4 * 3600) { session_destroy(); redirect('login.php?expired=1'); }
    $_SESSION['last'] = time();
}
function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function too_many_attempts(): bool {
    db()->prepare('DELETE FROM ' . t('login_attempts') . ' WHERE at < ?')->execute([time() - 900]);
    $st = db()->prepare('SELECT COUNT(*) FROM ' . t('login_attempts') . ' WHERE ip=?'); $st->execute([client_ip()]);
    return (int)$st->fetchColumn() >= 5;
}

/* ---- csrf ---- */
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function check_csrf(): void {
    $tk = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$tk)) { http_response_code(419); exit('Session expired. Please go back and reload the page.'); }
}

/* ---- helpers ---- */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'][] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f;
}
function redirect(string $to): void { header('Location: ' . $to); exit; }
function site_url(): string { return rtrim((string)setting('site_url', 'https://www.duobytes.in'), '/'); }
function admin_url(): string { return site_url() . '/admin'; }
function sent_today(): int {
    $st = db()->prepare('SELECT COUNT(*) FROM ' . t('sends') . " WHERE status='sent' AND sent_at >= ?");
    $st->execute([date('Y-m-d 00:00:00')]); return (int)$st->fetchColumn();
}
function valid_email(string $em): bool { return (bool)filter_var($em, FILTER_VALIDATE_EMAIL); }
function unsub_token(string $email): string { return substr(hash_hmac('sha256', strtolower($email), app_secret()), 0, 24); }
function unsub_link(string $email): string { return admin_url() . '/unsubscribe.php?e=' . rawurlencode($email) . '&t=' . unsub_token($email); }
function q(string $sql, array $args = []): PDOStatement { $st = db()->prepare($sql); $st->execute($args); return $st; }
function slugify(string $s): string { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-') ?: 'campaign'; }
