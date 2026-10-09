<?php
require_once 'app_bootstrap.php';
require_once 'security.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Contact Us | Travel India</title>
    <link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
    <link rel="stylesheet" href="css/sample.css">
</head>
<body class="contact-page">
<?php include 'header.php'; ?>
<main class="contact-section">
    <div class="contact-shell">
        <aside class="contactinfo"><div>
            <h2>Contact Info</h2>
            <ul class="info">
                <li><span><img src="img/location.png" alt=""></span><span><i>658 Salt Lake City Block Sector 1, Kolkata-700064</i></span></li>
                <li><span><img src="img/call.png" alt=""></span><span><i>+91 03324567890</i></span></li>
                <li><span><img src="img/mail.png" alt=""></span><span><i>TravelIndia@gmail.com</i></span></li>
            </ul>
            <ul class="map"><li><a href="https://www.google.com/maps/place/Travel+India/@22.5499067,88.3484806,13z/data=!4m9!1m2!2m1!1smap+of+travelIndia!3m5!1s0x3a027701d02af9b9:0x4e97f5e63470452b!8m2!3d22.5510036!4d88.3604903!15sChJtYXAgb2YgdHJhdmVsSW5kaWGSAQ10cmF2ZWxfYWdlbmN5"><img src="img/map.jpg" alt="Travel India location map"></a></li></ul>
            <nav class="contact-networks" aria-label="Travel India social media">
                <a class="network-link network-facebook" href="#" aria-label="Facebook" title="Facebook"></a><a class="network-link network-x" href="#" aria-label="X (Twitter)" title="X (Twitter)"></a><a class="network-link network-instagram" href="#" aria-label="Instagram" title="Instagram"></a><a class="network-link network-linkedin" href="#" aria-label="LinkedIn" title="LinkedIn"></a>
            </nav>
        </div></aside>
        <div class="contactForm">
            <h2>Send Message/Query</h2>
            <form id="form_id" action="contactins.php" method="post">
                <?php echo csrf_input(); ?>
                <div class="formBox">
                    <div class="inputBox w50"><input type="text" name="fname" required><span>First Name</span></div>
                    <div class="inputBox w50"><input type="text" name="lname" required><span>Last Name</span></div>
                    <div class="inputBox w50"><input type="email" id="email" name="email" required><span>Email Address</span></div>
                    <div class="inputBox w50"><input type="text" name="sub" required><span>Subject of this message</span></div>
                    <div class="inputBox w100"><textarea name="message" required></textarea><span>Message/Query</span></div>
                    <div class="inputBox w100"><input type="submit" value="Send"></div>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
