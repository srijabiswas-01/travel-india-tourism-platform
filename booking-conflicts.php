<?php
require_once 'app_bootstrap.php';
require_once 'security.php';
require_once 'booking-workflow.php';
header('Content-Type: application/json; charset=utf-8');
if (!customer_is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit; }
try {
    verify_csrf();
    $from = trim($_POST['from_date'] ?? '');
    $to = trim($_POST['to_date'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)) { echo json_encode(['conflicts'=>[]]); exit; }
    $familyIds = array_map('intval', $_POST['family_ids'] ?? []);
    $others = [];
    foreach ($_POST['other_name'] ?? [] as $index=>$name) $others[]=['name'=>$name,'date_of_birth'=>$_POST['other_dob'][$index]??''];
    $db = db_connect();
    $conflicts = booking_overlap_conflicts($db,$_SESSION['user_email'],$from,$to,!empty($_POST['include_self']),$familyIds,$others,(int)($_POST['booking_id']??0));
    foreach($conflicts as &$conflict){$conflict['booking_reference']=e($conflict['booking_reference']);$conflict['destination']=e($conflict['destination']);$conflict['status']=e($conflict['status']);$conflict['travellers']=array_map('e',$conflict['travellers']);}unset($conflict);
    echo json_encode(['conflicts'=>$conflicts],JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode(['error'=>'Date availability could not be checked.']);
}
