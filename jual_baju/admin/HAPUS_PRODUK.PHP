<?php
// admin/hapus_produk.php - Hapus Produk
require_once '../koneksi.php';

 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Ambil data gambar
    $stmt = mysqli_prepare($koneksi, "SELECT gambar FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    if ($product) {
        // Hapus gambar dari folder
        if ($product['gambar'] && file_exists('../uploads/' . $product['gambar'])) {
            unlink('../uploads/' . $product['gambar']);
        }
        
        // Hapus dari database
        $stmt = mysqli_prepare($koneksi, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Produk berhasil dihapus!'
            ];
        } else {
            $_SESSION['alert'] = [
                'type' => 'danger',
                'message' => 'Gagal menghapus produk!'
            ];
        }
    }
}

header('Location: dashboard.php');
exit;
?>