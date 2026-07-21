<?php
header('Content-Type: application/json');
include('../config/koneksi.php');

$conn = isset($koneksi) ? $koneksi : die("Koneksi gagal");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT dp.produk_id, dp.jumlah, p.nama_produk, p.harga_sewa, p.stok 
        FROM detail_penyewaan dp 
        JOIN produk p ON dp.produk_id = p.id 
        WHERE dp.penyewaan_id = $id";

$result = mysqli_query($conn, $query);
$data = [];

while($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>