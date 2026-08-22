<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('SITE_BASE')) {
    require_once dirname(__DIR__) . '/config/db.php';
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// SEO defaults
$pageTitle    = isset($pageTitle)    ? $pageTitle    : 'Online Colleges & Distance Education in India';
$pageDesc     = isset($pageDesc)     ? $pageDesc     : 'Discover 40,000+ online and distance colleges in India. Compare courses, fees, placements and apply free at CollegeKampus Online.';
$pageKeywords = isset($pageKeywords) ? $pageKeywords : 'online colleges India, distance education, UGC approved universities, online MBA, online BBA, online BCA, college admissions 2025';
// Defaults to the actual clean URL the visitor requested (Apache's internal
// rewrite for these routes leaves REQUEST_URI as the pretty path, not the
// underlying .php filename) rather than re-deriving the raw script name -
// same pattern used across the other CollegeKampus properties.
$pageCanonical = isset($pageCanonical) ? $pageCanonical : 'https://' . SITE_DOMAIN . $_SERVER['REQUEST_URI'];
$pageOgImage  = isset($pageOgImage)  ? $pageOgImage  : SITE_BASE . '/assets/img/og-default.jpg';
$pageType     = isset($pageType)     ? $pageType     : 'website';

$defaultJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'EducationalOrganization',
    'name'     => 'CollegeKampus Online',
    'url'      => SITE_BASE,
    'logo'     => SITE_BASE . '/assets/img/logo.png',
    'sameAs'   => ['https://www.facebook.com/collegekampus','https://twitter.com/collegekampus','https://www.instagram.com/collegekampus','https://www.youtube.com/collegekampus'],
    'contactPoint' => ['@type'=>'ContactPoint','telephone'=>'+91-1800-123-4567','contactType'=>'customer service','areaServed'=>'IN'],
];
$jsonLd = isset($jsonLd) ? $jsonLd : $defaultJsonLd;

$base = SITE_BASE;
require_once __DIR__ . '/site-settings-loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | CollegeKampus Online</title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="keywords"    content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="robots"      content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="author"      content="CollegeKampus Online">
    <link rel="canonical"    href="<?= htmlspecialchars($pageCanonical) ?>">
    <meta property="og:type"        content="<?= htmlspecialchars($pageType) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?> | CollegeKampus Online">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($pageCanonical) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($pageOgImage) ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"   content="CollegeKampus Online">
    <meta property="og:locale"      content="en_IN">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@collegekampus">
    <meta name="twitter:title"       content="<?= htmlspecialchars($pageTitle) ?> | CollegeKampus Online">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($pageOgImage) ?>">
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></script>
    <link rel="icon" type="image/png" href="<?= $base ?>/assets/img/favicon.png">
    <link rel="apple-touch-icon"      href="<?= $base ?>/assets/img/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php
    // Cache-bust portal.css/portal.js with the file's own mtime, so a
    // deployed CSS/JS change takes effect on a normal reload instead of
    // silently continuing to serve whatever the browser cached under the
    // un-versioned URL until the visitor happens to hard-refresh.
    $portalCssV = @filemtime(dirname(__DIR__) . '/assets/css/portal.css') ?: time();
    ?>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/portal.css?v=<?= $portalCssV ?>">
    <script>window.CK_BASE = '<?= rtrim(SITE_BASE, '/') ?>';</script>
    <?php if (isset($extraHead)) echo $extraHead; ?>
<style>
/* ── Mega Menu Navbar ── */
.ck-topbar{background:#0f172a;color:rgba(255,255,255,.75);font-size:.78rem;padding:5px 0;position:sticky;top:0;z-index:1101;}
.ck-topbar a{color:rgba(255,255,255,.7);text-decoration:none;}
.ck-topbar a:hover{color:#fff;}
.ck-navbar{background:#fff;box-shadow:0 2px 16px rgba(0,0,0,.08);padding:0;border-bottom:1px solid #e2e8f0;z-index:1100;position:sticky;top:var(--ck-topbar-h,0px);}
.ck-navbar .container{height:64px;align-items:center;flex-wrap:nowrap;}
.ck-navbar .navbar-brand{padding:0;margin-right:1rem;}
.ck-logo-icon{width:38px;height:38px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:.95rem;flex-shrink:0;}
.ck-brand-main{font-size:1rem;font-weight:800;color:#0f172a;line-height:1;}
.ck-brand-sub{font-size:.65rem;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:.08em;}
.ck-navbar .navbar-nav .nav-item>.nav-link{font-size:.82rem;font-weight:600;color:#0f172a;padding:0 8px;height:64px;display:flex;align-items:center;gap:3px;border-bottom:3px solid transparent;transition:color .2s,border-color .2s;white-space:nowrap;}
.ck-navbar .navbar-nav .nav-item>.nav-link:hover,.ck-navbar .navbar-nav .nav-item>.nav-link.active{color:#2563eb;border-bottom-color:#2563eb;}
.ck-navbar .navbar-nav .nav-item>.nav-link .caret{font-size:.6rem;opacity:.6;transition:transform .2s;}
.ck-navbar .nav-item:hover>.nav-link .caret{transform:rotate(180deg);}
.ck-navbar .nav-item{position:relative;}
.ck-dropdown{position:absolute;top:calc(100% + 2px);left:0;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.12);opacity:0;visibility:hidden;transform:translateY(10px);transition:transform .15s,visibility .15s;z-index:1200;min-width:220px;}
/* opacity is intentionally NOT transitioned (snaps 0->1 instantly): a
   timed opacity fade meant this fully-opaque #fff panel spent ~200ms
   partially see-through, letting the page behind it (e.g. a college
   detail hero) show through - easy to screenshot mid-fade and read as
   the menu having a broken/mixed background instead of solid white. */
.ck-navbar .nav-item:hover>.ck-dropdown{opacity:1;visibility:visible;transform:translateY(0);}
.ck-mega{width:620px;padding:0;overflow:hidden;}
.ck-mega-inner{display:grid;grid-template-columns:200px 1fr;}
.ck-mega-sidebar{background:#f8fafc;border-right:1px solid #e2e8f0;border-radius:12px 0 0 12px;padding:16px 0;}
.ck-mega-sidebar-item{display:flex;align-items:center;gap:10px;padding:9px 20px;font-size:.84rem;font-weight:600;color:#374151;cursor:pointer;transition:background .15s,color .15s;text-decoration:none;}
.ck-mega-sidebar-item:hover{background:#e0e7ff;color:#2563eb;}
.ck-mega-sidebar-item .icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.ck-mega-content{padding:18px;}
.ck-mega-content-label{font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f1f5f9;}
.ck-mega-grid{display:grid;grid-template-columns:1fr 1fr;gap:3px;}
.ck-mega-link{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;font-size:.82rem;font-weight:500;color:#374151;text-decoration:none;transition:background .15s,color .15s;}
.ck-mega-link:hover{background:#eff6ff;color:#2563eb;}
.ck-mega-link .prog-icon{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.82rem;flex-shrink:0;}
.ck-mega-link .prog-count{font-size:.7rem;color:#9ca3af;margin-left:auto;white-space:nowrap;}
.ck-simple-dropdown{padding:10px 0;min-width:240px;border-radius:12px;}
.ck-simple-dropdown a{display:flex;align-items:center;gap:8px;padding:9px 20px;font-size:.84rem;font-weight:500;color:#374151;text-decoration:none;transition:background .15s,color .15s;}
.ck-simple-dropdown a:hover{background:#eff6ff;color:#2563eb;}
.ck-simple-dropdown .dd-divider{margin:6px 16px;border-top:1px solid #f1f5f9;}
.ck-ai-badge{display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;font-size:.72rem;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap;}
.btn-ck-green{background:#22c55e;color:#fff;border:none;padding:7px 12px;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .2s;display:inline-flex;align-items:center;gap:4px;}
.btn-ck-green:hover{background:#16a34a;color:#fff;}
.btn-ck-primary{background:#2563eb;color:#fff;border:none;padding:7px 12px;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .2s;display:inline-flex;align-items:center;gap:4px;}
.btn-ck-primary:hover{background:#1d4ed8;color:#fff;}
.btn-ck-search{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;border:none;color:#374151;font-size:1rem;cursor:pointer;transition:background .2s;text-decoration:none;flex-shrink:0;}
.btn-ck-search:hover{background:#e2e8f0;}
@media(max-width:1199px){
  .ck-navbar .container{height:auto;min-height:56px;flex-wrap:wrap;}
  .ck-navbar .navbar-collapse{padding:8px 0 12px;border-top:1px solid #f1f5f9;width:100%;}
  .ck-navbar .navbar-nav .nav-item>.nav-link{height:auto;padding:10px 8px;border-bottom:none;border-left:3px solid transparent;}
  .ck-navbar .navbar-nav .nav-item>.nav-link:hover,.ck-navbar .navbar-nav .nav-item>.nav-link.active{border-left-color:#2563eb;border-bottom-color:transparent;background:#eff6ff;border-radius:0 6px 6px 0;}
  .ck-dropdown{position:static;opacity:1;visibility:visible;transform:none;box-shadow:none;border:none;background:#f8fafc;border-radius:8px;margin:2px 0 8px 16px;display:none;}
  .ck-navbar .nav-item.mobile-open>.ck-dropdown{display:block;}
  .ck-mega{width:auto;}
  .ck-mega-inner{grid-template-columns:1fr;}
  .ck-mega-sidebar{border-right:none;border-bottom:1px solid #e2e8f0;border-radius:0;}
  .ck-mega-grid{grid-template-columns:1fr 1fr;}
  .ck-right-actions{padding:8px 0;flex-wrap:wrap;}
}
/* Footer */
.ck-footer{background:#0f172a;color:#fff;margin-top:auto;}
.ck-footer-links{list-style:none;padding:0;margin:0;}
.ck-footer-links li{margin-bottom:8px;}
.ck-footer-links a{color:rgba(255,255,255,.6);text-decoration:none;font-size:.85rem;transition:color .2s;}
.ck-footer-links a:hover{color:#fff;}
.ck-footer-links li:not(:has(a)){color:rgba(255,255,255,.6);font-size:.85rem;}
.ck-social-link{width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.7);text-decoration:none;font-size:1rem;transition:background .2s,color .2s;flex-shrink:0;}
.ck-social-link:hover{background:#2563eb;color:#fff;}
</style>
</head>
<body>

<!-- Top bar -->
<div class="ck-topbar d-none d-md-block">
  <div class="container d-flex justify-content-between align-items-center">
    <span><i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars(cms('contact_email')) ?> &nbsp;|&nbsp; <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars(cms('contact_phone')) ?> (Toll Free)</span>
    <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars(cms('contact_hours')) ?> &nbsp;|&nbsp; <i class="bi bi-star-fill me-1 text-warning"></i>4.8/5 &middot; 1.25L+ students helped</span>
  </div>
</div>

<?php if (!empty($landingMode)): ?>
<!-- Campaign-mode navbar: a visitor who clicked a paid ad should see the
     lead form, not 15 ways to leave the page - logo + phone only, no mega
     menu, no dropdowns, no sign-in/compare links. -->
<nav class="navbar ck-navbar" id="mainNav" role="navigation" aria-label="Main navigation">
  <div class="container d-flex align-items-center justify-content-between" style="height:64px;">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base ?>/index.php" aria-label="CollegeKampus Online Home">
      <div class="ck-logo-icon">CK</div>
      <div class="lh-1">
        <span class="ck-brand-main">CollegeKampus</span>
        <span class="ck-brand-sub d-block">Online</span>
      </div>
    </a>
    <a href="tel:18001234567" class="btn-ck-green"><i class="bi bi-telephone-fill"></i> 1800-123-4567</a>
  </div>
</nav>
<?php else: ?>
<!-- Mega-menu Navbar -->
<nav class="navbar ck-navbar" id="mainNav" role="navigation" aria-label="Main navigation">
  <div class="container d-flex align-items-center">

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base ?>/index.php" aria-label="CollegeKampus Online Home">
      <div class="ck-logo-icon">CK</div>
      <div class="lh-1">
        <span class="ck-brand-main">CollegeKampus</span>
        <span class="ck-brand-sub d-block">Online</span>
      </div>
    </a>

    <button class="btn border-0 ms-auto d-xl-none" id="mobileToggle" aria-label="Toggle navigation">
      <i class="bi bi-list fs-4 text-dark"></i>
    </button>

    <div class="navbar-collapse" id="navMain" style="display:none;">
      <ul class="navbar-nav d-flex flex-column flex-xl-row align-items-xl-center flex-xl-grow-1" style="list-style:none;padding:0;margin:0;">

        <!-- Explore Programs -->
        <li class="nav-item">
          <a class="nav-link <?= $currentPage==='courses'?'active':'' ?>" href="<?= $base ?>/programs">
            Explore Programs <span class="caret">&#9660;</span>
          </a>
          <div class="ck-dropdown ck-mega">
            <div class="ck-mega-inner">
              <div class="ck-mega-sidebar">
                <div style="padding:0 20px 8px;font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">By Level</div>
                <a href="<?= $base ?>/programs?level=pg" class="ck-mega-sidebar-item"><span class="icon" style="background:#ede9fe;color:#7c3aed;">PG</span>PG Courses</a>
                <a href="<?= $base ?>/programs?level=ug" class="ck-mega-sidebar-item"><span class="icon" style="background:#dbeafe;color:#2563eb;">UG</span>UG Courses</a>
                <a href="<?= $base ?>/programs?level=certificate" class="ck-mega-sidebar-item"><span class="icon" style="background:#dcfce7;color:#16a34a;">Cr</span>Certificate</a>
                <a href="<?= $base ?>/programs?level=diploma" class="ck-mega-sidebar-item"><span class="icon" style="background:#fef3c7;color:#d97706;">Di</span>Diploma</a>
                <div class="dd-divider" style="margin:8px 16px;border-top:1px solid #e2e8f0;"></div>
                <a href="<?= $base ?>/programs" class="ck-mega-sidebar-item" style="color:#2563eb;"><span class="icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-grid-fill"></i></span>All Programs</a>
              </div>
              <div class="ck-mega-content">
                <div class="ck-mega-content-label">Popular Programs</div>
                <div class="ck-mega-grid">
                  <a href="<?= $base ?>/colleges?course=MBA" class="ck-mega-link"><span class="prog-icon" style="background:#ede9fe;color:#7c3aed;">&#128202;</span>Online MBA<span class="prog-count">320+</span></a>
                  <a href="<?= $base ?>/colleges?course=BBA" class="ck-mega-link"><span class="prog-icon" style="background:#dbeafe;color:#2563eb;">&#127919;</span>Online BBA<span class="prog-count">180+</span></a>
                  <a href="<?= $base ?>/colleges?course=BCA" class="ck-mega-link"><span class="prog-icon" style="background:#dcfce7;color:#16a34a;">&#128187;</span>Online BCA<span class="prog-count">150+</span></a>
                  <a href="<?= $base ?>/colleges?course=MCA" class="ck-mega-link"><span class="prog-icon" style="background:#e0f2fe;color:#0369a1;">&#128421;</span>Online MCA<span class="prog-count">120+</span></a>
                  <a href="<?= $base ?>/colleges?course=B.Com" class="ck-mega-link"><span class="prog-icon" style="background:#fef3c7;color:#d97706;">&#128200;</span>Online B.Com<span class="prog-count">200+</span></a>
                  <a href="<?= $base ?>/colleges?course=MA" class="ck-mega-link"><span class="prog-icon" style="background:#fce7f3;color:#be185d;">&#128218;</span>Online MA<span class="prog-count">240+</span></a>
                  <a href="<?= $base ?>/colleges?course=M.Com" class="ck-mega-link"><span class="prog-icon" style="background:#f0fdf4;color:#16a34a;">&#128176;</span>Online M.Com<span class="prog-count">160+</span></a>
                  <a href="<?= $base ?>/colleges?course=B.Sc" class="ck-mega-link"><span class="prog-icon" style="background:#f0f9ff;color:#0ea5e9;">&#128300;</span>Online B.Sc<span class="prog-count">190+</span></a>
                </div>
              </div>
            </div>
          </div>
        </li>

        <!-- Top Universities -->
        <li class="nav-item">
          <a class="nav-link <?= $currentPage==='colleges'?'active':'' ?>" href="<?= $base ?>/colleges">
            Top Universities <span class="caret">&#9660;</span>
          </a>
          <div class="ck-dropdown ck-simple-dropdown">
            <a href="<?= $base ?>/colleges?sort=naac"><i class="bi bi-award text-primary"></i>By NAAC Grade</a>
            <a href="<?= $base ?>/colleges?sort=nirf"><i class="bi bi-trophy text-warning"></i>By NIRF Rank</a>
            <div class="dd-divider"></div>
            <a href="<?= $base ?>/colleges?type=government"><i class="bi bi-bank text-success"></i>Government Universities</a>
            <a href="<?= $base ?>/colleges?type=private"><i class="bi bi-building text-info"></i>Private Universities</a>
            <a href="<?= $base ?>/colleges?type=deemed"><i class="bi bi-mortarboard" style="color:#7c3aed"></i>Deemed Universities</a>
            <div class="dd-divider"></div>
            <a href="<?= $base ?>/colleges?state=Maharashtra"><i class="bi bi-geo-alt text-danger"></i>Maharashtra</a>
            <a href="<?= $base ?>/colleges?state=Delhi"><i class="bi bi-geo-alt text-danger"></i>Delhi</a>
            <a href="<?= $base ?>/colleges?state=Karnataka"><i class="bi bi-geo-alt text-danger"></i>Karnataka</a>
            <a href="<?= $base ?>/colleges" style="color:#2563eb;font-weight:600;"><i class="bi bi-arrow-right"></i>View All States</a>
          </div>
        </li>

        <!-- Tools -->
        <li class="nav-item">
          <a class="nav-link" href="#">Tools <span class="caret">&#9660;</span></a>
          <div class="ck-dropdown ck-mega" style="width:540px;">
            <div class="ck-mega-inner" style="grid-template-columns:1fr 1fr;">
              <div style="padding:18px;border-right:1px solid #f1f5f9;">
                <div class="ck-mega-content-label">Pre-Admission Tools</div>
                <a href="<?= $base ?>/suggest" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#127919; Suggest My University</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Get personalised college matches</span>
                </a>
                <a href="<?= $base ?>/roi-calculator" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128202; ROI Calculator</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Is your degree worth it?</span>
                </a>
                <a href="<?= $base ?>/compare" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#9878; Compare Colleges</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Compare up to 3 universities</span>
                </a>
                <a href="<?= $base ?>/counselling" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#127891; Free Counselling</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Talk to expert mentors</span>
                </a>
                <a href="<?= $base ?>/coupons" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128184; Coupons &amp; Offers</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Save on your degree</span>
                </a>
                <a href="<?= $base ?>/colleges" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128269; College Predictor</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Find colleges by score</span>
                </a>
              </div>
              <div style="padding:18px;">
                <div class="ck-mega-content-label">Post-Admission Tools</div>
                <a href="https://jobs.collegekampus.com" target="_blank" rel="noopener" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128188; Jobs Portal <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;color:#9ca3af;"></i></span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Browse 10,000+ job listings</span>
                </a>
                <a href="https://internship.collegekampus.com" target="_blank" rel="noopener" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#127919; Internship Portal <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;color:#9ca3af;"></i></span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Find internships near you</span>
                </a>
                <a href="<?= $base ?>/apply.php" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128221; Apply Online</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Start your application</span>
                </a>
                <a href="<?= $base ?>/ck-assured" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#9989; CK Assured</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">100% placement guarantee</span>
                </a>
                <a href="<?= $base ?>/contact" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;margin-bottom:4px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128222; Ask an Expert</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Get personalised advice</span>
                </a>
                <a href="<?= $base ?>/blog" class="ck-mega-link" style="flex-direction:column;align-items:flex-start;gap:1px;padding:10px;">
                  <span style="display:flex;align-items:center;gap:7px;font-weight:600;">&#128240; Blog &amp; Articles</span>
                  <span style="font-size:.72rem;color:#9ca3af;padding-left:22px;">Tips, guides &amp; news</span>
                </a>
              </div>
            </div>
          </div>
        </li>

        <!-- About Us -->
        <li class="nav-item">
          <a class="nav-link" href="<?= $base ?>/#about-us">About Us</a>
        </li>

      </ul>

      <!-- Right CTAs -->
      <div class="d-flex align-items-center gap-2 ck-right-actions ms-xl-auto">
        <span class="ck-ai-badge d-none d-xl-inline-flex"><i class="bi bi-stars me-1"></i>AI-Powered</span>
        <?php if (!empty($_SESSION['compare_list'])): ?>
        <a href="<?= $base ?>/compare" class="btn-ck-green" id="navCompareBtn">
          <i class="bi bi-bar-chart-line"></i> Compare
          <span class="badge bg-white text-success ms-1" id="navCompareCount"><?= count($_SESSION['compare_list']) ?></span>
        </a>
        <?php endif; ?>
        <a href="<?= $base ?>/colleges" class="btn-ck-green d-none d-xl-inline-flex">
          <i class="bi bi-lightning-fill"></i> Compare in 2 mins
        </a>
        <a href="<?= $base ?>/counselling" class="btn-ck-primary d-none d-xl-inline-flex">
          <i class="bi bi-person-circle"></i> Sign In
        </a>
        <a href="<?= $base ?>/colleges" class="btn-ck-search" aria-label="Search">
          <i class="bi bi-search"></i>
        </a>
      </div>
    </div><!-- /navMain -->
  </div>
</nav>
<?php endif; ?>

<script>
(function(){
  var toggle = document.getElementById('mobileToggle');
  var nav = document.getElementById('navMain');
  if(toggle && nav){
    nav.classList.add('d-xl-flex');
    // Collapsed by default below the 1200px breakpoint, always shown at/above
    // it - previously this unconditionally cleared the inline display:none on
    // load (before this width check ever ran) and never re-hid it below
    // 1200px, so the full nav (all links + right-action buttons) stayed
    // permanently expanded inline at every screen width instead of collapsing
    // behind the hamburger toggle, overflowing the navbar's fixed height.
    function checkWidth(){
      nav.style.display = (window.innerWidth >= 1200) ? '' : 'none';
    }
    checkWidth();
    window.addEventListener('resize', checkWidth);
    toggle.addEventListener('click', function(){
      if(window.innerWidth >= 1200) return;
      nav.style.display = (nav.style.display === 'none') ? 'block' : 'none';
      ckUpdateStickyOffsets();
    });
  }
  // Mobile sub-menu toggle
  document.querySelectorAll('.ck-navbar .nav-item>.nav-link .caret').forEach(function(caret){
    caret.closest('.nav-link').addEventListener('click', function(e){
      if(window.innerWidth < 1200){
        var dd = this.parentElement.querySelector('.ck-dropdown');
        if(dd){ e.preventDefault(); this.parentElement.classList.toggle('mobile-open'); ckUpdateStickyOffsets(); }
      }
    });
  });

  // Stack the sticky topbar + navbar (and, on pages that have one, an
  // info banner right after <main>) so they all stay pinned together
  // while scrolling instead of each fighting over top:0. Heights are
  // measured rather than hard-coded since the topbar is hidden below the
  // md breakpoint and the navbar's own height changes when it wraps.
  function ckUpdateStickyOffsets(){
    var topbar = document.querySelector('.ck-topbar');
    var navbar = document.querySelector('.ck-navbar');
    var topbarH = (topbar && getComputedStyle(topbar).display !== 'none') ? topbar.offsetHeight : 0;
    var navbarH = navbar ? navbar.offsetHeight : 0;
    document.documentElement.style.setProperty('--ck-topbar-h', topbarH + 'px');
    document.documentElement.style.setProperty('--ck-navbar-h', navbarH + 'px');
  }
  ckUpdateStickyOffsets();
  window.addEventListener('load', ckUpdateStickyOffsets);
  window.addEventListener('resize', ckUpdateStickyOffsets);
})();
</script>

<main>
