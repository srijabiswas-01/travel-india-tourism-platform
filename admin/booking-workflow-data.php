<?php
require_once '_bootstrap.php';
require_admin_permission('bookings.manage');
header('Content-Type: application/json; charset=utf-8');
$db=admin_db();
$result=$db->query("SELECT b.id,b.from_date,b.to_date,b.status,b.payment_status,(SELECT status FROM booking_amendments ba WHERE ba.booking_id=b.id ORDER BY ba.id DESC LIMIT 1) amendment_status,(SELECT status FROM cancellation_requests cr WHERE cr.booking_id=b.id ORDER BY cr.id DESC LIMIT 1) cancellation_status FROM bookings b ORDER BY b.id DESC");
$items=[];
while($row=$result->fetch_assoc())$items[(string)$row['id']]=$row;
echo json_encode(['bookings'=>$items,'today'=>date('Y-m-d')],JSON_UNESCAPED_SLASHES);
