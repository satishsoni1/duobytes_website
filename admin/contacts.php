<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/sheet.php';
require_login();

$SEGMENTS = ['School', 'College', 'Hospital / Clinic', 'Business', 'Pharma / FMCG', 'Other'];
$FIELDS = ['email' => 'Email *', 'name' => 'Contact name', 'institution' => 'School / Organisation', 'city' => 'City', 'phone' => 'Phone'];
$tmpFile = fn($k) => DATA_DIR . '/upload_' . preg_replace('/[^a-f0-9]/', '', $k) . '.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $act = $_POST['act'] ?? '';
    if ($act === 'upload') {
        try {
            $f = $_FILES['file'] ?? null;
            if (!$f || $f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Please choose a file to upload.');
            if ($f['size'] > 15 * 1024 * 1024) throw new RuntimeException('File too large (max 15 MB).');
            $rows = read_sheet($f['tmp_name'], $f['name']);
            if (count($rows) < 2) throw new RuntimeException('The sheet needs a header row and at least one contact.');
            $key = bin2hex(random_bytes(8));
            file_put_contents($tmpFile($key), json_encode(['rows' => $rows, 'file' => $f['name'],
                'list_name' => trim($_POST['list_name'] ?: pathinfo($f['name'], PATHINFO_FILENAME)), 'segment' => $_POST['segment'] ?? 'Other', 'list_id' => (int)($_POST['list_id'] ?? 0)]));
            redirect('contacts.php?map=' . $key);
        } catch (Throwable $ex) { flash($ex->getMessage(), 'err'); redirect('contacts.php'); }
    }
    if ($act === 'import') {
        $key = $_POST['key'] ?? ''; $file = $tmpFile($key);
        if (!file_exists($file)) { flash('Upload expired, please upload again.', 'err'); redirect('contacts.php'); }
        $d = json_decode(file_get_contents($file), true); $rows = $d['rows']; $hasHeader = !empty($_POST['has_header']);
        if ($hasHeader) array_shift($rows);
        $map = []; foreach (array_keys($FIELDS) as $k) $map[$k] = ($_POST['col'][$k] ?? '') === '' ? null : (int)$_POST['col'][$k];
        if ($map['email'] === null) { flash('Please choose which column has the email address.', 'err'); redirect('contacts.php?map=' . $key); }
        $pdo = db(); $pdo->beginTransaction();
        if ($d['list_id']) $listId = $d['list_id'];
        else { q('INSERT INTO ' . t('lists') . '(name,segment) VALUES(?,?)', [$d['list_name'] ?: 'Contacts', $d['segment']]); $listId = (int)$pdo->lastInsertId(); }
        $ins = $pdo->prepare('INSERT IGNORE INTO ' . t('contacts') . '(list_id,email,name,institution,city,phone) VALUES(?,?,?,?,?,?)');
        $added = $dup = $bad = 0;
        foreach ($rows as $r) {
            $cell = fn($k) => $map[$k] === null ? null : mb_substr(trim((string)($r[$map[$k]] ?? '')), 0, 190);
            // a cell may hold several emails separated by , ; / or spaces
            $emails = preg_split('/[\s,;\/]+/', strtolower((string)$cell('email')), -1, PREG_SPLIT_NO_EMPTY);
            if (!$emails) { $bad++; continue; }
            foreach ($emails as $em) {
                $em = trim($em, " .<>()\"'");
                if (!valid_email($em)) { $bad++; continue; }
                $ins->execute([$listId, $em, $cell('name'), $cell('institution'), $cell('city'), $cell('phone')]);
                $ins->rowCount() ? $added++ : $dup++;
            }
        }
        $pdo->commit(); @unlink($file);
        flash("Imported $added contacts" . ($dup ? ", $dup duplicates skipped" : '') . ($bad ? ", $bad invalid / empty emails skipped" : '') . '.');
        redirect('contacts.php?list=' . $listId);
    }
    if ($act === 'delete_list') { q('DELETE FROM ' . t('lists') . ' WHERE id=?', [(int)$_POST['id']]); flash('List deleted.'); redirect('contacts.php'); }
    if ($act === 'delete_contact') { q('DELETE FROM ' . t('contacts') . ' WHERE id=?', [(int)$_POST['id']]); flash('Contact removed.'); redirect('contacts.php?list=' . (int)$_POST['list']); }
    if ($act === 'add_contact') {
        $em = strtolower(trim($_POST['email'] ?? ''));
        if (!valid_email($em)) flash('Invalid email.', 'err');
        else { q('INSERT IGNORE INTO ' . t('contacts') . '(list_id,email,name,institution,city,phone) VALUES(?,?,?,?,?,?)', [(int)$_POST['list'], $em, $_POST['name'] ?? '', $_POST['institution'] ?? '', $_POST['city'] ?? '', $_POST['phone'] ?? '']); flash('Contact added.'); }
        redirect('contacts.php?list=' . (int)$_POST['list']);
    }
}

// CSV export
if (isset($_GET['export'])) {
    $l = q('SELECT * FROM ' . t('lists') . ' WHERE id=?', [(int)$_GET['export']])->fetch(); if (!$l) exit;
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="' . slugify($l['name']) . '.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF"); fputcsv($o, ['email', 'name', 'institution', 'city', 'phone', 'unsubscribed']);
    foreach (q('SELECT c.*, (u.email IS NOT NULL) unsub FROM ' . t('contacts') . ' c LEFT JOIN ' . t('unsubscribes') . ' u ON u.email=c.email WHERE list_id=?', [$l['id']]) as $r)
        fputcsv($o, [$r['email'], $r['name'], $r['institution'], $r['city'], $r['phone'], $r['unsub'] ? 'yes' : '']);
    exit;
}

/* ---------- Step 2: column mapping ---------- */
if (isset($_GET['map'])) {
    $key = $_GET['map']; $file = $tmpFile($key);
    if (!file_exists($file)) { flash('Upload expired, please upload again.', 'err'); redirect('contacts.php'); }
    $d = json_decode(file_get_contents($file), true); $rows = $d['rows']; $headers = $rows[0];
    $guess = guess_columns($headers);
    if ($guess['email'] === null) { // pick the column containing most emails
        $best = -1; $bestN = 0;
        foreach (array_keys($headers) as $i) { $nn = 0; foreach (array_slice($rows, 0, 50) as $r) if (str_contains((string)($r[$i] ?? ''), '@')) $nn++; if ($nn > $bestN) { $bestN = $nn; $best = $i; } }
        if ($best >= 0) $guess['email'] = $best;
    }
    $headerLooksLikeData = str_contains(implode(' ', $headers), '@');
    page_head('Map columns', 'contacts.php'); ?>
    <section class="card"><div class="card-h"><h2><?= e($d['file']) ?> · <?= count($rows) - 1 ?> rows</h2></div>
    <p class="muted">Tell us which column is which. We guessed from your headers – check and adjust.</p>
    <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="import"><input type="hidden" name="key" value="<?= e($key) ?>">
      <div class="maprow">
      <?php foreach ($FIELDS as $k => $label): ?>
        <label><?= $label ?><select name="col[<?= $k ?>]"><option value="">— not in file —</option>
          <?php foreach ($headers as $i => $h): ?><option value="<?= $i ?>" <?= $guess[$k] === $i ? 'selected' : '' ?>><?= e(($h !== '' ? $h : 'Column ' . ($i + 1))) ?></option><?php endforeach; ?>
        </select></label>
      <?php endforeach; ?></div>
      <label class="check"><input type="checkbox" name="has_header" value="1" <?= $headerLooksLikeData ? '' : 'checked' ?>> First row is a header (don't import it)</label>
      <div class="scroll"><table class="tbl small"><tr><?php foreach ($headers as $h) echo '<th>' . e($h) . '</th>'; ?></tr>
      <?php foreach (array_slice($rows, 1, 6) as $r): ?><tr><?php foreach (array_keys($headers) as $i) echo '<td>' . e($r[$i] ?? '') . '</td>'; ?></tr><?php endforeach; ?></table></div>
      <div><button class="btn">Import contacts</button> <a href="contacts.php" class="btn ghost">Cancel</a></div>
    </form></section>
    <?php page_foot(); exit;
}

/* ---------- List detail ---------- */
if (isset($_GET['list'])) {
    $l = q('SELECT * FROM ' . t('lists') . ' WHERE id=?', [(int)$_GET['list']])->fetch();
    if (!$l) redirect('contacts.php');
    $search = trim($_GET['s'] ?? ''); $page = max(1, (int)($_GET['p'] ?? 1)); $per = 50;
    $where = 'list_id=?'; $args = [$l['id']];
    if ($search !== '') { $where .= ' AND (email LIKE ? OR name LIKE ? OR institution LIKE ? OR city LIKE ?)'; array_push($args, ...array_fill(0, 4, "%$search%")); }
    $total = (int)q('SELECT COUNT(*) FROM ' . t('contacts') . " WHERE $where", $args)->fetchColumn();
    $rows = q('SELECT c.*, (u.email IS NOT NULL) unsub FROM ' . t('contacts') . ' c LEFT JOIN ' . t('unsubscribes') . " u ON u.email=c.email WHERE $where ORDER BY c.id LIMIT $per OFFSET " . (($page - 1) * $per), $args)->fetchAll();
    page_head($l['name'], 'contacts.php'); ?>
    <section class="card"><div class="card-h"><h2><?= number_format($total) ?> contacts <small class="muted">· <?= e($l['segment']) ?></small></h2>
      <div class="actions"><a class="btn sm" href="campaigns.php?list=<?= $l['id'] ?>">Create campaign</a><a class="btn sm ghost" href="?export=<?= $l['id'] ?>">Export CSV</a>
      <form method="post" onsubmit="return confirm('Delete this list and all its contacts?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete_list"><input type="hidden" name="id" value="<?= $l['id'] ?>"><button class="btn sm danger">Delete list</button></form></div></div>
      <form class="inline" method="get"><input type="hidden" name="list" value="<?= $l['id'] ?>"><input name="s" value="<?= e($search) ?>" placeholder="Search email, name, school, city"><button class="btn sm ghost">Search</button></form>
      <div class="scroll"><table class="tbl"><tr><th>Email</th><th>Name</th><th>Organisation</th><th>City</th><th>Phone</th><th></th></tr>
      <?php foreach ($rows as $r): ?><tr><td><?= e($r['email']) ?> <?= $r['unsub'] ? badge('skipped') : '' ?></td><td><?= e($r['name']) ?></td><td><?= e($r['institution']) ?></td><td><?= e($r['city']) ?></td><td><?= e($r['phone']) ?></td>
        <td><form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="delete_contact"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="list" value="<?= $l['id'] ?>"><button class="link danger" title="Remove">✕</button></form></td></tr><?php endforeach; ?>
      </table></div>
      <?php if ($total > $per): ?><div class="pager"><?php for ($i = 1; $i <= ceil($total / $per); $i++): ?><a class="<?= $i === $page ? 'on' : '' ?>" href="?list=<?= $l['id'] ?>&s=<?= urlencode($search) ?>&p=<?= $i ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
    </section>
    <div class="grid2">
    <section class="card"><h2>Add one contact</h2>
      <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="add_contact"><input type="hidden" name="list" value="<?= $l['id'] ?>">
        <div class="row2"><label>Email *<input name="email" type="email" required></label><label>Name<input name="name"></label></div>
        <div class="row2"><label>Organisation<input name="institution"></label><label>City<input name="city"></label></div>
        <button class="btn sm">Add</button></form></section>
    <section class="card"><h2>Upload more to this list</h2>
      <form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="upload"><input type="hidden" name="list_id" value="<?= $l['id'] ?>">
        <label>Excel (.xlsx) or CSV<input type="file" name="file" accept=".xlsx,.csv" required></label><button class="btn sm">Upload</button></form></section>
    </div>
    <?php page_foot(); exit;
}

/* ---------- Lists overview ---------- */
$lists = q('SELECT l.*, COUNT(c.id) n FROM ' . t('lists') . ' l LEFT JOIN ' . t('contacts') . ' c ON c.list_id=l.id GROUP BY l.id ORDER BY l.id DESC')->fetchAll();
page_head('Contacts', 'contacts.php'); ?>
<div class="grid2">
<section class="card"><h2>Upload contacts</h2>
  <form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?><input type="hidden" name="act" value="upload">
    <label>Excel (.xlsx) or CSV file<input type="file" name="file" accept=".xlsx,.csv" required></label>
    <div class="row2"><label>List name<input name="list_name" placeholder="e.g. Pune CBSE schools"></label>
    <label>Segment<select name="segment"><?php foreach ($SEGMENTS as $s) echo "<option>" . e($s) . "</option>"; ?></select></label></div>
    <button class="btn">Upload &amp; map columns</button>
  </form>
  <p class="muted small">Columns can be in any order – e.g. <i>School Name, Principal, Email, City, Mobile</i>. Duplicate and invalid emails are skipped automatically. <a href="assets/sample-contacts.csv" download>Download sample CSV</a></p>
</section>
<section class="card"><h2>Your lists</h2>
  <?php if (!$lists): ?><p class="muted">No lists yet – upload your first Excel sheet.</p><?php else: ?>
  <table class="tbl"><tr><th>List</th><th>Segment</th><th>Contacts</th><th></th></tr>
  <?php foreach ($lists as $l): ?><tr><td><a href="?list=<?= $l['id'] ?>"><?= e($l['name']) ?></a></td><td><?= e($l['segment']) ?></td><td><?= number_format((int)$l['n']) ?></td><td><a class="btn sm ghost" href="campaigns.php?list=<?= $l['id'] ?>">Campaign →</a></td></tr><?php endforeach; ?>
  </table><?php endif; ?>
</section></div>
<?php page_foot();
