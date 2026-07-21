<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config/koneksi.php');

$conn = isset($koneksi) ? $koneksi : die("Koneksi database gagal.");

// Inisialisasi variabel
$search = '';
$edit_id = null;
$edit_data = null;

// ==========================================
// LOGIKA PROSES PENGEMBALIAN
// ==========================================
if (isset($_POST['proses_kembali'])) {
    $penyewaan_id = (int)$_POST['penyewaan_id'];
    $maintenance_items = isset($_POST['maintenance_items']) ? $_POST['maintenance_items'] : [];
    
    mysqli_begin_transaction($conn);
    
    try {
        // Ambil semua item dalam penyewaan ini
        $query_detail = "SELECT dp.produk_id, dp.jumlah, p.nama_produk, p.stok 
                        FROM detail_penyewaan dp 
                        JOIN produk p ON dp.produk_id = p.id 
                        WHERE dp.penyewaan_id = $penyewaan_id";
        $result_detail = mysqli_query($conn, $query_detail);
        
        while($item = mysqli_fetch_assoc($result_detail)) {
            $produk_id = $item['produk_id'];
            $jumlah = $item['jumlah'];
            $nama_produk = $item['nama_produk'];
            
            if(in_array($produk_id, $maintenance_items)) {
                // Barang masuk maintenance - update status produk
                mysqli_query($conn, "UPDATE produk SET status = 'maintenance' WHERE id = $produk_id");
                
                // CATAT KE MAINTENANCE_LOG
                $keterangan = "Rusak saat pengembalian sewa - " . date('Y-m-d');
                mysqli_query($conn, "INSERT INTO maintenance_log (produk_id, tanggal_mulai, keterangan, biaya, status) 
                                    VALUES ($produk_id, NOW(), '$keterangan', 0, 'dalam_perbaikan')");
            } else {
                // Barang kembali normal - stok bertambah
                mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah, status = 'tersedia' WHERE id = $produk_id");
            }
        }
        
        // Update status penyewaan menjadi selesai
        mysqli_query($conn, "UPDATE penyewaan SET status_sewa = 'selesai' WHERE id = $penyewaan_id");
        
        mysqli_commit($conn);
        header("Location: pengembalian.php?success=1");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal memproses: " . $e->getMessage() . "');</script>";
    }
}

// AKSI EDIT PELANGGAN
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $query = "UPDATE users SET nama_lengkap='$nama', email='$email', no_telepon='$telepon', alamat='$alamat' WHERE id=$id AND role='customer'";
    
    
    if (mysqli_query($conn, $query)) {
        header("Location: pelanggan.php?success=edit");
        exit;
    } else {
        echo "<script>alert('Gagal mengedit pelanggan: " . mysqli_error($conn) . "');</script>";
    }
}

// AKSI HAPUS PELANGGAN
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='customer'")) {
        header("Location: pelanggan.php?success=hapus");
        exit;
    } else {
        echo "<script>alert('Gagal menghapus pelanggan');</script>";
    }
}

// ==========================================
// PENCARIAN
// ==========================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_clause = "WHERE role = 'customer'";
if ($search != '') {
    $where_clause .= " AND (nama_lengkap LIKE '%$search%' OR email LIKE '%$search%' OR no_telepon LIKE '%$search%')";
}

$query_tampil = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $query_tampil);

// Ambil data edit jika ada
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = mysqli_query($conn, "SELECT * FROM users WHERE id=$edit_id AND role='customer'");
    if ($edit_query && mysqli_num_rows($edit_query) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_query);
    }
}

// Hitung total pelanggan
$total_pelanggan = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='customer'");
$total_count = mysqli_fetch_assoc($total_pelanggan)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan - SIMRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --primary-color: #990000; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', system-ui, sans-serif; }
        .main-content { margin-left: 280px; padding: 2rem; }
        .btn-maroon { background-color: var(--primary-color); color: white; border: none; }
        .btn-maroon:hover { background-color: #770000; color: white; }
        .text-maroon { color: var(--primary-color) !important; }
        .bg-maroon { background-color: var(--primary-color) !important; }
        
        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #990000, #cc0000);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .stat-card {
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        
        @media (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>

    <?php include('include/sidebar.php'); ?>

    <div class="main-content">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fas fa-users me-2 text-maroon"></i>Kelola Pelanggan
            </h4>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold">Admin SIMRO</div>
                    <small class="text-muted">Administrator</small>
                </div>
                <div class="bg-maroon text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <!-- Alert Success -->
        <?php if(isset($_GET['success'])): 
            $msg = $_GET['success'] == 'tambah' ? 'Pelanggan baru berhasil ditambahkan!' : 
                   ($_GET['success'] == 'edit' ? 'Data pelanggan berhasil diperbarui!' : 'Pelanggan berhasil dihapus!');
        ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-maroon bg-opacity-10 text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Pelanggan</div>
                            <div class="fw-bold fs-4 mb-0"><?php echo $total_count; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Pelanggan Aktif</div>
                            <div class="fw-bold fs-4 mb-0"><?php echo $total_count; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-plus fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Bulan Ini</div>
                            <div class="fw-bold fs-4 mb-0">
                                <?php 
                                $bulan_ini = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='customer' AND MONTH(created_at) = MONTH(CURRENT_DATE())");
                                echo mysqli_fetch_assoc($bulan_ini)['total'];
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Tombol Tambah -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <form method="GET" action="" class="d-flex gap-2">
                            <div class="input-group flex-grow-1">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama, email, atau no. telepon..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <button type="submit" class="btn btn-maroon"><i class="fas fa-search"></i></button>
                            <?php if($search): ?>
                                <a href="pelanggan.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fas fa-user-plus me-2"></i>Tambah Pelanggan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pelanggan -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 30%;">PELANGGAN</th>
                                <th style="width: 20%;">EMAIL</th>
                                <th style="width: 15%;">NO. TELEPON</th>
                                <th style="width: 20%;">ALAMAT</th>
                                <th class="text-center" style="width: 10%;">TERDAFTAR</th>
                                <th class="text-center" style="width: 10%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    // Ambil inisial nama untuk avatar
                                    $nama_parts = explode(' ', $row['nama_lengkap']);
                                    $inisial = strtoupper(substr($nama_parts[0], 0, 1) . (isset($nama_parts[1]) ? substr($nama_parts[1], 0, 1) : ''));
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="customer-avatar">
                                                <?php echo $inisial; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                                <small class="text-muted">ID: #<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-envelope text-muted me-1"></i><?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-phone text-muted me-1"></i><?php echo htmlspecialchars($row['no_telepon'] ?? '-'); ?></div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($row['alamat'] ?? '-', 0, 40)) . (strlen($row['alamat'] ?? '') > 40 ? '...' : ''); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted"><?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <a href="pelanggan.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-light text-primary me-1 border" title="Edit">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <button class="btn btn-sm btn-light text-danger border" 
                                                onclick="confirmHapus(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-users-slash text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="text-muted mb-0">Tidak ada pelanggan ditemukan.</p>
                                        <?php if($search): ?>
                                            <small class="text-muted">Coba kata kunci pencarian lain</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PELANGGAN -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-maroon text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">NAMA LENGKAP <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">EMAIL <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">NO. TELEPON <span class="text-danger">*</span></label>
                            <input type="text" name="no_telepon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">ALAMAT</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-maroon px-4">
                            <i class="fas fa-save me-2"></i>Simpan Pelanggan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PELANGGAN -->
    <?php if($edit_data): ?>
    <div class="modal fade show" id="modalEdit" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-pen-to-square me-2"></i>Edit Data Pelanggan</h5>
                    <a href="pelanggan.php" class="btn-close"></a>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">NAMA LENGKAP <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($edit_data['nama_lengkap']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">EMAIL <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_data['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">NO. TELEPON <span class="text-danger">*</span></label>
                            <input type="text" name="no_telepon" class="form-control" value="<?php echo htmlspecialchars($edit_data['no_telepon']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">ALAMAT</label>
                            <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($edit_data['alamat']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <a href="pelanggan.php" class="btn btn-light">Batal</a>
                        <button type="submit" name="edit" class="btn btn-warning px-4 text-dark">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Hapus Tersembunyi -->
    <form id="formHapus" method="GET" action="pelanggan.php" style="display: none;">
        <input type="hidden" name="hapus" id="hapusId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmHapus(id, nama) {
            if (confirm('Yakin ingin menghapus pelanggan "' + nama + '"?\n\nTindakan ini tidak dapat dibatalkan.')) {
                document.getElementById('hapusId').value = id;
                document.getElementById('formHapus').submit();
            }
        }

        // Auto show modal edit jika ada ?edit= di URL
        <?php if($edit_data): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
                editModal.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>