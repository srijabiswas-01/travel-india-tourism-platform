<?php
require_once '_bootstrap.php';
require_admin();
$db=admin_db();
$adminId=(int)$_SESSION['admin_id'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $currentPassword=$_POST['current_password']??'';
    $newPassword=$_POST['new_password']??'';
    $passwordConfirmation=$_POST['password_confirmation']??'';
    if(strlen($newPassword)<10||strlen($newPassword)>72||$newPassword!==$passwordConfirmation)redirect_with_message('profile.php','Enter matching new passwords between 10 and 72 characters.');

    $statement=$db->prepare('SELECT password FROM admins WHERE id=? AND is_active=1');
    $statement->bind_param('i',$adminId);
    $statement->execute();
    $account=$statement->get_result()->fetch_assoc();
    if(!$account||!password_verify($currentPassword,$account['password']))redirect_with_message('profile.php','The current password is incorrect.');
    if(password_verify($newPassword,$account['password']))redirect_with_message('profile.php','Choose a new password different from your current password.');

    try{
        $db->begin_transaction();
        $passwordHash=password_hash($newPassword,PASSWORD_DEFAULT);
        $update=$db->prepare('UPDATE admins SET password=? WHERE id=? AND is_active=1');
        $update->bind_param('si',$passwordHash,$adminId);
        $update->execute();
        if($update->affected_rows!==1)throw new RuntimeException('Password update failed.');
        admin_audit($db,'password_change','admin',(string)$adminId,'Administrator changed own password');
        $db->commit();
        session_regenerate_id(true);
        redirect_with_message('profile.php','Password changed successfully.');
    }catch(Throwable $error){
        try{$db->rollback();}catch(Throwable $ignored){}
        redirect_with_message('profile.php','Password could not be changed. Please try again.');
    }
}

$statement=$db->prepare('SELECT name,email,role,created_at,last_login_at FROM admins WHERE id=? AND is_active=1');
$statement->bind_param('i',$adminId);
$statement->execute();
$profile=$statement->get_result()->fetch_assoc();
if(!$profile)redirect_with_message('logout.php','This administrator account is unavailable.');
$pageTitle='Admin Profile';
include '_header.php';
?>
<div class="page-head"><div><span>Account settings</span><h1>Admin profile</h1><p>Review your administrator account and update its password.</p></div></div>
<section class="panel"><div class="panel-head"><h2>Profile details</h2></div><div class="customer-summary"><span><b>Name</b><?php echo e($profile['name']);?></span><span><b>Email</b><?php echo e($profile['email']);?></span><span><b>Role</b><?php echo e(ucwords(str_replace('_',' ',$profile['role'])));?></span><span><b>Account created</b><?php echo e(date('d M Y',strtotime($profile['created_at'])));?></span></div></section>
<section class="panel"><div class="panel-head"><h2>Change password</h2></div><form method="post" class="admin-form"><?php echo csrf_input();?>
 <label class="wide">Current password<input type="password" name="current_password" autocomplete="current-password" required></label>
 <label>New password<input type="password" name="new_password" minlength="10" maxlength="72" autocomplete="new-password" required><small>Use 10 to 72 characters.</small></label>
 <label>Confirm new password<input type="password" name="password_confirmation" minlength="10" maxlength="72" autocomplete="new-password" required></label>
 <div class="form-actions"><button type="submit"><i class="fa-solid fa-key"></i> Change password</button></div>
</form></section>
<?php include '_footer.php';?>