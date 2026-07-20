<?php
ob_start();
include 'includes/header.php'; 

// Cek koneksi database (Sesuaikan dengan variabel koneksi kamu, di sini saya siapkan cadangan ke $conn)
if (!isset($koneksi) && isset($conn)) {
    $koneksi = $conn;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['keranjang'])) {
    header('Location: keranjang.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$total_belanja = 0;

foreach($_SESSION['keranjang'] as $item) {
    $total_belanja += $item['subtotal'];
}

$error = '';
$success = '';

// Proses checkout dengan upload bukti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $nomor_pesanan = buat_kode_pesanan();
    $tgl_sewa = $_SESSION['keranjang'][0]['tgl_mulai'];
    $tgl_kembali = $_SESSION['keranjang'][0]['tgl_kembali'];
    
    // UBAHAN DISINI: Folder upload disesuaikan dengan folder yang dibaca oleh admin
    $upload_dir = 'assets/img/bukti/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['bukti_pembayaran'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = 'bukti_' . $nomor_pesanan . '_' . time() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;
    
    // Validasi file
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file_ext, $allowed_ext)) {
        $error = "Format file tidak didukung. Gunakan JPG, PNG, atau PDF.";
    } elseif ($file['size'] > $max_size) {
        $error = "Ukuran file terlalu besar. Maksimal 2MB.";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Terjadi kesalahan saat upload file.";
    } else {
        // Pindahkan file ke folder aset gambar
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Insert ke database
            $query_sewa = "INSERT INTO penyewaan (user_id, nomor_pesanan, tanggal_sewa, tanggal_kembali, total_harga, status_pembayaran, status_sewa, bukti_pembayaran) 
                           VALUES ($user_id, '$nomor_pesanan', '$tgl_sewa', '$tgl_kembali', $total_belanja, 'pending', 'diproses', '$file_name')";
            
            if (mysqli_query($koneksi, $query_sewa)) {
                $penyewaan_id = mysqli_insert_id($koneksi);
                
                foreach ($_SESSION['keranjang'] as $item) {
                    $p_id = $item['produk_id'];
                    $jml = $item['jumlah'];
                    $sub = $item['subtotal'];
                    
                    mysqli_query($koneksi, "INSERT INTO detail_penyewaan (penyewaan_id, produk_id, jumlah, subtotal) VALUES ($penyewaan_id, $p_id, $jml, $sub)");
                    mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jml WHERE id = $p_id");
                }
                
                unset($_SESSION['keranjang']);
                header('Location: success.php?order=' . $nomor_pesanan);
                exit;
            } else {
                $error = "Gagal menyimpan pesanan: " . mysqli_error($koneksi);
            }
        } else {
            $error = "Gagal mengupload file ke folder tujuan. Pastikan folder 'assets/img/bukti/' dapat ditulis.";
        }
    }
}

ob_end_flush();
?>

<section class="py-5">
    <div class="container">
        <h1 class="fw-bold mb-2" style="color: #8B0000;">Penyelesaian Pembayaran</h1>
        <p class="text-muted mb-5">Satu langkah lagi menuju petualangan Anda.</p>
        
        <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Payment Methods -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="far fa-credit-card me-2"></i>Metode Pembayaran
                        </h5>
                        
                        <!-- Bank Transfer -->
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Transfer ke Rekening Bank</h6>
                            <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white p-2 rounded me-3 border">
                                        <i class="fas fa-university fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Bank Central Asia (BCA)</div>
                                        <small class="text-muted">a.n. PT SIMRO OUTDOOR INDONESIA</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5">8830 1234 5678</div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="copyRekening()">
                                        <i class="far fa-copy me-1"></i>Copy
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Upload Bukti -->
                            <form method="POST" enctype="multipart/form-data" id="formCheckout">
                                <div class="border rounded p-4 mb-3">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <h6 class="fw-semibold mb-2">Upload Bukti Pembayaran</h6>
                                        <p class="text-muted small mb-3">Format: JPG, PNG, PDF (Maksimal 2MB)</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" 
                                               class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Pastikan bukti pembayaran jelas dan terbaca
                                        </div>
                                    </div>
                                    
                                    <div id="previewContainer" class="d-none mb-3">
                                        <p class="fw-semibold mb-2">Preview:</p>
                                        <div id="previewContent" class="border rounded p-3 bg-light"></div>
                                    </div>
                                </div>
                        </div>
                        
                        <!-- QRIS -->
                        <div>
                            <h6 class="fw-semibold mb-3">QRIS</h6>
                            <div class="border rounded p-4 text-center">
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                                     style="height: 200px; width: 200px; margin: 0 auto;">
                                    <i class="fas fa-qrcode fa-4x text-muted"></i>
                                </div>
                                <p class="text-muted small mb-0">Scan kode QR untuk pembayaran instan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="fas fa-file-invoice me-2"></i>Ringkasan Pesanan
                        </h5>
                        
                        <?php foreach($_SESSION['keranjang'] as $item): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><?= htmlspecialchars($item['nama']) ?></span>
                                <span class="text-muted">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                            </div>
                            <small class="text-muted"><?= $item['jumlah'] ?> unit x <?= $item['hari'] ?> hari</small>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="far fa-calendar me-1"></i>
                                <?= date('d M', strtotime($_SESSION['keranjang'][0]['tgl_mulai'])) ?> - 
                                <?= date('d M Y', strtotime($_SESSION['keranjang'][0]['tgl_kembali'])) ?> 
                            </small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold fs-5">Total Bayar</span>
                            <span class="text-danger fw-bold fs-4">Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
                        </div>
                        
                        <button type="submit" form="formCheckout" class="btn btn-danger w-100 py-3 rounded-pill fw-bold mb-3">
                            Bayar Sekarang <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        
                        <p class="text-center text-muted small mb-0">
                            Dengan menekan tombol di atas, Anda menyetujui 
                            <a href="#" class="text-danger">Syarat & Ketentuan</a> penyewaan kami.
                        </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyRekening() {
    navigator.clipboard.writeText('883012345678');
    alert('Nomor rekening berhasil disalin!');
}

// Preview file yang dipilih
document.getElementById('bukti_pembayaran').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('previewContainer');
    const previewContent = document.getElementById('previewContent');
    
    if (file) {
        previewContainer.classList.remove('d-none');
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContent.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            previewContent.innerHTML = `<i class="fas fa-file-pdf fa-3x text-danger"></i><p class="mb-0 mt-2">${file.name}</p>`;
        }
    } else {
        previewContainer.classList.add('d-none');
    }
});
</script>

<?php include 'includes/footer.php'; ?>