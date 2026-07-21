<?php 
include 'includes/header.php'; 
?>

<!-- Header Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #8B0000 0%, #6d0000 100%);">
    <div class="container text-center text-white">
        <h1 class="display-5 fw-bold mb-3">Tentang Kami</h1>
        <p class="lead mb-0">Mengenal lebih dekat SIMRO Merayau Adventure, mitra petualangan Anda.</p>
    </div>
</section>

<!-- Section 1: Siapa Kami (DENGAN GRID FOTO) -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <!-- Bagian Foto (Grid 4 Foto) -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="row g-3">
                    <!-- Ganti 'foto1.jpg' dst dengan nama file foto Anda yang sebenarnya -->
                    <div class="col-6">
                        <img src="assets/img/tentang/tim.jpeg" alt="Tim SIMRO" class="img-fluid rounded-4 shadow-sm w-100" style="object-fit: cover; height: 200px;">
                    </div>
                    <div class="col-6">
                        <img src="assets/img/tentang/maintenance.jpeg" alt="Basecamp SIMRO" class="img-fluid rounded-4 shadow-sm w-100" style="object-fit: cover; height: 200px;">
                    </div>
                    <div class="col-6">
                        <img src="assets/img/tentang/alat.jpeg" alt="Peralatan SIMRO" class="img-fluid rounded-4 shadow-sm w-100" style="object-fit: cover; height: 200px;">
                    </div>
                    <div class="col-6">
                        <img src="assets/img/tentang/kelompok.jpeg" alt="Aktivitas Outdoor" class="img-fluid rounded-4 shadow-sm w-100" style="object-fit: cover; height: 200px;">
                    </div>
                </div>
            </div>

            <!-- Bagian Teks -->
            <div class="col-lg-6">
                <h5 class="text-danger fw-bold text-uppercase">Siapa Kami?</h5>
                <h2 class="fw-bold mb-4">SIMRO Merayau Adventure</h2>
                <p class="text-muted mb-4">
                    SIMRO (Sistem Informasi Penyewaan Alat Outdoor) Merayau Adventure adalah penyedia jasa sewa peralatan camping dan pendakian yang berbasis di Pontianak, Kalimantan Barat. 
                </p>
                <p class="text-muted mb-4">
                    Kami hadir untuk memudahkan para pecinta alam dalam mendapatkan perlengkapan outdoor yang <strong>modern, higienis, dan terawat</strong> tanpa harus membeli dengan harga mahal. Dengan sistem pemesanan online, Anda bisa memilih alat, menentukan tanggal, dan mengambilnya langsung di basecamp kami dengan proses yang cepat dan transparan.
                </p>
                <a href="katalog.php" class="btn btn-danger px-4 py-2 rounded-pill">
                    <i class="fas fa-shopping-bag me-2"></i>Lihat Katalog Alat
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Visi & Misi -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Visi & Misi</h2>
            <p class="text-muted">Komitmen kami untuk para petualang</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-eye fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Visi</h4>
                    <p class="text-muted">Menjadi penyedia jasa penyewaan alat outdoor terdepan dan terpercaya di Kalimantan Barat yang mengutamakan kualitas, kenyamanan, dan keselamatan penyewa.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-bullseye fa-3x text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Misi</h4>
                    <ul class="text-muted text-start list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-danger me-2"></i> Menyediakan peralatan camping berkualitas tinggi dan terawat.</li>
                        <li class="mb-2"><i class="fas fa-check text-danger me-2"></i> Memberikan harga sewa yang terjangkau dan transparan.</li>
                        <li class="mb-2"><i class="fas fa-check text-danger me-2"></i> Mempermudah proses penyewaan melalui sistem digital.</li>
                        <li><i class="fas fa-check text-danger me-2"></i> Memberikan pelayanan yang ramah, cepat, dan informatif.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Mengapa Memilih Kami -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Mengapa Memilih Kami?</h2>
            <p class="text-muted">Keunggulan yang kami tawarkan untuk petualangan Anda</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-tools fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">Alat Terawat</h5>
                    <p class="small text-muted mb-0">Setiap peralatan dicek dan dibersihkan setelah setiap kali pemakaian.</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-tags fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">Harga Terjangkau</h5>
                    <p class="small text-muted mb-0">Harga bersaing dengan diskon khusus untuk penyewaan jangka panjang.</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-mobile-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">Pemesanan Mudah</h5>
                    <p class="small text-muted mb-0">Cek stok dan pesan alat kapan saja melalui website kami.</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">Jaminan Keamanan</h5>
                    <p class="small text-muted mb-0">Proses sewa aman dengan sistem jaminan identitas yang jelas.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 4: Cara Sewa & Kontak -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <!-- Cara Sewa -->
            <div class="col-lg-6">
                <h3 class="fw-bold mb-4">Cara Menyewa</h3>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <span class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">1</span>
                    </div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-1">Pilih Alat</h6>
                        <p class="text-muted small mb-0">Pilih peralatan yang Anda butuhkan di halaman Katalog.</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <span class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">2</span>
                    </div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-1">Tentukan Tanggal & Masukkan Keranjang</h6>
                        <p class="text-muted small mb-0">Pilih tanggal mulai dan kembali sewa, lalu tambahkan ke keranjang.</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <span class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">3</span>
                    </div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-1">Checkout & Bayar DP</h6>
                        <p class="text-muted small mb-0">Lakukan pembayaran DP sesuai total tagihan yang muncul.</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">4</span>
                    </div>
                    <div class="ms-3">
                        <h6 class="fw-bold mb-1">Ambil di Basecamp</h6>
                        <p class="text-muted small mb-0">Tunjukkan bukti pesanan dan bawa identitas (KTP/KTM) untuk jaminan.</p>
                    </div>
                </div>
            </div>

            <!-- Kontak -->
            <div class="col-lg-6">
                <h3 class="fw-bold mb-4">Hubungi Kami</h3>
                <div class="card border-0 shadow-sm p-4">
                    <div class="d-flex align-items-start mb-4">
                        <!-- Ditambahkan fa-fw agar sejajar -->
                        <i class="fas fa-map-marker-alt text-danger fa-lg fa-fw mt-1 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Alamat Basecamp</h6>
                            <p class="text-muted small mb-0">
                                <a href="https://www.google.com/maps/search/?api=1&query=Jl.Tanray+2+Gg.Nusa+Abadi+No.+22b+Pontianak" target="_blank" class="text-muted text-decoration-none">
                                    Jl. Tanray 2 Gg. Nusa Abadi No. 22b, Pontianak, Kalimantan Barat.
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <i class="fas fa-phone-alt text-danger fa-lg fa-fw mt-1 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Telepon / WhatsApp</h6>
                            <p class="text-muted small mb-0">
                                <a href="https://wa.me/6281522969194" target="_blank" class="text-muted text-decoration-none">+62 815-2296-9194</a>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <i class="fas fa-envelope text-danger fa-lg fa-fw mt-1 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Email</h6>
                            <p class="text-muted small mb-0">info@simromerayau.com</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <i class="fas fa-clock text-danger fa-lg fa-fw mt-1 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Jam Operasional</h6>
                            <p class="text-muted small mb-0">Setiap Hari: 08.00 - 23.30 WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>