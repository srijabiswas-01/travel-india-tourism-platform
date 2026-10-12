<?php
declare(strict_types=1);

$requestedPage = $argv[1] ?? '';
if (!in_array($requestedPage, ['index.php','transactions.php','reports.php'], true)) {
    fwrite(STDERR, "Choose index.php, transactions.php, or reports.php.\n");
    exit(2);
}

session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_email'] = 'render-test@invalid.local';
$_SESSION['admin_name'] = 'Render Test';
$_SESSION['admin_role'] = 'manager';
$_SERVER['PHP_SELF'] = '/admin/' . $requestedPage;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
chdir(__DIR__ . '/../admin');
ob_start();
require $requestedPage;
$html = ob_get_clean();
if (!str_contains($html, '</html>')) throw new RuntimeException("{$requestedPage} did not render a complete document.");
echo "PASS: {$requestedPage} rendered " . strlen($html) . " bytes\n";
