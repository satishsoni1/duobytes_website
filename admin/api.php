<?php
/* JSON endpoints for the admin UI (login + CSRF required) */
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/mailer.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['uid'])) { http_response_code(401); exit(json_encode(['error' => 'Logged out – please log in again.'])); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('{}'); }
check_csrf();
$_SESSION['last'] = time();
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$act = $in['act'] ?? '';
$sample = ['name' => 'Mrs. Sharma', 'institution' => 'Sunrise Public School', 'city' => 'Pune', 'email' => 'principal@example.com'];

try {
    switch ($act) {
        case 'preview': {   // render subject+html with sample or a real contact
            $c = ['name' => 'Preview', 'slug' => 'preview', 'subject' => (string)$in['subject'], 'html' => (string)$in['html']];
            $row = $sample;
            if (!empty($in['campaign'])) { $r = q('SELECT * FROM ' . t('sends') . ' WHERE campaign_id=? ORDER BY id LIMIT 1', [(int)$in['campaign']])->fetch(); if ($r) $row = $r; }
            $m = message_for($c, $row, false);
            echo json_encode($m); break;
        }
        case 'send_test': {
            $to = trim((string)$in['to']); if (!valid_email($to)) throw new RuntimeException('Enter a valid email address.');
            $c = ['name' => 'Test', 'slug' => 'test', 'subject' => '[TEST] ' . (string)$in['subject'], 'html' => (string)$in['html']];
            $m = message_for($c, $sample + ['email' => $to], false);
            $smtp = smtp_from_settings(); send_mail($smtp, $to, '', $m['subject'], $m['html'], false); $smtp->quit();
            echo json_encode(['ok' => true, 'msg' => "Test email sent to $to – check inbox (and spam)."]); break;
        }
        case 'test_smtp': {
            $smtp = smtp_from_settings(); $smtp->connect(); $smtp->quit();
            echo json_encode(['ok' => true, 'msg' => 'Connected and logged in to ' . setting('smtp_host', 'smtp.gmail.com') . ' successfully.']); break;
        }
        case 'start': case 'pause': {
            $cid = (int)$in['id'];
            if ($act === 'start') {
                if (!setting('smtp_user') || !setting('smtp_pass')) throw new RuntimeException('Set up Gmail SMTP in Settings first.');
                q('UPDATE ' . t('sends') . " SET status='queued' WHERE campaign_id=? AND status='sending'", [$cid]); // recover interrupted
                q('UPDATE ' . t('campaigns') . " SET status='running', started_at=COALESCE(started_at,NOW()) WHERE id=?", [$cid]);
            } else q('UPDATE ' . t('campaigns') . " SET status='paused' WHERE id=? AND status='running'", [$cid]);
            echo json_encode(campaign_stats($cid) + ['today' => sent_today(), 'limit' => (int)setting('daily_limit', '400')]); break;
        }
        case 'send_next': {
            @set_time_limit(60);
            echo json_encode(send_next((int)$in['id'])); break;
        }
        case 'stats': echo json_encode(campaign_stats((int)$in['id']) + ['today' => sent_today(), 'limit' => (int)setting('daily_limit', '400')]); break;
        default: http_response_code(400); echo json_encode(['error' => 'Unknown action']);
    }
} catch (Throwable $ex) {
    http_response_code(200);
    echo json_encode(['error' => $ex->getMessage()]);
}
