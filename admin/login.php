<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
if (!configured() || !has_admin()) redirect('setup.php');
if (!empty($_SESSION['uid'])) redirect('index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (too_many_attempts()) { flash('Too many failed attempts. Try again in 15 minutes.', 'err'); redirect('login.php'); }
    $u = q('SELECT * FROM ' . t('users') . ' WHERE username=?', [trim($_POST['username'] ?? '')])->fetch();
    if ($u && password_verify((string)($_POST['password'] ?? ''), $u['pass'])) {
        if (password_needs_rehash($u['pass'], PASSWORD_DEFAULT)) q('UPDATE ' . t('users') . ' SET pass=? WHERE id=?', [password_hash($_POST['password'], PASSWORD_DEFAULT), $u['id']]);
        q('DELETE FROM ' . t('login_attempts') . ' WHERE ip=?', [client_ip()]);
        q('UPDATE ' . t('users') . ' SET last_login=NOW() WHERE id=?', [$u['id']]);
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id']; $_SESSION['uname'] = $u['username']; $_SESSION['last'] = time();
        redirect('index.php');
    }
    q('INSERT INTO ' . t('login_attempts') . '(ip,at) VALUES(?,?)', [client_ip(), time()]);
    usleep(400000);
    flash('Wrong username or password.', 'err'); redirect('login.php');
}
if (isset($_GET['expired'])) flash('Session expired – please log in again.', 'warn');

auth_page('Admin login', function () { ?>
<form method="post" class="stack"><?= csrf_field() ?>
  <label>Username<input name="username" required autofocus autocomplete="username"></label>
  <label>Password<input name="password" type="password" required autocomplete="current-password"></label>
  <button class="btn">Log in</button>
</form><?php });
