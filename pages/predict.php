<?php
// Load konfigurasi
require_once '../includes/config.php';
require_once '../lib/Preprocessing.php';
require_once '../models/SentimentModel.php';

// Inisialisasi variabel
$input_text = '';
$prediction = null;
$error = '';
$processing_time = 0;
$word_importance = [];
$preprocessed_text = '';

// Proses formulir jika dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['text_input'])) {
    try {
        $input_text = $_POST['text_input'];
        
        // Validasi input
        if (empty($input_text)) {
            throw new Exception('Silakan masukkan teks yang akan dianalisis.');
        }
        
        // Mulai hitung waktu
        $start_time = microtime(true);
        
        // Instantiasi model sentimen
        $model = new SentimentModel();
        
        // Preprocessing teks
        $preprocessor = new Preprocessing();
        $preprocessed_text = $preprocessor->processText($input_text);
        
        // Normalisasi spasi (hapus multiple spaces)
        $preprocessed_text = preg_replace('/\s+/', ' ', $preprocessed_text);
        $preprocessed_text = trim($preprocessed_text);
        
        // Analisis sentimen
        $prediction = $model->analyze($preprocessed_text);
        
        // Hitung waktu proses
        $end_time = microtime(true);
        $processing_time = ($end_time - $start_time) * 1000; // dalam milidetik
        
        // Dapatkan kata-kata penting yang mempengaruhi sentimen
        if (isset($prediction['word_scores']) && !empty($prediction['word_scores'])) {
            // Urutkan berdasarkan nilai absolut skor (positif atau negatif)
            uasort($prediction['word_scores'], function($a, $b) {
                return abs($b) <=> abs($a);
            });
            
            // Ambil 10 kata teratas
            $word_importance = array_slice($prediction['word_scores'], 0, 10, true);
        } elseif (isset($prediction['word_sentiment']) && !empty($prediction['word_sentiment'])) {
            // Alternatif: gunakan word_sentiment jika tersedia
            $word_scores = [];
            foreach ($prediction['word_sentiment'] as $word => $data) {
                if (isset($data['score'])) {
                    $word_scores[$word] = $data['score'];
                }
            }
            
            // Urutkan berdasarkan nilai absolut skor
            uasort($word_scores, function($a, $b) {
                return abs($b) <=> abs($a);
            });
            
            // Ambil 10 kata teratas
            $word_importance = array_slice($word_scores, 0, 10, true);
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

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

// Icon untuk sentimen
$sentimentIcons = [
    'positive' => 'emoji-smile',
    'negative' => 'emoji-frown',
    'neutral' => 'emoji-neutral'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Sentimen - Analisis Sentimen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/style_prediksi.css">
</head>
<body>
    <?php include('../includes/nav_template.php'); ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item active">Prediksi</li>
                    </ol>
                </nav>
                <h2 class="mt-2">Prediksi Sentimen</h2>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-chat-text"></i> Masukkan Teks</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="text_input" class="form-label">Teks yang akan dianalisis:</label>
                                <textarea class="form-control" id="text_input" name="text_input" rows="5" placeholder="Masukkan teks dalam bahasa Indonesia di sini..."><?php echo htmlspecialchars($input_text); ?></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Analisis Sentimen
                                </button>
                            </div>
                        </form>
                        
                        <?php if (!empty($preprocessed_text)): ?>
                        <div class="mt-4">
                            <h6><i class="bi bi-gear"></i> Hasil Prediksi:</h6>
                            <div class="text-processing">
                                <?php echo htmlspecialchars($preprocessed_text); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($prediction): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-bar-chart"></i> Skor Sentimen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach (['positive', 'neutral', 'negative'] as $sentiment): ?>
                            <div class="col-md-4">
                                <div class="card mb-2">
                                    <div class="card-body p-3">
                                        <h6 class="text-<?php echo $sentimentColors[$sentiment]; ?> mb-2">
                                            <i class="bi bi-<?php echo $sentimentIcons[$sentiment]; ?>"></i> 
                                            <?php echo $sentimentTranslations[$sentiment]; ?>
                                        </h6>
                                        
                                        <?php 
                                        $score = isset($prediction['probabilities'][$sentiment]) ? 
                                                 $prediction['probabilities'][$sentiment] * 100 : 0;
                                        ?>
                                        
                                        <div class="progress progress-bar-custom">
                                            <div class="progress-bar bg-<?php echo $sentimentColors[$sentiment]; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $score; ?>%" 
                                                 aria-valuenow="<?php echo $score; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-end mb-0 mt-1">
                                            <strong><?php echo number_format($score, 1); ?>%</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-light border mt-3 mb-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Waktu pemrosesan: <?php echo number_format($processing_time, 2); ?> ms</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-5">
                <?php if ($prediction): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-emoji-smile"></i> Hasil Prediksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="sentiment-card">
                            <?php
                            $predictedSentiment = $prediction['sentiment'];
                            $iconClass = $sentimentIcons[$predictedSentiment];
                            $colorClass = $sentimentColors[$predictedSentiment];
                            $sentimentText = $sentimentTranslations[$predictedSentiment];
                            ?>
                            
                            <div class="sentiment-icon text-<?php echo $colorClass; ?>">
                                <i class="bi bi-<?php echo $iconClass; ?>-fill"></i>
                            </div>
                            
                            <h4>Teks ini terdeteksi memiliki sentimen</h4>
                            <h2 class="text-<?php echo $colorClass; ?>"><?php echo $sentimentText; ?></h2>
                            
                            <div class="sentiment-score">
                                <?php
                                // Tentukan skor berdasarkan metode analisis
                                $score = 0;
                                if (isset($prediction['score'])) {
                                    $score = $prediction['score'];
                                } elseif (isset($prediction['probabilities'][$predictedSentiment])) {
                                    // Gunakan nilai probabilitas sebagai skor jika tidak ada skor langsung
                                    $score = $prediction['probabilities'][$predictedSentiment];
                                }
                                ?>
                                Skor: <span class="text-<?php echo $colorClass; ?>"><?php echo number_format($score, 3); ?></span>
                            </div>
                            
                            <div class="mt-4">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-secondary" onclick="copyResults()">
                                        <i class="bi bi-clipboard"></i> Salin Hasil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($word_importance)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-list-ol"></i> Kata-kata yang Mempengaruhi</h5>
                    </div>
                    <div class="card-body word-importance">
                        <?php foreach ($word_importance as $word => $score): 
                            $sentimentClass = 'neutral';
                            $scoreClass = 'neutral';
                            if ($score > 0) {
                                $sentimentClass = 'positive';
                                $scoreClass = 'positive';
                            } elseif ($score < 0) {
                                $sentimentClass = 'negative';
                                $scoreClass = 'negative';
                            }
                        ?>
                        <div class="word-item word-<?php echo $sentimentClass; ?>">
                            <span class="word-text"><?php echo htmlspecialchars($word); ?></span>
                            <span class="word-score score-<?php echo $scoreClass; ?>"><?php echo number_format($score, 3); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="bi bi-info-circle"></i> Tentang Prediksi Sentimen</h5>
                    </div>
                    <div class="card-body">
                        <!-- <div class="text-center mb-4">
                            <img src="../assets/img/sentiment_illustration.svg" alt="Sentimen Analisis" style="max-width: 200px;" onerror="this.src='https://via.placeholder.com/200x150?text=Sentimen+Analisis'">
                        </div> -->
                        <p>Analisis sentimen adalah proses mengidentifikasi dan mengekstraksi pendapat dalam teks. Fitur prediksi ini memungkinkan Anda untuk:</p>
                        <ul>
                            <li>Menganalisis sentimen dari teks bahasa Indonesia</li>
                            <li>Melihat skor untuk sentimen positif, negatif, dan netral</li>
                            <li>Mengidentifikasi kata-kata kunci yang mempengaruhi hasil analisis</li>
                        </ul>
                        <p>Masukkan teks yang ingin Anda analisis pada form di sebelah kiri untuk memulai!</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!$prediction): ?>
        <!-- <div class="row mt-4 g-4">
            <div class="col-lg-4">
                <div class="card feature-card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h5 class="card-title">Analisis Cepat</h5>
                        <p class="card-text">Dapatkan hasil analisis sentimen dalam hitungan milidetik dengan model machine learning yang dioptimalkan.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card feature-card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-translate"></i>
                        </div>
                        <h5 class="card-title">Khusus Bahasa Indonesia</h5>
                        <p class="card-text">Model kami dilatih khusus untuk memahami nuansa bahasa Indonesia, termasuk slang dan bahasa gaul.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card feature-card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <h5 class="card-title">Hasil Terperinci</h5>
                        <p class="card-text">Lihat skor probabilitas untuk setiap sentimen dan kata-kata yang paling mempengaruhi hasil analisis.</p>
                    </div>
                </div>
            </div>
        </div> -->
        <?php endif; ?>
    </div>

    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <small class="text-muted">© <?php echo date('Y'); ?> Analisis Sentimen - Aplikasi Pendeteksi Sentimen Indonesia</small>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function fallbackCopyTextToClipboard(text) {
                // Membuat elemen textarea sementara
                const textArea = document.createElement("textarea");
                textArea.value = text;
                
                // Pastikan tidak terlihat
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                
                document.body.appendChild(textArea);
                
                // Pilih dan salin isi textarea
                textArea.focus();
                textArea.select();
                
                let success = false;
                try {
                    success = document.execCommand('copy');
                    if (success) {
                        alert('Hasil analisis telah disalin ke clipboard!');
                    } else {
                        alert('Gagal menyalin hasil. Browser Anda mungkin tidak mendukung fitur ini.');
                    }
                } catch (err) {
                    alert('Gagal menyalin hasil: ' + err);
                }
                
                document.body.removeChild(textArea);
            }
            
            function copyResults() {
                <?php if (isset($prediction)): ?>
                    const sentimentResult = 'Hasil Analisis Sentimen:\n' +
                        'Teks: <?php echo addslashes(htmlspecialchars($input_text)); ?>\n' +
                        'Sentimen: <?php echo $sentimentTranslations[$prediction['sentiment']]; ?>\n' +
                        'Skor: <?php echo number_format($score, 3); ?>\n' +
                        'Probabilitas:\n' +
                        '- Positif: <?php echo number_format($prediction['probabilities']['positive'] * 100, 2); ?>%\n' +
                        '- Netral: <?php echo number_format($prediction['probabilities']['neutral'] * 100, 2); ?>%\n' +
                        '- Negatif: <?php echo number_format($prediction['probabilities']['negative'] * 100, 2); ?>%';
                    
                    try {
                        // Metode asinkron
                        navigator.clipboard.writeText(sentimentResult)
                            .then(function() {
                                alert('Hasil analisis telah disalin ke clipboard!');
                            })
                            .catch(function() {
                                fallbackCopyTextToClipboard(sentimentResult);
                            });
                    } catch (err) {
                        fallbackCopyTextToClipboard(sentimentResult);
                    }
                <?php endif; ?>
            }
        
        // Aktifkan tooltips Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html> 