<?php
function page_head(string $title, string $active = ''): void {
    $nav = ['index.php' => ['layout-dashboard', 'Dashboard'], 'contacts.php' => ['users', 'Contacts'], 'templates.php' => ['mail', 'Templates'],
            'campaigns.php' => ['send', 'Campaigns'], 'unsubscribes.php' => ['user-x', 'Unsubscribes'], 'settings.php' => ['settings', 'Settings']];
    $icons = ['layout-dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'user-x' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5"/><path d="m22 8-5 5"/>',
        'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>'];
    $svg = fn($n) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icons[$n] . '</svg>';
    ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title><?= e($title) ?> · DuoBytes Admin</title>
<link rel="icon" href="../assets/company-mark.png"><link rel="stylesheet" href="assets/admin.css">
<meta name="csrf" content="<?= csrf_token() ?>"></head><body>
<aside class="side" id="side"><a href="index.php" class="logo"><img src="../assets/company-full.png" alt="DuoBytes"></a>
<nav><?php foreach ($nav as $href => [$ic, $label]): ?><a href="<?= $href ?>" class="<?= $active === $href ? 'on' : '' ?>"><?= $svg($ic) ?><?= $label ?></a><?php endforeach; ?></nav>
<div class="me"><span><?= e(current_user()) ?></span><a href="logout.php">Log out</a></div></aside>
<main class="main"><header class="top"><button class="burger" onclick="document.getElementById('side').classList.toggle('open')">☰</button><h1><?= e($title) ?></h1>
<span class="quota">Sent today: <b><?= sent_today() ?></b> / <?= e(setting('daily_limit', '400')) ?></span></header>
<?php foreach (flash() as [$type, $msg]): ?><div class="alert <?= e($type) ?>"><?= e($msg) ?></div><?php endforeach;
}
function page_foot(): void { ?></main><script src="assets/admin.js"></script></body></html><?php }

function auth_page(string $title, callable $body): void { ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> · DuoBytes Admin</title><link rel="icon" href="../assets/company-mark.png"><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth"><div class="auth-box"><img src="../assets/company-full.png" alt="DuoBytes" class="auth-logo"><h1><?= e($title) ?></h1>
<?php foreach (flash() as [$type, $msg]): ?><div class="alert <?= e($type) ?>"><?= e($msg) ?></div><?php endforeach; $body(); ?>
</div></body></html><?php }

function badge(string $s): string {
    $c = ['draft' => 'grey', 'running' => 'blue', 'paused' => 'amber', 'completed' => 'green', 'sent' => 'green', 'failed' => 'red',
          'queued' => 'grey', 'sending' => 'blue', 'skipped' => 'amber'][$s] ?? 'grey';
    return '<span class="badge ' . $c . '">' . e(ucfirst($s)) . '</span>';
}
