<!-- Mobile Topbar -->
<div class="lg:hidden mb-4 flex items-center justify-between" role="navigation" aria-label="Mobile Topbar">
    <div class="flex items-center gap-3">
        <button id="openNav" class="p-2 rounded-xl focus-ring
                                   shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                                   dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]" aria-label="Open navigation">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h1 class="font-extrabold">Sentiment AI</h1>
    </div>
    <button id="themeToggleMobile" class="p-2 rounded-xl focus-ring
             shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
             dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]" aria-label="Toggle theme">
        <span class="material-symbols-outlined">dark_mode</span>
    </button>
</div>

<!-- Mobile Drawer -->
<dialog id="navDrawer" class="hidden fixed inset-0 bg-black/30 dark:bg-white/10" aria-modal="true" aria-labelledby="mobileNavTitle">
    <div class="absolute left-0 top-0 h-full w-72 p-6 bg-background-light dark:bg-background-dark
                shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full p-2
                            shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                            dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]">
                    <span class="material-symbols-outlined">sentiment_satisfied</span>
                </div>
                <strong id="mobileNavTitle">Sentiment AI</strong>
            </div>
            <button id="closeNav" class="p-2 rounded-xl focus-ring
                         shadow-[9px_9px_16px_#d1d9e6,-9px_-9px_16px_#ffffff]
                         dark:shadow-[9px_9px_16px_#0c141c,-9px_-9px_16px_#141e28]" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <nav class="flex flex-col gap-2" aria-label="Mobile Navigation">
            <a class="flex items-center gap-4 p-3 rounded-xl hover:bg-black/5 dark:hover:bg-white/10 no-underline" href="dashboard.php">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a class="flex items-center gap-4 p-3 rounded-xl hover:bg-black/5 dark:hover:bg-white/10 no-underline" href="predict.php">
                <span class="material-symbols-outlined">search</span>
                <span>Analisis Teks</span>
            </a>
            <a class="flex items-center gap-4 p-3 rounded-xl hover:bg-black/5 dark:hover:bg-white/10 no-underline" href="train.php">
                <span class="material-symbols-outlined">model_training</span>
                <span>Training & Datasets</span>
            </a>
        </nav>
    </div>
</dialog>