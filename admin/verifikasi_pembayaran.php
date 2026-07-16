<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Update status pembayaran
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        mysqli_query($koneksi, "UPDATE penyewaan SET status_pembayaran = 'lunas' WHERE id = $id");
    } elseif ($action === 'reject') {
        mysqli_query($koneksi, "UPDATE penyewaan SET status_pembayaran = 'dibatalkan' WHERE id = $id");
    }
    
    header('Location: verifikasi_pembayaran.php');
    exit;
}

include '../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <h1 class="fw-bold mb-4" style="color: #8B0000;">Verifikasi Pembayaran</h1>
        
        <?php
        $query = mysqli_query($koneksi, "
            SELECT p.*, u.nama_lengkap 
            FROM penyewaan p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.status_pembayaran = 'pending' AND p.bukti_pembayaran IS NOT NULL
            ORDER BY p.created_at DESC
        ");
        ?>
        
        <?php if(mysqli_num_rows($query) > 0): ?>
            <div class="row g-4">
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><?= $row['nomor_pesanan'] ?></h5>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($row['nama_lengkap']) ?></p>
                                </div>
                                <span class="badge bg-warning text-dark">Pending</span>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total:</span>
                                    <span class="fw-bold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tanggal Sewa:</span>
                                    <span><?= date('d M Y', strtotime($row['tanggal_sewa'])) ?></span>
                                </div>
                            </div>
                            
                            <!-- Preview Bukti -->
                            <div class="mb-3">
                                <p class="fw-semibold mb-2">Bukti Pembayaran:</p>
                                <?php 
                                $file_path = '../assets/uploads/bukti_pembayaran/' . $row['bukti_pembayaran'];
                                $file_ext = strtolower(pathinfo($row['bukti_pembayaran'], PATHINFO_EXTENSION));
                                
                                if ($file_ext === 'pdf'): 
                                ?>
                                    <a href="<?= $file_path ?>" target="_blank" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-file-pdf me-2"></i>Lihat PDF
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $file_path ?>" target="_blank" class="d-block">
                                        <img src="<?= $file_path ?>" class="img-fluid rounded border" alt="Bukti Pembayaran">
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <a href="?action=approve&id=<?= $row['id'] ?>" 
                                    class="btn btn-success flex-fill"
                                    onclick="return confirm('Konfirmasi pembayaran ini?')">
                                    <i class="fas fa-check me-1"></i>Approve
                                </a>
                                <a href="?action=reject&id=<?= $row['id'] ?>" 
                                    class="btn btn-danger flex-fill"
                                    onclick="return confirm('Tolak pembayaran ini?')">
                                    <i class="fas fa-times me-1"></i>Tolak
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Tidak Ada Pembayaran Pending</h5>
                <p class="text-muted">Semua pembayaran sudah diverifikasi</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>