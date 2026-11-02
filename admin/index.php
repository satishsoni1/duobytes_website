<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require_login();

$n = fn($sql, $a = []) => (int)q($sql, $a)->fetchColumn();
$stats = [
  ['Contacts', $n('SELECT COUNT(DISTINCT email) FROM ' . t('contacts')), 'contacts.php'],
  ['Emails sent', $n('SELECT COUNT(*) FROM ' . t('sends') . " WHERE status='sent'"), 'campaigns.php'],
  ['Opened', $n('SELECT COUNT(*) FROM ' . t('sends') . ' WHERE opened_at IS NOT NULL'), 'campaigns.php'],
  ['Unsubscribed', $n('SELECT COUNT(*) FROM ' . t('unsubscribes')), 'unsubscribes.php'],
];
$camps = q('SELECT c.*, l.name list_name FROM ' . t('campaigns') . ' c LEFT JOIN ' . t('lists') . ' l ON l.id=c.list_id ORDER BY c.id DESC LIMIT 8')->fetchAll();
$smtpOk = setting('smtp_user') && setting('smtp_pass');
page_head('Dashboard', 'index.php'); ?>

<?php if (!$smtpOk): ?><div class="alert warn">Gmail SMTP is not set up yet. <a href="settings.php">Add it in Settings</a> before sending.</div><?php endif; ?>
<div class="stats"><?php foreach ($stats as [$l, $v, $h]): ?><a class="stat" href="<?= $h ?>"><span><?= $l ?></span><b><?= number_format($v) ?></b></a><?php endforeach; ?></div>

<div class="grid2">
  <section class="card"><div class="card-h"><h2>Quick start</h2></div>
    <ol class="steps">
      <li class="<?= $smtpOk ? 'done' : '' ?>"><a href="settings.php">Connect Gmail SMTP</a> <small>App Password from your Google account</small></li>
      <li class="<?= $n('SELECT COUNT(*) FROM ' . t('lists')) ? 'done' : '' ?>"><a href="contacts.php">Upload an Excel / CSV contact list</a> <small>schools, colleges, hospitals…</small></li>
      <li><a href="templates.php">Review the email template</a> <small>send yourself a test</small></li>
      <li class="<?= $n('SELECT COUNT(*) FROM ' . t('campaigns')) ? 'done' : '' ?>"><a href="campaigns.php">Create &amp; start a campaign</a></li>
    </ol>
  </section>
  <section class="card"><div class="card-h"><h2>Recent campaigns</h2><a href="campaigns.php" class="btn sm">New campaign</a></div>
    <?php if (!$camps): ?><p class="muted">No campaigns yet.</p><?php else: ?>
    <table class="tbl"><tr><th>Campaign</th><th>List</th><th>Status</th><th>Sent / Opened</th></tr>
    <?php foreach ($camps as $c): $s = campaign_stats_lite((int)$c['id']); ?>
      <tr><td><a href="campaign.php?id=<?= $c['id'] ?>"><?= e($c['name']) ?></a></td><td><?= e($c['list_name']) ?></td><td><?= badge($c['status']) ?></td><td><?= $s['sent'] ?> / <?= $s['opened'] ?></td></tr>
    <?php endforeach; ?></table><?php endif; ?>
  </section>
</div>
<?php page_foot();

function campaign_stats_lite(int $id): array {
    $r = q('SELECT SUM(status=\'sent\') sent, SUM(opened_at IS NOT NULL) opened FROM ' . t('sends') . ' WHERE campaign_id=?', [$id])->fetch();
    return ['sent' => (int)$r['sent'], 'opened' => (int)$r['opened']];
}
