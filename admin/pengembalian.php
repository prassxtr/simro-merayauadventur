<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config/koneksi.php');

$conn = isset($koneksi) ? $koneksi : die("Koneksi database gagal.");
$search = '';
$filter_status = '';

// ==========================================
// LOGIKA PROSES PENGEMBALIAN
// ==========================================
if (isset($_POST['proses_kembali'])) {
    $penyewaan_id = (int)$_POST['penyewaan_id'];
    $maintenance_items = isset($_POST['maintenance_items']) ? $_POST['maintenance_items'] : [];
    
    mysqli_begin_transaction($conn);
    
    try {
        $query_detail = "SELECT dp.produk_id, dp.jumlah, p.nama_produk, p.stok 
                        FROM detail_penyewaan dp 
                        JOIN produk p ON dp.produk_id = p.id 
                        WHERE dp.penyewaan_id = $penyewaan_id";
        $result_detail = mysqli_query($conn, $query_detail);
        
        while($item = mysqli_fetch_assoc($result_detail)) {
            $produk_id = $item['produk_id'];
            $jumlah = $item['jumlah'];
            
            if(in_array($produk_id, $maintenance_items)) {
                mysqli_query($conn, "UPDATE produk SET status = 'maintenance' WHERE id = $produk_id");
            } else {
                mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah, status = 'tersedia' WHERE id = $produk_id");
            }
        }
        
        mysqli_query($conn, "UPDATE penyewaan SET status_sewa = 'selesai' WHERE id = $penyewaan_id");
        
        mysqli_commit($conn);
        header("Location: pengembalian.php?success=1");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal memproses: " . $e->getMessage() . "');</script>";
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$where_clause = "WHERE 1=1";
if ($filter_status == '') {
    $where_clause .= " AND p.status_sewa IN ('diproses', 'disewa')";
} else {
    $where_clause .= " AND p.status_sewa = '$filter_status'";
}

if ($search != '') {
    $where_clause .= " AND (p.nomor_pesanan LIKE '%$search%' OR u.nama_lengkap LIKE '%$search%')";
}

$query_tampil = "SELECT p.*, u.nama_lengkap, u.no_telepon 
                 FROM penyewaan p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 $where_clause 
                 ORDER BY p.tanggal_kembali ASC";

$result = mysqli_query($conn, $query_tampil);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengembalian - SIMRO</title>
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
        .badge-diproses { background-color: #fff3cd; color: #856404; }
        .badge-disewa { background-color: #cce5ff; color: #004085; }
        .badge-selesai { background-color: #d4edda; color: #155724; }
        .badge-lunas { background-color: #198754; color: white; }
        .badge-pending { background-color: #ffc107; color: #000; }
        
        @media (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>

    <?php include('include/sidebar.php'); ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fas fa-clipboard-check me-2 text-maroon"></i>Kelola Pengembalian
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

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Pengembalian berhasil diproses!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari No. Pesanan atau Nama Penyewa..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Status Aktif (Default)</option>
                            <option value="diproses" <?php if($filter_status == 'diproses') echo 'selected'; ?>>Diproses</option>
                            <option value="disewa" <?php if($filter_status == 'disewa') echo 'selected'; ?>>Sedang Disewa</option>
                            <option value="selesai" <?php if($filter_status == 'selesai') echo 'selected'; ?>>Selesai Dikembalikan</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">NO. PESANAN</th>
                                <th>NAMA PENYEWA</th>
                                <th>TGL SEWA</th>
                                <th>TGL KEMBALI</th>
                                <th class="text-center">TOTAL HARGA</th>
                                <th class="text-center">STATUS SEWA</th>
                                <th class="text-center">PEMBAYARAN</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    $pay_class = $row['status_pembayaran'] == 'lunas' ? 'badge-lunas' : 'badge-pending';
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-maroon"><?php echo htmlspecialchars($row['nomor_pesanan']); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap'] ?? '-'); ?></div>
                                        <small class="text-muted"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($row['no_telepon'] ?? '-'); ?></small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal_sewa'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal_kembali'])); ?></td>
                                    <td class="text-center fw-bold">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?php echo $row['status_sewa']; ?> rounded-pill px-3 py-2">
                                            <?php echo strtoupper($row['status_sewa']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $pay_class; ?> rounded-pill px-3 py-2">
                                            <?php echo strtoupper($row['status_pembayaran']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if(in_array($row['status_sewa'], ['diproses', 'disewa'])): ?>
                                            <button class="btn btn-sm btn-maroon" 
                                                    onclick="openModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nomor_pesanan']); ?>', '<?php echo htmlspecialchars($row['nama_lengkap']); ?>', <?php echo $row['total_harga']; ?>)">
                                                <i class="fas fa-undo me-1"></i> Proses Kembali
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="text-muted mb-0">Tidak ada data pengembalian.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL UNIVERSAL -->
    <div class="modal fade" id="modalPengembalian" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-maroon text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i>Proses Pengembalian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="penyewaan_id" id="modal_penyewaan_id" value="">
                    
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong id="modal_nomor_pesanan"></strong> - 
                            <span id="modal_nama_penyewa"></span>
                        </div>
                        
                        <h6 class="fw-bold mb-3">Daftar Barang yang Dikembalikan:</h6>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tabel_barang">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-center">Harga/Hari</th>
                                        <th class="text-center">
                                            <i class="fas fa-tools me-1"></i>Maintenance?
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="list_barang">
                                    <!-- Data akan diisi oleh JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Keterangan:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li>Barang yang <strong>TIDAK</strong> dicentang → stok otomatis bertambah.</li>
                                <li>Barang yang <strong>dicentang</strong> → masuk status maintenance.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="proses_kembali" class="btn btn-maroon px-4">
                            <i class="fas fa-check me-2"></i>Proses Pengembalian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(id, nomor, nama, total) {
            document.getElementById('modal_penyewaan_id').value = id;
            document.getElementById('modal_nomor_pesanan').textContent = nomor;
            document.getElementById('modal_nama_penyewa').textContent = nama;
            
            // Fetch data barang via AJAX
            fetch('get_detail_penyewaan.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    let totalItems = 0;
                    
                    data.forEach(item => {
                        totalItems++;
                        html += `
                            <tr>
                                <td>
                                    <div class="fw-bold">${item.nama_produk}</div>
                                    <small class="text-muted">Stok saat ini: ${item.stok} unit</small>
                                </td>
                                <td class="text-center fw-bold">${item.jumlah}</td>
                                <td class="text-center">Rp ${parseInt(item.harga_sewa).toLocaleString('id-ID')}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="maintenance_items[]" 
                                               value="${item.produk_id}" 
                                               id="maint_${item.produk_id}"
                                               style="transform: scale(1.3);">
                                    </div>
                                    <small class="text-danger d-block mt-1">
                                        <i class="fas fa-wrench me-1"></i>Centang jika rusak
                                    </small>
                                </td>
                            </tr>
                        `;
                    });
                    
                    document.getElementById('list_barang').innerHTML = html;
                    
                    // Update tombol submit
                    const submitBtn = document.querySelector('#modalPengembalian button[type="submit"]');
                    submitBtn.innerHTML = `<i class="fas fa-check me-2"></i>Proses Pengembalian (${totalItems} Barang)`;
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('modalPengembalian'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengambil data barang');
                });
        }
    </script>
</body>
</html>