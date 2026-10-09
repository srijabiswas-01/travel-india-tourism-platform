<?php
require_once '_bootstrap.php';
$db = admin_db();
define('TRAVEL_INDIA_ALLOW_SCHEMA_MIGRATION', true);
ensure_app_schema($db);
$count = (int)$db->query('SELECT COUNT(*) total FROM admins')->fetch_assoc()['total'];
if ($count > 0) redirect_with_message('login.php', 'Administrator setup has already been completed.');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || $password !== $confirmPassword) {
        redirect_with_message('setup.php', 'Enter a name, valid email, matching passwords, and a password of at least 10 characters.');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'super_admin';
    $statement = $db->prepare('INSERT INTO admins(name,email,password,role) VALUES(?,?,?,?)');
    $statement->bind_param('ssss', $name, $email, $hash, $role);
    $statement->execute();
    redirect_with_message('login.php', 'Administrator account created. Sign in to continue.');
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Setup | Travel India</title>
<link rel="icon" href="../img/favicon.jpg?v=1" type="image/jpeg">
<script>document.documentElement.dataset.adminTheme=localStorage.getItem('travelIndiaAdminTheme')||'light';</script><link rel="stylesheet" href="../css/admin.css?v=16"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><script src="../js/admin-theme.js?v=1" defer></script><script src="../js/password-toggle.js" defer></script><script src="../js/form-validation.js?v=1" defer></script></head><body class="admin-auth">
<section class="auth-panel"><button class="admin-theme-toggle auth-theme-toggle" type="button" aria-label="Switch color theme" title="Switch color theme"><i class="fa-solid fa-moon" aria-hidden="true"></i></button><a class="auth-brand" href="../main.php"><img src="../img/logo-transparent.png?v=1" alt="Travel India emblem"><strong>Travel India</strong><span>One-time administrator setup</span></a>
<?php if (!empty($_SESSION['flash'])): ?><div class="admin-flash"><?php echo e($_SESSION['flash']); unset($_SESSION['flash']); ?></div><?php endif; ?>
<h1>Create administrator</h1><p>This page closes automatically after the first account is created.</p>
<form method="post"><?php echo csrf_input(); ?><label>Full name<input name="name" maxlength="150" required></label><label>Email<input type="email" name="email" maxlength="255" required></label><label>Password<span class="auth-password"><input id="admin-setup-password" type="password" name="password" minlength="10" required><button type="button" class="password-toggle" data-password-toggle="admin-setup-password" aria-label="Show password" title="Show password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></span></label><label>Confirm password<span class="auth-password"><input id="admin-setup-confirm-password" type="password" name="confirm_password" minlength="10" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle="admin-setup-confirm-password" aria-label="Show password" title="Show password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></span></label><button>Create secure account</button></form>
</section></body></html>
