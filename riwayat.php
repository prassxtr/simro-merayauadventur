<?php 
require_once 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penyewaan WHERE user_id = $user_id");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Query
$query = "SELECT * FROM penyewaan WHERE user_id = $user_id ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$riwayat_query = mysqli_query($koneksi, $query);
?>

<section class="py-5">
    <div class="container">
        <div class="mb-5">
            <h1 class="fw-bold mb-2" style="color: #8B0000;">Riwayat Penyewaan</h1>
            <p class="text-muted mb-0">Pantau semua aktivitas petualangan Anda.</p>
        </div>
        
        <?php if(mysqli_num_rows($riwayat_query) > 0): ?>
            
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
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-8">
                                <?php 
                                $items_count = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM detail_penyewaan WHERE penyewaan_id = {$row['id']}"));
                                ?>
                                <h5 class="fw-bold mb-3">Sewa (<?= $items_count ?> Item)</h5>
                                
                                <?php 
                                $items_query = mysqli_query($koneksi, "SELECT dp.*, p.nama_produk FROM detail_penyewaan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.penyewaan_id = {$row['id']}");
                                while($item = mysqli_fetch_assoc($items_query)):
                                ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="far fa-file-alt text-muted me-2"></i>
                                    <span class="text-muted small"><?= htmlspecialchars($item['nama_produk']) ?></span>
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
                            <div class="col-md-4 text-md-end">
                                <div class="mb-2">
                                    <span class="fw-bold" style="color: #8B0000; font-size: 1.1rem;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                                </div>
                                <div class="text-danger fw-semibold small mb-2">
                                    <i class="far fa-clock me-1"></i><?= $status_label ?>
                                </div>
                                <?php if($row['status_pembayaran'] == 'lunas'): ?>
                                <span class="badge bg-success">Lunas</span>
                                <?php else: ?>
                                <span class="badge bg-warning">Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Past Rentals -->
            <?php if(!empty($past_rentals)): ?>
            <div class="mb-4">
                <h6 class="text-muted fw-semibold mb-3">Sebelumnya</h6>
            </div>
            
            <?php foreach($past_rentals as $row): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <?php 
                            $items_count = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM detail_penyewaan WHERE penyewaan_id = {$row['id']}"));
                            ?>
                            <h5 class="fw-bold mb-3">Sewa (<?= $items_count ?> Item)</h5>
                            
                            <?php 
                            $items_query = mysqli_query($koneksi, "SELECT dp.*, p.nama_produk FROM detail_penyewaan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.penyewaan_id = {$row['id']}");
                            while($item = mysqli_fetch_assoc($items_query)):
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="far fa-file-alt text-muted me-2"></i>
                                <span class="text-muted small"><?= htmlspecialchars($item['nama_produk']) ?></span>
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
                        <div class="col-md-4 text-md-end">
                            <div class="mb-2">
                                <span class="fw-bold" style="color: #8B0000; font-size: 1.1rem;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success">
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
                        <a class="page-link" href="?page=<?= $i ?>" 
                           style="background: <?= $i == $page ? '#8B0000' : 'white' ?>; 
                                  color: <?= $i == $page ? 'white' : '#8B0000' ?>; 
                                  border-color: #8B0000;">
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
                <i class="fas fa-history text-muted" style="font-size: 5rem;"></i>
                <h4 class="mt-4 mb-3">Belum Ada Riwayat Penyewaan</h4>
                <p class="text-muted mb-4">Anda belum pernah melakukan penyewaan.</p>
                <a href="katalog.php" class="btn btn-danger btn-lg rounded-pill px-5">
                    <i class="fas fa-shopping-bag me-2"></i>Mulai Sewa
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.page-link {
    color: #8B0000;
    border-color: #8B0000;
}
.page-link:hover {
    background: #8B0000;
    color: white;
    border-color: #8B0000;
}
.page-item.active .page-link {
    background: #8B0000;
    border-color: #8B0000;
    color: white;
}
</style>

<?php include 'includes/footer.php'; ?>