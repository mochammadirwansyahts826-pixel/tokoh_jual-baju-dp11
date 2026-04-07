<?php
// includes/navbar.php - Komponen Navbar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan base path
 $base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false) {
    $base_path = '../';
}

 $cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="<?php echo $base_path; ?>index.php" class="navbar-brand">
            Toko<span>Baju</span>
        </a>
        
        <div class="navbar-toggle" onclick="toggleMenu(this)">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <ul class="navbar-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="<?php echo $base_path; ?>admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo $base_path; ?>admin/tambah_produk.php">Tambah Produk</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_path; ?>user/dashboard.php">Produk</a></li>
                    <li><a href="<?php echo $base_path; ?>cart.php">Keranjang <?php if ($cart_count > 0): ?>(<?php echo $cart_count; ?>)<?php endif; ?></a></li>
                <?php endif; ?>
                
                <li class="user-info">
                    <span>Hai, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="user-badge"><?php echo $_SESSION['role']; ?></span>
                </li>
                <li><a href="<?php echo $base_path; ?>logout.php" style="color: #e94560;">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base_path; ?>index.php">Login</a></li>
                <li><a href="<?php echo $base_path; ?>register.php">Daftar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
function toggleMenu(element) {
    element.classList.toggle('active');
    document.querySelector('.navbar-menu').classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.querySelector('.navbar-menu');
    const toggle = document.querySelector('.navbar-toggle');
    
    if (menu && toggle && !menu.contains(e.target) && !toggle.contains(e.target)) {
        menu.classList.remove('active');
        toggle.classList.remove('active');
    }
});

// Alert notification
function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-' + type;
    alert.textContent = message;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

<?php if (isset($_SESSION['alert'])): ?>
showAlert('<?php echo $_SESSION['alert']['type']; ?>', '<?php echo $_SESSION['alert']['message']; ?>');
<?php unset($_SESSION['alert']); ?>
<?php endif; ?>
</script>