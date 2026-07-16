<?php 
require_once 'config/koneksi.php';
include 'includes/header.php'; 

// Get ID produk
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query produk
$query = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.kategori_id = k.id WHERE p.id = $id");
$produk = mysqli_fetch_assoc($query);

if (!$produk) {
    header('Location: katalog.php');
    exit;
}

// Get produk terkait
$related_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE kategori_id = {$produk['kategori_id']} AND id != $id AND status = 'tersedia' LIMIT 3");
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>katalog.php" class="text-decoration-none">Katalog</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($produk['nama_produk']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Left: Image -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <img src="assets/img/produk/<?= htmlspecialchars($produk['gambar']) ?>" 
                        class="card-img-top" 
                        style="height: 500px; object-fit: cover;" 
                         alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                </div>
            </div>
            
            <!-- Right: Info -->
            <div class="col-lg-6">
                <span class="badge bg-danger mb-2"><?= htmlspecialchars($produk['nama_kategori']) ?></span>
                <h1 class="h2 fw-bold mb-3"><?= htmlspecialchars($produk['nama_produk']) ?></h1>
                <p class="text-muted mb-4"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Stok Tersedia</small>
                            <strong class="text-success fs-5"><?= $produk['stok'] ?> Unit</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Harga Sewa</small>
                            <strong class="text-danger fs-5">Rp <?= number_format($produk['harga_sewa'], 0, ',', '.') ?> <small class="text-muted">/hari</small></strong>
                        </div>
                    </div>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="tambah-keranjang.php" method="POST" class="border rounded-3 p-4 mb-4">
                        <input type="hidden" name="produk_id" value="<?= $produk['id'] ?>">
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Kembali</label>
                                <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Jumlah Unit</label>
                            <div class="input-group" style="max-width: 200px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="updateQty(-1)">-</button>
                                <input type="number" name="jumlah" id="jumlah" class="form-control text-center" value="1" min="1" max="<?= $produk['stok'] ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="updateQty(1)">+</button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                            <span class="fw-bold">Total Perkiraan:</span>
                            <span class="text-danger fw-bold fs-4" id="total_harga">Rp <?= number_format($produk['harga_sewa'], 0, ',', '.') ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold">
                            <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Silakan <a href="login.php" class="fw-bold">login</a> untuk menyewa alat ini.
                    </div>
                <?php endif; ?>
                
                <div class="border-top pt-4">
                    <h6 class="fw-bold mb-3">Detail Produk:</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Kondisi: Baik & Terawat</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Perlengkapan Lengkap</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Siap Pakai</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if(mysqli_num_rows($related_query) > 0): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Produk Lainnya</h3>
        <div class="row g-4">
            <?php while($related = mysqli_fetch_assoc($related_query)): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <img src="assets/img/produk/<?= htmlspecialchars($related['gambar']) ?>" 
                        class="card-img-top" 
                        style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2"><?= htmlspecialchars($related['nama_produk']) ?></h6>
                        <p class="text-danger fw-bold mb-3">Rp <?= number_format($related['harga_sewa'], 0, ',', '.') ?> <small class="text-muted">/hari</small></p>
                        <a href="detail-produk.php?id=<?= $related['id'] ?>" class="btn btn-outline-danger w-100 rounded-pill">Lihat Detail</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function updateQty(change) {
    const input = document.getElementById('jumlah');
    const max = <?= $produk['stok'] ?>;
    let newVal = parseInt(input.value) + change;
    if (newVal >= 1 && newVal <= max) {
        input.value = newVal;
        calculateTotal();
    }
}

function calculateTotal() {
    const harga = <?= $produk['harga_sewa'] ?>;
    const tglMulai = new Date(document.getElementById('tanggal_mulai').value);
    const tglKembali = new Date(document.getElementById('tanggal_kembali').value);
    const jumlah = parseInt(document.getElementById('jumlah').value);
    
    if (tglMulai && tglKembali && tglKembali >= tglMulai) {
        const diffTime = Math.abs(tglKembali - tglMulai);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        const total = harga * diffDays * jumlah;
        document.getElementById('total_harga').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
}

document.getElementById('tanggal_mulai').addEventListener('change', calculateTotal);
document.getElementById('tanggal_kembali').addEventListener('change', calculateTotal);
</script>

<?php include 'includes/footer.php'; ?>