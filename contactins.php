<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'notifications.php';
if (!isset($_SESSION['login'])) redirect_with_message('reglog.php', 'Please sign in or create an account before sending a message.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_with_message('contactus.php', 'Please send a message using the contact form.');
verify_csrf();
$firstName = trim($_POST['fname'] ?? '');
$lastName = trim($_POST['lname'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['sub'] ?? '');
$message = trim($_POST['message'] ?? '');
if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') {
    redirect_with_message('contactus.php', 'Please complete every contact field with a valid email address.');
}
try {
    $connection = db_connect();
    $statement = $connection->prepare('INSERT INTO message (email, fname, lname, sub, message) VALUES (?, ?, ?, ?, ?)');
    $statement->bind_param('sssss', $email, $firstName, $lastName, $subject, $message);
    $statement->execute();
    try{create_notification($connection,'New customer enquiry',$firstName.' '.$lastName.' sent an enquiry: '.$subject,'admin',null,'admin/inbox.php','info');}catch(Throwable $notificationError){}
    redirect_with_message('contactus.php', 'Your message has been sent. Thank you for contacting us.');
} catch (Throwable $error) {
    redirect_with_message('contactus.php', 'Your message could not be sent right now. Check the database setup and try again.');
}
