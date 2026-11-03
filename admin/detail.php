<?php
$title = "Detail Pengaduan";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['admin','camat','kasi','petugas']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT p.*, k.nama_kategori, u.nama AS nama_admin
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    LEFT JOIN pengguna u ON p.id_admin = u.id_pengguna
    WHERE p.id_pengaduan = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { redirect('dashboard.php', 'error', 'Data tidak ditemukan.'); }

// Riwayat status
$stmt = $pdo->prepare("
    SELECT r.*, u.nama AS pelaku
    FROM riwayat_status r
    LEFT JOIN pengguna u ON r.id_pengguna = u.id_pengguna
    WHERE r.id_pengaduan = ?
    ORDER BY r.waktu ASC
");
$stmt->execute([$id]);
$riwayat = $stmt->fetchAll();

// Disposisi
$stmt = $pdo->prepare("SELECT d.*, u.nama AS camat FROM disposisi d LEFT JOIN pengguna u ON d.id_camat=u.id_pengguna WHERE d.id_pengaduan=?");
$stmt->execute([$id]);
$disposisi = $stmt->fetch();

// Arahan
$stmt = $pdo->prepare("SELECT a.*, u1.nama AS kasi, u2.nama AS petugas FROM arahan a LEFT JOIN pengguna u1 ON a.id_kasi=u1.id_pengguna LEFT JOIN pengguna u2 ON a.id_petugas=u2.id_pengguna WHERE a.id_pengaduan=?");
$stmt->execute([$id]);
$arahan = $stmt->fetch();

// Tindak lanjut
$stmt = $pdo->prepare("SELECT t.*, u.nama AS petugas FROM tindak_lanjut t LEFT JOIN pengguna u ON t.id_petugas=u.id_pengguna WHERE t.id_pengaduan=?");
$stmt->execute([$id]);
$tl = $stmt->fetch();

require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h3><?= e($p['no_surat']) ?> <?= badge_status($p['status']) ?></h3>
    <table style="margin-top:1rem">
        <tr><th style="width:200px">Tanggal Diterima</th><td><?= e(date('d/m/Y', strtotime($p['tgl_diterima']))) ?></td></tr>
        <tr><th>Nama Pelapor</th><td><?= e($p['nama_pelapor'] ?: '-') ?></td></tr>
        <tr><th>Alamat Pelapor</th><td><?= e($p['alamat'] ?: '-') ?></td></tr>
        <tr><th>Lokasi Kejadian</th><td><?= e($p['lokasi_kejadian']) ?></td></tr>
        <tr><th>Kategori</th><td><?= e($p['nama_kategori'] ?? '-') ?></td></tr>
        <tr><th>Isi Pengaduan</th><td><?= nl2br(e($p['isi_pengaduan'])) ?></td></tr>
        <tr><th>Berkas Pendukung</th><td>
            <?php if ($p['berkas_pendukung']): ?>
                <a href="<?= UPLOAD_URL . e($p['berkas_pendukung']) ?>" target="_blank" class="btn btn-sm btn-primary">📎 Lihat Berkas</a>
            <?php else: ?>-<?php endif; ?>
        </td></tr>
        <tr><th>Diinput Oleh</th><td><?= e($p['nama_admin'] ?: '-') ?></td></tr>
    </table>
</div>

<?php if ($disposisi): ?>
<div class="card">
    <h3>Disposisi Camat</h3>
    <p><b>Status:</b> <?= e($disposisi['status_disposisi']) ?></p>
    <p><b>Instruksi:</b> <?= e($disposisi['instruksi'] ?: '-') ?></p>
    <?php if ($disposisi['alasan_penolakan']): ?>
        <p><b>Alasan Penolakan:</b> <?= e($disposisi['alasan_penolakan']) ?></p>
    <?php endif; ?>
    <p><b>Tanggal:</b> <?= e(date('d/m/Y H:i', strtotime($disposisi['tgl_disposisi']))) ?></p>
</div>
<?php endif; ?>

<?php if ($arahan): ?>
<div class="card">
    <h3>Arahan Kasi Trantibum</h3>
    <p><b>Petugas:</b> <?= e($arahan['petugas']) ?></p>
    <p><b>Arahan:</b> <?= e($arahan['arahan']) ?></p>
    <p><b>Tanggal:</b> <?= e(date('d/m/Y H:i', strtotime($arahan['tgl_arahan']))) ?></p>
</div>
<?php endif; ?>

<?php if ($tl): ?>
<div class="card">
    <h3>Hasil Tindak Lanjut</h3>
    <p><b>Petugas:</b> <?= e($tl['petugas']) ?></p>
    <p><b>Hasil Penanganan:</b> <?= nl2br(e($tl['hasil_penanganan'])) ?></p>
    <?php if ($tl['dokumentasi']): ?>
        <p><b>Dokumentasi:</b>
            <a href="<?= UPLOAD_URL . e($tl['dokumentasi']) ?>" target="_blank" class="btn btn-sm btn-primary">📷 Lihat Foto</a>
        </p>
    <?php endif; ?>
    <p><b>Tanggal:</b> <?= e(date('d/m/Y H:i', strtotime($tl['tgl_tl']))) ?></p>
</div>
<?php endif; ?>

<div class="card">
    <h3>Riwayat Status</h3>
    <div class="timeline">
        <?php foreach ($riwayat as $r): ?>
            <div class="timeline-item">
                <div class="tl-status"><?= e($r['status']) ?></div>
                <div class="tl-time"><?= e(date('d/m/Y H:i', strtotime($r['waktu']))) ?> — <?= e($r['pelaku'] ?: 'Sistem') ?></div>
                <?php if ($r['keterangan']): ?>
                    <div class="tl-note"><?= e($r['keterangan']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="no-print">
    <a href="javascript:history.back()" class="btn btn-secondary">← Kembali</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>