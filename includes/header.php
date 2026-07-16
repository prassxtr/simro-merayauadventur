<?php 
// 1. Pastikan session dimulai hanya sekali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Gunakan __DIR__ agar path selalu benar
require_once __DIR__ . '/../config/koneksi.php'; 

// 3. Deteksi halaman aktif untuk styling menu
$current_page = basename($_SERVER['PHP_SELF']);

// 4. Ambil data user jika sudah login
$user_nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$user_initial = strtoupper(substr($user_nama, 0, 1)); // Ambil huruf pertama untuk avatar
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMRO Merayau Adventure - Sewa Alat Camping</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top" style="z-index: 1030;">
    <div class="container">
        
        <!-- LOGO (Kiri) -->
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo SIMRO" style="height: 50px; width: auto; object-fit: contain;" class="me-3">
            <div class="d-flex flex-column justify-content-center" style="line-height: 1.1;">
                <span class="fw-bold" style="font-size: 1.6rem; color: #8B0000; letter-spacing: 1px;">SIMRO</span>
                <span class="fw-semibold" style="font-size: 0.75rem; color: #8B0000; letter-spacing: 2px; text-transform: uppercase;">MERAYAU ADVENTUR</span>
            </div>
        </a>
        
        <!-- Tombol Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            
            <!-- MENU NAVIGASI (Tengah) -->
            <ul class="navbar-nav mx-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php' || $current_page == '') ? 'active' : '' ?>" href="<?= BASE_URL ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'katalog.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>katalog.php">Katalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'riwayat.php') ? 'active' : '' ?>" href="<?= isset($_SESSION['user_id']) ? BASE_URL.'riwayat.php' : BASE_URL.'login.php' ?>">Riwayat Penyewaan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'tentang.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>tentang.php">Tentang Kami</a>
                </li>
            </ul>
            
            <!-- USER MENU (Kanan) - DINAMIS -->
            <ul class="navbar-nav align-items-center">
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- SUDAH LOGIN: Tampilkan Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <!-- Avatar Bulat dengan Initial Nama -->
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-weight: bold; font-size: 0.9rem;">
                                <?= $user_initial ?>
                            </div>
                            <span class="fw-semibold"><?= htmlspecialchars($user_nama) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>profile.php">
                                    <i class="fas fa-user me-2 text-danger"></i> Profile Saya
                                </a>
                            </li>
                            <li>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <?php else: ?>
                    <!-- BELUM LOGIN: Tampilkan Login & Registrasi -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger btn-sm ms-2 px-4 rounded-pill fw-semibold" href="<?= BASE_URL ?>register.php">Registrasi</a>
                    </li>
                <?php endif; ?>
                
                <!-- Icon Keranjang (Selalu Tampil) -->
                <li class="nav-item ms-lg-3 position-relative">
                    <a class="nav-link" href="<?= BASE_URL ?>keranjang.php" title="Keranjang Belanja">
                        <i class="fas fa-shopping-cart fa-lg" style="color: #8B0000;"></i>
                        <?php if(isset($_SESSION['keranjang']) && count($_SESSION['keranjang']) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0 4px;">
                                <?= count($_SESSION['keranjang']) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            
        </div>
    </div>
</nav>