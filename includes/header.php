<?php
// includes/header.php
require_once __DIR__ . '/functions.php';

// Pastikan user sudah login sebelum mengakses halaman apa pun yang menggunakan header ini
cek_login();

$nama  = $_SESSION['nama'];
$peran = $_SESSION['peran'];

// ==========================================
// KONFIGURASI MENU BERDASARKAN ROLE
// ==========================================
$menu_by_role = [
    'admin' => [
        ['Dashboard',         'dashboard.php',              '📊'],
        ['Input Pengaduan',   'admin/input_pengaduan.php',  '➕'],
        ['Modul Utama',       'admin/modul_utama.php',      '🗂️'],
        ['Riwayat Pengaduan', 'admin/riwayat.php',          '📋'],
        ['Kelola Pengguna',   'admin/pengguna.php',         '👥'],
        ['Laporan',           'admin/laporan.php',          '📄'],
    ],
    'camat' => [
        ['Dashboard',         'dashboard.php',              '📊'],
        ['Pengaduan Masuk',   'camat/masuk.php',            '📥'],
        ['Laporan',           'admin/laporan.php',          '📄'],
    ],
    'kasi' => [
        ['Dashboard',         'dashboard.php',              '📊'],
        ['Disposisi Masuk',   'kasi/masuk.php',             '📥'],
        ['Laporan',           'admin/laporan.php',          '📄'],
    ],
    'petugas' => [
        ['Dashboard',         'dashboard.php',              '📊'],
        ['Tugas Saya',        'petugas/tugas.php',          '🛠️'],
    ],
];

$menu = $menu_by_role[$peran] ?? [];
$current_path = $_SERVER['REQUEST_URI'];
$flash = get_flash();
$initial = strtoupper(substr($nama, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'SIPENTRA') ?> — SIPENTRA</title>
    
    <!-- Favicon & CSS -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="layout">
    <!-- ========================================== -->
    <!-- SIDEBAR KIRI -->
    <!-- ========================================== -->
    <aside class="sidebar">
        <!-- Brand / Logo -->
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h2>SIPENTRA</h2>
                <span>Medan Petisah</span>
            </div>
        </div>

        <!-- Info User Login -->
        <div class="sidebar-user">
            <div class="u-label">Masuk sebagai</div>
            <div class="u-name"><?= e($nama) ?></div>
            <div class="u-role"><?= e(ucfirst($peran)) ?></div>
        </div>

        <!-- Menu Navigasi -->
        <nav>
            <?php 
            foreach ($menu as $m): 
                $label = $m[0];
                $url   = $m[1];
                $icon  = $m[2];
                
                // Cek halaman aktif
                $active = (strpos($current_path, $url) !== false) ? 'active' : '';
            ?>
                <a href="<?= BASE_URL ?>/<?= $url ?>" class="<?= $active ?>">
                    <span class="nav-icon"><?= $icon ?></span> 
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
            
            <!-- Tombol Logout -->
            <a href="<?= BASE_URL ?>/logout.php" class="logout">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </nav>
    </aside>

    <!-- ========================================== -->
    <!-- KONTEN UTAMA KANAN -->
    <!-- ========================================== -->
    <main class="main-content">
        
        <!-- Topbar (Header Atas) -->
        <div class="topbar">
            <div class="topbar-left">
                <h1><?= e($title ?? 'Dashboard') ?></h1>
                <div class="breadcrumb">SIPENTRA · Kecamatan Medan Petisah</div>
            </div>
            <div class="topbar-right">
                <div class="topbar-badge">
                    <div class="avatar"><?= e($initial) ?></div>
                    <span><?= e($nama) ?></span>
                </div>
            </div>
        </div>

        <!-- Flash Message (Notifikasi Sukses/Error) -->
        <?php if ($flash): ?>
            <div class="flash flash-<?= e($flash['tipe']) ?>">
                <?= $flash['tipe'] === 'success' ? '✅' : ($flash['tipe'] === 'error' ? '❌' : 'ℹ️') ?>
                <?= e($flash['pesan']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Catatan: Tag penutup </main>, </div>, dan </body> 
             ada di dalam file includes/footer.php -->