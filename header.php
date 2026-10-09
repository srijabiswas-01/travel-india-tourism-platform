<?php
$siteRoot = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../' : '';
$headerNotifications=[];$notificationUnread=0;
try{
  require_once __DIR__.'/db.php';require_once __DIR__.'/notifications.php';require_once __DIR__.'/security.php';
  $notificationDb=db_connect();$notificationEmail=$_SESSION['user_email']??null;
  $headerNotifications=visible_notifications($notificationDb,$notificationEmail,8);
  if($notificationEmail)$notificationUnread=unread_notification_count($notificationDb,$notificationEmail);
}catch(Throwable $notificationError){}
?>
<link rel="icon" href="<?php echo $siteRoot; ?>img/favicon.jpg?v=1" type="image/jpeg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?php echo $siteRoot; ?>css/theme.css?v=18">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?php echo $siteRoot; ?>js/theme.js" defer></script>
<script src="<?php echo $siteRoot; ?>js/form-validation.js?v=1" defer></script>
<header class="site-header">
  <nav class="site-nav" aria-label="Main navigation">
    <a class="site-brand" href="<?php echo $siteRoot; ?>main.php" aria-label="Travel India home">
      <img src="<?php echo $siteRoot; ?>img/logo-transparent.png?v=1" alt="Travel India emblem" width="50" height="50">
      <span class="brand-text">Travel <strong>India</strong></span>
    </a>
    <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false"><span></span><span></span><span></span></button>
    <div class="nav-menu">
      <a href="<?php echo $siteRoot; ?>main.php">Home</a><a href="<?php echo $siteRoot; ?>Package.php">Packages</a><a href="<?php echo $siteRoot; ?>offers.php">Offers</a><a href="<?php echo $siteRoot; ?>about.php">About</a><a href="<?php echo $siteRoot; ?>contactus.php">Contact</a>
      <?php if (isset($_SESSION['login'])): ?>
        <a href="<?php echo $siteRoot; ?>profile.php"><i class="fa-solid fa-user" aria-hidden="true"></i> Profile</a><a href="<?php echo $siteRoot; ?>my-bookings.php">My Trips</a>
        <?php include __DIR__.'/notification-menu.php'; ?>
        <button class="theme-toggle" type="button" aria-label="Switch to dark mode" title="Switch to dark mode"><i class="theme-icon fa-solid fa-moon" aria-hidden="true"></i></button>
        <a class="nav-action" href="<?php echo $siteRoot; ?>logout.php">Log out</a>
      <?php else: ?>
        <?php include __DIR__.'/notification-menu.php'; ?>
        <button class="theme-toggle" type="button" aria-label="Switch to dark mode" title="Switch to dark mode"><i class="theme-icon fa-solid fa-moon" aria-hidden="true"></i></button>
        <a class="nav-action" href="<?php echo $siteRoot; ?>reglog.php">Sign in</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash-message" role="status"><?php echo htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash']); ?></div>
<?php endif; ?>
<button class="back-to-top" type="button" aria-label="Back to top" title="Back to top">
  <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</button>
