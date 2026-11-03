<?php
require_once __DIR__ . '/includes/functions.php';
if (isset($_SESSION['id_pengguna'])) { header("Location: " . BASE_URL . "/dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE username = ? AND status = 'aktif'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['nama']        = $user['nama'];
            $_SESSION['peran']       = $user['peran'];
            header("Location: " . BASE_URL . "/dashboard.php"); exit;
        }
    }
    $error = 'Username atau password salah!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login — SIPENTRA</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-hero">
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo" class="login-hero-logo" onerror="this.outerHTML='<div style=\'width:110px;height:110px;background:rgba(255,255,255,0.1);border-radius:24px;display:flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.5rem\'>🏛️</div>'">
        <h1>SIPENTRA</h1>
        <div class="divider-gold"></div>
        <p>Sistem Informasi Pengaduan Masyarakat dan Tindak Lanjut Bidang Trantibum Kecamatan Medan Petisah</p>
    </div>
    <div class="login-form-panel">
        <div class="login-card">
            <span class="welcome-tag">Selamat Datang</span>
            <h2>Masuk ke Akun</h2>
            <p class="subtitle">Silakan login untuk mengakses sistem</p>
            <?php if ($error): ?><div class="flash flash-error">❌ <?= e($error) ?></div><?php endif; ?>
            <form method="POST">
                <div class="login-field">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" required autofocus>
                </div>
                <div class="login-field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit">Masuk →</button>
            </form>
            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem;">
                Masyarakat? <a href="lacak.php" style="color: var(--primary); font-weight: bold; text-decoration: underline;">Lacak Pengaduan Anda di sini</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>