<!-- CDN Bootstrap 5 & Font Awesome untuk icon -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .custom-sidebar {
        width: 280px; 
        height: 100vh; 
        position: fixed; 
        background-color: #f0f4fa; /* Warna background putih kebiruan pucat */
        border-right: 1px solid #dcdcdc;
    }
    .brand-title {
        color: #990000; /* Warna merah marun khas SIMRO */
        font-weight: 800;
        font-size: 1.5rem;
        line-height: 1.1;
        letter-spacing: 0.5px;
    }
    .brand-subtitle {
        color: #990000;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .custom-hr {
        border-top: 2px solid #990000;
        opacity: 1;
        margin-top: 15px;
        margin-bottom: 25px;
    }
    .custom-hr-bottom {
        border-top: 2px solid #990000;
        opacity: 1;
        margin-bottom: 15px;
    }
    .nav-custom .nav-link {
        color: #000000 !important; /* Teks menu warna hitam */
        font-weight: 600;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        transition: all 0.2s;
    }
    .nav-custom .nav-link i {
        font-size: 1.2rem;
        width: 30px;
    }
    /* Style menu ketika aktif (Persis seperti Kelola Pengembalian di gambar) */
    .nav-custom .nav-link.active {
        background-color: #990000 !important; /* Kotak merah marun */
        color: #ffffff !important; /* Teks putih */
    }
    .logout-section {
        padding-top: 15px;
    }
    .logout-link {
        color: #000000 !important;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 10px 16px;
    }
    .logout-link i {
        font-size: 1.3rem;
        margin-right: 10px;
    }
</style>

<div class="d-flex flex-column flex-shrink-0 p-3 custom-sidebar justify-content-between">
    <div>
        <!-- Bagian Logo & Judul Atas -->
        <div class="d-flex align-items-center px-2 pt-2">
            <!-- SILAKAN GANTI 'assets/logo.png' DENGAN PATH LOGO ANDA -->
            <img src="assets/logo.png" alt="Logo SIMRO" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
                <div class="brand-title">SIMRO</div>
                <div class="brand-subtitle">Merayau Adventure</div>
            </div>
        </div>
        
        <hr class="custom-hr">
        
        <!-- Daftar Menu -->
        <ul class="nav nav-pills flex-column nav-custom">
            <li class="nav-item">
                <a href="katalog.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'katalog.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-grid-2x2 me-2"><!-- Icon Grid/Kotak -->
                        <span class="d-inline-block" style="border: 2px solid currentColor; width: 7px; height: 7px; margin: 1px;"></span>
                        <span class="d-inline-block" style="border: 2px solid currentColor; width: 7px; height: 7px; margin: 1px;"></span><br>
                        <span class="d-inline-block" style="border: 2px solid currentColor; width: 7px; height: 7px; margin: 1px;"></span>
                        <span class="d-inline-block" style="border: 2px solid currentColor; width: 7px; height: 7px; margin: 1px;"></span>
                    </i> 
                    Kelola Katalog
                </a>
            </li>
            <li>
                <a href="penyewaan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'penyewaan.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-cart-shopping me-2"></i> Kelola Penyewaan
                </a>
            </li>
            <li>
                <!-- Di gambar ini yang sedang aktif, otomatis akan berwarna merah marun jika file-nya pengembalian.php -->
                <a href="pengembalian.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pengembalian.php' || basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box-archive me-2"></i> Kelola Pengembalian
                </a>
            </li>
            <li>
                <a href="pelanggan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pelanggan.php') ? 'active' : ''; ?>">
                    <i class="fa-regular fa-user me-2"></i> Kelola pelanggan
                </a>
            </li>
            <li>
                <a href="maintenance.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'maintenance.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gear me-2"></i> Manitenance
                </a>
            </li>
        </ul>
    </div>

    <!-- Bagian Tombol Logout Paling Bawah -->
    <div class="logout-section">
        <hr class="custom-hr-bottom">
        <a href="logout.php" class="logout-link">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</div>