<?php
/**
 * Kelas CountVectorizer
 * Mengimplementasikan ekstraksi fitur dengan metode CountVectorizer
 */
class CountVectorizer {
    private $vocabulary;
    private $document_frequency;
    private $min_df;

    /**
     * Constructor
     * 
     * @param int $min_df Minimum document frequency
     */
    public function __construct($min_df = 1) {
        $this->vocabulary = array();
        $this->document_frequency = array();
        $this->min_df = $min_df;
    }

    /**
     * Membangun vocabulary dari corpus dokumen
     * 
     * @param array $documents Array berisi dokumen-dokumen teks
     * @return array Vocabulary yang sudah dibangun
     */
    public function fit($documents) {
        // Menghitung document frequency
        foreach ($documents as $doc) {
            $seen_words = array();
            foreach ($doc as $word) {
                if (!isset($seen_words[$word])) {
                    if (!isset($this->document_frequency[$word])) {
                        $this->document_frequency[$word] = 0;
                    }
                    $this->document_frequency[$word]++;
                    $seen_words[$word] = true;
                }
            }
        }
        
        // Filter vocabulary berdasarkan min_df
        foreach ($this->document_frequency as $word => $freq) {
            if ($freq >= $this->min_df) {
                $this->vocabulary[$word] = count($this->vocabulary);
            }
        }
        
        return $this;
    }

    /**
     * Mengubah dokumen menjadi vektor fitur
     * 
     * @param array $documents Array berisi dokumen-dokumen teks
     * @return array Matrix fitur
     */
    public function transform($documents) {
        $X = array();
        
        foreach ($documents as $doc) {
            $vector = array_fill(0, count($this->vocabulary), 0);
            
            foreach ($doc as $word) {
                if (isset($this->vocabulary[$word])) {
                    $vector[$this->vocabulary[$word]]++;
                }
            }
            
            $X[] = $vector;
        }
        
        return $X;
    }

    /**
     * Menggabungkan proses fit dan transform
     * 
     * @param array $documents Array berisi dokumen-dokumen teks
     * @return array Matrix fitur
     */
    public function fit_transform($documents) {
        $this->fit($documents);
        return $this->transform($documents);
    }

    /**
     * Mengambil vocabulary yang sudah dibangun
     * 
     * @return array Vocabulary
     */
    public function get_vocabulary() {
        return $this->vocabulary;
    }

    /**
     * Mengubah satu dokumen menjadi vektor fitur
     * 
     * @param string $text Teks dokumen
     * @return array Vektor fitur
     */
    public function transformSingle($text) {
        return $this->transform([$text])[0];
    }
    
    /**
     * Menyimpan model ke file
     * 
     * @param string $filepath Path file untuk menyimpan model
     * @return bool Status berhasil/gagal
     */
    public function saveModel($filepath) {
        $modelData = [
            'vocabulary' => $this->vocabulary,
            'document_frequency' => $this->document_frequency,
            'min_df' => $this->min_df
        ];
        
        return file_put_contents($filepath, serialize($modelData)) !== false;
    }
    
    /**
     * Memuat model dari file
     * 
     * @param string $filepath Path file model
     * @return bool Status berhasil/gagal
     */
    public function loadModel($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }
        
        $modelData = unserialize(file_get_contents($filepath));
        
        if (isset($modelData['vocabulary'])) {
            $this->vocabulary = $modelData['vocabulary'];
            $this->document_frequency = $modelData['document_frequency'] ?? [];
            $this->min_df = $modelData['min_df'] ?? 1;
            return true;
        }
        
        return false;
    }
}