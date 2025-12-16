<!-- Sidebar -->
<aside class="hidden lg:flex lg:w-72 p-6">
    <div class="flex flex-col w-full h-full">
        <div class="flex items-center gap-3 mb-10">
            <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12 p-2 border-2 border-black bg-yellow-300 flex items-center justify-center shadow-[4px_4px_0_0_#000]">
                <span class="material-symbols-outlined text-3xl text-black">sentiment_satisfied</span>
            </div>
            <div class="flex flex-col">
                <h1 class="text-lg font-extrabold">Sentiment AI</h1>
                <p class="text-sm opacity-70">Welcome back!</p>
            </div>
        </div>

        <nav class="flex flex-col gap-3" aria-label="Primary">
            <a class="flex items-center gap-4 p-3 border-2 transition-all no-underline <?php echo ($current_page == 'dashboard') ? 'bg-yellow-100 border-black shadow-[4px_4px_0_0_#000] translate-x-[-2px] translate-y-[-2px]' : 'border-transparent hover:border-black hover:bg-white hover:shadow-[4px_4px_0_0_#000]'; ?>" href="dashboard.php" aria-current="<?php echo ($current_page == 'dashboard') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="<?php echo ($current_page == 'dashboard') ? 'font-bold' : 'font-medium'; ?>">Dashboard</span>
            </a>
            <a class="flex items-center gap-4 p-3 border-2 transition-all no-underline <?php echo ($current_page == 'analyze') ? 'bg-yellow-100 border-black shadow-[4px_4px_0_0_#000] translate-x-[-2px] translate-y-[-2px]' : 'border-transparent hover:border-black hover:bg-white hover:shadow-[4px_4px_0_0_#000]'; ?>" href="predict.php" aria-current="<?php echo ($current_page == 'analyze') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">search</span>
                <span class="<?php echo ($current_page == 'analyze') ? 'font-bold' : 'font-medium'; ?>">Analisis Teks</span>
            </a>
            <a class="flex items-center gap-4 p-3 border-2 transition-all no-underline <?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'bg-yellow-100 border-black shadow-[4px_4px_0_0_#000] translate-x-[-2px] translate-y-[-2px]' : 'border-transparent hover:border-black hover:bg-white hover:shadow-[4px_4px_0_0_#000]'; ?>" href="train.php" aria-current="<?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">model_training</span>
                <span class="<?php echo ($current_page == 'train' || $current_page == 'dataset') ? 'font-bold' : 'font-medium'; ?>">Training & Datasets</span>
            </a>
            <a class="flex items-center gap-4 p-3 border-2 transition-all no-underline <?php echo ($current_page == 'about') ? 'bg-yellow-100 border-black shadow-[4px_4px_0_0_#000] translate-x-[-2px] translate-y-[-2px]' : 'border-transparent hover:border-black hover:bg-white hover:shadow-[4px_4px_0_0_#000]'; ?>" href="about.php" aria-current="<?php echo ($current_page == 'about') ? 'page' : 'false'; ?>">
                <span class="material-symbols-outlined">info</span>
                <span class="<?php echo ($current_page == 'about') ? 'font-bold' : 'font-medium'; ?>">About</span>
            </a>
        </nav>

        <a href="../index.php" class="w-full mt-auto py-3 px-4 font-bold transition-all border-2 border-black bg-white shadow-[4px_4px_0_0_#000] hover:translate-x-[-2px] hover:translate-y-[-2px] hover:shadow-[6px_6px_0_0_#000] text-center no-underline">
            <span class="material-symbols-outlined align-middle mr-2">home</span>
            Beranda
        </a>
    </div>
</aside>
