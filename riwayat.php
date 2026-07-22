<?php 
// 1. WAJIB: Mulai session di baris paling atas
session_start(); 
require_once 'config/koneksi.php';
include 'includes/header.php';

// 2. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 3. Amankan variabel user_id
$user_id = (int)$_SESSION['user_id'];
$current_date = date('Y-m-d');

// 4. Pagination
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Pastikan minimal 1
$offset = ($page - 1) * $limit;

// 5. Hitung total data untuk pagination
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penyewaan WHERE user_id = $user_id");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// 6. Query utama ambil data penyewaan
$query = "SELECT * FROM penyewaan WHERE user_id = $user_id ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$riwayat_query = mysqli_query($koneksi, $query);

// Include header (jika Anda memisahkan header)
// include 'includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penyewaan - SIMRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #8B0000; }
        body { background-color: #f8f9fa; }
        .page-link { color: var(--primary-color); border-color: var(--primary-color); }
        .page-link:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .page-item.active .page-link { background: var(--primary-color); border-color: var(--primary-color); color: white; }
    </style>
</head>
<body>

<!-- (Opsional: Include header Anda di sini jika dipisah) -->
<!-- <?php // include 'includes/header.php'; ?> -->

<section class="py-5">
    <div class="container">
        <div class="mb-5">
            <h1 class="fw-bold mb-2" style="color: var(--primary-color);">Riwayat Penyewaan</h1>
            <p class="text-muted mb-0">Pantau semua aktivitas petualangan Anda.</p>
        </div>
        
        <?php if($riwayat_query && mysqli_num_rows($riwayat_query) > 0): ?>
            
            <?php 
            // Pisahkan active dan past rentals
            $active_rentals = [];
            $past_rentals = [];
            
            while($row = mysqli_fetch_assoc($riwayat_query)) {
                $days_remaining = 0;
                $status_label = '';
                
                $return_date = new DateTime($row['tanggal_kembali']);
                $today = new DateTime($current_date);
                $diff = $today->diff($return_date);
                $days_remaining = $diff->days;
                
                if ($row['status_sewa'] == 'diproses' || $row['status_sewa'] == 'disewa') {
                    if ($days_remaining <= 2 && $days_remaining >= 0) {
                        $status_label = "Sisa $days_remaining Hari";
                    } else {
                        $status_label = "Aktif";
                    }
                    $active_rentals[] = ['data' => $row, 'status' => $status_label];
                } else {
                    $past_rentals[] = $row;
                }
            }
            ?>
            
            <!-- Active Rentals -->
            <?php if(!empty($active_rentals)): ?>
                <?php foreach($active_rentals as $rental): 
                    $row = $rental['data'];
                    $status_label = $rental['status'];
                    
                    // Ambil jumlah item (Optimized)
                    $items_count_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM detail_penyewaan WHERE penyewaan_id = {$row['id']}");
                    $items_count = mysqli_fetch_assoc($items_count_query)['total'];
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="fw-bold mb-3">Sewa (<?= $items_count ?> Item)</h5>
                                
                                <?php 
                                $items_query = mysqli_query($koneksi, "SELECT dp.jumlah, p.nama_produk FROM detail_penyewaan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.penyewaan_id = {$row['id']}");
                                while($item = mysqli_fetch_assoc($items_query)):
                                ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-campground text-muted me-2" style="color: var(--primary-color) !important;"></i>
                                    <span class="text-muted small"><?= htmlspecialchars($item['nama_produk']) ?> (<?= $item['jumlah'] ?>x)</span>
                                </div>
                                <?php endwhile; ?>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">MULAI</small>
                                            <span class="fw-semibold">
                                                <i class="far fa-calendar me-1"></i>
                                                <?= date('d M Y', strtotime($row['tanggal_sewa'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">KEMBALI</small>
                                            <span class="fw-semibold">
                                                <i class="far fa-calendar me-1"></i>
                                                <?= date('d M Y', strtotime($row['tanggal_kembali'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <span class="fw-bold" style="color: var(--primary-color); font-size: 1.1rem;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                                </div>
                                <div class="text-danger fw-semibold small mb-2">
                                    <i class="far fa-clock me-1"></i><?= $status_label ?>
                                </div>
                                <?php if($row['status_pembayaran'] == 'lunas'): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-2">Lunas</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Past Rentals -->
            <?php if(!empty($past_rentals)): ?>
            <div class="mb-4 mt-5">
                <h6 class="text-muted fw-semibold mb-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">Riwayat Sebelumnya</h6>
            </div>
            
            <?php foreach($past_rentals as $row): 
                $items_count_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM detail_penyewaan WHERE penyewaan_id = {$row['id']}");
                $items_count = mysqli_fetch_assoc($items_count_query)['total'];
            ?>
            <div class="card border-0 shadow-sm mb-3 opacity-75">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-3">Sewa (<?= $items_count ?> Item)</h5>
                            
                            <?php 
                            $items_query = mysqli_query($koneksi, "SELECT dp.jumlah, p.nama_produk FROM detail_penyewaan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.penyewaan_id = {$row['id']}");
                            while($item = mysqli_fetch_assoc($items_query)):
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-campground text-muted me-2"></i>
                                <span class="text-muted small"><?= htmlspecialchars($item['nama_produk']) ?> (<?= $item['jumlah'] ?>x)</span>
                            </div>
                            <?php endwhile; ?>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">MULAI</small>
                                        <span class="fw-semibold">
                                            <i class="far fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($row['tanggal_sewa'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">KEMBALI</small>
                                        <span class="fw-semibold">
                                            <i class="far fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($row['tanggal_kembali'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end d-flex flex-column justify-content-center">
                            <div class="mb-2">
                                <span class="fw-bold" style="color: var(--primary-color); font-size: 1.1rem;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i>Selesai
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav class="d-flex justify-content-center mt-5">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link rounded-start" href="?page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link rounded-end" href="?page=<?= $page + 1 ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-history text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
                <h4 class="mt-4 mb-3 fw-bold">Belum Ada Riwayat Penyewaan</h4>
                <p class="text-muted mb-4">Anda belum pernah melakukan penyewaan. Yuk mulai petualangan Anda!</p>
                <a href="katalog.php" class="btn btn-danger btn-lg rounded-pill px-5 shadow-sm">
                    <i class="fas fa-shopping-bag me-2"></i>Mulai Sewa
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>