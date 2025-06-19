<?php
// Aktifkan error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Import kelas
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;

// Test loading library
echo "Mencoba me-load library...\n";
try {
    require_once 'vendor/autoload.php';
    echo "✓ Berhasil me-load vendor/autoload.php\n";
    
    require_once 'lib/Preprocessing.php';
    echo "✓ Berhasil me-load lib/Preprocessing.php\n";
    
    // Test initialize object
    echo "\nMencoba inisialisasi objek...\n";
    
    $preprocessor = new Preprocessing();
    echo "✓ Berhasil membuat objek Preprocessing\n";
    
    $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
    echo "✓ Berhasil membuat objek TokenCountVectorizer\n";
    
    echo "\nSemua test berhasil!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "PHP ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?> 