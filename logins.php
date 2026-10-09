<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_with_message('reglog.php', 'Please sign in using the form.');
verify_csrf();
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) redirect_with_message('reglog.php', 'Enter a valid email address and a password with at least 8 characters.');

try {
    $connection = db_connect();
    ensure_app_schema($connection);
    if(auth_is_rate_limited($connection,$email,'customer'))redirect_with_message('reglog.php','Too many sign-in attempts. Try again in 15 minutes.');
    $statement = $connection->prepare('SELECT name, password, is_active FROM users WHERE email = ? LIMIT 1');
    $statement->bind_param('s', $email);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();
    $valid = $user && (int)$user['is_active'] === 1 && (password_verify($password, $user['password']) || hash_equals($user['password'], md5($password)));
    if (!$valid){auth_record_failure($connection,$email,'customer');redirect_with_message('reglog.php', 'Incorrect email address or password.');}
    if (strlen($user['password']) === 32) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upgrade = $connection->prepare('UPDATE users SET password = ? WHERE email = ?');
        $upgrade->bind_param('ss', $newHash, $email);
        $upgrade->execute();
    }
    session_regenerate_id(true);
    auth_clear_failures($connection,$email,'customer');
    $_SESSION['login'] = $user['name'];
    $_SESSION['user_email'] = $email;
    redirect_with_message('profile.php', 'Welcome back, ' . $user['name'] . '.');
} catch (Throwable $error) {
    redirect_with_message('reglog.php', 'Sign in is temporarily unavailable. Make sure MySQL is running and try again.');
}
