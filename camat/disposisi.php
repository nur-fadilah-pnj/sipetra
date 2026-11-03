<?php
$title = "Form Disposisi";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['camat']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE id_pengaduan = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) redirect('camat/masuk.php', 'error', 'Data tidak ditemukan.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status_disposisi'];
    $inst   = trim($_POST['instruksi']);
    $alasan = trim($_POST['alasan_penolakan']);

    if ($status === 'Ditolak' && $alasan === '') {
        set_flash('error', 'Alasan penolakan wajib diisi.');
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO disposisi (id_pengaduan, id_camat, instruksi, status_disposisi, alasan_penolakan)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id, $_SESSION['id_pengguna'], $inst, $status, $alasan]);

            $new_status = ($status === 'Disetujui') ? 'Disetujui' : 'Ditolak';
            $pdo->prepare("UPDATE pengaduan SET status = ? WHERE id_pengaduan = ?")
                ->execute([$new_status, $id]);

            catat_riwayat($pdo, $id, $new_status, "Disposisi Camat: $status. " . ($inst ?: ''));

            $pdo->commit();
            redirect('camat/masuk.php', 'success', "Disposisi berhasil: $status");
        } catch (Exception $ex) {
            $pdo->rollBack();
            set_flash('error', 'Gagal: ' . $ex->getMessage());
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h3><?= e($p['no_surat']) ?> — <?= e($p['lokasi_kejadian']) ?></h3>
    <p style="margin:1rem 0;color:#475569"><?= nl2br(e($p['isi_pengaduan'])) ?></p>

    <form method="POST" class="form-grid">
        <div class="form-group">
            <label>Keputusan *</label>
            <select name="status_disposisi" required>
                <option value="Disetujui">✅ Disetujui</option>
                <option value="Ditolak">❌ Ditolak</option>
            </select>
        </div>
        <div class="form-group">
            <label>Alasan Penolakan (jika ditolak)</label>
            <input type="text" name="alasan_penolakan">
        </div>
        <div class="form-group full">
            <label>Instruksi / Arahan</label>
            <textarea name="instruksi" rows="3"></textarea>
        </div>
        <div class="full">
            <button type="submit" class="btn btn-success">💾 Simpan Disposisi</button>
            <a href="masuk.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>