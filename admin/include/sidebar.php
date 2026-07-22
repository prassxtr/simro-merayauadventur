<!-- CDN Bootstrap 5 & Font Awesome untuk icon -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .custom-sidebar {
        width: 280px; 
        height: 100vh; 
        position: fixed; 
        background-color: #f8f9fa;
        border-right: 1px solid #dee2e6;
        z-index: 1000;
    }
    .brand-title {
        color: #990000;
        font-weight: 800;
        font-size: 1.4rem;
        line-height: 1.2;
        letter-spacing: 0.5px;
    }
    .brand-subtitle {
        color: #990000;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .custom-hr {
        border-top: 2px solid #990000;
        opacity: 1;
        margin: 20px 0;
    }
    .custom-hr-bottom {
        border-top: 1px solid #dee2e6;
        opacity: 1;
        margin: 15px 0;
    }
    .nav-custom .nav-link {
        color: #2c3e50 !important;
        font-weight: 600;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    .nav-custom .nav-link:hover {
        background-color: #e9ecef;
        color: #990000 !important;
    }
    .nav-custom .nav-link i {
        font-size: 1.1rem;
        width: 28px;
        margin-right: 12px;
    }
    .nav-custom .nav-link.active {
        background-color: #990000 !important;
        color: #ffffff !important;
    }
    .nav-custom .nav-link.active i {
        color: #ffffff !important;
    }
    .logout-section {
        padding-top: 10px;
    }
    .logout-link {
        color: #2c3e50 !important;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 10px 16px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .logout-link:hover {
        background-color: #e9ecef;
        color: #990000 !important;
    }
    .logout-link i {
        font-size: 1.2rem;
        margin-right: 12px;
    }
</style>

<div class="d-flex flex-column flex-shrink-0 p-3 custom-sidebar">
    <div>
        <!-- Bagian Logo & Judul Atas -->
        <div class="d-flex align-items-center px-2 pt-2 pb-3">
            <img src="../assets/img/logo.png" alt="Logo SIMRO" class="rounded-circle me-3" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid #990000;">
            <div>
                <div class="brand-title">SIMRO</div>
                <div class="brand-subtitle">Merayau Adventure</div>
            </div>
        </div>
        
        <hr class="custom-hr">
        
        <!-- Daftar Menu -->
        <ul class="nav nav-pills flex-column nav-custom mb-4">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-layer-group"></i> 
                    <span>Kelola Katalog</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="penyewaan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'penyewaan.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-cart-shopping"></i> 
                    <span>Kelola Penyewaan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pengembalian.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pengembalian.php' || basename($_SERVER['PHP_SELF']) == 'pengembalian.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clipboard-check"></i> 
                    <span>Kelola Pengembalian</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pelanggan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pelanggan.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i> 
                    <span>Kelola Pelanggan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="maintenance.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'maintenance.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-screwdriver-wrench"></i> 
                    <span>Maintenance</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Bagian Tombol Logout Paling Bawah -->
    <div class="logout-section mt-auto">
        <hr class="custom-hr-bottom">
        
        <!-- Tambahkan onclick="return confirm(...)" untuk mencegah logout tidak sengaja -->
        <a href="logout.php" class="logout-link" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
            <i class="fa-solid fa-right-from-bracket"></i> 
            <span>Logout</span>
        </a>
    </div>
</div> 
</div> 