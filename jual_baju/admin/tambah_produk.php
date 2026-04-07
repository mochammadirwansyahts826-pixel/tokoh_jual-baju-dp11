<?php
// admin/tambah_produk.php - Tambah Produk Baru

include '../koneksi.php';

 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = clean_input($_POST['nama_produk']);
    $harga = clean_input($_POST['harga']);
    $stok = clean_input($_POST['stok']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    // Validasi
    if (empty($nama_produk)) {
        $errors['nama_produk'] = 'Nama produk harus diisi!';
    }
    
    if (empty($harga) || !is_numeric($harga) || $harga <= 0) {
        $errors['harga'] = 'Harga harus berupa angka positif!';
    }
    
    if (!is_numeric($stok) || $stok < 0) {
        $errors['stok'] = 'Stok harus berupa angka non-negatif!';
    }
    
    // Handle upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['gambar']['type'];
        $file_size = $_FILES['gambar']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['gambar'] = 'Format file harus JPG, PNG, atau GIF!';
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB max
            $errors['gambar'] = 'Ukuran file maksimal 2MB!';
        } else {
            // Generate nama file unik
            $extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $gambar = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $upload_path = '../uploads/' . $gambar;
            
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                $errors['gambar'] = 'Gagal mengupload gambar!';
            }
        }
    }
    
    // Simpan ke database
    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO products (nama_produk, harga, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sdiss", $nama_produk, $harga, $stok, $deskripsi, $gambar);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Produk berhasil ditambahkan!'
            ];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - TokoBaju</title>
    <link rel="stylesheet" href="../css/file.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Tambah Produk Baru</h1>
            <p>Isi form di bawah untuk menambah produk</p>
        </div>
        
        <div class="dashboard-content">
            <div class="form-container">
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger" style="position: static; animation: none;">
                    <?php echo $errors['general']; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk *</label>
                        <input type="text" id="nama_produk" name="nama_produk" placeholder="Contoh: Kaos Polos Hitam" value="<?php echo htmlspecialchars($nama_produk ?? ''); ?>" required>
                        <?php if (isset($errors['nama_produk'])): ?>
                        <small class="error"><?php echo $errors['nama_produk']; ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="harga">Harga (Rp) *</label>
                            <input type="number" id="harga" name="harga" placeholder="85000" min="0" value="<?php echo htmlspecialchars($harga ?? ''); ?>" required>
                            <?php if (isset($errors['harga'])): ?>
                            <small class="error"><?php echo $errors['harga']; ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="stok">Stok *</label>
                            <input type="number" id="stok" name="stok" placeholder="10" min="0" value="<?php echo htmlspecialchars($stok ?? '0'); ?>" required>
                            <?php if (isset($errors['stok'])): ?>
                            <small class="error"><?php echo $errors['stok']; ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Deskripsi produk..."><?php echo htmlspecialchars($deskripsi ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Gambar Produk</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-label" id="fileLabel">
                                📷 Klik untuk pilih gambar<br>
                                <small>Format: JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                            <input type="file" name="gambar" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <?php if (isset($errors['gambar'])): ?>
                        <small class="error"><?php echo $errors['gambar']; ?></small>
                        <?php endif; ?>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                    <a href="dashboard.php" class="btn btn-secondary" style="display: inline-block; margin-left: 0.5rem;">Batal</a>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function previewImage(input) {
        var preview = document.getElementById('imagePreview');
        var label = document.getElementById('fileLabel');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                label.innerHTML = '✅ Gambar dipilih';
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>