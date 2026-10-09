<?php
	require_once 'app_bootstrap.php';
	session_destroy();
	header("location:reglog.php");
	exit;
?>
