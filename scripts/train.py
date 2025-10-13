import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.naive_bayes import GaussianNB
# from sklearn.naive_bayes import MultinomialNB
from sklearn.metrics import classification_report, confusion_matrix
import matplotlib.pyplot as plt
import seaborn as sns
import pickle
import json
import os
import mysql.connector
from mysql.connector import Error

def connect_to_database():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='sentiment_analysis',
            user='root',
            password=''
        )
        return connection
    except Error as e:
        print(f"Error connecting to database: {e}")
        return None

def load_data(dataset_id):
    connection = connect_to_database()
    if connection is None:
        raise Exception("Could not connect to database")
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("""
            SELECT text, processed_text, sentiment 
            FROM dataset_items 
            WHERE dataset_id = %s 
            AND processed_text IS NOT NULL 
            AND processed_text != ''
            AND sentiment IN ('positive', 'negative', 'neutral')
        """, (dataset_id,))
        
        rows = cursor.fetchall()
        if not rows:
            raise Exception(f"No data found for dataset_id {dataset_id}")
            
        df = pd.DataFrame(rows)
        print(f"Loaded {len(df)} rows of data")
        print("Sentiment distribution:")
        print(df['sentiment'].value_counts())
        
        return df
    except Error as e:
        print(f"Error querying database: {e}")
        raise
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def preprocess_text(text):
    if pd.isna(text) or text == '':
        return ''
    # Gunakan processed_text yang sudah ada
    return str(text).lower().strip()

def train_model(dataset_id):
    print("Loading dataset...")
    df = load_data(dataset_id)
    
    if df.empty:
        print("No data found for dataset_id:", dataset_id)
        return
    
    # Preprocess text
    print("Preprocessing text...")
    df['processed_text'] = df['processed_text'].fillna('')
    
    # Hapus data dengan teks kosong
    df = df[df['processed_text'].str.strip() != '']
    print(f"Data after removing empty texts: {len(df)} rows")
    
    if len(df) == 0:
        print("No valid data after preprocessing")
        return
    
    # Define features (X) and target (y)
    X = df['processed_text']
    y = df['sentiment']
    
    # Split data
    print("Splitting dataset...")
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    
    # Create and train vectorizer
    print("Training vectorizer...")
    vectorizer = CountVectorizer(min_df=2, max_df=0.95)  # Tambahkan parameter untuk mengurangi noise
    X_train_vec = vectorizer.fit_transform(X_train)
    X_test_vec = vectorizer.transform(X_test)
    
    # Convert sparse matrix to dense array for GaussianNB
    X_train_dense = X_train_vec.toarray()
    X_test_dense = X_test_vec.toarray()
    
    print(f"Vocabulary size: {len(vectorizer.vocabulary_)}")
    print(f"Training data shape: {X_train_dense.shape}")
    print(f"Testing data shape: {X_test_dense.shape}")
    
    # Save vectorizer
    print("Saving vectorizer...")
    os.makedirs('models', exist_ok=True)
    with open('models/vectorizer.pkl', 'wb') as f:
        pickle.dump(vectorizer, f)
    
    # Train Naive Bayes
    print("Training Naive Bayes model...")
    classifier = GaussianNB()
    classifier.fit(X_train_dense, y_train)
    # classifier = MultinomialNB()
    # classifier.fit(X_train_vec, y_train)
    # Save model
    print("Saving model...")
    with open('models/naive_bayes.pkl', 'wb') as f:
        pickle.dump(classifier, f)
    
    # Evaluate model
    print("\nEvaluating model...")
    y_pred = classifier.predict(X_test_dense)
    
    # Generate and print detailed metrics
    print("\nDetailed Metrics:")
    print("Unique values in y_test:", np.unique(y_test))
    print("Unique values in y_pred:", np.unique(y_pred))
    
    # Generate classification report
    report = classification_report(y_test, y_pred)
    print("\nClassification Report:")
    print(report)
    
    # Create confusion matrix
    cm = confusion_matrix(y_test, y_pred)
    print("\nConfusion Matrix:")
    print(cm)
    
    # Save test data and classification report
    test_data = {
        'X_test': X_test.tolist(),
        'y_test': y_test.tolist(),
        'y_pred': y_pred.tolist(),
        'classification_report': report
    }
    
    os.makedirs('data/testing', exist_ok=True)
    with open(f'data/testing/test_data_{dataset_id}.json', 'w') as f:
        json.dump(test_data, f)
    
    # Plot confusion matrix with better visibility
    plt.figure(figsize=(10,8))
    sns.heatmap(cm, annot=True, fmt='d', cmap='Blues',
                xticklabels=sorted(y.unique()),
                yticklabels=sorted(y.unique()))
    plt.xlabel('Prediksi')
    plt.ylabel('Aktual')
    plt.title('Confusion Matrix Naive Bayes')
    
    # Save plot with better quality
    print("Saving confusion matrix plot...")
    plt.savefig('models/confusion_matrix.png', bbox_inches='tight', dpi=300)
    plt.close()
    
    print("\nTraining completed successfully!")

if __name__ == "__main__":
    import sys
    if len(sys.argv) != 2:
        print("Usage: python train.py <dataset_id>")
        sys.exit(1)
    
    dataset_id = sys.argv[1]
    try:
        train_model(dataset_id)
    except Exception as e:
        print(f"Error during training: {e}")
        sys.exit(1)