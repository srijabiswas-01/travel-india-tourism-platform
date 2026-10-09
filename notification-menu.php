<div class="notification-menu">
  <button class="notification-toggle" type="button" aria-label="Open notifications" aria-expanded="false" title="Notifications"><i class="fa-solid fa-bell"></i><?php if($notificationUnread>0):?><span><?php echo min(99,$notificationUnread);?></span><?php endif;?></button>
  <div class="notification-panel" hidden>
    <div class="notification-head"><strong>Notifications</strong><?php if(!empty($_SESSION['user_email'])&&$notificationUnread):?><form method="post" action="<?php echo $siteRoot;?>notification-read.php"><?php echo csrf_input();?><input type="hidden" name="action" value="all"><input type="hidden" name="return" value="<?php echo e(basename($_SERVER['PHP_SELF']));?>"><button>Mark all read</button></form><?php endif;?></div>
    <div class="notification-list">
    <?php if(!$headerNotifications):?><div class="notification-empty"><i class="fa-regular fa-bell"></i><p>No current notifications</p></div><?php endif;?>
    <?php foreach($headerNotifications as $notice):$target=notification_link($notice['link']??null,basename($_SERVER['PHP_SELF']));?>
      <?php if(!empty($_SESSION['user_email'])):?><form method="post" action="<?php echo $siteRoot;?>notification-read.php" class="notification-item <?php echo empty($notice['read_at'])?'is-unread':'';?> type-<?php echo e($notice['type']);?>"><?php echo csrf_input();?><input type="hidden" name="id" value="<?php echo (int)$notice['id'];?>"><input type="hidden" name="return" value="<?php echo e($target);?>"><button><i class="fa-solid <?php echo $notice['type']==='success'?'fa-circle-check':($notice['type']==='warning'?'fa-triangle-exclamation':'fa-circle-info');?>"></i><span><strong><?php echo e($notice['title']);?></strong><small><?php echo e($notice['message']);?></small><time><?php echo e(date('d M, h:i A',strtotime($notice['created_at'])));?></time></span></button></form>
      <?php else:?><a href="<?php echo e($target);?>" class="notification-item type-<?php echo e($notice['type']);?>"><i class="fa-solid fa-bullhorn"></i><span><strong><?php echo e($notice['title']);?></strong><small><?php echo e($notice['message']);?></small><time><?php echo e(date('d M, h:i A',strtotime($notice['created_at'])));?></time></span></a><?php endif;?>
    <?php endforeach;?>
    </div>
  </div>
</div>
