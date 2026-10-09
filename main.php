<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
$states = [];
try { $db=db_connect(); $result=$db->query("SELECT * FROM states WHERE status='active' ORDER BY name"); while($row=$result->fetch_assoc())$states[]=$row; } catch(Throwable $error) {}
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
</head>
<body>
<?php include 'header.php'; ?>
<div class="header">
    <video autoplay loop class="back-vedio" muted playsinline><source src="media/bgvedio.mp4" type="video/mp4"></video>
    <div class="content"><h1>TRAVEL INDIA</h1></div>
</div>
<div class="space"></div>
<div class="heading"><h1>... TRAVEL INDIA PACKAGES ...</h1></div>
<main>
<?php foreach($states as $state): ?>
<a href="Package.php?state=<?php echo e($state['slug']); ?>">
    <div class="box">
        <img class="img" src="<?php echo e($state['image']); ?>" alt="Tourism in <?php echo e($state['name']); ?>">
        <div class="text"><?php echo e($state['name']); ?><?php echo $state['type']==='union_territory'?' (UT)':''; ?></div>
    </div>
</a>
<?php endforeach; ?>
<div class="space"></div>
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
