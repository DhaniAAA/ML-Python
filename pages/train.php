<?php
// Tambahkan memory helper untuk meningkatkan batas memori
require_once '../includes/memory_helper.php';
require_once '../vendor/autoload.php';
require_once '../includes/config.php';
require_once '../lib/Preprocessing.php';

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\Classification\NaiveBayes;
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\StopWords\StopWords;
use Phpml\Preprocessing\Normalizer;
use Phpml\Metric\Accuracy;

// Inisialisasi session
session_start();

$message = '';
$error = '';

// Tambahkan handler untuk menghapus dataset
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $id = (int) $_GET['id'];

        // Cek koneksi database
        if (!$conn) {
            throw new Exception('Koneksi database gagal. Silakan periksa konfigurasi.');
        }

        // Ambil informasi file dataset
        $stmt = $conn->prepare("SELECT filename FROM datasets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Dataset tidak ditemukan.');
        }

        $row = $result->fetch_assoc();
        $dataset_filename = $row['filename'];
        $stmt->close();

        // Hapus file dataset
        $file_path = __DIR__ . '/data/uploads/' . $dataset_filename;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Cek apakah ini dataset terakhir yang ditraining
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM datasets WHERE id != ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_remaining = $row['total'];
        $stmt->close();

        // Jika ini dataset terakhir yang ditraining, hapus model
        if ($total_remaining === 0) {
            // Hapus file model
            $vectorizer_path = __DIR__ . '/models/vectorizer.json';
            $naive_bayes_path = __DIR__ . '/models/naive_bayes.dat';

            if (file_exists($vectorizer_path)) {
                unlink($vectorizer_path);
            }

            if (file_exists($naive_bayes_path)) {
                unlink($naive_bayes_path);
            }
        }

        // Hapus dataset dari database (models dan dataset_items akan otomatis terhapus karena foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM datasets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $message = "Dataset dan model berhasil dihapus.";

        // Redirect kembali ke halaman training
        header('Location: train.php?message=' . urlencode($message));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Di bagian awal train.php, setelah inisialisasi session
// Tambahkan handler untuk pesan dari redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fungsi untuk membuat tabel datasets jika belum ada
function createDatasetsTableIfNotExists($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS datasets (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_filename VARCHAR(255) NOT NULL,
        sample_count INT(11) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('processing', 'completed', 'failed') DEFAULT 'processing'
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Error membuat tabel datasets: " . $conn->error);
    }
}

// Fungsi untuk membuat tabel dataset_items jika belum ada
function createDatasetItemsTableIfNotExists($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS dataset_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        dataset_id INT(11) NOT NULL,
        text TEXT NOT NULL,
        processed_text TEXT NOT NULL,
        sentiment ENUM('positive', 'negative', 'neutral') NOT NULL,
        score INT(11) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dataset_id) REFERENCES datasets(id) ON DELETE CASCADE
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Error membuat tabel dataset_items: " . $conn->error);
    }
}

// Fungsi untuk membuat tabel models jika belum ada
function createModelsTableIfNotExists($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS models (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        dataset_id INT(11) NOT NULL,
        filename VARCHAR(255) NOT NULL,
        model_type ENUM('vectorizer', 'naive_bayes') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dataset_id) REFERENCES datasets(id) ON DELETE CASCADE
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Error membuat tabel models: " . $conn->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Cek koneksi database
        if (!$conn) {
            throw new Exception('Koneksi database gagal. Silakan periksa konfigurasi.');
        }

        // Buat tabel jika belum ada
        createDatasetsTableIfNotExists($conn);
        createDatasetItemsTableIfNotExists($conn);
        createModelsTableIfNotExists($conn);

        if (!isset($_FILES['dataset']) || $_FILES['dataset']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Silakan pilih file dataset');
        }

        $file = $_FILES['dataset']['tmp_name'];
        $original_filename = $_FILES['dataset']['name'];

        // Simpan file upload ke direktori data/uploads
        $upload_dir = __DIR__ . '/data/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = date('YmdHis') . '_' . $original_filename;
        $target_path = $upload_dir . $filename;

        if (!move_uploaded_file($file, $target_path)) {
            throw new Exception('Gagal menyimpan file dataset');
        }

        // Buat record baru di tabel datasets
        $stmt = $conn->prepare("INSERT INTO datasets (filename, original_filename, sample_count, status) VALUES (?, ?, 0, 'processing')");
        $stmt->bind_param("ss", $filename, $original_filename);

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan informasi dataset: ' . $stmt->error);
        }

        $dataset_id = $conn->insert_id;
        $stmt->close();

        $handle = fopen($target_path, 'r');

        if (!$handle) {
            throw new Exception('Gagal membuka file');
        }

        // Baca header CSV
        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 3) {
            throw new Exception('Format CSV tidak valid. Dibutuhkan kolom: Username, Create time, Teks');
        }

        // Batasi jumlah dataset untuk mencegah kehabisan memori
        $max_samples = isset($_POST['max_samples']) ? (int)$_POST['max_samples'] : 1000;
        $batch_size = 100; // Proses data dalam batch

        // Baca data
        $documents = [];
        $labels = [];
        $preprocessor = new Preprocessing();
        $sample_count = 0;
        $lexicon_scores = [];
        $processed_texts = []; // Array untuk menyimpan teks yang sudah diproses untuk deteksi duplikat

        // Load lexicon sekali saja di luar loop
        $lexicon = file_exists('data/lexicon/lexicon.txt') ? file('data/lexicon/lexicon.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

        // Parse lexicon file
        foreach ($lexicon as $line) {
            $parts = explode(",", $line);
            if (count($parts) === 2) {
                $lexicon_scores[trim($parts[0])] = (int)trim($parts[1]);
            }
        }

        // Bebaskan memori
        unset($lexicon);

        // Untuk batch insert ke database
        $batch_data = [];
        $batch_counter = 0;
        $stmt = $conn->prepare("INSERT INTO dataset_items (dataset_id, text, processed_text, sentiment, score) VALUES (?, ?, ?, ?, ?)");

        while (($row = fgetcsv($handle)) !== false && $sample_count < $max_samples) {
            if (count($row) >= 3) {
                $text = $row[2]; // Kolom Teks

                // Preprocessing teks
                $text = $preprocessor->convertEmoji($text);
                $text = $preprocessor->convertEmoticons($text);
                $cleaned_text = $preprocessor->cleanText($text);

                // Skip jika teks kosong setelah cleaning
                if (empty(trim($cleaned_text))) {
                    continue; // Lewati baris ini
                }

                $tokens = $preprocessor->tokenize($cleaned_text);
                $tokens = $preprocessor->removeStopwords($tokens);
                $stemmed_tokens = $preprocessor->stemWords($tokens);
                $stemmed_text = implode(' ', $stemmed_tokens);

                // Skip jika teks kosong setelah stemming
                if (empty(trim($stemmed_text))) {
                    continue; // Lewati baris ini
                }

                // Cek duplikat teks, gunakan teks yang pertama muncul
                if (in_array($cleaned_text, $processed_texts)) {
                    continue; // Lewati jika sudah ada teks yang sama
                }

                // Tambahkan ke array processed_texts untuk pengecekan duplikat
                $processed_texts[] = $cleaned_text;

                $documents[] = $stemmed_text;

                // Labeling berdasarkan lexicon
                $total_score = 0;

                // Hitung skor total
                foreach ($tokens as $token) {
                    if (isset($lexicon_scores[$token])) {
                        $total_score += $lexicon_scores[$token];
                    }
                }

                // Tentukan label berdasarkan skor
                $sentiment = 'neutral';
                if ($total_score > 0) {
                    $sentiment = 'positive';
                    $labels[] = 'positive';
                } elseif ($total_score < 0) {
                    $sentiment = 'negative';
                    $labels[] = 'negative';
                } else {
                    $labels[] = 'neutral';
                }

                // Pastikan data dalam format string
                $text_to_save = is_array($text) ? implode(' ', $text) : $text;
                $stemmed_text_to_save = is_array($stemmed_text) ? implode(' ', $stemmed_text) : $stemmed_text;

                // Simpan ke database
                $stmt->bind_param("isssi", $dataset_id, $text_to_save, $stemmed_text_to_save, $sentiment, $total_score);
                if (!$stmt->execute()) {
                    throw new Exception('Gagal menyimpan item dataset: ' . $stmt->error);
                }

                $sample_count++;

                // Bebaskan memori setelah setiap batch
                if ($sample_count % $batch_size === 0) {
                    gc_collect_cycles();
                    // Bebaskan memori array processed_texts jika sudah terlalu besar
                    if (count($processed_texts) > 1000) {
                        $processed_texts = array_slice($processed_texts, -1000);
                    }
                }
            }
        }
        fclose($handle);
        $stmt->close();

        if (empty($documents)) {
            throw new Exception('Dataset kosong');
        }

        // Update jumlah sampel di tabel datasets
        $stmt = $conn->prepare("UPDATE datasets SET sample_count = ?, status = 'completed' WHERE id = ?");
        $stmt->bind_param("ii", $sample_count, $dataset_id);
        $stmt->execute();
        $stmt->close();

        // Persiapkan ArrayDataset dari dokumen dan label yang telah diproses
        $dataset = new ArrayDataset($documents, $labels);

        // Buat split dengan rasio 80/20 (train/test)
        $split = new StratifiedRandomSplit($dataset, 0.2); // 0.2 = 20% untuk testing

        // Ambil data training dan testing
        $trainSamples = $split->getTrainSamples();
        $trainLabels = $split->getTrainLabels();
        $testSamples = $split->getTestSamples();
        $testLabels = $split->getTestLabels();

        // Simpan data testing untuk evaluasi
        if (!is_dir('data/testing')) {
            mkdir('data/testing', 0777, true);
        }
        file_put_contents('data/testing/test_samples_' . $dataset_id . '.dat', serialize($testSamples));
        file_put_contents('data/testing/test_labels_' . $dataset_id . '.dat', serialize($testLabels));

        // Training Vectorizer hanya dengan data training
        $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer(), null, 0.03);
        $vectorizer->fit($trainSamples);
        $vectorizer->transform($trainSamples);

        // Simpan model vectorizer
        $vectorizerModel = [
            'vocabulary' => $vectorizer->getVocabulary()
        ];
        file_put_contents('models/vectorizer.json', json_encode($vectorizerModel));

        // Bebaskan memori
        gc_collect_cycles();

        // Training Naive Bayes dengan data training
        $classifier = new NaiveBayes();
        $classifier->train($trainSamples, $trainLabels);

        // Simpan model Naive Bayes menggunakan serialize
        if (!is_dir('models')) {
            mkdir('models', 0777, true);
        }
        file_put_contents('models/naive_bayes.dat', serialize($classifier));

        // Simpan informasi model
        $stmt = $conn->prepare("INSERT INTO models (dataset_id, filename, model_type, created_at) VALUES (?, ?, ?, NOW())");
        $vectorizer_filename = 'vectorizer.json';
        $naive_bayes_filename = 'naive_bayes.dat';

        // Simpan informasi vectorizer
        $stmt->bind_param("iss", $dataset_id, $vectorizer_filename, $model_type);
        $model_type = 'vectorizer';
        $stmt->execute();

        // Simpan informasi Naive Bayes
        $stmt->bind_param("iss", $dataset_id, $naive_bayes_filename, $model_type);
        $model_type = 'naive_bayes';
        $stmt->execute();

        $stmt->close();

        $message = "Model berhasil dilatih dengan $sample_count dataset!";
    } catch (Exception $e) {
        // Update status dataset jika gagal
        if (isset($dataset_id) && isset($conn)) {
            $stmt = $conn->prepare("UPDATE datasets SET status = 'failed' WHERE id = ?");
            $stmt->bind_param("i", $dataset_id);
            $stmt->execute();
            $stmt->close();
        }

        $error = $e->getMessage();
    }
}

// Ambil daftar dataset yang telah diupload
$datasets = [];
if (isset($conn)) {
    $sql = "SELECT id, filename, original_filename, sample_count, status, created_at
            FROM datasets
            ORDER BY created_at DESC
            LIMIT 10";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $datasets[] = $row;
        }
    }
}

// Tampilkan pesan jika belum ada upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $message = 'Silakan upload file dataset CSV untuk memulai training.';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Model - Analisis Sentimen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../assets/css/style_dataset_view.css" rel="stylesheet">
    <link href="../assets/css/navbar.css" rel="stylesheet">
</head>

<body>
    <?php include('../includes/nav_template.php'); ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Training Model</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form id="upload-form" method="post" enctype="multipart/form-data" action="train.php">
                            <div class="mb-3">
                                <label for="dataset" class="form-label">Pilih Dataset (CSV)</label>
                                <input type="file" class="form-control" id="dataset" name="dataset" accept=".csv" required>
                                <div class="form-text">
                                    Format CSV harus memiliki kolom: Username, Create time, Teks
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="max_samples" class="form-label">Jumlah Data Maksimum</label>
                                <input type="number" class="form-control" id="max_samples" name="max_samples" min="10" max="10000" value="1000">
                                <div class="form-text">
                                    Batasi jumlah data untuk menghindari kehabisan memori (1000-10000)
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Mulai Training</button>
                        </form>
                    </div>
                </div>

                <!-- Tampilkan daftar dataset yang telah diupload -->
                <?php if (!empty($datasets)): ?>
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-database"></i> Dataset Tersimpan</h5>
                            <div class="d-flex align-items-center">
                                <div class="input-group input-group-sm" style="width: 200px;">
                                    <input type="text" class="form-control" placeholder="Cari dataset..." id="searchDataset">
                                    <button class="btn btn-outline-primary" type="button" id="searchButton"><i class="bi bi-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover data-table rounded-3 overflow-hidden">
                                    <thead>
                                        <tr>
                                            <th width="60">ID</th>
                                            <th>Nama File</th>
                                            <th width="110">Jumlah Data</th>
                                            <th width="100">Status</th>
                                            <th width="150">Tanggal Upload</th>
                                            <th width="140" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datasets as $dataset): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo $dataset['id']; ?></td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 250px;"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="<?php echo htmlspecialchars($dataset['original_filename']); ?>">
                                                        <?php echo htmlspecialchars($dataset['original_filename']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <?php echo number_format($dataset['sample_count']); ?>
                                                    </span>
                                                </td>
                                                <td>
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
                                                </td>
                                                <td><i class="bi bi-calendar-date me-1"></i><?php echo date('d/m/Y H:i', strtotime($dataset['created_at'])); ?></td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="dataset_view.php?id=<?php echo $dataset['id']; ?>" class="btn btn-sm btn-info btn-icon" data-bs-toggle="tooltip" title="Lihat detail dataset">
                                                            <i class="bi bi-eye"></i>Detail
                                                        </a>
                                                        <a href="train.php?action=delete&id=<?php echo $dataset['id']; ?>" class="btn btn-sm btn-danger btn-icon"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus dataset ini? Semua model yang terkait juga akan dihapus.')"
                                                            data-bs-toggle="tooltip" title="Hapus dataset ini">
                                                            <i class="bi bi-trash"></i>Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inisialisasi tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Form submit handler untuk menampilkan loading
            const uploadForm = document.querySelector('form');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function() {
                    document.getElementById('loadingOverlay').style.display = 'flex';
                });
            }

            // Aktifkan tooltip Bootstrap
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body'
                });
            });

            // Fungsi pencarian dataset
            const searchInput = document.getElementById('searchDataset');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const value = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('.data-table tbody tr');

                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.indexOf(value) > -1) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });

                // Tombol pencarian
                const searchButton = document.getElementById('searchButton');
                if (searchButton) {
                    searchButton.addEventListener('click', function() {
                        const event = new Event('keyup');
                        searchInput.dispatchEvent(event);
                    });
                }
            }
        });
    </script>

    <!-- <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <small class="text-muted">© <?php echo date('Y'); ?> Analisis Sentimen - Aplikasi Pendeteksi Sentimen Indonesia</small>
        </div>
    </footer> -->
</body>

</html>