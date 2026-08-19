<?php
// ── CMS Settings loader ───────────────────────────────────────────────────────
// Graceful fallback: if site_settings table doesn't exist, use hardcoded defaults
$cms = [];
$cms_defaults = [
    'hero_badge'          => "India's #1 Online Degree Discovery Platform",
    'hero_headline'       => 'Find the Best <span>Online Degree</span><br>Universities in India',
    'hero_subheadline'    => 'Compare 50+ UGC-approved online universities. Check fees, placements, NAAC ratings — all in one place. Apply in minutes.',
    'stat_colleges'       => '50+',
    'stat_colleges_label' => 'Online Universities',
    'stat_students'       => '1.25 Lakh+',
    'stat_ugc'            => '100%',
    'cta_primary'         => 'Browse Universities',
    'cta_secondary'       => 'Talk to a Counsellor',
    'stats_bar_colleges'  => '50+',
    'carousel_slides'     => '[{"title":"Find Your Perfect Online Degree","subtitle":"Explore 50+ UGC-approved universities in India","img":"https://placehold.co/800x400/1a4fba/ffffff?text=Find+Your+Perfect+Online+Degree","btn_text":"Explore Now","btn_link":"colleges.php"},{"title":"Compare 50+ Universities","subtitle":"Side-by-side comparison in just 2 minutes","img":"https://placehold.co/800x400/16a34a/ffffff?text=Compare+50+Universities","btn_text":"Compare Now","btn_link":"compare.php"},{"title":"Free Expert Counselling","subtitle":"Talk to certified counsellors — no commission, no bias","img":"https://placehold.co/800x400/ea580c/ffffff?text=Free+Expert+Counselling","btn_text":"Book Free Session","btn_link":"counselling.php"}]',
];

try {
    if (!defined('SITE_BASE')) {
        require_once __DIR__ . '/config/db.php';
    }
    $__pdo = getDB();
    $__rows = $__pdo->query("SELECT `key`, `value` FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($__rows as $k => $v) {
        $cms[$k] = $v;
    }
} catch (Throwable $e) {
    // Table may not exist yet — silently use defaults
}

// Real online-college count, used as the *default* for stat_colleges/
// stats_bar_colleges below - was hardcoded "500+" regardless of actual
// data (only 50 real online colleges exist). Still fully admin-overridable
// via site_settings, same as every other cms() field.
try {
    require_once __DIR__ . '/includes/colleges-seed.php';
    collegesEnsureSeed($__pdo);
    $__realCollegeCount = (int) $__pdo->query("SELECT COUNT(*) FROM colleges WHERE is_online = 1")->fetchColumn();
    if ($__realCollegeCount > 0) {
        $cms_defaults['stat_colleges']      = $__realCollegeCount . '+';
        $cms_defaults['stats_bar_colleges'] = $__realCollegeCount . '+';
    }
} catch (Throwable $e) {}

function cms(string $key) {
    global $cms, $cms_defaults;
    return $cms[$key] ?? $cms_defaults[$key] ?? '';
}

// Parse carousel slides
$carouselSlides = [];
try {
    $__raw = cms('carousel_slides');
    $carouselSlides = json_decode($__raw, true);
    if (!is_array($carouselSlides)) $carouselSlides = [];
} catch (Throwable $e) {}
if (empty($carouselSlides)) {
    $__def = json_decode($cms_defaults['carousel_slides'], true);
    $carouselSlides = is_array($__def) ? $__def : [];
}
?>
<?php
$pageTitle = 'CollegeKampus Online — Find Your Dream Online Degree in India';
$pageDesc  = 'Compare 50+ online universities in India. Explore MBA, BCA, B.Com, BBA and more online degree programs. Check fees, placements and apply in minutes.';
$extraHead = <<<'EXTRAHEAD_CSS'
  <link rel="stylesheet" href="style.css">
<style>
    :root {
      --ck-blue: #1a4fba;
      --ck-blue2: #2563eb;
      --ck-green: #16a34a;
      --ck-orange: #ea580c;
      --ck-dark: #0f172a;
      --ck-gray: #64748b;
    }
    .ck-topbar { background: #0f172a; color: rgba(255,255,255,.7); font-size:.8rem; padding:6px 0; }
    .ck-topbar a { color: rgba(255,255,255,.7); text-decoration:none; }
    .ck-topbar a:hover { color: #fff; }
    .ck-info-banner { background: #1a4fba; color: #fff; font-size: .88rem; padding: 10px 0; }
    .ck-info-banner a { color: #fbbf24; font-weight: 700; text-decoration: none; }
    .ck-info-banner a:hover { text-decoration: underline; }
    .ck-navbar { background: #fff; box-shadow: 0 1px 0 #e2e8f0; }
    .ck-logo-icon { width:38px; height:38px; background:linear-gradient(135deg,#1a4fba,#2563eb); color:#fff;
      border-radius:8px; display:flex; align-items:center; justify-content:center;
      font-weight:900; font-size:.9rem; letter-spacing:-.5px; flex-shrink:0; }
    .ck-brand-main { font-weight:800; color:#0f172a; font-size:1rem; line-height:1.1; }
    .ck-brand-sub  { font-size:.7rem; color:#64748b; font-weight:500; }
    .ck-navbar .nav-link { color:#374151; font-weight:600; font-size:.88rem; padding:8px 12px; }
    .ck-navbar .nav-link:hover { color:#1a4fba; }
    .ck-navbar .dropdown-menu { border:none; box-shadow:0 8px 32px rgba(0,0,0,.12); border-radius:12px; padding:8px; min-width:220px; }
    .ck-navbar .dropdown-item { border-radius:8px; padding:8px 14px; font-size:.85rem; font-weight:500; color:#374151; }
    .ck-navbar .dropdown-item:hover { background:#f0f6ff; color:#1a4fba; }
    .ck-navbar .dropdown-item i { width:20px; }
    .nav-dd-header { font-size:.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; padding:6px 14px 4px; }
    .btn-compare-nav { background:#f0f6ff; color:#1a4fba; border:1.5px solid #bfdbfe; border-radius:8px; padding:7px 16px; font-size:.82rem; font-weight:700; transition:all .2s; text-decoration:none; }
    .btn-compare-nav:hover { background:#1a4fba; color:#fff; border-color:#1a4fba; }
    .btn-signin { background:#1a4fba; color:#fff; border:none; border-radius:8px; padding:7px 18px; font-size:.82rem; font-weight:700; transition:background .2s; text-decoration:none; }
    .btn-signin:hover { background:#1d4ed8; color:#fff; }
    .ai-badge { background:linear-gradient(90deg,#f59e0b,#ef4444); color:#fff; border-radius:20px; font-size:.7rem; font-weight:700; padding:3px 10px; }
    .ck-hero { background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#1a4fba 100%); padding:70px 0 60px; position:relative; overflow:hidden; }
    .ck-hero::before { content:''; position:absolute; inset:0; background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
    .ck-hero-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fbbf24; border-radius:20px; padding:5px 14px; font-size:.78rem; font-weight:700; margin-bottom:18px; backdrop-filter:blur(8px); }
    .ck-hero h1 { font-size:clamp(1.9rem,4.5vw,3.2rem); font-weight:900; color:#fff; line-height:1.15; margin-bottom:14px; }
    .ck-hero h1 span { color:#fbbf24; }
    .ck-hero-sub { color:rgba(255,255,255,.75); font-size:1rem; max-width:560px; margin-bottom:28px; line-height:1.7; }
    .hero-search-wrap { background:#fff; border-radius:14px; display:flex; align-items:center; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,.25); max-width:600px; margin-bottom:20px; }
    .hero-search-select { border:none; outline:none; padding:14px 16px; font-size:.88rem; font-weight:600; color:#374151; background:#f8fafc; border-right:1px solid #e2e8f0; min-width:130px; cursor:pointer; }
    .hero-search-input { flex:1; border:none; outline:none; padding:14px 16px; font-size:.9rem; color:#0f172a; }
    .hero-search-btn { background:#1a4fba; color:#fff; border:none; padding:0 24px; font-weight:700; font-size:.9rem; cursor:pointer; height:54px; transition:background .2s; display:flex; align-items:center; gap:6px; white-space:nowrap; }
    .hero-search-btn:hover { background:#1d4ed8; }
    .hero-tags { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
    .hero-tag-label { color:rgba(255,255,255,.6); font-size:.8rem; }
    .hero-tag { background:rgba(255,255,255,.12); color:rgba(255,255,255,.9); border:1px solid rgba(255,255,255,.2); border-radius:20px; padding:4px 14px; font-size:.78rem; font-weight:600; text-decoration:none; transition:all .2s; }
    .hero-tag:hover { background:rgba(255,255,255,.25); color:#fff; }
    .hero-stats-col { display:flex; flex-direction:column; gap:14px; justify-content:center; }
    .hero-stat-card { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); backdrop-filter:blur(10px); border-radius:14px; padding:18px 22px; color:#fff; display:flex; align-items:center; gap:14px; }
    .hero-stat-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
    .hero-stat-num { font-size:1.5rem; font-weight:900; line-height:1; }
    .hero-stat-lbl { font-size:.78rem; color:rgba(255,255,255,.7); margin-top:2px; }
    .hero-carousel-wrap { border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.35); }
    .hero-carousel-wrap .carousel-item img { width:100%; height:260px; object-fit:cover; }
    .hero-carousel-wrap .carousel-caption { background:rgba(0,0,0,.5); border-radius:8px; padding:12px 16px; bottom:16px; left:8px; right:8px; text-align:left; }
    .hero-carousel-wrap .carousel-caption h5 { font-size:1rem; font-weight:800; margin-bottom:4px; }
    .hero-carousel-wrap .carousel-caption p  { font-size:.78rem; opacity:.9; margin-bottom:8px; }
    .hero-carousel-wrap .carousel-caption .btn { font-size:.78rem; font-weight:700; padding:5px 14px; }
    .hero-carousel-wrap .carousel-indicators [data-bs-target] { width:8px; height:8px; border-radius:50%; }
    .hero-carousel-wrap .carousel-control-prev,
    .hero-carousel-wrap .carousel-control-next { width:36px; height:36px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,.2); border-radius:50%; }
    .ck-stats-bar { background:#fff; border-bottom:1px solid #e2e8f0; padding:20px 0; }
    .stat-pill { display:flex; align-items:center; gap:10px; padding:0 24px; border-right:1px solid #e2e8f0; }
    .stat-pill:last-child { border-right:none; }
    .stat-pill-icon { font-size:1.6rem; }
    .stat-pill-num { font-size:1.3rem; font-weight:900; color:#0f172a; line-height:1.1; }
    .stat-pill-lbl { font-size:.75rem; color:#64748b; font-weight:500; }
    .prog-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px 16px; text-align:center; cursor:pointer; transition:all .25s; text-decoration:none; display:block; height:100%; }
    .prog-card:hover { box-shadow:0 8px 28px rgba(0,0,0,.1); transform:translateY(-4px); border-color:#bfdbfe; }
    .prog-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; margin:0 auto 12px; }
    .prog-label { font-size:.85rem; font-weight:700; color:#0f172a; margin-bottom:2px; }
    .prog-count { font-size:.75rem; color:#64748b; }
    .sec-badge { display:inline-block; background:#eff6ff; color:#1a4fba; font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:4px 12px; border-radius:20px; margin-bottom:8px; }
    .sec-title { font-size:clamp(1.4rem,3vw,2rem); font-weight:900; color:#0f172a; margin-bottom:8px; }
    .sec-title span { color:#1a4fba; }
    .sec-sub { color:#64748b; font-size:.95rem; }
    .ck-uni-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; transition:all .25s; height:100%; display:flex; flex-direction:column; }
    .ck-uni-card:hover { box-shadow:0 12px 36px rgba(0,0,0,.1); transform:translateY(-4px); border-color:#bfdbfe; }
    .uni-card-top { padding:18px 18px 12px; flex:1; }
    .uni-logo-wrap { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
    .uni-logo { width:50px; height:50px; border-radius:10px; border:1px solid #e2e8f0; background:#f8fafc; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:800; color:#1a4fba; overflow:hidden; flex-shrink:0; }
    .uni-logo img { width:100%; height:100%; object-fit:contain; }
    .uni-name { font-size:.95rem; font-weight:800; color:#0f172a; line-height:1.3; }
    .uni-location { font-size:.78rem; color:#64748b; margin-top:2px; }
    .uni-badges { display:flex; flex-wrap:wrap; gap:5px; margin:10px 0; }
    .uni-badge { font-size:.68rem; font-weight:700; padding:3px 8px; border-radius:5px; }
    .ub-online { background:#e0f2fe; color:#0369a1; }
    .ub-naac   { background:#dcfce7; color:#15803d; }
    .ub-nirf   { background:#fef3c7; color:#92400e; }
    .ub-ugc    { background:#ede9fe; color:#6d28d9; }
    .uni-meta  { display:flex; gap:16px; margin-top:8px; }
    .uni-meta-item { font-size:.78rem; color:#374151; }
    .uni-meta-item strong { display:block; font-size:.88rem; color:#0f172a; }
    .uni-card-footer { padding:12px 18px; border-top:1px solid #f1f5f9; display:flex; gap:8px; align-items:center; }
    .btn-view-uni { flex:1; text-align:center; padding:9px; background:#1a4fba; color:#fff; border-radius:8px; font-size:.8rem; font-weight:700; text-decoration:none; transition:background .2s; }
    .btn-view-uni:hover { background:#1d4ed8; color:#fff; }
    .btn-cmp-uni { padding:9px 14px; background:#f0f6ff; color:#1a4fba; border:1.5px solid #bfdbfe; border-radius:8px; font-size:.8rem; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-cmp-uni:hover,.btn-cmp-uni.added { background:#1a4fba; color:#fff; border-color:#1a4fba; }
    .why-card { background:#fff; border-radius:14px; padding:28px 24px; border:1px solid #e2e8f0; height:100%; transition:box-shadow .2s; }
    .why-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.08); }
    .why-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:16px; }
    .compare-banner { background:linear-gradient(135deg,#0f172a,#1e3a8a); border-radius:20px; padding:40px 36px; color:#fff; }
    .compare-banner h2 { font-size:clamp(1.4rem,3vw,2rem); font-weight:900; margin-bottom:10px; }
    .compare-banner p { color:rgba(255,255,255,.75); margin-bottom:24px; }
    .btn-compare-big { background:#fbbf24; color:#0f172a; border:none; border-radius:10px; padding:13px 28px; font-weight:800; font-size:.95rem; cursor:pointer; transition:background .2s; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
    .btn-compare-big:hover { background:#f59e0b; color:#0f172a; }
    .testi-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:24px; height:100%; }
    .testi-stars { color:#f59e0b; font-size:.9rem; margin-bottom:10px; }
    .testi-text { font-size:.88rem; color:#374151; line-height:1.7; margin-bottom:16px; font-style:italic; }
    .testi-name { font-weight:700; font-size:.85rem; color:#0f172a; }
    .testi-college { font-size:.78rem; color:#64748b; }
    .ck-cta { background:linear-gradient(135deg,#1a4fba,#2563eb); padding:64px 0; }
    .ck-about { background:#f8fafc; }
    .about-promise-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:28px 24px; height:100%; transition:box-shadow .2s; }
    .about-promise-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.08); }
    .about-promise-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:16px; }
    .about-mv-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:36px 32px; }
    .about-mv-label { display:inline-block; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; padding:4px 12px; border-radius:20px; margin-bottom:14px; }
    .about-mv-label.mission { background:#eff6ff; color:#1a4fba; }
    .about-mv-label.vision  { background:#f0fdf4; color:#16a34a; }
    .ck-explore-cta { background:linear-gradient(135deg,#0f172a,#1e3a8a); }
    .ck-footer { background:#0f172a; color:rgba(255,255,255,.7); }
    .ck-footer h6 { color:#fff; }
    .ck-footer-links li { margin-bottom:6px; }
    .ck-footer-links a { color:rgba(255,255,255,.6); text-decoration:none; font-size:.85rem; transition:color .2s; }
    .ck-footer-links a:hover { color:#fff; }
    .ck-social { width:36px; height:36px; border-radius:8px; background:rgba(255,255,255,.08); display:inline-flex; align-items:center; justify-content:center; color:rgba(255,255,255,.6); font-size:1rem; text-decoration:none; transition:all .2s; }
    .ck-social:hover { background:#1a4fba; color:#fff; }
    .ck-spinner { width:36px; height:36px; border:3px solid #e2e8f0; border-top-color:#1a4fba; border-radius:50%; animation:spin .7s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
    .skel { background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
  </style>
EXTRAHEAD_CSS;
require_once __DIR__ . '/includes/header.php';
?>


<!-- Info Banner -->
<div class="ck-info-banner" id="infoBanner">
  <div class="container d-flex align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-info-circle-fill"></i>
      <span>Looking for regular colleges? Visit <a href="https://explore.collegekampus.com" target="_blank" rel="noopener"><strong>explore.collegekampus.com</strong></a> &rarr;</span>
    </div>
    <button type="button" class="btn-close btn-close-white btn-sm flex-shrink-0" aria-label="Dismiss"
      onclick="document.getElementById('infoBanner').style.display='none'"></button>
  </div>
</div>

<!-- Hero -->
<section class="ck-hero">
  <div class="container position-relative">
    <div class="row align-items-center gy-4">
      <div class="col-lg-7">
        <div class="ck-hero-badge">
          <i class="bi bi-award-fill"></i> <?= htmlspecialchars(cms('hero_badge')) ?>
        </div>
        <h1><?= cms('hero_headline') ?></h1>
        <p class="ck-hero-sub"><?= htmlspecialchars(cms('hero_subheadline')) ?></p>
        <div class="hero-search-wrap">
          <select class="hero-search-select" id="heroProgram">
            <option value="">All Programs</option>
            <option value="MBA">Online MBA</option>
            <option value="BBA">Online BBA</option>
            <option value="BCA">Online BCA</option>
            <option value="MCA">Online MCA</option>
            <option value="BCOM">Online B.Com</option>
            <option value="MCOM">Online M.Com</option>
            <option value="BA">Online BA</option>
            <option value="MA">Online MA</option>
            <option value="BSC">Online B.Sc</option>
          </select>
          <input class="hero-search-input" id="heroSearch" type="text" placeholder="Search universities, cities, states…" autocomplete="off">
          <button class="hero-search-btn" onclick="heroGo()">
            <i class="bi bi-search"></i> Search
          </button>
        </div>
        <div class="hero-tags">
          <span class="hero-tag-label">Popular:</span>
          <a href="colleges.php?course=MBA" class="hero-tag">MBA</a>
          <a href="colleges.php?course=BCA" class="hero-tag">BCA</a>
          <a href="colleges.php?course=BBA" class="hero-tag">BBA</a>
          <a href="colleges.php?course=BCOM" class="hero-tag">B.Com</a>
          <a href="colleges.php?course=MCA" class="hero-tag">MCA</a>
          <a href="colleges.php?course=MA" class="hero-tag">MA</a>
        </div>
      </div>

      <!-- Hero Carousel (desktop) -->
      <div class="col-lg-5 d-none d-lg-block">
        <div class="hero-carousel-wrap">
          <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
              <?php foreach ($carouselSlides as $i => $slide): ?>
              <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>"
                class="<?= $i===0?'active':'' ?>" aria-label="Slide <?= $i+1 ?>"></button>
              <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
              <?php foreach ($carouselSlides as $i => $slide): ?>
              <div class="carousel-item <?= $i===0?'active':'' ?>">
                <img src="<?= htmlspecialchars($slide['img'] ?? '') ?>" alt="<?= htmlspecialchars($slide['title'] ?? '') ?>" loading="lazy">
                <div class="carousel-caption">
                  <h5><?= htmlspecialchars($slide['title'] ?? '') ?></h5>
                  <p><?= htmlspecialchars($slide['subtitle'] ?? '') ?></p>
                  <?php if (!empty($slide['btn_text'])): ?>
                  <a href="<?= htmlspecialchars($slide['btn_link'] ?? '#') ?>" class="btn btn-warning btn-sm fw-bold">
                    <?= htmlspecialchars($slide['btn_text']) ?> <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Hero Stats — mobile only -->
      <div class="col-12 d-lg-none">
        <div class="hero-stats-col">
          <div class="hero-stat-card">
            <div class="hero-stat-icon" style="background:rgba(251,191,36,.15)"><i class="bi bi-mortarboard-fill" style="color:#fbbf24"></i></div>
            <div>
              <div class="hero-stat-num"><?= htmlspecialchars(cms('stat_colleges')) ?></div>
              <div class="hero-stat-lbl"><?= htmlspecialchars(cms('stat_colleges_label')) ?></div>
            </div>
          </div>
          <div class="hero-stat-card">
            <div class="hero-stat-icon" style="background:rgba(34,197,94,.15)"><i class="bi bi-people-fill" style="color:#4ade80"></i></div>
            <div>
              <div class="hero-stat-num"><?= htmlspecialchars(cms('stat_students')) ?></div>
              <div class="hero-stat-lbl">Students Guided</div>
            </div>
          </div>
          <div class="hero-stat-card">
            <div class="hero-stat-icon" style="background:rgba(96,165,250,.15)"><i class="bi bi-shield-check-fill" style="color:#60a5fa"></i></div>
            <div>
              <div class="hero-stat-num"><?= htmlspecialchars(cms('stat_ugc')) ?></div>
              <div class="hero-stat-lbl">UGC-Approved Programs Only</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats Bar -->
<div class="ck-stats-bar d-none d-md-block">
  <div class="container">
    <div class="d-flex justify-content-center">
      <div class="stat-pill">
        <div class="stat-pill-icon">🏛️</div>
        <div><div class="stat-pill-num"><?= htmlspecialchars(cms('stats_bar_colleges')) ?></div><div class="stat-pill-lbl">Online Universities</div></div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon">📚</div>
        <div><div class="stat-pill-num">600+</div><div class="stat-pill-lbl">Online Programs</div></div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon">👨‍🎓</div>
        <div><div class="stat-pill-num">1.25L+</div><div class="stat-pill-lbl">Students Helped</div></div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon">⭐</div>
        <div><div class="stat-pill-num">4.8/5</div><div class="stat-pill-lbl">Google Rating</div></div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon">🗺️</div>
        <div><div class="stat-pill-num">28</div><div class="stat-pill-lbl">States Covered</div></div>
      </div>
    </div>
  </div>
</div>

<!-- Explore Programs -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-4">
      <span class="sec-badge">Explore</span>
      <h2 class="sec-title mt-1">Browse by <span>Program</span></h2>
      <p class="sec-sub">Choose the program that fits your career goals</p>
    </div>
    <div class="row g-3">
      <div class="col-6 col-md-3 col-lg-2">
        <a href="colleges.php?course=MBA" class="prog-card">
          <div class="prog-icon" style="background:#eff6ff"><span style="font-size:1.6rem">💼</span></div>
          <div class="prog-label">Online MBA</div>
          <div class="prog-count">138+ colleges</div>
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="colleges.php?course=BCA" class="prog-card">
          <div class="prog-icon" style="background:#f0fdf4"><span style="font-size:1.6rem">💻</span></div>
          <div class="prog-label">Online BCA</div>
          <div class="prog-count">95+ colleges</div>
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="colleges.php?course=MCA" class="prog-card">
          <div class="prog-icon" style="background:#fef9c3"><span style="font-size:1.6rem">🖥️</span></div>
          <div class="prog-label">Online MCA</div>
          <div class="prog-count">82+ colleges</div>
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="colleges.php?course=BBA" class="prog-card">
          <div class="prog-icon" style="background:#fdf4ff"><span style="font-size:1.6rem">📊</span></div>
          <div class="prog-label">Online BBA</div>
          <div class="prog-count">110+ colleges</div>
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="colleges.php?course=BCOM" class="prog-card">
          <div class="prog-icon" style="background:#fff7ed"><span style="font-size:1.6rem">💰</span></div>
          <div class="prog-label">Online B.Com</div>
          <div class="prog-count">120+ colleges</div>
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="colleges.php?course=MA" class="prog-card">
          <div class="prog-icon" style="background:#fef2f2"><span style="font-size:1.6rem">🎓</span></div>
          <div class="prog-label">Online MA</div>
          <div class="prog-count">75+ colleges</div>
        </a>
      </div>
    </div>
    <div class="text-center mt-4">
      <a href="courses.php" class="btn btn-outline-primary px-4 fw-semibold">View All Programs <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>

<!-- Featured Online Universities -->
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <span class="sec-badge">Top Picks</span>
        <h2 class="sec-title mt-1">Featured <span>Online Universities</span></h2>
        <p class="sec-sub mb-0">Handpicked UGC-approved universities offering quality online degrees</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="loadFeatured('all')" id="tabAll">All</button>
        <button class="btn btn-sm btn-outline-primary" onclick="loadFeatured('government')" id="tabGovt">Government</button>
        <button class="btn btn-sm btn-outline-primary" onclick="loadFeatured('private')" id="tabPrivate">Private</button>
        <button class="btn btn-sm btn-outline-primary" onclick="loadFeatured('deemed')" id="tabDeemed">Deemed</button>
      </div>
    </div>
    <div class="row g-4" id="featured-colleges-grid">
      <div class="col-md-6 col-lg-4"><div class="skel" style="height:220px;border-radius:14px"></div></div>
      <div class="col-md-6 col-lg-4"><div class="skel" style="height:220px;border-radius:14px"></div></div>
      <div class="col-md-6 col-lg-4"><div class="skel" style="height:220px;border-radius:14px"></div></div>
    </div>
    <div class="text-center mt-4">
      <a href="colleges.php" class="btn btn-primary px-5 fw-bold">
        <i class="bi bi-grid me-2"></i>View All Universities
      </a>
    </div>
  </div>
</section>

<!-- Compare Banner -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="compare-banner">
      <div class="row align-items-center gy-4">
        <div class="col-lg-8">
          <div class="ai-badge mb-3 d-inline-block"><i class="bi bi-stars me-1"></i>AI-Powered Tool</div>
          <h2>Compare Online Universities<br>Side by Side — in 2 Minutes</h2>
          <p>Compare fees, placements, NAAC ratings, course duration and more across multiple universities at once.</p>
          <div class="d-flex flex-wrap gap-3">
            <a href="compare.php" class="btn-compare-big">
              <i class="bi bi-bar-chart-steps"></i> Compare Colleges Now
            </a>
            <a href="counselling.php" class="btn btn-outline-light px-4">
              <i class="bi bi-headset me-2"></i>Free Counselling
            </a>
          </div>
        </div>
        <div class="col-lg-4 text-center d-none d-lg-block">
          <div style="font-size:6rem;opacity:.4">⚖️</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Why CollegeKampus -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-badge">Why Us</span>
      <h2 class="sec-title mt-1">Why Choose <span>CollegeKampus</span>?</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="why-card">
          <div class="why-icon" style="background:#eff6ff;color:#1a4fba"><i class="bi bi-shield-check-fill"></i></div>
          <h5 class="fw-800 mb-2" style="font-size:.95rem">100% Verified Info</h5>
          <p class="text-muted small mb-0">All college data is manually verified. Only UGC-approved online programs listed.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="why-card">
          <div class="why-icon" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-currency-rupee"></i></div>
          <h5 class="fw-800 mb-2" style="font-size:.95rem">Free to Use</h5>
          <p class="text-muted small mb-0">No hidden charges. Compare unlimited colleges, apply online — all free.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="why-card">
          <div class="why-icon" style="background:#fef9c3;color:#ca8a04"><i class="bi bi-headset"></i></div>
          <h5 class="fw-800 mb-2" style="font-size:.95rem">Expert Counselling</h5>
          <p class="text-muted small mb-0">Free 1-on-1 sessions with 600+ expert mentors to guide your admission journey.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="why-card">
          <div class="why-icon" style="background:#fdf4ff;color:#9333ea"><i class="bi bi-stars"></i></div>
          <h5 class="fw-800 mb-2" style="font-size:.95rem">AI-Powered Match</h5>
          <p class="text-muted small mb-0">Our AI recommends the best college based on your budget, goals and eligibility.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-badge">Simple Steps</span>
      <h2 class="sec-title mt-1">How It <span>Works</span></h2>
    </div>
    <div class="row g-4 text-center">
      <div class="col-md-3">
        <div style="width:52px;height:52px;border-radius:50%;background:#1a4fba;color:#fff;font-size:1.3rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">1</div>
        <i class="bi bi-search fs-2 text-primary d-block mb-2"></i>
        <h5 class="fw-700">Search</h5>
        <p class="text-muted small">Search by program, university name, city or budget</p>
      </div>
      <div class="col-md-3">
        <div style="width:52px;height:52px;border-radius:50%;background:#16a34a;color:#fff;font-size:1.3rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">2</div>
        <i class="bi bi-funnel fs-2 text-success d-block mb-2"></i>
        <h5 class="fw-700">Filter</h5>
        <p class="text-muted small">Filter by NAAC grade, fees, NIRF rank and delivery mode</p>
      </div>
      <div class="col-md-3">
        <div style="width:52px;height:52px;border-radius:50%;background:#ea580c;color:#fff;font-size:1.3rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">3</div>
        <i class="bi bi-bar-chart-steps fs-2 text-warning d-block mb-2"></i>
        <h5 class="fw-700">Compare</h5>
        <p class="text-muted small">Compare up to 3 universities side by side on 20+ factors</p>
      </div>
      <div class="col-md-3">
        <div style="width:52px;height:52px;border-radius:50%;background:#9333ea;color:#fff;font-size:1.3rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">4</div>
        <i class="bi bi-send fs-2 d-block mb-2" style="color:#9333ea"></i>
        <h5 class="fw-700">Apply</h5>
        <p class="text-muted small">Submit your application directly — no visits required</p>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="sec-badge">Student Stories</span>
      <h2 class="sec-title mt-1">What Our <span>Students Say</span></h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"Found my dream online MBA college in less than 10 minutes. The comparison tool is amazing — saved me weeks of research!"</p>
          <div class="testi-name">Priya Sharma</div>
          <div class="testi-college">Online MBA — Manipal University</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p class="testi-text">"The counsellor helped me choose between 3 universities based on my budget and career goals. Got admitted to BITS Pilani online!"</p>
          <div class="testi-name">Rahul Verma</div>
          <div class="testi-college">Online BCA — BITS Pilani</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★★★★☆</div>
          <p class="testi-text">"Very transparent fee information. No surprises after joining. CollegeKampus made the whole admission process smooth and stress-free."</p>
          <div class="testi-name">Ananya Singh</div>
          <div class="testi-college">Online B.Com — Amity University</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="ck-cta">
  <div class="container text-center">
    <h2 class="text-white fw-900 mb-2" style="font-size:clamp(1.5rem,3vw,2.2rem)">Ready to Find Your Online University?</h2>
    <p class="text-white-50 mb-4">Join 1.25 lakh+ students who discovered their perfect online degree on CollegeKampus.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="colleges.php" class="btn btn-light btn-lg px-5 fw-bold"><?= htmlspecialchars(cms('cta_primary')) ?></a>
      <a href="counselling.php" class="btn btn-outline-light btn-lg px-5"><?= htmlspecialchars(cms('cta_secondary')) ?></a>
    </div>
  </div>
</section>

<!-- About Us Section -->
<section class="py-5 ck-about" id="about-us">
  <div class="container">

    <!-- Why This Exists -->
    <div class="row align-items-center gy-4 mb-5 pb-3">
      <div class="col-lg-6">
        <span class="sec-badge">Why This Exists</span>
        <h2 class="sec-title mt-2">The Decision Nobody Was<br>Making <span>Honestly</span></h2>
        <p class="text-muted" style="line-height:1.8;">When a student searches for the right college, they are bombarded by paid rankings, biased agents, and commission-driven counsellors. Nobody in the room was truly on the student's side.</p>
        <p class="text-muted" style="line-height:1.8;">We built CollegeKampus Online because we believed that changing one decision — which college you go to — can change the entire trajectory of a person's life. That decision deserved honesty.</p>
        <blockquote class="border-start border-primary border-3 ps-3 my-3">
          <p class="mb-1 fw-bold" style="color:#0f172a;font-size:.95rem;">"Counsellors are Selling Colleges. We Choose the Truth Instead."</p>
          <footer class="text-muted" style="font-size:.82rem;">That is not a positioning statement. It is the rule this platform was built on.</footer>
        </blockquote>
      </div>
      <div class="col-lg-5 offset-lg-1">
        <div class="p-4 rounded-3 text-center" style="background:linear-gradient(135deg,#eff6ff,#e0f2fe);border:1px solid #bfdbfe;">
          <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#1a4fba,#2563eb);color:#fff;font-size:1.8rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="bi bi-quote"></i></div>
          <div class="fw-bold" style="color:#0f172a;">A Note From the Founders</div>
          <p class="mt-3 text-muted small" style="line-height:1.7;">"We spent years watching students make bad college decisions because of bad advice. We built this to fix that."</p>
        </div>
      </div>
    </div>

    <!-- Our Promise (4 cards) -->
    <div class="mb-5">
      <div class="text-center mb-4">
        <span class="sec-badge">Our Promise</span>
        <h2 class="sec-title mt-1">What Makes Us <span>Different</span></h2>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="about-promise-card">
            <div class="about-promise-icon" style="background:#f0fdf4;"><span style="font-size:1.5rem;">🚫</span></div>
            <h5 style="font-size:.95rem;font-weight:800;color:#0f172a;margin-bottom:10px;">We Don't Take College Commissions</h5>
            <p class="text-muted small mb-0">Every rupee we earn comes from students and employers — never from colleges paying us to send them students. Our advice is never influenced by who pays us more.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="about-promise-card">
            <div class="about-promise-icon" style="background:#eff6ff;"><span style="font-size:1.5rem;">✅</span></div>
            <h5 style="font-size:.95rem;font-weight:800;color:#0f172a;margin-bottom:10px;">Verified, Unbiased Data</h5>
            <p class="text-muted small mb-0">Our college data is collected independently, cross-verified with multiple sources, and updated regularly. We show you the truth — even when it's unflattering.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="about-promise-card">
            <div class="about-promise-icon" style="background:#fff7ed;"><span style="font-size:1.5rem;">🧑‍💼</span></div>
            <h5 style="font-size:.95rem;font-weight:800;color:#0f172a;margin-bottom:10px;">Certified Counsellors, Not Salespeople</h5>
            <p class="text-muted small mb-0">Our counsellors are paid a fixed salary. They have no incentive to push you toward any college. Their only job is to help you decide what's best for you.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="about-promise-card">
            <div class="about-promise-icon" style="background:#fdf4ff;"><span style="font-size:1.5rem;">🆓</span></div>
            <h5 style="font-size:.95rem;font-weight:800;color:#0f172a;margin-bottom:10px;">Free for Every Student</h5>
            <p class="text-muted small mb-0">All guidance, comparisons, and counselling sessions are completely free for students. Because the ability to pay should never determine the quality of your future.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Mission & Vision -->
    <div>
      <div class="text-center mb-4">
        <span class="sec-badge">What Drives Us</span>
        <h2 class="sec-title mt-1">Mission &amp; <span>Vision</span></h2>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="about-mv-card h-100">
            <div class="about-mv-label mission">Mission</div>
            <h3 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:12px;">To make honest education guidance accessible to every Indian student</h3>
            <p class="text-muted mb-0" style="line-height:1.8;">Regardless of city, background, or budget — every student deserves the same quality of guidance that was once available only to the privileged few.</p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="about-mv-card h-100">
            <div class="about-mv-label vision">Vision</div>
            <h3 style="font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:12px;">A world where no student makes a life-altering decision without the full truth</h3>
            <p class="text-muted mb-0" style="line-height:1.8;">We envision an India where college rankings can't be bought, counsellors can't be bribed, and students choose futures — not advertisements.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- Explore Regular Colleges CTA -->
<section class="ck-explore-cta py-5">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-8">
        <span class="sec-badge" style="background:rgba(255,255,255,.15);color:#fff;">Regular Colleges</span>
        <h2 class="text-white fw-900 mt-2 mb-2" style="font-size:clamp(1.4rem,3vw,2rem);">Looking for Regular / Campus-Based Colleges?</h2>
        <p class="mb-0" style="color:rgba(255,255,255,.75);">This portal focuses on online degrees. For regular colleges, engineering, medical, law and more — explore our main platform at <strong style="color:#fbbf24;">explore.collegekampus.com</strong></p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="https://explore.collegekampus.com" target="_blank" rel="noopener"
           class="btn btn-warning btn-lg px-5 fw-bold">
          <i class="bi bi-box-arrow-up-right me-2"></i>Explore Regular Colleges
        </a>
      </div>
    </div>
  </div>
</section>


</main>

<!-- Footer -->
<footer class="ck-footer">
  <div class="container">
    <div class="row gy-4 py-5">
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="ck-logo-icon">CK</div>
          <div class="lh-1">
            <span class="ck-brand-main" style="color:#fff">CollegeKampus</span>
            <span class="ck-brand-sub d-block">Online</span>
          </div>
        </div>
        <p class="small" style="color:rgba(255,255,255,.55)">India's trusted online college discovery platform. Compare 50+ UGC-approved universities and apply in minutes.</p>
        <div class="d-flex gap-2 mt-3">
          <a href="#" class="ck-social"><i class="bi bi-facebook"></i></a>
          <a href="#" class="ck-social"><i class="bi bi-instagram"></i></a>
          <a href="#" class="ck-social"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="ck-social"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="ck-social"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em">Programs</h6>
        <ul class="list-unstyled ck-footer-links">
          <li><a href="colleges.php?course=MBA">Online MBA</a></li>
          <li><a href="colleges.php?course=BCA">Online BCA</a></li>
          <li><a href="colleges.php?course=MCA">Online MCA</a></li>
          <li><a href="colleges.php?course=BBA">Online BBA</a></li>
          <li><a href="colleges.php?course=BCOM">Online B.Com</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em">Universities</h6>
        <ul class="list-unstyled ck-footer-links">
          <li><a href="colleges.php?type=government">Government</a></li>
          <li><a href="colleges.php?type=private">Private</a></li>
          <li><a href="colleges.php?type=deemed">Deemed</a></li>
          <li><a href="colleges.php?sort=nirf">NIRF Ranked</a></li>
          <li><a href="colleges.php?accreditation=NAAC">NAAC A++</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em">Tools</h6>
        <ul class="list-unstyled ck-footer-links">
          <li><a href="compare.php">Compare Colleges</a></li>
          <li><a href="counselling.php">Free Counselling</a></li>
          <li><a href="apply.php">Apply Online</a></li>
          <li><a href="contact.php">Contact Us</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em">Contact</h6>
        <ul class="list-unstyled ck-footer-links">
          <li><i class="bi bi-telephone me-1 text-primary"></i>1800-123-4567</li>
          <li class="mt-2"><i class="bi bi-envelope me-1 text-primary"></i>info@collegekampus.in</li>
          <li class="mt-2"><i class="bi bi-clock me-1 text-primary"></i>Mon–Sat 9am–7pm</li>
        </ul>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.1)">
    <div class="row align-items-center py-3">
      <div class="col-md-6 text-center text-md-start">
        <small style="color:rgba(255,255,255,.4)">&copy; <?= date('Y') ?> CollegeKampus Online. All rights reserved.</small>
      </div>
      <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
        <small><a href="#" style="color:rgba(255,255,255,.4);text-decoration:none">Privacy Policy</a>
        &bull; <a href="#" style="color:rgba(255,255,255,.4);text-decoration:none">Terms of Use</a>
        &bull; <a href="#" style="color:rgba(255,255,255,.4);text-decoration:none">Sitemap</a></small>
      </div>
    </div>
  </div>
</footer>

<!-- Compare Bar -->
<div class="compare-bar" id="compareBar">
  <span class="compare-bar-label">Compare:</span>
  <div class="compare-chips" id="compareChips"></div>
  <button class="btn-clear-compare" onclick="clearCompare()">Clear</button>
  <button class="btn-go-compare" onclick="goToCompare()"><i class="bi bi-bar-chart-steps me-1"></i>Compare Now</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.CK_BASE = '/dashboard';</script>
<script src="script.js"></script>
<script src="assets/js/portal.js"></script>
<script>
function heroGo() {
  const program = document.getElementById('heroProgram').value;
  const search  = document.getElementById('heroSearch').value.trim();
  let url = '/dashboard/colleges.php?';
  if (program) url += 'course=' + encodeURIComponent(program) + '&';
  if (search)  url += 'search=' + encodeURIComponent(search);
  window.location.href = url;
}
document.getElementById('heroSearch').addEventListener('keydown', e => { if (e.key === 'Enter') heroGo(); });

function initials(n) { return (n||'??').split(' ').slice(0,2).map(w=>w[0]).join('').toUpperCase(); }
function feeINR(n)   { return n ? '₹' + Number(n).toLocaleString('en-IN') : null; }
function typeLabel(t) {
  const map = {government:'Government',private:'Private',deemed:'Deemed',autonomous:'Autonomous'};
  return map[t] || (t ? t.charAt(0).toUpperCase()+t.slice(1) : 'University');
}
function typeBg(t) {
  const map = {government:'ub-naac',private:'ub-online',deemed:'ub-nirf',autonomous:'ub-ugc'};
  return map[t] || 'ub-online';
}
function renderColleges(colleges) {
  if (!colleges || !colleges.length) {
    return '<div class="col-12 text-center text-muted py-4">No universities found.</div>';
  }
  return colleges.map(c => {
    const slug    = c.slug || c.id;
    const rating  = c.rating ? (parseFloat(c.rating).toFixed(1)) : null;
    const nirf    = c.nirf_rank ? '#' + c.nirf_rank + ' NIRF' : null;
    const fees    = feeINR(c.min_fees);
    return `
    <div class="col-md-6 col-lg-4">
      <div class="ck-uni-card">
        <div class="uni-card-top">
          <div class="uni-logo-wrap">
            <div class="uni-logo">
              ${c.logo_url ? '<img src="'+c.logo_url+'" alt="'+escHtml(c.name||'')+'">' : '<span>'+initials(c.name)+'</span>'}
            </div>
            <div>
              <div class="uni-name">${escHtml(c.name||'')}</div>
              <div class="uni-location"><i class="bi bi-geo-alt me-1"></i>${escHtml((c.city||'')+(c.state ? ', '+c.state : ''))}</div>
            </div>
          </div>
          <div class="uni-badges">
            <span class="uni-badge ${typeBg(c.type||c.college_type)}">${typeLabel(c.type||c.college_type)}</span>
            ${c.accreditation ? '<span class="uni-badge ub-naac"><i class="bi bi-patch-check-fill me-1"></i>'+escHtml(c.accreditation)+'</span>' : ''}
            ${nirf ? '<span class="uni-badge ub-nirf">'+nirf+'</span>' : ''}
            ${c.ugc_approved ? '<span class="uni-badge ub-ugc">UGC</span>' : ''}
          </div>
          <div class="uni-meta">
            ${fees ? '<div class="uni-meta-item"><strong>'+fees+'/yr</strong>Fees from</div>' : ''}
            ${rating ? '<div class="uni-meta-item"><strong>⭐ '+rating+'</strong>Rating</div>' : ''}
            ${c.placement_rate ? '<div class="uni-meta-item"><strong>'+parseFloat(c.placement_rate).toFixed(0)+'%</strong>Placements</div>' : ''}
          </div>
        </div>
        <div class="uni-card-footer">
          <a href="/dashboard/college-detail.php?slug=${encodeURIComponent(slug)}" class="btn-view-uni">View Details</a>
          <button class="btn-cmp-uni btn-compare-add" data-id="${c.id}" data-name="${escHtml(c.name||'')}" onclick="addToCompare('${c.id}','${escHtml(c.name||'')}')">+ Compare</button>
        </div>
      </div>
    </div>`;
  }).join('');
}
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
let currentType = 'all';
function loadFeatured(type) {
  currentType = type;
  ['All','Govt','Private','Deemed'].forEach(t => {
    const el = document.getElementById('tab' + t);
    if (el) { el.className = 'btn btn-sm btn-outline-primary'; }
  });
  const tabId = {all:'tabAll',government:'tabGovt',private:'tabPrivate',deemed:'tabDeemed'}[type];
  if (tabId && document.getElementById(tabId)) {
    document.getElementById(tabId).className = 'btn btn-sm btn-primary';
  }
  const grid = document.getElementById('featured-colleges-grid');
  grid.innerHTML = '<div class="col-12 text-center py-4"><div class="ck-spinner mx-auto"></div></div>';
  let url = '/dashboard/api/colleges.php?featured=1&limit=6';
  if (type && type !== 'all') url += '&type=' + encodeURIComponent(type);
  fetch(url)
    .then(r => r.json())
    .then(data => {
      if (data.error) { grid.innerHTML = '<div class="col-12 text-center text-danger small py-3">Error: ' + escHtml(data.error) + '</div>'; return; }
      grid.innerHTML = renderColleges(data.colleges);
      updateCompareButtons();
    })
    .catch(() => {
      grid.innerHTML = '<div class="col-12 text-center text-muted py-3">Unable to load. <a href="colleges.php">Browse all universities</a></div>';
    });
}
loadFeatured('all');
</script>
</body>
</html>
