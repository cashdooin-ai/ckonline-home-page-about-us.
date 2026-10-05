<?php
session_start();
require_once __DIR__ . '/includes/credentials.php';

// Credentials resolve DB (site_settings, set via change-password.php) ->
// ADMIN_USER/ADMIN_PASS_HASH env vars -> hardcoded fallback. See
// includes/credentials.php for the resolution order and the fallback hash.
$creds     = ckGetAdminCredentials();
$adminUser = $creds['username'];
$adminHash = $creds['hash'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === $adminUser && password_verify($p, $adminHash)) {
        session_regenerate_id(true);
        $_SESSION['ck_admin_auth'] = true;
        $_SESSION['ck_admin_user'] = $u;
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}

if (!empty($_SESSION['ck_admin_auth'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — CollegeKampus</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
body{background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;}
.login-card{background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.12);padding:2.5rem 2rem;width:100%;max-width:400px;}
.ck-logo{width:44px;height:44px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:1rem;}
</style>
</head>
<body>
<div class="login-card">
  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="ck-logo">CK</div>
    <div>
      <div class="fw-800 fs-5 text-dark">CollegeKampus</div>
      <div class="text-muted" style="font-size:.75rem;">Admin Panel</div>
    </div>
  </div>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['changed'])): ?>
    <div class="alert alert-success py-2">Password changed — sign in with your new password.</div>
  <?php endif; ?>
  <form method="post" autocomplete="off">
    <div class="mb-3">
      <label class="form-label fw-600">Username</label>
      <input type="text" name="username" class="form-control" required autofocus>
    </div>
    <div class="mb-4">
      <label class="form-label fw-600">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Sign In</button>
  </form>
</div>
</body>
</html>
