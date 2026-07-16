<?php 
require_once 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak terdaftar!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMRO Merayau Adventur</title>
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
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .login-image {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('assets/img/hero-bg.jpeg');
            background-size: cover;
            background-position: center;
            min-height: 600px;
            display: flex;
            align-items: center;
            padding: 40px;
            color: white;
        }
        
        .login-image h2 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .login-image p {
            font-size: 1.2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .login-form {
            padding: 60px 50px;
        }
        
        .login-form h3 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .login-form .text-muted {
            font-size: 0.95rem;
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
        
        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            padding: 0 15px;
            color: #999;
            font-size: 0.9rem;
        }
        
        .btn-social {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 10px;
            background: white;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-social:hover {
            background: #f8f9fa;
            border-color: #ccc;
        }
        
        .btn-social i {
            margin-right: 10px;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            float: right;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .register-link {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link:hover {
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
        
        @media (max-width: 768px) {
            .login-image {
                display: none;
            }
            
            .login-form {
                padding: 40px 30px;
            }
            
            .login-image h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container">
        <div class="row g-0">
            <!-- Left Side - Image -->
            <div class="col-md-6 d-none d-md-block">
                <div class="login-image">
                    <div>
                        <h2>Siap Untuk Petualang?</h2>
                        <p>Masuk untuk melihat Status penyewaan alat outdoor anda</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="col-md-6">
                <div class="login-form">
                    <h3>Selamat Datang</h3>
                    <p class="text-muted mb-4">Silakan masuk untuk melakukan penyewaan alat outdoor Anda.</p>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="masukan email anda" required>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-semibold">Password</label>
                                <a href="#" class="forgot-password">Lupa Password?</a>
                            </div>
                            <input type="password" name="password" class="form-control" placeholder="masukan Password anda" required>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-login w-100 text-white">
                            Masuk
                        </button>
                    </form>
                    
                    <div class="divider">
                        <span>Atau masuk dengan</span>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <button class="btn btn-social">
                                <i class="fab fa-google text-danger"></i> Google
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-social">
                                <i class="fab fa-facebook text-primary"></i> Facebook
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">Belum punya akun? <a href="register.php" class="register-link">Daftar Sekarang</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>