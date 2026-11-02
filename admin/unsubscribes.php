<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (($_POST['act'] ?? '') === 'add') {
        $n = 0; $st = db()->prepare('INSERT IGNORE INTO ' . t('unsubscribes') . '(email,reason) VALUES(?,?)');
        foreach (preg_split('/[\s,;]+/', strtolower((string)$_POST['emails']), -1, PREG_SPLIT_NO_EMPTY) as $em) if (valid_email($em)) { $st->execute([$em, 'Added manually']); $n += $st->rowCount(); }
        flash("$n emails added to the do-not-email list.");
    }
    if (($_POST['act'] ?? '') === 'remove') { q('DELETE FROM ' . t('unsubscribes') . ' WHERE email=?', [$_POST['email']]); flash('Removed from do-not-email list.'); }
    redirect('unsubscribes.php');
}
$rows = q('SELECT * FROM ' . t('unsubscribes') . ' ORDER BY at DESC LIMIT 500')->fetchAll();
page_head('Unsubscribes', 'unsubscribes.php'); ?>
<div class="grid2">
<section class="card"><h2>Do-not-email list <span class="muted">(<?= count($rows) ?>)</span></h2>
  <p class="muted small">People here are never emailed again by any campaign. Those who click "unsubscribe" in an email are added automatically.</p>
  <div class="scroll"><table class="tbl"><tr><th>Email</th><th>Reason</th><th>Date</th><th></th></tr>
  <?php foreach ($rows as $r): ?><tr><td><?= e($r['email']) ?></td><td class="small"><?= e($r['reason']) ?></td><td class="small"><?= e(date('d M Y', strtotime($r['at']))) ?></td>
    <td><form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="remove"><input type="hidden" name="email" value="<?= e($r['email']) ?>"><button class="link">Remove</button></form></td></tr><?php endforeach; ?>
  </table></div></section>
<section class="card"><h2>Add manually</h2>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="add">
    <label>Emails (one per line, or comma-separated)<textarea name="emails" rows="8" placeholder="Paste emails of people who replied 'not interested' or asked to stop"></textarea></label>
    <button class="btn sm">Add to list</button></form></section>
</div>
<?php page_foot();
