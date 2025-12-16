<?php
require_once '../includes/config.php';
$current_page = 'dashboard';
$page_title = 'Dashboard - Sentiment AI';

// Get statistics
$total_datasets = 0;
$total_models = 0;
$model_status = 'Ready';

if ($conn) {
    // Get total datasets
    $result = $conn->query("SELECT COUNT(*) as count FROM datasets");
    if ($result) {
        $total_datasets = $result->fetch_assoc()['count'];
    }

    // Get total models
    $result = $conn->query("SELECT COUNT(*) as count FROM models");
    if ($result) {
        $total_models = $result->fetch_assoc()['count'];
    }

    // Check if model files exist
    $vectorizer_path = __DIR__ . '/models/vectorizer.json';
    $model_path = __DIR__ . '/models/naive_bayes.dat';

    if (file_exists($vectorizer_path) && file_exists($model_path)) {
        $model_status = 'Ready';
    } else {
        $model_status = 'Not Trained';
    }
}

include '../includes/header.php';
?>

<?php include '../includes/sidebar.php'; ?>

<!-- Main -->
<main class="flex-1 p-4 sm:p-6 lg:p-8">
    <?php include '../includes/mobile_nav.php'; ?>

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <header class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight">Dashboard Overview</h1>
                    <p class="text-sm opacity-70 mt-2">Selamat datang di Sentiment Analysis Dashboard</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="predict.php" class="btn btn-primary">
                        <span class="material-symbols-outlined align-middle text-base sm:text-lg mr-1">search</span>
                        Analisis Baru
                    </a>
                    <button id="themeToggle" class="btn btn-outline" aria-label="Toggle theme">
                        <span class="material-symbols-outlined align-middle text-base sm:text-lg">dark_mode</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 mb-8">
            <!-- Total Datasets -->
            <article class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Total Datasets</h2>
                    <span class="material-symbols-outlined text-3xl opacity-50">dataset</span>
                </div>
                <p class="text-4xl font-extrabold"><?php echo $total_datasets; ?></p>
                <p class="text-sm opacity-70 mt-2">Dataset tersimpan</p>
            </article>

            <!-- Total Models -->
            <article class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Total Models</h2>
                    <span class="material-symbols-outlined text-3xl opacity-50">analytics</span>
                </div>
                <p class="text-4xl font-extrabold"><?php echo $total_models; ?></p>
                <p class="text-sm opacity-70 mt-2">Models trained</p>
            </article>

            <!-- Quick Analysis -->
            <article class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Status Model</h2>
                    <span class="material-symbols-outlined text-3xl opacity-50">check_circle</span>
                </div>
                <p class="text-4xl font-extrabold <?php echo $model_status === 'Ready' ? 'text-green-600' : 'text-yellow-600'; ?>"><?php echo $model_status; ?></p>
                <p class="text-sm opacity-70 mt-2"><?php echo $model_status === 'Ready' ? 'Siap untuk analisis' : 'Perlu training'; ?></p>
            </article>
        </section>

        <!-- Quick Actions -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
            <!-- Analyze Text -->
            <article class="card">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">search</span>
                    Analisis Sentimen Teks
                </h2>
                <p class="opacity-70 mb-4">Analisis sentimen dari teks bahasa Indonesia secara real-time dengan akurasi tinggi.</p>
                <a href="predict.php" class="btn btn-primary self-start">
                    Mulai Analisis
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </article>

            <!-- Train Model -->
            <article class="card">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">model_training</span>
                    Training Model
                </h2>
                <p class="opacity-70 mb-4">Upload dataset dan latih model machine learning untuk meningkatkan akurasi analisis.</p>
                <a href="train.php" class="btn btn-primary self-start">
                    Upload Dataset
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </article>
        </section>

        <!-- Features -->
        <section class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <article class="card text-center">
                <span class="material-symbols-outlined text-5xl mb-4 inline-block opacity-70">speed</span>
                <h3 class="text-lg font-bold mb-2">Analisis Cepat</h3>
                <p class="text-sm opacity-70">Hasil analisis dalam hitungan milidetik</p>
            </article>

            <article class="card text-center">
                <span class="material-symbols-outlined text-5xl mb-4 inline-block opacity-70">translate</span>
                <h3 class="text-lg font-bold mb-2">Bahasa Indonesia</h3>
                <p class="text-sm opacity-70">Dioptimalkan untuk teks bahasa Indonesia</p>
            </article>

            <article class="card text-center">
                <span class="material-symbols-outlined text-5xl mb-4 inline-block opacity-70">psychology</span>
                <h3 class="text-lg font-bold mb-2">Machine Learning</h3>
                <p class="text-sm opacity-70">Powered by Naive Bayes algorithm</p>
            </article>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
