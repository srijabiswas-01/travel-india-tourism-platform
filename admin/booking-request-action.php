<?php
require_once '_bootstrap.php';
require_once __DIR__.'/../booking-workflow.php';
require_once __DIR__.'/../notifications.php';
require_admin_permission('bookings.manage');
if($_SERVER['REQUEST_METHOD']!=='POST')redirect_with_message('bookings.php','Use the booking review controls.');
verify_csrf();
$type=$_POST['request_type']??'';$requestId=(int)($_POST['request_id']??0);$action=$_POST['decision']??'';$response=trim($_POST['admin_response']??'');
if(!in_array($type,['amendment','cancellation'],true)||!in_array($action,['approve','reject'],true))redirect_with_message('bookings.php','Invalid request action.');
if($action==='reject'&&$response==='')redirect_with_message('bookings.php','A rejection reason is required.');
try{
    $db=admin_db();$db->begin_transaction();$table=$type==='amendment'?'booking_amendments':'cancellation_requests';
    $request=$db->execute_query("SELECT r.*,b.user_email,b.booking_reference,b.status booking_status,b.payment_status,b.total_amount FROM `$table` r JOIN bookings b ON b.id=r.booking_id WHERE r.id=? AND r.status IN ('requested','under_review') FOR UPDATE",[$requestId])->fetch_assoc();
    if(!$request)throw new RuntimeException('This request has already been reviewed.');
    $bookingId=(int)$request['booking_id'];$reference=$request['booking_reference']?:'#'.$bookingId;
    if($action==='reject'){
        $db->execute_query("UPDATE `$table` SET status='rejected',admin_response=?,reviewed_at=NOW(),reviewed_by=? WHERE id=?",[$response,(int)$_SESSION['admin_id'],$requestId]);
        $title=$type==='amendment'?'Date-change request declined':'Cancellation request declined';
        $message=$title.' for booking '.$reference.'. '.$response;
    }elseif($type==='amendment'){
        $travellers=$db->execute_query('SELECT name,date_of_birth,family_member_id,traveller_type FROM booking_travellers WHERE booking_id=?',[$bookingId])->fetch_all(MYSQLI_ASSOC);
        $includeSelf=false;$family=[];$others=[];
        foreach($travellers as $traveller){if($traveller['traveller_type']==='primary')$includeSelf=true;elseif($traveller['traveller_type']==='family')$family[]=(int)$traveller['family_member_id'];else$others[]=['name'=>$traveller['name'],'date_of_birth'=>$traveller['date_of_birth']];}
        $conflicts=booking_overlap_conflicts($db,$request['user_email'],$request['requested_from_date'],$request['requested_to_date'],$includeSelf,$family,$others,$bookingId);
        if(array_filter($conflicts,fn($c)=>$c['severity']==='hard'))throw new RuntimeException('Approval blocked because the requested dates now overlap another confirmed trip.');
        $db->execute_query('UPDATE bookings SET from_date=?,to_date=?,admin_note=? WHERE id=?',[$request['requested_from_date'],$request['requested_to_date'],$response?:'Date change approved.',$bookingId]);
        $db->execute_query("UPDATE booking_amendments SET status='approved',admin_response=?,reviewed_at=NOW(),reviewed_by=? WHERE id=?",[$response,(int)$_SESSION['admin_id'],$requestId]);
        $db->execute_query('INSERT INTO booking_status_history(booking_id,old_status,new_status,note,changed_by) VALUES(?,?,?,?,?)',[$bookingId,$request['booking_status'],$request['booking_status'],'Date change approved: '.$request['old_from_date'].' to '.$request['old_to_date'].' changed to '.$request['requested_from_date'].' to '.$request['requested_to_date'].($response?' - '.$response:''),$_SESSION['admin_email']]);
        $title='Date change approved';$message='Booking '.$reference.' now travels from '.$request['requested_from_date'].' to '.$request['requested_to_date'].'.'.($response?' '.$response:'');
    }else{
        $approved=max(0,min((float)$request['paid_amount'],(float)($_POST['approved_refund']??$request['estimated_refund'])));
        $needsRefund=$request['payment_status']==='paid'&&$approved>0;
        $nextRequestStatus=$needsRefund?'refund_pending':'approved';
        $nextPaymentStatus=$needsRefund?'refund_pending':$request['payment_status'];
        $db->execute_query("UPDATE cancellation_requests SET status=?,approved_refund=?,admin_response=?,reviewed_at=NOW(),reviewed_by=? WHERE id=?",[$nextRequestStatus,$approved,$response,(int)$_SESSION['admin_id'],$requestId]);
        $db->execute_query("UPDATE bookings SET status='cancelled',payment_status=?,admin_note=? WHERE id=?",[$nextPaymentStatus,$response?:($needsRefund?'Cancellation approved. Refund is pending.':'Cancellation approved.'),$bookingId]);
        $db->execute_query('INSERT INTO booking_status_history(booking_id,old_status,new_status,note,changed_by) VALUES(?,?,?,?,?)',[$bookingId,$request['booking_status'],'cancelled','Cancellation approved'.($approved>0?'; approved refund '.inr($approved):'').($response?' - '.$response:''),$_SESSION['admin_email']]);
        $title='Cancellation approved';$message='Booking '.$reference.' has been cancelled.'.($needsRefund?' Approved refund: '.inr($approved).'. Refund is pending.':($approved>0?' Approved refund: '.inr($approved).'.':'')).($response?' '.$response:'');
    }
    create_notification($db,$title,$message,'user',$request['user_email'],'my-bookings.php#booking-'.$bookingId,$action==='approve'?'success':'warning',(int)$_SESSION['admin_id']);
    admin_audit($db,$action,$type.'_request',(string)$requestId,$reference);$db->commit();redirect_with_message('booking-details.php?id='.$bookingId,$title.'.');
}catch(Throwable $error){
    if(isset($db)){try{$db->rollback();}catch(Throwable $ignored){}}
    redirect_with_message('booking-details.php?id='.($request['booking_id']??0),$error->getMessage());
}
