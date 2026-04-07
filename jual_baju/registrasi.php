<?php
// register.php - Halaman Registrasi
session_start();
require_once 'koneksi.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit;
}

 $errors = [];
 $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($username)) {
        $errors['username'] = 'Username harus diisi!';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username minimal 4 karakter!';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi!';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter!';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Password tidak cocok!';
    }
    
    // Cek username sudah ada atau belum
    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $errors['username'] = 'Username sudah digunakan!';
        } else {
            // Hash password dan simpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'message' => 'Registrasi berhasil! Silakan login.'
                ];
                header('Location: index.php');
                exit;
            } else {
                $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
            }
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
    <title>Registrasi - TokoBaju</title>
    <link rel="stylesheet" href="css/file.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Toko<span>Baju</span></h1>
            <p>Buat akun baru</p>
            
            <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" style="position: static; animation: none;">
                <?php echo $errors['general']; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Minimal 4 karakter" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    <?php if (isset($errors['username'])): ?>
                    <small class="error"><?php echo $errors['username']; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    <?php if (isset($errors['password'])): ?>
                    <small class="error"><?php echo $errors['password']; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                    <small class="error"><?php echo $errors['confirm_password']; ?></small>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Daftar</button>
            </form>
            
            <div class="auth-links">
                <p>Sudah punya akun? <a href="index.php">Masuk disini</a></p>
            </div>
        </div>
    </div>
</body>
</html>