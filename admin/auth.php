<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ck_admin_auth'])) {
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/login.php');
    exit;
}

// A logged-in admin's session cookie rides along with ANY request their
// browser sends, including one triggered by a crafted link/image/redirect
// on another site - a per-session token that only this app's own forms
// know proves the request actually came from a page this app rendered.
if (empty($_SESSION['ck_csrf_token'])) {
    $_SESSION['ck_csrf_token'] = bin2hex(random_bytes(32));
}
function ckCsrfToken(): string {
    return $_SESSION['ck_csrf_token'];
}
function ckCsrfCheck(): bool {
    return !empty($_POST['csrf_token']) && hash_equals($_SESSION['ck_csrf_token'], $_POST['csrf_token']);
}
