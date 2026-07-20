<?php 
session_start();
require_once 'config/koneksi.php';
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    $foto_name = $_FILES['foto_profil']['name'];
    $foto_tmp = $_FILES['foto_profil']['tmp_name'];
    $foto_error = $_FILES['foto_profil']['error'];
    
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if ($foto_error === UPLOAD_ERR_OK && !empty($foto_name)) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $_FILES['foto_profil']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $foto_baru = time() . '_' . basename($foto_name);
            
            if (!empty($user['foto_profil']) && file_exists($upload_dir . $user['foto_profil'])) {
                unlink($upload_dir . $user['foto_profil']);
            }
            
            move_uploaded_file($foto_tmp, $upload_dir . $foto_baru);
            
            $update_query = "UPDATE users SET 
                nama_lengkap='$nama_lengkap',
                email='$email',
                no_telepon='$no_telepon',
                alamat='$alamat',
                foto_profil='$foto_baru'
                WHERE id=$user_id";
        } else {
            echo "<script>alert('Format foto harus JPG atau PNG!');</script>";
            $update_query = "UPDATE users SET 
                nama_lengkap='$nama_lengkap',
                email='$email',
                no_telepon='$no_telepon',
                alamat='$alamat'
                WHERE id=$user_id";
        }
    } else {
        $update_query = "UPDATE users SET 
            nama_lengkap='$nama_lengkap',
            email='$email',
            no_telepon='$no_telepon',
            alamat='$alamat'
            WHERE id=$user_id";
    }
    
    if (mysqli_query($koneksi, $update_query)) {
        echo "<script>alert('Profil berhasil diupdate!'); window.location='profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal update profil');</script>";
    }
}
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h4 class="fw-bold mb-4 text-center">Edit Profil</h4>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <?php if (!empty($user['foto_profil']) && file_exists('uploads/' . $user['foto_profil'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($user['foto_profil']) ?>" 
                                         class="rounded-circle mb-3" 
                                         style="width: 120px; height: 120px; object-fit: cover;" 
                                         id="previewImage" 
                                         alt="Foto Profil">
                                <?php else: ?>
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;" 
                                         id="previewInitial">
                                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                    </div>
                                    <img src="" 
                                         class="rounded-circle mb-3 d-none" 
                                         style="width: 120px; height: 120px; object-fit: cover;" 
                                         id="previewImage" 
                                         alt="Foto Profil">
                                <?php endif; ?>
                                
                                <div>
                                    <label class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-camera"></i> Pilih Foto
                                        <input type="file" name="foto_profil" class="d-none" accept="image/*" onchange="previewFile(this)">
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" 
                                       value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="no_telepon" class="form-control" 
                                       value="<?= htmlspecialchars($user['no_telepon'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="profile.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" name="update" class="btn btn-danger">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function previewFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var initial = document.getElementById('previewInitial');
            if(initial) initial.classList.add('d-none');
            var img = document.getElementById('previewImage');
            img.src = e.target.result;
            img.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>