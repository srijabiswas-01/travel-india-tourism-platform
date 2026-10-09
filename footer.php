<footer class="footer site-footer">
  <div class="footer-main">
    <section class="footer-brand-block" aria-label="About Travel India">
      <a class="footer-brand" href="main.php"><img src="img/logo-transparent.png?v=1" alt="Travel India emblem" width="54" height="54"><span>Travel <strong>India</strong></span></a>
      <p>Discover thoughtfully planned journeys, trusted stays, and memorable destinations across India.</p>
      <div class="footer-contact">
        <a href="tel:+913324567890"><i class="fa-solid fa-phone" aria-hidden="true"></i><span>+91 033 2456 7890</span></a>
        <a href="mailto:TravelIndia@gmail.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span>TravelIndia@gmail.com</span></a>
      </div>
    </section>

    <nav class="footer-column" aria-label="Explore Travel India">
      <h2>Explore</h2>
      <ul class="footer-nav-list">
        <li><a href="main.php">Home</a></li><li><a href="Package.php">Packages</a></li><li><a href="offers.php">Special offers</a></li><li><a href="about.php">About us</a></li>
      </ul>
    </nav>

    <nav class="footer-column" aria-label="Customer support">
      <h2>Support</h2>
      <ul class="footer-nav-list">
        <li><a href="contactus.php">Contact us</a></li><li><a href="booking.php">Plan a trip</a></li><li><a href="Package.php">Find a destination</a></li>
      </ul>
    </nav>

    <nav class="footer-column" aria-label="Customer account">
      <h2>Account</h2>
      <ul class="footer-nav-list">
        <?php if(isset($_SESSION['login'])): ?>
          <li><a href="profile.php">My profile</a></li><li><a href="my-bookings.php">My trips</a></li><li><a href="logout.php">Log out</a></li>
        <?php else: ?>
          <li><a href="reglog.php">Sign in</a></li><li><a href="reglog.php?mode=register">Create account</a></li>
        <?php endif; ?>
      </ul>
      <div class="footer-social" aria-label="Travel India social media">
        <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
        <a href="https://x.com/" target="_blank" rel="noopener noreferrer" aria-label="X" title="X"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i></a>
        <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" title="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
        <a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" title="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
      </div>
    </nav>
  </div>
  <div class="outer-footer"><span>&copy; <?php echo date('Y'); ?> Travel India. All rights reserved.</span><span>Indian tours priced in INR</span></div>
</footer>
