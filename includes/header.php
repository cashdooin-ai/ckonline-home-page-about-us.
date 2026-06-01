<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$compareCount = isset($_SESSION['compare_ids']) ? count($_SESSION['compare_ids']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | CollegeKampus Online' : 'CollegeKampus Online – Find & Apply to Online Colleges' ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? htmlspecialchars($pageDesc) : 'Browse 40,000+ colleges, compare courses, apply online and get free expert counselling.' ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/style.css">
  <link rel="stylesheet" href="/assets/css/portal.css">
</head>
<body>

<nav class="navbar">
  <div class="container nav-inner">
    <a href="/index.html" class="logo">
      <span class="logo-icon">🎓</span>
      <span class="logo-name"><strong>CollegeKampus</strong> Online</span>
      <span class="logo-badge">Online</span>
    </a>
    <ul class="nav-links" id="navLinks">
      <li><a href="/index.html" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
      <li><a href="/colleges.php" class="<?= $currentPage === 'colleges' ? 'active' : '' ?>">Colleges</a></li>
      <li><a href="/courses.php" class="<?= $currentPage === 'courses' ? 'active' : '' ?>">Courses</a></li>
      <li><a href="/compare.php" class="<?= $currentPage === 'compare' ? 'active' : '' ?>">Compare</a></li>
      <li><a href="/index.html#about" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About Us</a></li>
      <li><a href="/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a href="/compare.php" class="btn-compare" id="navCompareBtn" style="<?= $compareCount === 0 ? 'display:none' : '' ?>">
        Compare (<span id="navCompareCount"><?= $compareCount ?></span>)
      </a>
      <a href="/counselling.php" class="btn-signin">Free Counselling</a>
    </div>
    <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">&#9776;</button>
  </div>
</nav>

<script>
document.getElementById('hamburgerBtn').addEventListener('click', function() {
  var nl = document.getElementById('navLinks');
  nl.style.display = nl.style.display === 'flex' ? 'none' : 'flex';
  nl.style.flexDirection = 'column';
  nl.style.position = 'absolute';
  nl.style.top = '68px';
  nl.style.left = '0';
  nl.style.right = '0';
  nl.style.background = '#fff';
  nl.style.padding = '16px 24px';
  nl.style.borderBottom = '1px solid #e2e8f0';
  nl.style.zIndex = '999';
});
</script>
