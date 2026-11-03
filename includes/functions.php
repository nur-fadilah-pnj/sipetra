<?php
/**
 * Fungsi bantu global
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Cek apakah user sudah login
 */
function cek_login() {
    if (!isset($_SESSION['id_pengguna'])) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

/**
 * Cek role user (hanya role tertentu yang boleh akses)
 */
function cek_role(array $roles) {
    cek_login();
    if (!in_array($_SESSION['peran'], $roles, true)) {
        set_flash('error', 'Anda tidak memiliki akses ke halaman ini.');
        header("Location: " . BASE_URL . "/dashboard.php");
        exit;
    }
}

/**
 * Set flash message (pesan sekali tampil)
 */
function set_flash($tipe, $pesan) {
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/**
 * Ambil & hapus flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/**
 * Redirect dengan flash message
 */
function redirect($url, $tipe = null, $pesan = null) {
    if ($tipe && $pesan) set_flash($tipe, $pesan);
    header("Location: " . BASE_URL . "/" . ltrim($url, '/'));
    exit;
}

/**
 * Escape HTML (mencegah XSS)
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate nomor surat otomatis: 001/PKL/2026
 */
function generate_no_surat(PDO $pdo) {
    $tahun = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengaduan WHERE YEAR(created_at) = ?");
    $stmt->execute([$tahun]);
    $no = ((int)$stmt->fetchColumn()) + 1;
    return str_pad($no, 3, '0', STR_PAD_LEFT) . "/PKL/" . $tahun;
}

/**
 * Upload file (foto/dokumen) — return nama file atau null
 */
function upload_file($field, $allowed = ['jpg','jpeg','png','pdf']) {
    if (empty($_FILES[$field]['name'])) return null;

    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        throw new Exception("Format file tidak diizinkan. Gunakan: " . implode(', ', $allowed));
    }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
        throw new Exception("Ukuran file maksimal 5 MB.");
    }

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);

    $nama = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], UPLOAD_DIR . $nama)) {
        throw new Exception("Gagal mengunggah file.");
    }
    return $nama;
}

/**
 * Catat riwayat status
 */
function catat_riwayat(PDO $pdo, $id_pengaduan, $status, $keterangan = '') {
    $stmt = $pdo->prepare("
        INSERT INTO riwayat_status (id_pengaduan, status, id_pengguna, keterangan)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $id_pengaduan,
        $status,
        $_SESSION['id_pengguna'] ?? null,
        $keterangan
    ]);
}

/**
 * Label badge CSS berdasarkan status
 */
function badge_status($status) {
    $map = [
        'Diterima'                   => 'badge-blue',
        'Menunggu Persetujuan Camat' => 'badge-yellow',
        'Disetujui'                  => 'badge-green',
        'Ditindaklanjuti'            => 'badge-purple',
        'Selesai'                    => 'badge-emerald',
        'Ditolak'                    => 'badge-red',
    ];
    $cls = $map[$status] ?? 'badge-gray';
    return "<span class='badge {$cls}'>" . e($status) . "</span>";
}