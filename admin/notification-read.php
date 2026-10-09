<?php
require_once '_bootstrap.php';
require_once __DIR__.'/../notifications.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
verify_csrf();
$return=basename((string)($_POST['return']??'index.php'));
$allowedPages=['index.php','bookings.php','booking-details.php','inbox.php','messages.php','notifications.php','customers.php','reports.php'];
if(!in_array($return,$allowedPages,true))$return='index.php';
try{$db=admin_db();mark_admin_notification_read($db,(int)($_POST['id']??0),(string)$_SESSION['admin_email']);}catch(Throwable $error){}
header('Location: '.$return);exit;