<?php
$title = "Input Pengaduan";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['admin']);

$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$no_surat_auto = generate_no_surat($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $berkas = upload_file('berkas');

        $stmt = $pdo->prepare("
            INSERT INTO pengaduan
            (no_surat, tgl_diterima, nama_pelapor, alamat, lokasi_kejadian,
             id_kategori, isi_pengaduan, berkas_pendukung, status, id_admin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Diterima', ?)
        ");
        $stmt->execute([
            trim($_POST['no_surat']),
            $_POST['tgl_diterima'],
            trim($_POST['nama_pelapor']),
            trim($_POST['alamat']),
            trim($_POST['lokasi_kejadian']),
            (int)$_POST['id_kategori'],
            trim($_POST['isi_pengaduan']),
            $berkas,
            $_SESSION['id_pengguna'],
        ]);
        $id = $pdo->lastInsertId();
        catat_riwayat($pdo, $id, 'Diterima', 'Pengaduan diinput oleh admin');

        redirect('admin/riwayat.php', 'success', 'Pengaduan berhasil disimpan!');
    } catch (Exception $ex) {
        $error = $ex->getMessage();
    }
}

require __DIR__ . '/../includes/header.php';
?>
<?php if (!empty($error)): ?>
    <div class="flash flash-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST" enctype="multipart/form-data" class="form-grid">
        <div class="form-group">
            <label>Nomor Surat</label>
            <input type="text" name="no_surat" value="<?= e($no_surat_auto) ?>" required>
        </div>
        <div class="form-group">
            <label>Tanggal Diterima</label>
            <input type="date" name="tgl_diterima" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Nama Pelapor</label>
            <input type="text" name="nama_pelapor" placeholder="Opsional">
        </div>
        <div class="form-group">
            <label>Alamat Pelapor</label>
            <input type="text" name="alamat" placeholder="Opsional">
        </div>
        <div class="form-group full">
            <label>Lokasi Kejadian *</label>
            <input type="text" name="lokasi_kejadian" required>
        </div>
        <div class="form-group">
            <label>Kategori *</label>
            <select name="id_kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori as $k): ?>
                    <option value="<?= $k['id_kategori'] ?>"><?= e($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Berkas Pendukung (foto/dokumen, max 5MB)</label>
            <input type="file" name="berkas" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <div class="form-group full">
            <label>Isi Pengaduan *</label>
            <textarea name="isi_pengaduan" rows="5" required></textarea>
        </div>
        <div class="full">
            <button type="submit" class="btn btn-primary">💾 Simpan Pengaduan</button>
            <a href="riwayat.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>