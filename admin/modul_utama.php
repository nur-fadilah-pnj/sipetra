<?php
$title = "Modul Utama Pengaduan";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['admin']);

// Filter (Pencarian & Status)
$where  = [];
$params = [];

if (!empty($_GET['q'])) {
    $where[] = "(no_surat LIKE ? OR lokasi_kejadian LIKE ? OR nama_pelapor LIKE ?)";
    $kw = '%' . $_GET['q'] . '%';
    array_push($params, $kw, $kw, $kw);
}
if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}
$sql_where = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    $sql_where
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$data = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Daftar Seluruh Pengaduan Masuk</h3>
        <a href="input_pengaduan.php" class="btn btn-primary btn-sm">➕ Tambah Pengaduan Baru</a>
    </div>
    
    <!-- Filter Pencarian -->
    <form method="GET" class="filter-bar">
        <input type="text" name="q" placeholder="Cari nomor surat / lokasi / pelapor..." value="<?= e($_GET['q'] ?? '') ?>">
        <select name="status">
            <option value="">-- Semua Status --</option>
            <?php foreach (['Diterima','Menunggu Persetujuan Camat','Disetujui','Ditindaklanjuti','Selesai','Ditolak'] as $s): ?>
                <option value="<?= $s ?>" <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">🔍 Cari</button>
        <a href="modul_utama.php" class="btn btn-secondary">Reset</a>
    </form>

    <!-- Tabel Data -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No Surat</th>
                    <th>Tanggal Masuk</th>
                    <th>Kategori</th>
                    <th>Lokasi Kejadian</th>
                    <th>Pelapor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $r): ?>
                <tr>
                    <td><b><?= e($r['no_surat']) ?></b></td>
                    <td><?= e(date('d/m/Y', strtotime($r['tgl_diterima']))) ?></td>
                    <td><?= e($r['nama_kategori'] ?? '-') ?></td>
                    <td><?= e($r['lokasi_kejadian']) ?></td>
                    <td><?= e($r['nama_pelapor'] ?: '-') ?></td>
                    <td><?= badge_status($r['status']) ?></td>
                    <td>
                        <a href="detail.php?id=<?= $r['id_pengaduan'] ?>" class="btn btn-sm btn-secondary">Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="7" style="text-align:center; color:var(--text-muted)">Tidak ada data pengaduan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>