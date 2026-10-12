<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

function reporting_expect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: {$message}\n";
}

$db = db_connect();
$ledgerTotal = (float)$db->query("SELECT COALESCE(SUM(amount),0) total FROM payments WHERE status IN ('paid','refunded')")->fetch_assoc()['total'];
$bookingLedgerTotal = (float)$db->query("SELECT COALESCE(SUM((SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.booking_id=b.id AND p.status IN ('paid','refunded'))),0) total FROM bookings b")->fetch_assoc()['total'];
reporting_expect(abs($ledgerTotal - $bookingLedgerTotal) < 0.01, 'dashboard and booking-ledger net revenue agree');

$monthly = $db->query("SELECT DATE_FORMAT(b.created_at,'%Y-%m') month,COUNT(*) bookings,SUM(b.total_amount) booked_value,SUM(CASE WHEN EXISTS(SELECT 1 FROM payments pp WHERE pp.booking_id=b.id AND pp.status='paid') THEN 1 ELSE 0 END) paid,SUM(COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.booking_id=b.id AND p.status IN ('paid','refunded')),0)) net_revenue FROM bookings b GROUP BY month ORDER BY month DESC LIMIT 12");
reporting_expect($monthly !== false, 'monthly booked-value and net-revenue report executes');

$offers = $db->query("SELECT o.id,(SELECT COALESCE(SUM((SELECT COALESCE(SUM(pay.amount),0) FROM payments pay WHERE pay.booking_id=r.booking_id AND pay.status IN ('paid','refunded'))),0) FROM offer_redemptions r WHERE r.offer_id=o.id) net_revenue FROM offers o");
reporting_expect($offers !== false, 'offer net-revenue report executes');

foreach (["DATE_FORMAT(p.created_at,'%Y-%m')", "CONCAT(YEAR(p.created_at),' Q',QUARTER(p.created_at))", "DATE_FORMAT(p.created_at,'%Y')"] as $periodExpression) {
    $periodReport = $db->query("SELECT $periodExpression period_label,COUNT(DISTINCT p.booking_id) bookings,SUM(p.status='paid') paid_transactions,COALESCE(SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END),0) collected,COALESCE(SUM(CASE WHEN p.status='refunded' THEN -p.amount ELSE 0 END),0) refunds,COALESCE(SUM(CASE WHEN p.status IN ('paid','refunded') THEN p.amount ELSE 0 END),0) net_revenue FROM payments p WHERE p.status IN ('paid','refunded') GROUP BY period_label");
    reporting_expect($periodReport !== false, 'period-filtered financial report executes');
}

$transactionRegister = $db->query("SELECT p.id,b.booking_reference,c.approved_refund FROM payments p JOIN bookings b ON b.id=p.booking_id LEFT JOIN cancellation_requests c ON c.booking_id=b.id AND c.id=(SELECT MAX(c2.id) FROM cancellation_requests c2 WHERE c2.booking_id=b.id) ORDER BY p.created_at DESC LIMIT 15");
reporting_expect($transactionRegister !== false, 'transaction and refund register executes');

$overRefunded = (int)$db->query("SELECT COUNT(*) total FROM (SELECT booking_id,SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) paid,SUM(CASE WHEN status='refunded' THEN -amount ELSE 0 END) refunded FROM payments GROUP BY booking_id HAVING refunded>paid+0.01) invalid_refunds")->fetch_assoc()['total'];
reporting_expect($overRefunded === 0, 'completed refunds do not exceed recorded payments');

echo "\nReporting smoke test completed successfully. Net revenue: {$ledgerTotal}\n";
