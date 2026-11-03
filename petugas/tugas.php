<?php
$title = "Tugas Saya";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['petugas']);

$stmt = $pdo->prepare("
    SELECT p.*, a.arahan, a.tgl_arahan
    FROM arahan a
    JOIN pengaduan p ON a.id_pengaduan = p.id_pengaduan
    WHERE a.id_petugas = ? AND p.status = 'Ditindaklanjuti'
    ORDER BY a.tgl_arahan ASC
");
$stmt->execute([$_SESSION['id_pengguna']]);
$data = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h3>Daftar Tugas Aktif</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No Surat</th><th>Lokasi</th><th>Arahan</th><th>Tanggal Arahan</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($data as $r): ?>
                <tr>
                    <td><?= e($r['no_surat']) ?></td>
                    <td><?= e($r['lokasi_kejadian']) ?></td>
                    <td><?= e($r['arahan']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($r['tgl_arahan']))) ?></td>
                    <td><a href="hasil.php?id=<?= $r['id_pengaduan'] ?>" class="btn btn-sm btn-primary">Input Hasil</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="5" style="text-align:center;color:#64748b">Tidak ada tugas aktif.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>