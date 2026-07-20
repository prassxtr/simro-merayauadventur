<!-- Hero Section - Pas 1 Layar -->
<section class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/hero-bg.jpeg'); background-size: cover; background-position: center; height: calc(100vh - 70px); display: flex; align-items: center;">
    <div class="container">
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-3 fw-bold text-white mb-4" style="line-height: 1.2; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
                    Jasa Penyewaan Alat<br>
                    Outdoor Camping <br>
                    Merayau Adventure 
                </h1> 
                <p class="lead text-white mb-5" style="font-size: 1.2rem; max-width: 700px; text-shadow: 1px 1px 2px rgba(0,0,0,0.7);">
                    Modern, higienis, dan terawat. Pilih perlengkapan petualangan Anda secara online, tentukan opsi jaminan, bayar DP, dan ambil di basecamp kami!
                </p>
                <a href="katalog.php" class="btn btn-danger btn-lg px-5 py-3 rounded-pill fw-bold" style="font-size: 1.1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    Sewa Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Kategori Perlengkapan</h2>
            <p class="text-muted">Temukan perlengkapan yang nyaman untuk menemani petualangan anda</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm overflow-hidden h-100">
                    <img src="assets/img/kategori/tenda.png" class="card-img-top" style="height: 500px; object-fit: cover;" alt="Tenda">
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <img src="assets/img/kategori/cookingset.png" class="card-img-top" style="height: 240px; object-fit: cover;" alt="Peralatan Masak">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm overflow-hidden h-100">
                            <img src="assets/img/kategori/carrier.png" class="card-img-top" style="height: 240px; object-fit: cover;" alt="Carrier">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm overflow-hidden h-100">
                            <img src="assets/img/kategori/sepatu.png" class="card-img-top" style="height: 240px; object-fit: cover;" alt="Sepatu">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Aturan Sewa Section -->
<!-- Aturan Sewa Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Aturan Sewa</h2>
            <p class="text-muted">Ingat wajib patuhi aturan sewa ini</p>
        </div>
        
        <div class="row g-4">
            <!-- Waktu Sewa Box (Kiri - Besar) -->
            <div class="col-lg-6">
                <div class="card border-0 h-100" style="background: #FFF0F0; border-radius: 15px;">
                    <div class="card-body p-4 position-relative">
                        <!-- Badge Diskon -->
                        <div class="position-absolute top-0 end-0 mt-3 me-3">
                            <div style="background: #8B0000; color: white; padding: 15px 10px; clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 85%, 0 100%); text-align: center; min-width: 70px;">
                                <div style="font-size: 0.65rem; text-transform: uppercase; margin-bottom: 2px;">DISKON</div>
                                <div style="font-size: 1.4rem; font-weight: bold; line-height: 1;">30%</div>
                            </div>
                        </div>
                        
                        <h5 class="fw-bold mb-3" style="color: #8B0000;">WAKTU SEWA</h5>
                        <ul class="list-unstyled mb-0" style="font-size: 0.9rem; color: #666;">
                            <li class="mb-3">
                                Maksimal penyewaan dihitung per hari (24 jam). Toleransi waktu pengembalian hanya diberikan 4 jam. Keterlambatan pengembalian akan dikenakan biaya tambahan sesuai tarif sewa per hari.
                            </li>
                            <li>
                                Dan diskon 30% untuk penyewaan lebih dari 7 hari.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Rules List (Kanan) -->
            <div class="col-lg-6">
                <!-- Pengembalian Peralatan -->
                <div class="d-flex align-items-start mb-4 pb-4" style="border-bottom: 1px dashed #ddd;">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #FFF5F5;">
                            <i class="fas fa-upload" style="color: #8B0000; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #8B0000;">PENGEMBALIAN PERALATAN</h6>
                        <p class="text-muted mb-0 small">Penyewa wajib memeriksa kondisi barang saat pengambilan. setelah barang dibawa, dianggap sudah sesuai dan lengkap.</p>
                    </div>
                </div>
                
                <!-- Kerusakan atau Kehilangan -->
                <div class="d-flex align-items-start mb-4 pb-4" style="border-bottom: 1px dashed #ddd;">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #FFF5F5;">
                            <i class="fas fa-exclamation-triangle" style="color: #8B0000; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #8B0000;">KERUSAKAN ATAU KEHILANGAN</h6>
                        <p class="text-muted mb-0 small">Penyewa bertanggung jawab atas kerusakan atau kehilangan barang selama masa sewa. Biaya ganti rugi disesuaikan dengan biaya perbaikan atau harga barang-barang.</p>
                    </div>
                </div>
                
                <!-- Jaminan -->
                <div class="d-flex align-items-start mb-4 pb-4" style="border-bottom: 1px dashed #ddd;">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #FFF5F5;">
                            <i class="fas fa-shield-alt" style="color: #8B0000; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #8B0000;">JAMINAN</h6>
                        <p class="text-muted mb-0 small">Penyewa harus memberikan jaminan identitas asli (KTP/SIM/KTM) selama masa sewa. Identitas akan dikembalikan setelah alat dikembalikan.</p>
                    </div>
                </div>
                
                <!-- Penggunaan Peralatan -->
                <div class="d-flex align-items-start mb-4 pb-4" style="border-bottom: 1px dashed #ddd;">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #FFF5F5;">
                            <i class="fas fa-cog" style="color: #8B0000; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #8B0000;">PENGGUNAAN PERALATAN</h6>
                        <p class="text-muted mb-0 small">Gunakan peralatan sesuai fungsi. Dilarang menggunakan peralatan untuk aktivitas yang dapat merusak atau membahayakan alat.</p>
                    </div>
                </div>
                
                <!-- Pengambilan Peralatan -->
                <div class="d-flex align-items-start mb-4">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #FFF5F5;">
                            <i class="fas fa-search" style="color: #8B0000; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #8B0000;">PENGAMBILAN PERALATAN</h6>
                        <p class="text-muted mb-0 small">Penyewa wajib memeriksa kondisi barang saat pengambilan. Setelah barang dibawa, dianggap sudah sesuai dan lengkap.</p>
                    </div>
                </div>
                
                <!-- Pembatalan -->
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #FFF5F5;">
                            <i class="fas fa-exclamation-triangle" style="color: #8B0000; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #8B0000;">PEMBATALAN</h6>
                        <p class="text-muted mb-0 small">Pembatalan mendadak dapat menyebabkan DP tidak dapat dikembalikan.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Banner -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 text-center" style="background: linear-gradient(135deg, #8B0000 0%, #6d0000 100%); border-radius: 10px;">
                    <div class="card-body py-3 px-4">
                        <p class="text-white mb-0 small">
                            <i class="fas fa-handshake me-2"></i>
                            <strong>Dengan melakukan penyewaan, penyewa dianggap telah membaca dan menyetujui semua aturan di atas.</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>