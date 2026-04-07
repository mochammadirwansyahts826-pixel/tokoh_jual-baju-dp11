<?php
// koneksi.php - File koneksi database

 $host = 'localhost';
 $username = 'root';
 $password = '';
 $database = 'jualan_baju';

// Koneksi ke database
 $koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($koneksi, "utf8");

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, htmlspecialchars(strip_tags(trim($data))));
}

// Fungsi untuk format rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk menampilkan pesan alert
function set_alert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

function show_alert() {
    if (isset($_SESSION['alert'])) {
        $type = $_SESSION['alert']['type'];
        $message = $_SESSION['alert']['message'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('$type', '$message');
            });
        </script>";
        unset($_SESSION['alert']);
    }
}
?>