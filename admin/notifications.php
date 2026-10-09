<?php
require_once '_bootstrap.php';
require_once __DIR__.'/../notifications.php';
require_admin_permission('notifications.manage');
$db=admin_db();
if(!function_exists('mb_strimwidth')){function mb_strimwidth(string $text,int $start,int $width,string $trim=''): string{$value=substr($text,$start,$width);return strlen($text)>$width?$value.$trim:$value;}}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'create';
    $id=(int)($_POST['id']??0);
    if($action==='delete'){
        $s=$db->prepare('DELETE FROM notifications WHERE id=?');$s->bind_param('i',$id);$s->execute();
        admin_audit($db,'delete','notification',(string)$id);redirect_with_message('notifications.php','Notification deleted.');
    }
    if($action==='toggle'){
        $s=$db->prepare('UPDATE notifications SET is_active=1-is_active WHERE id=?');$s->bind_param('i',$id);$s->execute();
        admin_audit($db,'toggle','notification',(string)$id);redirect_with_message('notifications.php','Notification visibility updated.');
    }
    $title=trim($_POST['title']??'');$message=trim($_POST['message']??'');$audience=$_POST['audience']??'public';$email=trim($_POST['user_email']??'');$link=trim($_POST['link']??'');$type=$_POST['type']??'info';$starts=trim($_POST['starts_at']??'');$expires=trim($_POST['expires_at']??'');
    if(!in_array($audience,['public','user','admin'],true)||$title===''||$message===''||($audience==='user'&&$email===''))redirect_with_message('notifications.php','Choose an audience and add a title and message; a customer email is required for individual messages.');
    if($audience==='user'){$s=$db->prepare('SELECT COUNT(*) total FROM users WHERE email=?');$s->bind_param('s',$email);$s->execute();if(!(int)$s->get_result()->fetch_assoc()['total'])redirect_with_message('notifications.php','That customer email is not registered.');}
    $starts=$starts!==''?str_replace('T',' ',$starts).':00':null;$expires=$expires!==''?str_replace('T',' ',$expires).':00':null;
    create_notification($db,$title,$message,$audience,$email?:null,$link?:null,$type,(int)$_SESSION['admin_id'],$starts,$expires);
    admin_audit($db,'create','notification','',$audience);redirect_with_message('notifications.php','Notification published.');
}

$customers=$db->query('SELECT email,name FROM users WHERE is_active=1 ORDER BY name');
$pagination=admin_paginate($db,'SELECT COUNT(*) total FROM notifications',10);
$items=$db->query('SELECT n.*,a.name admin_name,(SELECT COUNT(*) FROM notification_reads r WHERE r.notification_id=n.id) read_count FROM notifications n LEFT JOIN admins a ON a.id=n.created_by ORDER BY n.created_at DESC LIMIT '.$pagination['limit'].' OFFSET '.$pagination['offset']);
$pageTitle='Notifications';include '_header.php';
?>
<div class="page-head"><div><span>Customer communication</span><h1>Notifications</h1><p>Publish announcements for visitors, customers, or the admin team.</p></div></div>
<section class="panel"><div class="panel-head"><h2>Create notification</h2></div><form method="post" class="admin-form" id="notification-form"><?php echo csrf_input();?><input type="hidden" name="action" value="create">
 <label>Audience<select name="audience" id="notification-audience"><option value="public">Public announcement</option><option value="user">Specific customer</option><option value="admin">Admin team</option></select></label>
 <label id="notification-customer" hidden>Customer email<input name="user_email" type="email" list="customer-emails" placeholder="customer@example.com"><datalist id="customer-emails"><?php while($customer=$customers->fetch_assoc()):?><option value="<?php echo e($customer['email']);?>"><?php echo e($customer['name']);?></option><?php endwhile;?></datalist></label>
 <label>Title<input name="title" maxlength="160" required placeholder="e.g. Monsoon package offer"></label><label>Type<select name="type"><option value="info">Information</option><option value="success">Success</option><option value="warning">Important</option></select></label>
 <label class="wide">Message<textarea name="message" required placeholder="Write the notification customers will see."></textarea></label><label class="wide">Optional link<input name="link" maxlength="255" placeholder="Package.php or my-bookings.php"></label>
 <label>Show from (optional)<input type="datetime-local" name="starts_at"></label><label>Expire at (optional)<input type="datetime-local" name="expires_at"></label>
 <div class="form-actions"><button><i class="fa-solid fa-paper-plane"></i> Publish notification</button></div>
</form></section>
<section class="panel"><div class="panel-head"><h2>Notification history</h2><small><?php echo (int)$pagination['total'];?> records</small></div><div class="table-wrap"><table><thead><tr><th>Notification</th><th>Audience</th><th>Schedule</th><th>Reads</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php while($item=$items->fetch_assoc()):?><tr><td><strong><?php echo e($item['title']);?></strong><small><?php echo e(mb_strimwidth($item['message'],0,85,'...'));?></small></td><td><?php echo $item['audience']==='public'?'Everyone':($item['audience']==='admin'?'Admin team':e($item['user_email']));?><small><?php echo e($item['type']);?></small></td><td><?php echo e(date('d M Y, h:i A',strtotime($item['created_at'])));?><small><?php echo $item['expires_at']?'Ends '.e(date('d M Y',strtotime($item['expires_at']))):'No expiry';?></small></td><td><?php echo (int)$item['read_count'];?></td><td><span class="pill"><?php echo $item['is_active']?'active':'hidden';?></span></td><td><div class="action-row"><form method="post"><?php echo csrf_input();?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?php echo (int)$item['id'];?>"><button class="icon-btn status-toggle <?php echo $item['is_active']?'is-on':'is-off';?>" title="<?php echo $item['is_active']?'Hide':'Show';?> notification"><i class="fa-solid fa-toggle-<?php echo $item['is_active']?'on':'off';?>"></i></button></form><form method="post" onsubmit="return confirm('Delete this notification?')"><?php echo csrf_input();?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$item['id'];?>"><button class="icon-btn danger-icon" title="Delete notification"><i class="fa-solid fa-trash"></i></button></form></div></td></tr><?php endwhile;?>
</tbody></table></div><?php admin_pagination($pagination);?></section>
<script>const audience=document.querySelector('#notification-audience');const customer=document.querySelector('#notification-customer');const syncAudience=()=>{const targeted=audience.value==='user';customer.hidden=!targeted;customer.querySelector('input').required=targeted};audience.addEventListener('change',syncAudience);syncAudience();</script>
<?php include '_footer.php';?>
