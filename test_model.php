<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST MODEL ===\n\n";

// Test akses ke file model
$vectorizerFile = 'models/vectorizer.json';
echo "Memeriksa file vectorizer: ";
if (file_exists($vectorizerFile)) {
    echo "OK (" . filesize($vectorizerFile) . " bytes)\n";
    
    $content = file_get_contents($vectorizerFile);
    echo "- Membaca isi file: " . (empty($content) ? "KOSONG" : strlen($content) . " bytes") . "\n";
    
    // Test decode JSON
    echo "- Mencoba parse JSON: ";
    $data = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "BERHASIL\n";
        if (isset($data['vocabulary'])) {
            echo "  - Vocabulary: " . count($data['vocabulary']) . " kata\n";
        } else {
            echo "  - Vocabulary tidak ditemukan dalam JSON\n";
        }
    } else {
        echo "GAGAL (" . json_last_error_msg() . ")\n";
        echo "  - 50 karakter pertama: " . substr($content, 0, 50) . "\n";
    }
} else {
    echo "TIDAK DITEMUKAN\n";
}

echo "\n";

// Test akses ke model Naive Bayes
$modelFile = 'models/naive_bayes.dat';
echo "Memeriksa file model: ";
if (file_exists($modelFile)) {
    echo "OK (" . filesize($modelFile) . " bytes)\n";
    
    echo "- Membaca file model: ";
    $modelData = file_get_contents($modelFile);
    if ($modelData !== false) {
        echo "BERHASIL (" . strlen($modelData) . " bytes)\n";
        
        echo "- Mencoba unserialize: ";
        try {
            $model = unserialize($modelData);
            if ($model === false) {
                echo "GAGAL\n";
            } else {
                echo "BERHASIL\n";
                echo "  - Class: " . get_class($model) . "\n";
            }
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "PHP ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "GAGAL\n";
    }
} else {
    echo "TIDAK DITEMUKAN\n";
}

echo "\n=== INFORMASI SISTEM ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . " detik\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
?> 