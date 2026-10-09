<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';

if (!customer_is_logged_in()) redirect_with_message('reglog.php', 'Please sign in to manage your booking.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_with_message('my-bookings.php', 'Please use the booking actions.');
verify_csrf();

$id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
try {
    $db = db_connect();
    ensure_app_schema($db);
    $db->begin_transaction();
    $booking = $db->execute_query('SELECT id,booking_reference,status,payment_status,user_email FROM bookings WHERE id=? AND user_email=? FOR UPDATE', [$id, $_SESSION['user_email']])->fetch_assoc();
    if (!$booking) throw new RuntimeException('Booking not found.');
    if (in_array($booking['payment_status'], ['paid','refund_pending','refunded'], true)) throw new RuntimeException('Paid bookings must use the cancellation and refund request flow.');
    if (!in_array($booking['status'], ['pending','needs_information','confirmed','rejected'], true)) throw new RuntimeException('This booking can no longer be deleted directly.');

    $db->execute_query('DELETE FROM offer_redemptions WHERE booking_id=?', [$id]);
    $db->execute_query('DELETE FROM booking_amendments WHERE booking_id=?', [$id]);
    $db->execute_query('DELETE FROM cancellation_requests WHERE booking_id=?', [$id]);
    $db->execute_query('DELETE FROM booking_status_history WHERE booking_id=?', [$id]);
    $db->execute_query('DELETE FROM booking_travellers WHERE booking_id=?', [$id]);
    $db->execute_query("DELETE FROM payments WHERE booking_id=? AND status IN ('pending','failed','cancelled')", [$id]);
    $db->execute_query('DELETE FROM bookings WHERE id=? AND user_email=?', [$id, $_SESSION['user_email']]);
    $db->commit();
    redirect_with_message('my-bookings.php', 'Booking ' . ($booking['booking_reference'] ?: '#'.$id) . ' was deleted.');
} catch (Throwable $error) {
    if (isset($db)) { try { $db->rollback(); } catch (Throwable $ignored) {} }
    redirect_with_message('my-bookings.php', $error->getMessage());
}
