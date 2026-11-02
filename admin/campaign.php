<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/mailer.php';
require_login();

$c = q('SELECT c.*, t.name tpl FROM ' . t('campaigns') . ' c LEFT JOIN ' . t('templates') . ' t ON t.id=c.template_id WHERE c.id=?', [(int)($_GET['id'] ?? $_POST['id'] ?? 0)])->fetch();
if (!$c) redirect('campaigns.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $act = $_POST['act'] ?? '';
    if ($act === 'save_content' && $c['status'] !== 'running') {
        q('UPDATE ' . t('campaigns') . ' SET name=?, subject=?, html=? WHERE id=?', [trim($_POST['name']), trim($_POST['subject']), $_POST['html'], $c['id']]);
        flash('Campaign email updated.');
    }
    if ($act === 'retry_failed') { $n = q('UPDATE ' . t('sends') . " SET status='queued', error=NULL WHERE campaign_id=? AND status='failed'", [$c['id']])->rowCount();
        if ($n && $c['status'] === 'completed') q('UPDATE ' . t('campaigns') . " SET status='paused' WHERE id=?", [$c['id']]); flash("$n failed emails re-queued."); }
    if ($act === 'delete') { q('DELETE FROM ' . t('campaigns') . ' WHERE id=?', [$c['id']]); flash('Campaign deleted.'); redirect('campaigns.php'); }
    redirect('campaign.php?id=' . $c['id']);
}

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="campaign-' . $c['id'] . '-report.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF"); fputcsv($o, ['email', 'name', 'institution', 'city', 'status', 'sent_at', 'opened_at', 'opens', 'error']);
    foreach (q('SELECT * FROM ' . t('sends') . ' WHERE campaign_id=? ORDER BY id', [$c['id']]) as $r) fputcsv($o, [$r['email'], $r['name'], $r['institution'], $r['city'], $r['status'], $r['sent_at'], $r['opened_at'], $r['opens'], $r['error']]);
    exit;
}

$s = campaign_stats((int)$c['id']);
$filter = $_GET['f'] ?? ''; $page = max(1, (int)($_GET['p'] ?? 1)); $per = 50;
$where = 'campaign_id=?' . ($filter ? ($filter === 'opened' ? ' AND opened_at IS NOT NULL' : ' AND status=?') : '');
$args = [$c['id']]; if ($filter && $filter !== 'opened') $args[] = $filter;
$total = (int)q('SELECT COUNT(*) FROM ' . t('sends') . " WHERE $where", $args)->fetchColumn();
$rows = q('SELECT * FROM ' . t('sends') . " WHERE $where ORDER BY (sent_at IS NULL), sent_at DESC, id LIMIT $per OFFSET " . (($page - 1) * $per), $args)->fetchAll();
$dmin = (int)setting('delay_min', '20'); $dmax = (int)setting('delay_max', '45');
page_head($c['name'], 'campaigns.php'); ?>

<section class="card run" id="run" data-id="<?= $c['id'] ?>" data-dmin="<?= $dmin ?>" data-dmax="<?= $dmax ?>">
  <div class="card-h"><div><h2>Sending</h2><span class="muted small">Template: <?= e($c['tpl']) ?> · <?= $dmin ?>–<?= $dmax ?> sec between emails</span></div><span id="status"><?= badge($s['status']) ?></span></div>
  <div class="bar"><i id="barFill" style="width:<?= $s['total'] ? round(($s['sent'] + $s['failed'] + $s['skipped']) * 100 / $s['total']) : 0 ?>%"></i></div>
  <div class="kv" id="kv">
    <div><span>Recipients</span><b data-k="total"><?= $s['total'] ?></b></div><div><span>Sent</span><b data-k="sent"><?= $s['sent'] ?></b></div>
    <div><span>Queued</span><b data-k="queued"><?= $s['queued'] ?></b></div><div><span>Opened</span><b data-k="opened"><?= $s['opened'] ?></b></div>
    <div><span>Failed</span><b data-k="failed"><?= $s['failed'] ?></b></div><div><span>Skipped</span><b data-k="skipped"><?= $s['skipped'] ?></b></div>
  </div>
  <div class="actions">
    <button class="btn" id="btnStart" <?= in_array($s['status'], ['completed'], true) ? 'disabled' : '' ?>><?= $s['status'] === 'paused' ? 'Resume sending' : 'Start sending' ?></button>
    <button class="btn ghost" id="btnPause">Pause</button>
    <a class="btn ghost" href="?id=<?= $c['id'] ?>&export=1">Download report</a>
    <?php if ($s['failed']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="retry_failed"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn ghost">Retry failed</button></form><?php endif; ?>
  </div>
  <p class="small" id="runMsg">Keep this tab open while sending. You can pause anytime and resume later – nobody gets the email twice.</p>
  <div class="log" id="log"></div>
</section>

<div class="editor">
<section class="card"><h2>Email content</h2>
  <?php if ($c['status'] === 'running'): ?><p class="muted">Pause the campaign to edit the email.</p><?php endif; ?>
  <form method="post" class="stack" id="tplForm"><?= csrf_field() ?><input type="hidden" name="act" value="save_content"><input type="hidden" name="id" value="<?= $c['id'] ?>">
    <label>Campaign name<input name="name" value="<?= e($c['name']) ?>"></label>
    <label>Subject<input name="subject" id="subj" value="<?= e($c['subject']) ?>"></label>
    <label>HTML<textarea name="html" id="html" rows="14" spellcheck="false"><?= e($c['html']) ?></textarea></label>
  </form>
  <div class="actions"><button class="btn sm" form="tplForm" <?= $c['status'] === 'running' ? 'disabled' : '' ?>>Save changes</button>
  <form method="post" onsubmit="return confirm('Delete campaign and its report?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn sm ghost danger">Delete campaign</button></form></div>
</section>
<section class="card preview-card"><div class="card-h"><h2>Preview</h2><span class="muted small">first recipient</span></div>
  <div class="subject-prev" id="subjPrev"></div><iframe id="prev" title="Email preview"></iframe>
  <form class="inline" id="testForm"><input type="email" id="testTo" placeholder="Send a test to…" value="<?= e(setting('smtp_user')) ?>" required><button class="btn sm">Send test</button></form><p class="small" id="testMsg"></p>
</section></div>

<section class="card"><div class="card-h"><h2>Recipients</h2>
  <div class="tabs-s"><?php foreach (['' => 'All', 'sent' => 'Sent', 'opened' => 'Opened', 'queued' => 'Queued', 'failed' => 'Failed', 'skipped' => 'Skipped'] as $k => $lbl): ?><a class="<?= $filter === $k ? 'on' : '' ?>" href="?id=<?= $c['id'] ?>&f=<?= $k ?>"><?= $lbl ?></a><?php endforeach; ?></div></div>
  <div class="scroll"><table class="tbl"><tr><th>Email</th><th>Organisation</th><th>Status</th><th>Sent</th><th>Opened</th><th>Note</th></tr>
  <?php foreach ($rows as $r): ?><tr><td><?= e($r['email']) ?><br><small class="muted"><?= e($r['name']) ?></small></td><td><?= e($r['institution']) ?><br><small class="muted"><?= e($r['city']) ?></small></td><td><?= badge($r['status']) ?></td>
    <td><?= $r['sent_at'] ? e(date('d M, H:i', strtotime($r['sent_at']))) : '–' ?></td><td><?= $r['opened_at'] ? e(date('d M, H:i', strtotime($r['opened_at']))) . ($r['opens'] > 1 ? ' ×' . $r['opens'] : '') : '–' ?></td><td class="small"><?= e($r['error']) ?></td></tr><?php endforeach; ?>
  </table></div>
  <?php if ($total > $per): ?><div class="pager"><?php for ($i = 1; $i <= min(ceil($total / $per), 40); $i++): ?><a class="<?= $i === $page ? 'on' : '' ?>" href="?id=<?= $c['id'] ?>&f=<?= $filter ?>&p=<?= $i ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
</section>
<script>window.CAMPAIGN_ID=<?= (int)$c['id'] ?>;</script>
<?php page_foot();
