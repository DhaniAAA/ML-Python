<!-- Sidebar -->
<aside class="hidden lg:flex lg:w-72 p-6">
    <div class="flex flex-col w-full h-full">
        <div class="flex items-center gap-3 mb-10">
            <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12 p-2 
                        shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff] 
                        dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                <span class="material-symbols-outlined text-3xl text-primary">sentiment_satisfied</span>
            </div>
            <div class="flex flex-col">
                <h1 class="text-lg font-extrabold">Sentiment AI</h1>
                <p class="text-sm opacity-70">Welcome back!</p>
            </div>
        </div>

        <nav class="flex flex-col gap-2" aria-label="Primary">
            <a class="flex items-center gap-4 p-3 rounded-xl transition hover:bg-black/5 dark:hover:bg-white/10 <?php echo ($current_page == 'dashboard') ? 'bg-black/5 dark:bg-white/10 shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff] dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]' : ''; ?>" href="dashboard.php">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="<?php echo ($current_page == 'dashboard') ? 'font-bold' : 'font-medium'; ?>">Dashboard</span>
            </a>
            <a class="flex items-center gap-4 p-3 rounded-xl transition hover:bg-black/5 dark:hover:bg-white/10 <?php echo ($current_page == 'analyze') ? 'bg-black/5 dark:bg-white/10 shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff] dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]' : ''; ?>" href="predict.php">
                <span class="material-symbols-outlined">search</span>
                <span class="<?php echo ($current_page == 'analyze') ? 'font-bold' : 'font-medium'; ?>">Analisis Teks</span>
            </a>
            <a class="flex items-center gap-4 p-3 rounded-xl transition hover:bg-black/5 dark:hover:bg-white/10 <?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'bg-black/5 dark:bg-white/10 shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff] dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]' : ''; ?>" href="train.php">
                <span class="material-symbols-outlined">model_training</span>
                <span class="<?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'font-bold' : 'font-medium'; ?>">Training & Datasets</span>
            </a>
        </nav>

        <a href="../index.php" class="w-full mt-auto py-3 px-4 font-bold rounded-xl transition
                       bg-black/5 dark:bg-white/10
                       hover:bg-black/10 dark:hover:bg-white/[.15]
                       shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                       dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28] focus-ring text-center no-underline">
            <span class="material-symbols-outlined align-middle mr-2">home</span>
            Beranda
        </a>
    </div>
</aside>
