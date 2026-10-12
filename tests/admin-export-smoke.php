<?php
session_start();
$_SESSION['admin_id']=1;$_SESSION['admin_email']='export-test@invalid.local';$_SESSION['admin_name']='Export Test';$_SESSION['admin_role']='manager';
$_SERVER['PHP_SELF']='/admin/reports.php';$_SERVER['REQUEST_METHOD']='GET';
$_GET=['period'=>$argv[1]??'monthly','year'=>$argv[2]??'','export'=>'csv'];
chdir(__DIR__.'/../admin');require 'reports.php';
