<?php
// Gunakan header Tailwind untuk konsistensi gaya di seluruh halaman
$page_title = 'Analisis Sentimen - Beranda';
include 'includes/header.php';
?>

<main class="flex-1">
    <?php include 'includes/mobile_nav.php'; ?>
    <section class="relative overflow-hidden py-16 sm:py-24">
        <div class="absolute inset-0 -z-10 opacity-40 dark:opacity-30" aria-hidden="true">
            <div class="h-64 bg-gradient-to-r from-primary/20 via-secondary/20 to-accent/20 blur-2xl"></div>
        </div>
        <div class="container-max px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight mb-4">Analisis Sentimen Bahasa Indonesia</h1>
                    <p class="text-base sm:text-lg text-muted mb-6">Platform untuk memahami emosi dan pendapat dalam teks berbahasa Indonesia yang didukung machine learning dan pipeline preprocessing yang kaya fitur.</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="pages/train.php" class="btn btn-primary transition-base hover-elevate focus-ring">
                            <span class="material-symbols-outlined">model_training</span>
                            Mulai Training
                        </a>
                        <a href="pages/about.php" class="btn btn-outline transition-base focus-ring">
                            <span class="material-symbols-outlined">info</span>
                            Pelajari Selengkapnya
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="card surface dark:surface-dark transition-base">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="material-symbols-outlined text-3xl text-primary">sentiment_satisfied</span>
                            <strong>Sentiment AI</strong>
                        </div>
                        <p class="text-sm text-muted">Desain baru yang konsisten, responsif, dan aksesibel di seluruh halaman.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-10">
        <div class="container-max px-4">
            <header class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-extrabold">Fitur Utama</h2>
                <p class="text-sm text-muted">Apa yang bisa Anda lakukan dengan aplikasi ini</p>
            </header>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <article class="card surface dark:surface-dark transition-base hover-elevate">
                    <div class="feature-icon mb-3">
                        <span class="material-symbols-outlined">dashboard</span>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Modern Dashboard</h3>
                    <p class="text-sm text-muted">Dashboard dengan visualisasi interaktif dan analisis sentimen real-time.</p>
                    <a href="pages/dashboard.php" class="btn btn-outline mt-4">Buka Dashboard</a>
                </article>
                <article class="card surface dark:surface-dark transition-base hover-elevate">
                    <div class="feature-icon mb-3">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Analisis Teks</h3>
                    <p class="text-sm text-muted">Masukkan teks untuk mengetahui sentimen yang terkandung.</p>
                    <a href="pages/predict.php" class="btn btn-outline mt-4">Coba Sekarang</a>
                </article>
                <article class="card surface dark:surface-dark transition-base hover-elevate">
                    <div class="feature-icon mb-3">
                        <span class="material-symbols-outlined">model_training</span>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Training Model</h3>
                    <p class="text-sm text-muted">Upload dataset untuk melatih model sentimen Anda sendiri.</p>
                    <a href="pages/train.php" class="btn btn-outline mt-4">Latih Model</a>
                </article>
            </div>
        </div>
    </section>

    <footer class="mt-8 py-6">
        <div class="container-max px-4 text-center">
            <hr class="opacity-10 mb-4" />
            <small class="opacity-70">© <?php echo date('Y'); ?> Sentiment AI — Analisis Sentimen Indonesia</small>
        </div>
    </footer>
</main>

<?php include 'includes/footer.php'; ?>