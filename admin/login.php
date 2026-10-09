<?php
require_once '_bootstrap.php';
if (admin_logged_in()) { header('Location:index.php'); exit; }
$dbError = ''; $adminCount = 0;
try { $db=admin_db(); $adminCount=(int)$db->query('SELECT COUNT(*) total FROM admins')->fetch_assoc()['total']; } catch(Throwable $error) { $dbError='Database unavailable. Check the connection settings.'; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); $email=trim($_POST['email']??''); $password=$_POST['password']??'';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8)redirect_with_message('login.php','Enter a valid email address and a password with at least 8 characters.');
    try {
        $db=admin_db();if(auth_is_rate_limited($db,$email,'admin'))redirect_with_message('login.php','Too many sign-in attempts. Try again in 15 minutes.');$statement=$db->prepare('SELECT id,name,email,password,role FROM admins WHERE email=? AND is_active=1'); $statement->bind_param('s',$email); $statement->execute(); $admin=$statement->get_result()->fetch_assoc();
        if (!$admin || !password_verify($password,$admin['password'])){auth_record_failure($db,$email,'admin');redirect_with_message('login.php','Incorrect administrator email or password.');}
        session_regenerate_id(true);auth_clear_failures($db,$email,'admin'); $_SESSION['admin_id']=$admin['id']; $_SESSION['admin_name']=$admin['name']; $_SESSION['admin_email']=$admin['email']; $_SESSION['admin_role']=$admin['role'];
        $db->query('UPDATE admins SET last_login_at=NOW() WHERE id='.(int)$admin['id']); header('Location:index.php'); exit;
    } catch(Throwable $error) { redirect_with_message('login.php','Administrator sign in is unavailable.'); }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Sign In | Travel India</title><link rel="icon" href="../img/favicon.jpg?v=1" type="image/jpeg"><script>document.documentElement.dataset.adminTheme=localStorage.getItem('travelIndiaAdminTheme')||'light';</script><link rel="stylesheet" href="../css/admin.css?v=16"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><script src="../js/admin-theme.js?v=1" defer></script><script src="../js/password-toggle.js" defer></script><script src="../js/form-validation.js?v=1" defer></script></head><body class="admin-auth">
<section class="auth-panel"><button class="admin-theme-toggle auth-theme-toggle" type="button" aria-label="Switch color theme" title="Switch color theme"><i class="fa-solid fa-moon" aria-hidden="true"></i></button><a class="auth-brand" href="../main.php"><img src="../img/logo-transparent.png?v=1" alt="Travel India emblem"><strong>Travel India</strong><span>Management Information System</span></a>
<?php if(!empty($_SESSION['flash'])):?><div class="admin-flash"><?php echo e($_SESSION['flash']);unset($_SESSION['flash']);?></div><?php endif;?><?php if($dbError):?><div class="admin-flash error"><?php echo e($dbError);?></div><?php endif;?><h1>Administrator sign in</h1><p>Use an authorized staff account to manage travel operations.</p>
<form method="post"><?php echo csrf_input();?><label>Email<input type="email" name="email" required autocomplete="username"></label><label>Password<span class="auth-password"><input id="admin-login-password" type="password" name="password" minlength="8" required autocomplete="current-password"><button type="button" class="password-toggle" data-password-toggle="admin-login-password" aria-label="Show password" title="Show password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></span></label><button type="submit">Sign in</button></form>
<?php if($adminCount===0&&!$dbError):?><a class="setup-link" href="setup.php">Create the first administrator</a><?php endif;?></section></body></html>
