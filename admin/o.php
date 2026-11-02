<?php
/* Open-tracking pixel */
require __DIR__ . '/inc/bootstrap.php';
$tk = preg_replace('/[^a-f0-9]/', '', (string)($_GET['t'] ?? ''));
if (strlen($tk) === 24) {
    try { q('UPDATE ' . t('sends') . ' SET opens=opens+1, opened_at=COALESCE(opened_at,NOW()) WHERE token=?', [$tk]); } catch (Throwable $e) {}
}
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
