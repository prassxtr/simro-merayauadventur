<?php
// 1. Hubungkan ke database secara paksa
if (file_exists('../config/koneksi.php')) {
    include '../config/koneksi.php';
} else if (file_exists('config/koneksi.php')) {
    include 'config/koneksi.php';
}

// Samakan variabel dengan milik temanmu
$conn = isset($koneksi) ? $koneksi : (isset($conn) ? $conn : false);

if (!$conn) {
    die("Koneksi Database Gagal. Pastikan file config/koneksi.php sudah benar.");
}

// ==========================================
// 2. PROSES AKSI: UPDATE STATUS OPERASIONAL
// ==========================================
if (isset($_POST['update_operasional'])) {
    $id_sewa     = mysqli_real_escape_string($conn, $_POST['id_sewa']);
    $status_pemb = mysqli_real_escape_string($conn, $_POST['status_pembayaran']); 
    $status_sewa = mysqli_real_escape_string($conn, $_POST['status_sewa']);       
    
    $query_update = "UPDATE penyewaan SET status_pembayaran='$status_pemb', status_sewa='$status_sewa' WHERE id='$id_sewa'";
    if (mysqli_query($conn, $query_update)) {
        header("Location: penyewaan.php");
        exit;
    }
}

// ==========================================
// 3. AMBIL DATA DENGAN LOGIKA DETEKSI KOLOM
// ==========================================
$kolom_nama = "id"; // Default fallback

$cek_kolom = mysqli_query($conn, "SHOW COLUMNS FROM users");
if ($cek_kolom) {
    $list_kolom = [];
    while ($k = mysqli_fetch_assoc($cek_kolom)) {
        $list_kolom[] = strtolower($k['Field']);
    }

    if (in_array('username', $list_kolom)) {
        $kolom_nama = "username";
    } elseif (in_array('name', $list_kolom)) {
        $kolom_nama = "name";
    } elseif (in_array('nama_lengkap', $list_kolom)) {
        $kolom_nama = "nama_lengkap";
    } elseif (in_array('email', $list_kolom)) {
        $kolom_nama = "email";
    }
}

$query_tampil = "SELECT p.*, u.$kolom_nama AS nama_penyewa 
                 FROM penyewaan p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 ORDER BY p.id DESC";
$result = mysqli_query($conn, $query_tampil);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyewaan - SIMRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 280px; padding: 30px; min-height: 100vh; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .table-container { background: white; border: 1px solid #e0e0e0; border-radius: 16px; padding: 25px; }
        .text-maroon { color: #800000; font-weight: 700; }
        .btn-maroon { background-color: #800000; color: white; }
        .btn-maroon:hover { background-color: #600000; color: white; }
        th { font-size: 0.75rem; text-transform: uppercase; color: #888888; letter-spacing: 0.5px; }
        
        /* Variasi Badge Warna Status Pembayaran */
        .badge-pending { background-color: #FFF3CD; color: #856404; border: 1px solid #FFEBAA; }
        .badge-belum-lunas { background-color: #E2E3E5; color: #383D41; border: 1px solid #D6D8DB; }
        .badge-lunas { background-color: #D4EDDA; color: #155724; border: 1px solid #C3E6CB; }
        .badge-dibatalkan { background-color: #F8D7DA; color: #721c24; border: 1px solid #F5C6CB; }
        
        .click-user { cursor: pointer; transition: color 0.2s; }
        .click-user:hover { color: #800000 !important; text-decoration: underline; }
    </style>
</head>
<body>

    <!-- Memanggil file sidebar -->
    <?php 
    if (file_exists('include/sidebar.php')) { include 'include/sidebar.php'; } 
    elseif (file_exists('includes/sidebar.php')) { include 'includes/sidebar.php'; }
    ?>

    <div class="main-content">
        <div class="header-section">
            <div>
                <h4 class="fw-bold m-0 text-dark">Daftar Transaksi Penyewaan</h4>
                <small class="text-muted">Klik nama pelanggan untuk meninjau berkas/bukti transfer pembayaran.</small>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr style="border-bottom: 2px solid #f0f0f0;">
                            <th>Nomor Pesanan</th>
                            <th>Nama Pelanggan</th>
                            <th>Tanggal Sewa / Kembali</th>
                            <th>Total Harga</th>
                            <th>Status Bayar</th>
                            <th>Status Sewa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) { 
                                // Deteksi badge pembayaran yang fleksibel
                                $pay_status = strtolower($row['status_pembayaran']);
                                if ($pay_status == 'lunas') {
                                    $badge_pay = "badge-lunas";
                                } elseif ($pay_status == 'belum lunas') {
                                    $badge_pay = "badge-belum-lunas";
                                } elseif ($pay_status == 'dibatalkan') {
                                    $badge_pay = "badge-dibatalkan";
                                } else {
                                    $badge_pay = "badge-pending"; // default untuk 'pending'
                                }
                        ?>
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td><strong class="font-monospace text-dark"><?= $row['nomor_pesanan']; ?></strong></td>
                            
                            <!-- KLIK NAMA DI SINI UNTUK MEMBUKA DETAIL BUKTI -->
                            <td>
                                <div class="click-user" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id']; ?>">
                                    <strong class="text-dark d-block text-capitalize">
                                        <?= (!empty($row['nama_penyewa']) && $row['nama_penyewa'] != $row['user_id']) ? $row['nama_penyewa'] : 'Pelanggan'; ?>
                                        <i class="fa-solid fa-receipt text-muted small ms-1" style="font-size:10px;"></i>
                                    </strong>
                                    <small class="text-muted font-monospace" style="font-size: 11px;">User ID: <?= $row['user_id']; ?></small>
                                </div>
                            </td>

                            <td>
                                <div class="small"><i class="fa-regular fa-calendar text-success me-1"></i> Mulai: <?= $row['tanggal_sewa']; ?></div>
                                <div class="small text-muted"><i class="fa-regular fa-calendar-minus text-danger me-1"></i> Kembali: <?= $row['tanggal_kembali']; ?></div>
                            </td>
                            <td><div class="fw-bold text-maroon">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></div></td>
                            <td><span class="badge <?= $badge_pay ?> px-3 py-1.5 rounded"><?= ucfirst($row['status_pembayaran']); ?></span></td>
                            <td><span class="badge bg-secondary px-3 py-1.5 text-white rounded text-capitalize"><?= $row['status_sewa']; ?></span></td>
                            
                            <td class="text-center">
                                <button class="btn btn-link text-maroon p-0" data-bs-toggle="modal" data-bs-target="#modalEditSewa<?= $row['id']; ?>">
                                    <i class="fa-solid fa-pen-to-square fs-5"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- MODAL PREVIEW BUKTI PEMBAYARAN -->
                        <div class="modal fade" id="modalBukti<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content p-3" style="border-radius:15px;">
                                    <div class="modal-header border-0 pb-2">
                                        <h6 class="modal-title fw-bold text-dark">Bukti Pembayaran</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center p-2 bg-light rounded" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                        <?php 
                                        if(!empty($row['bukti_pembayaran'])): 
                                            $nama_file = $row['bukti_pembayaran'];
                                        ?>
                                            <!-- Kita paksa browser memuat dari folder aset utama di luar folder admin -->
                                            <img src="../assets/img/bukti/<?= $nama_file; ?>" 
                                                class="img-fluid rounded border shadow-sm mb-2" 
                                                style="max-height: 350px; object-fit: contain; width: 100%;" 
                                                alt="Bukti Transfer"
                                                onerror="this.onerror=null; this.src='../assets/img/<?= $nama_file; ?>'; this.onerror=function(){this.src='../uploads/<?= $nama_file; ?>'; this.onerror=function(){this.src='https://placehold.co/300x400/e9ecef/6c757d?text=Cek+Folder+Upload';}}">
                                                
                                        <?php else: ?>
                                            <div class="text-muted small">
                                                <i class="fa-solid fa-image-blur fs-1 d-block mb-2 text-secondary"></i>
                                                Belum mengunggah<br>bukti pembayaran.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-center mt-3 small text-muted font-monospace">
                                        Nota: <?= $row['nomor_pesanan']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE STATUS -->
                        <div class="modal fade" id="modalEditSewa<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content p-3" style="border-radius:15px;">
                                    <div class="modal-header border-0 pb-0">
                                        <h6 class="modal-title fw-bold text-maroon">UPDATE STATUS TRANSAKSI</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="">
                                        <input type="hidden" name="id_sewa" value="<?= $row['id']; ?>">
                                        <div class="modal-body py-3">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">PEMBAYARAN</label>
                                                <select name="status_pembayaran" class="form-select">
                                                    <option value="pending" <?= $row['status_pembayaran'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="belum lunas" <?= $row['status_pembayaran'] == 'belum lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                                                    <option value="lunas" <?= $row['status_pembayaran'] == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                                    <option value="dibatalkan" <?= $row['status_pembayaran'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-muted">STATUS SEWA</label>
                                                <select name="status_sewa" class="form-select">
                                                    <option value="diproses" <?= $row['status_sewa'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                    <option value="disewa" <?= $row['status_sewa'] == 'disewa' ? 'selected' : '' ?>>Disewa</option>
                                                    <option value="selesai" <?= $row['status_sewa'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                    <option value="dibatalkan" <?= $row['status_sewa'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="px-3 pb-2">
                                            <button type="submit" name="update_operasional" class="btn btn-maroon w-100 py-2 rounded-3">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-5 text-muted'>Benar-benar tidak ada data yang terbaca dari database.</td></tr>";
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>