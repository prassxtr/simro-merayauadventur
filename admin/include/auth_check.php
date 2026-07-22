<?php
// admin/include/auth_check.php

// Cek apakah session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login DAN role-nya admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Simpan halaman yang diminta
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect ke login
    header("Location: ../login.php");
    exit();
}

// Cek timeout session (30 menit)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?timeout=1");
    exit();
}

// Update waktu aktivitas terakhir
$_SESSION['last_activity'] = time();
?>