<?php 
require_once 'config/koneksi.php';
include 'includes/header.php'; 

$total_belanja = 0;
$total_item = 0;
?>

<section class="py-5">
    <div class="container">
        <h1 class="fw-bold mb-2">Keranjang Sewa</h1>
        <p class="text-muted mb-5">Pastikan durasi dan alat camping pilihanmu sudah sesuai untuk petualanganmu.</p>
        
        <?php if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])): ?>
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <?php foreach($_SESSION['keranjang'] as $index => $item): 
                        $total_belanja += $item['subtotal'];
                        $total_item += $item['jumlah'];
                    ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-2 mb-3 mb-md-0">
                                    <img src="assets/img/produk/<?= htmlspecialchars($item['gambar']) ?>" 
                                        class="img-fluid rounded" 
                                        alt="<?= htmlspecialchars($item['nama']) ?>">
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <h6 class="fw-bold mb-2"><?= htmlspecialchars($item['nama']) ?></h6>
                                    <p class="text-danger fw-bold mb-0">Rp <?= number_format($item['harga'], 0, ',', '.') ?> <small class="text-muted">/hari</small></p>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <small class="text-muted d-block">Durasi Sewa</small>
                                    <div class="mb-2">
                                        <small class="fw-semibold">Mulai:</small>
                                        <span class="d-block"><?= date('d/m/Y', strtotime($item['tgl_mulai'])) ?></span>
                                    </div>
                                    <div>
                                        <small class="fw-semibold">Kembali:</small>
                                        <span class="d-block"><?= date('d/m/Y', strtotime($item['tgl_kembali'])) ?></span>
                                    </div>
                                    <span class="badge bg-light text-dark mt-1"><?= $item['hari'] ?> Hari</span>
                                </div>
                                <div class="col-md-2 mb-3 mb-md-0 text-center">
                                    <small class="text-muted d-block">Jumlah Unit</small>
                                    <div class="input-group input-group-sm mt-1" style="max-width: 120px; margin: 0 auto;">
                                        <button class="btn btn-outline-secondary" type="button">-</button>
                                        <input type="text" class="form-control text-center" value="<?= $item['jumlah'] ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button">+</button>
                                    </div>
                                </div>
                                <div class="col-md-1 text-end">
                                    <a href="hapus-keranjang.php?index=<?= $index ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Hapus item ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <a href="katalog.php" class="btn btn-outline-danger rounded-pill px-4">
                        <i class="fas fa-plus me-2"></i>Tambah Alat Lainnya
                    </a>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Ringkasan Penyewaan</h5>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Subtotal (<?= $total_item ?> Unit)</span>
                                <span class="fw-semibold">Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Durasi</span>
                                <span class="fw-semibold"><?= $item['hari'] ?> Hari x <?= $total_item ?></span>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Jaminan Alat</label>
                                <select class="form-select">
                                    <option value="KTP">KTP</option>
                                    <option value="SIM">SIM</option>
                                    <option value="KTM">KTM</option>
                                </select>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-bold fs-5">TOTAL PEMBAYARAN</span>
                                <span class="text-danger fw-bold fs-4">Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-danger w-100 py-3 rounded-pill fw-bold">
                                Lanjutkan Pembayaran <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart text-muted" style="font-size: 5rem;"></i>
                <h4 class="mt-4 mb-3">Keranjang Anda Masih Kosong</h4>
                <p class="text-muted mb-4">Yuk, pilih alat camping untuk petualangan Anda!</p>
                <a href="katalog.php" class="btn btn-danger btn-lg rounded-pill px-5">Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>