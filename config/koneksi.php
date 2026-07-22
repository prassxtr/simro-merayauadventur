<?php
// Cek apakah session sudah aktif sebelum memulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_merayau_adventure";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Cek apakah constant sudah didefinisikan
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/simro-merayauadventur/');
}

// Bungkus fungsi dengan function_exists untuk mencegah redeclare
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('tanggal_indo')) {
    function tanggal_indo($tanggal) {
        $bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
}

if (!function_exists('buat_kode_pesanan')) {
    function buat_kode_pesanan() {
        $kode = 'MRW-' . date('Ymd') . '-';
        global $koneksi;
        $query = mysqli_query($koneksi, "SELECT nomor_pesanan FROM penyewaan WHERE nomor_pesanan LIKE '$kode%' ORDER BY nomor_pesanan DESC LIMIT 1");
        $data = mysqli_fetch_assoc($query);
        if ($data) {
            $urutan = (int) substr($data['nomor_pesanan'], -3);
            $urutan++;
        } else {
            $urutan = 1;
        }
        return $kode . sprintf('%03d', $urutan);
    }
}
?>