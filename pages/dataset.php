<?php
require_once '../includes/config.php';

$current_page = 'dataset';
$page_title = 'Dataset Details - Sentiment AI';

// Get dataset ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: train.php');
    exit;
}

$dataset_id = (int)$_GET['id'];
$dataset = null;
$stats = [
    'total' => 0,
    'positive' => 0,
    'negative' => 0,
    'neutral' => 0
];

// Get dataset info
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM datasets WHERE id = ?");
    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $dataset = $result->fetch_assoc();
        
        // Read CSV and calculate stats with auto-labeling
        $filepath = __DIR__ . '/data/uploads/' . $dataset['filename'];
        if (file_exists($filepath)) {
            // Load lexicon
            $lexicon_path = __DIR__ . '/../data/lexicon/lexicon.txt';
            $lexicon_scores = [];
            
            if (file_exists($lexicon_path)) {
                $lexicon = file($lexicon_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lexicon as $line) {
                    $parts = explode(",", $line);
                    if (count($parts) === 2) {
                        $lexicon_scores[trim($parts[0])] = (int)trim($parts[1]);
                    }
                }
            }
            
            $handle = fopen($filepath, 'r');
            $header = fgetcsv($handle);
            
            // Find text column (case insensitive)
            $textIndex = -1;
            foreach ($header as $index => $col) {
                if (strtolower(trim($col)) === 'teks' || strtolower(trim($col)) === 'text') {
                    $textIndex = $index;
                    break;
                }
            }
            
            if ($textIndex !== -1) {
                require_once '../lib/Preprocessing.php';
                $preprocessor = new Preprocessing();
                
                while (($row = fgetcsv($handle)) !== false) {
                    if (!isset($row[$textIndex]) || empty(trim($row[$textIndex]))) {
                        continue;
                    }
                    
                    $text = $row[$textIndex];
                    
                    // Preprocessing
                    $text = $preprocessor->convertEmoji($text);
                    $text = $preprocessor->convertEmoticons($text);
                    $cleaned_text = $preprocessor->cleanText($text);
                    
                    if (empty(trim($cleaned_text))) {
                        continue;
                    }
                    
                    $tokens = $preprocessor->tokenize($cleaned_text);
                    $tokens = $preprocessor->removeStopwords($tokens);
                    $stemmed_tokens = $preprocessor->stemWords($tokens);
                    
                    // Calculate sentiment score
                    $total_score = 0;
                    foreach ($stemmed_tokens as $token) {
                        if (isset($lexicon_scores[$token])) {
                            $total_score += $lexicon_scores[$token];
                        }
                    }
                    
                    // Determine sentiment
                    $sentiment = 'neutral';
                    if ($total_score > 0) {
                        $sentiment = 'positive';
                    } elseif ($total_score < 0) {
                        $sentiment = 'negative';
                    }
                    
                    $stats['total']++;
                    if (isset($stats[$sentiment])) {
                        $stats[$sentiment]++;
                    }
                }
            }
            fclose($handle);
        }
    }
    $stmt->close();
}

if (!$dataset) {
    header('Location: train.php');
    exit;
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
                    <nav class="flex items-center text-sm opacity-70 mb-2" aria-label="Breadcrumb">
                        <a class="hover:underline" href="dashboard.php">Dashboard</a>
                        <span class="mx-2">/</span>
                        <a class="hover:underline" href="train.php">Training</a>
                        <span class="mx-2">/</span>
                        <span aria-current="page">Dataset Details</span>
                    </nav>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight"><?php echo htmlspecialchars($dataset['original_filename']); ?></h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="download_dataset.php?id=<?php echo $dataset_id; ?>" class="px-3 sm:px-4 py-2 rounded-xl text-sm sm:text-base focus-ring transition 
                             bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/[.15]
                             shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                             dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28] no-underline">
                        <span class="material-symbols-outlined align-middle text-base sm:text-lg mr-1">download</span>
                        Export
                    </a>
                    <button id="themeToggle" class="px-3 sm:px-4 py-2 rounded-xl text-sm sm:text-base focus-ring transition 
                               bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/[.15]
                               shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                               dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]" aria-label="Toggle theme">
                        <span class="material-symbols-outlined align-middle text-base sm:text-lg">dark_mode</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Grid -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8 auto-rows-fr">
            <!-- Dataset Info -->
            <article class="p-6 rounded-xl 
                            bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <h2 class="text-xl font-bold mb-4">Dataset Info</h2>
                <dl class="space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="opacity-70">Nama File</dt>
                        <dd class="font-semibold text-xs"><?php echo htmlspecialchars($dataset['original_filename']); ?></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="opacity-70">Total Data</dt>
                        <dd class="font-semibold"><?php echo number_format($stats['total']); ?></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="opacity-70">Status</dt>
                        <dd class="font-semibold"><?php echo ucfirst($dataset['status']); ?></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="opacity-70">Tanggal Upload</dt>
                        <dd class="font-semibold"><?php echo date('d M Y', strtotime($dataset['created_at'])); ?></dd>
                    </div>
                </dl>
            </article>

            <!-- Sentiment Distribution -->
            <article class="p-6 rounded-xl xl:col-span-2 
                            bg-white/70 dark:bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/50
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <h2 class="text-xl font-bold">Distribusi Sentimen</h2>
                    <div class="flex items-center gap-2 text-sm opacity-80">
                        <div class="size-3 rounded-full bg-green-500"></div> <span>Positive</span>
                        <div class="size-3 rounded-full ml-4 bg-red-500"></div> <span>Negative</span>
                        <div class="size-3 rounded-full ml-4 bg-yellow-500"></div> <span>Neutral</span>
                    </div>
                </div>
                <div class="w-full grid grid-cols-1 md:grid-cols-[auto_1fr] gap-6 items-center">
                    <?php
                    $posPercent = $stats['total'] > 0 ? round(($stats['positive'] / $stats['total']) * 100, 1) : 0;
                    $negPercent = $stats['total'] > 0 ? round(($stats['negative'] / $stats['total']) * 100, 1) : 0;
                    $neuPercent = $stats['total'] > 0 ? round(($stats['neutral'] / $stats['total']) * 100, 1) : 0;
                    
                    $posDash = round($posPercent * 100 / 100, 1);
                    $negDash = round($negPercent * 100 / 100, 1);
                    $neuDash = round($neuPercent * 100 / 100, 1);
                    ?>
                    <div class="relative w-56 h-56 md:w-64 md:h-64 mx-auto">
                        <svg class="w-full h-full" viewBox="0 0 36 36" aria-label="Sentiment donut chart" role="img">
                            <title><?php echo "$posPercent% Positive, $negPercent% Negative, $neuPercent% Neutral"; ?></title>
                            <circle r="15.915" cx="18" cy="18" fill="none" stroke="#e5e7eb" stroke-width="3"></circle>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#ef4444" stroke-dasharray="<?php echo $negDash; ?> <?php echo 100 - $negDash; ?>" stroke-width="4"></path>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#eab308" stroke-dasharray="<?php echo $neuDash; ?> <?php echo 100 - $neuDash; ?>" stroke-dashoffset="-<?php echo $negDash; ?>" stroke-width="4"></path>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#22c55e" stroke-dasharray="<?php echo $posDash; ?> <?php echo 100 - $posDash; ?>" stroke-dashoffset="-<?php echo $negDash + $neuDash; ?>" stroke-width="4"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl md:text-4xl font-extrabold"><?php echo number_format($stats['total']); ?></span>
                            <span class="opacity-70 text-sm">Total</span>
                        </div>
                    </div>
                    <ul class="grid grid-cols-3 gap-2 md:gap-4 text-center">
                        <li class="p-3 rounded-xl bg-white/60 dark:bg-white/5 backdrop-blur 
                                   shadow-[inset_9px_9px_16px_#d1d9e6,inset_-9px_-9px_16px_#ffffff]
                                   dark:shadow-[inset_9px_9px_16px_#0c141c,inset_-9px_-9px_16px_#141e28]">
                            <p class="text-xs opacity-70">Positive</p>
                            <p class="text-2xl font-bold"><?php echo $posPercent; ?>%</p>
                            <p class="text-xs opacity-50"><?php echo number_format($stats['positive']); ?> data</p>
                        </li>
                        <li class="p-3 rounded-xl bg-white/60 dark:bg-white/5 backdrop-blur 
                                   shadow-[inset_9px_9px_16px_#d1d9e6,inset_-9px_-9px_16px_#ffffff]
                                   dark:shadow-[inset_9px_9px_16px_#0c141c,inset_-9px_-9px_16px_#141e28]">
                            <p class="text-xs opacity-70">Negative</p>
                            <p class="text-2xl font-bold"><?php echo $negPercent; ?>%</p>
                            <p class="text-xs opacity-50"><?php echo number_format($stats['negative']); ?> data</p>
                        </li>
                        <li class="p-3 rounded-xl bg-white/60 dark:bg-white/5 backdrop-blur 
                                   shadow-[inset_9px_9px_16px_#d1d9e6,inset_-9px_-9px_16px_#ffffff]
                                   dark:shadow-[inset_9px_9px_16px_#0c141c,inset_-9px_-9px_16px_#141e28]">
                            <p class="text-xs opacity-70">Neutral</p>
                            <p class="text-2xl font-bold"><?php echo $neuPercent; ?>%</p>
                            <p class="text-xs opacity-50"><?php echo number_format($stats['neutral']); ?> data</p>
                        </li>
                    </ul>
                </div>
            </article>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
