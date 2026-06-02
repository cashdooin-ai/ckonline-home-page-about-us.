<?php
/**
 * Admin Portal Bridge — secure redirect to ckampus-dashboard admin
 * Only accessible to users with admin session token
 */
require_once __DIR__ . '/config/db.php';

// Admin portal URL on Hostinger (same domain, different subfolder)
define('ADMIN_PORTAL_URL', 'https://collegekampus.com/admin');
define('PARTNER_PORTAL_URL', 'https://collegekampus.com/partner');

session_start();

$action = trim($_GET['action'] ?? '');

if ($action === 'admin') {
    header('Location: ' . ADMIN_PORTAL_URL);
    exit;
}
if ($action === 'partner') {
    header('Location: ' . PARTNER_PORTAL_URL);
    exit;
}

$pageTitle = 'Portal Access | CollegeKampus';
include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <h1 class="mb-4 text-center">Portal Access</h1>
        <div class="row g-4">
          <!-- Admin Portal Card -->
          <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size:3rem;">🛡️</div>
                <h4>Admin Portal</h4>
                <p class="text-muted small">Manage colleges, courses, leads and analytics. Full dashboard access for CollegeKampus administrators.</p>
                <a href="<?= ADMIN_PORTAL_URL ?>" class="btn btn-dark w-100 mt-2" target="_blank">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Open Admin Dashboard
                </a>
              </div>
            </div>
          </div>
          <!-- Partner Portal Card -->
          <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size:3rem;">🤝</div>
                <h4>Partner Portal</h4>
                <p class="text-muted small">For college partners to manage their listings, update courses, view student leads and inquiries.</p>
                <a href="<?= PARTNER_PORTAL_URL ?>" class="btn btn-success w-100 mt-2" target="_blank">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Open Partner Portal
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-5 p-4 bg-light rounded">
          <h5><i class="bi bi-info-circle me-2"></i>Integration Overview</h5>
          <ul class="mb-0 small text-muted">
            <li>All three portals share the same MySQL database: <code>u939138857_ckampus_dash26</code></li>
            <li>Student leads from this portal flow into the admin CRM automatically</li>
            <li>College data updated by partners appears live here within seconds</li>
            <li>Admin can feature/unfeature colleges — reflected here via <code>is_featured</code> flag</li>
            <li>Partner portal manages <code>college_courses</code>, <code>college_streams</code> tables</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
