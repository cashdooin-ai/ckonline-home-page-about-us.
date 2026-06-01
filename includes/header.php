<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>CollegeKampus Online</title>
    <meta name="description" content="<?= isset($pageDesc) ? htmlspecialchars($pageDesc) : 'Discover top colleges in India. Compare courses, fees, placements and apply online at CollegeKampus.' ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/portal.css">
</head>
<body>
<div class="ck-topbar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <span><i class="bi bi-envelope-fill me-1"></i>info@collegekampus.in</span>
        <span><i class="bi bi-telephone-fill me-1"></i>1800-123-4567 (Toll Free)</span>
    </div>
</div>
<nav class="navbar navbar-expand-lg ck-navbar sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/index.html">
            <div class="ck-logo-icon">CK</div>
            <div class="lh-1">
                <span class="ck-brand-main">CollegeKampus</span>
                <span class="ck-brand-sub d-block">Online</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <i class="bi bi-list fs-4 text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link <?= $currentPage==='index'?'active':'' ?>" href="/index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage==='colleges'?'active':'' ?>" href="/colleges.php">Colleges</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage==='courses'?'active':'' ?>" href="/courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="/index.html#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage==='contact'?'active':'' ?>" href="/contact.php">Contact</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-ck-primary btn-sm px-3" href="/colleges.php"><i class="bi bi-search me-1"></i>Find College</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
