<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
if(!customer_is_logged_in())redirect_with_message('reglog.php','Please sign in to manage your booking.');
if($_SERVER['REQUEST_METHOD']!=='POST')redirect_with_message('my-bookings.php','Please use the booking actions.');
verify_csrf();
$id=filter_input(INPUT_POST,'booking_id',FILTER_VALIDATE_INT)?:0;
try{
    $db=db_connect();ensure_app_schema($db);$db->begin_transaction();
    $booking=$db->execute_query("SELECT b.id,b.booking_reference,b.status,b.payment_status,(SELECT status FROM cancellation_requests WHERE booking_id=b.id ORDER BY id DESC LIMIT 1) cancellation_status,(SELECT approved_refund FROM cancellation_requests WHERE booking_id=b.id ORDER BY id DESC LIMIT 1) approved_refund FROM bookings b WHERE b.id=? AND b.user_email=? FOR UPDATE",[$id,$_SESSION['user_email']])->fetch_assoc();
    if(!$booking)throw new RuntimeException('Booking not found.');
    $settledCancellation=$booking['status']==='cancelled'&&(in_array($booking['payment_status'],['unpaid','refunded'],true)||($booking['payment_status']==='paid'&&$booking['cancellation_status']==='approved'&&(float)$booking['approved_refund']<=0));
    $removableRejection=$booking['status']==='rejected'&&$booking['payment_status']==='unpaid';
    if(!$settledCancellation&&!$removableRejection)throw new RuntimeException('This booking must remain visible until all payment or refund activity is complete.');
    $db->execute_query('UPDATE bookings SET customer_hidden_at=NOW() WHERE id=?',[$id]);$db->commit();
    redirect_with_message('my-bookings.php','Booking '.($booking['booking_reference']?:'#'.$id).' was deleted from My Trips. Travel India retains its administrative history.');
}catch(Throwable $error){if(isset($db)){try{$db->rollback();}catch(Throwable $ignored){}}redirect_with_message('my-bookings.php',$error->getMessage());}
