<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ck_admin_auth'])) {
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/login.php');
    exit;
}
