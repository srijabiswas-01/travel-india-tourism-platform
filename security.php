<?php
require_once __DIR__ . '/app_bootstrap.php';
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_input(): string { return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">'; }
function verify_csrf(): void
{
    if (empty($_POST['csrf_token']) || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page, and try again.');
    }
}
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function customer_is_logged_in(): bool { return !empty($_SESSION['login']) && !empty($_SESSION['user_email']); }
function auth_client_ip(): string { return substr((string)($_SERVER['REMOTE_ADDR']??'unknown'),0,45); }
function auth_is_rate_limited(mysqli $db,string $identifier,string $portal,int $limit=5,int $minutes=15): bool {$since=date('Y-m-d H:i:s',time()-($minutes*60));$ip=auth_client_ip();$statement=$db->prepare('SELECT COUNT(*) total FROM login_attempts WHERE portal=? AND attempted_at>=? AND (identifier=? OR ip_address=?)');$statement->bind_param('ssss',$portal,$since,$identifier,$ip);$statement->execute();return (int)$statement->get_result()->fetch_assoc()['total'] >= $limit;}
function auth_record_failure(mysqli $db,string $identifier,string $portal): void {$ip=auth_client_ip();$statement=$db->prepare('INSERT INTO login_attempts(identifier,portal,ip_address) VALUES(?,?,?)');$statement->bind_param('sss',$identifier,$portal,$ip);$statement->execute();}
function auth_clear_failures(mysqli $db,string $identifier,string $portal): void {$statement=$db->prepare('DELETE FROM login_attempts WHERE identifier=? AND portal=?');$statement->bind_param('ss',$identifier,$portal);$statement->execute();}
function inr(mixed $amount): string
{
    $number = number_format((float)$amount, 2, '.', '');
    [$whole, $decimal] = explode('.', $number);
    $lastThree = substr($whole, -3);
    $rest = substr($whole, 0, -3);
    if ($rest !== '') $lastThree = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $lastThree;
    return '₹' . $lastThree . ($decimal === '00' ? '' : '.' . $decimal);
}
