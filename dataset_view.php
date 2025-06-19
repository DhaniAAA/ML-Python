<?php
// Load konfigurasi
require_once 'config.php';
require_once 'lib/Preprocessing.php';
require_once 'memory_helper.php';

// Tingkatkan batas memori
increaseMemoryLimit();

// Cek apakah ID dataset diberikan
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: train.php');
    exit;
}

$dataset_id = (int)$_GET['id'];
$dataset = null;
$dataset_items = [];
$error = '';
$word_frequencies = [];

/**
 * Helper untuk meningkatkan batas memori
 */
function increaseMemoryLimit() {
    // Coba tingkatkan batas memori
    $current = ini_get('memory_limit');
    if ($current !== false && $current !== '-1') {
        ini_set('memory_limit', '2G'); // Coba tingkatkan ke 2GB
    }
    
    // Aktifkan garbage collection otomatis
    ini_set('zend.enable_gc', 1);
    gc_enable();
}

function calculatePythonModelMetrics($dataset_id) {
    // Path ke file hasil evaluasi
    $testDataPath = "data/testing/test_data_{$dataset_id}.json";
    $confusionMatrixPath = "models/confusion_matrix.png";
    
    // Cek apakah file hasil evaluasi ada
    if (!file_exists($testDataPath)) {
        // Jalankan training model Python
        $output = [];
        $returnVar = 0;
        exec("python train.py {$dataset_id} 2>&1", $output, $returnVar);
        
        if ($returnVar !== 0) {
            error_log("Error running Python training: " . implode("\n", $output));
            return null;
        }
    }
    
    // Baca hasil evaluasi
    if (file_exists($testDataPath)) {
        $testData = json_decode(file_get_contents($testDataPath), true);
        if (!$testData) {
            error_log("Error decoding test data JSON");
            return null;
        }
        
        // Parse classification report untuk mendapatkan metrik
        $report = $testData['classification_report'];
        $lines = explode("\n", $report);
        $metrics = [];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Parse setiap baris report
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 5) {
                $label = $parts[0];
                if (in_array($label, ['positive', 'negative', 'neutral'])) {
                    $metrics[$label] = [
                        'precision' => floatval($parts[1]) * 100,
                        'recall' => floatval($parts[2]) * 100,
                        'f1score' => floatval($parts[3]) * 100
                    ];
                }
            } else if (count($parts) >= 2 && $parts[0] === 'accuracy') {
                $metrics['accuracy'] = floatval($parts[1]) * 100;
            }
        }
        
        // Hitung confusion matrix dari prediksi
        $y_test = $testData['y_test'];
        $y_pred = $testData['y_pred'];
        $labels = ['positive', 'negative', 'neutral'];
        $matrix = [];
        
        // Inisialisasi matrix
        foreach ($labels as $actual) {
            $matrix[$actual] = [];
            foreach ($labels as $predicted) {
                $matrix[$actual][$predicted] = 0;
            }
        }
        
        // Isi confusion matrix
        for ($i = 0; $i < count($y_test); $i++) {
            $actual = $y_test[$i];
            $predicted = $y_pred[$i];
            $matrix[$actual][$predicted]++;
        }
        
        // Format confusion matrix untuk tampilan
        $confusionMatrix = [
            'positive' => [
                'tp' => $matrix['positive']['positive'],
                'fn_neutral' => $matrix['positive']['neutral'],
                'fn_negative' => $matrix['positive']['negative']
            ],
            'neutral' => [
                'fp_positive' => $matrix['neutral']['positive'],
                'tn' => $matrix['neutral']['neutral'],
                'fn_negative' => $matrix['neutral']['negative']
            ],
            'negative' => [
                'fp_positive' => $matrix['negative']['positive'],
                'fp_neutral' => $matrix['negative']['neutral'],
                'tn' => $matrix['negative']['negative']
            ]
        ];
        
        // Format hasil evaluasi
        $result = [
            'confusion_matrix_image' => file_exists($confusionMatrixPath) ? $confusionMatrixPath : null,
            'confusionMatrix' => $confusionMatrix,
            'accuracy' => $metrics['accuracy'] ?? 0,
            'precision' => [
                'positive' => $metrics['positive']['precision'] ?? 0,
                'neutral' => $metrics['neutral']['precision'] ?? 0,
                'negative' => $metrics['negative']['precision'] ?? 0
            ],
            'recall' => [
                'positive' => $metrics['positive']['recall'] ?? 0,
                'neutral' => $metrics['neutral']['recall'] ?? 0,
                'negative' => $metrics['negative']['recall'] ?? 0
            ],
            'f1score' => [
                'positive' => $metrics['positive']['f1score'] ?? 0,
                'neutral' => $metrics['neutral']['f1score'] ?? 0,
                'negative' => $metrics['negative']['f1score'] ?? 0
            ]
        ];
        
        return $result;
    }
    
    return null;
}

// Modifikasi fungsi calculateModelMetrics
function calculateModelMetrics($conn, $dataset_id) {
    // Coba dapatkan hasil evaluasi dari model Python
    $pythonMetrics = calculatePythonModelMetrics($dataset_id);
    
    if ($pythonMetrics !== null) {
        return $pythonMetrics;
    }
    
    // Jika tidak ada hasil dari model Python, gunakan implementasi PHP yang ada
    // ... existing PHP implementation code ...
}

// Ambil informasi dataset
try {
    if (!$conn) {
        throw new Exception('Koneksi database gagal. Silakan periksa konfigurasi.');
    }
    
    // Ambil detail dataset
    $stmt = $conn->prepare("SELECT id, filename, original_filename, sample_count, status, created_at 
                          FROM datasets 
                          WHERE id = ?");
    $stmt->bind_param("i", $dataset_id);
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengambil detail dataset: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        throw new Exception('Dataset tidak ditemukan');
    }
    
    $dataset = $result->fetch_assoc();
    $stmt->close();
    
    // Ambil statistik sentimen
    $stmt = $conn->prepare("SELECT sentiment, COUNT(*) as count 
                          FROM dataset_items 
                          WHERE dataset_id = ? 
                          GROUP BY sentiment");
    $stmt->bind_param("i", $dataset_id);
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengambil statistik sentimen: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $sentiment_stats = [];
    while ($row = $result->fetch_assoc()) {
        $sentiment_stats[$row['sentiment']] = $row['count'];
    }
    $stmt->close();
    
    // Hitung persentase untuk setiap sentimen
    $total = array_sum($sentiment_stats);
    foreach ($sentiment_stats as $sentiment => $count) {
        $sentiment_stats[$sentiment . '_percent'] = ($total > 0) ? round(($count / $total) * 100, 2) : 0;
    }
    
    // Ambil frekuensi kata untuk word cloud
    $stmt = $conn->prepare("SELECT 
                          word, 
                          COUNT(*) as frequency,
                          sentiment 
                          FROM (
                              SELECT 
                                  SUBSTRING_INDEX(SUBSTRING_INDEX(processed_text, ' ', n.n), ' ', -1) as word,
                                  sentiment
                              FROM 
                                  dataset_items,
                                  (SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) as n
                              WHERE 
                                  dataset_id = ? AND
                                  LENGTH(processed_text) - LENGTH(REPLACE(processed_text, ' ', '')) >= n.n - 1
                          ) as words
                          WHERE LENGTH(word) > 2
                          GROUP BY word, sentiment
                          ORDER BY frequency DESC
                          LIMIT 500");
    $stmt->bind_param("i", $dataset_id);
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengambil frekuensi kata: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $word_frequencies[] = [
            'text' => $row['word'],
            'weight' => $row['frequency'],
            'sentiment' => $row['sentiment']
        ];
    }
    $stmt->close();
    
    // Ambil item dataset (dibatasi 20 untuk performa)
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $stmt = $conn->prepare("SELECT id, text, processed_text, sentiment, score 
                          FROM dataset_items 
                          WHERE dataset_id = ? 
                          ORDER BY id 
                          LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $dataset_id, $limit, $offset);
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengambil item dataset: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $dataset_items[] = $row;
    }
    $stmt->close();
    
    // Hitung total halaman
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM dataset_items WHERE dataset_id = ?");
    $stmt->bind_param("i", $dataset_id);
    if (!$stmt->execute()) {
        throw new Exception('Gagal menghitung total item: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_items = $row['total'];
    $total_pages = ceil($total_items / $limit);
    $stmt->close();
    
    // Hitung metrics untuk model
    $modelMetrics = calculateModelMetrics($conn, $dataset_id);
    
} catch (Exception $e) {
    $error = $e->getMessage();
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dataset - Analisis Sentimen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CSS -->
    <!-- <link href="assets/css/style.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="assets/css/style_dataset_view.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3@7.8.5/dist/d3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-cloud@1.2.5/build/d3.layout.cloud.min.js"></script>
</head>
<body>
    <?php include('nav_template.php'); ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="train.php" class="text-decoration-none">Training</a></li>
                        <li class="breadcrumb-item active">Detail Dataset</li>
                    </ol>
                </nav>
                <h2 class="mt-2">Detail Dataset</h2>
            </div>
            <a href="train.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php else: ?>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title"><i class="bi bi-info-circle"></i> Informasi Dataset</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-card">
                                <div class="info-item">
                                    <div class="info-label">Nama File</div>
                                    <div class="info-value text-truncate" title="<?php echo htmlspecialchars($dataset['original_filename']); ?>">
                                        <?php echo htmlspecialchars($dataset['original_filename']); ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Jumlah Data</div>
                                    <div class="info-value">
                                        <span class="badge bg-primary"><?php echo number_format($dataset['sample_count']); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                <?php 
                                    $statusClass = 'secondary';
                                            $statusIcon = 'circle';
                                            if ($dataset['status'] == 'completed') {
                                                $statusClass = 'success';
                                                $statusIcon = 'check-circle-fill';
                                            }
                                            if ($dataset['status'] == 'failed') {
                                                $statusClass = 'danger';
                                                $statusIcon = 'x-circle-fill';
                                            }
                                            if ($dataset['status'] == 'processing') {
                                                $statusClass = 'info';
                                                $statusIcon = 'arrow-repeat';
                                            }
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                            <i class="bi bi-<?php echo $statusIcon; ?> me-1"></i>
                                    <?php echo ucfirst($dataset['status']); ?>
                                </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Tanggal Upload</div>
                                    <div class="info-value">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($dataset['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title"><i class="bi bi-pie-chart"></i> Distribusi Sentimen</h5>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Chart Type">
                                <button type="button" class="btn btn-outline-primary active" id="chartTypeBar"><i class="bi bi-bar-chart"></i></button>
                                <button type="button" class="btn btn-outline-primary" id="chartTypePie"><i class="bi bi-pie-chart"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="chart-container" style="position: relative; height: 250px;">
                                        <canvas id="sentimentChart" width="100%" height="100%"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Sentimen</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th class="text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (['positive', 'negative', 'neutral'] as $sentiment): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-<?php echo $sentimentColors[$sentiment]; ?>">
                                                            <?php echo $sentimentTranslations[$sentiment]; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center"><?php echo isset($sentiment_stats[$sentiment]) ? number_format($sentiment_stats[$sentiment]) : 0; ?></td>
                                                    <td class="text-end">
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar bg-<?php echo $sentimentColors[$sentiment]; ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo isset($sentiment_stats[$sentiment . '_percent']) ? $sentiment_stats[$sentiment . '_percent'] : 0; ?>%" 
                                                                 aria-valuenow="<?php echo isset($sentiment_stats[$sentiment . '_percent']) ? $sentiment_stats[$sentiment . '_percent'] : 0; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                        <small><?php echo isset($sentiment_stats[$sentiment . '_percent']) ? $sentiment_stats[$sentiment . '_percent'] . '%' : '0%'; ?></small>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white d-flex justify-content-end">
                            <button class="btn btn-sm btn-outline-primary" id="downloadChartBtn">
                                <i class="bi bi-download"></i> Download Diagram
                            </button>
                        </div>
                            </div>
                        </div>                        
                    </div>
                    
            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title"><i class="bi bi-grid-3x3"></i> Confusion Matrix & Akurasi Model</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!isset($modelMetrics) || $modelMetrics === null): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Metrik evaluasi model belum tersedia. Hal ini mungkin terjadi karena jumlah data tidak cukup atau model belum dilatih dengan benar.
                            </div>
                            <?php else: ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3 text-center">Confusion Matrix</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-confusion-matrix text-center">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 180px;" class="text-center border-end-0 border-bottom-0"></th>
                                                    <th colspan="3" class="text-center bg-light">Predicted</th>
                                                </tr>
                                                <tr>
                                                    <th class="border-end-0 border-top-0"></th>
                                                    <th class="text-center" style="width: 100px;">Positif</th>
                                                    <th class="text-center" style="width: 100px;">Netral</th>
                                                    <th class="text-center" style="width: 100px;">Negatif</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Gunakan confusion matrix dari hasil model metrics jika tersedia
                                                $confusionMatrix = isset($modelMetrics['confusionMatrix']) ? $modelMetrics['confusionMatrix'] : [
                                                    'positive' => ['tp' => 0, 'fn_neutral' => 0, 'fn_negative' => 0],
                                                    'neutral' => ['fp_positive' => 0, 'tn' => 0, 'fn_negative' => 0],
                                                    'negative' => ['fp_positive' => 0, 'fp_neutral' => 0, 'tn' => 0]
                                                ];
                                                ?>
                                                <tr>
                                                    <th class="bg-light text-center">Actual<br>Positif</th>
                                                    <td class="bg-success bg-opacity-25"><?php echo $confusionMatrix['positive']['tp']; ?></td>
                                                    <td><?php echo $confusionMatrix['positive']['fn_neutral']; ?></td>
                                                    <td><?php echo $confusionMatrix['positive']['fn_negative']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light text-center">Actual<br>Netral</th>
                                                    <td><?php echo $confusionMatrix['neutral']['fp_positive']; ?></td>
                                                    <td class="bg-warning bg-opacity-25"><?php echo $confusionMatrix['neutral']['tn']; ?></td>
                                                    <td><?php echo $confusionMatrix['neutral']['fn_negative']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light text-center">Actual<br>Negatif</th>
                                                    <td><?php echo $confusionMatrix['negative']['fp_positive']; ?></td>
                                                    <td><?php echo $confusionMatrix['negative']['fp_neutral']; ?></td>
                                                    <td class="bg-danger bg-opacity-25"><?php echo $confusionMatrix['negative']['tn']; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-3 text-center">Metrik Evaluasi Model</h6>
                                    <div class="metrics-container p-3">
                                        <?php
                                        // Gunakan metrics dari hasil model metrics jika tersedia
                                        $accuracy = isset($modelMetrics['accuracy']) ? $modelMetrics['accuracy'] : 0;
                                        $precision = isset($modelMetrics['precision']) ? $modelMetrics['precision'] : [
                                            'positive' => 0,
                                            'neutral' => 0,
                                            'negative' => 0
                                        ];
                                        $recall = isset($modelMetrics['recall']) ? $modelMetrics['recall'] : [
                                            'positive' => 0,
                                            'neutral' => 0,
                                            'negative' => 0
                                        ];
                                        $f1score = isset($modelMetrics['f1score']) ? $modelMetrics['f1score'] : [
                                            'positive' => 0,
                                            'neutral' => 0,
                                            'negative' => 0
                                        ];
                                        ?>
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="metric-card text-center">
                                                    <h3 class="metric-value"><?php echo number_format($accuracy, 2); ?>%</h3>
                                                    <p class="metric-label">Akurasi Keseluruhan</p>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-primary" style="width: <?php echo $accuracy; ?>%" role="progressbar" aria-valuenow="<?php echo $accuracy; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-center">Kelas</th>
                                                        <th class="text-center">Precision</th>
                                                        <th class="text-center">Recall</th>
                                                        <th class="text-center">F1-Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge bg-success">Positif</span></td>
                                                        <td class="text-center"><?php echo number_format($precision['positive'], 2); ?>%</td>
                                                        <td class="text-center"><?php echo number_format($recall['positive'], 2); ?>%</td>
                                                        <td class="text-center"><?php echo number_format($f1score['positive'], 2); ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-warning">Netral</span></td>
                                                        <td class="text-center"><?php echo number_format($precision['neutral'], 2); ?>%</td>
                                                        <td class="text-center"><?php echo number_format($recall['neutral'], 2); ?>%</td>
                                                        <td class="text-center"><?php echo number_format($f1score['neutral'], 2); ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-danger">Negatif</span></td>
                                                        <td class="text-center"><?php echo number_format($precision['negative'], 2); ?>%</td>
                                                        <td class="text-center"><?php echo number_format($recall['negative'], 2); ?>%</td>
                                                        <td class="text-center"><?php echo number_format($f1score['negative'], 2); ?>%</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white text-muted">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Confusion matrix menunjukkan jumlah data dari setiap kelas yang diprediksi dengan benar atau salah. Diagonal utama (yang disorot) menunjukkan prediksi yang benar.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title"><i class="bi bi-cloud"></i> Word Cloud</h5>
                            <div class="d-flex align-items-center gap-2">
                                <div class="btn-group btn-group-sm me-2" role="group" aria-label="Sentiment Filter">
                                    <button type="button" class="btn btn-outline-primary active" id="wordcloud-all">Semua</button>
                                    <button type="button" class="btn btn-outline-success" id="wordcloud-positive">Positif</button>
                                    <button type="button" class="btn btn-outline-danger" id="wordcloud-negative">Negatif</button>
                                    <button type="button" class="btn btn-outline-warning" id="wordcloud-neutral">Netral</button>
                                </div>
                                <select class="form-select form-select-sm" id="wordcloud-limit" style="width: 100px;">
                                    <option value="50">50 Kata</option>
                                    <option value="100" selected>100 Kata</option>
                                    <option value="200">200 Kata</option>
                                    <option value="300">300 Kata</option>
                                    <option value="500">500 Kata</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="wordcloud" class="shadow-sm"></div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Hover pada kata untuk melihat detail. Ukuran kata menunjukkan frekuensi kemunculan.</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between">
                            <small class="text-muted">Hover pada kata untuk melihat detail. Ukuran kata menunjukkan frekuensi kemunculan.</small>
                            <button class="btn btn-sm btn-outline-primary" id="downloadWordcloudBtn">
                                <i class="bi bi-download"></i> Download Wordcloud
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title"><i class="bi bi-table"></i> Data</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" class="form-control" placeholder="Cari..." id="searchInput">
                            <button class="btn btn-outline-primary" type="button"><i class="bi bi-search"></i></button>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" id="downloadDatasetBtn">
                            <i class="bi bi-download"></i> Download Dataset
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover data-table">
                            <thead>
                                <tr>
                                    <th width="70">Nomor</th>
                                    <th>Teks Asli</th>
                                    <th>Teks Preprocessing</th>
                                    <th width="100">Sentimen</th>
                                    <th width="80">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataset_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" 
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($item['text']); ?>">
                                            <?php echo htmlspecialchars(substr($item['text'], 0, 50)) . (strlen($item['text']) > 50 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" 
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($item['processed_text']); ?>">
                                            <?php echo htmlspecialchars(substr($item['processed_text'], 0, 50)) . (strlen($item['processed_text']) > 50 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $sentimentColors[$item['sentiment']]; ?>">
                                            <i class="bi bi-emoji-<?php echo $item['sentiment'] == 'positive' ? 'smile' : ($item['sentiment'] == 'negative' ? 'frown' : 'neutral'); ?>"></i>
                                            <?php echo $sentimentTranslations[$item['sentiment']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?php echo $sentimentColors[$item['sentiment']]; ?>" 
                                                style="width: <?php echo min(100, abs($item['score']) * 100); ?>%">
                                            </div>
                                        </div>
                                        <small class="d-block text-center mt-1"><?php echo $item['score']; ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?id=<?php echo $dataset_id; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?id=' . $dataset_id . '&page=1">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                                echo '<a class="page-link" href="?id=' . $dataset_id . '&page=' . $i . '">' . $i . '</a>';
                                echo '</li>';
                            }
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?id=' . $dataset_id . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?id=<?php echo $dataset_id; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tambahkan section untuk menampilkan hasil evaluasi model -->
            <!-- <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Hasil Evaluasi Model</h5>
                </div>
                <div class="card-body">
                    <?php
                    $metrics = calculateModelMetrics($conn, $dataset_id);
                    if ($metrics !== null) {
                        if (isset($metrics['confusion_matrix_image'])) {
                            // Tampilkan confusion matrix dari model Python
                            echo '<h6>Confusion Matrix:</h6>';
                            echo '<img src="' . $metrics['confusion_matrix_image'] . '" class="img-fluid" alt="Confusion Matrix">';
                            
                            if (isset($metrics['test_data'])) {
                                echo '<h6 class="mt-4">Classification Report:</h6>';
                                if (isset($metrics['test_data']['classification_report'])) {
                                    echo '<pre class="bg-light p-3">' . $metrics['test_data']['classification_report'] . '</pre>';
                                }
                            }
                        } else if (isset($metrics['confusionMatrix'])) {
                            // Tampilkan hasil dari model PHP (fallback)
                            echo '<h6>Confusion Matrix:</h6>';
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-bordered">';
                            echo '<thead><tr><th>Actual/Predicted</th><th>Positive</th><th>Neutral</th><th>Negative</th></tr></thead>';
                            echo '<tbody>';
                            
                            // Tampilkan data confusion matrix
                            $labels = ['positive', 'neutral', 'negative'];
                            foreach ($labels as $actual) {
                                echo '<tr>';
                                echo '<th>' . ucfirst($actual) . '</th>';
                                foreach ($labels as $predicted) {
                                    $value = $metrics['confusionMatrix'][$actual]['tp'];
                                    if ($actual != $predicted) {
                                        $value = $metrics['confusionMatrix'][$actual]['fn_' . $predicted];
                                    }
                                    echo '<td>' . $value . '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                            
                            // Tampilkan metrik evaluasi
                            echo '<h6 class="mt-4">Metrik Evaluasi:</h6>';
                            echo '<div class="row">';
                            echo '<div class="col-md-3">';
                            echo '<p><strong>Accuracy:</strong> ' . number_format($metrics['accuracy'], 2) . '%</p>';
                            echo '</div>';
                            
                            // Tampilkan precision, recall, dan F1-score untuk setiap kelas
                            foreach ($labels as $label) {
                                echo '<div class="col-md-3">';
                                echo '<p class="mb-2"><strong>' . ucfirst($label) . ':</strong></p>';
                                echo '<ul class="list-unstyled pl-3">';
                                echo '<li>Precision: ' . number_format($metrics['precision'][$label], 2) . '%</li>';
                                echo '<li>Recall: ' . number_format($metrics['recall'][$label], 2) . '%</li>';
                                echo '<li>F1-score: ' . number_format($metrics['f1score'][$label], 2) . '</li>';
                                echo '</ul>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">Hasil evaluasi model tidak tersedia.</div>';
                    }
                    ?>
                </div>
            </div> -->
        <?php endif; ?>
    </div>

    <!-- <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <small class="text-muted">© <?php echo date('Y'); ?> Analisis Sentimen - Aplikasi Pendeteksi Sentimen Indonesia</small>
        </div>
    </footer> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!$error): ?>
    <script>
        // Inisialisasi tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body'
                });
            });
            
            // Render charts
            renderSentimentChart('bar');
            
            // Tombol untuk beralih jenis chart
            document.getElementById('chartTypeBar').addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('chartTypePie').classList.remove('active');
                renderSentimentChart('bar');
            });
            
            document.getElementById('chartTypePie').addEventListener('click', function() {
                this.classList.add('active');
                document.getElementById('chartTypeBar').classList.remove('active');
                renderSentimentChart('pie');
            });
            
            // Filter untuk word cloud
            document.getElementById('wordcloud-all').addEventListener('click', function() {
                resetWordCloudFilter();
                this.classList.add('active');
                renderWordCloud('all');
            });
            
            document.getElementById('wordcloud-positive').addEventListener('click', function() {
                resetWordCloudFilter();
                this.classList.add('active');
                renderWordCloud('positive');
            });
            
            document.getElementById('wordcloud-negative').addEventListener('click', function() {
                resetWordCloudFilter();
                this.classList.add('active');
                renderWordCloud('negative');
            });
            
            document.getElementById('wordcloud-neutral').addEventListener('click', function() {
                resetWordCloudFilter();
                this.classList.add('active');
                renderWordCloud('neutral');
            });
            
            // Tambahkan event listener untuk limit kata
            document.getElementById('wordcloud-limit').addEventListener('change', function() {
                const currentFilter = document.querySelector('.btn-group-sm .active').id.replace('wordcloud-', '');
                renderWordCloud(currentFilter);
            });
            
            // Render word cloud awal
            renderWordCloud('all');
        });
        
        function resetWordCloudFilter() {
            document.getElementById('wordcloud-all').classList.remove('active');
            document.getElementById('wordcloud-positive').classList.remove('active');
            document.getElementById('wordcloud-negative').classList.remove('active');
            document.getElementById('wordcloud-neutral').classList.remove('active');
        }
        
        // Fungsi untuk rendering sentiment chart
        function renderSentimentChart(type) {
            // // Hapus chart yang ada jika sudah ada
            // if (window.sentimentChart) {
            //     window.sentimentChart.destroy();
            // }
            
            const ctx = document.getElementById('sentimentChart').getContext('2d');
            window.sentimentChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: ['Positif', 'Negatif', 'Netral'],
                    datasets: [{
                        label: 'Jumlah Data',
                        data: [
                            <?php echo isset($sentiment_stats['positive']) ? $sentiment_stats['positive'] : 0; ?>,
                            <?php echo isset($sentiment_stats['negative']) ? $sentiment_stats['negative'] : 0; ?>,
                            <?php echo isset($sentiment_stats['neutral']) ? $sentiment_stats['neutral'] : 0; ?>
                        ],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(255, 193, 7, 0.7)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: type === 'bar' ? {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                drawBorder: false
                        }
                    },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    } : {},
                    plugins: {
                        legend: {
                            display: type === 'pie',
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
            
        // Render wordcloud dengan filter
        function renderWordCloud(filter) {
            // Hapus word cloud yang sudah ada
            document.getElementById('wordcloud').innerHTML = '';
            
            const width = document.getElementById('wordcloud').offsetWidth;
            const height = 300;
            
            // Ambil limit kata yang dipilih
            const wordLimit = parseInt(document.getElementById('wordcloud-limit').value);
            
            // Prepare data untuk word cloud dengan filter
            const wordFrequencies = <?php echo json_encode($word_frequencies); ?>;
            let filteredWords = wordFrequencies;
            
            if (filter !== 'all') {
                filteredWords = wordFrequencies.filter(item => item.sentiment === filter);
            }
            
            // Urutkan dan batasi jumlah kata
            filteredWords.sort((a, b) => b.weight - a.weight);
            filteredWords = filteredWords.slice(0, wordLimit);
            
            const words = filteredWords.map(item => {
                // Tentukan warna berdasarkan sentimen
                let color = '#888888'; // default untuk neutral
                if (item.sentiment === 'positive') {
                    color = '#28a745'; // hijau
                } else if (item.sentiment === 'negative') {
                    color = '#dc3545'; // merah
                } else if (item.sentiment === 'neutral') {
                    color = '#ffc107'; // kuning
                }
                
                return {
                    text: item.text,
                    size: Math.max(14, Math.min(50, 14 + (item.weight * 2))), // Scale font size
                    color: color,
                    sentiment: item.sentiment
                };
            });
            
            // Create word cloud using D3
            const layout = d3.layout.cloud()
                .size([width, height])
                .words(words)
                .padding(5)
                .rotate(() => 0)
                .fontSize(d => d.size)
                .on('end', draw);
                
            layout.start();
            
            function draw(words) {
                d3.select('#wordcloud')
                    .append('svg')
                    .attr('width', width)
                    .attr('height', height)
                    .append('g')
                    .attr('transform', `translate(${width/2},${height/2})`)
                    .selectAll('text')
                    .data(words)
                    .enter()
                    .append('text')
                    .style('font-size', d => `${d.size}px`)
                    .style('font-family', '"Segoe UI", sans-serif')
                    .style('font-weight', '600')
                    .style('fill', d => d.color)
                    .attr('text-anchor', 'middle')
                    .attr('class', 'wordcloud-text')
                    .attr('transform', d => `translate(${d.x},${d.y})rotate(${d.rotate})`)
                    .text(d => d.text)
                    .append('title')
                    .text(d => `${d.text} (${d.sentiment})`)
            }
        }
        
        // Fungsionalitas search pada tabel data
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if(text.indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Fungsi untuk download chart sebagai gambar
        document.getElementById('downloadChartBtn').addEventListener('click', function() {
            const canvas = document.getElementById('sentimentChart');
            
            // Simpan konfigurasi asli
            const originalChart = window.sentimentChart;
            
            // Buat canvas sementara untuk gambar dengan background putih
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            
            // Isi background putih
            tempCtx.fillStyle = 'white';
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            
            // Gambar chart ke canvas sementara
            tempCtx.drawImage(canvas, 0, 0);
            
            // Ubah canvas ke image URL
            const image = tempCanvas.toDataURL('image/png', 1.0);
            
            // Buat link untuk download
            const downloadLink = document.createElement('a');
            downloadLink.download = 'sentiment_chart.png';
            downloadLink.href = image;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
        
        // Fungsi untuk download wordcloud sebagai gambar
        document.getElementById('downloadWordcloudBtn').addEventListener('click', function() {
            const svgElement = document.querySelector('#wordcloud svg');
            
            if (!svgElement) {
                alert('Wordcloud belum dirender, silahkan tunggu sebentar.');
                return;
            }
            
            // Buat clone SVG untuk dimanipulasi
            const svgClone = svgElement.cloneNode(true);
            const svgData = new XMLSerializer().serializeToString(svgClone);
            
            // Buat canvas untuk konversi
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Buat image dari SVG
            const img = new Image();
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml'});
            const url = URL.createObjectURL(svgBlob);
            
            img.onload = function() {
                canvas.width = svgElement.width.baseVal.value;
                canvas.height = svgElement.height.baseVal.value;
                
                // Isi background putih
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                
                // Gambar SVG ke canvas
                ctx.drawImage(img, 0, 0);
                
                // Download image
                const downloadLink = document.createElement('a');
                downloadLink.download = 'wordcloud.png';
                downloadLink.href = canvas.toDataURL('image/png');
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                
                // Bersihkan
                URL.revokeObjectURL(url);
            };
            
            img.src = url;
        });
        
        // Fungsi untuk download dataset
        document.getElementById('downloadDatasetBtn').addEventListener('click', function() {
            // Download dataset dalam format CSV
            window.location.href = 'download_dataset.php?id=<?php echo $dataset_id; ?>&format=csv';
        });
    </script>
    <?php endif; ?>
</body>
</html> 