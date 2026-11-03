<?php
$title = "Pengaduan Masuk";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['camat']);

$data = $pdo->query("
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.status = 'Diterima'
    ORDER BY p.tgl_diterima ASC
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h3>Pengaduan Menunggu Persetujuan</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No Surat</th><th>Tanggal</th><th>Kategori</th><th>Lokasi</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($data as $r): ?>
                <tr>
                    <td><?= e($r['no_surat']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($r['tgl_diterima']))) ?></td>
                    <td><?= e($r['nama_kategori'] ?? '-') ?></td>
                    <td><?= e($r['lokasi_kejadian']) ?></td>
                    <td>
                        <a href="disposisi.php?id=<?= $r['id_pengaduan'] ?>" class="btn btn-sm btn-primary">Disposisi</a>
                        <a href="<?= BASE_URL ?>/admin/detail.php?id=<?= $r['id_pengaduan'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="5" style="text-align:center;color:#64748b">Tidak ada pengaduan menunggu.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>