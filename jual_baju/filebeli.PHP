<?php
// cart.php - Keranjang Belanja
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

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $product_id = (int)$_POST['product_id'];
            $quantity = max(1, (int)$_POST['quantity']);
            
            // Cek produk
            $stmt = mysqli_prepare($koneksi, "SELECT * FROM products WHERE id = ? AND stok > 0");
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($result);
            
            if ($product) {
                // Cek stok
                $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
                $new_qty = min($current_qty + $quantity, $product['stok']);
                
                $_SESSION['cart'][$product_id] = [
                    'id' => $product['id'],
                    'nama' => $product['nama_produk'],
                    'harga' => $product['harga'],
                    'gambar' => $product['gambar'],
                    'quantity' => $new_qty
                ];
                
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'message' => 'Produk ditambahkan ke keranjang!'
                ];
            }
            
            // Redirect back
            $referer = $_SERVER['HTTP_REFERER'] ?? 'user/dashboard.php';
            header('Location: ' . $referer);
            exit;
            break;
            
        case 'update':
            $product_id = (int)$_POST['product_id'];
            $quantity = max(0, (int)$_POST['quantity']);
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                // Cek stok
                $stmt = mysqli_prepare($koneksi, "SELECT stok FROM products WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $product_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);
                
                if ($product && isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] = min($quantity, $product['stok']);
                }
            }
            
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Keranjang diupdate!'
            ];
            header('Location: cart.php');
            exit;
            break;
            
        case 'remove':
            $product_id = (int)$_POST['product_id'];
            unset($_SESSION['cart'][$product_id]);
            
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Produk dihapus dari keranjang!'
            ];
            header('Location: cart.php');
            exit;
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Keranjang dikosongkan!'
            ];
            header('Location: cart.php');
            exit;
            break;
    }
}

// Hitung total
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
    <title>Keranjang Belanja - TokoBaju</title>
    <link rel="stylesheet" href="css/file.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Keranjang Belanja</h1>
            <p>Review pesanan Anda sebelum checkout</p>
        </div>
        
        <div class="dashboard-content">
            <?php if (empty($_SESSION['cart'])): ?>
            <div class="cart-empty">
                <p>Keranjang Anda kosong</p>
                <a href="user/dashboard.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
            <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="d-flex justify-between align-center mb-2">
                        <h3>Daftar Produk</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Kosongkan keranjang?')">Kosongkan</button>
                        </form>
                    </div>
                    
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
                            <p class="cart-item-price"><?php echo format_rupiah($item['harga']); ?></p>
                        </div>
                        <div class="cart-item-quantity">
                            <form method="POST" action="" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="qty-btn">-</button>
                                <span style="min-width: 30px; text-align: center;"><?php echo $item['quantity']; ?></span>
                                <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn">+</button>
                            </form>
                        </div>
                        <div class="cart-item-total">
                            <p><?php echo format_rupiah($item['harga'] * $item['quantity']); ?></p>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="margin-top: 0.5rem;">Hapus</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo format_rupiah($total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span>Gratis</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?php echo format_rupiah($total); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary mt-2" style="display: block; text-align: center;">
                        Checkout Sekarang
                    </a>
                    <a href="user/dashboard.php" class="btn btn-secondary mt-1" style="display: block; text-align: center;">
                        Lanjut Belanja
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2024 TokoBaju. Tugas Pemrograman Web.</p>
    </footer>
</body>
</html>