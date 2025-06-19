# Aplikasi Analisis Sentimen

Aplikasi web untuk menganalisis sentimen teks menggunakan algoritma Naive Bayes dan CountVectorizer.

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
- Composer
- Web server (Apache/Nginx)

## Instalasi

1. Clone repositori ini
2. Install dependensi dengan Composer:
   ```bash
   composer install
   ```
3. Pastikan direktori `data` dan `models` dapat ditulis oleh web server
4. Buat file konfigurasi database di `config.php`
5. Import skema database dari `database.sql`

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
├── assets/
│   ├── css/
│   └── js/
├── data/
│   ├── emoticons.json
│   ├── lexicon.json
│   └── stopwords.txt
├── lib/
│   ├── Preprocessing.php
│   ├── CountVectorizer.php
│   └── NaiveBayes.php
├── models/
├── config.php
├── index.php
├── analyze.php
└── composer.json
```

## Lisensi

MIT License 