<?php
require_once '_bootstrap.php';
require_admin();
$db=admin_db();
ensure_app_schema($db);
$monthly=$db->query("SELECT DATE_FORMAT(b.created_at,'%Y-%m') month,COUNT(*) bookings,SUM(b.total_amount) booked_value,SUM(CASE WHEN EXISTS(SELECT 1 FROM payments pp WHERE pp.booking_id=b.id AND pp.status='paid') THEN 1 ELSE 0 END) paid,SUM(COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.booking_id=b.id AND p.status IN ('paid','refunded')),0)) net_revenue FROM bookings b GROUP BY month ORDER BY month DESC LIMIT 12");
$destinations=$db->query("SELECT b.destination,COUNT(*) bookings,SUM(b.people) travellers,SUM(b.total_amount) booked_value,SUM(COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.booking_id=b.id AND p.status IN ('paid','refunded')),0)) net_revenue FROM bookings b GROUP BY b.destination ORDER BY bookings DESC LIMIT 10");
$themes=$db->query("SELECT trip_theme,COUNT(*) bookings,SUM(people) travellers FROM bookings GROUP BY trip_theme ORDER BY bookings DESC");
$mealPlans=$db->query("SELECT meal_plan_name,COUNT(*) bookings,SUM(people) travellers,SUM(meal_surcharge_total) upgrade_revenue FROM bookings GROUP BY meal_plan_name ORDER BY bookings DESC");
$foodPreferences=$db->query("SELECT food_preference,COUNT(*) travellers,SUM(dietary_notes IS NOT NULL AND dietary_notes<>'') dietary_notes FROM booking_travellers GROUP BY food_preference ORDER BY travellers DESC");
$pageTitle='Reports';
include '_header.php';
?>
<div class="page-head"><div><span>India tourism management information</span><h1>Reports</h1></div><button class="primary-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print report</button></div>
<div class="report-grid">
<section class="panel"><div class="panel-head"><h2>Monthly performance</h2></div><div class="table-wrap"><table><thead><tr><th>Month</th><th>Bookings</th><th>Paid</th><th>Booked value (INR)</th><th>Net revenue (INR)</th></tr></thead><tbody><?php while($r=$monthly->fetch_assoc()):?><tr><td><?php echo e($r['month']);?></td><td><?php echo (int)$r['bookings'];?></td><td><?php echo (int)$r['paid'];?></td><td><?php echo inr($r['booked_value']);?></td><td><?php echo inr($r['net_revenue']);?></td></tr><?php endwhile;?></tbody></table></div></section>
<section class="panel"><div class="panel-head"><h2>Top Indian destinations</h2></div><div class="table-wrap"><table><thead><tr><th>Destination</th><th>Bookings</th><th>Travellers</th><th>Booked value (INR)</th><th>Net revenue (INR)</th></tr></thead><tbody><?php while($r=$destinations->fetch_assoc()):?><tr><td><?php echo e($r['destination']);?></td><td><?php echo (int)$r['bookings'];?></td><td><?php echo (int)$r['travellers'];?></td><td><?php echo inr($r['booked_value']);?></td><td><?php echo inr($r['net_revenue']);?></td></tr><?php endwhile;?></tbody></table></div></section>
<section class="panel"><div class="panel-head"><h2>Bookings by trip theme</h2></div><div class="table-wrap"><table><thead><tr><th>Theme</th><th>Bookings</th><th>Travellers</th></tr></thead><tbody><?php while($r=$themes->fetch_assoc()):?><tr><td><?php echo e(ucwords(str_replace('_',' ',$r['trip_theme'])));?></td><td><?php echo (int)$r['bookings'];?></td><td><?php echo (int)$r['travellers'];?></td></tr><?php endwhile;?></tbody></table></div></section>
<section class="panel"><div class="panel-head"><h2>Meal-plan performance</h2></div><div class="table-wrap"><table><thead><tr><th>Meal plan</th><th>Bookings</th><th>Travellers</th><th>Upgrade revenue</th></tr></thead><tbody><?php while($r=$mealPlans->fetch_assoc()):?><tr><td><?php echo e($r['meal_plan_name']);?></td><td><?php echo (int)$r['bookings'];?></td><td><?php echo (int)$r['travellers'];?></td><td><?php echo inr($r['upgrade_revenue']);?></td></tr><?php endwhile;?></tbody></table></div></section>
<section class="panel"><div class="panel-head"><h2>Traveller food preferences</h2></div><div class="table-wrap"><table><thead><tr><th>Preference</th><th>Travellers</th><th>Dietary notes</th></tr></thead><tbody><?php while($r=$foodPreferences->fetch_assoc()):?><tr><td><?php echo e($r['food_preference']);?></td><td><?php echo (int)$r['travellers'];?></td><td><?php echo (int)$r['dietary_notes'];?></td></tr><?php endwhile;?></tbody></table></div></section>
</div>
<?php include '_footer.php';?>
