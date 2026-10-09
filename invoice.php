<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'booking-pricing.php';
if(!customer_is_logged_in())redirect_with_message('reglog.php','Please sign in to view your invoice.');
$bookingId=filter_input(INPUT_GET,'booking_id',FILTER_VALIDATE_INT)?:0;
$booking=null;$travellers=[];$payment=null;
try{
    $db=db_connect();
    $statement=$db->prepare("SELECT b.*,p.name package_name,p.description package_description FROM bookings b LEFT JOIN packages p ON p.id=b.package_id WHERE b.id=? AND b.user_email=? AND b.payment_status='paid'");
    if(!$statement)throw new RuntimeException('Invoice is unavailable.');
    $email=$_SESSION['user_email'];
    $statement->bind_param('is',$bookingId,$email);
    $statement->execute();
    $booking=$statement->get_result()->fetch_assoc();
    if($booking){
        $statement=$db->prepare('SELECT name,relationship,age_category,is_pwd FROM booking_travellers WHERE booking_id=? ORDER BY id');
        $statement->bind_param('i',$bookingId);
        $statement->execute();
        $result=$statement->get_result();
        while($row=$result->fetch_assoc())$travellers[]=$row;
        $statement=$db->prepare("SELECT method,transaction_reference,created_at FROM payments WHERE booking_id=? AND status='paid' ORDER BY id DESC LIMIT 1");
        $statement->bind_param('i',$bookingId);
        $statement->execute();
        $payment=$statement->get_result()->fetch_assoc();
    }
}catch(Throwable $error){$booking=null;}
if(!$booking)redirect_with_message('my-bookings.php','A paid invoice is not available for that booking.');
$travellerCount=max(1,(int)$booking['people']);
$hotelUpgrade=max(0,(float)($booking['hotel_surcharge_total']??0));
$packageSubtotal=max(0,(float)($booking['base_amount']??0)-$hotelUpgrade);
$unitPrice=(float)($booking['package_unit_price']??0);
if($unitPrice<=0)$unitPrice=$packageSubtotal/$travellerCount;
$priceRows=booking_price_rows_from_travellers($travellers,$unitPrice,$packageSubtotal,$travellerCount);
$discount=max(0,(float)($booking['discount_amount']??0));
$duration=max(1,(new DateTimeImmutable($booking['from_date']))->diff(new DateTimeImmutable($booking['to_date']))->days+1);
$invoiceDate=$payment['created_at']??$booking['updated_at']??$booking['created_at'];
$pageTitle='Booking Invoice';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo e($pageTitle);?> | Travel India</title><link rel="icon" href="img/favicon.jpg?v=1"><link rel="stylesheet" href="css/portal.css?v=12"></head><body class="invoice-body"><?php include 'header.php';?>
<main class="portal-page invoice-page"><div class="invoice-actions"><a class="portal-button" href="my-bookings.php#booking-<?php echo (int)$booking['id'];?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to My Trips</a><button class="portal-button" type="button" onclick="window.print()"><i class="fa-solid fa-download" aria-hidden="true"></i> Download / print invoice</button></div>
<article class="invoice-paper"><header class="invoice-heading"><div><span>Travel India</span><h1>Booking invoice</h1><p>Paid travel booking receipt</p></div><div><b>Invoice reference</b><strong><?php echo e('TI-'.$booking['booking_reference']);?></strong><small>Issued <?php echo e(date('d M Y',strtotime($invoiceDate)));?></small></div></header>
<section class="invoice-meta"><div><span>Customer</span><strong><?php echo e($booking['customer_name']);?></strong><small><?php echo e($booking['user_email']);?></small></div><div><span>Booking reference</span><strong><?php echo e($booking['booking_reference']?:'#'.$booking['id']);?></strong><small><?php echo e(ucwords(str_replace('_',' ',$booking['status'])));?></small></div></section>
<section class="invoice-trip"><div class="invoice-section-title"><h2><?php echo e($booking['package_name']?:$booking['destination']);?></h2><span><?php echo e($booking['destination']);?></span></div><div class="invoice-trip-facts"><span><b>Start date</b><?php echo e(date('d M Y',strtotime($booking['from_date'])));?></span><span><b>End date</b><?php echo e(date('d M Y',strtotime($booking['to_date'])));?></span><span><b>Duration</b><?php echo $duration;?> days</span><span><b>Hotel</b><?php echo e($booking['hotel']);?></span><span><b>Travellers</b><?php echo (int)$booking['people'];?></span></div><?php if($booking['package_description']):?><p class="invoice-description"><?php echo nl2br(e($booking['package_description']));?></p><?php endif;?></section>
<section class="invoice-charges"><h2>Charges</h2><?php echo render_booking_price_rows($priceRows,'invoice-line');?><?php if($hotelUpgrade>0):?><div class="invoice-line"><span><strong>Hotel upgrade</strong><small><?php echo inr($booking['hotel_surcharge_per_traveller']);?> × <?php echo (int)$booking['people'];?> traveller<?php echo (int)$booking['people']===1?'':'s';?></small></span><strong><?php echo inr($hotelUpgrade);?></strong></div><?php endif;?><?php if($discount>0):?><div class="invoice-line invoice-discount"><span><strong><?php echo e($booking['offer_name']?:($booking['offer_code']?:'Offer discount'));?></strong><small>Promotional discount</small></span><strong>-<?php echo inr($discount);?></strong></div><?php endif;?><div class="invoice-total"><strong>Total paid</strong><strong><?php echo inr($booking['total_amount']);?></strong></div></section>
<section class="invoice-payment"><h2>Payment</h2><div><span><b>Status</b>Paid</span><span><b>Method</b><?php echo e($payment?strtoupper(preg_replace('/^(demo_|practice_)/','',$payment['method'])):'Recorded by Travel India');?></span><span><b>Transaction reference</b><?php echo e(isset($payment['transaction_reference'])?preg_replace('/^(?:DEMO|PRACTICE)-/','TI-PAY-',$payment['transaction_reference']):'Admin recorded payment');?></span></div><?php if($payment&&(str_starts_with($payment['method'],'demo_')||str_starts_with($payment['method'],'practice_'))):?><p>No real funds were transferred for this payment.</p><?php endif;?></section>
<?php if($travellers):?><section class="invoice-travellers"><h2>Travellers</h2><ol><?php foreach($travellers as $traveller):?><li><?php echo e($traveller['name']);?><?php if($traveller['relationship']):?> <span>(<?php echo e($traveller['relationship']);?>)</span><?php endif;?><?php if($traveller['age_category']):?> <small><?php echo e(ucfirst($traveller['age_category']));?></small><?php endif;?><?php if(!empty($traveller['is_pwd'])):?> <small>PWD assistance noted</small><?php endif;?></li><?php endforeach;?></ol></section><?php endif;?></article></main><?php include 'footer.php';?></body></html>
