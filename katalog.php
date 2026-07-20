<?php 
require_once 'config/koneksi.php';
include 'includes/header.php'; 

// Get kategori dari database
$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");

// Filter kategori
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Query produk
$query = "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.kategori_id = k.id WHERE p.status = 'tersedia'";

if($kategori_id > 0) {
    $query .= " AND p.kategori_id = '$kategori_id'";
}

if($search) {
    $query .= " AND (p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%')";
}

$produk_query = mysqli_query($koneksi, $query);
?>

<!-- Header Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #8B0000 0%, #6d0000 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white">
                <span class="badge bg-white text-danger mb-3 px-3 py-2">Penyewaan Alat Camping Pontianak</span>
                <h1 class="display-5 fw-bold mb-3">Jelajahi Alam Bebas<br>Dengan Alat Berkualitas</h1>
                <p class="lead mb-0">Modern, higienis, dan terawat. Pilih perlengkapan petualangan Anda secara online, tentukan opsi jaminan, bayar DP, dan ambil di basecamp kami!</p>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="fas fa-campground" style="font-size: 150px; opacity: 0.2; color: white;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Section -->
<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <h5 class="fw-bold mb-1">Pilih Alat Camping</h5>
            <p class="text-muted mb-4">Stok sinkron otomatis dengan sistem pusat (SIMRO)</p>
        </div>
        
        <div class="row g-4 mb-5">
            <?php 
            // Reset pointer kategori untuk ditampilkan
            mysqli_data_seek($kategori_query, 0);
            while($kat = mysqli_fetch_assoc($kategori_query)): 
                // Icon mapping untuk setiap kategori
                $icons = [
                    '1' => 'fa-shopping-bag',      // Carrier/Tas
                    '2' => 'fa-campground',        // Camping Gear
                    '3' => 'fa-home',              // Tenda/Dome
                    '4' => 'fa-hiking',            // Trekking Gear
                    '5' => 'fa-utensils',          // Cooking Set
                    '6' => 'fa-lightbulb'          // Lampu & ACC
                ];
                $icon = $icons[$kat['id']] ?? 'fa-box';
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="?kategori=<?= $kat['id'] ?>" class="text-decoration-none text-center d-block <?= $kategori_id == $kat['id'] ? 'active' : '' ?>">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 <?= $kategori_id == $kat['id'] ? 'bg-danger' : '' ?>" 
                         style="width: 70px; height: 70px; background: #c9a9a9; transition: all 0.3s;">
                        <i class="fas <?= $icon ?> text-white" style="font-size: 1.8rem;"></i>
                    </div>
                    <small class="text-dark fw-semibold"><?= htmlspecialchars($kat['nama_kategori']) ?></small>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <?php if($kategori_id > 0): ?>
                        <input type="hidden" name="kategori" value="<?= $kategori_id ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control" placeholder="Cari alat camping..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if($search): ?>
                        <a href="katalog.php<?= $kategori_id > 0 ? '?kategori='.$kategori_id : '' ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Produk Grid -->
        <div class="row g-4">
            <?php if(mysqli_num_rows($produk_query) > 0): ?>
                <?php while($produk = mysqli_fetch_assoc($produk_query)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; overflow: hidden;">
                        <div style="height: 250px; overflow: hidden; background: #f8f9fa;">
                            <img src="assets/img/katalog/<?= htmlspecialchars($produk['gambar']) ?>" 
                                class="card-img-top h-100" 
                                style="object-fit: cover;" 
                                alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                        </div>
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-2"><?= htmlspecialchars($produk['nama_produk']) ?></h6>
                            <p class="text-muted small mb-3" style="height: 60px; overflow: hidden;">
                                <?= htmlspecialchars($produk['deskripsi']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-danger fw-bold" style="font-size: 1.1rem;">
                                    Rp <?= number_format($produk['harga_sewa'], 0, ',', '.') ?>
                                </span>
                                <span class="text-muted small">/ day</span>
                            </div>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button onclick="addToCart(<?= $produk['id'] ?>)" 
                                        class="btn btn-outline-danger w-100 rounded-pill">
                                    <i class="fas fa-plus me-2"></i>Tambah ke Sewa
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-danger w-100 rounded-pill">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login untuk Sewa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">Tidak ada produk yang tersedia</h5>
                    <p class="text-muted">Coba ubah filter pencarian Anda</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function addToCart(produkId) {
    // Redirect ke detail produk untuk pilih tanggal
    window.location.href = 'detail-produk.php?id=' + produkId;
}

// Active state untuk kategori
document.querySelectorAll('.kategori-filter a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.kategori-filter a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<style>
/* Custom styles untuk katalog */
.rounded-circle {
    transition: all 0.3s ease;
}

.rounded-circle:hover,
.rounded-circle.bg-danger {
    background: #8B0000 !important;
    transform: translateY(-5px);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.btn-outline-danger {
    border-color: #8B0000;
    color: #8B0000;
}

.btn-outline-danger:hover {
    background: #8B0000;
    border-color: #8B0000;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>