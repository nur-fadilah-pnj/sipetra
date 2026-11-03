<?php
$title = "Disposisi Masuk";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['kasi']);

$data = $pdo->query("
    SELECT p.*, k.nama_kategori, d.instruksi
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    LEFT JOIN disposisi d ON d.id_pengaduan = p.id_pengaduan
    WHERE p.status = 'Disetujui'
    ORDER BY p.created_at ASC
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h3>Pengaduan Disetujui — Beri Arahan</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No Surat</th><th>Lokasi</th><th>Instruksi Camat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($data as $r): ?>
                <tr>
                    <td><?= e($r['no_surat']) ?></td>
                    <td><?= e($r['lokasi_kejadian']) ?></td>
                    <td><?= e($r['instruksi'] ?: '-') ?></td>
                    <td><a href="arahan.php?id=<?= $r['id_pengaduan'] ?>" class="btn btn-sm btn-primary">Beri Arahan</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="4" style="text-align:center;color:#64748b">Tidak ada disposisi masuk.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>