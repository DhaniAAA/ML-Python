<?php
require_once '../includes/config.php';
$current_page = 'analyze';
$page_title = 'Analisis Sentimen - Sentiment AI';
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
                    <nav class="flex items-center text-sm opacity-70 mb-2" aria-label="Breadcrumb">
                        <a class="hover:underline" href="dashboard.php">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span aria-current="page">Analisis Sentimen</span>
                    </nav>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight">Analisis Sentimen Teks</h1>
                </div>
                <button id="themeToggle" class="px-3 sm:px-4 py-2 rounded-xl text-sm sm:text-base focus-ring transition 
                           bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/[.15]
                           shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                           dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]" aria-label="Toggle theme">
                    <span class="material-symbols-outlined align-middle text-base sm:text-lg">dark_mode</span>
                </button>
            </div>
        </header>

        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Grid -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
            <!-- Input Section -->
            <article class="xl:col-span-2 p-6 rounded-xl 
                            bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">edit_note</span>
                    Input Teks
                </h2>
                <textarea id="textInput" rows="8" 
                          class="w-full p-4 rounded-xl bg-white/60 dark:bg-white/5 backdrop-blur
                                 shadow-[inset_9px_9px_16px_#d1d9e6,inset_-9px_-9px_16px_#ffffff]
                                 dark:shadow-[inset_9px_9px_16px_#0c141c,inset_-9px_-9px_16px_#141e28]
                                 border-0 focus:outline-none focus:ring-2 focus:ring-primary/20"
                          placeholder="Masukkan teks dalam bahasa Indonesia di sini..."></textarea>
                
                <div class="flex gap-3 mt-4">
                    <button onclick="analyzeText()" class="flex-1 px-4 py-3 rounded-xl font-bold transition
                                   bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/[.15]
                                   shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                                   dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28] focus-ring">
                        <span class="material-symbols-outlined align-middle mr-2">search</span>
                        Analisis Sentimen
                    </button>
                    <button onclick="clearText()" class="px-4 py-3 rounded-xl font-bold transition
                                   bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/[.15]
                                   shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                                   dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28] focus-ring">
                        <span class="material-symbols-outlined align-middle">close</span>
                    </button>
                </div>

                <!-- Preprocessed Text -->
                <div id="preprocessedSection" class="mt-4 hidden">
                    <h3 class="text-sm font-bold mb-2 opacity-70">Teks Setelah Preprocessing:</h3>
                    <div id="preprocessedText" class="p-4 rounded-xl bg-white/60 dark:bg-white/5 backdrop-blur
                                                       shadow-[inset_9px_9px_16px_#d1d9e6,inset_-9px_-9px_16px_#ffffff]
                                                       dark:shadow-[inset_9px_9px_16px_#0c141c,inset_-9px_-9px_16px_#141e28]
                                                       text-sm font-mono"></div>
                </div>
            </article>

            <!-- Quick Info -->
            <article class="p-6 rounded-xl 
                            bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">info</span>
                    Tentang
                </h2>
                <p class="text-sm opacity-70 mb-4">Analisis sentimen mengidentifikasi emosi dalam teks:</p>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-green-600">sentiment_satisfied</span>
                        <span><strong>Positif:</strong> Sentimen baik/senang</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-yellow-600">sentiment_neutral</span>
                        <span><strong>Netral:</strong> Sentimen biasa</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-red-600">sentiment_dissatisfied</span>
                        <span><strong>Negatif:</strong> Sentimen buruk/sedih</span>
                    </li>
                </ul>
            </article>
        </section>

        <!-- Results Section -->
        <section id="resultsSection" class="mt-8 hidden">
            <!-- Sentiment Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <article class="p-6 rounded-xl text-center
                                bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                                shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                                dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                    <span class="material-symbols-outlined text-5xl text-green-600 mb-3 inline-block">sentiment_satisfied</span>
                    <h3 class="text-lg font-bold mb-2">Positive</h3>
                    <p class="text-3xl font-extrabold" id="positiveScore">0%</p>
                    <div class="mt-3 h-2 bg-white/60 dark:bg-white/5 rounded-full overflow-hidden">
                        <div id="positiveBar" class="h-full bg-green-500 transition-all duration-500" style="width: 0%"></div>
                    </div>
                </article>

                <article class="p-6 rounded-xl text-center
                                bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                                shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                                dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                    <span class="material-symbols-outlined text-5xl text-yellow-600 mb-3 inline-block">sentiment_neutral</span>
                    <h3 class="text-lg font-bold mb-2">Neutral</h3>
                    <p class="text-3xl font-extrabold" id="neutralScore">0%</p>
                    <div class="mt-3 h-2 bg-white/60 dark:bg-white/5 rounded-full overflow-hidden">
                        <div id="neutralBar" class="h-full bg-yellow-500 transition-all duration-500" style="width: 0%"></div>
                    </div>
                </article>

                <article class="p-6 rounded-xl text-center
                                bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                                shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                                dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                    <span class="material-symbols-outlined text-5xl text-red-600 mb-3 inline-block">sentiment_dissatisfied</span>
                    <h3 class="text-lg font-bold mb-2">Negative</h3>
                    <p class="text-3xl font-extrabold" id="negativeScore">0%</p>
                    <div class="mt-3 h-2 bg-white/60 dark:bg-white/5 rounded-full overflow-hidden">
                        <div id="negativeBar" class="h-full bg-red-500 transition-all duration-500" style="width: 0%"></div>
                    </div>
                </article>
            </div>

            <!-- Final Result -->
            <article class="p-8 rounded-xl text-center
                            bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <span id="resultIcon" class="material-symbols-outlined text-7xl mb-4 inline-block">sentiment_satisfied</span>
                <h2 class="text-2xl font-bold mb-2">Hasil Analisis</h2>
                <p class="text-5xl font-extrabold mb-4" id="resultSentiment">Positive</p>
                <p class="text-sm opacity-70">Confidence: <span id="resultConfidence" class="font-bold">0%</span></p>
                <p class="text-xs opacity-50 mt-4">Processing time: <span id="processingTime">0ms</span></p>
            </article>

            <!-- Word Importance -->
            <article id="wordImportanceSection" class="mt-8 p-6 rounded-xl hidden
                            bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">label</span>
                    Kata-kata Berpengaruh
                </h2>
                <div id="wordImportanceList" class="flex flex-wrap gap-2"></div>
            </article>
        </section>
    </div>
</main>

<script>
function showAlert(message, type = 'info') {
    const container = document.getElementById('alertContainer');
    const colors = {
        success: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200',
        error: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200',
        info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200'
    };
    
    const alert = document.createElement('div');
    alert.className = `p-4 rounded-xl ${colors[type]} shadow-lg mb-4`;
    alert.innerHTML = `
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span>
            <span>${message}</span>
        </div>
    `;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

function clearText() {
    document.getElementById('textInput').value = '';
    document.getElementById('resultsSection').classList.add('hidden');
    document.getElementById('preprocessedSection').classList.add('hidden');
}

async function analyzeText() {
    const text = document.getElementById('textInput').value.trim();
    if (!text) {
        showAlert('Silakan masukkan teks untuk dianalisis', 'error');
        return;
    }

    showAlert('Menganalisis sentimen...', 'info');
    const startTime = performance.now();

    try {
        const formData = new FormData();
        formData.append('text', text);

        const response = await fetch('analyze.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Analisis gagal');

        const result = await response.json();
        if (result.error) throw new Error(result.error);

        const endTime = performance.now();
        displayResults(result, endTime - startTime);
        showAlert('Analisis berhasil!', 'success');

    } catch (error) {
        showAlert('Error: ' + error.message, 'error');
    }
}

function displayResults(result, time) {
    // Show results
    document.getElementById('resultsSection').classList.remove('hidden');
    
    // Update scores
    const positive = (result.probabilities.positive * 100).toFixed(1);
    const neutral = (result.probabilities.neutral * 100).toFixed(1);
    const negative = (result.probabilities.negative * 100).toFixed(1);
    
    document.getElementById('positiveScore').textContent = positive + '%';
    document.getElementById('positiveBar').style.width = positive + '%';
    
    document.getElementById('neutralScore').textContent = neutral + '%';
    document.getElementById('neutralBar').style.width = neutral + '%';
    
    document.getElementById('negativeScore').textContent = negative + '%';
    document.getElementById('negativeBar').style.width = negative + '%';
    
    // Update final result
    const sentiment = result.sentiment;
    const icons = {
        positive: 'sentiment_satisfied',
        neutral: 'sentiment_neutral',
        negative: 'sentiment_dissatisfied'
    };
    const labels = {
        positive: 'Positif',
        neutral: 'Netral',
        negative: 'Negatif'
    };
    
    document.getElementById('resultIcon').textContent = icons[sentiment];
    document.getElementById('resultSentiment').textContent = labels[sentiment];
    document.getElementById('resultConfidence').textContent = Math.max(positive, neutral, negative) + '%';
    document.getElementById('processingTime').textContent = time.toFixed(2) + 'ms';
    
    // Show preprocessed text
    if (result.preprocessed_text) {
        document.getElementById('preprocessedSection').classList.remove('hidden');
        document.getElementById('preprocessedText').textContent = result.preprocessed_text;
    }
    
    // Show word importance
    if (result.word_scores) {
        const section = document.getElementById('wordImportanceSection');
        const list = document.getElementById('wordImportanceList');
        section.classList.remove('hidden');
        list.innerHTML = '';
        
        Object.entries(result.word_scores)
            .sort((a, b) => Math.abs(b[1]) - Math.abs(a[1]))
            .slice(0, 15)
            .forEach(([word, score]) => {
                const color = score > 0 ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' :
                             score < 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' :
                             'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200';
                const tag = document.createElement('span');
                tag.className = `px-3 py-1 rounded-lg text-sm font-medium ${color}`;
                tag.textContent = `${word} (${score.toFixed(3)})`;
                list.appendChild(tag);
            });
    }
    
    // Scroll to results
    document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Keyboard shortcut
document.getElementById('textInput').addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'Enter') {
        analyzeText();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
