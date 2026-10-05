<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/includes/credentials.php';

$creds   = ckGetAdminCredentials();
$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = trim($_POST['current_password'] ?? '');
    $newUser = trim($_POST['new_username'] ?? '') ?: $creds['username'];
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $creds['hash'])) {
        $message = 'Current password is incorrect.';
        $msgType = 'error';
    } elseif (strlen($newPass) < 8) {
        $message = 'New password must be at least 8 characters.';
        $msgType = 'error';
    } elseif ($newPass !== $confirm) {
        $message = 'New password and confirmation do not match.';
        $msgType = 'error';
    } else {
        ckSaveAdminCredentials($newUser, password_hash($newPass, PASSWORD_DEFAULT));
        session_destroy();
        header('Location: login.php?changed=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password | CollegeKampus Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{background:#f1f5f9;font-family:'Inter',sans-serif;}
.topbar{background:#0f172a;color:#fff;padding:14px 24px;display:flex;align-items:center;gap:16px;}
.topbar a{color:rgba(255,255,255,.7);text-decoration:none;font-size:.875rem;}
.topbar a:hover{color:#fff;}
.admin-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:28px;max-width:460px;margin:40px auto;}
</style>
</head>
<body>

<div class="topbar d-flex align-items-center gap-3">
    <div style="font-size:1.1rem;font-weight:800;">🔑 Change Password</div>
    <div class="ms-auto d-flex gap-3">
        <a href="index.php"><i class="bi bi-arrow-left"></i> Back to Admin</a>
        <a href="/dashboard/" target="_blank"><i class="bi bi-house"></i> Front-end</a>
    </div>
</div>

<div class="admin-card">
    <p class="text-muted" style="font-size:.85rem;">Signed in as <strong><?= htmlspecialchars($creds['username']) ?></strong>. Changing your password here saves it to the database — it takes effect immediately and survives future deploys, unlike the old hardcoded default.</p>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType === 'error' ? 'danger' : 'success' ?> py-2"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label fw-bold">Current Password *</label>
            <input type="password" name="current_password" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input type="text" name="new_username" class="form-control" value="<?= htmlspecialchars($creds['username']) ?>">
            <small class="text-muted">Leave as-is to keep the current username.</small>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">New Password *</label>
            <input type="password" name="new_password" class="form-control" required minlength="8">
            <small class="text-muted">At least 8 characters.</small>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold">Confirm New Password *</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary fw-bold w-100"><i class="bi bi-save"></i> Change Password</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
