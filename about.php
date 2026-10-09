<?php require_once 'app_bootstrap.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Travel India</title>
    <link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
    <link rel="stylesheet" type="text/css" href="css/about.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
</head>
<body class="about-page">
    <?php include('header.php'); ?>
    <section class="about">
        <div class="main">

                <div class="about-heading">
                    <h1 class="typewriter-title">About <span>Us</span></h1>
                    <ion-icon name="bookmark-sharp" class="tag" aria-hidden="true"></ion-icon>
                </div>
                <div class="typewriter-copy" data-typewriter-copy>
                    <p>India's longest established travel company Travel India started their journey on 15th March, 2019. We are greatly humbled and honoured to be receiving the President of India Category-I Award by Shri Pranab Mukherjee for Tour Operator, Rest of India and National Tourism Awards 2013-14 Category-III for Outstanding Performance in Tourism Sector and Domestic Tour Operator - Jammu &amp; Kashmir Region.</p>
                    <p>For Travel India Family, you are the centre of everything we do. Thus with every tour, we ensure working tirelessly to meet and outstrip your expectation in an enjoyable and responsible way. We follow continuous research for new products, themes and travel upgrades that help us serve our guests. Whether it is a corporate tour, romantic delight, adventure, or a short break, we have ample tour choices to suit your taste, requirements, and budget.</p>
                    <p>Hurry up and book a holiday with Travel India!</p>
                </div>
        </div>  
                <div class="slide hi-slide">
                    <h4>Our Gallery</h4>
                    <div class="hi-prev" aria-label="Previous gallery image"></div>
                    <div class="hi-next" aria-label="Next gallery image"></div>
                    <ul>
                        <li><img src="img/Delhi.jpg" alt="Delhi destination"></li>
                        <li><img src="img/Gujarat.jpg" alt="Gujarat destination"></li>
                        <li><img src="img/Jammu & Kashmir.jpg" alt="Jammu and Kashmir destination"></li>
                        <li><img src="img/Karnataka.jpg" alt="Karnataka destination"></li>
                        <li><img src="img/Kerala.jpg" alt="Kerala destination"></li>
                        <li><img src="img/Kolkata.jpg" alt="Kolkata destination"></li>
                        <li><img src="img/Madhya Pradesh.jpg" alt="Madhya Pradesh destination"></li>
                        <li><img src="img/Maldives.jpg" alt="Maldives destination"></li>
                        <li><img src="img/Manipur.jpg" alt="Manipur destination"></li>
                        <li><img src="img/Meghalaya.jpg" alt="Meghalaya destination"></li>
                        <li><img src="img/Rajasthan.jpg" alt="Rajasthan destination"></li>
                        <li><img src="img/Sikkim.jpg" alt="Sikkim destination"></li>
                    </ul>
                </div>
                <script src="js/jquery.hislide.js"></script>
                <script>jQuery(function ($) { $('.slide').hiSlide(); });</script>

        
        <div class="wrapper" id="team">
            <h1>Our Team</h1>
            <div class="team">
              <div class="team_member">
                <div class="team_img">
                  <img src="img/payel.jpg" alt="Team_image">
                </div>
                <h3>Payel Bag</h3>
                <p class="role">TEAM LEADER</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/srija.jpg" alt="Team_image">
                </div>
                <h3>Srija Biswas</h3>
                <p class="role">CO - TEAM LEADER</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/bapu.jpeg" alt="Team_image">
                </div>
                <h3>Bapu Yadav</h3>
                <p class="role">RESEARCHER</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/bhikhari.jpg" alt="Team_image">
                </div>
                <h3>Bhikhari Kumar</h3>
                <p class="role">SUPPORT LEAD</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/manjit.jpeg" alt="Team_image">
                </div>
                <h3>Manjit Thakur</h3>
                <p class="role">CODER</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/megha.jpeg" alt="Team_image">
                </div>
                <h3>Megha Das</h3>
                <p class="role">CODER</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/moutusi.jpg" alt="Team_image">
                </div>
                <h3>Moutusi Manna</h3>
                <p class="role">SUPPORT LEAD</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/ruchira.jpg" alt="Team_image">
                </div>
                <h3>Ruchira Bag</h3>
                <p class="role">DESIGNER/CODER</p>
              </div>
              <div class="team_member">
                <div class="team_img">
                  <img src="img/sayoni.jpg" alt="Team_image">
                </div>
                <h3>Sayoni Das</h3>
                <p class="role">DESIGNER/CODER</p>
              </div>
              
              <div class="team_member">
                <div class="team_img">
                  <img src="img/tithi.jpg" alt="Team_image">
                </div>
                <h3>Tithi Dey</h3>
                <p class="role">DESIGNER/CODER</p>
              </div>
          


            </div>
          </div>	
        
    </section>
    <?php include('footer.php'); ?>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
