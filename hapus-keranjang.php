<?php
session_start();

if (isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (isset($_SESSION['keranjang'][$index])) {
        unset($_SESSION['keranjang'][$index]);
        // Reset array index agar tidak berantakan
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    }
}

header('Location: keranjang.php');
exit;
?>