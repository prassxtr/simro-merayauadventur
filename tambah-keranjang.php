<?php
session_start();
require_once 'config/koneksi.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = (int)$_POST['produk_id'];
    $jumlah = (int)$_POST['jumlah'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_kembali = $_POST['tanggal_kembali'];

    // Validasi dasar
    if ($jumlah <= 0) {
        echo "<script>alert('Jumlah sewa minimal 1!'); window.history.back();</script>";
        exit;
    }

    if (strtotime($tanggal_kembali) < strtotime($tanggal_mulai)) {
        echo "<script>alert('Tanggal kembali tidak boleh lebih awal dari tanggal mulai!'); window.history.back();</script>";
        exit;
    }

    // 3. Ambil data produk dari database
    // Gunakan prepared statement untuk keamanan lebih baik
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id = ? AND status = 'tersedia'");
    mysqli_stmt_bind_param($stmt, "i", $produk_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);

    if ($produk) {
        // Hitung jumlah hari sewa
        $start = new DateTime($tanggal_mulai);
        $end = new DateTime($tanggal_kembali);
        $diff = $start->diff($end)->days + 1; // +1 karena hari pertama dihitung
        
        if ($diff <= 0) $diff = 1;

        // Pastikan session keranjang sudah berupa array
        if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }

        // 4. Cek apakah produk dengan tanggal yang sama sudah ada di keranjang
        $found_index = -1;
        foreach ($_SESSION['keranjang'] as $index => $item) {
            if ($item['produk_id'] == $produk_id && 
                $item['tgl_mulai'] == $tanggal_mulai && 
                $item['tgl_kembali'] == $tanggal_kembali) {
                $found_index = $index;
                break;
            }
        }

        if ($found_index !== -1) {
            // Jika sudah ada, update jumlahnya
            $new_jumlah = $_SESSION['keranjang'][$found_index]['jumlah'] + $jumlah;
            
            // Cek apakah total jumlah baru melebihi stok
            if ($new_jumlah > $produk['stok']) {
                echo "<script>alert('Maaf, stok tidak mencukupi untuk jumlah total ini! Sisa stok: " . $produk['stok'] . "'); window.history.back();</script>";
                exit;
            }

            $_SESSION['keranjang'][$found_index]['jumlah'] = $new_jumlah;
            $_SESSION['keranjang'][$found_index]['subtotal'] = $produk['harga_sewa'] * $new_jumlah * $diff;
            
        } else {
            // Jika belum ada, cek stok untuk item baru
            if ($produk['stok'] < $jumlah) {
                echo "<script>alert('Maaf, stok tidak mencukupi! Sisa stok: " . $produk['stok'] . "'); window.history.back();</script>";
                exit;
            }

            // Masukkan sebagai item baru ke Session Keranjang
            $item_baru = [
                'produk_id'   => $produk_id,
                'nama'        => $produk['nama_produk'],
                'harga'       => $produk['harga_sewa'],
                'jumlah'      => $jumlah,
                'tgl_mulai'   => $tanggal_mulai,
                'tgl_kembali' => $tanggal_kembali,
                'hari'        => $diff,
                'subtotal'    => $produk['harga_sewa'] * $jumlah * $diff,
                'gambar'      => $produk['gambar']
            ];

            $_SESSION['keranjang'][] = $item_baru;
        }

        // Redirect ke halaman keranjang
        header('Location: keranjang.php');
        exit;

    } else {
        echo "<script>alert('Produk tidak ditemukan atau sedang tidak tersedia!'); window.history.back();</script>";
        exit;
    }
} else {
    // Jika diakses bukan via POST
    header('Location: katalog.php');
    exit;
}
?>