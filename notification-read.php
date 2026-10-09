<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'notifications.php';
if(!customer_is_logged_in()){header('Location: reglog.php');exit;}
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: main.php');exit;}
verify_csrf();
$return=notification_link($_POST['return']??null);
try{
    $db=db_connect();$email=$_SESSION['user_email'];
    if(($_POST['action']??'')==='all')mark_notification_read($db,0,$email);
    else mark_notification_read($db,(int)($_POST['id']??0),$email);
}catch(Throwable $error){}
header('Location: '.$return);exit;
