<?php
require_once '../includes/config.php';
$current_page = 'about';
$page_title = 'Tentang - Analisis Sentimen';
include '../includes/header.php';
?>

<?php include '../includes/sidebar.php'; ?>

<main class="flex-1 p-4 sm:p-6 lg:p-8">
    <?php include '../includes/mobile_nav.php'; ?>

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight mb-2">Tentang</h1>
            <p class="text-lg font-medium opacity-70">Informasi analisis sentimen dan panduan penggunaan aplikasi.</p>
        </header>

        <!-- Main Content -->
        <div class="space-y-8">
            <!-- Intro Card -->
            <section class="card">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">info</span>
                    Apa itu Analisis Sentimen?
                </h2>
                <p class="text-base leading-relaxed">
                    Analisis sentimen adalah proses menganalisis teks digital untuk menentukan apakah emosi atau perasaan yang terkandung di dalamnya positif, negatif, atau netral. Aplikasi ini menggunakan teknologi Machine Learning untuk melakukan klasifikasi tersebut secara otomatis.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="p-4 border-2 border-black bg-green-50 shadow-[4px_4px_0_0_#000] text-center">
                        <span class="material-symbols-outlined text-4xl text-green-600 mb-2">sentiment_satisfied</span>
                        <h3 class="font-bold text-lg mb-1">Sentimen Positif</h3>
                        <p class="text-sm opacity-80">Mengekspresikan perasaan bahagia, puas, atau setuju</p>
                    </div>
                    <div class="p-4 border-2 border-black bg-yellow-50 shadow-[4px_4px_0_0_#000] text-center">
                        <span class="material-symbols-outlined text-4xl text-yellow-600 mb-2">sentiment_neutral</span>
                        <h3 class="font-bold text-lg mb-1">Sentimen Netral</h3>
                        <p class="text-sm opacity-80">Mengekspresikan pernyataan faktual atau tidak memiliki muatan emosi</p>
                    </div>
                    <div class="p-4 border-2 border-black bg-red-50 shadow-[4px_4px_0_0_#000] text-center">
                        <span class="material-symbols-outlined text-4xl text-red-600 mb-2">sentiment_dissatisfied</span>
                        <h3 class="font-bold text-lg mb-1">Sentimen Negatif</h3>
                        <p class="text-sm opacity-80">Mengekspresikan perasaan tidak senang, kecewa, atau tidak setuju</p>
                    </div>
                </div>
            </section>

            <!-- Tech Stack -->
            <section class="card">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">code</span>
                    Teknologi
                </h2>
                <p class="mb-6">Aplikasi ini dibangun menggunakan berbagai teknologi modern untuk memastikan akurasi dan kecepatan:</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 border-2 border-black bg-gray-50">
                        <h3 class="font-bold mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">psychology</span>
                            Preprocessing
                        </h3>
                        <p class="text-sm opacity-70">Convert Emoji, Case Folding, Tokenisasi, Stopword Removal, Stemming (Sastrawi).</p>
                    </div>
                    <div class="p-4 border-2 border-black bg-gray-50">
                        <h3 class="font-bold mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">model_training</span>
                            Machine Learning
                        </h3>
                        <p class="text-sm opacity-70">Naive Bayes Classifier dan Count Vectorizer untuk klasifikasi teks.</p>
                    </div>
                    <div class="p-4 border-2 border-black bg-gray-50">
                        <h3 class="font-bold mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">analytics</span>
                            Visualisasi
                        </h3>
                        <p class="text-sm opacity-70">Diagram interaktif untuk menampilkan distribusi sentimen dan statistik.</p>
                    </div>
                </div>
            </section>

            <!-- Guide -->
            <section class="card">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined">menu_book</span>
                    Panduan Penggunaan
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-primary">
                            <span class="material-symbols-outlined">looks_one</span>
                            Training Model
                        </h3>
                        <ol class="space-y-4 list-decimal list-inside font-medium opacity-80 pl-2">
                            <li>Buka halaman <strong>Training</strong>.</li>
                            <li>Upload file dataset format CSV.</li>
                            <li>Klik tombol <strong>Upload & Train</strong>.</li>
                            <li>Tunggu proses selesai.</li>
                        </ol>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-primary">
                            <span class="material-symbols-outlined">looks_two</span>
                            Analisis & Hasil
                        </h3>
                        <ol class="space-y-4 list-decimal list-inside font-medium opacity-80 pl-2">
                            <li>Buka halaman <strong>Analisis</strong> atau Dashboard.</li>
                            <li>Masukkan teks atau lihat hasil training.</li>
                            <li>Sistem menampilkan sentimen (Positif/Negatif/Netral).</li>
                            <li>Lihat detail skor akurasi dan visualisasi.</li>
                        </ol>
                    </div>
                </div>

                <div class="mt-8 p-4 border-2 border-black bg-blue-50 flex gap-4 items-start">
                    <span class="material-symbols-outlined text-blue-700 text-3xl">lightbulb</span>
                    <div>
                        <h4 class="font-bold text-blue-900 mb-1">Tips</h4>
                        <p class="text-blue-800 text-sm">Gunakan dataset berkualitas dengan jumlah yang cukup (minimal 50-100 data) agar model pembelajaran mesin dapat mengenali pola bahasa dengan lebih akurat.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>