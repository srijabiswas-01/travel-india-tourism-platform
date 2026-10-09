<?php
require_once '_bootstrap.php';
require_admin_permission('admins.manage');
$db=admin_db();
$roles=[
    'manager'=>'Manager',
    'booking_admin'=>'Booking administrator',
    'package_admin'=>'Package administrator',
    'super_admin'=>'Super administrator',
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'create';
    if(in_array($action,['toggle','role'],true)){
        $id=(int)($_POST['id']??0);
        $target=$db->execute_query('SELECT id,email,role,is_active FROM admins WHERE id=?',[$id])->fetch_assoc();
        if(!$target)redirect_with_message('admins.php','Administrator account not found.');
        if($action==='toggle'){
            if($id===(int)$_SESSION['admin_id'])redirect_with_message('admins.php','You cannot disable your own signed-in account.');
            if($target['role']==='super_admin'&&(int)$target['is_active']===1){
                $activeSuperAdmins=(int)$db->query("SELECT COUNT(*) total FROM admins WHERE role='super_admin' AND is_active=1")->fetch_assoc()['total'];
                if($activeSuperAdmins<=1)redirect_with_message('admins.php','The last active super administrator cannot be disabled.');
            }
            $next=(int)$target['is_active']===1?0:1;
            $db->execute_query('UPDATE admins SET is_active=? WHERE id=?',[$next,$id]);
            admin_audit($db,$next?'enable':'disable','admin',(string)$id,$target['email']);
            redirect_with_message('admins.php','Administrator access updated.');
        }
        $newRole=$_POST['role']??'';
        if(!isset($roles[$newRole]))redirect_with_message('admins.php','Select a valid administrator role.');
        if($target['role']==='super_admin'&&$newRole!=='super_admin'&&(int)$target['is_active']===1){
            $activeSuperAdmins=(int)$db->query("SELECT COUNT(*) total FROM admins WHERE role='super_admin' AND is_active=1")->fetch_assoc()['total'];
            if($activeSuperAdmins<=1)redirect_with_message('admins.php','The last active super administrator must keep that role.');
        }
        $db->execute_query('UPDATE admins SET role=? WHERE id=?',[$newRole,$id]);
        if($id===(int)$_SESSION['admin_id'])$_SESSION['admin_role']=$newRole;
        admin_audit($db,'role_update','admin',(string)$id,$target['role'].' -> '.$newRole);
        redirect_with_message('admins.php','Administrator role updated.');
    }
    $name=trim($_POST['name']??'');
    $email=strtolower(trim($_POST['email']??''));
    $password=$_POST['password']??'';
    $passwordConfirmation=$_POST['password_confirmation']??'';
    $role=$_POST['role']??'';
    if($name===''||strlen($name)>150||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>255||strlen($password)<10||$password!==$passwordConfirmation||!isset($roles[$role]))redirect_with_message('admins.php','Enter a valid name, email, matching passwords of at least 10 characters, and role.');

    $check=$db->prepare('SELECT id FROM admins WHERE email=?');
    $check->bind_param('s',$email);
    $check->execute();
    if($check->get_result()->fetch_assoc())redirect_with_message('admins.php','An administrator already uses that email address.');

    try{
        $db->begin_transaction();
        $hash=password_hash($password,PASSWORD_DEFAULT);
        $statement=$db->prepare('INSERT INTO admins(name,email,password,role) VALUES(?,?,?,?)');
        $statement->bind_param('ssss',$name,$email,$hash,$role);
        $statement->execute();
        $id=(string)$db->insert_id;
        admin_audit($db,'create','admin',$id,$email.'; role '.$role);
        $db->commit();
        redirect_with_message('admins.php','Administrator account created.');
    }catch(Throwable $error){
        try{$db->rollback();}catch(Throwable $ignored){}
        redirect_with_message('admins.php','Administrator account could not be created. Check that the email is not already in use.');
    }
}

$admins=$db->query('SELECT id,name,email,role,is_active,created_at,last_login_at FROM admins ORDER BY created_at DESC');
$pageTitle='Admins';
include '_header.php';
?>
<div class="page-head"><div><span>Access control</span><h1>Administrators</h1><p>Create staff accounts and assign the least-privileged role they need.</p></div></div>
<section class="panel"><div class="panel-head"><h2>Add administrator</h2></div>
<form method="post" class="admin-form"><?php echo csrf_input();?><input type="hidden" name="action" value="create">
 <label>Full name<input name="name" maxlength="150" autocomplete="name" required></label>
 <label>Email address<input type="email" name="email" maxlength="255" autocomplete="email" required></label>
 <label>Initial password<input type="password" name="password" minlength="10" autocomplete="new-password" required><small>At least 10 characters. Use a unique password and share it securely.</small></label>
 <label>Confirm password<input type="password" name="password_confirmation" minlength="10" autocomplete="new-password" required></label>
 <label>Role<select name="role" required><option value="">Select a role</option><?php foreach($roles as $value=>$label):?><option value="<?php echo e($value);?>"><?php echo e($label);?></option><?php endforeach;?></select></label>
 <div class="form-actions"><button type="submit"><i class="fa-solid fa-user-plus"></i> Create administrator</button></div>
</form></section>
<section class="panel"><div class="panel-head"><h2>Administrator accounts</h2><small><?php echo (int)$admins->num_rows;?> accounts</small></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last sign in</th><th>Actions</th></tr></thead><tbody>
<?php while($admin=$admins->fetch_assoc()):?><tr><td><strong><?php echo e($admin['name']);?></strong><small>Added <?php echo e(date('d M Y',strtotime($admin['created_at'])));?></small></td><td><?php echo e($admin['email']);?></td><td><form method="post" class="action-row"><?php echo csrf_input();?><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?php echo (int)$admin['id'];?>"><select name="role" aria-label="Role for <?php echo e($admin['name']);?>"><?php foreach($roles as $value=>$label):?><option value="<?php echo e($value);?>" <?php echo $admin['role']===$value?'selected':'';?>><?php echo e($label);?></option><?php endforeach;?></select><button class="icon-btn" title="Save role" aria-label="Save role"><i class="fa-solid fa-floppy-disk"></i></button></form></td><td><span class="pill"><?php echo $admin['is_active']?'Active':'Inactive';?></span></td><td><?php echo $admin['last_login_at']?e(date('d M Y, h:i A',strtotime($admin['last_login_at']))):'Never';?></td><td><form method="post"><?php echo csrf_input();?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?php echo (int)$admin['id'];?>"><button class="icon-btn" title="<?php echo $admin['is_active']?'Disable':'Enable';?> administrator" aria-label="<?php echo $admin['is_active']?'Disable':'Enable';?> administrator" <?php echo (int)$admin['id']===(int)$_SESSION['admin_id']?'disabled':'';?>><i class="fa-solid <?php echo $admin['is_active']?'fa-toggle-on':'fa-toggle-off';?>"></i></button></form></td></tr><?php endwhile;?>
</tbody></table></div></section>
<?php include '_footer.php';?>
