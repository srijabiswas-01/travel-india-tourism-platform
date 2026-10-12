<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'offers.php';
$states = [];
$homeOffers = [];
try { $db=db_connect(); $result=$db->query("SELECT * FROM states WHERE status='active' ORDER BY name"); while($row=$result->fetch_assoc())$states[]=$row; } catch(Throwable $error) {}
try {
    $db ??= db_connect();
    $offerResult = $db->query("SELECT o.*,
        (SELECT COUNT(DISTINCT op_count.package_id) FROM offer_packages op_count JOIN packages p_count ON p_count.id=op_count.package_id AND p_count.status='published' WHERE op_count.offer_id=o.id) package_count,
        (SELECT p_image.id FROM offer_packages op_image JOIN packages p_image ON p_image.id=op_image.package_id AND p_image.status='published' WHERE op_image.offer_id=o.id ORDER BY p_image.name LIMIT 1) package_id,
        (SELECT p_background.image FROM offer_packages op_background JOIN packages p_background ON p_background.id=op_background.package_id AND p_background.status='published' WHERE op_background.offer_id=o.id AND p_background.image IS NOT NULL AND p_background.image<>'' ORDER BY p_background.name LIMIT 1) background_image,
        COALESCE(NULLIF(o.image,''),(SELECT p_image.image FROM offer_packages op_image JOIN packages p_image ON p_image.id=op_image.package_id AND p_image.status='published' WHERE op_image.offer_id=o.id ORDER BY p_image.name LIMIT 1)) display_image
        FROM offers o
        WHERE o.status='active' AND o.booking_end_at>=NOW()
        AND EXISTS (SELECT 1 FROM offer_packages op_live JOIN packages p_live ON p_live.id=op_live.package_id AND p_live.status='published' WHERE op_live.offer_id=o.id)
        AND (o.usage_limit IS NULL OR (SELECT COUNT(*) FROM offer_redemptions r JOIN bookings b ON b.id=r.booking_id WHERE r.offer_id=o.id AND b.status NOT IN ('cancelled','rejected'))<o.usage_limit)
        ORDER BY CASE WHEN o.booking_start_at<=NOW() THEN 0 ELSE 1 END,
        CASE WHEN o.booking_start_at<=NOW() THEN o.booking_end_at ELSE o.booking_start_at END,
        o.is_featured DESC,o.priority DESC LIMIT 3");
    while ($offerResult && $offer = $offerResult->fetch_assoc()) $homeOffers[] = $offer;
} catch (Throwable $error) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Travel India</title>
    <link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sample.css">
    <link rel="stylesheet" href="css/home-offers.css?v=13">
</head>
<body>
<?php include 'header.php'; ?>
<div class="header">
    <video autoplay loop class="back-vedio" muted playsinline><source src="media/bgvedio.mp4" type="video/mp4"></video>
    <div class="content"><h1>TRAVEL INDIA</h1></div>
</div>
<div class="space"></div>
<div class="heading"><h1>... TRAVEL INDIA PACKAGES ...</h1></div>
<main class="home-main">
<?php foreach($states as $state): ?>
<a href="Package.php?state=<?php echo e($state['slug']); ?>">
    <div class="box">
        <img class="img" src="<?php echo e($state['image']); ?>" alt="Tourism in <?php echo e($state['name']); ?>">
        <div class="text"><?php echo e($state['name']); ?><?php echo $state['type']==='union_territory'?' (UT)':''; ?></div>
    </div>
</a>
<?php endforeach; ?>
<div class="space"></div>
<?php if ($homeOffers): ?>
<section class="home-offers home-offers-simple" aria-labelledby="home-offers-title">
    <div class="home-offers-head"><div><span>Limited-time journeys</span><h2 id="home-offers-title">Special Offers</h2></div></div>
    <div class="home-offers-stage">
        <div class="home-offers-list" style="--offer-columns:<?php echo min(3,count($homeOffers));?>">
        <?php foreach ($homeOffers as $offer): $offerUrl='offers.php?offer_id='.(int)$offer['id']; ?>
            <article class="home-offer-card">
                <a class="home-offer-image" href="<?php echo e($offerUrl); ?>" aria-label="Get offer: <?php echo e($offer['public_heading']); ?>">
                    <img src="<?php echo e($offer['display_image']); ?>" alt="<?php echo e($offer['public_heading']); ?>" loading="lazy">
                </a>
            </article>
        <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<div class="service-section">
    <div class="inner-width">
        <h1 class="section-title">Our Services</h1><div class="border"></div>
        <div class="service-container">
            <div class="service-box"><div class="service-icon"><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i></div><div class="service-title">Curated Travel Packages</div><div class="service-desc">Choose thoughtfully planned journeys for families, couples, groups, and solo travellers. Each package combines memorable destinations, practical itineraries, and flexible options for different budgets.</div></div>
            <div class="service-box"><div class="service-icon"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></div><div class="service-title">Secure Payments</div><div class="service-desc">Book with confidence through a clear and dependable payment process. Your booking details remain protected, and every confirmed transaction is handled with transparency.</div></div>
            <div class="service-box"><div class="service-icon"><i class="fa-solid fa-hotel" aria-hidden="true"></i></div><div class="service-title">Hotels &amp; Transportation</div><div class="service-desc">Travel comfortably with carefully selected stays and convenient transport options. We help coordinate your accommodation and local travel for a smooth experience from arrival to departure.</div></div>
        </div>
    </div>
</div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
