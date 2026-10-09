<?php
define('TRAVEL_INDIA_ALLOW_SCHEMA_MIGRATION', true);
require_once __DIR__ . '/../db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    $database = db_connect();
    ensure_app_schema($database);
    echo "Travel India schema is ready.\n\nTables:\n";
    $tables = $database->query('SHOW TABLES');
    while ($table = $tables->fetch_row()) echo '- ' . $table[0] . "\n";
    $stateCount = (int)$database->query("SELECT COUNT(*) total FROM states WHERE status='active'")->fetch_assoc()['total'];
    $destinationCount = (int)$database->query("SELECT COUNT(*) total FROM destinations WHERE status='active'")->fetch_assoc()['total'];
    $indiaPackages = (int)$database->query("SELECT COUNT(*) total FROM packages WHERE status='published' AND state_id IS NOT NULL AND currency='INR'")->fetch_assoc()['total'];
    $outsidePackages = (int)$database->query("SELECT COUNT(*) total FROM packages WHERE status='published' AND state_id IS NULL")->fetch_assoc()['total'];
    echo "\nCatalog:\n- Active states and union territories: $stateCount\n- Active destinations: $destinationCount\n- Published INR packages: $indiaPackages\n- Published packages outside the India catalog: $outsidePackages\n";
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
    exit(1);
}
