<?php
$title = "Kelola Pengguna";
require_once __DIR__ . '/../includes/functions.php';
cek_role(['admin']);

// Tambah pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $peran    = $_POST['peran'];
    $no_hp    = trim($_POST['no_hp']);

    if (strlen($password) < 6) {
        set_flash('error', 'Password minimal 6 karakter.');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO pengguna (nama, username, password, peran, no_hp) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $username, password_hash($password, PASSWORD_DEFAULT), $peran, $no_hp]);
            set_flash('success', "Pengguna '$nama' berhasil ditambahkan.");
        } catch (PDOException $ex) {
            set_flash('error', 'Gagal: username mungkin sudah digunakan.');
        }
    }
    redirect('admin/pengguna.php');
}

// Toggle status
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE pengguna SET status = IF(status='aktif','nonaktif','aktif') WHERE id_pengguna=? AND id_pengguna<>?")
        ->execute([$id, $_SESSION['id_pengguna']]);
    redirect('admin/pengguna.php', 'success', 'Status pengguna diperbarui.');
}

$data = $pdo->query("SELECT * FROM pengguna ORDER BY peran, nama")->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h3>Tambah Pengguna</h3>
    <form method="POST" class="form-grid">
        <input type="hidden" name="tambah" value="1">
        <div class="form-group"><label>Nama</label><input type="text" name="nama" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required minlength="6"></div>
        <div class="form-group"><label>Peran</label>
            <select name="peran" required>
                <option value="admin">Admin</option>
                <option value="camat">Camat</option>
                <option value="kasi">Kasi Trantibum</option>
                <option value="petugas">Petugas</option>
            </select>
        </div>
        <div class="form-group"><label>No HP</label><input type="text" name="no_hp"></div>
        <div class="form-group full"><button type="submit" class="btn btn-primary">+ Tambah Pengguna</button></div>
    </form>
</div>

<div class="card">
    <h3>Daftar Pengguna</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nama</th><th>Username</th><th>Peran</th><th>No HP</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $u): ?>
                <tr>
                    <td><?= e($u['nama']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e(ucfirst($u['peran'])) ?></td>
                    <td><?= e($u['no_hp'] ?: '-') ?></td>
                    <td><span class="badge badge-<?= $u['status']==='aktif'?'green':'red' ?>"><?= e($u['status']) ?></span></td>
                    <td>
                        <?php if ($u['id_pengguna'] != $_SESSION['id_pengguna']): ?>
                            <a href="?toggle=<?= $u['id_pengguna'] ?>" class="btn btn-sm btn-secondary" data-confirm="Ubah status pengguna ini?">Toggle</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>