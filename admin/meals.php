<?php
require_once '_bootstrap.php';
require_admin_permission('catalog.manage');
$db=admin_db();
ensure_app_schema($db);

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    if(in_array($action,['create','update'],true)){
        $destinationId=(int)($_POST['destination_id']??0);
        $planType=$_POST['plan_type']??'premium';
        $name=$planType==='standard'?'Standard meals':'Premium meals';
        $includedMeals=trim($_POST['included_meals']??'');
        $description=trim($_POST['description']??'');
        $surcharge=(float)($_POST['surcharge_per_traveller']??0);
        $veg=!empty($_POST['supports_veg'])?1:0;
        $nonveg=!empty($_POST['supports_nonveg'])?1:0;
        if(!in_array($planType,['standard','premium'],true)||$includedMeals===''||strlen($includedMeals)>255||strlen($description)>2000||(!$veg&&!$nonveg)||$surcharge<0||$surcharge>1000000||($planType==='standard'&&$surcharge!=0.0))redirect_with_message('meals.php','Enter a valid plan. Standard meals must remain free and at least one food preference is required.');
        if($destinationId&&!$db->execute_query("SELECT id FROM destinations WHERE id=? AND status='active'",[$destinationId])->fetch_assoc())redirect_with_message('meals.php','Select an active destination.');
        $duplicate=$db->execute_query('SELECT id FROM meal_plans WHERE plan_type=? AND COALESCE(destination_id,0)=? AND id<>?',[$planType,$destinationId,$id])->fetch_assoc();
        if($duplicate)redirect_with_message('meals.php','That destination already has this meal-plan type. Edit the existing plan instead.');
        $destinationValue=$destinationId?:null;
        if($action==='create')$db->execute_query('INSERT INTO meal_plans(destination_id,name,plan_type,included_meals,description,surcharge_per_traveller,supports_veg,supports_nonveg) VALUES(?,?,?,?,?,?,?,?)',[$destinationValue,$name,$planType,$includedMeals,$description,$surcharge,$veg,$nonveg]);
        else $db->execute_query('UPDATE meal_plans SET destination_id=?,name=?,plan_type=?,included_meals=?,description=?,surcharge_per_traveller=?,supports_veg=?,supports_nonveg=?,updated_at=NOW() WHERE id=?',[$destinationValue,$name,$planType,$includedMeals,$description,$surcharge,$veg,$nonveg,$id]);
        admin_audit($db,$action,'meal_plan',(string)($id?:$db->insert_id),$name);
        redirect_with_message('meals.php','Meal plan saved.');
    }
    if(in_array($action,['enable','disable'],true)){
        $plan=$db->execute_query('SELECT plan_type,destination_id FROM meal_plans WHERE id=?',[$id])->fetch_assoc();
        if(!$plan)redirect_with_message('meals.php','Meal plan not found.');
        if($action==='disable'&&$plan['plan_type']==='standard'&&$plan['destination_id']===null)redirect_with_message('meals.php','The global Standard meal plan must remain active.');
        $status=$action==='enable'?'active':'disabled';
        $db->execute_query('UPDATE meal_plans SET status=?,updated_at=NOW() WHERE id=?',[$status,$id]);
        admin_audit($db,$action,'meal_plan',(string)$id);
        redirect_with_message('meals.php','Meal plan status updated.');
    }
    if($action==='delete'){
        $used=(int)$db->execute_query('SELECT COUNT(*) total FROM bookings WHERE meal_plan_id=?',[$id])->fetch_assoc()['total'];
        if($used)redirect_with_message('meals.php','This plan is used by bookings. Disable it instead.');
        $db->execute_query("DELETE FROM meal_plans WHERE id=? AND plan_type<>'standard'",[$id]);
        admin_audit($db,'delete','meal_plan',(string)$id);
        redirect_with_message('meals.php','Meal plan deleted.');
    }
}

$edit=null;
if(!empty($_GET['edit']))$edit=$db->execute_query('SELECT * FROM meal_plans WHERE id=?',[(int)$_GET['edit']])->fetch_assoc();
$destinations=$db->query("SELECT d.id,d.name,s.name state_name FROM destinations d JOIN states s ON s.id=d.state_id WHERE d.status='active' ORDER BY s.name,d.name")->fetch_all(MYSQLI_ASSOC);
$mealPlans=[];$mealLoadError='';
$mealResult=$db->query("SELECT m.*,d.name destination,s.name state_name,(SELECT COUNT(*) FROM bookings b WHERE b.meal_plan_id=m.id) booking_count FROM meal_plans m LEFT JOIN destinations d ON d.id=m.destination_id LEFT JOIN states s ON s.id=d.state_id ORDER BY m.plan_type,m.status,m.name");
if($mealResult)$mealPlans=$mealResult->fetch_all(MYSQLI_ASSOC);else $mealLoadError='Meal-plan data is unavailable. Run php db/migrate.php and reload this page.';
$pageTitle='Meal Plans';
include '_header.php';
?>
<div class="page-head"><div><span>Traveller dining</span><h1>Meal Plans</h1><p>All-destination plans are defaults. Active destination-specific plans replace the defaults for that destination.</p></div></div>
<?php if($mealLoadError):?><div class="admin-flash"><?php echo e($mealLoadError);?></div><?php endif;?>
<section class="panel form-panel"><div class="panel-head"><h2><?php echo $edit?'Edit meal plan':'Add meal plan';?></h2><?php if($edit):?><a href="meals.php">Cancel</a><?php endif;?></div>
<form class="admin-form" method="post"><?php echo csrf_input();?><input type="hidden" name="action" value="<?php echo $edit?'update':'create';?>"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0);?>">
<label>Destination<select name="destination_id"><option value="0">All destinations (default)</option><?php foreach($destinations as $destination):?><option value="<?php echo (int)$destination['id'];?>" <?php echo (int)($edit['destination_id']??0)===(int)$destination['id']?'selected':'';?>><?php echo e($destination['state_name'].' - '.$destination['name']);?></option><?php endforeach;?></select><small>Defaults are used only where no active destination-specific plans exist.</small></label>
<label>Plan type<select name="plan_type" required><option value="standard" <?php echo ($edit['plan_type']??'premium')==='standard'?'selected':'';?>>Standard - included</option><option value="premium" <?php echo ($edit['plan_type']??'premium')==='premium'?'selected':'';?>>Premium upgrade</option></select></label>
<label>Upgrade / traveller (INR)<input type="number" name="surcharge_per_traveller" min="0" max="1000000" step="0.01" value="<?php echo e($edit['surcharge_per_traveller']??'0.00');?>" required><small>Standard plans must use INR 0.</small></label>
<label class="wide">Meals included<input name="included_meals" maxlength="255" value="<?php echo e($edit['included_meals']??'Breakfast, lunch and dinner');?>" required></label>
<label><span><input type="checkbox" name="supports_veg" value="1" <?php echo !isset($edit)||$edit['supports_veg']?'checked':'';?>> Vegetarian</span></label>
<label><span><input type="checkbox" name="supports_nonveg" value="1" <?php echo !isset($edit)||$edit['supports_nonveg']?'checked':'';?>> Non-vegetarian</span></label>
<label class="wide">Description<textarea name="description" maxlength="2000"><?php echo e($edit['description']??'');?></textarea></label>
<div class="form-actions"><button><i class="fa-solid fa-floppy-disk"></i> Save meal plan</button></div></form></section>
<section class="panel"><div class="panel-head"><h2>Meal-plan directory</h2></div><div class="table-wrap"><table><thead><tr><th>Type</th><th>Destination</th><th>Food options</th><th>Upgrade / traveller</th><th>Bookings</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($mealPlans as $meal):?><tr><td><strong><?php echo e(ucfirst($meal['plan_type']));?></strong><small><?php echo e($meal['included_meals']);?></small></td><td><?php echo e($meal['destination']?:'All destinations');?><?php if($meal['state_name']):?><small><?php echo e($meal['state_name']);?></small><?php endif;?></td><td><?php echo $meal['supports_veg']?'Vegetarian':'';?><?php echo $meal['supports_veg']&&$meal['supports_nonveg']?' / ':'';?><?php echo $meal['supports_nonveg']?'Non-vegetarian':'';?></td><td><?php echo $meal['plan_type']==='standard'?'Included':inr($meal['surcharge_per_traveller']);?></td><td><?php echo (int)$meal['booking_count'];?></td><td><span class="pill"><?php echo e($meal['status']);?></span></td><td><div class="action-row"><a class="icon-btn" href="?edit=<?php echo (int)$meal['id'];?>" title="Edit meal plan"><i class="fa-solid fa-pen"></i></a><form method="post"><?php echo csrf_input();?><input type="hidden" name="id" value="<?php echo (int)$meal['id'];?>"><input type="hidden" name="action" value="<?php echo $meal['status']==='active'?'disable':'enable';?>"><button class="icon-btn" title="<?php echo $meal['status']==='active'?'Disable':'Enable';?> plan"><i class="fa-solid fa-toggle-<?php echo $meal['status']==='active'?'on':'off';?>"></i></button></form><?php if($meal['plan_type']!=='standard'):?><form method="post" onsubmit="return confirm('Delete this unused meal plan?')"><?php echo csrf_input();?><input type="hidden" name="id" value="<?php echo (int)$meal['id'];?>"><input type="hidden" name="action" value="delete"><button class="icon-btn danger-icon" title="Delete meal plan"><i class="fa-solid fa-trash"></i></button></form><?php endif;?></div></td></tr><?php endforeach;?>
</tbody></table></div></section>
<?php include '_footer.php';?>
