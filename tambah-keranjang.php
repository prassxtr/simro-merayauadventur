<?php
session_start();
require_once 'include/auth_user.php';
require_once 'config/koneksi.php';;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = (int)$_POST['produk_id'];
    $jumlah = (int)$_POST['jumlah'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_kembali = $_POST['tanggal_kembali'];

    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Ambil data produk dari database
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $produk_id AND status = 'tersedia'");
    $produk = mysqli_fetch_assoc($query);

    if ($produk && $produk['stok'] >= $jumlah) {
        // Hitung jumlah hari sewa
        $start = new DateTime($tanggal_mulai);
        $end = new DateTime($tanggal_kembali);
        $diff = $start->diff($end)->days + 1; // +1 karena hari pertama dihitung
        
        if ($diff <= 0) $diff = 1;

        $subtotal = $produk['harga_sewa'] * $jumlah * $diff;

        // Masukkan ke Session Keranjang
        $item = [
            'produk_id' => $produk_id,
            'nama' => $produk['nama_produk'],
            'harga' => $produk['harga_sewa'],
            'jumlah' => $jumlah,
            'tgl_mulai' => $tanggal_mulai,
            'tgl_kembali' => $tanggal_kembali,
            'hari' => $diff,
            'subtotal' => $subtotal,
            'gambar' => $produk['gambar']
        ];

        $_SESSION['keranjang'][] = $item;
        header('Location: keranjang.php');
        exit;
    } else {
        echo "<script>alert('Maaf, stok tidak mencukupi!'); window.history.back();</script>";
        exit;
    }
}
?>