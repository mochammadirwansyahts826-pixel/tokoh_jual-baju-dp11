<?php
// admin/dashboard.php - Dashboard Admin
// require_once '../includes/auth.php';
// require_admin();
require_once '../koneksi.php';

// Hitung statistik
// Total produk
 $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM products");
 $total_produk = mysqli_fetch_assoc($result)['total'];

// Total stok
 $result = mysqli_query($koneksi, "SELECT SUM(stok) as total FROM products");
 $total_stok = mysqli_fetch_assoc($result)['total'] ?? 0;

// Total pesanan
 $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM orders");
 $total_pesanan = mysqli_fetch_assoc($result)['total'];

// Total user
 $result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
 $total_user = mysqli_fetch_assoc($result)['total'];

// Ambil semua produk
 $products = mysqli_query($koneksi, "SELECT * FROM products ORDER BY created_at DESC");

// Ambil pesanan terbaru
 $orders = mysqli_query($koneksi, "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.tanggal DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - TokoBaju</title>
    <link rel="stylesheet" href="../css/file.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard Admin</h1>
            <p>Kelola produk dan pesanan Anda</p>
        </div>
        
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $total_produk; ?></h3>
                        <p>Total Produk</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo $total_stok; ?></h3>
                        <p>Total Stok</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3><?php echo $total_pesanan; ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $total_user; ?></h3>
                        <p>Total User</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mb-3">
                <a href="tambah_produk.PHP" class="btn btn-primary" style="width: auto;">+ Tambah Produk Baru</a>
            </div>
            
            <!-- Products Table -->
            <h2 class="mb-2">Daftar Produk</h2>
            <div class="table-container mb-3">
                <table>
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>
                                <?php if ($product['gambar']): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($product['gambar']); ?>" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>" class="table-image">
                                <?php else: ?>
                                <div class="table-image" style="background: #16213e; display: flex; align-items: center; justify-content: center;">📷</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                            <td><?php echo format_rupiah($product['harga']); ?></td>
                            <td>
                                <?php 
                                if ($product['stok'] <= 0) {
                                    echo '<span style="color: #dc3545;">Habis</span>';
                                } elseif ($product['stok'] < 10) {
                                    echo '<span style="color: #ffc107;">' . $product['stok'] . '</span>';
                                } else {
                                    echo $product['stok'];
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="hapus_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($products) == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada produk</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Orders Table -->
            <h2 class="mb-2">Pesanan Terbaru</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo format_rupiah($order['total']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['tanggal'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($orders) == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada pesanan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2024 TokoBaju. Tugas Pemrograman Web.</p>
    </footer>
</body>
</html>