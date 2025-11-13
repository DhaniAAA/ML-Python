<!-- Sidebar -->
<aside class="hidden lg:flex lg:w-72 p-6">
    <div class="flex flex-col w-full h-full">
        <div class="flex items-center gap-3 mb-10">
            <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12 p-2 surface dark:surface-dark transition-base">
                <span class="material-symbols-outlined text-3xl text-primary">sentiment_satisfied</span>
            </div>
            <div class="flex flex-col">
                <h1 class="text-lg font-extrabold">Sentiment AI</h1>
                <p class="text-sm opacity-70">Welcome back!</p>
            </div>
        </div>

        <nav class="flex flex-col gap-2" aria-label="Primary">
            <a class="flex items-center gap-4 p-3 rounded-xl transition-base hover-elevate no-underline <?php echo ($current_page == 'dashboard') ? 'surface dark:surface-dark' : ''; ?>" href="dashboard.php" aria-current="<?php echo ($current_page == 'dashboard') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="<?php echo ($current_page == 'dashboard') ? 'font-bold' : 'font-medium'; ?>">Dashboard</span>
            </a>
            <a class="flex items-center gap-4 p-3 rounded-xl transition-base hover-elevate no-underline <?php echo ($current_page == 'analyze') ? 'surface dark:surface-dark' : ''; ?>" href="predict.php" aria-current="<?php echo ($current_page == 'analyze') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">search</span>
                <span class="<?php echo ($current_page == 'analyze') ? 'font-bold' : 'font-medium'; ?>">Analisis Teks</span>
            </a>
            <a class="flex items-center gap-4 p-3 rounded-xl transition-base hover-elevate no-underline <?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'surface dark:surface-dark' : ''; ?>" href="train.php" aria-current="<?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">model_training</span>
                <span class="<?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'font-bold' : 'font-medium'; ?>">Training & Datasets</span>
            </a>
        </nav>

        <a href="../index.php" class="w-full mt-auto py-3 px-4 font-bold rounded-xl transition-base hover-elevate surface dark:surface-dark focus-ring text-center no-underline">
            <span class="material-symbols-outlined align-middle mr-2">home</span>
            Beranda
        </a>
    </div>
</aside>
