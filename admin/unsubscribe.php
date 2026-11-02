<?php
/* Public unsubscribe endpoint (link in every email + one-click List-Unsubscribe POST) */
require __DIR__ . '/inc/bootstrap.php';
header('X-Robots-Tag: noindex');
$email = strtolower(trim((string)($_GET['e'] ?? '')));
$ok = $email !== '' && hash_equals(unsub_token($email), (string)($_GET['t'] ?? ''));
$done = false;
if ($ok && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
    q('INSERT IGNORE INTO ' . t('unsubscribes') . '(email,reason) VALUES(?,?)', [$email, isset($_POST['List-Unsubscribe']) ? 'One-click (mail app)' : 'Unsubscribe link']);
    $done = true;
    if (isset($_POST['List-Unsubscribe'])) { http_response_code(200); exit('OK'); }
}
$already = $ok && q('SELECT 1 FROM ' . t('unsubscribes') . ' WHERE email=?', [$email])->fetchColumn();
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Unsubscribe · DuoBytes</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth"><div class="auth-box">
<img src="../assets/company-full.png" alt="DuoBytes" class="auth-logo">
<?php if (!$ok): ?><h1>Link not valid</h1><p class="muted">This unsubscribe link is incomplete. Please reply to our email with "unsubscribe" and we'll remove you.</p>
<?php elseif ($done || $already): ?><h1>You're unsubscribed</h1><p class="muted"><b><?= htmlspecialchars($email) ?></b> won't receive any more emails from DuoBytes Technologies. Sorry for the trouble.</p>
<?php else: ?><h1>Unsubscribe?</h1><p class="muted">Stop receiving emails from DuoBytes Technologies at <b><?= htmlspecialchars($email) ?></b>.</p>
<form method="post"><button class="btn">Yes, unsubscribe me</button></form><?php endif; ?>
<p class="small muted" style="margin-top:22px"><a href="../">duobytes.in</a></p></div></body></html>
