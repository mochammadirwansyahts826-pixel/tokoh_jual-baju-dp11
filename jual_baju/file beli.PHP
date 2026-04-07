<?php
// checkout.php - Proses Checkout
session_start();
require_once 'koneksi.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'message' => 'Silakan login terlebih dahulu!'
    ];
    header('Location: index.php');
    exit;
}

// Redirect jika keranjang kosong
if (empty($_SESSION['cart'])) {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'message' => 'Keranjang Anda kosong!'
    ];
    header('Location: cart.php');
    exit;
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $total = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['harga'] * $item['quantity'];
    }
    
    // Mulai transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        // Insert ke tabel orders
        $stmt = mysqli_prepare($koneksi, "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
        mysqli_stmt_bind_param($stmt, "id", $user_id, $total);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($koneksi);
        
        // Insert ke tabel order_items dan update stok
        foreach ($_SESSION['cart'] as $item) {
            // Insert order item
            $stmt = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, jumlah, harga) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item['id'], $item['quantity'], $item['harga']);
            mysqli_stmt_execute($stmt);
            
            // Update stok
            $stmt = mysqli_prepare($koneksi, "UPDATE products SET stok = stok - ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['id']);
            mysqli_stmt_execute($stmt);
        }
        
        // Commit transaksi
        mysqli_commit($koneksi);
        
        // Kosongkan keranjang
        $_SESSION['cart'] = [];
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Pesanan berhasil dibuat! Order #' . $order_id
        ];
        
        header('Location: user/dashboard.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback jika gagal
        mysqli_rollback($koneksi);
        
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Terjadi kesalahan. Silakan coba lagi.'
        ];
        header('Location: cart.php');
        exit;
    }
}

// Hitung total untuk tampilan
 $total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['harga'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TokoBaju</title>
    <link rel="stylesheet" href="css/file.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Checkout</h1>
            <p>Konfirmasi pesanan Anda</p>
        </div>
        
        <div class="dashboard-content">
            <div class="cart-container">
                <div class="cart-items">
                    <h3>Detail Pesanan</h3>
                    
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if ($item['gambar']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                            <?php else: ?>
                            <span>📷</span>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-info">
                            <h4><?php echo htmlspecialchars($item['nama']); ?></h4>
                            <p class="cart-item-price"><?php echo format_rupiah($item['harga']); ?> x <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="cart-item-total">
                            <p><?php echo format_rupiah($item['harga'] * $item['quantity']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Ringkasan Pembayaran</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo format_rupiah($total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span>Gratis</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Bayar</span>
                        <span><?php echo format_rupiah($total); ?></span>
                    </div>
                    
                    <form method="POST" action="">
                        <button type="submit" class="btn btn-primary mt-2" style="width: 100%;">
                            Konfirmasi Pesanan
                        </button>
                    </form>
                    
                    <a href="cart.php" class="btn btn-secondary mt-1" style="display: block; text-align: center;">
                        Kembali ke Keranjang
                    </a>
                    
                    <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(0, 210, 106, 0.1); border-radius: 12px; border: 1px solid rgba(0, 210, 106, 0.3);">
                        <p style="font-size: 0.9rem; color: #00d26a;">
                            ✅ Pembayaran akan diproses setelah pesanan dikonfirmasi
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2024 TokoBaju. Tugas Pemrograman Web.</p>
    </footer>
</body>
</html>