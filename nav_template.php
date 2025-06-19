<?php
// Tentukan halaman aktif berdasarkan nama file
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-graph-up"></i> Analisis Sentimen</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Beranda</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'predict.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="predict.php"><i class="bi bi-graph-up"></i> Prediksi</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'train.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="train.php"><i class="bi bi-cpu"></i> Training</a>
                </li>
                <li class="nav-item <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="about.php"><i class="bi bi-info-circle"></i> Tentang</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 