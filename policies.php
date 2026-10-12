<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
$db=db_connect();
ensure_app_schema($db);
$result=$db->query("SELECT name,minimum_days_before,refund_percentage,administrative_fee,effective_from,effective_to FROM cancellation_policies WHERE status='active' AND effective_from<=CURDATE() AND (effective_to IS NULL OR effective_to>=CURDATE()) ORDER BY minimum_days_before DESC,id");
$policies=[];while($row=$result->fetch_assoc())$policies[]=$row;
$pageTitle='Cancellation & Refund Policy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cancellation &amp; Refund Policy | Travel India</title>
<link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
<link rel="stylesheet" href="css/policies.css?v=1">
<link rel="stylesheet" href="css/policies-theme.css?v=1">
</head>
<body>
<?php include 'header.php';?>
<main class="policy-page">
<header class="policy-hero"><div><span>Booking support</span><h1>Cancellation &amp; Refund Policy</h1><p>Review the currently published refund schedule before sending a cancellation request.</p></div></header>
<section class="policy-content" aria-labelledby="refund-schedule-title">
<div class="policy-intro"><div><span>Current policy</span><h2 id="refund-schedule-title">Refund schedule</h2></div><p>The applicable row is based on the number of full days between the request date and your departure date.</p></div>
<?php if($policies):?><div class="policy-table-wrap"><table><thead><tr><th>Cancellation timing</th><th>Refund</th><th>Administrative fee</th><th>Effective period</th></tr></thead><tbody><?php foreach($policies as $policy):?><tr><td><strong><?php echo e($policy['name']);?></strong><small><?php echo (int)$policy['minimum_days_before'];?> or more days before departure</small></td><td><?php echo e(number_format((float)$policy['refund_percentage'],2));?>%</td><td><?php echo (float)$policy['administrative_fee']>0?inr($policy['administrative_fee']):'No fee';?></td><td><?php echo e(date('d M Y',strtotime($policy['effective_from'])));?><?php echo $policy['effective_to']?' to '.e(date('d M Y',strtotime($policy['effective_to']))):' onward';?></td></tr><?php endforeach;?></tbody></table></div>
<?php else:?><div class="policy-empty"><i class="fa-solid fa-circle-info"></i><div><h2>No cancellation schedule is currently published</h2><p>Contact Travel India before booking or requesting cancellation so the applicable terms can be confirmed.</p></div></div><?php endif;?>
<div class="policy-rules"><section><h2>How refunds are handled</h2><ul><li>The displayed percentage is applied to the amount actually paid, not the original package value.</li><li>Any published administrative fee is deducted after the percentage calculation.</li><li>The estimated refund can never exceed the paid amount or fall below zero.</li><li>The administrator reviews every request and confirms the final refund and status.</li></ul></section><section><h2>Important conditions</h2><ul><li>Submitting a request does not immediately cancel the booking.</li><li>Non-refundable supplier, ticket, hotel, or payment charges may affect the final approved amount.</li><li>Date-change requests are handled separately from cancellations.</li><li>Your booking and notification areas show the request decision and refund progress.</li></ul></section></div>
<div class="policy-actions"><a href="my-bookings.php" class="policy-button"><i class="fa-solid fa-suitcase-rolling"></i> View my trips</a><a href="contactus.php" class="policy-button secondary"><i class="fa-solid fa-envelope"></i> Contact support</a></div>
</section></main>
<?php include 'footer.php';?>
</body>
</html>
