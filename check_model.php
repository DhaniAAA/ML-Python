<?php
// Tambahkan memory helper untuk meningkatkan batas memori
require_once 'memory_helper.php';
require_once 'vendor/autoload.php';
require_once 'config.php';

// Set output sebagai plain text
header('Content-Type: text/plain');

echo "=== PENGECEKAN MODEL ANALISIS SENTIMEN ===\n\n";

// Fungsi untuk memeriksa file
function checkFile($path, $description) {
    echo "Memeriksa {$description}... ";
    
    if (!file_exists($path)) {
        echo "TIDAK ADA!\n";
        return false;
    }
    
    $size = filesize($path);
    echo "ADA ({$size} bytes)\n";
    
    return true;
}

// Periksa model vectorizer
$vectorizerPath = 'models/vectorizer.json';
if (checkFile($vectorizerPath, 'model vectorizer')) {
    $content = file_get_contents($vectorizerPath);
    echo "- Mencoba membaca model vectorizer... ";
    
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "ERROR: " . json_last_error_msg() . "\n";
    } else {
        if (isset($data['vocabulary']) && is_array($data['vocabulary'])) {
            $vocabCount = count($data['vocabulary']);
            echo "BERHASIL (vocabulary: {$vocabCount} kata)\n";
        } else {
            echo "ERROR: Format vocabulary tidak valid\n";
        }
    }
}

// Periksa model Naive Bayes
$modelPath = 'models/naive_bayes.dat';
if (checkFile($modelPath, 'model Naive Bayes')) {
    echo "- Mencoba membaca model Naive Bayes... ";
    
    try {
        $model = unserialize(file_get_contents($modelPath));
        if ($model === false) {
            echo "ERROR: Tidak dapat unserialize model\n";
        } else {
            echo "BERHASIL (model valid)\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Periksa file lexicon jika ada
$lexiconPath = 'data/lexicon/lexicon.txt';
checkFile($lexiconPath, 'file lexicon');

echo "\n=== INFORMASI SISTEM ===\n";
echo "Batas Memori PHP: " . ini_get('memory_limit') . "\n";
echo "Penggunaan Memori Saat Ini: " . formatBytes(memory_get_usage(true)) . "\n";
echo "Penggunaan Memori Puncak: " . formatBytes(memory_get_peak_usage(true)) . "\n";

echo "\n=== SARAN ===\n";
echo "Jika model tidak valid, coba lakukan training ulang dengan dataset yang lebih kecil atau\n";
echo "dengan opsi minDF yang lebih besar (misal 0.02 atau 0.05) untuk mengurangi ukuran vocabulary.\n";

// Menghapus fungsi formatBytes karena sudah ada di memory_helper.php
?> 