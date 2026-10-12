<?php require_once 'app_bootstrap.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Travel India</title>
    <link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
    <link rel="stylesheet" type="text/css" href="css/about.css?v=2">

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
                <div class="typewriter-copy">
                    <p>Travel India is a tour planning and reservation portal created to make exploring destinations across India simpler. Travellers can browse packages by state or union territory, compare destinations and included accommodation, discover active offers, and submit booking requests from one convenient place.</p>
                    <p>Registered customers can maintain their profile and family traveller details, provide accessibility or medical-assistance information, review trip pricing, and track booking progress through My Trips. Important booking confirmations, updates, and announcements are delivered through the notification menu.</p>
                    <p>Our administration team manages destinations, packages, accommodation upgrades, transport categories, temporary offers, customer enquiries, payments, and booking workflows through the Travel India MIS. Every booking request is reviewed before confirmation so that travel dates, traveller requirements, pricing, and services can be checked carefully.</p>
                </div>
        </div>  
                <div class="slide hi-slide">
                    <h4>Our Gallery</h4>
                    <button class="hi-prev" type="button" aria-label="Previous gallery image"></button>
                    <button class="hi-next" type="button" aria-label="Next gallery image"></button>
                    <ul aria-label="Travel India destination gallery">
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
                <script>
                jQuery(function ($) {
                    const desktop = window.matchMedia('(min-width: 901px)');
                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
                    const gallery = document.querySelector('.hi-slide');
                    if (desktop.matches) { $('.slide').hiSlide(); return; }
                    const track = gallery.querySelector('ul');
                    const slides = [...track.querySelectorAll('li')];
                    const previous = gallery.querySelector('.hi-prev');
                    const next = gallery.querySelector('.hi-next');
                    let index = 0;
                    let timer = null;
                    let scrollTimer = null;
                    const showSlide = (newIndex, smooth = true) => {
                        index = (newIndex + slides.length) % slides.length;
                        track.scrollTo({ left: slides[index].offsetLeft, behavior: smooth && !reducedMotion.matches ? 'smooth' : 'auto' });
                    };
                    const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
                    const start = () => { stop(); if (!reducedMotion.matches) timer = window.setInterval(() => showSlide(index + 1), 3500); };
                    previous.addEventListener('click', () => { showSlide(index - 1); start(); });
                    next.addEventListener('click', () => { showSlide(index + 1); start(); });
                    track.addEventListener('scroll', () => {
                        window.clearTimeout(scrollTimer);
                        scrollTimer = window.setTimeout(() => {
                            index = slides.reduce((nearest, slide, position) => Math.abs(slide.offsetLeft - track.scrollLeft) < Math.abs(slides[nearest].offsetLeft - track.scrollLeft) ? position : nearest, 0);
                        }, 100);
                    }, { passive: true });
                    track.addEventListener('pointerdown', stop);
                    track.addEventListener('pointerup', start);
                    track.addEventListener('pointercancel', start);
                    gallery.addEventListener('focusin', stop);
                    gallery.addEventListener('focusout', start);
                    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
                    start();
                });
                </script>

        
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
