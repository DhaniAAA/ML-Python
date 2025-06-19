import pickle
import numpy as np
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.naive_bayes import GaussianNB

def load_model():
    try:
        # Load vectorizer
        with open('models/vectorizer.pkl', 'rb') as f:
            vectorizer = pickle.load(f)
        
        # Load classifier
        with open('models/naive_bayes.pkl', 'rb') as f:
            classifier = pickle.load(f)
        
        return vectorizer, classifier
    except Exception as e:
        print(f"Error loading model: {e}")
        return None, None

def preprocess_text(text):
    if not text or text.isspace():
        return ''
    return str(text).lower().strip()

def predict_sentiment(text):
    # Load model dan vectorizer
    vectorizer, classifier = load_model()
    if vectorizer is None or classifier is None:
        return {
            'error': 'Model tidak dapat dimuat'
        }
    
    try:
        # Preprocess text
        processed_text = preprocess_text(text)
        if not processed_text:
            return {
                'error': 'Teks kosong setelah preprocessing'
            }
        
        # Vectorize text dan convert ke dense array
        X = vectorizer.transform([processed_text]).toarray()
        
        # Predict
        sentiment = classifier.predict(X)[0]
        
        # Get probabilities
        probabilities = classifier.predict_proba(X)[0]
        
        # Create result dictionary
        result = {
            'sentiment': sentiment,
            'probabilities': {
                'positive': float(probabilities[list(classifier.classes_).index('positive')]),
                'negative': float(probabilities[list(classifier.classes_).index('negative')]),
                'neutral': float(probabilities[list(classifier.classes_).index('neutral')])
            },
            'method': 'model',
            'processed_text': processed_text
        }
        
        return result
    except Exception as e:
        return {
            'error': f'Error dalam prediksi: {str(e)}'
        }

if __name__ == "__main__":
    # Test prediction
    test_texts = [
        "Saya sangat senang dengan pelayanannya!",
        "Produknya biasa saja",
        "Sangat mengecewakan, tidak akan beli lagi"
    ]
    
    print("\nTesting predictions:")
    for text in test_texts:
        result = predict_sentiment(text)
        print(f"\nText: {text}")
        if 'error' in result:
            print(f"Error: {result['error']}")
        else:
            print(f"Processed text: {result['processed_text']}")
            print(f"Sentiment: {result['sentiment']}")
            print("Probabilities:")
            for sentiment, prob in result['probabilities'].items():
                print(f"  {sentiment}: {prob:.4f}") 