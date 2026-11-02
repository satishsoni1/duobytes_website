<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$step = !configured() ? 'db' : (!has_admin() ? 'user' : 'done');
if ($step === 'done') redirect('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if ($step === 'db') {
        $c = ['db_host' => trim($_POST['db_host'] ?: 'localhost'), 'db_port' => (int)($_POST['db_port'] ?: 3306), 'db_name' => trim($_POST['db_name']),
              'db_user' => trim($_POST['db_user']), 'db_pass' => (string)$_POST['db_pass'], 'db_prefix' => preg_replace('/[^a-z0-9_]/i', '', $_POST['db_prefix'] ?: 'db_')];
        try {
            db_connect($c);
            if (!write_config($c)) throw new RuntimeException('Could not write admin/config.php – check folder permissions.');
            flash('Database connected. Now create your admin login.');
        } catch (Throwable $ex) { flash('Database error: ' . $ex->getMessage(), 'err'); }
        redirect('setup.php');
    }
    if ($step === 'user') {
        $u = trim($_POST['username'] ?? ''); $p = (string)($_POST['password'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_.@-]{3,64}$/', $u)) { flash('Username: 3–64 letters, numbers or _ . @ -', 'err'); redirect('setup.php'); }
        if (strlen($p) < 10) { flash('Password must be at least 10 characters.', 'err'); redirect('setup.php'); }
        if ($p !== ($_POST['password2'] ?? '')) { flash('Passwords do not match.', 'err'); redirect('setup.php'); }
        if (has_admin()) redirect('login.php');
        q('INSERT INTO ' . t('users') . '(username,pass) VALUES(?,?)', [$u, password_hash($p, PASSWORD_DEFAULT)]);
        $uid = (int)db()->lastInsertId();
        set_setting('site_url', rtrim($_POST['site_url'] ?: 'https://www.duobytes.in', '/'));
        app_secret();
        session_regenerate_id(true);
        $_SESSION['uid'] = $uid; $_SESSION['uname'] = $u; $_SESSION['last'] = time();
        flash('Welcome! Next: add your Gmail SMTP details.');
        redirect('settings.php');
    }
}

auth_page($step === 'db' ? 'Setup · Database' : 'Setup · Admin login', function () use ($step) { ?>
<?php if ($step === 'db'): ?>
<p class="muted">Step 1 of 2 – enter the MySQL database you created in Hostinger (hPanel › Databases › MySQL Databases).</p>
<form method="post" class="stack"><?= csrf_field() ?>
  <div class="row2"><label>Host<input name="db_host" value="localhost" required></label><label>Port<input name="db_port" value="3306"></label></div>
  <label>Database name<input name="db_name" required placeholder="u123456789_campaign"></label>
  <label>Database user<input name="db_user" required placeholder="u123456789_admin"></label>
  <label>Database password<input name="db_pass" type="password"></label>
  <label>Table prefix<input name="db_prefix" value="db_"></label>
  <button class="btn">Connect database</button>
</form>
<?php else: ?>
<p class="muted">Step 2 of 2 – create the admin login for this panel.</p>
<form method="post" class="stack"><?= csrf_field() ?>
  <label>Username<input name="username" required autocomplete="username"></label>
  <label>Password (min 10 characters)<input name="password" type="password" required minlength="10" autocomplete="new-password"></label>
  <label>Confirm password<input name="password2" type="password" required autocomplete="new-password"></label>
  <label>Website URL<input name="site_url" value="https://www.duobytes.in"></label>
  <button class="btn">Create admin &amp; continue</button>
</form>
<?php endif; });
