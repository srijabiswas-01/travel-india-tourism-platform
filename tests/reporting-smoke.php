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

$overRefunded = (int)$db->query("SELECT COUNT(*) total FROM (SELECT booking_id,SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) paid,SUM(CASE WHEN status='refunded' THEN -amount ELSE 0 END) refunded FROM payments GROUP BY booking_id HAVING refunded>paid+0.01) invalid_refunds")->fetch_assoc()['total'];
reporting_expect($overRefunded === 0, 'completed refunds do not exceed recorded payments');

echo "\nReporting smoke test completed successfully. Net revenue: {$ledgerTotal}\n";
