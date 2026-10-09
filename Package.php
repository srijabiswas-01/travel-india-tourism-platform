<?php
require_once 'app_bootstrap.php';
require_once 'db.php';
require_once 'security.php';
require_once 'offers.php';
$packages = [];
$states = [];
$selectedState = trim($_GET['state'] ?? '');
$stateName = '';
$loadError = '';
try {
    $db = db_connect();
    ensure_app_schema($db);
    $stateResult = $db->query("SELECT name,slug FROM states WHERE status='active' ORDER BY name");
    while ($stateResult && $state = $stateResult->fetch_assoc()) { $states[]=$state; if($state['slug']===$selectedState)$stateName=$state['name']; }
    if ($selectedState !== '') {
        $statement=$db->prepare("SELECT p.*,s.name state_name FROM packages p JOIN states s ON s.id=p.state_id WHERE p.status='published' AND s.slug=? ORDER BY p.name");
        $statement->bind_param('s',$selectedState); $statement->execute(); $result=$statement->get_result();
    } else {
        $result=$db->query("SELECT p.*,s.name state_name FROM packages p JOIN states s ON s.id=p.state_id WHERE p.status='published' ORDER BY s.name,p.name");
    }
    while ($result && $row = $result->fetch_assoc()) {
        $row['active_offer']=null;$rating=$db->execute_query("SELECT AVG(package_rating) avg_rating,COUNT(*) review_count FROM booking_reviews WHERE package_id=? AND status='published'",[(int)$row['id']])->fetch_assoc();$row['avg_rating']=(float)($rating['avg_rating']??0);$row['review_count']=(int)($rating['review_count']??0);
        foreach(eligible_offers($db,(int)$row['id']) as $candidate){$travellers=max(1,(int)$candidate['minimum_travellers']);$candidate['display_discount']=offer_discount_amount($candidate,(float)$row['price'],$travellers)/$travellers;if($candidate['display_discount']>0&&(!$row['active_offer']||$candidate['display_discount']>$row['active_offer']['display_discount']))$row['active_offer']=$candidate;}
        $packages[]=$row;
    }
} catch (Throwable $error) { $loadError = 'Packages are temporarily unavailable. Please try again shortly.'; }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tour Packages | Travel India</title><link rel="icon" href="img/favicon.jpg?v=1" type="image/jpeg">
<link rel="stylesheet" href="css/package.css?v=2"><link rel="stylesheet" href="css/sample.css">
</head><body class="package-page">
<?php include 'header.php'; ?>
<style id="package-critical-layout">
.package-page .feature{min-height:calc(100vh - 64px)!important;height:auto!important;padding:0 20px 64px!important}
.package-page .feature>.heading{margin:0!important;padding:28px 20px 12px!important;text-align:center!important;font:800 clamp(2rem,5vw,3.25rem)/1.15 Inter,"Segoe UI",sans-serif!important;letter-spacing:0!important}
.package-page .feature>.heading span{font:inherit!important;color:var(--brand,#0d8d82)!important}
.package-page .package-filter{width:min(680px,calc(100% - 20px))!important;margin:8px auto 28px!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:12px!important}
.package-page .package-filter-controls{display:flex!important;align-items:center!important;gap:9px!important}
.package-page .package-grid{width:min(1240px,100%)!important;margin:0 auto!important;display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;align-items:stretch!important;gap:28px!important}
.package-page .package-grid .package-card{width:100%!important;min-width:0!important;height:auto!important;margin:0!important;display:flex!important;flex-direction:column!important;overflow:hidden!important;position:relative!important;border:1px solid var(--border,#d8e0e2)!important;border-radius:8px!important;background:var(--surface,#fff)!important}
.package-page .package-grid .package-card-image{display:block!important;width:100%!important;height:auto!important;aspect-ratio:16/9!important;object-fit:cover!important}
.package-page .package-grid .package-price{position:absolute!important;z-index:2!important;top:12px!important;left:12px!important;right:auto!important;width:auto!important;min-width:0!important;height:auto!important;margin:0!important;padding:8px 11px!important;display:inline-flex!important;align-items:baseline!important;gap:7px!important;border-radius:5px!important;background:var(--accent,#f5aa3c)!important;color:#172126!important;font-weight:800!important;line-height:1!important;white-space:nowrap!important}
.package-page .package-grid .package-price small{display:inline!important;margin:0!important;color:#5f4b25!important;font-size:.72rem!important;line-height:1!important;text-decoration:line-through!important}
.package-page .package-grid .package-price .current-price{display:inline!important;font-size:1rem!important;line-height:1!important}
.package-page .package-grid .content{min-height:330px!important;padding:22px 20px 26px!important;display:flex!important;flex:1!important;flex-direction:column!important;text-align:left!important}
.package-page .package-grid .package-offer{position:static!important;align-self:flex-start!important;margin:0 0 12px!important;padding:6px 9px!important;display:inline-flex!important;border-radius:4px!important;background:#fff0d9!important;color:#8b4f0b!important;font-size:.76rem!important;font-weight:800!important;line-height:1.3!important;text-decoration:none!important}
.package-page .package-grid .title{margin:0 0 8px!important;line-height:1.12!important}
.package-page .package-grid .content .btn{width:100%!important;min-height:44px!important;height:auto!important;margin:18px 0 0!important;margin-top:auto!important;display:inline-flex!important;align-items:center!important;justify-content:center!important}
@media(max-width:980px){.package-page .package-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
@media(max-width:620px){.package-page .feature{padding-inline:14px!important}.package-page .package-filter{align-items:stretch!important;flex-direction:column!important}.package-page .package-filter-controls{width:100%!important}.package-page .package-filter select{min-width:0!important;flex:1!important}.package-page .package-grid{grid-template-columns:1fr!important;gap:18px!important}.package-page .package-grid .content{min-height:0!important}}
</style>
<section class="feature" id="feature"><h1 class="heading"><?php echo $stateName?e($stateName).' ':''; ?><span>T</span>our <span>P</span>ackages</h1>
<form class="package-filter" method="get"><label for="state-filter">Explore by state or union territory</label><div class="package-filter-controls"><select id="state-filter" name="state" onchange="this.form.submit()"><option value="">All India</option><?php foreach($states as $state):?><option value="<?php echo e($state['slug']);?>" <?php echo $selectedState===$state['slug']?'selected':'';?>><?php echo e($state['name']);?></option><?php endforeach;?></select><a class="package-filter-reset<?php echo $selectedState===''?' is-disabled':'';?>" href="Package.php" aria-label="Reset state filter" title="Reset state filter" <?php echo $selectedState===''?'aria-disabled="true"':'';?>><i class="fa-solid fa-rotate-left" aria-hidden="true"></i><span>Reset</span></a></div></form>
<?php if ($loadError): ?><div class="flash-message"><?php echo e($loadError); ?></div><?php endif; ?>
<div class="card-container package-grid">
<?php foreach ($packages as $package): ?>
<article class="card package-card">
  <span class="price package-price"><?php if($package['active_offer']):?><small><?php echo inr($package['price']);?></small><span class="current-price"><?php echo inr(max(0,(float)$package['price']-$package['active_offer']['display_discount']));?></span><?php else:?><span class="current-price"><?php echo inr($package['price']); ?></span><?php endif;?></span>
  <img class="package-card-image" src="<?php echo e($package['image']); ?>" alt="<?php echo e($package['name']); ?>">
  <div class="content"><?php if($package['active_offer']):?><a class="package-offer" href="offers.php"><?php echo e(offer_label($package['active_offer']));?> · ends <?php echo e(date('d M',strtotime($package['active_offer']['booking_end_at'])));?></a><?php endif;?><h3 class="title"><?php echo e(strtoupper($package['name'])); ?></h3><span class="package-state"><?php echo e($package['state_name']); ?></span>
    <p><?php echo e($package['description']); ?></p>
    <?php if($package['review_count']):?><p class="package-state"><i class="fa-solid fa-star"></i> <?php echo number_format((float)$package['avg_rating'],1);?>/5 from <?php echo (int)$package['review_count'];?> review<?php echo (int)$package['review_count']===1?'':'s';?></p><?php endif;?><div class="stars" aria-label="<?php echo (int)$package['hotel_rating']; ?> star accommodation">
      <?php for ($i=1; $i<=5; $i++): ?><span class="fa fa-star<?php echo $i <= (int)$package['hotel_rating'] ? ' checked' : ''; ?>"></span><?php endfor; ?>
    </div>
    <a href="View.php?id=<?php echo (int)$package['id']; ?>" class="btn">View Details</a>
  </div>
</article>
<?php endforeach; ?>
<?php if(!$loadError && !$packages):?><div class="empty-packages"><h2>Packages coming soon</h2><p>We are preparing curated journeys for <?php echo e($stateName?:'this region'); ?>.</p><a href="Package.php">View all available packages</a></div><?php endif;?>
</div></section>
<?php include 'footer.php'; ?>
<script>if('scrollRestoration' in history){history.scrollRestoration='manual'}if(!location.hash){scrollTo(0,0)}</script>
</body></html>
