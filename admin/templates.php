<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/mailer.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $act = $_POST['act'] ?? '';
    if ($act === 'save') {
        $id = (int)$_POST['id'];
        $args = [trim($_POST['name']), trim($_POST['product']), trim($_POST['subject']), $_POST['html']];
        if ($id) q('UPDATE ' . t('templates') . ' SET name=?,product=?,subject=?,html=? WHERE id=?', [...$args, $id]);
        else { q('INSERT INTO ' . t('templates') . '(name,product,subject,html,slug) VALUES(?,?,?,?,?)', [...$args, slugify($_POST['name']) . '-' . substr(bin2hex(random_bytes(3)), 0, 6)]); $id = (int)db()->lastInsertId(); }
        flash('Template saved.'); redirect('templates.php?id=' . $id);
    }
    if ($act === 'duplicate') {
        $tp = q('SELECT * FROM ' . t('templates') . ' WHERE id=?', [(int)$_POST['id']])->fetch();
        q('INSERT INTO ' . t('templates') . '(name,product,subject,html,slug) VALUES(?,?,?,?,?)', [$tp['name'] . ' (copy)', $tp['product'], $tp['subject'], $tp['html'], $tp['slug'] . '-' . substr(bin2hex(random_bytes(3)), 0, 6)]);
        flash('Template duplicated.'); redirect('templates.php?id=' . db()->lastInsertId());
    }
    if ($act === 'delete') { q('DELETE FROM ' . t('templates') . ' WHERE id=?', [(int)$_POST['id']]); flash('Template deleted.'); redirect('templates.php'); }
    if ($act === 'reset') {
        require_once __DIR__ . '/inc/default_templates.php';
        $tp = q('SELECT * FROM ' . t('templates') . ' WHERE id=?', [(int)$_POST['id']])->fetch();
        foreach (default_templates() as $d) if ($d['slug'] === $tp['slug']) { q('UPDATE ' . t('templates') . ' SET subject=?, html=? WHERE id=?', [$d['subject'], $d['html'], $tp['id']]); flash('Restored the original draft.'); }
        redirect('templates.php?id=' . $tp['id']);
    }
}

if (isset($_GET['id']) || isset($_GET['new'])) {
    $tp = isset($_GET['new']) ? ['id' => 0, 'name' => '', 'product' => '', 'subject' => '', 'html' => '<p>Dear {{name|Sir/Madam}},</p>', 'slug' => '']
        : q('SELECT * FROM ' . t('templates') . ' WHERE id=?', [(int)$_GET['id']])->fetch();
    if (!$tp) redirect('templates.php');
    require_once __DIR__ . '/inc/default_templates.php';
    $isDefault = in_array($tp['slug'], array_column(default_templates(), 'slug'), true);
    page_head($tp['id'] ? 'Edit template' : 'New template', 'templates.php'); ?>
    <div class="editor">
      <section class="card">
        <form method="post" class="stack" id="tplForm"><?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" value="<?= $tp['id'] ?>">
          <div class="row2"><label>Template name<input name="name" value="<?= e($tp['name']) ?>" required></label><label>Product<input name="product" value="<?= e($tp['product']) ?>"></label></div>
          <label>Subject line<input name="subject" id="subj" value="<?= e($tp['subject']) ?>" required></label>
          <label>Email HTML<textarea name="html" id="html" rows="22" spellcheck="false"><?= e($tp['html']) ?></textarea></label>
          <p class="muted small">Placeholders: <code>{{name|Sir/Madam}}</code> <code>{{institution|your school}}</code> <code>{{city}}</code> <code>{{email}}</code> <code>{{sender_name}}</code> <code>{{sender_phone}}</code> <code>{{site_url}}</code> <code>{{campaign}}</code> <code>{{unsubscribe_url}}</code> — text after <code>|</code> is used when the value is empty.</p>
        </form>
        <div class="actions"><button class="btn" form="tplForm">Save template</button>
        <?php if ($tp['id']): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="duplicate"><input type="hidden" name="id" value="<?= $tp['id'] ?>"><button class="btn ghost">Duplicate</button></form>
          <?php if ($isDefault): ?><form method="post" onsubmit="return confirm('Replace with the original draft?')"><?= csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="id" value="<?= $tp['id'] ?>"><button class="btn ghost">Restore original</button></form><?php endif; ?>
          <form method="post" onsubmit="return confirm('Delete this template?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $tp['id'] ?>"><button class="btn ghost danger">Delete</button></form>
        <?php endif; ?></div>
      </section>
      <section class="card preview-card">
        <div class="card-h"><h2>Preview</h2><span class="muted small">sample: "Sunrise Public School", Pune</span></div>
        <div class="subject-prev" id="subjPrev"></div>
        <iframe id="prev" title="Email preview"></iframe>
        <?php if ($tp['id']): ?>
        <form class="inline" id="testForm"><input type="email" id="testTo" placeholder="Send a test to…" value="<?= e(setting('smtp_user')) ?>" required><button class="btn sm">Send test</button></form>
        <p class="small" id="testMsg"></p><?php endif; ?>
      </section>
    </div>
    <script>window.TPL_ID=<?= (int)$tp['id'] ?>;</script>
    <?php page_foot(); exit;
}

$tpls = q('SELECT * FROM ' . t('templates') . ' ORDER BY id')->fetchAll();
page_head('Email templates', 'templates.php'); ?>
<div class="card-h"><p class="muted">Ready-made drafts for each product. Open one to edit, preview and send yourself a test.</p><a href="?new=1" class="btn sm">New template</a></div>
<div class="tpl-grid"><?php foreach ($tpls as $tp): ?>
  <a class="tpl" href="?id=<?= $tp['id'] ?>"><span class="badge blue"><?= e($tp['product']) ?></span><h3><?= e($tp['name']) ?></h3><p><?= e($tp['subject']) ?></p><small>Updated <?= e(date('d M Y', strtotime($tp['updated_at']))) ?></small></a>
<?php endforeach; ?></div>
<?php page_foot();
