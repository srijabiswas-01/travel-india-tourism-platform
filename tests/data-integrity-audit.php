<?php
declare(strict_types=1);

if (!defined('TRAVEL_INDIA_ALLOW_SCHEMA_MIGRATION')) define('TRAVEL_INDIA_ALLOW_SCHEMA_MIGRATION', true);
require_once __DIR__ . '/../db.php';

$db = db_connect();
ensure_app_schema($db);
$checks = [
    'duplicate booking references' => "SELECT COUNT(1) total FROM (SELECT booking_reference FROM bookings GROUP BY booking_reference HAVING COUNT(1)>1) duplicates",
    'orphaned booking travellers' => 'SELECT COUNT(1) total FROM booking_travellers bt LEFT JOIN bookings b ON b.id=bt.booking_id WHERE b.id IS NULL',
    'orphaned booking histories' => 'SELECT COUNT(1) total FROM booking_status_history h LEFT JOIN bookings b ON b.id=h.booking_id WHERE b.id IS NULL',
    'orphaned payments' => 'SELECT COUNT(1) total FROM payments p LEFT JOIN bookings b ON b.id=p.booking_id WHERE b.id IS NULL',
    'orphaned offer reservations' => 'SELECT COUNT(1) total FROM offer_redemptions r LEFT JOIN bookings b ON b.id=r.booking_id WHERE b.id IS NULL',
    'offer reservations on unpaid bookings' => "SELECT COUNT(1) total FROM offer_redemptions r JOIN bookings b ON b.id=r.booking_id WHERE b.payment_status NOT IN ('paid','refund_pending','refunded')",
    'orphaned amendment requests' => 'SELECT COUNT(1) total FROM booking_amendments r LEFT JOIN bookings b ON b.id=r.booking_id WHERE b.id IS NULL',
    'orphaned cancellation requests' => 'SELECT COUNT(1) total FROM cancellation_requests r LEFT JOIN bookings b ON b.id=r.booking_id WHERE b.id IS NULL',
    'booking traveller-count mismatches' => "SELECT COUNT(1) total FROM (SELECT b.id FROM bookings b LEFT JOIN booking_travellers t ON t.booking_id=b.id GROUP BY b.id HAVING MAX(b.people)<>COUNT(t.id)) mismatches",
    'paid bookings without paid ledger entry' => "SELECT COUNT(1) total FROM bookings b WHERE b.payment_status='paid' AND NOT EXISTS(SELECT 1 FROM payments p WHERE p.booking_id=b.id AND p.status='paid')",
    'refund-pending bookings without approved cancellation' => "SELECT COUNT(1) total FROM bookings b WHERE b.payment_status='refund_pending' AND NOT EXISTS(SELECT 1 FROM cancellation_requests c WHERE c.booking_id=b.id AND c.status='refund_pending' AND c.approved_refund>0)",
    'refunded bookings without refund ledger entry' => "SELECT COUNT(1) total FROM bookings b WHERE b.payment_status='refunded' AND NOT EXISTS(SELECT 1 FROM payments p WHERE p.booking_id=b.id AND p.status='refunded' AND p.amount<0)",
    'cancellation requests on unpaid bookings' => "SELECT COUNT(1) total FROM cancellation_requests c JOIN bookings b ON b.id=c.booking_id WHERE b.payment_status NOT IN ('paid','refund_pending','refunded')",
    'paid rejected bookings' => "SELECT COUNT(1) total FROM bookings WHERE status='rejected' AND payment_status IN ('paid','refund_pending','refunded')",
    'paid cancellations without cancellation approval' => "SELECT COUNT(1) total FROM bookings b WHERE b.status='cancelled' AND b.payment_status IN ('paid','refund_pending','refunded') AND NOT EXISTS(SELECT 1 FROM cancellation_requests c WHERE c.booking_id=b.id AND c.status IN ('refund_pending','approved','refunded'))",
    'booking amount arithmetic mismatches' => "SELECT COUNT(1) total FROM bookings WHERE ABS(total_amount - GREATEST(0,base_amount-discount_amount)) > 0.01",
    'orphaned booking reviews' => 'SELECT COUNT(1) total FROM booking_reviews r LEFT JOIN bookings b ON b.id=r.booking_id WHERE b.id IS NULL',
    'duplicate booking reviews' => 'SELECT COUNT(1) total FROM (SELECT booking_id FROM booking_reviews GROUP BY booking_id HAVING COUNT(1)>1) duplicates',
    'reviews for incomplete bookings' => "SELECT COUNT(1) total FROM booking_reviews r JOIN bookings b ON b.id=r.booking_id WHERE b.status<>'completed'",
    'review rating range violations' => 'SELECT COUNT(1) total FROM booking_reviews WHERE package_rating NOT BETWEEN 1 AND 5 OR hotel_rating NOT BETWEEN 1 AND 5',
    'orphaned review packages' => 'SELECT COUNT(1) total FROM booking_reviews r LEFT JOIN packages p ON p.id=r.package_id WHERE p.id IS NULL',
    'orphaned review hotels' => 'SELECT COUNT(1) total FROM booking_reviews r LEFT JOIN hotels h ON h.id=r.hotel_id WHERE r.hotel_id IS NOT NULL AND h.id IS NULL',
    'unfinished transactional test users' => "SELECT COUNT(1) total FROM users WHERE email LIKE 'workflow-test-%@invalid.local'",
];

$failures = 0;
foreach ($checks as $name=>$sql) {
    $result = $db->query($sql);
    if (!$result) throw new RuntimeException("Integrity query failed for {$name}: {$db->error}");
    $count = (int)$result->fetch_assoc()['total'];
    echo ($count===0 ? 'PASS' : 'FAIL') . ": {$name} = {$count}\n";
    if ($count !== 0) $failures++;
}

$policyCount = (int)$db->query("SELECT COUNT(1) total FROM cancellation_policies WHERE status='active'")->fetch_assoc()['total'];
echo ($policyCount>0 ? 'PASS' : 'FAIL') . ": active cancellation policies = {$policyCount}\n";
if ($policyCount===0) $failures++;

if ($failures) {
    fwrite(STDERR,"\nData integrity audit found {$failures} issue(s).\n");
    exit(1);
}
echo "\nData integrity audit completed successfully.\n";
