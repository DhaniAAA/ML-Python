<?php
require_once '../includes/memory_helper.php';
require_once '../vendor/autoload.php';
require_once '../includes/config.php';
require_once '../lib/Preprocessing.php';

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\Classification\NaiveBayes;
use Phpml\FeatureExtraction\TfIdfTransformer;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['csv_file']) || !isset($_POST['dataset_name'])) {
        throw new Exception('Missing required fields');
    }

    $dataset_name = trim($_POST['dataset_name']);
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'csv') {
        throw new Exception('Only CSV files are allowed');
    }

    // Create upload directory if not exists
    $upload_dir = __DIR__ . '/data/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid('dataset_') . '.csv';
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save file');
    }

    // Read and validate CSV
    $handle = fopen($filepath, 'r');
    if (!$handle) {
        throw new Exception('Cannot read CSV file');
    }

    $header = fgetcsv($handle);
    
    // Cari index kolom Teks (case insensitive)
    $textIndex = -1;
    foreach ($header as $index => $col) {
        if (strtolower(trim($col)) === 'teks' || strtolower(trim($col)) === 'text') {
            $textIndex = $index;
            break;
        }
    }
    
    if ($textIndex === -1) {
        fclose($handle);
        unlink($filepath);
        throw new Exception('CSV harus memiliki kolom "Teks"');
    }

    // Load lexicon untuk labeling otomatis
    $lexicon_path = __DIR__ . '/../data/lexicon/lexicon.txt';
    if (!file_exists($lexicon_path)) {
        fclose($handle);
        unlink($filepath);
        throw new Exception('File lexicon tidak ditemukan');
    }
    
    $lexicon = file($lexicon_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lexicon_scores = [];
    
    foreach ($lexicon as $line) {
        $parts = explode(",", $line);
        if (count($parts) === 2) {
            $lexicon_scores[trim($parts[0])] = (int)trim($parts[1]);
        }
    }
    unset($lexicon);

    $samples = [];
    $labels = [];
    $preprocessor = new Preprocessing();
    $processed_texts = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[$textIndex]) || empty(trim($row[$textIndex]))) {
            continue;
        }

        $text = $row[$textIndex];

        // Preprocessing
        $text = $preprocessor->convertEmoji($text);
        $text = $preprocessor->convertEmoticons($text);
        $cleaned_text = $preprocessor->cleanText($text);

        if (empty(trim($cleaned_text))) {
            continue;
        }

        $tokens = $preprocessor->tokenize($cleaned_text);
        $tokens = $preprocessor->removeStopwords($tokens);
        $stemmed_tokens = $preprocessor->stemWords($tokens);
        $stemmed_text = implode(' ', $stemmed_tokens);

        if (empty(trim($stemmed_text))) {
            continue;
        }

        // Skip duplikat
        if (in_array($cleaned_text, $processed_texts)) {
            continue;
        }
        $processed_texts[] = $cleaned_text;

        // Labeling otomatis berdasarkan lexicon
        $total_score = 0;
        foreach ($stemmed_tokens as $token) {
            if (isset($lexicon_scores[$token])) {
                $total_score += $lexicon_scores[$token];
            }
        }

        // Tentukan sentiment
        $sentiment = 'neutral';
        if ($total_score > 0) {
            $sentiment = 'positive';
        } elseif ($total_score < 0) {
            $sentiment = 'negative';
        }

        $samples[] = $stemmed_text;
        $labels[] = $sentiment;
    }
    fclose($handle);

    if (count($samples) < 10) {
        unlink($filepath);
        throw new Exception('Dataset too small. Need at least 10 valid samples');
    }

    // Save to database
    $stmt = $conn->prepare("INSERT INTO datasets (filename, original_filename, sample_count, status, created_at) VALUES (?, ?, ?, 'completed', NOW())");
    $sample_count = count($samples);
    $stmt->bind_param("ssi", $filename, $dataset_name, $sample_count);
    $stmt->execute();
    $dataset_id = $conn->insert_id;
    $stmt->close();

    // Train model
    $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
    $vectorizer->fit($samples);
    $vectorizer->transform($samples);

    $transformer = new TfIdfTransformer();
    $transformer->fit($samples);
    $transformer->transform($samples);

    $classifier = new NaiveBayes();
    $classifier->train($samples, $labels);

    // Save model
    $model_dir = __DIR__ . '/models/';
    if (!is_dir($model_dir)) {
        mkdir($model_dir, 0777, true);
    }

    file_put_contents($model_dir . 'vectorizer.json', json_encode([
        'vocabulary' => $vectorizer->getVocabulary(),
        'samples' => $samples
    ]));

    file_put_contents($model_dir . 'naive_bayes.dat', serialize($classifier));

    // Save model info to database (2 records: vectorizer dan naive_bayes)
    $vectorizer_filename = 'vectorizer.json';
    $nb_filename = 'naive_bayes.dat';
    
    $stmt = $conn->prepare("INSERT INTO models (dataset_id, filename, model_type, created_at) VALUES (?, ?, ?, NOW())");
    
    // Insert vectorizer model
    $model_type = 'vectorizer';
    $stmt->bind_param("iss", $dataset_id, $vectorizer_filename, $model_type);
    $stmt->execute();
    
    // Insert naive bayes model
    $model_type = 'naive_bayes';
    $stmt->bind_param("iss", $dataset_id, $nb_filename, $model_type);
    $stmt->execute();
    
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Dataset uploaded and model trained successfully',
        'dataset_id' => $dataset_id,
        'samples_count' => count($samples)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
