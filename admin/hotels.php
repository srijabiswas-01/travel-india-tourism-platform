<?php
require_once '_bootstrap.php';
require_admin_permission('catalog.manage');
$db = admin_db();
ensure_app_schema($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if (in_array($action, ['create', 'update'], true)) {
        $destinationId = (int)($_POST['destination_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Standard');
        $upgradeSurcharge = (float)($_POST['upgrade_surcharge'] ?? 0);
        $address = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (!$destinationId || $name === '' || !in_array($category, ['Budget','Standard','Premium','Luxury','Resort','Homestay'], true) || $upgradeSurcharge < 0 || $upgradeSurcharge > 1000000) {
            redirect_with_message('hotels.php', 'Choose a destination and category, enter a hotel name, and use an upgrade amount from INR 0 to INR 10,00,000.');
        }
        if ($category === 'Standard') $upgradeSurcharge = 0;
        $check = $db->prepare("SELECT id FROM destinations WHERE id=? AND status='active'");
        $check->bind_param('i', $destinationId);
        $check->execute();
        if (!$check->get_result()->fetch_assoc()) redirect_with_message('hotels.php', 'Select an active destination.');

        if ($action === 'create') {
            $statement = $db->prepare('INSERT INTO hotels(destination_id,name,category,address,description,upgrade_surcharge) VALUES(?,?,?,?,?,?)');
            $statement->bind_param('issssd', $destinationId, $name, $category, $address, $description, $upgradeSurcharge);
        } else {
            $statement = $db->prepare('UPDATE hotels SET destination_id=?,name=?,category=?,address=?,description=?,upgrade_surcharge=? WHERE id=?');
            $statement->bind_param('issssdi', $destinationId, $name, $category, $address, $description, $upgradeSurcharge, $id);
        }
        if (!$statement->execute()) redirect_with_message('hotels.php', 'That hotel already exists for this destination.');
        admin_audit($db, $action, 'hotel', (string)($id ?: $db->insert_id), $name);
    } elseif (in_array($action, ['enable', 'disable'], true)) {
        $status = $action === 'enable' ? 'active' : 'disabled';
        $statement = $db->prepare('UPDATE hotels SET status=? WHERE id=?');
        $statement->bind_param('si', $status, $id);
        $statement->execute();
        admin_audit($db, $action, 'hotel', (string)$id);
    } elseif ($action === 'delete') {
        $statement = $db->prepare('SELECT COUNT(*) total FROM bookings WHERE hotel_id=?');
        $statement->bind_param('i', $id);
        $statement->execute();
        if ((int)$statement->get_result()->fetch_assoc()['total']) redirect_with_message('hotels.php', 'This hotel is used by bookings. Disable it instead.');
        $statement = $db->prepare('DELETE FROM hotels WHERE id=?');
        $statement->bind_param('i', $id);
        $statement->execute();
        admin_audit($db, 'delete', 'hotel', (string)$id);
    }
    redirect_with_message('hotels.php', 'Hotel information updated.');
}

$edit = null;
if (!empty($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $statement = $db->prepare('SELECT * FROM hotels WHERE id=?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $edit = $statement->get_result()->fetch_assoc();
}

$destinations = [];
$result = $db->query("SELECT d.id,d.name,s.name state_name FROM destinations d JOIN states s ON s.id=d.state_id WHERE d.status='active' ORDER BY s.name,d.name");
while ($row = $result->fetch_assoc()) $destinations[] = $row;
$pagination = admin_paginate($db, 'SELECT COUNT(*) total FROM hotels');
$hotels = $db->query('SELECT h.*,d.name destination,s.name state_name,(SELECT COUNT(*) FROM bookings b WHERE b.hotel_id=h.id) booking_count,(SELECT AVG(hotel_rating) FROM booking_reviews r WHERE r.hotel_id=h.id AND r.status=\'published\') avg_rating,(SELECT COUNT(*) FROM booking_reviews r WHERE r.hotel_id=h.id AND r.status=\'published\') review_count FROM hotels h JOIN destinations d ON d.id=h.destination_id JOIN states s ON s.id=d.state_id ORDER BY h.updated_at DESC LIMIT '.$pagination['limit'].' OFFSET '.$pagination['offset']);
$pageTitle = 'Hotels';
include '_header.php';
?>
<div class="page-head"><div><span>Accommodation catalog</span><h1>Hotels</h1><p>Create the destination-specific hotel options customers can select while booking.</p></div></div>
<section class="panel form-panel"><div class="panel-head"><h2><?php echo $edit ? 'Edit hotel' : 'Add hotel'; ?></h2><?php if ($edit): ?><a href="hotels.php">Cancel</a><?php endif; ?></div>
<form class="admin-form" method="post"><?php echo csrf_input(); ?><input type="hidden" name="action" value="<?php echo $edit ? 'update' : 'create'; ?>"><input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">
<label>Destination<select name="destination_id" required><option value="">Select destination</option><?php foreach ($destinations as $destination): ?><option value="<?php echo (int)$destination['id']; ?>" <?php echo (int)($edit['destination_id'] ?? 0) === (int)$destination['id'] ? 'selected' : ''; ?>><?php echo e($destination['state_name'].' - '.$destination['name']); ?></option><?php endforeach; ?></select></label>
<label>Hotel name<input name="name" maxlength="180" value="<?php echo e($edit['name'] ?? ''); ?>" required></label>
<label>Category<select name="category" id="hotel-category" required><?php foreach (['Standard','Budget','Premium','Luxury','Resort','Homestay'] as $category): ?><option <?php echo ($edit['category'] ?? 'Standard') === $category ? 'selected' : ''; ?>><?php echo $category; ?></option><?php endforeach; ?></select></label>
<label>Upgrade surcharge (INR)<input id="hotel-upgrade-surcharge" type="number" name="upgrade_surcharge" min="0" max="1000000" step="0.01" value="<?php echo ($edit['category'] ?? 'Standard') === 'Standard' ? '0.00' : e($edit['upgrade_surcharge'] ?? '0.00'); ?>" <?php echo ($edit['category'] ?? 'Standard') === 'Standard' ? 'readonly' : ''; ?>><small id="hotel-surcharge-help">Standard hotels are included in the package price. Other categories can have a per-traveller, per-trip upgrade amount.</small></label>
<label>Address<input name="address" maxlength="500" value="<?php echo e($edit['address'] ?? ''); ?>"></label>
<label class="wide">Description<textarea name="description" maxlength="1000"><?php echo e($edit['description'] ?? ''); ?></textarea></label>
<div class="form-actions"><button><i class="fa-solid fa-floppy-disk"></i><?php echo $edit ? 'Save hotel' : 'Add hotel'; ?></button></div></form></section>
<section class="panel"><div class="panel-head"><h2>Hotel directory</h2><small><?php echo (int)$pagination['total']; ?> records</small></div><div class="table-wrap"><table><thead><tr><th>Hotel</th><th>Destination</th><th>Category</th><th>Rating</th><th>Upgrade / traveller</th><th>Bookings</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php while ($hotel = $hotels->fetch_assoc()): ?><tr><td><strong><?php echo e($hotel['name']); ?></strong><small><?php echo e($hotel['address']); ?></small></td><td><?php echo e($hotel['destination']); ?><small><?php echo e($hotel['state_name']); ?></small></td><td><?php echo e($hotel['category']); ?></td><td><?php echo (int)$hotel['review_count']?number_format((float)$hotel['avg_rating'],1).'/5':'No reviews';?><small><?php echo (int)$hotel['review_count'];?> review(s)</small></td><td><?php echo $hotel['category']==='Standard'?'Included':inr($hotel['upgrade_surcharge']);?></td><td><?php echo (int)$hotel['booking_count']; ?></td><td><span class="pill"><?php echo e($hotel['status']); ?></span></td><td><div class="action-row"><a class="icon-btn" href="?edit=<?php echo (int)$hotel['id']; ?>" title="Edit hotel"><i class="fa-solid fa-pen"></i></a><form method="post"><?php echo csrf_input(); ?><input type="hidden" name="id" value="<?php echo (int)$hotel['id']; ?>"><input type="hidden" name="action" value="<?php echo $hotel['status'] === 'active' ? 'disable' : 'enable'; ?>"><button class="icon-btn status-toggle <?php echo $hotel['status'] === 'active' ? 'is-on' : 'is-off'; ?>" title="<?php echo $hotel['status'] === 'active' ? 'Disable' : 'Enable'; ?> hotel"><i class="fa-solid fa-toggle-<?php echo $hotel['status'] === 'active' ? 'on' : 'off'; ?>"></i></button></form><form method="post" onsubmit="return confirm('Delete this unused hotel?')"><?php echo csrf_input(); ?><input type="hidden" name="id" value="<?php echo (int)$hotel['id']; ?>"><input type="hidden" name="action" value="delete"><button class="icon-btn danger-icon" title="Delete hotel"><i class="fa-solid fa-trash"></i></button></form></div></td></tr><?php endwhile; ?></tbody></table></div><?php admin_pagination($pagination); ?></section>
<script>const hotelCategory=document.querySelector('#hotel-category'),hotelSurcharge=document.querySelector('#hotel-upgrade-surcharge');hotelCategory?.addEventListener('change',()=>{const standard=hotelCategory.value==='Standard';hotelSurcharge.readOnly=standard;if(standard)hotelSurcharge.value='0.00'});</script>
<?php include '_footer.php'; ?>
