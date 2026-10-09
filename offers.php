<?php
require_once __DIR__.'/db.php';

function offer_discount_amount(array $offer, float $unitPrice, int $travellers): float
{
    $base = max(0, $unitPrice * $travellers);
    return offer_discount_amount_for_base($offer, $base, $travellers);
}

function offer_discount_amount_for_base(array $offer, float $base, int $travellers): float
{
    $base = max(0, $base);
    if ($travellers < (int)$offer['minimum_travellers'] || $base < (float)$offer['minimum_booking_amount']) return 0.0;
    $value = (float)$offer['discount_value'];
    if ($offer['discount_type'] === 'percentage') $discount = $base * min(100, $value) / 100;
    elseif ($offer['calculation_scope'] === 'per_traveller') $discount = $value * $travellers;
    else $discount = $value;
    if ($offer['maximum_discount'] !== null && (float)$offer['maximum_discount'] > 0) $discount = min($discount, (float)$offer['maximum_discount']);
    return round(min($base, max(0, $discount)), 2);
}

function eligible_offers(mysqli $db, int $packageId, string $email = '', string $code = ''): array
{
    $code = trim($code);
    $offerFilter = $code === '' ? 'o.is_automatic=1' : '(o.code IS NOT NULL AND UPPER(o.code)=UPPER(?))';
    $sql = "SELECT o.*,(SELECT COUNT(*) FROM offer_redemptions r JOIN bookings b ON b.id=r.booking_id WHERE r.offer_id=o.id AND b.status NOT IN ('cancelled','rejected')) usage_count,(SELECT COUNT(*) FROM offer_redemptions r JOIN bookings b ON b.id=r.booking_id WHERE r.offer_id=o.id AND r.user_email=? AND b.status NOT IN ('cancelled','rejected')) customer_usage FROM offers o JOIN offer_packages op ON op.offer_id=o.id WHERE op.package_id=? AND o.status='active' AND NOW() BETWEEN o.booking_start_at AND o.booking_end_at AND {$offerFilter} HAVING (usage_limit IS NULL OR usage_count<usage_limit) AND (per_customer_limit IS NULL OR customer_usage<per_customer_limit) ORDER BY o.priority DESC,o.discount_value DESC";
    $statement = $db->prepare($sql);
    if ($code === '') $statement->bind_param('si', $email, $packageId);
    else $statement->bind_param('sis', $email, $packageId, $code);
    $statement->execute();
    $offers = [];
    $result = $statement->get_result();
    while ($offer = $result->fetch_assoc()) $offers[] = $offer;
    return $offers;
}

function best_offer(mysqli $db, int $packageId, float $unitPrice, int $travellers, string $email = '', string $code = ''): ?array
{
    return best_offer_for_base($db, $packageId, max(0, $unitPrice * $travellers), $travellers, $email, $code);
}

function best_offer_for_base(mysqli $db, int $packageId, float $base, int $travellers, string $email = '', string $code = ''): ?array
{
    $best = null;
    foreach (eligible_offers($db, $packageId, $email, trim($code)) as $offer) {
        $offer['calculated_discount'] = offer_discount_amount_for_base($offer, $base, $travellers);
        if ($offer['calculated_discount'] <= 0) continue;
        if (!$best || $offer['calculated_discount'] > $best['calculated_discount'] || ($offer['calculated_discount'] === $best['calculated_discount'] && (int)$offer['priority'] > (int)$best['priority'])) $best = $offer;
    }
    return $best;
}

function offer_label(array $offer): string
{
    if ($offer['discount_type'] === 'percentage') return rtrim(rtrim(number_format((float)$offer['discount_value'], 2), '0'), '.').'% off';
    return inr($offer['discount_value']).($offer['calculation_scope'] === 'per_traveller' ? ' off per traveller' : ' off');
}

function offer_runtime_status(array $offer): string
{
    if (in_array($offer['status'], ['draft','paused','archived'], true)) return $offer['status'];
    if (strtotime($offer['booking_start_at']) > time()) return 'scheduled';
    if (strtotime($offer['booking_end_at']) < time()) return 'expired';
    if ($offer['usage_limit'] !== null && isset($offer['redemptions']) && (int)$offer['redemptions'] >= (int)$offer['usage_limit']) return 'exhausted';
    return 'active';
}

if(realpath($_SERVER['SCRIPT_FILENAME']??'')!==__FILE__)return;
$publicOffers=[];$previewId=(int)($_GET['preview']??0);
try{
    $db=db_connect();ensure_app_schema($db);
    if($previewId){require_once __DIR__.'/security.php';if(empty($_SESSION['admin_id'])){$previewId=0;}}
    $where=$previewId?'o.id=?':"o.status='active' AND NOW() BETWEEN o.booking_start_at AND o.booking_end_at AND (o.usage_limit IS NULL OR (SELECT COUNT(*) FROM offer_redemptions rr JOIN bookings bb ON bb.id=rr.booking_id WHERE rr.offer_id=o.id AND bb.status NOT IN ('cancelled','rejected'))<o.usage_limit)";
    $sql="SELECT o.*,p.id package_id,p.name package_name,p.destination,p.price package_price,p.image package_image FROM offers o JOIN offer_packages op ON op.offer_id=o.id JOIN packages p ON p.id=op.package_id AND p.status='published' WHERE {$where} ORDER BY o.is_featured DESC,o.priority DESC,o.booking_end_at";
    $result=$previewId?$db->execute_query($sql,[$previewId]):$db->query($sql);while($row=$result->fetch_assoc())$publicOffers[]=$row;
}catch(Throwable $error){}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Special Offers | Travel India</title><link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg"><link rel="stylesheet" href="css/offers.css?v=2"></head><body class="offers-page"><?php include 'header.php';?>
<main><section class="offers-hero"><span>Limited-time journeys</span><h1>Special Offers</h1><p>Save on selected India tour packages while each promotion is active.</p></section><section class="offers-list" aria-label="Available offers">
<?php foreach($publicOffers as $offer):$travellers=max(1,(int)$offer['minimum_travellers']);$base=(float)$offer['package_price']*$travellers;$discount=offer_discount_amount($offer,(float)$offer['package_price'],$travellers);$total=$base-$discount;?><article class="offer-card"><img src="<?php echo e($offer['image']?:$offer['package_image']);?>" alt="<?php echo e($offer['public_heading']);?>"><div class="offer-card-body"><div class="offer-topline"><span><?php echo e(offer_label($offer));?></span><?php if($offer['is_featured']):?><b>Featured</b><?php endif;?></div><h2><?php echo e($offer['public_heading']);?></h2><p><?php echo e($offer['description']);?></p><h3><?php echo e($offer['package_name']);?></h3><p class="offer-place"><i class="fa-solid fa-location-dot"></i> <?php echo e($offer['destination']);?></p><div class="offer-price"><span><?php echo inr($base);?></span><strong><?php echo inr($total);?></strong><small>for <?php echo $travellers;?> traveller<?php echo $travellers===1?'':'s';?></small></div><ul><li>Valid until <?php echo e(date('d M Y',strtotime($offer['booking_end_at'])));?></li><?php if($offer['minimum_travellers']>1):?><li>Minimum <?php echo (int)$offer['minimum_travellers'];?> travellers</li><?php endif;?><?php if($offer['code']):?><li>Code: <strong><?php echo e($offer['code']);?></strong></li><?php endif;?></ul><?php if($offer['terms']):?><details><summary>Terms and conditions</summary><p><?php echo nl2br(e($offer['terms']));?></p></details><?php endif;?><div class="offer-actions"><a href="View.php?id=<?php echo (int)$offer['package_id'];?>">View package</a><a class="offer-book" href="booking.php?package_id=<?php echo (int)$offer['package_id'];?><?php echo $offer['code']?'&offer_code='.urlencode($offer['code']):'';?>">Book offer</a></div></div></article><?php endforeach;?>
<?php if(!$publicOffers):?><div class="offer-empty"><i class="fa-solid fa-tags"></i><h2>No special offers are available right now</h2><p>Explore our India tour packages and check again soon for seasonal deals and limited-time journeys.</p><a href="Package.php">Explore packages</a></div><?php endif;?></section></main><?php include 'footer.php';?></body></html>
