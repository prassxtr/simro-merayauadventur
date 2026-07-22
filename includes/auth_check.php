<?php
// include/auth_user.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Simpan halaman yang diminta
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    // Redirect ke login
    header("Location: login.php");
    exit();
}

// Cek timeout (30 menit)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update waktu aktivitas
$_SESSION['last_activity'] = time();
?>