<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_with_message('reglog.php', 'Please register using the form.');
verify_csrf();
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
if ($name === '' || strlen($name)>150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>255 || strlen($password) < 8 || strlen($password)>72 || $password !== $confirmPassword) {
    redirect_with_message('reglog.php', 'Check your name and email, and make sure both passwords match and contain 8 to 72 characters.');
}
try {
    $connection = db_connect();
    ensure_app_schema($connection);
    $statement = $connection->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    if (!$statement) throw new RuntimeException('Registration requires the updated database schema from db/travel_india.sql.');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $statement->bind_param('sss', $name, $email, $hash);
    if (!$statement->execute()) {
        $message = $statement->errno === 1062 ? 'An account with that email already exists.' : 'Registration could not be completed.';
        redirect_with_message('reglog.php', $message);
    }
    redirect_with_message('reglog.php', 'Account created successfully. You can now sign in.');
} catch (Throwable $error) {
    redirect_with_message('reglog.php', 'Registration is temporarily unavailable. Check the database setup and try again.');
}
