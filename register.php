<?php 
require_once 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Validasi
    if ($password !== $konfirmasi_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Cek email sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
        
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (nama_lengkap, email, no_telepon, alamat, password, role) 
                      VALUES ('$nama', '$email', '$telepon', '$alamat', '$password_hash', 'customer')";
            
            if (mysqli_query($koneksi, $query)) {
                $success = 'Registrasi berhasil! Silakan login.';
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Registrasi gagal! Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SIMRO Merayau Adventur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B0000;
            --primary-hover: #6d0000;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .register-image {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('assets/img/hero-bg.jpeg');
            background-size: cover;
            background-position: center;
            min-height: 700px;
            display: flex;
            align-items: center;
            padding: 40px;
            color: white;
        }
        
        .register-image h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .register-image p {
            font-size: 1.1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .register-form {
            padding: 50px 40px;
            max-height: 700px;
            overflow-y: auto;
        }
        
        .register-form h3 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .form-control {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(139, 0, 0, 0.15);
        }
        
        .btn-register {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .login-link {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .login-link:hover {
            text-decoration: underline;
        }
        
        .alert-danger {
            background-color: #fff5f5;
            border-color: #fed7d7;
            color: #c53030;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #f0fff4;
            border-color: #c6f6d5;
            color: #276749;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .register-image {
                display: none;
            }
            
            .register-form {
                padding: 40px 30px;
                max-height: none;
            }
            
            .register-image h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="register-container">
        <div class="row g-0">
            <!-- Left Side - Image -->
            <div class="col-md-6 d-none d-md-block">
                <div class="register-image">
                    <div>
                        <h2>Mulai Petualangan Anda</h2>
                        <p>Bergabunglah bersama kami dan nikmati pengalaman camping yang tak terlupakan</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="col-md-6">
                <div class="register-form">
                    <h3>Buat Akun Baru</h3>
                    <p class="text-muted mb-4">Lengkapi form di bawah untuk mendaftar</p>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap anda" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Masukkan email anda" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="telepon" class="form-control" placeholder="Masukkan nomor telepon" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat anda" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Buat password anda" required minlength="6">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control" placeholder="Konfirmasi password anda" required minlength="6">
                        </div>
                        
                        <button type="submit" name="register" class="btn btn-register w-100 text-white">
                            Daftar Sekarang
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">Sudah punya akun? <a href="login.php" class="login-link">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>