<?php

require_once 'include/auth_check.php';
require_once '../config/koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config/koneksi.php');

$conn = isset($koneksi) ? $koneksi : die("Koneksi database gagal.");

$search = '';
$filter_status = '';

// ==========================================
// LOGIKA CRUD MAINTENANCE
// ==========================================

// TAMBAH MAINTENANCE MANUAL
if (isset($_POST['tambah_maintenance'])) {
    $produk_id = (int)$_POST['produk_id'];
    $jumlah = (int)$_POST['jumlah'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $biaya = (float)$_POST['biaya'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    
    mysqli_begin_transaction($conn);
    try {
        $query = "INSERT INTO maintenance_log (produk_id, jumlah, tanggal_mulai, keterangan, biaya, status) 
                  VALUES ($produk_id, $jumlah, '$tanggal_mulai', '$keterangan', $biaya, 'dalam_perbaikan')";
        mysqli_query($conn, $query);
        
        mysqli_query($conn, "UPDATE produk SET status = 'maintenance' WHERE id = $produk_id");
        
        mysqli_commit($conn);
        header("Location: maintenance.php?success=tambah");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal: " . $e->getMessage() . "');</script>";
    }
}

// SELESAIKAN MAINTENANCE
if (isset($_POST['selesai_maintenance'])) {
    $log_id = (int)$_POST['log_id'];
    $produk_id = (int)$_POST['produk_id'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    
    mysqli_begin_transaction($conn);
    try {
        $log_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jumlah FROM maintenance_log WHERE id = $log_id"));
        $jumlah = $log_data['jumlah'] ?? 1;
        
        mysqli_query($conn, "UPDATE maintenance_log SET tanggal_selesai = '$tanggal_selesai', status = 'selesai' WHERE id = $log_id");
        mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah, status = 'tersedia' WHERE id = $produk_id");
        
        mysqli_commit($conn);
        header("Location: maintenance.php?success=selesai");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal: " . $e->getMessage() . "');</script>";
    }
}

// HAPUS LOG MAINTENANCE
if (isset($_GET['hapus'])) {
    $log_id = (int)$_GET['hapus'];
    $log = mysqli_fetch_assoc(mysqli_query($conn, "SELECT produk_id, status, jumlah FROM maintenance_log WHERE id = $log_id"));
    
    if ($log) {
        if ($log['status'] == 'dalam_perbaikan') {
            $jumlah = $log['jumlah'] ?? 1;
            mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah, status = 'tersedia' WHERE id = {$log['produk_id']}");
        }
        mysqli_query($conn, "DELETE FROM maintenance_log WHERE id = $log_id");
    }
    header("Location: maintenance.php?success=hapus");
    exit;
}

// ==========================================
// PENCARIAN & FILTER
// ==========================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$where_clause = "WHERE ml.status != 'selesai'";
if ($filter_status != '') {
    $where_clause = "WHERE ml.status = '$filter_status'";
}

if ($search != '') {
    $where_clause .= " AND (p.nama_produk LIKE '%$search%' OR ml.keterangan LIKE '%$search%')";
}

$query_tampil = "SELECT ml.*, p.nama_produk, p.harga_sewa, p.stok, p.gambar, k.nama_kategori
                 FROM maintenance_log ml
                 JOIN produk p ON ml.produk_id = p.id
                 LEFT JOIN kategori k ON p.kategori_id = k.id
                 $where_clause
                 ORDER BY ml.tanggal_mulai DESC";

$result = mysqli_query($conn, $query_tampil);

// SIMPAN DATA MAINTENANCE AKTIF KE ARRAY UNTUK MODAL SELESAI
$data_maintenance_aktif = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_maintenance_aktif[] = $row;
    }
}

$query_riwayat = "SELECT ml.*, p.nama_produk 
                  FROM maintenance_log ml
                  JOIN produk p ON ml.produk_id = p.id
                  WHERE ml.status = 'selesai'
                  ORDER BY ml.tanggal_selesai DESC
                  LIMIT 10";
$result_riwayat = mysqli_query($conn, $query_riwayat);

$list_produk = mysqli_query($conn, "SELECT id, nama_produk FROM produk ORDER BY nama_produk ASC");

$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_aktif,
        SUM(CASE WHEN status = 'dalam_perbaikan' THEN 1 ELSE 0 END) as dalam_perbaikan,
        SUM(CASE WHEN status = 'selesai' AND MONTH(tanggal_selesai) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) as selesai_bulan_ini,
        COALESCE(SUM(CASE WHEN status = 'dalam_perbaikan' THEN biaya ELSE 0 END), 0) as total_biaya
    FROM maintenance_log
"));

// Set judul halaman untuk header
$page_title = "Kelola Maintenance";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?> - SIMRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- 1. SIDEBAR -->
    <?php include('include/sidebar.php'); ?>

    <!-- 2. CONTENT WRAPPER -->
    <div class="content-wrapper">
        
        <!-- 3. HEADER -->
        <?php include('include/header.php'); ?>
        
        <!-- 4. MAIN CONTENT -->
        <div class="main-content">
            
            <!-- Alert Success -->
            <?php if(isset($_GET['success'])): 
                $msg = $_GET['success'] == 'tambah' ? 'Maintenance baru berhasil ditambahkan!' : 
                       ($_GET['success'] == 'selesai' ? 'Maintenance berhasil diselesaikan! Stok dikembalikan.' : 'Log maintenance dihapus.');
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-tools fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Dalam Perbaikan</div>
                                <div class="fw-bold fs-4 mb-0"><?php echo $stats['dalam_perbaikan']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745 !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Selesai Bulan Ini</div>
                                <div class="fw-bold fs-4 mb-0"><?php echo $stats['selesai_bulan_ini']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Biaya</div>
                                <div class="fw-bold fs-4 mb-0 text-danger">Rp <?php echo number_format($stats['total_biaya'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="border-left: 4px solid #17a2b8 !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-clipboard-list fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Riwayat</div>
                                <div class="fw-bold fs-4 mb-0">
                                    <?php 
                                    $total_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM maintenance_log"));
                                    echo $total_all['total'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Tombol Tambah -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="" class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama alat atau keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Tampilkan: Dalam Perbaikan (Aktif)</option>
                                <option value="dalam_perbaikan" <?php if($filter_status == 'dalam_perbaikan') echo 'selected'; ?>>Dalam Perbaikan</option>
                                <option value="selesai" <?php if($filter_status == 'selesai') echo 'selected'; ?>>Selesai</option>
                                <option value="dibatalkan" <?php if($filter_status == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                                <option value="all" <?php if($filter_status == 'all') echo 'selected'; ?>>Semua Status</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button type="button" class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="fas fa-plus me-2"></i>Tambah Maintenance Manual
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Maintenance Aktif -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-wrench me-2 text-warning"></i>Maintenance Aktif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">ALAT</th>
                                    <th class="text-center">QTY</th>
                                    <th>TGL MULAI</th>
                                    <th>DURASI</th>
                                    <th>KETERANGAN</th>
                                    <th class="text-center">BIAYA</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($data_maintenance_aktif)): ?>
                                    <?php foreach($data_maintenance_aktif as $row): 
                                        $durasi = floor((strtotime(date('Y-m-d')) - strtotime($row['tanggal_mulai'])) / 86400);
                                        $durasi_text = $durasi > 0 ? "$durasi hari" : "Hari ini";
                                        $badge_class = 'badge-' . str_replace('_', '', $row['status']);
                                        $jumlah_unit = $row['jumlah'] ?? 1;
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="../assets/img/katalog/<?php echo htmlspecialchars($row['gambar']); ?>" 
                                                     class="product-thumb" 
                                                     onerror="this.src='https://placehold.co/50x50/e9ecef/990000?text=No+Img'">
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['nama_kategori'] ?? '-'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning bg-opacity-20 text-dark fw-bold border border-warning px-2 py-1">
                                                <?php echo $jumlah_unit; ?> unit
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">
                                                <?php echo $durasi_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($row['keterangan'], 0, 50)) . (strlen($row['keterangan']) > 50 ? '...' : ''); ?></small>
                                        </td>
                                        <td class="text-center fw-bold text-danger">
                                            Rp <?php echo number_format($row['biaya'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 py-2">
                                                <?php echo strtoupper(str_replace('_', ' ', $row['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if($row['status'] == 'dalam_perbaikan'): ?>
                                                <button class="btn btn-sm btn-success me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalSelesai<?php echo $row['id']; ?>"
                                                        title="Selesaikan">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <a href="maintenance.php?hapus=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-light text-danger border"
                                                   onclick="return confirm('Yakin ingin menghapus log ini?')"
                                                   title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="text-muted mb-0">Tidak ada maintenance aktif. Semua alat dalam kondisi baik!</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Riwayat Maintenance -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-history me-2 text-info"></i>Riwayat Maintenance (10 Terakhir)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">ALAT</th>
                                    <th class="text-center">QTY</th>
                                    <th>TGL MULAI</th>
                                    <th>TGL SELESAI</th>
                                    <th>DURASI</th>
                                    <th class="text-center">BIAYA</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result_riwayat && mysqli_num_rows($result_riwayat) > 0): ?>
                                    <?php while($rw = mysqli_fetch_assoc($result_riwayat)): 
                                        $durasi = floor((strtotime($rw['tanggal_selesai']) - strtotime($rw['tanggal_mulai'])) / 86400);
                                        $jumlah_unit_rw = $rw['jumlah'] ?? 1;
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($rw['nama_produk']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <?php echo $jumlah_unit_rw; ?> unit
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($rw['tanggal_mulai'])); ?></td>
                                        <td><?php echo date('d M Y', strtotime($rw['tanggal_selesai'])); ?></td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill">
                                                <?php echo $durasi; ?> hari
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">Rp <?php echo number_format($rw['biaya'], 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-selesai rounded-pill px-3 py-2">SELESAI</span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat maintenance.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- End Main Content -->
    </div> <!-- End Content Wrapper -->


    <!-- ======================================================= -->
    <!-- MODAL-MODAL DILETAKKAN DI LUAR WRAPPER UTAMA             -->
    <!-- ======================================================= -->

    <!-- 1. MODAL SELESAIKAN MAINTENANCE -->
    <?php if(!empty($data_maintenance_aktif)): ?>
        <?php foreach($data_maintenance_aktif as $row): ?>
            <?php if($row['status'] == 'dalam_perbaikan'): 
                $jumlah_unit = $row['jumlah'] ?? 1;
            ?>
                <div class="modal fade" id="modalSelesai<?php echo $row['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-bottom bg-success text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Selesaikan Maintenance</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="log_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="produk_id" value="<?php echo $row['produk_id']; ?>">
                                <div class="modal-body">
                                    <p class="mb-2">Selesaikan maintenance untuk:</p>
                                    <div class="bg-light p-3 rounded mb-3">
                                        <div class="fw-bold text-maroon"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                                        <div class="text-dark small mt-1">
                                            Jumlah: <span class="badge bg-warning text-dark border fw-bold"><?php echo $jumlah_unit; ?> unit</span>
                                        </div>
                                        <small class="text-muted d-block mt-1">Mulai: <?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-muted">TANGGAL SELESAI</label>
                                        <input type="date" name="tanggal_selesai" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <p class="small text-success mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Setelah diselesaikan, status alat akan kembali ke <strong>"Tersedia"</strong> dan stok bertambah sebanyak <strong><?php echo $jumlah_unit; ?> unit</strong>.
                                    </p>
                                </div>
                                <div class="modal-footer border-top">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="selesai_maintenance" class="btn btn-success px-4">
                                        <i class="fas fa-check me-2"></i>Selesai
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 2. MODAL TAMBAH MAINTENANCE MANUAL -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-maroon text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Maintenance Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gunakan fitur ini untuk mencatat maintenance di luar proses pengembalian. Status alat akan otomatis berubah menjadi <strong>"Maintenance"</strong>.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">PILIH ALAT <span class="text-danger">*</span></label>
                            <select name="produk_id" class="form-select" required>
                                <option value="">-- Pilih Alat --</option>
                                <?php if($list_produk && mysqli_num_rows($list_produk) > 0): ?>
                                    <?php mysqli_data_seek($list_produk, 0); ?>
                                    <?php while($p = mysqli_fetch_assoc($list_produk)): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_produk']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">JUMLAH BARANG <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" value="1" min="1" required>
                            <small class="text-muted">Berapa unit yang masuk maintenance?</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">TANGGAL MULAI <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">KETERANGAN / KERUSAKAN <span class="text-danger">*</span></label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Jelaskan kerusakan atau alasan maintenance..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">ESTIMASI BIAYA (Rp)</label>
                            <input type="number" name="biaya" class="form-control" value="0" min="0">
                            <small class="text-muted">Kosongkan atau isi 0 jika belum ada biaya</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_maintenance" class="btn btn-maroon px-4">
                            <i class="fas fa-save me-2"></i>Simpan Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>