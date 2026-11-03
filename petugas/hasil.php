<?php
$title = "Form Tindak Lanjut Petugas";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['petugas']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, k.nama_kategori FROM pengaduan p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori WHERE p.id_pengaduan = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) redirect('petugas/tugas.php', 'error', 'Data tidak ditemukan.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_tindakan   = $_POST['tgl_tindakan'];
    $bentuk         = trim($_POST['bentuk_tindakan']);
    $arahan         = trim($_POST['arahan']);
    
    try {
        $foto = upload_file('foto_dokumentasi', ['jpg','jpeg','png']);

        $pdo->beginTransaction();
        
        // Simpan ke tabel tindak_lanjut
        $stmt = $pdo->prepare("
            INSERT INTO tindak_lanjut (id_pengaduan, id_petugas, tgl_tindakan, bentuk_tindakan, hasil_penanganan, dokumentasi) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id, $_SESSION['id_pengguna'], $tgl_tindakan, $bentuk, $arahan, $foto]);

        // Ubah status pengaduan
        $pdo->prepare("UPDATE pengaduan SET status='Selesai' WHERE id_pengaduan=?")->execute([$id]);
        catat_riwayat($pdo, $id, 'Selesai', "Bentuk: $bentuk. " . $arahan);

        $pdo->commit();
        redirect('petugas/tugas.php', 'success', 'Laporan tindak lanjut berhasil disimpan. Status: Selesai.');
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('error', 'Gagal: ' . $ex->getMessage());
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="info-panel">
    <h4><?= e($p['no_surat']) ?> | <?= e($p['nama_kategori']) ?> - <?= e($p['lokasi_kejadian']) ?></h4>
</div>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <h3>Form Tindak Lanjut Petugas</h3>
    </div>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid" style="margin-bottom:1rem">
            <div class="form-group">
                <label>Tanggal tindakan</label>
                <input type="date" name="tgl_tindakan" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Bentuk tindakan</label>
                <select name="bentuk_tindakan" required>
                    <option value="">-- Pilih --</option>
                    <option value="Mediasi">Mediasi</option>
                    <option value="Penertiban">Penertiban</option>
                    <option value="Pembinaan">Pembinaan</option>
                    <option value="Koordinasi Instansi">Koordinasi Instansi</option>
                </select>
            </div>
        </div>

        <div class="form-group full" style="margin-bottom:1rem">
            <label>Uraian tindakan / hasil mediasi *</label>
            <textarea name="arahan" rows="4" required></textarea>
        </div>

        <div class="form-group full" style="margin-bottom:2rem">
            <label>Foto dokumentasi (Opsional)</label>
            <input type="file" name="foto_dokumentasi" accept="image/*">
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
            <a href="tugas.php" class="btn btn-secondary" style="background:#e2e8f0; color:#1a2233;">BATAL</a>
            <button type="submit" class="btn" style="background:#c2410c; color:white;">SIMPAN</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>