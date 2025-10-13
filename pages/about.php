<?php
// Inisialisasi session jika diperlukan
session_start();

// Load konfigurasi dasar
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang - Analisis Sentimen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- <link href="../assets/css/style.css" rel="stylesheet"> -->
    <link href="../assets/css/style_dataset_view.css" rel="stylesheet">
    <link href="../assets/css/navbar.css" rel="stylesheet">
</head>
<body>
    <?php include('../includes/nav_template.php'); ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-info-circle-fill me-2"></i>Tentang Analisis Sentimen</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-3">Apa itu Analisis Sentimen?</h4>
                        <p class="lead">
                            Analisis sentimen adalah proses menganalisis teks digital untuk menentukan apakah emosi atau perasaan yang terkandung di dalamnya positif, negatif, atau netral.
                        </p>
                        
                        <div class="row mt-4">
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-success mb-3">
                                            <i class="bi bi-emoji-smile"></i>
                                        </div>
                                        <h5 class="card-title">Sentimen Positif</h5>
                                        <p class="card-text">Mengekspresikan perasaan bahagia, puas, atau setuju</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-warning mb-3">
                                            <i class="bi bi-emoji-neutral"></i>
                                        </div>
                                        <h5 class="card-title">Sentimen Netral</h5>
                                        <p class="card-text">Mengekspresikan pernyataan faktual atau tidak memiliki muatan emosi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-danger mb-3">
                                            <i class="bi bi-emoji-frown"></i>
                                        </div>
                                        <h5 class="card-title">Sentimen Negatif</h5>
                                        <p class="card-text">Mengekspresikan perasaan tidak senang, kecewa, atau tidak setuju</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- <h4 class="mb-3 mt-4">Manfaat Analisis Sentimen</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush mb-4">
                                    <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Memahami opini publik tentang suatu topik</li>
                                    <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Memantau reputasi brand di media sosial</li>
                                    <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Analisis ulasan produk atau layanan</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush mb-4">
                                    <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Meningkatkan pengalaman pelanggan</li>
                                    <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Riset pasar dan analisis kompetitor</li>
                                    <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i>Deteksi isu yang berkembang di masyarakat</li>
                                </ul>
                            </div>
                        </div> -->
                        
                        <h4 class="mb-3 mt-4">Tentang Aplikasi</h4>
                        <p>
                            Aplikasi Analisis Sentimen ini dirancang untuk menganalisis teks dalam Bahasa Indonesia menggunakan algoritma Naive Bayes. 
                            Aplikasi ini dapat memproses teks dari media sosial, ulasan produk, komentar, atau sumber teks lainnya untuk 
                            mengategorikan sentimen setiap kalimat sebagai positif, negatif, atau netral.
                        </p>
                        <p>
                            Teknologi yang digunakan dalam aplikasi ini meliputi:
                        </p>
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-code-slash me-2"></i>Preprocessing</h6>
                                        <p class="card-text small">Convert Emoji ke Text, Case Folding, Tokenisasi, Stopword Removal, Stemming (Sastrawi), dan Normalisasi teks</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-cpu me-2"></i>Model Machine Learning</h6>
                                        <p class="card-text small">Naive Bayes Classifier dan Count Vectorizer</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-bar-chart me-2"></i>Visualisasi</h6>
                                        <p class="card-text small">Visualisasi Sentimen dengan Diagram Batang dan Wordcloud</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tata Cara Penggunaan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-book-fill me-2"></i>Tata Cara Penggunaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-1-circle me-2 text-primary"></i>Training Model</h5>
                                        <div class="steps">
                                            <div class="step">
                                                <div class="step-number"><strong>1</strong></div>
                                                <div class="step-content">
                                                    <p>Buka halaman <strong>Training</strong> dan upload file dataset dalam format CSV.</p>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number"><strong>2</strong></div>
                                                <div class="step-content">
                                                    <p>Atur jumlah data maksimum yang akan diproses dan klik tombol <strong>Mulai Training</strong>.</p>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number"><strong>3</strong></div>
                                                <div class="step-content">
                                                    <p>Tunggu proses training selesai. Model akan tersimpan otomatis dan siap digunakan.</p>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number"><strong>4</strong></div>
                                                <div class="step-content">
                                                    <p>Lihat detail dataset yang telah diupload dengan mengklik tombol <strong>Detail</strong>.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-2-circle me-2 text-primary"></i>Melihat Hasil Analisis dan Training</h5>
                                        <div class="steps">
                                            <div class="step">
                                                <div class="step-number"><strong>1</strong></div>
                                                <div class="step-content">
                                                    <p>Scroll <strong>Kebawah</strong> dan klik tombol <strong>Detail</strong>.</p>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number"><strong>2</strong></div>
                                                <div class="step-content">
                                                    <p>Tunggu Beberapa Detik <strong>User akan beralih ke halaman Lain</strong> dan hasil akan ditampilkan.</p>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number"><strong>3</strong></div>
                                                <div class="step-content">
                                                    <p>Hasil dari tranning dataset akan ditampilkan berupa <strong>diagram batang dan wordcloud.</strong></p>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number"><strong>4</strong></div>
                                                <div class="step-content">
                                                    <p>User juga akan mendapatkan hasil <strong>Akurasi</strong> model yang digunakan.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-lightbulb-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Tips Penggunaan</h5>
                                    <p class="mb-0">
                                        Untuk hasil terbaik, gunakan dataset yang cukup besar dan semuanya akan diproses secara otomatis, pengguna hanya tinggal menuggu hasil akhirnya.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informasi Pengembang -->
                <!-- <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-person-fill me-2"></i>Informasi Pengembang</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center mb-4">
                            <div class="col-md-3 text-center">
                                <img src="../assets/img/21876.jpg" alt="Foto Pengembang" class="img-fluid rounded-circle mb-3" style="max-width: 150px; height: auto;">
                            </div>
                            <div class="col-md-9">
                                <h4>Ramadhani</h4>
                                <p class="text-muted mb-2">Mahasiswa Teknik Informatika</p>
                                <p>Aplikasi ini dikembangkan sebagai bagian dari tugas akhir/skripsi di Universitas Islam Syekh Yusuf".</p>
                                <div class="mt-3">
                                    <a href="mailto:Ramadhanigb19@gmail.com" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-envelope me-1"></i>Kontak</a>
                                    <a href="https://github.com/DhaniAAA" target="_blank" class="btn btn-sm btn-outline-dark me-2"><i class="bi bi-github me-1"></i>GitHub</a>
                                    <a href="https://linkedin.com/in/username" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-linkedin me-1"></i>LinkedIn</a>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Ucapan Terima Kasih</h5>
                        <p>
                            Terima kasih kepada semua pihak yang telah berkontribusi dalam pengembangan aplikasi ini:
                        </p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><i class="bi bi-award me-2 text-warning"></i>Dosen Pembimbing: <strong>Taufik Hidayat, S.Kom., M.Kom., MCF.</strong> <span>dan</span> <strong>Sukisno, S.Kom., M.Kom., MCF.</strong></li>
                            <li class="list-group-item"><i class="bi bi-award me-2 text-warning"></i>Institusi: <strong>Universitas Islam Syekh Yusuf</strong></li>
                            <li class="list-group-item"><i class="bi bi-award me-2 text-warning"></i>Sumber dataset: <strong>Media Sosial X</strong></li>
                            <li class="list-group-item"><i class="bi bi-award me-2 text-warning"></i>Sastrawi untuk stemming bahasa Indonesia</li>
                        </ul>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <small class="text-muted">© <?php echo date('Y'); ?> Analisis Sentimen - Aplikasi Pendeteksi Sentimen Indonesia</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html> 