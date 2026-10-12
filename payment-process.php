<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'notifications.php';
if(!customer_is_logged_in())redirect_with_message('reglog.php','Please sign in to continue.');
if($_SERVER['REQUEST_METHOD']!=='POST')redirect_with_message('my-bookings.php','Open a confirmed booking to start checkout.');
verify_csrf();
$bookingId=filter_input(INPUT_POST,'booking_id',FILTER_VALIDATE_INT)?:0;
$method=$_POST['method']??'';
$outcome=$_POST['outcome']??'';
$methods=['practice_upi','practice_card','practice_netbanking'];
$outcomes=['success','failure','cancel'];
if(!$bookingId||!in_array($method,$methods,true)||!in_array($outcome,$outcomes,true))redirect_with_message('my-bookings.php','Select a valid payment method and result.');
$email=$_SESSION['user_email'];
try{
    $db=db_connect();
    $db->begin_transaction();
    $statement=$db->prepare('SELECT booking_reference,status,payment_status,total_amount,base_amount,hotel_surcharge_total,discount_amount,offer_id FROM bookings WHERE id=? AND user_email=? FOR UPDATE');
    if(!$statement)throw new RuntimeException('Checkout is temporarily unavailable.');
    $statement->bind_param('is',$bookingId,$email);
    $statement->execute();
    $booking=$statement->get_result()->fetch_assoc();
    if(!$booking||$booking['status']!=='confirmed')throw new RuntimeException('Only your confirmed bookings can be paid.');
    if(in_array($booking['payment_status'],['paid','refund_pending','refunded'],true))throw new RuntimeException('This booking is already settled.');

        $reference='TI-PAY-'.date('ymdHis').'-'.strtoupper(bin2hex(random_bytes(3)));
    $attemptStatus=['success'=>'paid','failure'=>'failed','cancel'=>'cancelled'][$outcome];
    $amount=(float)$booking['total_amount'];
    $insert=$db->prepare('INSERT INTO payments(booking_id,amount,method,transaction_reference,status) VALUES(?,?,?,?,?)');
    if(!$insert)throw new RuntimeException('Checkout is temporarily unavailable.');
    $insert->bind_param('idsss',$bookingId,$amount,$method,$reference,$attemptStatus);
    $insert->execute();

    if($outcome==='success'){
        $update=$db->prepare("UPDATE bookings SET payment_status='paid',admin_note=CASE WHEN admin_note IS NOT NULL AND LOWER(admin_note) REGEXP 'pay|payment|amount' THEN 'Payment received in full.' ELSE admin_note END WHERE id=? AND user_email=? AND status='confirmed' AND payment_status NOT IN ('paid','refund_pending','refunded')");
        if(!$update)throw new RuntimeException('Payment could not be recorded.');
        $update->bind_param('is',$bookingId,$email);
        $update->execute();
        if($update->affected_rows!==1)throw new RuntimeException('Payment could not be recorded.');
        $historyNote='Payment '.$reference.' succeeded; booking remains confirmed until travel starts.';$changedBy='payment:'.$reference;$history=$db->prepare('INSERT INTO booking_status_history(booking_id,old_status,new_status,note,changed_by) VALUES(?,?,?,?,?)');$oldStatus='confirmed';$newStatus='confirmed';$history->bind_param('issss',$bookingId,$oldStatus,$newStatus,$historyNote,$changedBy);$history->execute();
        if(!empty($booking['offer_id'])&&(float)$booking['discount_amount']>0){$packageBase=max(0,(float)$booking['base_amount']-(float)$booking['hotel_surcharge_total']);$db->execute_query('INSERT IGNORE INTO offer_redemptions(offer_id,booking_id,user_email,base_amount,discount_amount,final_amount) VALUES(?,?,?,?,?,?)',[(int)$booking['offer_id'],$bookingId,$email,$packageBase,(float)$booking['discount_amount'],(float)$booking['total_amount']]);}
        $title='Payment recorded';
        $message='Payment reference '.$reference.' was marked successful for booking '.($booking['booking_reference']?:'#'.$bookingId).'. No money was charged.';
        $type='success';
        $flash='Payment succeeded. No money was charged.';
    }elseif($outcome==='failure'){
        $title='Payment failed';
        $message='Payment attempt '.$reference.' failed. No money was charged; you can retry.';
        $type='warning';
        $flash='Payment failed. No money was charged; you can retry.';
    }else{
        $title='Payment cancelled';
        $message='Payment attempt '.$reference.' was cancelled. No money was charged.';
        $type='info';
        $flash='Payment cancelled. No money was charged.';
    }
    create_notification($db,$title,$message,'user',$email,'payment.php?booking_id='.$bookingId,$type);
    $db->commit();
    redirect_with_message('payment.php?booking_id='.$bookingId,$flash);
}catch(Throwable $error){
    if(isset($db)&&$db instanceof mysqli){try{$db->rollback();}catch(Throwable $ignored){}}
    redirect_with_message('payment.php?booking_id='.$bookingId,'Payment could not be completed. Please retry.');
}
