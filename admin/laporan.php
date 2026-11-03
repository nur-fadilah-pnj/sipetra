<?php
$title = "Laporan Rekapitulasi";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['admin','camat','kasi']);

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.tgl_diterima BETWEEN ? AND ?
    ORDER BY p.tgl_diterima ASC
");
$stmt->execute([$dari, $sampai]);
$data = $stmt->fetchAll();

$rekap_status = [];
foreach ($data as $r) {
    $rekap_status[$r['status']] = ($rekap_status[$r['status']] ?? 0) + 1;
}

require __DIR__ . '/../includes/header.php';
?>

<!-- Print Header (hanya tampil saat print) -->
<div class="print-header">
    <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo"
         onerror="this.style.display='none'">
    <h2>PEMERINTAH KOTA MEDAN<br>KECAMATAN MEDAN PETISAH</h2>
    <p>Jl. Sultan Iskandar Muda No. 270 A, Kec. Medan Petisah, Kota Medan</p>
    <hr style="margin-top:0.8rem">
    <h3 style="margin-top:0.8rem">LAPORAN REKAPITULASI PENGADUAN TRANTIBUM</h3>
    <p>Periode: <?= e(date('d/m/Y', strtotime($dari))) ?> — <?= e(date('d/m/Y', strtotime($sampai))) ?></p>
</div>

<div class="card no-print">
    <form method="GET" class="filter-bar">
        <input type="date" name="dari" value="<?= e($dari) ?>">
        <input type="date" name="sampai" value="<?= e($sampai) ?>">
        <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
        <a href="export_excel.php?dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>"
           class="btn btn-success">📊 Export Excel</a>
        <button type="button" class="btn btn-accent" onclick="window.print()">🖨️ Cetak / PDF</button>
    </form>
</div>

<div class="card">
    <h3>📊 Ringkasan Status</h3>
    <div class="table-wrap" style="margin-top:1rem">
        <table>
            <thead><tr><th>Status</th><th>Jumlah</th></tr></thead>
            <tbody>
            <?php foreach ($rekap_status as $s => $j): ?>
                <tr><td><?= e($s) ?></td><td><b><?= $j ?></b></td></tr>
            <?php endforeach; ?>
            <tr style="background:#f8fafc;font-weight:bold">
                <td>TOTAL</td><td><?= count($data) ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>📋 Detail Pengaduan</h3>
    <div class="table-wrap" style="margin-top:1rem">
        <table>
            <thead>
                <tr><th>No</th><th>No Surat</th><th>Tanggal</th><th>Kategori</th><th>Lokasi</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($data as $r): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= e($r['no_surat']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($r['tgl_diterima']))) ?></td>
                    <td><?= e($r['nama_kategori'] ?? '-') ?></td>
                    <td><?= e($r['lokasi_kejadian']) ?></td>
                    <td><?= e($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>