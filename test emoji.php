<!-- uji-emoji.php -->
<form method="post">
    <label>Masukkan Pesan (boleh pakai emoji):</label><br>
    <textarea name="pesan" rows="4" cols="40"></textarea><br>
    <button type="submit">Simpan</button>
</form>

<?php
require 'config.php'; // file koneksi kamu

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pesan = $_POST['pesan'];
    $stmt = mysqli_prepare($conn, "INSERT INTO komentar (pesan) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $pesan);
    mysqli_stmt_execute($stmt);
    echo "Pesan berhasil disimpan!<br><br>";
}

// Tampilkan data yang tersimpan
$result = mysqli_query($conn, "SELECT pesan FROM komentar ORDER BY id DESC LIMIT 5");

echo "<h3>Data Terakhir:</h3>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<p>" . htmlspecialchars($row['pesan']) . "</p>";
}
?>
