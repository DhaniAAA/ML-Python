# Aplikasi Analisis Sentimen

Aplikasi web untuk menganalisis sentimen teks menggunakan algoritma Naive Bayes dan CountVectorizer. Aplikasi ini menggunakan PHP dan Python untuk analisis sentimen bahasa Indonesia.

## Fitur

- Preprocessing teks:
  - Konversi emoji ke teks
  - Pembersihan data
  - Penghapusan stopwords
  - Tokenisasi
  - Stemming menggunakan Sastrawi
- Ekstraksi fitur menggunakan CountVectorizer
- Klasifikasi sentimen menggunakan Naive Bayes
- Labeling menggunakan lexicon
- Visualisasi:
  - Diagram batang probabilitas sentimen
  - Word cloud

## Persyaratan Sistem

- PHP >= 7.4
- Python >= 3.8
- Composer
- Web server (Apache/Nginx)
- Dependensi Python:
  - numpy >= 1.24.0
  - pandas >= 2.0.0
  - scikit-learn >= 1.3.0
  - matplotlib >= 3.7.0
  - seaborn >= 0.12.0
  - mysql-connector-python >= 8.0.0

## Instalasi

1. Clone repositori ini
2. Install dependensi dengan Composer:
   ```bash
   composer install
   ```
3. Install dependensi Python:
   ```bash
   pip install -r requirements.txt
   ```
4. Pastikan direktori `data` dan `models` dapat ditulis oleh web server
5. Buat file konfigurasi database di `config.php`
6. Import skema database dari `database.sql`

## Penggunaan

1. Buka aplikasi di browser
2. Masukkan teks yang ingin dianalisis
3. Klik tombol "Analisis"
4. Hasil analisis akan ditampilkan dalam bentuk:
   - Label sentimen (positif/negatif)
   - Skor sentimen
   - Diagram probabilitas
   - Word cloud

## Struktur Direktori

```
.
├── assets/           # Asset statis (CSS, JS, gambar)
│   ├── css/
│   └── js/
├── data/            # Data dan resource
│   ├── emoji_convert.json
│   ├── emoticons.json
│   ├── english_id.json
│   ├── lexicon/
│   ├── stopwords_id.txt
│   ├── testing/     # File pengujian (tidak di-upload ke GitHub)
│   └── uploads/
├── database/        # Database schema
│   └── schema.sql
├── includes/        # File konfigurasi dan helper
│   ├── config.php
│   ├── memory_helper.php
│   └── nav_template.php
├── lib/             # Library PHP custom
│   ├── CountVectorizer.php
│   ├── MyTokenCountVectorizer.php
│   ├── NaiveBayes.php
│   ├── Preprocessing.php
│   └── Visualization.php
├── models/          # Model machine learning (file besar tidak di-upload ke GitHub)
├── pages/           # Halaman aplikasi
│   ├── about.php
│   ├── analyze.php
│   ├── dataset.php 
│   ├── download_dataset.php
│   ├── predict.php
│   ├── result.php
│   └── train.php
├── scripts/         # Python scripts
│   ├── predict.py
│   └── train.py
├── index.php        # Halaman utama
└── composer.json
```

## Catatan Penting

Beberapa file tidak disertakan dalam repositori GitHub karena ukurannya yang besar atau karena merupakan file pengujian:
1. File model machine learning di direktori `models/`:
   - `naive_bayes.dat`
   - `naive_bayes.pkl`
   - `vectorizer.pkl`

2. File pengujian di direktori `data/testing/` dan file test di root direktori.

File-file ini akan dibuat secara otomatis saat menjalankan proses training atau dapat diminta secara terpisah jika diperlukan.

## Lisensi

MIT License