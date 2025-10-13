<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Sentimen Bahasa Indonesia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link href="assets/css/navbar.css" rel="stylesheet">
    <link href="assets/css/style_home.css" rel="stylesheet">
    <!-- <link href="assets/css/style_dataset_view.css" rel="stylesheet"> -->
    <style>
        /* Override untuk memastikan teks navbar tetap putih saat hover */
        .navbar .nav-link:hover,
        .navbar .nav-item.active .nav-link,
        .navbar .nav-link:focus,
        .navbar .nav-link:active {
            color: #ffffff !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include('includes/nav_template.php'); ?>

    <!-- Hero Section -->
    <div class="hero-section text-white" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center justify-contect-center">
                <div class="col-12 text-center text-lg-start" data-aos="fade-right" data-aos-delay="200">
                    <h1 class="display-4 fw-bold mb-4">Analisis Sentimen Bahasa Indonesia</h1>
                    <p class="lead mb-4">Platform analisis sentimen yang membantu Anda memahami emosi dan pendapat dalam teks berbahasa Indonesia menggunakan teknologi machine learning canggih.</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                        <a href="pages/train.php" class="btn btn-light btn-lg px-4 me-2">
                            <i class="bi bi-search me-2"></i>Mulai Training
                        </a>
                        <a href="pages/about.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-info-circle me-2"></i>Pelajari Selengkapnya
                        </a>
                    </div>
                </div>
                    <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left" data-aos-delay="400">
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="row text-center mb-5" data-aos="fade-up">
            <div class="col">
                <h2 class="fw-bold mb-2">Fitur Utama</h2>
                <p class="lead text-muted">Apa yang bisa Anda lakukan dengan aplikasi ini</p>
                <div class="divider mx-auto mb-4" style="width: 80px; height: 4px; background: var(--primary-color); border-radius: 2px;"></div>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body feature-card">
                        <div class="feature-icon mb-4">
                            <i class="bi bi-search"></i>
                        </div>
                        <h4 class="card-title mb-3">Analisis Teks</h4>
                        <p class="card-text text-muted">Masukkan teks berbahasa Indonesia untuk mengetahui sentimen yang terkandung dalam teks tersebut dengan akurasi tinggi.</p>
                        <a href="pages/analyze.php" class="btn btn-outline-primary mt-3">Coba Sekarang</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body feature-card">
                        <div class="feature-icon mb-4">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <h4 class="card-title mb-3">Training Model</h4>
                        <p class="card-text text-muted">Upload dataset Anda sendiri untuk melatih model analisis sentimen yang lebih akurat sesuai kebutuhan Anda.</p>
                        <a href="pages/train.php" class="btn btn-outline-primary mt-3">Latih Model</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <hr class="my-4" style="opacity: 0.1;">
            <small class="text-muted">© <?php echo date('Y'); ?> Analisis Sentimen - Aplikasi Pendeteksi Sentimen Indonesia</small>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Menghapus Chart.js -->
    <!-- Menghapus D3.js -->
    <!-- Menghapus D3-cloud -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize AOS animation
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        function scrollToDemo() {
            // Tambahkan ID "demo-section" ke elemen yang sesuai atau hapus fungsi ini
            // Misalnya pada section fitur utama
            document.querySelector('.container.py-5').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Navbar behavior is now controlled by navbar.css
    </script>
</body>
</html>