<?php

/**
 * Helper untuk meningkatkan batas memori PHP
 * File ini dapat dimasukkan di awal file yang memerlukan penggunaan memori lebih besar
 */

// Coba meningkatkan batas memori ke 2 GB
ini_set('memory_limit', '2048M');

// Aktifkan garbage collector
gc_enable();

// Nonaktifkan batas waktu eksekusi untuk operasi yang membutuhkan waktu lama
set_time_limit(600); // 10 menit

// Fungsi untuk memeriksa penggunaan memori
function checkMemoryUsage()
{
    $memUsage = memory_get_usage(true);
    $memPeak = memory_get_peak_usage(true);

    return [
        'current' => formatBytes($memUsage),
        'peak' => formatBytes($memPeak)
    ];
}

// Fungsi untuk memformat bytes ke format yang mudah dibaca
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Fungsi untuk membersihkan memori setelah operasi berat
function clearMemory()
{
    // Eksplisit panggil garbage collector
    gc_collect_cycles();

    // Log penggunaan memori
    $memUsage = checkMemoryUsage();
    error_log("Memory cleaned. Current usage: " . $memUsage['current'] . ", Peak: " . $memUsage['peak']);
}
