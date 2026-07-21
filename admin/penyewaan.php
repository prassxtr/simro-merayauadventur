<?php
// 1. KONEKSI DATABASE
if (file_exists('../config/koneksi.php')) {
    include '../config/koneksi.php';
} else if (file_exists('config/koneksi.php')) {
    include 'config/koneksi.php';
}

$conn = isset($koneksi) ? $koneksi : (isset($conn) ? $conn : false);
if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// 2. PROSES AKSI: TAMBAH PENYEWAAN BARU (KASIR)
if (isset($_POST['tambah_penyewaan'])) {
    $nomor_pesanan     = mysqli_real_escape_string($conn, $_POST['nomor_pesanan']);
    $user_id           = intval($_POST['user_id']);
    $tanggal_sewa      = mysqli_real_escape_string($conn, $_POST['tanggal_sewa']);
    $tanggal_kembali   = mysqli_real_escape_string($conn, $_POST['tanggal_kembali']);
    $total_harga       = floatval($_POST['total_harga']);
    $status_pembayaran = mysqli_real_escape_string($conn, $_POST['status_pembayaran']);
    $status_sewa       = mysqli_real_escape_string($conn, $_POST['status_sewa']);
    
    $sudah_dibayar     = isset($_POST['sudah_dibayar']) ? floatval($_POST['sudah_dibayar']) : 0;

    if ($status_pembayaran == 'lunas') {
        $sudah_dibayar = $total_harga;
    }

    // Data Rincian Barang
    $arr_barang_id     = isset($_POST['barang_id']) ? $_POST['barang_id'] : [];
    $arr_barang_nama   = isset($_POST['barang_nama']) ? $_POST['barang_nama'] : [];
    $arr_jumlah        = isset($_POST['jumlah_item']) ? $_POST['jumlah_item'] : [];

    // Simpan Transaksi Penyewaan Utama
    $q_tambah = "INSERT INTO penyewaan (nomor_pesanan, user_id, tanggal_sewa, tanggal_kembali, total_harga, sudah_dibayar, status_pembayaran, status_sewa) 
                 VALUES ('$nomor_pesanan', '$user_id', '$tanggal_sewa', '$tanggal_kembali', '$total_harga', '$sudah_dibayar', '$status_pembayaran', '$status_sewa')";
    
    $simpan = mysqli_query($conn, $q_tambah);

    if ($simpan) {
        $penyewaan_id = mysqli_insert_id($conn);

        // Simpan Detail Penyewaan & Potong Stok Produk
        for ($i = 0; $i < count($arr_barang_id); $i++) {
            $b_id  = intval($arr_barang_id[$i]);
            $b_qty = intval($arr_jumlah[$i]);

            // Ambil harga produk untuk hitung subtotal
            $q_harga = mysqli_query($conn, "SELECT harga_sewa FROM produk WHERE id = '$b_id'");
            $d_harga = mysqli_fetch_assoc($q_harga);
            $harga_sewa = isset($d_harga['harga_sewa']) ? floatval($d_harga['harga_sewa']) : 0;
            $subtotal   = $harga_sewa * $b_qty;

            // Simpan ke tabel detail_penyewaan
            mysqli_query($conn, "INSERT INTO detail_penyewaan (penyewaan_id, produk_id, jumlah, subtotal) VALUES ('$penyewaan_id', '$b_id', '$b_qty', '$subtotal')");

            // Kurangi stok produk
            mysqli_query($conn, "UPDATE produk SET stok = stok - $b_qty WHERE id = '$b_id' AND stok >= $b_qty");
        }

        header("Location: penyewaan.php?pesan=berhasil_tambah");
        exit();
    }
}

// 3. PROSES AKSI: HAPUS DATA & RESTOCK STOK
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = intval($_GET['id']);
    
    // Kembalikan stok produk dari detail_penyewaan
    $q_detail = mysqli_query($conn, "SELECT produk_id, jumlah FROM detail_penyewaan WHERE penyewaan_id = '$id'");
    if ($q_detail) {
        while ($d = mysqli_fetch_assoc($q_detail)) {
            $p_id  = intval($d['produk_id']);
            $p_qty = intval($d['jumlah']);
            mysqli_query($conn, "UPDATE produk SET stok = stok + $p_qty WHERE id = '$p_id'");
        }
    }

    // Hapus detail & transaksi utama
    mysqli_query($conn, "DELETE FROM detail_penyewaan WHERE penyewaan_id = '$id'");
    if (mysqli_query($conn, "DELETE FROM penyewaan WHERE id = '$id'")) {
        header("Location: penyewaan.php?pesan=berhasil_hapus");
        exit();
    }
}

// 4. PROSES AKSI: UPDATE STATUS OPERASIONAL & PEMBAYARAN
if (isset($_POST['update_status'])) {
    $id_sewa       = intval($_POST['id_sewa']);
    $total_harga   = floatval($_POST['total_harga_val']);
    $status_pemb   = mysqli_real_escape_string($conn, strtolower(trim($_POST['status_pembayaran'])));
    $status_sewa   = mysqli_real_escape_string($conn, strtolower(trim($_POST['status_sewa'])));
    
    $sudah_dibayar = isset($_POST['sudah_dibayar']) ? floatval($_POST['sudah_dibayar']) : 0;

    if ($status_pemb == 'lunas') {
        $sudah_dibayar = $total_harga;
    }

    // Update data di database
    $q_update = "UPDATE penyewaan SET 
                    status_pembayaran = '$status_pemb', 
                    status_sewa = '$status_sewa', 
                    sudah_dibayar = '$sudah_dibayar' 
                 WHERE id = '$id_sewa'";

    if (mysqli_query($conn, $q_update)) {
        header("Location: penyewaan.php?pesan=berhasil_update");
        exit();
    }
}

// 5. AMBIL DATA USERS
$users_list = [];
$q_u = mysqli_query($conn, "SELECT id, nama_lengkap FROM users ORDER BY nama_lengkap ASC");
if ($q_u) {
    while ($u = mysqli_fetch_assoc($q_u)) {
        $users_list[] = $u;
    }
}

// 6. AMBIL DATA PRODUK
$produk_list = [];
$q_p = mysqli_query($conn, "SELECT id, nama_produk, harga_sewa, stok FROM produk WHERE status != 'maintenance' ORDER BY nama_produk ASC");
if ($q_p) {
    while ($p = mysqli_fetch_assoc($q_p)) {
        $produk_list[] = $p;
    }
}

// 7. AMBIL DATA TRANSAKSI PENYEWAAN
$q_sewa = "SELECT penyewaan.*, users.nama_lengkap AS nama_pelanggan 
           FROM penyewaan 
           LEFT JOIN users ON penyewaan.user_id = users.id 
           ORDER BY penyewaan.id DESC";
$result = mysqli_query($conn, $q_sewa);

$nota_otomatis = "NTA-" . date('ymd') . "-" . sprintf("%03d", rand(1, 999));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyewaan Kasir - SIMRO</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body { background-color: #f4f5f8; font-family: 'Segoe UI', system-ui, sans-serif; color: #2c3345; }
        .main-content { margin-left: 260px; padding: 25px 35px; min-height: 100vh; }
        @media (max-width: 991px) { .main-content { margin-left: 0; padding: 15px; } }
        
        .btn-brand { background-color: #800000; color: #fff; font-weight: 600; border-radius: 8px; border: none; }
        .btn-brand:hover { background-color: #600000; color: #fff; }
        .card-custom { background: #fff; border: 1px solid #e2e7f0; border-radius: 12px; padding: 20px; }
        
        .table-clean th { font-size: 0.75rem; text-transform: uppercase; color: #8c97a8; border-bottom: 1px solid #edf2f7; padding: 12px; }
        .table-clean td { padding: 14px 12px; vertical-align: middle; font-size: 0.875rem; }
        
        .badge-status { font-size: 0.72rem; font-weight: 700; padding: 4px 10px; border-radius: 12px; text-transform: capitalize; }
        .bg-dp { background-color: #fef3c7; color: #d97706; }
        .bg-lunas { background-color: #dcfce7; color: #15803d; }
        .bg-batal { background-color: #fee2e2; color: #b91c1c; }
        .box-kasir { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; }
    </style>
</head>
<body>

    <!-- IMPORT SIDEBAR -->
    <?php 
    if (file_exists('include/sidebar.php')) include 'include/sidebar.php'; 
    elseif (file_exists('includes/sidebar.php')) include 'includes/sidebar.php'; 
    elseif (file_exists('../include/sidebar.php')) include '../include/sidebar.php'; 
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold m-0 d-inline">Kelola Penyewaan</h4>
                <span class="text-muted ms-3 border-start ps-3 small"><?= date('l, j F Y'); ?></span>
            </div>
            <button type="button" class="btn btn-brand btn-sm px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalKasirSewa">
                <i class="fa-solid fa-cart-plus me-1"></i> Buat Sewa Baru (Kasir)
            </button>
        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php 
                    if ($_GET['pesan'] == 'berhasil_tambah') echo "Sewa berhasil disimpan & stok produk berkurang!";
                    elseif ($_GET['pesan'] == 'berhasil_update') echo "Status transaksi & pembayaran berhasil diperbarui!";
                    elseif ($_GET['pesan'] == 'berhasil_hapus') echo "Transaksi dihapus & stok produk dikembalikan!";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold m-0 text-uppercase">Daftar Transaksi Penyewaan</h6>
                    <small class="text-muted">Kelola status, rincian produk, sisa tagihan, dan operasional sewa.</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-clean align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NOTA / PELANGGAN</th>
                            <th>BARANG YANG DISEWA</th>
                            <th>TANGGAL SEWA</th>
                            <th>KEUANGAN</th>
                            <th>STATUS</th>
                            <th class="text-center">AKSI OPERASIONAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                    $tot_harga   = floatval($row['total_harga']);
                                    $sdh_bayar   = isset($row['sudah_dibayar']) ? floatval($row['sudah_dibayar']) : 0;
                                    $sisa_tampil = max(0, $tot_harga - $sdh_bayar);

                                    // Ambil Rincian Produk dari tabel detail_penyewaan
                                    $sewa_id = $row['id'];
                                    $rincian_items = [];
                                    $q_dtl = mysqli_query($conn, "SELECT dp.jumlah, p.nama_produk 
                                                                  FROM detail_penyewaan dp 
                                                                  JOIN produk p ON dp.produk_id = p.id 
                                                                  WHERE dp.penyewaan_id = '$sewa_id'");
                                    if ($q_dtl && mysqli_num_rows($q_dtl) > 0) {
                                        while ($dtl = mysqli_fetch_assoc($q_dtl)) {
                                            $rincian_items[] = "- " . htmlspecialchars($dtl['nama_produk']) . " (" . $dtl['jumlah'] . " Unit)";
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <small class="text-muted font-monospace d-block"><?= $row['nomor_pesanan']; ?></small>
                                        <strong class="text-dark">
                                            <?= !empty($row['nama_pelanggan']) ? htmlspecialchars($row['nama_pelanggan']) : (!empty($row['user_id']) ? "Pelanggan #" . $row['user_id'] : "Pelanggan Umum"); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="d-block fw-semibold text-wrap" style="max-width: 250px;">
                                            <?= !empty($rincian_items) ? implode("<br>", $rincian_items) : '<span class="text-muted italic">Transaksi ID #' . $row['id'] . '</span>'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted"><i class="fa-regular fa-circle-check text-success me-1"></i>Ambil: <?= $row['tanggal_sewa']; ?></small>
                                        <small class="d-block text-muted"><i class="fa-regular fa-circle-xmark text-danger me-1"></i>Kembali: <?= $row['tanggal_kembali']; ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">Rp <?= number_format($tot_harga, 0, ',', '.'); ?></div>
                                        <small class="<?= strtolower($row['status_pembayaran']) == 'lunas' ? 'text-success fw-bold' : 'text-warning fw-bold' ?>">
                                            <?= ucfirst($row['status_pembayaran']); ?>
                                        </small>
                                        
                                        <?php if (strtolower($row['status_pembayaran']) != 'lunas'): ?>
                                            <div class="small text-muted mt-1" style="font-size: 0.78rem;">
                                                Masuk: <span class="text-success">Rp <?= number_format($sdh_bayar, 0, ',', '.'); ?></span><br>
                                                Sisa: <span class="text-danger fw-bold">Rp <?= number_format($sisa_tampil, 0, ',', '.'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $st_pemb = strtolower($row['status_pembayaran']);
                                            $badge = 'bg-dp';
                                            if ($st_pemb == 'lunas') $badge = 'bg-lunas';
                                            elseif ($st_pemb == 'dibatalkan') $badge = 'bg-batal';
                                        ?>
                                        <span class="badge-status <?= $badge; ?>">
                                            <?= ucfirst($row['status_pembayaran']); ?> (<?= ucfirst($row['status_sewa']); ?>)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light text-primary border-0 me-1" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id']; ?>" title="Lihat Bukti Bayar">
                                            <i class="fa-solid fa-image"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-light text-warning border-0 me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id']; ?>" title="Edit Status">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <a href="penyewaan.php?aksi=hapus&id=<?= $row['id']; ?>" class="btn btn-sm btn-light text-danger border-0" onclick="return confirm('Hapus transaksi <?= $row['nomor_pesanan']; ?>? (Stok akan dikembalikan)')" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- MODAL BUKTI BAYAR -->
                                <div class="modal fade" id="modalBukti<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Bukti Pembayaran - <?= $row['nomor_pesanan']; ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center p-4">
                                                <?php 
                                                    $file_bukti = isset($row['bukti_pembayaran']) ? $row['bukti_pembayaran'] : '';
                                                    $path_bukti = "../assets/img/bukti/" . $file_bukti;
                                                ?>
                                                <?php if (!empty($file_bukti) && file_exists($path_bukti)): ?>
                                                    <img src="<?= $path_bukti; ?>" class="img-fluid rounded shadow-sm" style="max-height: 400px;" alt="Bukti Transfer">
                                                    <a href="<?= $path_bukti; ?>" target="_blank" class="btn btn-sm btn-outline-primary d-block mt-3">
                                                        <i class="fa-solid fa-up-right-from-square me-1"></i> Buka Ukuran Penuh
                                                    </a>
                                                <?php else: ?>
                                                    <div class="py-4 text-muted">
                                                        <i class="fa-regular fa-image fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                                        Tidak ada bukti pembayaran diunggah.<br>
                                                        <small class="text-muted">(Transaksi diinput langsung dari Kasir Admin)</small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL EDIT STATUS & KALKULATOR SISA BAYAR -->
                                <div class="modal fade modal-edit-sewa" id="modalEdit<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Update Status & Pembayaran - <?= $row['nomor_pesanan']; ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id_sewa" value="<?= $row['id']; ?>">
                                                    <input type="hidden" class="val-total" name="total_harga_val" value="<?= $tot_harga; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Total Transaksi</label>
                                                        <input type="text" class="form-control bg-light fw-bold text-dark" value="Rp <?= number_format($tot_harga, 0, ',', '.'); ?>" readonly>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Status Pembayaran</label>
                                                        <select name="status_pembayaran" class="form-select status-bayar-select" onchange="toggleBoxKalkulator(this)">
                                                            <option value="pending" <?= strtolower($row['status_pembayaran']) == 'pending' ? 'selected' : '' ?>>Pending / Booking DP</option>
                                                            <option value="belum lunas" <?= strtolower($row['status_pembayaran']) == 'belum lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                                                            <option value="lunas" <?= strtolower($row['status_pembayaran']) == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                                            <option value="dibatalkan" <?= strtolower($row['status_pembayaran']) == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                        </select>
                                                    </div>

                                                    <!-- KALKULATOR SISA BAYAR (OTOMATIS RESPONSIF) -->
                                                    <div class="p-3 bg-light rounded border mb-3 box-kalkulator <?= strtolower($row['status_pembayaran']) == 'lunas' ? 'd-none' : '' ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-success">Jumlah Yang Sudah Dibayar (Rp)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" 
                                                                       name="sudah_dibayar" 
                                                                       class="form-control val-dibayar" 
                                                                       value="<?= $sdh_bayar; ?>" 
                                                                       min="0"
                                                                       max="<?= $tot_harga; ?>"
                                                                       oninput="hitungSisaTagihan(this)"
                                                                       onkeyup="hitungSisaTagihan(this)">
                                                            </div>
                                                        </div>

                                                        <div class="p-2 bg-white rounded border">
                                                            <label class="form-label small fw-bold text-danger m-0 d-block">Sisa Tagihan (Otomatis):</label>
                                                            <h4 class="fw-bold text-danger m-0 lbl-sisa">Rp <?= number_format($sisa_tampil, 0, ',', '.'); ?></h4>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Status Sewa</label>
                                                        <select name="status_sewa" class="form-select">
                                                            <option value="diproses" <?= strtolower($row['status_sewa']) == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                            <option value="disewa" <?= strtolower($row['status_sewa']) == 'disewa' ? 'selected' : '' ?>>Disewa</option>
                                                            <option value="selesai" <?= strtolower($row['status_sewa']) == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                            <option value="dibatalkan" <?= strtolower($row['status_sewa']) == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-brand">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data penyewaan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL KASIR SEWA BARU -->
    <div class="modal fade" id="modalKasirSewa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-cash-register text-danger me-2"></i>Sistem Kasir Sewa Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">No. Nota / Pesanan</label>
                                <input type="text" name="nomor_pesanan" class="form-control font-monospace" value="<?= $nota_otomatis; ?>" required readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Pilih Pelanggan (Cari Name/ID)</label>
                                <select name="user_id" id="select_user" class="form-select select2-search" required>
                                    <option value="">-- Cari Pelanggan --</option>
                                    <?php foreach ($users_list as $user_item): ?>
                                        <option value="<?= $user_item['id']; ?>"><?= htmlspecialchars($user_item['nama_lengkap']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tanggal Ambil</label>
                                <input type="date" name="tanggal_sewa" id="tgl_sewa" class="form-control" value="<?= date('Y-m-d'); ?>" onchange="hitungTotalKasir()" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tanggal Kembali</label>
                                <input type="date" name="tanggal_kembali" id="tgl_kembali" class="form-control" value="<?= date('Y-m-d', strtotime('+2 days')); ?>" onchange="hitungTotalKasir()" required>
                            </div>
                        </div>

                        <!-- PANEL KASIR PRODUK -->
                        <div class="box-kasir mb-3">
                            <label class="form-label small fw-bold text-uppercase text-primary"><i class="fa-solid fa-plus-circle me-1"></i>Pilih Produk (Ketik untuk mencari)</label>
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <select id="pilih_barang" class="form-select select2-search">
                                        <option value="">-- Cari Produk --</option>
                                        <?php if (!empty($produk_list)): ?>
                                            <?php foreach ($produk_list as $prod): ?>
                                                <option value="<?= $prod['id']; ?>" 
                                                        data-nama="<?= htmlspecialchars($prod['nama_produk']); ?>" 
                                                        data-harga="<?= $prod['harga_sewa']; ?>" 
                                                        data-stok="<?= $prod['stok']; ?>">
                                                    <?= htmlspecialchars($prod['nama_produk']); ?> (Rp <?= number_format($prod['harga_sewa'], 0, ',', '.'); ?>/hr - Stok: <?= $prod['stok']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" id="pilih_jumlah" class="form-control form-control-sm" value="1" min="1" placeholder="Jumlah Unit" style="height: 38px;">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100 fw-bold" onclick="tambahKeKeranjang()" style="height: 38px;">
                                        <i class="fa-solid fa-plus me-1"></i> Tambahkan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- KERANJANG -->
                        <div class="table-responsive mb-3 border rounded">
                            <table class="table table-sm table-striped align-middle mb-0" id="tabelKeranjang">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Produk</th>
                                        <th width="130">Harga / Hari</th>
                                        <th width="90">Jumlah</th>
                                        <th width="140">Subtotal</th>
                                        <th width="50" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyKeranjang">
                                    <tr id="barisKosong">
                                        <td colspan="5" class="text-center text-muted py-3 small">Belum ada produk dipilih. Silakan pilih produk di atas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- RINGKASAN BAYAR MODAL SEWA BARU -->
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Status Pembayaran & Sewa</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <select name="status_pembayaran" id="kasir_status_pembayaran" class="form-select form-select-sm" onchange="toggleKasirDibayar()" required>
                                            <option value="pending">Booking / DP</option>
                                            <option value="lunas" selected>Lunas</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select name="status_sewa" class="form-select form-select-sm" required>
                                            <option value="disewa" selected>Disewa</option>
                                            <option value="diproses">Diproses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-2 d-none" id="boxKasirDibayar">
                                    <label class="form-label small fw-bold text-success m-0">Jumlah DP / Dibayar (Rp)</label>
                                    <input type="number" name="sudah_dibayar" id="kasir_sudah_dibayar" class="form-control form-control-sm" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="d-block small text-muted">Durasi Sewa: <strong id="lblDurasi">2</strong> Hari</span>
                                <span class="d-block small text-muted">Total Pembayaran:</span>
                                <h3 class="fw-bold text-success m-0" id="lblGrandTotal">Rp 0</h3>
                                <input type="hidden" name="total_harga" id="inputGrandTotal" value="0">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_penyewaan" class="btn btn-sm btn-brand px-4">Simpan Transaksi Kasir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LIBRARIES -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // FUNGSI KALKULATOR SISA PEMBAYARAN REAL-TIME (NATIVE JS)
        function hitungSisaTagihan(inputElemen) {
            let modal = inputElemen.closest('.modal-edit-sewa');
            if (!modal) return;

            let inputTotal = modal.querySelector('.val-total');
            let labelSisa  = modal.querySelector('.lbl-sisa');

            if (!inputTotal || !labelSisa) return;

            let total   = parseFloat(inputTotal.value) || 0;
            let dibayar = parseFloat(inputElemen.value) || 0;

            let sisa = total - dibayar;
            if (sisa < 0) sisa = 0;

            labelSisa.innerText = "Rp " + Math.round(sisa).toLocaleString('id-ID');
        }

        // TAMPILKAN / SEMBUNYIKAN BOX KALKULATOR
        function toggleBoxKalkulator(selectElemen) {
            let modal = selectElemen.closest('.modal-edit-sewa');
            if (!modal) return;

            let boxKalkulator = modal.querySelector('.box-kalkulator');
            let inputDibayar  = modal.querySelector('.val-dibayar');

            if (selectElemen.value === 'lunas') {
                boxKalkulator.classList.add('d-none');
            } else {
                boxKalkulator.classList.remove('d-none');
                if (inputDibayar) hitungSisaTagihan(inputDibayar);
            }
        }

        // INITIALIZE SELECT2 PADA MODAL KASIR
        $(document).ready(function() {
            $('#modalKasirSewa').on('shown.bs.modal', function () {
                $('.select2-search').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalKasirSewa')
                });
            });
        });

        // FUNGSI SISTEM KASIR MODAL SEWA BARU
        let keranjang = [];

        function toggleKasirDibayar() {
            let st = $('#kasir_status_pembayaran').val();
            if (st === 'pending' || st === 'belum lunas') {
                $('#boxKasirDibayar').removeClass('d-none');
            } else {
                $('#boxKasirDibayar').addClass('d-none');
            }
        }

        function tambahKeKeranjang() {
            let selectBarang = $('#pilih_barang');
            let id = selectBarang.val();

            if (!id) {
                alert("Silakan pilih produk terlebih dahulu!");
                return;
            }

            let selectedOption = selectBarang.find(':selected');
            let nama = selectedOption.data('nama');
            let harga = parseFloat(selectedOption.data('harga')) || 0;
            let stok = parseInt(selectedOption.data('stok')) || 0;
            let jumlah = parseInt($('#pilih_jumlah').val()) || 1;

            if (jumlah > stok) {
                alert("Jumlah melebihi stok yang tersedia (" + stok + " unit)!");
                return;
            }

            let index = keranjang.findIndex(item => item.id === id);
            if (index !== -1) {
                if ((keranjang[index].jumlah + jumlah) > stok) {
                    alert("Total jumlah di keranjang melebihi stok tersedia!");
                    return;
                }
                keranjang[index].jumlah += jumlah;
            } else {
                keranjang.push({ id: id, nama: nama, harga: harga, jumlah: jumlah });
            }

            selectBarang.val('').trigger('change');
            $('#pilih_jumlah').val("1");

            renderKeranjang();
        }

        function hapusItemKeranjang(index) {
            keranjang.splice(index, 1);
            renderKeranjang();
        }

        function renderKeranjang() {
            let tbody = document.getElementById('bodyKeranjang');
            tbody.innerHTML = "";

            if (keranjang.length === 0) {
                tbody.innerHTML = `<tr id="barisKosong"><td colspan="5" class="text-center text-muted py-3 small">Belum ada produk dipilih. Silakan pilih produk di atas.</td></tr>`;
                hitungTotalKasir();
                return;
            }

            keranjang.forEach((item, index) => {
                let durasi = getDurasiHari();
                let subtotal = item.harga * item.jumlah * durasi;

                let row = `
                    <tr>
                        <td>
                            <strong>${item.nama}</strong>
                            <input type="hidden" name="barang_id[]" value="${item.id}">
                            <input type="hidden" name="barang_nama[]" value="${item.nama}">
                        </td>
                        <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
                        <td>
                            <input type="hidden" name="jumlah_item[]" value="${item.jumlah}">
                            <span class="badge bg-secondary">${item.jumlah} Unit</span>
                        </td>
                        <td class="fw-bold">Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="hapusItemKeranjang(${index})">&times;</button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            hitungTotalKasir();
        }

        function getDurasiHari() {
            let tgl1 = new Date(document.getElementById('tgl_sewa').value);
            let tgl2 = new Date(document.getElementById('tgl_kembali').value);
            
            let durasi = 1;
            if (tgl2 > tgl1) {
                let diff = Math.abs(tgl2 - tgl1);
                durasi = Math.ceil(diff / (1000 * 60 * 60 * 24));
            }
            document.getElementById('lblDurasi').innerText = durasi;
            return durasi;
        }

        function hitungTotalKasir() {
            let durasi = getDurasiHari();
            let grandTotal = 0;

            keranjang.forEach(item => {
                grandTotal += (item.harga * item.jumlah * durasi);
            });

            document.getElementById('lblGrandTotal').innerText = "Rp " + grandTotal.toLocaleString('id-ID');
            document.getElementById('inputGrandTotal').value = grandTotal;
        }
    </script>
</body>
</html>