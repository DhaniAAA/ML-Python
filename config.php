<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sentiment_analysis');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// PENTING: Aktifkan utf8mb4 agar emoji bisa disimpan
mysqli_set_charset($conn, "utf8mb4");
?>
