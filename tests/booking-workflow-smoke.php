<?php
declare(strict_types=1);

require_once __DIR__ . '/../booking-workflow.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: {$message}\n";
}

$db = db_connect();
$db->begin_transaction();

try {
    $suffix = bin2hex(random_bytes(5));
    $email = "workflow-test-{$suffix}@invalid.local";
    $reference = 'TEST-' . strtoupper($suffix);
    $password = password_hash('TransactionalTestOnly!', PASSWORD_DEFAULT);
    $db->execute_query('INSERT INTO users(email,name,password,is_active) VALUES(?,?,?,1)',[$email,'Workflow Test',$password]);
    $db->execute_query("INSERT INTO bookings(booking_reference,user_email,from_date,to_date,destination,hotel,people,customer_name,status,payment_status,total_amount) VALUES(?,?,?,?,?,?,1,?,'pending','unpaid',10000)",[$reference,$email,'2027-01-10','2027-01-13','Test Destination','Test Hotel','Workflow Test']);
    $bookingId = (int)$db->insert_id;
    $db->execute_query("INSERT INTO booking_travellers(booking_id,name,relationship,date_of_birth,gender,age_category,traveller_type) VALUES(?,?,?,?,?,'adult','primary')",[$bookingId,'Workflow Test','Self','1990-01-01','Other']);

    $warning = booking_overlap_conflicts($db,$email,'2027-01-12','2027-01-15',true,[],[]);
    expect(count($warning)===1 && $warning[0]['severity']==='warning','pending overlap produces a warning');

    $boundary = booking_overlap_conflicts($db,$email,'2027-01-13','2027-01-16',true,[],[]);
    expect(count($boundary)===1,'shared boundary date is treated as an overlap');

    $available = booking_overlap_conflicts($db,$email,'2027-01-14','2027-01-17',true,[],[]);
    expect($available===[],'the day after an existing trip is available');

    $db->execute_query("UPDATE bookings SET status='confirmed',payment_status='paid' WHERE id=?",[$bookingId]);
    $hard = booking_overlap_conflicts($db,$email,'2027-01-11','2027-01-12',true,[],[]);
    expect(count($hard)===1 && $hard[0]['severity']==='hard','confirmed overlap produces a hard conflict');

    $quote = cancellation_quote($db,['from_date'=>'2027-01-10','status'=>'confirmed','payment_status'=>'paid','total_amount'=>10000]);
    expect((float)$quote['percentage']===90.0 && (float)$quote['refund']===9000.0,'15+ day cancellation policy returns a 90% estimate');

    $db->execute_query("INSERT INTO booking_amendments(booking_id,old_from_date,old_to_date,requested_from_date,requested_to_date,customer_reason) VALUES(?,?,?,?,?,'Test request')",[$bookingId,'2027-01-10','2027-01-13','2027-02-10','2027-02-13']);
    expect(active_booking_request($db,'booking_amendments',$bookingId)!==null,'active amendment request is detected');

    $db->execute_query("UPDATE bookings SET status='cancelled' WHERE id=?",[$bookingId]);
    $ignored = booking_overlap_conflicts($db,$email,'2027-01-10','2027-01-13',true,[],[]);
    expect($ignored===[],'cancelled bookings are ignored by conflict detection');

    $stale = (int)$db->query("SELECT COUNT(1) total FROM bookings WHERE payment_status='paid' AND LOWER(TRIM(admin_note)) IN ('pay the amount','please pay the amount','pay amount','please pay amount')")->fetch_assoc()['total'];
    expect($stale===0,'paid bookings contain no known stale payment reminders');

    echo "\nBooking workflow smoke test completed successfully.\n";
} finally {
    $db->rollback();
}
