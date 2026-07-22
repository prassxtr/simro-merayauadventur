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
// LOGIKA PROSES PENGEMBALIAN (SUDAH DIPERBAIKI TOTAL)
// ==========================================
if (isset($_POST['proses_kembali'])) {
    $penyewaan_id = (int)$_POST['penyewaan_id'];
    $kondisi_barang = $_POST['kondisi_barang'] ?? []; 
    $keterangan_rusak = $_POST['keterangan_rusak'] ?? []; 
    
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
            $kondisi = $kondisi_barang[$produk_id] ?? 'baik';
            
            if ($kondisi == 'baik') {
                // 1. Kembali normal: Stok bertambah
                mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah, status = 'tersedia' WHERE id = $produk_id");
                
            } elseif ($kondisi == 'cuci') {
                // 2. Cuci/Maintenance: Stok TIDAK bertambah, masuk log maintenance (dengan jumlah)
                mysqli_query($conn, "UPDATE produk SET status = 'maintenance' WHERE id = $produk_id");
                mysqli_query($conn, "INSERT INTO maintenance_log (produk_id, jumlah, tanggal_mulai, keterangan, biaya, status) 
                                    VALUES ($produk_id, $jumlah, NOW(), 'Cuci setelah penyewaan', 0, 'dalam_perbaikan')");
                
            } elseif ($kondisi == 'rusak' || $kondisi == 'hilang') {
                // 3. Rusak/Hilang: Stok TIDAK bertambah (permanen hilang), masuk log kerusakan
                $ket = mysqli_real_escape_string($conn, $keterangan_rusak[$produk_id] ?? 'Tidak ada keterangan');
                mysqli_query($conn, "INSERT INTO log_kerusakan (penyewaan_id, produk_id, jumlah, jenis, keterangan, tanggal) 
                                    VALUES ($penyewaan_id, $produk_id, $jumlah, '$kondisi', '$ket', NOW())");
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

// ==========================================
// PENCARIAN & FILTER
// ==========================================
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

// Set judul halaman untuk header
$page_title = "Kelola Pengembalian";
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
<!--     
    <style>
        :root { 
            --primary-color: #990000;
            --sidebar-width: 280px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', system-ui, sans-serif; 
        }

        /* LAYOUT BARU (SAMA SEPERTI KATALOG) */
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
        
        .content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .top-header {
            position: sticky;
            top: 0;
            background: white;
            border-bottom: 1px solid #dee2e6;
            z-index: 100;
            padding: 1rem 2rem;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
        }

        /* STYLE KHUSUS PENGEMBALIAN (TIDAK DIUBAH) */
        .btn-maroon { background-color: var(--primary-color); color: white; border: none; }
        .btn-maroon:hover { background-color: #770000; color: white; }
        .text-maroon { color: var(--primary-color) !important; }
        .bg-maroon { background-color: var(--primary-color) !important; }
        .badge-diproses { background-color: #fff3cd; color: #856404; }
        .badge-disewa { background-color: #cce5ff; color: #004085; }
        .badge-selesai { background-color: #d4edda; color: #155724; }
        .badge-lunas { background-color: #198754; color: white; }
        .badge-pending { background-color: #ffc107; color: #000; }
        
        .kondisi-select { font-size: 0.85rem; padding: 0.3rem; }
        .keterangan-box { display: none; margin-top: 0.5rem; }
        
        /* RESPONSIVE */
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
            .main-content {
                padding: 1rem;
            }
        }
    </style> -->
</head>
<body>

    <!-- 1. SIDEBAR -->
    <?php include('include/sidebar.php'); ?>

    <!-- 2. CONTENT WRAPPER -->
    <div class="content-wrapper">
        
        <!-- 3. HEADER (DIPANGGIL DARI FILE TERPISAH) -->
        <?php include('include/header.php'); ?>
        
        <!-- 4. MAIN CONTENT (ISI ASLI ANDA TIDAK DIUBAH) -->
        <div class="main-content">
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
                                                        onclick="openModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nomor_pesanan']); ?>', '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')">
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
        </div> <!-- End Main Content -->
    </div> <!-- End Content Wrapper -->

    <!-- MODAL UNIVERSAL PENGEMBALIAN (DILETAKKAN DI LUAR WRAPPER) -->
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
                        
                        <h6 class="fw-bold mb-3">Tentukan Kondisi Setiap Barang:</h6>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tabel_barang">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-center">Kondisi Pulang</th>
                                    </tr>
                                </thead>
                                <tbody id="list_barang">
                                    <!-- Data akan diisi oleh JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning mb-0 small">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Keterangan Logika:</strong>
                            <ul class="mb-0 mt-1">
                                <li><strong>Baik:</strong> Stok otomatis bertambah.</li>
                                <li><strong>Cuci:</strong> Stok belum bertambah, masuk antrean Maintenance.</li>
                                <li><strong>Rusak/Hilang:</strong> Stok <u>permanen tidak bertambah</u> (hilang dari inventaris) & wajib diisi keterangan.</li>
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
        function openModal(id, nomor, nama) {
            document.getElementById('modal_penyewaan_id').value = id;
            document.getElementById('modal_nomor_pesanan').textContent = nomor;
            document.getElementById('modal_nama_penyewa').textContent = nama;
            
            fetch('get_detail_penyewaan.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    
                    data.forEach(item => {
                        html += `
                            <tr>
                                <td>
                                    <div class="fw-bold">${item.nama_produk}</div>
                                    <small class="text-muted">Stok sistem: ${item.stok} unit</small>
                                </td>
                                <td class="text-center fw-bold align-middle">${item.jumlah}</td>
                                <td style="min-width: 250px;">
                                    <select name="kondisi_barang[${item.produk_id}]" class="form-select kondisi-select" onchange="toggleKeterangan(this, ${item.produk_id})">
                                        <option value="baik">Baik (Stok Bertambah)</option>
                                        <option value="cuci">Maintenance (Stok Ditahan)</option>
                                        <option value="rusak">Rusak (Stok Permanen Berkurang)</option>
                                        <option value="hilang">Hilang (Stok Permanen Berkurang)</option>
                                    </select>
                                    <textarea name="keterangan_rusak[${item.produk_id}]" id="ket_${item.produk_id}" class="form-control keterangan-box mt-2" rows="2" placeholder="Jelaskan detail kerusakan / kronologi kehilangan..."></textarea>
                                </td>
                            </tr>
                        `;
                    });
                    
                    document.getElementById('list_barang').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('modalPengembalian'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengambil data barang');
                });
        }

        // FUNGSI INI SUDAH DIPERBAIKI (Typo 'each' diganti menjadi '}')
        function toggleKeterangan(selectElement, produkId) {
            const ketBox = document.getElementById('ket_' + produkId);
            if (selectElement.value === 'rusak' || selectElement.value === 'hilang') {
                ketBox.style.display = 'block';
                ketBox.required = true; // Wajib diisi jika rusak/hilang
            } else {
                ketBox.style.display = 'none';
                ketBox.required = false;
                ketBox.value = ''; // Reset nilai
            }
        }
    </script>
</body>
</html>