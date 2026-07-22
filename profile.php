<?php 
session_start(); // Pastikan session dimulai (jika belum ada di header.php/koneksi.php)
// require_once 'include/auth_user.php';
require_once 'config/koneksi.php';
include 'includes/header.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <!-- Jika ada foto_profil, tampilkan foto. Jika tidak, tampilkan inisial -->
                            <?php if (!empty($user['foto_profil']) && file_exists('uploads/' . $user['foto_profil'])): ?>
                                <img src="uploads/<?= htmlspecialchars($user['foto_profil']) ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 80px; height: 80px; object-fit: cover;" 
                                     alt="Foto Profil">
                            <?php else: ?>
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            
                            <h3 class="fw-bold"><?= htmlspecialchars($user['nama_lengkap']) ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        
                        <hr>
                        
                        <h5 class="fw-bold mb-3">Informasi Pribadi</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Nama Lengkap</label>
                                <p class="fw-semibold mb-0"><?= htmlspecialchars($user['nama_lengkap']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Email</label>
                                <p class="fw-semibold mb-0"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">No. Telepon</label>
                                <p class="fw-semibold mb-0"><?= htmlspecialchars($user['no_telepon'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Alamat</label>
                                <p class="fw-semibold mb-0"><?= htmlspecialchars($user['alamat'] ?? '-') ?></p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4 d-flex justify-content-center gap-3">
                            <a href="edit-profile.php" class="btn btn-secondary px-4">
                                <i class="bi bi-pencil-square"></i> Edit Profil
                            </a>
                            <a href="riwayat.php" class="btn btn-danger px-4">
                                <i class="bi bi-clock-history"></i> Lihat Riwayat Penyewaan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>