<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'offers.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$offerId = filter_input(INPUT_GET, 'offer_id', FILTER_VALIDATE_INT) ?: 0;
$offerCode = trim($_GET['offer_code'] ?? '');
$package = null;$selectedOffer=null;
try {
    $db = db_connect();
    if ($id) { $statement = $db->prepare("SELECT p.*,s.name state_name,(SELECT AVG(package_rating) FROM booking_reviews r WHERE r.package_id=p.id AND r.status='published') avg_rating,(SELECT COUNT(*) FROM booking_reviews r WHERE r.package_id=p.id AND r.status='published') review_count FROM packages p JOIN states s ON s.id=p.state_id WHERE p.id = ? AND p.status = 'published'"); $statement->bind_param('i', $id); $statement->execute(); $package = $statement->get_result()->fetch_assoc(); }
    if($package&&$offerId){$selectedOffer=$db->execute_query("SELECT o.* FROM offers o JOIN offer_packages op ON op.offer_id=o.id WHERE o.id=? AND op.package_id=? AND o.status='active' AND NOW() BETWEEN o.booking_start_at AND o.booking_end_at LIMIT 1",[$offerId,(int)$package['id']])->fetch_assoc();if($selectedOffer&&$selectedOffer['code']&&strcasecmp($offerCode,(string)$selectedOffer['code'])!==0)$selectedOffer=null;}
} catch (Throwable $error) {}
if (!$package) redirect_with_message('Package.php', 'That package is not available.');
$visitPlaces=array_values(array_filter(array_map('trim',explode(',',(string)($package['visit_places']??'')))));
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo e($package['name']); ?> | Travel India</title><link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg"><link rel="stylesheet" href="css/View.css?v=3"></head><body>
<?php include 'header.php'; ?>
<section id="view-section"><div class="container"><a class="view-back" href="Package.php"><i class="fa-solid fa-arrow-left"></i> Back to Packages</a><h2><?php echo e($package['name']); ?> Tour</h2><div class="view-form">
<div class="book"><img src="<?php echo e($package['image']); ?>" alt="<?php echo e($package['name']); ?>">
<li>Included accommodation: <?php for ($i=1;$i<=5;$i++): ?><span class="fa fa-star<?php echo $i <= (int)$package['hotel_rating'] ? ' checked' : ''; ?>"></span><?php endfor; ?></li>
<?php if((int)$package['review_count']>0):?><li>Traveller rating: <?php echo number_format((float)$package['avg_rating'],1);?>/5 from <?php echo (int)$package['review_count'];?> review<?php echo (int)$package['review_count']===1?'':'s';?></li><?php endif;?><li>From <?php echo inr($package['price']); ?> per traveller</li>
<?php if($selectedOffer):$previewTravellers=max(1,(int)$selectedOffer['minimum_travellers']);$previewBase=(float)$package['price']*$previewTravellers;$previewDiscount=offer_discount_amount($selectedOffer,(float)$package['price'],$previewTravellers);?><div class="view-offer"><span><?php echo e(offer_label($selectedOffer));?></span><strong><?php echo e($selectedOffer['public_heading']);?></strong><p><del><?php echo inr($previewBase);?></del> <b><?php echo inr($previewBase-$previewDiscount);?></b> for <?php echo $previewTravellers;?> traveller<?php echo $previewTravellers===1?'':'s';?></p><small><?php echo $selectedOffer['code']?'Code: '.e($selectedOffer['code']):'Applied automatically';?> · Valid until <?php echo e(date('d M Y',strtotime($selectedOffer['booking_end_at'])));?></small><a href="offers.php?package_id=<?php echo (int)$package['id'];?>&offer_id=<?php echo (int)$selectedOffer['id'];?>">View offer details</a></div><?php endif;?>
<a href="booking.php?package_id=<?php echo (int)$package['id']; ?><?php if($selectedOffer):?>&offer_id=<?php echo (int)$selectedOffer['id'];?><?php echo $selectedOffer['code']?'&offer_code='.urlencode($selectedOffer['code']):'';?><?php endif;?>" class="submit"><?php echo $selectedOffer?'Book with offer':'Book Now';?></a></div>
<div class="description"><h3>Description</h3><p><?php echo e($package['description']); ?></p><?php if($visitPlaces):?><section class="visit-places" aria-labelledby="visit-places-title"><h4 id="visit-places-title">Places you'll visit</h4><div><?php foreach($visitPlaces as $place):?><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i><?php echo e($place);?></span><?php endforeach;?></div></section><?php endif;?>
<li>Duration: <?php echo (int)$package['duration_days']; ?> days</li><li>State / UT: <?php echo e($package['state_name']); ?></li><li>Destination: <?php echo e($package['destination']); ?></li><li>Transportation: <?php echo e($package['transport']); ?></li>
</div></div></div></section>
<?php include 'footer.php'; ?></body></html>
