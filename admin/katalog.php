<?php
// Hubungkan ke file koneksi di folder luar (root)
include('../config/koneksi.php');

if (isset($koneksi) && !isset($conn)) {
    $conn = $koneksi;
}
// ==========================================
// 1. AKSI TAMBAH DATA (SIMPAN)
// ==========================================
if (isset($_POST['simpan'])) {
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $stok        = mysqli_real_escape_string($conn, $_POST['stok']);
    $harga_sewa  = mysqli_real_escape_string($conn, $_POST['harga_sewa']);
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Proses Upload Gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];
    
    if(!empty($gambar)) {
        // Ambil ekstensi & rename file unik untuk menghindari nama kembar
        $ekstensi = pathinfo($gambar, PATHINFO_EXTENSION);
        $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;
        $path = "../assets/img/katalog/" . $nama_gambar_baru;
        
        if(move_uploaded_file($tmp, $path)) {
            $gambar_db = $nama_gambar_baru;
        } else {
            $gambar_db = 'default.png';
        }
    } else {
        $gambar_db = 'default.png';
    }

    // Insert ke tabel produk asli Anda
    $query = "INSERT INTO produk (nama_produk, kategori_id, deskripsi, harga_sewa, stok, gambar, status) 
              VALUES ('$nama_produk', '$kategori_id', '$deskripsi', '$harga_sewa', '$stok', '$gambar_db', 'Tersedia')";
              
    if(mysqli_query($conn, $query)) {
        header("Location: katalog.php");
        exit;
    } else {
        echo "<script>alert('Gagal menambah data: " . mysqli_error($conn) . "');</script>";
    }
}

// ==========================================
// 2. AKSI EDIT DATA (UPDATE)
// ==========================================
if (isset($_POST['update'])) {
    $id          = mysqli_real_escape_string($conn, $_POST['id']);
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
    $stok        = mysqli_real_escape_string($conn, $_POST['stok']);
    $harga_sewa  = mysqli_real_escape_string($conn, $_POST['harga_sewa']);
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];
    
    if(!empty($gambar)) {
        $ekstensi = pathinfo($gambar, PATHINFO_EXTENSION);
        $nama_gambar_baru = time() . '_' . rand(100, 999) . '.' . $ekstensi;
        $path = "../assets/img/katalog/" . $nama_gambar_baru;
        
        if(move_uploaded_file($tmp, $path)) {
            $query = "UPDATE produk SET nama_produk='$nama_produk', kategori_id='$kategori_id', stok='$stok', harga_sewa='$harga_sewa', deskripsi='$deskripsi', gambar='$nama_gambar_baru' WHERE id='$id'";
        }
    } else {
        $query = "UPDATE produk SET nama_produk='$nama_produk', kategori_id='$kategori_id', stok='$stok', harga_sewa='$harga_sewa', deskripsi='$deskripsi' WHERE id='$id'";
    }
    
    if(mysqli_query($conn, $query)) {
        header("Location: katalog.php");
        exit;
    }
}

// ==========================================
// 3. AKSI HAPUS DATA
// ==========================================
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM produk WHERE id = '$id_hapus'");
    header("Location: katalog.php");
    exit;
}

// ==========================================
// 4. PENCARIAN & FILTER KATEGORI
// ==========================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter = isset($_GET['kategori_id']) ? mysqli_real_escape_string($conn, $_GET['kategori_id']) : '';

$where_clause = "WHERE 1=1";
if ($search != '') {
    $where_clause .= " AND (p.nama_produk LIKE '%$search%' OR p.id LIKE '%$search%')";
}
if ($filter != '') {
    $where_clause .= " AND p.kategori_id = '$filter'";
}

// Ambil Data Produk dengan JOIN Kategori
$query_tampil = "SELECT p.*, k.nama_kategori 
                 FROM produk p 
                 LEFT JOIN kategori k ON p.kategori_id = k.id 
                 $where_clause ORDER BY p.id DESC";
$result = mysqli_query($conn, $query_tampil);

// Ambil Semua Kategori untuk Dropdown
$list_kategori = mysqli_query($conn, "SELECT * FROM kategori");
$kategori_options = [];
while($k = mysqli_fetch_assoc($list_kategori)) {
    $kategori_options[] = $k;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Katalog - SIMRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .main-content { margin-left: 280px; padding: 30px; min-height: 100vh; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-maroon { background-color: #990000; color: white; border-radius: 8px; font-weight: 600; }
        .btn-maroon:hover { background-color: #770000; color: white; }
        .table-container { background: #f0f4fa; border: 2px solid #990000; border-radius: 16px; padding: 20px; }
        .product-img { width: 55px; height: 55px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd; }
        .badge-ready { background-color: #ffe8cc; color: #e67e22; font-weight: 600; }
        .text-maroon { color: #990000; font-weight: 700; }
    </style>
</head>
<body>

    <!-- Memanggil Sidebar Terpisah -->
    <?php include('include/sidebar.php'); ?>

    <!-- Area Konten Utama -->
    <div class="main-content">
        <div class="header-section">
            <h4 class="fw-bold text-dark">Kelola Katalog Alat</h4>
            <div class="d-flex align-items-center">
                <span class="me-2 text-end"><strong>Admin SIMRO</strong></span>
                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;"><i class="fas fa-user"></i></div>
            </div>
        </div>

        <!-- Filter & Tombol Tambah -->
        <div class="d-flex justify-content-between mb-4">
            <form method="GET" action="" class="d-flex w-50 gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama alat..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select name="kategori_id" class="form-select w-50" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach($kategori_options as $kat) { ?>
                        <option value="<?php echo $kat['id']; ?>" <?php if($filter == $kat['id']) echo 'selected'; ?>><?php echo $kat['nama_kategori']; ?></option>
                    <?php } ?>
                </select>
            </form>
            <button class="btn btn-maroon px-4" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus me-2"></i> Tambah Alat</button>
        </div>

        <!-- Tabel Data -->
        <div class="table-container shadow-sm">
            <table class="table table-borderless align-middle bg-transparent m-0">
                <thead>
                    <tr style="border-bottom: 2px solid #dee2e6;">
                        <th>Alat & Detail</th>
                        <th>Harga / Hari</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) { 
                    ?>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="../assets/img/katalog/<?php echo $row['gambar']; ?>" class="product-img me-3" onerror="this.src='https://placehold.co/60'">
                                <div>
                                    <div class="fw-bold m-0 text-dark"><?php echo $row['nama_produk']; ?></div>
                                    <span class="badge bg-primary-subtle text-primary" style="font-size:0.75rem;"><?php echo $row['nama_kategori'] ?? 'Umum'; ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-maroon">Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></td>
                        <td><small class="text-muted"><?php echo substr($row['deskripsi'], 0, 60); ?>...</small></td>
                        <td class="text-center fw-bold"><span class="badge badge-ready px-3 py-2 rounded-pill"><?php echo $row['stok']; ?> Unit</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-link text-muted me-2" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id']; ?>"><i class="fas fa-pencil-alt fs-5"></i></button>
                            <button class="btn btn-sm btn-link text-muted" data-bs-toggle="modal" data-bs-target="#modalHapus<?php echo $row['id']; ?>"><i class="fas fa-trash-alt fs-5"></i></button>
                        </td>
                    </tr>

                    <!-- MODAL EDIT DATA -->
                    <div class="modal fade" id="modalEdit<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content p-3">
                                <div class="modal-header border-0"><h5 class="modal-title fw-bold text-maroon text-center w-100">EDIT STOK ALAT</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">KATEGORI ALAT</label>
                                                <select name="kategori_id" class="form-select" required>
                                                    <?php foreach($kategori_options as $kat) { ?>
                                                        <option value="<?php echo $kat['id']; ?>" <?php echo ($row['kategori_id'] == $kat['id'])?'selected':''; ?>><?php echo $kat['nama_kategori']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-12"><label class="form-label small fw-bold">NAMA ALAT</label><input type="text" name="nama_produk" class="form-control" value="<?php echo $row['nama_produk']; ?>" required></div>
                                            <div class="col-12"><label class="form-label small fw-bold">DESKRIPSI</label><textarea name="deskripsi" class="form-control" rows="2"><?php echo $row['deskripsi']; ?></textarea></div>
                                            <div class="col-6"><label class="form-label small fw-bold">TOTAL STOK</label><input type="number" name="stok" class="form-control" value="<?php echo $row['stok']; ?>" required></div>
                                            <div class="col-6"><label class="form-label small fw-bold">HARGA SEWA</label><input type="number" name="harga_sewa" class="form-control" value="<?php echo $row['harga_sewa']; ?>" required></div>
                                            <div class="col-12"><label class="form-label small fw-bold">GANTI GAMBAR (KOSONGKAN JIKA TIDAK UBAH)</label><input type="file" name="gambar" class="form-control"></div>
                                        </div>
                                    </div>
                                    <div class="text-center px-3 pb-3"><button type="submit" name="update" class="btn btn-maroon w-100 py-2">Simpan Perubahan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL HAPUS -->
                    <div class="modal fade" id="modalHapus<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content text-center p-4">
                                <h5 class="fw-bold mb-2">Hapus Alat?</h5>
                                <p class="text-muted small">Yakin ingin menghapus <strong><?php echo $row['nama_produk']; ?></strong>?</p>
                                <div class="d-flex gap-2 justify-content-center mt-3">
                                    <button type="button" class="btn btn-light w-50" data-bs-dismiss="modal">Batal</button>
                                    <a href="katalog.php?hapus=<?php echo $row['id']; ?>" class="btn btn-maroon w-50">Hapus</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Tidak ada produk ditemukan. Pastikan tabel kategori terisi.</td></tr>";
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH DATA BARU -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header border-0"><h5 class="modal-title fw-bold text-maroon text-center w-100">TAMBAH ALAT BARU</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">KATEGORI ALAT</label>
                                <select name="kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach($kategori_options as $kat) { ?>
                                        <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12"><label class="form-label small fw-bold">NAMA ALAT</label><input type="text" name="nama_produk" class="form-control" required></div>
                            <div class="col-12"><label class="form-label small fw-bold">DESKRIPSI</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
                            <div class="col-6"><label class="form-label small fw-bold">TOTAL STOK</label><input type="number" name="stok" class="form-control" required></div>
                            <div class="col-6"><label class="form-label small fw-bold">HARGA SEWA / HARI</label><input type="number" name="harga_sewa" class="form-control" required></div>
                            <div class="col-12"><label class="form-label small fw-bold">UPLOAD GAMBAR</label><input type="file" name="gambar" class="form-control" required></div>
                        </div>
                    </div>
                    <div class="text-center px-3 pb-3"><button type="submit" name="simpan" class="btn btn-maroon w-100 py-2">Simpan Alat Baru</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>