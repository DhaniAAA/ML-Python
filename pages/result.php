<?php
// Load konfigurasi
require_once '../includes/config.php';

// Cek apakah ada hasil analisis di session
if (!isset($_SESSION['analysis_result'])) {
    header('Location: analyze.php');
    exit;
}

$result = $_SESSION['analysis_result'];

// Inisialisasi visualisasi
$visualization = new Visualization();

// Warna untuk sentimen
$sentimentColors = [
    'positive' => 'success',
    'negative' => 'danger',
    'neutral' => 'warning'
];

// Terjemahan sentimen
$sentimentTranslations = [
    'positive' => 'Positif',
    'negative' => 'Negatif',
    'neutral' => 'Netral'
];

// Persiapkan data untuk wordcloud
$wordCloudData = $visualization->generateWordCloudData($result['word_sentiment']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Analisis - Analisis Sentimen</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqcloud/1.0.4/jqcloud.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Analisis Sentimen</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="analyze.php">Analisis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">Tentang</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Hasil Analisis Sentimen</h2>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Teks yang Dianalisis</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo htmlspecialchars($result['text']); ?></p>
                <hr>
                <p class="mb-0"><strong>Setelah Preprocessing:</strong> <span class="text-muted"><?php echo htmlspecialchars($result['processed_text']); ?></span></p>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Hasil Sentimen</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="display-4 text-<?php echo $sentimentColors[$result['sentiment']]; ?>">
                                <?php echo $sentimentTranslations[$result['sentiment']]; ?>
                            </div>
                        </div>
                        
                        <h6>Probabilitas per Kelas:</h6>
                        <div class="progress-group mb-3">
                            <div class="progress-label d-flex justify-content-between mb-1">
                                <span>Positif</span>
                                <span><?php echo round($result['probabilities']['positive'] * 100, 2); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $result['probabilities']['positive'] * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="progress-group mb-3">
                            <div class="progress-label d-flex justify-content-between mb-1">
                                <span>Negatif</span>
                                <span><?php echo round($result['probabilities']['negative'] * 100, 2); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: <?php echo $result['probabilities']['negative'] * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="progress-group">
                            <div class="progress-label d-flex justify-content-between mb-1">
                                <span>Netral</span>
                                <span><?php echo round($result['probabilities']['neutral'] * 100, 2); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar"