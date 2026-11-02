<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/mailer.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $act = $_POST['act'] ?? '';
    if ($act === 'create') {
        $tp = q('SELECT * FROM ' . t('templates') . ' WHERE id=?', [(int)$_POST['template_id']])->fetch();
        $listIds = array_map('intval', (array)($_POST['list_ids'] ?? []));
        if (!$tp || !$listIds) { flash('Choose a template and at least one contact list.', 'err'); redirect('campaigns.php'); }
        $name = trim($_POST['name']) ?: ($tp['name'] . ' – ' . date('d M Y'));
        $pdo = db(); $pdo->beginTransaction();
        q('INSERT INTO ' . t('campaigns') . '(name,template_id,list_id,subject,html,slug) VALUES(?,?,?,?,?,?)',
          [$name, $tp['id'], $listIds[0], $tp['subject'], $tp['html'], slugify($name)]);
        $cid = (int)$pdo->lastInsertId();
        $in = implode(',', array_fill(0, count($listIds), '?'));
        $rows = q('SELECT c.* FROM ' . t('contacts') . ' c LEFT JOIN ' . t('unsubscribes') . " u ON u.email=c.email
                   WHERE c.list_id IN ($in) AND u.email IS NULL ORDER BY c.id", $listIds)->fetchAll();
        // skip people already emailed in the last N days (any campaign)
        $days = (int)($_POST['skip_days'] ?? 0); $recent = [];
        if ($days > 0) foreach (q('SELECT DISTINCT email FROM ' . t('sends') . " WHERE status='sent' AND sent_at >= ?", [date('Y-m-d H:i:s', time() - $days * 86400)]) as $r) $recent[$r['email']] = 1;
        $ins = $pdo->prepare('INSERT IGNORE INTO ' . t('sends') . '(campaign_id,contact_id,email,name,institution,city,token) VALUES(?,?,?,?,?,?,?)');
        $n = $skipped = 0;
        foreach ($rows as $r) {
            if (isset($recent[$r['email']])) { $skipped++; continue; }
            $ins->execute([$cid, $r['id'], $r['email'], $r['name'], $r['institution'], $r['city'], bin2hex(random_bytes(12))]); $n += $ins->rowCount();
        }
        $pdo->commit();
        flash("Campaign created with $n recipients" . ($skipped ? " ($skipped emailed recently were skipped)" : '') . '. Review it, send a test, then press Start.');
        redirect('campaign.php?id=' . $cid);
    }
    if ($act === 'delete') { q('DELETE FROM ' . t('campaigns') . ' WHERE id=?', [(int)$_POST['id']]); flash('Campaign deleted.'); redirect('campaigns.php'); }
}

$lists = q('SELECT l.*, COUNT(c.id) n FROM ' . t('lists') . ' l LEFT JOIN ' . t('contacts') . ' c ON c.list_id=l.id GROUP BY l.id ORDER BY l.id DESC')->fetchAll();
$tpls = q('SELECT id,name,product FROM ' . t('templates') . ' ORDER BY id')->fetchAll();
$camps = q('SELECT c.*, t.name tpl, (SELECT COUNT(*) FROM ' . t('sends') . ' s WHERE s.campaign_id=c.id) total,
           (SELECT COUNT(*) FROM ' . t('sends') . " s WHERE s.campaign_id=c.id AND s.status='sent') sent,
           (SELECT COUNT(*) FROM " . t('sends') . ' s WHERE s.campaign_id=c.id AND s.opened_at IS NOT NULL) opened
           FROM ' . t('campaigns') . ' c LEFT JOIN ' . t('templates') . ' t ON t.id=c.template_id ORDER BY c.id DESC')->fetchAll();
$pre = (int)($_GET['list'] ?? 0);
page_head('Campaigns', 'campaigns.php'); ?>
<div class="grid2">
<section class="card"><h2>New campaign</h2>
<?php if (!$lists): ?><p class="muted">Upload a <a href="contacts.php">contact list</a> first.</p><?php else: ?>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="create">
    <label>Campaign name<input name="name" placeholder="e.g. Pune schools – School ERP – Nov"></label>
    <label>Email template<select name="template_id" required><?php foreach ($tpls as $tp): ?><option value="<?= $tp['id'] ?>"><?= e($tp['name']) ?></option><?php endforeach; ?></select></label>
    <fieldset><legend>Send to lists</legend><?php foreach ($lists as $l): ?>
      <label class="check"><input type="checkbox" name="list_ids[]" value="<?= $l['id'] ?>" <?= $pre === (int)$l['id'] ? 'checked' : '' ?>> <?= e($l['name']) ?> <span class="muted">(<?= number_format((int)$l['n']) ?> · <?= e($l['segment']) ?>)</span></label>
    <?php endforeach; ?></fieldset>
    <label>Skip contacts emailed in the last<select name="skip_days"><option value="0">— don't skip —</option><option value="7">7 days</option><option value="15" selected>15 days</option><option value="30">30 days</option></select></label>
    <button class="btn">Create campaign</button>
    <p class="muted small">Unsubscribed and duplicate emails are removed automatically. Nothing is sent until you press <b>Start</b>.</p>
  </form><?php endif; ?>
</section>
<section class="card"><h2>All campaigns</h2>
<?php if (!$camps): ?><p class="muted">No campaigns yet.</p><?php else: ?>
<div class="scroll"><table class="tbl"><tr><th>Campaign</th><th>Template</th><th>Status</th><th>Sent</th><th>Opened</th></tr>
<?php foreach ($camps as $c): ?><tr><td><a href="campaign.php?id=<?= $c['id'] ?>"><?= e($c['name']) ?></a><br><small class="muted"><?= e(date('d M Y', strtotime($c['created_at']))) ?></small></td><td><?= e($c['tpl']) ?></td><td><?= badge($c['status']) ?></td>
  <td><?= (int)$c['sent'] ?> / <?= (int)$c['total'] ?></td><td><?= (int)$c['sent'] ? round($c['opened'] * 100 / $c['sent']) . '%' : '–' ?></td></tr><?php endforeach; ?>
</table></div><?php endif; ?>
</section></div>
<?php page_foot();
