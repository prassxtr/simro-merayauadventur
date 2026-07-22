<?php

require_once 'include/auth_check.php';
require_once '../config/koneksi.php';

// ... kode lainnya tetap sama ...
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config/koneksi.php');

$conn = null;
$result = null;
$search = '';
$filter = '';
$kategori_options = [];

if (isset($koneksi)) {
    $conn = $koneksi;
} else {
    die("Koneksi database gagal.");
}

// ==========================================
// LOGIKA PHP (CRUD)
// ==========================================
if (isset($_POST['simpan'])) {
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori_id = (int)$_POST['kategori_id'];
    $stok = (int)$_POST['stok'];
    $harga_sewa = (int)$_POST['harga_sewa'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $gambar_db = 'default.png';

    if(!empty($_FILES['gambar']['name'])) {
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;
            if(move_uploaded_file($_FILES['gambar']['tmp_name'], "../assets/img/katalog/" . $nama_gambar_baru)) {
                $gambar_db = $nama_gambar_baru;
            }
        }
    }

    $query = "INSERT INTO produk (nama_produk, kategori_id, deskripsi, harga_sewa, stok, gambar, status) VALUES ('$nama_produk', '$kategori_id', '$deskripsi', '$harga_sewa', '$stok', '$gambar_db', 'tersedia')";
    if(mysqli_query($conn, $query)) { header("Location: index.php"); exit; } 
    else { echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>"; }
}

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori_id = (int)$_POST['kategori_id'];
    $stok = (int)$_POST['stok'];
    $harga_sewa = (int)$_POST['harga_sewa'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE produk SET nama_produk='$nama_produk', kategori_id='$kategori_id', stok='$stok', harga_sewa='$harga_sewa', deskripsi='$deskripsi', status='$status' WHERE id='$id'";

    if(!empty($_FILES['gambar']['name'])) {
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if(in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;
            if(move_uploaded_file($_FILES['gambar']['tmp_name'], "../assets/img/katalog/" . $nama_gambar_baru)) {
                $query = "UPDATE produk SET nama_produk='$nama_produk', kategori_id='$kategori_id', stok='$stok', harga_sewa='$harga_sewa', deskripsi='$deskripsi', status='$status', gambar='$nama_gambar_baru' WHERE id='$id'";
            }
        }
    }
    if(mysqli_query($conn, $query)) { header("Location: index.php"); exit; }
    else { echo "<script>alert('Gagal update: " . mysqli_error($conn) . "');</script>"; }
}

if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM produk WHERE id = " . (int)$_GET['hapus']);
    header("Location: index.php"); exit;
}

// ==========================================
// PENCARIAN & QUERY
// ==========================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : '';

$where_clause = "WHERE 1=1";
if ($search != '') $where_clause .= " AND (p.nama_produk LIKE '%$search%' OR p.id LIKE '%$search%')";
if ($filter != '') $where_clause .= " AND p.kategori_id = '$filter'";

$query_tampil = "SELECT p.id, p.nama_produk, p.kategori_id, k.nama_kategori, p.deskripsi, p.harga_sewa, p.stok AS total_stok, p.gambar, p.status,
                 COALESCE(SUM(CASE WHEN peny.status_sewa IN ('diproses', 'disewa') THEN dp.jumlah ELSE 0 END), 0) AS rented_count
                 FROM produk p 
                 LEFT JOIN kategori k ON p.kategori_id = k.id 
                 LEFT JOIN detail_penyewaan dp ON p.id = dp.produk_id
                 LEFT JOIN penyewaan peny ON dp.penyewaan_id = peny.id
                 $where_clause GROUP BY p.id ORDER BY p.id DESC";

$result = mysqli_query($conn, $query_tampil);

$list_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
while($k = mysqli_fetch_assoc($list_kategori)) { $kategori_options[] = $k; }

$page_title = "Katalog & Stok Alat";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SIMRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- <style>
        :root { 
            --primary-color: #990000;
            --sidebar-width: 280px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        
        /* SIDEBAR: Fixed di kiri, full height */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: white;
            border-right: 1px solid #dee2e6;
            z-index: 1000;
            overflow-y: auto;
        }
        
        /* CONTENT WRAPPER: Di sebelah kanan sidebar */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* HEADER: Sticky di atas area konten */
        .top-header {
            position: sticky;
            top: 0;
            background: white;
            border-bottom: 1px solid #dee2e6;
            z-index: 100;
            padding: 1rem 2rem;
        }
        
        /* MAIN CONTENT: Area scroll */
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        /* Warna Brand */
        .text-maroon { color: var(--primary-color) !important; }
        .bg-maroon { background-color: var(--primary-color) !important; }
        .btn-maroon { background-color: var(--primary-color); color: white; border: none; font-weight: 600; }
        .btn-maroon:hover { background-color: #770000; color: white; }
        
        /* Komponen */
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #e9ecef; }
        
        /* Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content-wrapper {
                margin-left: 0;
            }
        }
    </style> -->
</head>
<body>

    <!-- 1. SIDEBAR (Fixed di kiri) -->
    <?php include('include/sidebar.php'); ?>
    
    <!-- 2. CONTENT WRAPPER (Di sebelah kanan sidebar) -->
    <div class="content-wrapper">
        
        <!-- 3. HEADER (Sticky di atas konten) -->
        <?php include('include/header.php'); ?>
        
        <!-- 4. MAIN CONTENT -->
        <div class="main-content">
            
            <!-- Form Pencarian & Filter -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama atau kode alat..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="kategori_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <?php foreach($kategori_options as $kat): ?>
                                    <option value="<?php echo $kat['id']; ?>" <?php if($filter == $kat['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button type="button" class="btn btn-maroon w-100" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="fas fa-plus me-2"></i>Tambah Alat Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 25%;">PRODUK & DETAIL</th>
                                    <th style="width: 12%;">HARGA /HARI</th>
                                    <th style="width: 15%;">DESKRIPSI</th>
                                    <th class="text-center" style="width: 8%;">TOTAL</th>
                                    <th class="text-center" style="width: 10%;">READY</th>
                                    <th class="text-center" style="width: 10%;">RENTED</th>
                                    <th class="text-center" style="width: 10%;">MAINT</th>
                                    <th class="text-center" style="width: 10%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result && mysqli_num_rows($result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): 
                                        $total = $row['total_stok'];
                                        $rented = $row['rented_count'];
                                        $maintenance = ($row['status'] == 'maintenance') ? $total : 0;
                                        $ready = max(0, $total - $rented - $maintenance);
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="../assets/img/katalog/<?php echo htmlspecialchars($row['gambar']); ?>" class="product-img" onerror="this.src='https://placehold.co/50x50/e9ecef/990000?text=No+Img'">
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?></span>
                                                    <div class="small text-muted mt-1">ID: <?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><div class="fw-bold text-maroon">Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></div><small class="text-muted">/hari</small></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 40)) . (strlen($row['deskripsi']) > 40 ? '...' : ''); ?></small></td>
                                        <td class="text-center fw-bold"><?php echo $total; ?></td>
                                        <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success-emphasis rounded-pill px-3"><?php echo $ready; ?></span></td>
                                        <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3"><?php echo $rented; ?></span></td>
                                        <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger-emphasis rounded-pill px-3"><?php echo $maintenance; ?></span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light text-primary me-1 border" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id']; ?>"><i class="fas fa-pen-to-square"></i></button>
                                            <button class="btn btn-sm btn-light text-danger border" data-bs-toggle="modal" data-bs-target="#modalHapus<?php echo $row['id']; ?>"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center py-5"><i class="fas fa-box-open text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i><p class="text-muted mb-0">Tidak ada data alat ditemukan.</p></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL (Diletakkan di luar wrapper)         -->
    <!-- ========================================== -->

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-maroon"><i class="fas fa-plus me-2"></i>Tambah Alat Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label fw-semibold small text-muted">NAMA ALAT</label><input type="text" name="nama_produk" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">KATEGORI</label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach($kategori_options as $kat): ?><option value="<?php echo $kat['id']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label fw-semibold small text-muted">STOK</label><input type="number" name="stok" class="form-control" required></div>
                            <div class="col-6 mb-3"><label class="form-label fw-semibold small text-muted">HARGA / HARI</label><input type="number" name="harga_sewa" class="form-control" required></div>
                        </div>
                        <div class="mb-3"><label class="form-label fw-semibold small text-muted">DESKRIPSI</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
                        <div class="mb-3"><label class="form-label fw-semibold small text-muted">GAMBAR</label><input type="file" name="gambar" class="form-control" required></div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpan" class="btn btn-maroon px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php 
    if ($result) {
        mysqli_data_seek($result, 0);
        while($row = mysqli_fetch_assoc($result)): 
    ?>
        <!-- MODAL EDIT -->
        <div class="modal fade" id="modalEdit<?php echo $row['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-maroon"><i class="fas fa-pen-to-square me-2"></i>Edit Alat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label fw-semibold small text-muted">NAMA ALAT</label><input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($row['nama_produk']); ?>" required></div>
                            <div class="row">
                                <div class="col-6 mb-3"><label class="form-label fw-semibold small text-muted">TOTAL STOK</label><input type="number" name="stok" class="form-control" value="<?php echo $row['total_stok']; ?>" required></div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold small text-muted">STATUS</label>
                                    <select name="status" class="form-select">
                                        <option value="tersedia" <?php echo $row['status']=='tersedia'?'selected':''; ?>>Tersedia</option>
                                        <!-- <option value="maintenance" <?php echo $row['status']=='maintenance'?'selected':''; ?>>Maintenance</option> -->
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3"><label class="form-label fw-semibold small text-muted">GANTI GAMBAR (Opsional)</label><input type="file" name="gambar" class="form-control"></div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="update" class="btn btn-maroon px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL HAPUS -->
        <div class="modal fade" id="modalHapus<?php echo $row['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow text-center p-4">
                    <i class="fas fa-circle-exclamation text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mb-2">Hapus Alat?</h5>
                    <p class="text-muted small mb-4">Data <strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong> akan dihapus permanen.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Batal</button>
                        <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger flex-grow-1" onclick="return confirm('Yakin ingin menghapus data ini?')">Ya, Hapus</a>
                    </div>
                </div>
            </div>
        </div>
    <?php 
        endwhile; 
    } 
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>