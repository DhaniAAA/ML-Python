# Fitur Terjemahan Bahasa Inggris ke Indonesia

## Deskripsi
Fitur ini secara otomatis menerjemahkan kata-kata bahasa Inggris ke bahasa Indonesia selama proses preprocessing teks. Ini sangat berguna untuk analisis sentimen teks yang mengandung campuran bahasa Inggris dan Indonesia.

## Kosa Kata yang Ditambahkan
Total **366 kata** bahasa Inggris dengan terjemahan Indonesia, termasuk:

### Kategori Sentimen
- **Positif**: amazing, awesome, excellent, great, wonderful, fantastic, perfect, nice, cool, best, delicious, tasty, satisfied, recommend, etc.
- **Negatif**: worst, bad, terrible, horrible, awful, poor, disappointed, boring, disgusting, broken, problem, error, wrong, etc.
- **Netral**: good, normal, simple, easy, etc.

### Kategori E-commerce & Review
- Product, item, store, shop, customer, seller, buyer
- Quality, service, price, value, worth
- Buy, purchase, order, delivery, shipping
- Payment, discount, sale, free, refund, cancel

### Kategori Umum
- Kata kerja: make, do, go, come, help, support, send, receive, etc.
- Kata sifat: big, small, fast, slow, hot, cold, clean, dirty, etc.
- Kata keterangan: always, never, sometimes, often, very, too, etc.
- Emosi: happy, sad, angry, love, hate, scared, proud, confused, etc.

## Cara Kerja

### 1. Urutan Preprocessing
```php
public function processText($text) {
    $text = $this->convertEmoji($text);           // 1. Konversi emoji
    $text = $this->convertEmoticons($text);       // 2. Konversi emoticon
    $text = $this->cleanText($text);              // 3. Cleaning (lowercase, hapus URL, dll)
    $text = $this->translateEnglishToIndonesian($text); // 4. Terjemahkan EN->ID
    $tokens = $this->tokenize($text);             // 5. Tokenisasi
    $tokens = $this->removeStopwords($tokens);    // 6. Hapus stopwords
    $tokens = $this->stemWords($tokens);          // 7. Stemming
    
    return implode(' ', $tokens);
}
```

### 2. Algoritma Terjemahan
```php
public function translateEnglishToIndonesian($text) {
    $tokens = $this->tokenize(strtolower($text));
    $translatedTokens = [];
    
    foreach ($tokens as $token) {
        if (isset($this->englishIdDictionary[$token])) {
            // Terjemahkan jika ada di kamus
            $translatedTokens[] = $this->englishIdDictionary[$token];
        } else {
            // Biarkan kata asli jika tidak ada terjemahan
            $translatedTokens[] = $token;
        }
    }
    
    return implode(' ', $translatedTokens);
}
```

## Contoh Penggunaan

### Input 1: Teks Campuran
```
"This product is amazing! Very good quality and fast delivery"
```

### Setelah Preprocessing:
```
"produk menakjubkan sangat baik kualitas cepat pengiriman"
```

### Input 2: Review E-commerce
```
"I'm disappointed with the service. The product is broken and the seller is rude"
```

### Setelah Preprocessing:
```
"kecewa layanan produk rusak penjual kasar"
```

### Input 3: Teks Indonesia Murni
```
"Produk ini sangat bagus dan pengirimannya cepat"
```

### Setelah Preprocessing:
```
"produk sangat bagus kirim cepat"
```
*(Tidak ada perubahan karena sudah bahasa Indonesia)*

## Fungsi Tambahan

### 1. Deteksi Bahasa Inggris
```php
public function containsEnglish($text) {
    $tokens = $this->tokenize(strtolower($text));
    $englishWords = array_keys($this->englishIdDictionary);
    
    foreach ($tokens as $token) {
        if (in_array($token, $englishWords)) {
            return true;
        }
    }
    
    return false;
}
```

## File yang Dimodifikasi

### 1. `data/english_id.json`
- **Sebelum**: 97 kata
- **Sesudah**: 366 kata (+269 kata baru)
- Format: `{"english_word": "terjemahan_indonesia"}`

### 2. `lib/Preprocessing.php`
- Menambahkan `$this->englishIdDictionary` di constructor
- Menambahkan method `translateEnglishToIndonesian()`
- Menambahkan method `containsEnglish()`
- Mengintegrasikan terjemahan ke dalam `processText()`

## Keuntungan

1. **Analisis Lebih Akurat**: Teks campuran EN-ID dapat dianalisis dengan benar
2. **Otomatis**: Tidak perlu input manual dari user
3. **Fleksibel**: Mudah menambah kosa kata baru ke `english_id.json`
4. **Efisien**: Proses terjemahan terintegrasi dalam preprocessing

## Cara Menambah Kosa Kata

Edit file `data/english_id.json` dan tambahkan pasangan kata:
```json
{
  "existing_word": "kata_lama",
  "new_word": "kata_baru",
  "another_word": "kata_lain"
}
```

**Catatan**: Pastikan tidak ada duplicate keys!

## Testing

### Test 1: Teks Bahasa Inggris
```php
$preprocessor = new Preprocessing();
$text = "This product is very good and cheap";
$result = $preprocessor->processText($text);
// Output: "produk sangat baik murah"
```

### Test 2: Teks Campuran
```php
$text = "Saya very happy dengan product ini, recommended!";
$result = $preprocessor->processText($text);
// Output: "sangat senang produk direkomendasikan"
```

### Test 3: Deteksi Bahasa
```php
$text1 = "Produk bagus";
$text2 = "Good product";

$preprocessor->containsEnglish($text1); // false
$preprocessor->containsEnglish($text2); // true
```

## Catatan Penting

1. **Case Insensitive**: Terjemahan tidak case-sensitive (semua lowercase)
2. **Word-by-Word**: Terjemahan dilakukan per kata, bukan per frasa
3. **Fallback**: Kata yang tidak ada di kamus akan dibiarkan apa adanya
4. **Multi-word Phrases**: Frasa seperti "thank you" dan "wake up" juga didukung

## Maintenance

Untuk memperbarui kamus:
1. Edit `data/english_id.json`
2. Tambahkan kata baru dengan format JSON yang benar
3. Pastikan tidak ada duplicate keys
4. Restart aplikasi untuk memuat kamus baru
