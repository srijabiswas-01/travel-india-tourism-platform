<?php
require_once '_bootstrap.php';
require_admin_permission('catalog.manage');
$db=admin_db();

function trip_theme_slug(string $name): string {
    $slug=strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/','_',$name),'_'));
    return substr($slug,0,110);
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    if(in_array($action,['create','update'],true)){
        $name=trim($_POST['name']??'');
        $description=trim($_POST['description']??'');
        $displayOrder=(int)($_POST['display_order']??0);
        if($name===''||strlen($name)>100||strlen($description)>500||$displayOrder<0||$displayOrder>9999)redirect_with_message('trip-themes.php','Enter a valid theme name, description, and display order.');
        $existing=$id?$db->execute_query('SELECT slug FROM trip_themes WHERE id=?',[$id])->fetch_assoc():null;
        $slug=$existing['slug']??trip_theme_slug($name);
        if($slug==='')redirect_with_message('trip-themes.php','Theme name must contain letters or numbers.');
        try{
            if($action==='create')$db->execute_query('INSERT INTO trip_themes(name,slug,description,display_order) VALUES(?,?,?,?)',[$name,$slug,$description,$displayOrder]);
            else $db->execute_query('UPDATE trip_themes SET name=?,description=?,display_order=? WHERE id=?',[$name,$description,$displayOrder,$id]);
        }catch(Throwable $error){redirect_with_message('trip-themes.php','That trip theme already exists.');}
        admin_audit($db,$action,'trip_theme',(string)($id?:$db->insert_id),$name);
    }elseif(in_array($action,['enable','disable'],true)){
        $status=$action==='enable'?'active':'disabled';
        $db->execute_query('UPDATE trip_themes SET status=? WHERE id=?',[$status,$id]);
        admin_audit($db,$action,'trip_theme',(string)$id);
    }elseif($action==='delete'){
        $theme=$db->execute_query('SELECT slug FROM trip_themes WHERE id=?',[$id])->fetch_assoc();
        if(!$theme)redirect_with_message('trip-themes.php','Trip theme not found.');
        $used=(int)$db->execute_query('SELECT COUNT(*) total FROM bookings WHERE trip_theme=?',[$theme['slug']])->fetch_assoc()['total'];
        if($used)redirect_with_message('trip-themes.php','This theme is used by bookings. Disable it instead.');
        $db->execute_query('DELETE FROM trip_themes WHERE id=?',[$id]);
        admin_audit($db,'delete','trip_theme',(string)$id);
    }
    redirect_with_message('trip-themes.php','Trip theme updated.');
}

$edit=null;
if(!empty($_GET['edit']))$edit=$db->execute_query('SELECT * FROM trip_themes WHERE id=?',[(int)$_GET['edit']])->fetch_assoc();
$pagination=admin_paginate($db,'SELECT COUNT(*) total FROM trip_themes');
$items=$db->query("SELECT t.*,(SELECT COUNT(*) FROM bookings b WHERE b.trip_theme=t.slug) booking_count FROM trip_themes t ORDER BY t.display_order,t.name LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}");
$pageTitle='Trip Themes';
include '_header.php';
?>
<div class="page-head"><div><span>Journey preferences</span><h1>Trip Themes</h1><p>Create the travel themes customers can select while booking.</p></div></div>
<section class="panel form-panel"><div class="panel-head"><h2><?php echo $edit?'Edit theme':'Add theme';?></h2><?php if($edit):?><a href="trip-themes.php">Cancel</a><?php endif;?></div><form class="admin-form" method="post"><?php echo csrf_input();?><input type="hidden" name="action" value="<?php echo $edit?'update':'create';?>"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0);?>"><label>Name<input name="name" maxlength="100" value="<?php echo e($edit['name']??'');?>" required></label><label>Display order<input type="number" name="display_order" min="0" max="9999" value="<?php echo (int)($edit['display_order']??0);?>" required></label><label class="wide">Description<input name="description" maxlength="500" value="<?php echo e($edit['description']??'');?>"></label><div class="form-actions"><button><i class="fa-solid fa-floppy-disk"></i> Save theme</button></div></form></section>
<section class="panel"><div class="panel-head"><h2>Trip-theme directory</h2><small><?php echo (int)$pagination['total'];?> records</small></div><div class="table-wrap"><table><thead><tr><th>Theme</th><th>Description</th><th>Order</th><th>Bookings</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php while($theme=$items->fetch_assoc()):?><tr><td><strong><?php echo e($theme['name']);?></strong><small><?php echo e($theme['slug']);?></small></td><td><?php echo e($theme['description']);?></td><td><?php echo (int)$theme['display_order'];?></td><td><?php echo (int)$theme['booking_count'];?></td><td><span class="pill"><?php echo e($theme['status']);?></span></td><td><div class="action-row"><a class="icon-btn" href="?edit=<?php echo (int)$theme['id'];?>" title="Edit theme"><i class="fa-solid fa-pen"></i></a><form method="post"><?php echo csrf_input();?><input type="hidden" name="id" value="<?php echo (int)$theme['id'];?>"><input type="hidden" name="action" value="<?php echo $theme['status']==='active'?'disable':'enable';?>"><button class="icon-btn status-toggle <?php echo $theme['status']==='active'?'is-on':'is-off';?>" title="<?php echo $theme['status']==='active'?'Disable':'Enable';?> theme"><i class="fa-solid fa-toggle-<?php echo $theme['status']==='active'?'on':'off';?>"></i></button></form><form method="post" onsubmit="return confirm('Delete this unused trip theme?')"><?php echo csrf_input();?><input type="hidden" name="id" value="<?php echo (int)$theme['id'];?>"><input type="hidden" name="action" value="delete"><button class="icon-btn danger-icon" title="Delete theme"><i class="fa-solid fa-trash"></i></button></form></div></td></tr><?php endwhile;?></tbody></table></div><?php admin_pagination($pagination);?></section>
<?php include '_footer.php';?>
