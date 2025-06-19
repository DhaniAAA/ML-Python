<?php
// Script untuk memeriksa kata-kata dalam vocabulary model

// Load vectorizer
$vectorizerPath = 'models/vectorizer.json';
if (!file_exists($vectorizerPath)) {
    die("File model vectorizer tidak ditemukan di $vectorizerPath.");
}

echo "Membaca file vectorizer.json...\n";
$vectorizerJson = file_get_contents($vectorizerPath);
if (!$vectorizerJson) {
    die("Gagal membaca file vectorizer.");
}

echo "Ukuran file: " . strlen($vectorizerJson) . " bytes\n";

echo "Contoh isi awal file (100 karakter pertama):\n";
echo substr($vectorizerJson, 0, 100) . "...\n\n";

$data = json_decode($vectorizerJson, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error parsing JSON: " . json_last_error_msg());
}

// Format yang mungkin berbeda
if (isset($data['vocabulary']) && is_array($data['vocabulary'])) {
    // Format: {"vocabulary": ["kata1", "kata2", ...]}
    $vocab = $data['vocabulary'];
    echo "Format vocabulary: array of strings\n";
    echo "Jumlah kata: " . count($vocab) . "\n\n";
    
    echo "10 kata pertama:\n";
    for ($i = 0; $i < min(10, count($vocab)); $i++) {
        echo ($i + 1) . ". " . $vocab[$i] . "\n";
    }
} elseif (isset($data['vocabulary']) && is_array($data['vocabulary'])) {
    // Format: {"vocabulary": {"kata1": 0, "kata2": 1, ...}}
    $vocab = $data['vocabulary'];
    echo "Format vocabulary: associative array\n";
    echo "Jumlah kata: " . count($vocab) . "\n\n";
    
    echo "10 kata pertama:\n";
    $i = 0;
    foreach ($vocab as $word => $index) {
        if ($i++ < 10) {
            echo "$i. $word (index: $index)\n";
        } else {
            break;
        }
    }
} else {
    // Coba format lain
    if (is_array($data)) {
        echo "Format tidak dikenal. Array level atas memiliki kunci:\n";
        print_r(array_keys($data));
    } else {
        echo "Format tidak dikenal. Tipe data: " . gettype($data) . "\n";
        // Coba cetak data mentah
        echo "Cetak awal data:\n";
        print_r(substr(print_r($data, true), 0, 500));
    }
}

// Contoh kalimat yang bisa digunakan
echo "\nContoh kalimat untuk pengujian:\n";
if (isset($vocab) && is_array($vocab)) {
    if (is_int(key($vocab))) {
        // Array
        $wordList = $vocab;
    } else {
        // Associative array
        $wordList = array_keys($vocab);
    }
    
    // Buat 3 contoh kalimat
    for ($i = 0; $i < 3; $i++) {
        $sentence = [];
        // Pilih 3-5 kata random
        $wordCount = rand(3, 5);
        for ($j = 0; $j < $wordCount; $j++) {
            $randomIndex = array_rand($wordList);
            $sentence[] = $wordList[$randomIndex];
        }
        echo ($i + 1) . ". " . implode(' ', $sentence) . "\n";
    }
}
?> 