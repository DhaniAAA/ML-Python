<?php
/**
 * Kelas NaiveBayes
 * Mengimplementasikan algoritma Naive Bayes untuk klasifikasi teks
 */
class NaiveBayes {
    private $vocabulary;
    private $class_word_counts;
    private $class_doc_counts;
    private $total_docs;
    private $lexicon;
    
    public function __construct() {
        $this->vocabulary = array();
        $this->class_word_counts = array();
        $this->class_doc_counts = array();
        $this->total_docs = 0;
        // Load lexicon
        $this->lexicon = json_decode(file_get_contents('../data/lexicon/lexicon.txt'), true);
    }
    
    public function train($documents, $labels) {
        $this->total_docs = count($documents);
        
        foreach ($documents as $idx => $doc) {
            $label = $labels[$idx];
            
            if (!isset($this->class_doc_counts[$label])) {
                $this->class_doc_counts[$label] = 0;
                $this->class_word_counts[$label] = array();
            }
            
            $this->class_doc_counts[$label]++;
            
            foreach ($doc as $word) {
                if (!isset($this->class_word_counts[$label][$word])) {
                    $this->class_word_counts[$label][$word] = 0;
                }
                $this->class_word_counts[$label][$word]++;
                $this->vocabulary[$word] = true;
            }
        }
    }
    
    public function predict($document) {
        $scores = array();
        $vocab_size = count($this->vocabulary);
        
        foreach ($this->class_doc_counts as $class => $count) {
            $class_probability = log($count / $this->total_docs);
            $word_probabilities = 0;
            
            foreach ($document as $word) {
                $word_count = isset($this->class_word_counts[$class][$word]) 
                    ? $this->class_word_counts[$class][$word] : 0;
                $total_words = array_sum($this->class_word_counts[$class]);
                
                // Laplace smoothing
                $probability = ($word_count + 1) / ($total_words + $vocab_size);
                $word_probabilities += log($probability);
            }
            
            $scores[$class] = $class_probability + $word_probabilities;
        }
        
        arsort($scores);
        return array_key_first($scores);
    }
    
    public function getLexiconSentiment($text) {
        $words = explode(' ', $text);
        $score = 0;
        
        foreach ($words as $word) {
            if (isset($this->lexicon[$word])) {
                $score += $this->lexicon[$word];
            }
        }
        
        return $score;
    }
}