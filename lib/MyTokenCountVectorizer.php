<?php

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\Tokenizer;
use Phpml\FeatureExtraction\StopWords;

/**
 * MyTokenCountVectorizer - extends TokenCountVectorizer with ability to set vocabulary
 */
class MyTokenCountVectorizer extends TokenCountVectorizer
{
    private $customVocabulary = [];

    /**
     * Set vocabulary manually (for use with saved models)
     * 
     * @param array $vocabulary The vocabulary as word => index
     * @return void
     */
    public function setVocabulary(array $vocabulary): void
    {
        $this->customVocabulary = $vocabulary;
    }

    /**
     * Override transform to use custom vocabulary
     * 
     * @param array $samples The samples to transform
     * @param array|null $targets The targets (not used)
     * @return void
     */
    public function transform(array &$samples, ?array &$targets = null): void
    {
        $result = [];
        
        foreach ($samples as $sample) {
            $counts = [];
            $tokens = $this->getTokenizer()->tokenize($sample);
            
            // Initialize counts for all vocabulary terms
            foreach ($this->customVocabulary as $term => $index) {
                $counts[$index] = 0;
            }
            
            // Count tokens that exist in vocabulary
            foreach ($tokens as $token) {
                if (isset($this->customVocabulary[$token])) {
                    $index = $this->customVocabulary[$token];
                    $counts[$index]++;
                }
            }
            
            // Sort by index
            ksort($counts);
            $result[] = $counts;
        }
        
        // Replace the original samples with the transformed ones (by reference)
        $samples = $result;
    }
    
    /**
     * Get the tokenizer
     * 
     * @return Tokenizer
     */
    private function getTokenizer()
    {
        // Use reflection to access private tokenizer property
        $reflection = new \ReflectionClass(TokenCountVectorizer::class);
        $property = $reflection->getProperty('tokenizer');
        $property->setAccessible(true);
        
        return $property->getValue($this);
    }
} 