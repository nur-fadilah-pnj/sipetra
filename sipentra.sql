-- =============================================
-- DATABASE SIPENTRA
-- Sistem Informasi Pengaduan Trantibum
-- Kecamatan Medan Petisah
-- =============================================

CREATE DATABASE IF NOT EXISTS sipentra 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sipentra;

-- ---------------------------------------------
-- Tabel pengguna (semua role)
-- ---------------------------------------------
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    peran ENUM('admin','camat','kasi','petugas') NOT NULL,
    no_hp VARCHAR(20),
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabel kategori pengaduan
-- ---------------------------------------------
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabel pengaduan (inti)
-- ---------------------------------------------
CREATE TABLE pengaduan (
    id_pengaduan INT AUTO_INCREMENT PRIMARY KEY,
    no_surat VARCHAR(50) UNIQUE NOT NULL,
    tgl_diterima DATE NOT NULL,
    nama_pelapor VARCHAR(100),
    alamat VARCHAR(255),
    lokasi_kejadian VARCHAR(255) NOT NULL,
    id_kategori INT,
    isi_pengaduan TEXT NOT NULL,
    berkas_pendukung VARCHAR(255),
    status ENUM(
        'Diterima',
        'Menunggu Persetujuan Camat',
        'Disetujui',
        'Ditindaklanjuti',
        'Selesai',
        'Ditolak'
    ) DEFAULT 'Diterima',
    id_admin INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE SET NULL,
    FOREIGN KEY (id_admin) REFERENCES pengguna(id_pengguna) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabel disposisi (oleh Camat)
-- ---------------------------------------------
CREATE TABLE disposisi (
    id_disposisi INT AUTO_INCREMENT PRIMARY KEY,
    id_pengaduan INT NOT NULL,
    id_camat INT NOT NULL,
    instruksi TEXT,
    status_disposisi ENUM('Disetujui','Ditolak') NOT NULL,
    alasan_penolakan TEXT,
    tgl_disposisi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengaduan) REFERENCES pengaduan(id_pengaduan) ON DELETE CASCADE,
    FOREIGN KEY (id_camat) REFERENCES pengguna(id_pengguna)
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabel arahan (oleh Kasi ke Petugas)
-- ---------------------------------------------
CREATE TABLE arahan (
    id_arahan INT AUTO_INCREMENT PRIMARY KEY,
    id_pengaduan INT NOT NULL,
    id_kasi INT NOT NULL,
    id_petugas INT NOT NULL,
    arahan TEXT NOT NULL,
    tgl_arahan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengaduan) REFERENCES pengaduan(id_pengaduan) ON DELETE CASCADE,
    FOREIGN KEY (id_kasi) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_petugas) REFERENCES pengguna(id_pengguna)
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabel tindak lanjut (oleh Petugas)
-- ---------------------------------------------
CREATE TABLE tindak_lanjut (
    id_tl INT AUTO_INCREMENT PRIMARY KEY,
    id_pengaduan INT NOT NULL,
    id_petugas INT NOT NULL,
    hasil_penanganan TEXT NOT NULL,
    dokumentasi VARCHAR(255),
    tgl_tl DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengaduan) REFERENCES pengaduan(id_pengaduan) ON DELETE CASCADE,
    FOREIGN KEY (id_petugas) REFERENCES pengguna(id_pengguna)
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabel riwayat status
-- ---------------------------------------------
CREATE TABLE riwayat_status (
    id_riwayat INT AUTO_INCREMENT PRIMARY KEY,
    id_pengaduan INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    id_pengguna INT,
    keterangan TEXT,
    waktu DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengaduan) REFERENCES pengaduan(id_pengaduan) ON DELETE CASCADE,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- DATA AWAL
-- =============================================

-- Kategori pengaduan
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Pedagang Kaki Lima', 'Lapak berdagang di trotoar atau badan jalan'),
('Parkir Liar', 'Kendaraan parkir di tempat terlarang'),
('Kebisingan', 'Suara mengganggu pada jam istirahat'),
('Bangunan Melanggar', 'Bangunan atau reklame tanpa izin'),
('Gepeng/Anjal', 'Gelandangan, pengemis, dan anak jalanan'),
('Gangguan Keamanan', 'Perselisihan warga atau kenakalan remaja'),
('Fasilitas Umum', 'Penyalahgunaan fasilitas umum'),
('Lainnya', 'Pengaduan trantibum lainnya');

-- Pengguna awal (password semua: 123456)
-- Hash bcrypt untuk "123456"
INSERT INTO pengguna (nama, username, password, peran, no_hp) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567890'),
('Dr. Roberto Kaban', 'camat', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'camat', '081234567891'),
('Muhammad Andika', 'kasi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasi', '081234567892'),
('Petugas Lapangan', 'petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', '081234567893');

-- Contoh data pengaduan
INSERT INTO pengaduan (no_surat, tgl_diterima, nama_pelapor, alamat, lokasi_kejadian, id_kategori, isi_pengaduan, status, id_admin) VALUES
('001/PKL/2026', '2026-08-05', 'Budi Santoso', 'Jl. Sei Putih No. 10', 'Trotoar Jl. S. Parman', 1, 'PKL berjualan di trotoar sehingga pejalan kaki tidak bisa lewat.', 'Diterima', 1),
('002/PKL/2026', '2026-08-07', 'Siti Aminah', 'Jl. Petisah Tengah', 'Depan Pasar Petisah', 2, 'Banyak kendaraan parkir liar di bahu jalan.', 'Diterima', 1),
('003/PKL/2026', '2026-08-10', 'Ahmad Yusuf', 'Jl. Sei Sikambing', 'Simpang Sei Sikambing', 3, 'Musik keras dari kafe sampai jam 2 pagi.', 'Diterima', 1);