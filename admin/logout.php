<?php
// admin/logout.php

session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// PENTING: Redirect ke login.php di folder ROOT (naik satu tingkat dengan ../)
header("Location: ../login.php?logout=1");
exit();
?>