<?php
$files = [
    'database/migrations/2026_06_05_000001_add_google_review_completed_at_to_users_table.php',
    'app/Models/User.php',
    'app/Models/Order.php',
    'app/Http/Controllers/OrderController.php',
    'app/Http/Controllers/AdminController.php',
    'app/Services/ReviewRequestService.php',
    'routes/web.php',
    'resources/views/orders/tracking.blade.php',
    'resources/views/admin/orders.blade.php',
    'resources/views/emails/review-request.blade.php',
    'PROJECT_CONTEXT.md',
];

$base = __DIR__;
$deploy = $base . '/deploy_upload';

foreach ($files as $file) {
    $src = $base . '/' . $file;
    $dst = $deploy . '/' . $file;
    if (!file_exists($src)) {
        echo "MISSING: $file\n";
        continue;
    }
    if (!is_dir(dirname($dst))) {
        mkdir(dirname($dst), 0755, true);
    }
    copy($src, $dst);
    echo "OK: $file\n";
}

echo "Done.\n";
