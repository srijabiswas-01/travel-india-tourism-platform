<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$package = null;
try {
    $db = db_connect();
    if ($id) { $statement = $db->prepare("SELECT p.*,s.name state_name,(SELECT AVG(package_rating) FROM booking_reviews r WHERE r.package_id=p.id AND r.status='published') avg_rating,(SELECT COUNT(*) FROM booking_reviews r WHERE r.package_id=p.id AND r.status='published') review_count FROM packages p JOIN states s ON s.id=p.state_id WHERE p.id = ? AND p.status = 'published'"); $statement->bind_param('i', $id); $statement->execute(); $package = $statement->get_result()->fetch_assoc(); }
} catch (Throwable $error) {}
if (!$package) redirect_with_message('Package.php', 'That package is not available.');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo e($package['name']); ?> | Travel India</title><link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg"><link rel="stylesheet" href="css/View.css"></head><body>
<?php include 'header.php'; ?>
<section id="view-section"><div class="container"><h2><?php echo e($package['name']); ?> Tour</h2><div class="view-form">
<div class="book"><img src="<?php echo e($package['image']); ?>" alt="<?php echo e($package['name']); ?>">
<li>Hotel rating: <?php for ($i=1;$i<=5;$i++): ?><span class="fa fa-star<?php echo $i <= (int)$package['hotel_rating'] ? ' checked' : ''; ?>"></span><?php endfor; ?></li>
<?php if((int)$package['review_count']>0):?><li>Traveller rating: <?php echo number_format((float)$package['avg_rating'],1);?>/5 from <?php echo (int)$package['review_count'];?> review<?php echo (int)$package['review_count']===1?'':'s';?></li><?php endif;?><li>From <?php echo inr($package['price']); ?> per traveller</li>
<a href="booking.php?package_id=<?php echo (int)$package['id']; ?>" class="submit">Book Now</a></div>
<div class="description"><h3>Description</h3><p><?php echo e($package['description']); ?></p>
<li>Duration: <?php echo (int)$package['duration_days']; ?> days</li><li>State / UT: <?php echo e($package['state_name']); ?></li><li>Destination: <?php echo e($package['destination']); ?></li><li>Transportation: <?php echo e($package['transport']); ?></li>
</div></div></div></section>
<?php include 'footer.php'; ?></body></html>
