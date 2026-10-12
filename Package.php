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
$pageSize = 21;
$currentPage = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$totalPackages = 0;
$totalPages = 1;
try {
    $db = db_connect();
    ensure_app_schema($db);
    $stateResult = $db->query("SELECT name,slug FROM states WHERE status='active' ORDER BY name");
    while ($stateResult && $state = $stateResult->fetch_assoc()) { $states[]=$state; if($state['slug']===$selectedState)$stateName=$state['name']; }
    if ($selectedState !== '') {
        $countStatement=$db->prepare("SELECT COUNT(*) total FROM packages p JOIN states s ON s.id=p.state_id WHERE p.status='published' AND s.slug=?");
        $countStatement->bind_param('s',$selectedState);$countStatement->execute();$totalPackages=(int)$countStatement->get_result()->fetch_assoc()['total'];
    } else {
        $totalPackages=(int)$db->query("SELECT COUNT(*) total FROM packages WHERE status='published'")->fetch_assoc()['total'];
    }
    $totalPages=max(1,(int)ceil($totalPackages/$pageSize));$currentPage=min($currentPage,$totalPages);$offset=($currentPage-1)*$pageSize;
    if ($selectedState !== '') {
        $statement=$db->prepare("SELECT p.*,s.name state_name FROM packages p JOIN states s ON s.id=p.state_id WHERE p.status='published' AND s.slug=? ORDER BY p.name LIMIT ? OFFSET ?");
        $statement->bind_param('sii',$selectedState,$pageSize,$offset);$statement->execute();$result=$statement->get_result();
    } else {
        $statement=$db->prepare("SELECT p.*,s.name state_name FROM packages p JOIN states s ON s.id=p.state_id WHERE p.status='published' ORDER BY p.name,s.name LIMIT ? OFFSET ?");
        $statement->bind_param('ii',$pageSize,$offset);$statement->execute();$result=$statement->get_result();
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
<link rel="stylesheet" href="css/package.css?v=5"><link rel="stylesheet" href="css/sample.css">
</head><body class="package-page">
<?php include 'header.php'; ?>
<style id="package-critical-layout">
.package-page .feature{min-height:calc(100vh - 64px)!important;height:auto!important;padding:0 20px 64px!important}
.package-page .feature>.heading{margin:0!important;padding:28px 20px 12px!important;text-align:center!important;font:800 clamp(2rem,5vw,3.25rem)/1.15 Inter,"Segoe UI",sans-serif!important;letter-spacing:0!important}
.package-page .feature>.heading span{font:inherit!important;color:var(--brand,#0d8d82)!important}
.package-page .package-filter{box-sizing:border-box!important;width:min(1080px,calc(100% - 20px))!important;margin:8px auto 28px!important;display:grid!important;grid-template-columns:minmax(180px,220px) minmax(0,1fr)!important;align-items:center!important;gap:12px 18px!important}
.package-page .package-filter-controls{min-width:0!important;display:grid!important;grid-template-columns:minmax(180px,.85fr) minmax(280px,1.25fr) max-content max-content!important;align-items:center!important;gap:9px!important}.package-page .package-filter select{width:100%!important;min-width:0!important;max-width:100%!important}
.package-page .package-search-group{display:flex!important;min-width:0!important}.package-page .package-search{flex:1 1 auto!important;width:auto!important;min-width:0!important;height:42px!important;padding:0 12px!important;border:1px solid var(--border,#d8e0e2)!important;border-radius:6px!important;background:var(--surface,#fff)!important;color:var(--text,#172326)!important}.package-page .package-offers-toggle{min-height:42px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:8px!important;padding:0 14px!important;border:1px solid var(--border,#d8e0e2)!important;border-radius:6px!important;background:var(--surface,#fff)!important;color:var(--brand,#0d8d82)!important;font-weight:800!important;white-space:nowrap!important}.package-page .package-offers-toggle:hover,.package-page .package-offers-toggle.is-active{border-color:var(--brand,#0d8d82)!important;background:var(--brand,#0d8d82)!important;color:#fff!important}.package-page .package-no-results{grid-column:1/-1;padding:44px 24px;text-align:center;border:1px solid rgba(255,255,255,.22);border-radius:8px;background:var(--surface,#fff);color:var(--text,#172326)}
.package-page .package-grid{width:min(1240px,100%)!important;margin:0 auto!important;display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;align-items:stretch!important;gap:28px!important}
.package-page .package-grid .package-card{width:100%!important;min-width:0!important;height:auto!important;margin:0!important;display:flex!important;flex-direction:column!important;overflow:hidden!important;position:relative!important;border:1px solid var(--border,#d8e0e2)!important;border-radius:8px!important;background:var(--surface,#fff)!important}
.package-page .package-grid .package-card[hidden]{display:none!important}
.package-page .package-grid .package-card-image{display:block!important;width:100%!important;height:auto!important;aspect-ratio:16/9!important;object-fit:cover!important}
.package-page .package-grid .package-price{position:absolute!important;z-index:2!important;top:12px!important;left:12px!important;right:auto!important;width:auto!important;min-width:0!important;height:auto!important;margin:0!important;padding:8px 11px!important;display:inline-flex!important;align-items:baseline!important;gap:7px!important;border-radius:5px!important;background:var(--accent,#f5aa3c)!important;color:#172126!important;font-weight:800!important;line-height:1!important;white-space:nowrap!important}
.package-page .package-grid .package-price small{display:inline!important;margin:0!important;color:#5f4b25!important;font-size:.72rem!important;line-height:1!important;text-decoration:line-through!important}
.package-page .package-grid .package-price .current-price{display:inline!important;font-size:1rem!important;line-height:1!important}
.package-page .package-grid .content{min-height:330px!important;padding:22px 20px 26px!important;display:flex!important;flex:1!important;flex-direction:column!important;text-align:left!important}
.package-page .package-grid .package-offer{position:static!important;align-self:flex-start!important;margin:0 0 12px!important;padding:6px 9px!important;display:inline-flex!important;border-radius:4px!important;background:#fff0d9!important;color:#8b4f0b!important;font-size:.76rem!important;font-weight:800!important;line-height:1.3!important;text-decoration:none!important}
.package-page .package-grid .title{margin:0 0 8px!important;line-height:1.12!important}
.package-page .package-grid .content .btn{width:100%!important;min-height:44px!important;height:auto!important;margin:18px 0 0!important;margin-top:auto!important;display:inline-flex!important;align-items:center!important;justify-content:center!important}
.package-page .package-pagination{width:min(1240px,100%);margin:30px auto 0;display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:7px}.package-page .package-pagination>a,.package-page .package-pagination>span{min-width:42px;min-height:42px;box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid var(--border,#d8e0e2);border-radius:5px;background:var(--surface,#fff);color:var(--text,#172326);font-weight:800;text-decoration:none}.package-page .package-pagination>a:hover,.package-page .package-pagination>a:focus-visible,.package-page .package-pagination>.is-current{border-color:var(--brand,#0d8d82);background:var(--brand,#0d8d82);color:#fff}.package-page .package-pagination>.is-disabled{opacity:.5}
@media(max-width:980px){.package-page .package-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
@media(max-width:1100px){.package-page .package-filter{grid-template-columns:1fr!important}.package-page .package-filter-controls{grid-template-columns:minmax(180px,.85fr) minmax(280px,1.25fr) max-content max-content!important}}@media(max-width:760px){.package-page .package-filter-controls{grid-template-columns:1fr 1fr!important}.package-page .package-search-group{grid-column:1/-1!important;grid-row:1!important}}@media(max-width:620px){.package-page .feature{padding-inline:14px!important}.package-page .package-filter{align-items:stretch!important}.package-page .package-filter-controls{width:100%!important;grid-template-columns:1fr!important}.package-page .package-filter select{width:100%!important;min-width:0!important}.package-page .package-search-group{grid-column:auto!important;grid-row:auto!important}.package-page .package-search-button,.package-page .package-offers-toggle,.package-page .package-filter-reset{width:100%!important}.package-page .package-grid{grid-template-columns:1fr!important;gap:18px!important}.package-page .package-grid .content{min-height:0!important}}
</style>
<section class="feature" id="feature"><h1 class="heading"><?php echo $stateName?e($stateName).' ':''; ?><span>T</span>our <span>P</span>ackages</h1>
<form class="package-filter" method="get" id="package-filter"><label for="state-filter">Find your destination</label><div class="package-filter-controls"><select id="state-filter" name="state" aria-label="Filter by state or union territory" onchange="this.form.submit()"><option value="">All India</option><?php foreach($states as $state):?><option value="<?php echo e($state['slug']);?>" <?php echo $selectedState===$state['slug']?'selected':'';?>><?php echo e($state['name']);?></option><?php endforeach;?></select><div class="package-search-group"><input class="package-search" id="destination-search" type="search" placeholder="Search destination by name" autocomplete="off" aria-label="Search destinations by name"></div><button class="package-offers-toggle" type="button" id="offers-only" aria-pressed="false"><i class="fa-solid fa-tag" aria-hidden="true"></i><span>Offers only</span></button><a class="package-filter-reset<?php echo $selectedState===''?' is-disabled':'';?>" href="Package.php" aria-label="Reset all package filters" title="Reset filters" <?php echo $selectedState===''?'aria-disabled="true"':'';?>><i class="fa-solid fa-rotate-left" aria-hidden="true"></i><span>Reset</span></a></div></form>
<?php if ($loadError): ?><div class="flash-message"><?php echo e($loadError); ?></div><?php endif; ?>
<div class="card-container package-grid" id="package-grid">
<?php foreach ($packages as $package): ?>
<article class="card package-card" data-package-name="<?php echo e(strtolower($package['name'])); ?>" data-has-offer="<?php echo $package['active_offer']?'1':'0'; ?>">
  <span class="price package-price"><?php if($package['active_offer']):?><small><?php echo inr($package['price']);?></small><span class="current-price"><?php echo inr(max(0,(float)$package['price']-$package['active_offer']['display_discount']));?></span><?php else:?><span class="current-price"><?php echo inr($package['price']); ?></span><?php endif;?></span>
  <img class="package-card-image" src="<?php echo e($package['image']); ?>" alt="<?php echo e($package['name']); ?>">
  <div class="content"><?php if($package['active_offer']):?><a class="package-offer" href="offers.php?package_id=<?php echo (int)$package['id'];?>&offer_id=<?php echo (int)$package['active_offer']['id'];?>"><?php echo e(offer_label($package['active_offer']));?> · ends <?php echo e(date('d M',strtotime($package['active_offer']['booking_end_at'])));?></a><?php endif;?><h3 class="title"><?php echo e(strtoupper($package['name'])); ?></h3><span class="package-state"><?php echo e($package['state_name']); ?></span>
    <p><?php echo e($package['description']); ?></p>
    <?php if($package['review_count']):?><p class="package-state"><i class="fa-solid fa-star"></i> <?php echo number_format((float)$package['avg_rating'],1);?>/5 from <?php echo (int)$package['review_count'];?> review<?php echo (int)$package['review_count']===1?'':'s';?></p><?php endif;?><div class="stars" aria-label="<?php echo (int)$package['hotel_rating']; ?> star accommodation">
      <?php for ($i=1; $i<=5; $i++): ?><span class="fa fa-star<?php echo $i <= (int)$package['hotel_rating'] ? ' checked' : ''; ?>"></span><?php endfor; ?>
    </div>
    <a href="view.php?id=<?php echo (int)$package['id']; ?><?php if($package['active_offer']):?>&offer_id=<?php echo (int)$package['active_offer']['id'];?><?php echo $package['active_offer']['code']?'&offer_code='.urlencode($package['active_offer']['code']):'';?><?php endif;?>" class="btn">View Details</a>
  </div>
</article>
<?php endforeach; ?>
<div class="package-no-results" id="package-no-results" hidden><h2>No matching destinations</h2><p>Try another destination name, state, or turn off the offers-only filter.</p></div>
<?php if(!$loadError && !$packages):?><div class="empty-packages"><h2>Packages coming soon</h2><p>We are preparing curated journeys for <?php echo e($stateName?:'this region'); ?>.</p><a href="Package.php">View all available packages</a></div><?php endif;?>
</div>
<?php if(!$loadError && $totalPages>1):?><nav class="package-pagination" aria-label="Package pages">
<?php $pageQuery=$selectedState!==''?['state'=>$selectedState]:[];$previous=max(1,$currentPage-1);$next=min($totalPages,$currentPage+1);?>
<?php if($currentPage>1):?><a href="?<?php echo e(http_build_query($pageQuery+['page'=>$previous]));?>" aria-label="Previous package page"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i><span class="sr-only">Previous</span></a><?php else:?><span class="is-disabled" aria-hidden="true"><i class="fa-solid fa-chevron-left"></i></span><?php endif;?>
<?php $start=max(1,$currentPage-2);$end=min($totalPages,$currentPage+2);if($start>1):?><a href="?<?php echo e(http_build_query($pageQuery+['page'=>1]));?>">1</a><?php if($start>2):?><span class="is-disabled">&hellip;</span><?php endif;?><?php endif;?>
<?php for($page=$start;$page<=$end;$page++):?><?php if($page===$currentPage):?><span class="is-current" aria-current="page"><?php echo $page;?></span><?php else:?><a href="?<?php echo e(http_build_query($pageQuery+['page'=>$page]));?>"><?php echo $page;?></a><?php endif;?><?php endfor;?>
<?php if($end<$totalPages):?><?php if($end<$totalPages-1):?><span class="is-disabled">&hellip;</span><?php endif;?><a href="?<?php echo e(http_build_query($pageQuery+['page'=>$totalPages]));?>"><?php echo $totalPages;?></a><?php endif;?>
<?php if($currentPage<$totalPages):?><a href="?<?php echo e(http_build_query($pageQuery+['page'=>$next]));?>" aria-label="Next package page"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i><span class="sr-only">Next</span></a><?php else:?><span class="is-disabled" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span><?php endif;?>
</nav><?php endif;?>
</section>
<?php include 'footer.php'; ?>
<script>
if('scrollRestoration' in history){history.scrollRestoration='manual'}if(!location.hash){scrollTo(0,0)}
(()=>{const form=document.getElementById('package-filter'),input=document.getElementById('destination-search'),offersButton=document.getElementById('offers-only'),reset=document.querySelector('.package-filter-reset'),state=document.getElementById('state-filter'),cards=[...document.querySelectorAll('.package-card')],empty=document.getElementById('package-no-results');let offersOnly=false;const filterPackages=()=>{const term=input.value.trim().toLocaleLowerCase();let visible=0;cards.forEach(card=>{const show=card.dataset.packageName.includes(term)&&(!offersOnly||card.dataset.hasOffer==='1');card.hidden=!show;if(show)visible++});empty.hidden=visible!==0||cards.length===0};form.addEventListener('submit',event=>{event.preventDefault();filterPackages();input.focus()});input.addEventListener('input',filterPackages);offersButton.addEventListener('click',()=>{offersOnly=!offersOnly;offersButton.classList.toggle('is-active',offersOnly);offersButton.setAttribute('aria-pressed',String(offersOnly));filterPackages()});if(reset){reset.classList.remove('is-disabled');reset.removeAttribute('aria-disabled');reset.addEventListener('click',event=>{if(state.value!=='')return;event.preventDefault();input.value='';offersOnly=false;offersButton.classList.remove('is-active');offersButton.setAttribute('aria-pressed','false');filterPackages();input.focus()})}})();
</script>
</body></html>
