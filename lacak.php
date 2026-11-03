<?php
require_once __DIR__ . '/includes/functions.php';

$hasil = null;
$error = '';

if (isset($_GET['no_surat']) && $_GET['no_surat'] !== '') {
    $no_surat = trim($_GET['no_surat']);
    
    // Cari pengaduan berdasarkan nomor surat
    $stmt = $pdo->prepare("
        SELECT p.*, k.nama_kategori 
        FROM pengaduan p 
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
        WHERE p.no_surat = ?
    ");
    $stmt->execute([$no_surat]);
    $pengaduan = $stmt->fetch();

    if ($pengaduan) {
        // Ambil riwayat status
        $stmt = $pdo->prepare("
            SELECT r.*, u.nama AS pelaku 
            FROM riwayat_status r 
            LEFT JOIN pengguna u ON r.id_pengguna = u.id_pengguna 
            WHERE r.id_pengaduan = ? 
            ORDER BY r.waktu ASC
        ");
        $stmt->execute([$pengaduan['id_pengaduan']]);
        $riwayat = $stmt->fetchAll();
        $hasil = ['pengaduan' => $pengaduan, 'riwayat' => $riwayat];
    } else {
        $error = "Nomor surat '$no_surat' tidak ditemukan. Pastikan penulisan sudah benar (contoh: 001/PKL/2026).";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pengaduan — SIPENTRA</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <div style="background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; color: white;">
        <div style="display:flex; align-items:center; gap:1rem;">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" style="height:40px; background:white; border-radius:8px; padding:4px;" alt="Logo">
            <h2 style="font-family: var(--font-display); margin:0;">SIPENTRA</h2>
        </div>
        <a href="login.php" class="btn btn-accent btn-sm">Login Petugas</a>
    </div>

    <div style="max-width: 800px; margin: 3rem auto; padding: 0 1rem;">
        <div class="card" style="text-align: center; padding: 3rem 2rem;">
            <h2 style="font-family: var(--font-display); color: var(--primary); margin-bottom: 0.5rem;">Lacak Status Pengaduan</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Masukkan Nomor Surat Pengaduan Anda untuk melihat status penanganan.</p>

            <form method="GET" style="display: flex; gap: 0.5rem; max-width: 500px; margin: 0 auto;">
                <input type="text" name="no_surat" placeholder="Contoh: 001/PKL/2026" value="<?= e($_GET['no_surat'] ?? '') ?>" required 
                       style="flex:1; padding: 0.8rem; border: 1px solid var(--border); border-radius: 8px;">
                <button type="submit" class="btn btn-primary">🔍 Lacak</button>
            </form>

            <?php if ($error): ?>
                <div class="flash flash-error" style="margin-top: 2rem;">❌ <?= e($error) ?></div>
            <?php endif; ?>
        </div>

        <?php if ($hasil): ?>
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3>Hasil Pelacakan: <?= e($hasil['pengaduan']['no_surat']) ?></h3>
                    <div><?= badge_status($hasil['pengaduan']['status']) ?></div>
                </div>
                
                <table style="margin-bottom: 2rem;">
                    <tr><th style="width: 200px;">Tanggal Lapor</th><td><?= date('d/m/Y', strtotime($hasil['pengaduan']['tgl_diterima'])) ?></td></tr>
                    <tr><th>Kategori</th><td><?= e($hasil['pengaduan']['nama_kategori']) ?></td></tr>
                    <tr><th>Lokasi Kejadian</th><td><?= e($hasil['pengaduan']['lokasi_kejadian']) ?></td></tr>
                    <tr><th>Isi Pengaduan</th><td><?= nl2br(e($hasil['pengaduan']['isi_pengaduan'])) ?></td></tr>
                </table>

                <h4 style="margin-bottom: 1rem; color: var(--primary);">Riwayat Penanganan:</h4>
                <div class="timeline">
                    <?php foreach ($hasil['riwayat'] as $r): ?>
                        <div class="timeline-item">
                            <div class="tl-status"><?= e($r['status']) ?></div>
                            <div class="tl-time"><?= date('d/m/Y H:i', strtotime($r['waktu'])) ?></div>
                            <?php if ($r['keterangan']): ?>
                                <div class="tl-note"><?= e($r['keterangan']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>