<?php 
include 'includes/header.php'; 

$nomor_pesanan = isset($_GET['order']) ? $_GET['order'] : '';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- Success Icon -->
                <div class="mb-4">
                    <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-check text-white" style="font-size: 3rem;"></i>
                    </div>
                </div>
                
                <h1 class="fw-bold mb-3" style="color: #8B0000;">Petualangan Anda Dimulai!</h1>
                <p class="text-muted mb-5">
                    Terima kasih! Pembayaran Anda telah kami terima dan pesanan Anda sedang kami siapkan untuk perjalanan luar biasa Anda.
                </p>
                
                <!-- Transaction Summary -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4 text-start">
                                <h6 class="fw-bold text-danger mb-4">
                                    <i class="fas fa-receipt me-2"></i>Ringkasan Transaksi
                                </h6>
                                <div class="mb-3">
                                    <small class="text-muted d-block">ID PESANAN</small>
                                    <strong class="fs-5">#<?= $nomor_pesanan ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">TANGGAL</small>
                                    <strong><?= date('d M Y') ?></strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Total Bayar</small>
                                    <strong class="text-danger fs-4">Rp 150.000</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4 text-start">
                                <h6 class="fw-bold text-danger mb-4">
                                    <i class="fas fa-campground me-2"></i>Item yang Disewa
                                </h6>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold">Tenda Dome 4P Pro</span>
                                        <span class="fw-bold">1x</span>
                                    </div>
                                    <small class="text-muted">Kapasitas 4 Orang • Waterproof</small>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold">Tas Carrier</span>
                                        <span class="fw-bold">1x</span>
                                    </div>
                                    <small class="text-muted">Ergonomic Back System</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-3 justify-content-center">
                    <a href="riwayat.php" class="btn btn-danger btn-lg rounded-pill px-5">
                        <i class="fas fa-list me-2"></i>Lihat Sewa Aktif
                    </a>
                    <a href="katalog.php" class="btn btn-outline-danger btn-lg rounded-pill px-5">
                        <i class="fas fa-search me-2"></i>Kembali ke Katalog
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>