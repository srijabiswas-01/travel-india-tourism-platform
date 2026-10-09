<?php
require_once '_bootstrap.php';
require_once __DIR__.'/../notifications.php';
require_admin_permission('messages.manage');
$db=admin_db();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $action=$_POST['action']??'reply';
    if($action==='delete'){
        $s=$db->prepare('DELETE FROM message WHERE id=?');
        $s->bind_param('i',$id);$s->execute();
        admin_audit($db,'delete','message',(string)$id);
        redirect_with_message('inbox.php','Message deleted.');
    }
    $status=($_POST['status']??'open')==='resolved'?'resolved':'open';
    $reply=trim($_POST['admin_response']??'');
    if($reply==='')redirect_with_message('inbox.php','Write a reply before saving.');
    $resolved=$status==='resolved'?'NOW()':'NULL';
    $s=$db->prepare("UPDATE message SET status=?,admin_response=?,resolved_at=$resolved WHERE id=?");
    $s->bind_param('ssi',$status,$reply,$id);$s->execute();
    $s=$db->prepare('SELECT m.email,m.sub,COUNT(u.email) registered FROM message m LEFT JOIN users u ON u.email=m.email WHERE m.id=? GROUP BY m.id');$s->bind_param('i',$id);$s->execute();$recipient=$s->get_result()->fetch_assoc();
    if($recipient&&!empty($recipient['registered']))create_notification($db,'Support replied to your enquiry','A response was added to your enquiry: '.$recipient['sub'],'user',$recipient['email'],'contactus.php','info',(int)$_SESSION['admin_id']);
    admin_audit($db,'reply','message',(string)$id,$status);
    redirect_with_message('inbox.php','Reply saved.');
}

$pagination=admin_paginate($db,'SELECT COUNT(*) total FROM message',10);
$messages=$db->query('SELECT * FROM message ORDER BY created_at DESC,id DESC LIMIT '.$pagination['limit'].' OFFSET '.$pagination['offset']);
$pageTitle='Messages';
include '_header.php';
?>
<div class="page-head"><div><span>Customer support</span><h1>Messages</h1><p>Review enquiries, reply, update their status, or remove obsolete records.</p></div></div>
<section class="panel">
 <div class="panel-head"><h2>Customer enquiries</h2><small><?php echo (int)$pagination['total'];?> messages</small></div>
 <div class="table-wrap"><table><thead><tr><th>Customer</th><th>Subject</th><th>Received</th><th>Status</th><th>Actions</th></tr></thead><tbody>
 <?php while($m=$messages->fetch_assoc()):$preview=strlen($m['message'])>70?substr($m['message'],0,70).'...':$m['message'];?><tr><td><strong><?php echo e($m['fname'].' '.$m['lname']);?></strong><small><?php echo e($m['email']);?></small></td><td><?php echo e($m['sub']);?><small><?php echo e($preview);?></small></td><td><?php echo e(date('d M Y',strtotime($m['created_at'])));?><small><?php echo e(date('h:i A',strtotime($m['created_at'])));?></small></td><td><span class="pill"><?php echo e($m['status']);?></span></td><td><div class="action-row"><button type="button" class="icon-btn message-reply" title="View and reply" aria-label="Reply to <?php echo e($m['fname']);?>" data-id="<?php echo (int)$m['id'];?>" data-name="<?php echo e($m['fname'].' '.$m['lname']);?>" data-email="<?php echo e($m['email']);?>" data-subject="<?php echo e($m['sub']);?>" data-message="<?php echo e($m['message']);?>" data-reply="<?php echo e($m['admin_response']);?>" data-status="<?php echo e($m['status']);?>"><i class="fa-solid fa-reply"></i></button><a class="icon-btn" href="mailto:<?php echo e($m['email']);?>?subject=<?php echo rawurlencode('Re: '.$m['sub']);?>" title="Open email client"><i class="fa-solid fa-envelope"></i></a><form method="post" onsubmit="return confirm('Permanently delete this message?')"><?php echo csrf_input();?><input type="hidden" name="id" value="<?php echo (int)$m['id'];?>"><input type="hidden" name="action" value="delete"><button class="icon-btn danger-icon" title="Delete message" aria-label="Delete message"><i class="fa-solid fa-trash"></i></button></form></div></td></tr><?php endwhile;?>
 </tbody></table></div><?php admin_pagination($pagination);?>
</section>
<dialog class="admin-dialog" id="reply-dialog" aria-labelledby="reply-title">
 <div class="dialog-head"><div><span>Customer enquiry</span><h2 id="reply-title">Reply to message</h2></div><button type="button" class="dialog-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>
 <div class="message-view"><strong id="reply-subject"></strong><p id="reply-sender"></p><div id="reply-message"></div></div>
 <form method="post" class="dialog-form"><?php echo csrf_input();?><input type="hidden" name="id" id="reply-id"><input type="hidden" name="action" value="reply"><label>Reply / resolution note<textarea name="admin_response" id="reply-text" rows="6" required></textarea></label><label>Status<select name="status" id="reply-status"><option value="open">Open</option><option value="resolved">Resolved</option></select></label><div class="dialog-actions"><button type="button" class="reset-btn dialog-cancel">Cancel</button><button class="primary-btn"><i class="fa-solid fa-reply"></i>Save reply</button></div></form>
</dialog>
<script>
(()=>{const dialog=document.querySelector('#reply-dialog');document.addEventListener('click',event=>{const button=event.target.closest('.message-reply');if(!button)return;document.querySelector('#reply-id').value=button.dataset.id;document.querySelector('#reply-title').textContent='Reply to '+button.dataset.name;document.querySelector('#reply-subject').textContent=button.dataset.subject;document.querySelector('#reply-sender').textContent=button.dataset.email;document.querySelector('#reply-message').textContent=button.dataset.message;document.querySelector('#reply-text').value=button.dataset.reply;document.querySelector('#reply-status').value=button.dataset.status;dialog.showModal()});document.querySelectorAll('.dialog-close,.dialog-cancel').forEach(button=>button.addEventListener('click',()=>dialog.close()));dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()})})();
</script>
<?php include '_footer.php';?>
