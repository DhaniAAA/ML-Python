<?php
require_once '../includes/memory_helper.php';
require_once '../vendor/autoload.php';
require_once '../includes/config.php';
require_once '../lib/Preprocessing.php';

use Phpml\Classification\NaiveBayes;
use Phpml\Dataset\CsvDataset;

session_start();

$current_page = 'train';
$page_title = 'Training Model - Sentiment AI';
$message = '';
$error = '';

// Handle delete dataset
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $id = (int) $_GET['id'];

        if (!$conn) {
            throw new Exception('Koneksi database gagal');
        }

        $stmt = $conn->prepare("SELECT filename FROM datasets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Dataset tidak ditemukan');
        }

        $row = $result->fetch_assoc();
        $dataset_filename = $row['filename'];
        $stmt->close();

        $file_path = __DIR__ . '/data/uploads/' . $dataset_filename;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $stmt = $conn->prepare("DELETE FROM datasets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $message = "Dataset berhasil dihapus";
        header('Location: train.php?message=' . urlencode($message));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Get datasets
$datasets = [];
if ($conn) {
    $result = $conn->query("SELECT * FROM datasets ORDER BY created_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $datasets[] = $row;
        }
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
                    <nav class="flex items-center text-sm opacity-70 mb-2" aria-label="Breadcrumb">
                        <a class="hover:underline" href="dashboard.php">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span aria-current="page">Training Model</span>
                    </nav>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight">Training Model</h1>
                </div>

            </div>
        </header>

        <!-- Alert Messages -->
        <?php if ($message): ?>
        <div class="card bg-green-100 border-green-500 mb-6">
            <div class="flex items-center gap-2 text-green-800 font-bold">
                <span class="material-symbols-outlined">check_circle</span>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="card bg-red-100 border-red-500 mb-6">
            <div class="flex items-center gap-2 text-red-800 font-bold">
                <span class="material-symbols-outlined">error</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upload Section -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8 mb-8">
            <!-- Upload Form -->
            <article class="xl:col-span-2 card">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">upload_file</span>
                    Upload Dataset CSV
                </h2>

                <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Nama Dataset</label>
                        <input type="text" name="dataset_name" id="datasetName" required
                               class="w-full"
                               placeholder="Contoh: Dataset Review Produk">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">File CSV</label>
                        <div class="relative">
                            <input type="file" name="csv_file" id="csvFile" accept=".csv" required
                                   class="w-full">
                        </div>
                        <p class="text-xs opacity-70 mt-2">Format: CSV dengan kolom 'Create time' dan 'Teks' (labeling otomatis)</p>
                    </div>

                    <button type="submit" class="w-full btn btn-primary justify-center text-lg">
                        <span class="material-symbols-outlined align-middle mr-2">cloud_upload</span>
                        Upload & Train Model
                    </button>
                </form>

                <!-- Progress -->
                <div id="progressSection" class="mt-6 hidden">
                    <div class="card bg-blue-50 border-blue-500">
                        <div class="flex items-center gap-2 mb-2 font-bold text-blue-900">
                            <span class="material-symbols-outlined animate-spin">progress_activity</span>
                            <span id="progressText">Uploading...</span>
                        </div>
                        <div class="w-full h-4 bg-white border-2 border-black rounded-none overflow-hidden">
                            <div id="progressBar" class="h-full bg-blue-500 transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Info -->
            <article class="card">
                <h2 class="text-xl font-bold mb-4">
                    <span class="material-symbols-outlined align-middle mr-2">info</span>
                    Panduan
                </h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <h3 class="font-bold mb-1">Format CSV:</h3>
                        <p class="opacity-70">File harus memiliki kolom 'Create time' dan 'Teks'</p>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Labeling Otomatis:</h3>
                        <p class="opacity-70">Sistem akan otomatis melabeli sentimen berdasarkan lexicon</p>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Contoh Data:</h3>
                        <code class="block p-2 border-2 border-black bg-gray-50 rounded text-xs overflow-x-auto">
                            Create time,Teks<br>
                            "2024-01-01","Produk bagus"<br>
                            "2024-01-02","Sangat buruk"
                        </code>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Catatan:</h3>
                        <ul class="opacity-70 space-y-1 ml-4 text-xs">
                            <li>• Minimal 10 data valid</li>
                            <li>• Duplikat akan dihapus</li>
                            <li>• Teks kosong diabaikan</li>
                        </ul>
                    </div>
                </div>
            </article>
        </section>

        <!-- Datasets List -->
        <section class="card">
            <h2 class="text-xl font-bold mb-4">
                <span class="material-symbols-outlined align-middle mr-2">dataset</span>
                Dataset Tersimpan
            </h2>

            <?php if (empty($datasets)): ?>
            <div class="text-center py-12 opacity-70">
                <span class="material-symbols-outlined text-6xl mb-4 inline-block">folder_open</span>
                <p>Belum ada dataset. Upload dataset pertama Anda!</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-black">
                            <th class="text-left p-3 font-bold">Nama Dataset</th>
                            <th class="text-left p-3 font-bold">File</th>
                            <th class="text-left p-3 font-bold">Tanggal Upload</th>
                            <th class="text-center p-3 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datasets as $dataset): ?>
                        <tr class="border-b border-black hover:bg-yellow-50">
                            <td class="p-3 font-medium"><?php echo htmlspecialchars($dataset['original_filename']); ?></td>
                            <td class="p-3 text-sm opacity-70"><?php echo htmlspecialchars($dataset['filename']); ?></td>
                            <td class="p-3 text-sm opacity-70"><?php echo date('d M Y', strtotime($dataset['created_at'])); ?></td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="dataset.php?id=<?php echo $dataset['id']; ?>"
                                       class="btn btn-outline text-sm py-1 px-3 bg-blue-100 hover:bg-blue-200 border-black text-black">
                                        <span class="material-symbols-outlined align-middle text-sm">visibility</span>
                                        Detail
                                    </a>
                                    <a href="?action=delete&id=<?php echo $dataset['id']; ?>"
                                       onclick="return confirm('Yakin ingin menghapus dataset ini?')"
                                       class="btn btn-outline text-sm py-1 px-3 bg-red-100 hover:bg-red-200 border-black text-black">
                                        <span class="material-symbols-outlined align-middle text-sm">delete</span>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<script>
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const progressSection = document.getElementById('progressSection');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    progressSection.classList.remove('hidden');
    progressText.textContent = 'Uploading dataset...';
    progressBar.style.width = '30%';

    try {
        const response = await fetch('upload_dataset.php', {
            method: 'POST',
            body: formData
        });

        progressBar.style.width = '60%';
        progressText.textContent = 'Training model...';

        const result = await response.json();

        if (result.success) {
            progressBar.style.width = '100%';
            progressText.textContent = 'Training completed!';

            setTimeout(() => {
                window.location.href = 'train.php?message=' + encodeURIComponent('Dataset berhasil diupload dan model berhasil ditraining!');
            }, 1000);
        } else {
            throw new Error(result.error || 'Upload failed');
        }
    } catch (error) {
        progressSection.innerHTML = `
            <div class="card bg-red-100 border-red-500">
                <div class="flex items-center gap-2 text-red-800 font-bold">
                    <span class="material-symbols-outlined">error</span>
                    <span>Error: ${error.message}</span>
                </div>
            </div>
        `;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
