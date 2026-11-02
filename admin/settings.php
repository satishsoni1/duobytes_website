<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/smtp.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $act = $_POST['act'] ?? '';
    if ($act === 'smtp') {
        foreach (['smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user', 'from_name', 'from_email', 'reply_to', 'sender_phone', 'site_url'] as $k)
            set_setting($k, trim((string)($_POST[$k] ?? '')));
        if (($_POST['smtp_pass'] ?? '') !== '') set_setting('smtp_pass', encrypt_secret(str_replace(' ', '', $_POST['smtp_pass'])));
        set_setting('daily_limit', (string)max(1, min(2000, (int)$_POST['daily_limit'])));
        $dmin = max(5, (int)$_POST['delay_min']); set_setting('delay_min', (string)$dmin);
        set_setting('delay_max', (string)max($dmin, (int)$_POST['delay_max']));
        flash('Settings saved. Use "Test connection" to check Gmail login.');
    }
    if ($act === 'password') {
        $u = q('SELECT * FROM ' . t('users') . ' WHERE id=?', [$_SESSION['uid']])->fetch();
        if (!password_verify((string)$_POST['current'], $u['pass'])) flash('Current password is wrong.', 'err');
        elseif (strlen((string)$_POST['new']) < 10) flash('New password must be at least 10 characters.', 'err');
        elseif ($_POST['new'] !== $_POST['new2']) flash('New passwords do not match.', 'err');
        else { q('UPDATE ' . t('users') . ' SET pass=? WHERE id=?', [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); flash('Password changed.'); }
    }
    if ($act === 'add_user') {
        $un = trim($_POST['username'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_.@-]{3,64}$/', $un) || strlen((string)$_POST['password']) < 10) flash('Username 3–64 chars and password at least 10 characters.', 'err');
        else { try { q('INSERT INTO ' . t('users') . '(username,pass) VALUES(?,?)', [$un, password_hash($_POST['password'], PASSWORD_DEFAULT)]); flash("User $un added."); } catch (PDOException $ex) { flash('That username already exists.', 'err'); } }
    }
    if ($act === 'del_user' && (int)$_POST['id'] !== (int)$_SESSION['uid']) { q('DELETE FROM ' . t('users') . ' WHERE id=?', [(int)$_POST['id']]); flash('User removed.'); }
    redirect('settings.php');
}
$v = fn($k, $d = '') => e(setting($k, $d));
$users = q('SELECT id, username, last_login FROM ' . t('users') . ' ORDER BY id')->fetchAll();
page_head('Settings', 'settings.php'); ?>
<div class="grid2">
<section class="card"><h2>Gmail SMTP</h2>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="smtp">
    <div class="row3"><label>SMTP host<input name="smtp_host" value="<?= $v('smtp_host', 'smtp.gmail.com') ?>"></label>
      <label>Port<input name="smtp_port" value="<?= $v('smtp_port', '465') ?>"></label>
      <label>Security<select name="smtp_secure"><?php foreach (['ssl' => 'SSL (465)', 'tls' => 'STARTTLS (587)', 'none' => 'None'] as $k => $l): ?><option value="<?= $k ?>" <?= setting('smtp_secure', 'ssl') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></label></div>
    <label>Gmail address<input name="smtp_user" type="email" value="<?= $v('smtp_user') ?>" placeholder="you@gmail.com or you@duobytes.in" required></label>
    <label>App Password <?= setting('smtp_pass') ? '<span class="badge green">saved</span>' : '' ?><input name="smtp_pass" type="password" placeholder="<?= setting('smtp_pass') ? 'leave blank to keep the saved one' : '16-character app password' ?>" autocomplete="new-password"></label>
    <p class="muted small">Not your normal Gmail password. Turn on 2-Step Verification, then create one at <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">myaccount.google.com/apppasswords</a>. It is stored encrypted.</p>
    <div class="row2"><label>From name<input name="from_name" value="<?= $v('from_name', 'DuoBytes Technologies') ?>"></label><label>From email <small class="muted">(blank = Gmail address)</small><input name="from_email" type="email" value="<?= $v('from_email') ?>"></label></div>
    <div class="row2"><label>Reply-to email<input name="reply_to" type="email" value="<?= $v('reply_to') ?>" placeholder="sales@duobytes.in"></label><label>Your phone (in signature)<input name="sender_phone" value="<?= $v('sender_phone', '+91 90000 00000') ?>"></label></div>
    <label>Website URL <small class="muted">(used for logos, links &amp; unsubscribe)</small><input name="site_url" value="<?= $v('site_url', 'https://www.duobytes.in') ?>"></label>
    <h3>Sending speed</h3>
    <div class="row3"><label>Daily limit<input name="daily_limit" type="number" value="<?= $v('daily_limit', '400') ?>" min="1" max="2000"></label>
      <label>Min gap (sec)<input name="delay_min" type="number" value="<?= $v('delay_min', '20') ?>" min="5"></label>
      <label>Max gap (sec)<input name="delay_max" type="number" value="<?= $v('delay_max', '45') ?>" min="5"></label></div>
    <p class="muted small">Gmail allows about 500 emails/day on a free account and 2,000/day on Google Workspace. Start new accounts with 50–100/day and increase slowly – this protects your account from being blocked.</p>
    <div class="actions"><button class="btn">Save settings</button><button type="button" class="btn ghost" id="testSmtp">Test connection</button></div>
    <p class="small" id="smtpMsg"></p>
  </form>
</section>
<div>
<section class="card"><h2>Change password</h2>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="password">
    <label>Current password<input name="current" type="password" required autocomplete="current-password"></label>
    <div class="row2"><label>New password<input name="new" type="password" minlength="10" required autocomplete="new-password"></label><label>Confirm<input name="new2" type="password" minlength="10" required autocomplete="new-password"></label></div>
    <button class="btn sm">Update password</button></form>
</section>
<section class="card"><h2>Admin users</h2>
  <table class="tbl"><?php foreach ($users as $u): ?><tr><td><?= e($u['username']) ?></td><td class="muted small"><?= $u['last_login'] ? 'last login ' . e(date('d M, H:i', strtotime($u['last_login']))) : 'never logged in' ?></td>
    <td><?php if ((int)$u['id'] !== (int)$_SESSION['uid']): ?><form method="post" onsubmit="return confirm('Remove this user?')"><?= csrf_field() ?><input type="hidden" name="act" value="del_user"><input type="hidden" name="id" value="<?= $u['id'] ?>"><button class="link danger">Remove</button></form><?php else: ?><span class="muted small">you</span><?php endif; ?></td></tr><?php endforeach; ?></table>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="add_user">
    <div class="row2"><label>Username<input name="username" required></label><label>Password<input name="password" type="password" minlength="10" required autocomplete="new-password"></label></div>
    <button class="btn sm ghost">Add user</button></form>
</section></div>
</div>
<?php page_foot();
