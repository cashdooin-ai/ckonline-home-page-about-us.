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

// SEO defaults — individual pages override these before including header
$pageTitle    = isset($pageTitle)    ? $pageTitle    : 'Online Colleges & Distance Education in India';
$pageDesc     = isset($pageDesc)     ? $pageDesc     : 'Discover 40,000+ online and distance colleges in India. Compare courses, fees, placements and apply free at CollegeKampus Online.';
$pageKeywords = isset($pageKeywords) ? $pageKeywords : 'online colleges India, distance education, UGC approved universities, online MBA, online BBA, online BCA, college admissions 2025';
$pageCanonical = isset($pageCanonical) ? $pageCanonical : SITE_BASE . '/' . basename($_SERVER['PHP_SELF']);
$pageOgImage  = isset($pageOgImage)  ? $pageOgImage  : SITE_BASE . '/assets/img/og-default.jpg';
$pageType     = isset($pageType)     ? $pageType     : 'website';

// JSON-LD structured data — pages can set $jsonLd array before including header
$defaultJsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'EducationalOrganization',
    'name'     => 'CollegeKampus Online',
    'url'      => SITE_BASE,
    'logo'     => SITE_BASE . '/assets/img/logo.png',
    'sameAs'   => [
        'https://www.facebook.com/collegekampus',
        'https://twitter.com/collegekampus',
        'https://www.instagram.com/collegekampus',
        'https://www.youtube.com/collegekampus',
    ],
    'contactPoint' => [
        '@type'       => 'ContactPoint',
        'telephone'   => '+91-1800-123-4567',
        'contactType' => 'customer service',
        'areaServed'  => 'IN',
    ],
];
$jsonLd = isset($jsonLd) ? $jsonLd : $defaultJsonLd;

// Base path for all assets (works from /dashboard/ subfolder)
$base = SITE_BASE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ── Primary SEO ─────────────────────────────────────────────── -->
    <title><?= htmlspecialchars($pageTitle) ?> | CollegeKampus Online</title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="keywords"    content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="robots"      content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="author"      content="CollegeKampus Online">
    <link rel="canonical"    href="<?= htmlspecialchars($pageCanonical) ?>">

    <!-- ── Open Graph (Facebook / WhatsApp / LinkedIn) ────────────── -->
    <meta property="og:type"        content="<?= htmlspecialchars($pageType) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?> | CollegeKampus Online">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($pageCanonical) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($pageOgImage) ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name"   content="CollegeKampus Online">
    <meta property="og:locale"      content="en_IN">

    <!-- ── Twitter Card ───────────────────────────────────────────── -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@collegekampus">
    <meta name="twitter:title"       content="<?= htmlspecialchars($pageTitle) ?> | CollegeKampus Online">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($pageOgImage) ?>">

    <!-- ── Structured Data (JSON-LD) ──────────────────────────────── -->
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></script>

    <!-- ── Favicon ────────────────────────────────────────────────── -->
    <link rel="icon" type="image/png" href="<?= $base ?>/assets/img/favicon.png">
    <link rel="apple-touch-icon"      href="<?= $base ?>/assets/img/apple-touch-icon.png">

    <!-- ── Fonts & Styles ─────────────────────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/portal.css">

    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<!-- ── Top bar ──────────────────────────────────────────────────────── -->
<div class="ck-topbar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center py-1">
        <span class="small"><i class="bi bi-envelope-fill me-1"></i>info@collegekampus.in</span>
        <span class="small"><i class="bi bi-telephone-fill me-1"></i>1800-123-4567 (Toll Free) &nbsp;|&nbsp; <i class="bi bi-clock me-1"></i>Mon–Sat 9am–7pm</span>
    </div>
</div>

<!-- ── Navbar ───────────────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg ck-navbar sticky-top" id="mainNav" role="navigation" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base ?>/index.html" aria-label="CollegeKampus Online Home">
            <div class="ck-logo-icon">CK</div>
            <div class="lh-1">
                <span class="ck-brand-main">CollegeKampus</span>
                <span class="ck-brand-sub d-block">Online</span>
            </div>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-4 text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='index'||$currentPage==='portal'?'active':'' ?>" href="<?= $base ?>/portal.html">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='colleges'?'active':'' ?>" href="<?= $base ?>/colleges.php">Colleges</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='courses'?'active':'' ?>" href="<?= $base ?>/courses.php">Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='compare'?'active':'' ?>" href="<?= $base ?>/compare.php">Compare</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='counselling'?'active':'' ?>" href="<?= $base ?>/counselling.php">Free Counselling</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/index.html#about">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage==='contact'?'active':'' ?>" href="<?= $base ?>/contact.php">Contact</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-ck-primary btn-sm px-3" href="<?= $base ?>/colleges.php">
                        <i class="bi bi-search me-1"></i>Find College
                    </a>
                </li>
                <?php if (!empty($_SESSION['compare_list'])): ?>
                <li class="nav-item ms-lg-1">
                    <a class="btn btn-outline-warning btn-sm px-3" href="<?= $base ?>/compare.php">
                        <i class="bi bi-bar-chart-line me-1"></i>Compare
                        <span class="badge bg-warning text-dark ms-1"><?= count($_SESSION['compare_list']) ?></span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
