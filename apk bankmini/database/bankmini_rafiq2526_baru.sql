-- =====================================================================
-- DATABASE BARU Bank Mini (SMK MVP)  -  bankmini_rafiq2526
--
-- !! File ini MENGHAPUS database bankmini_rafiq2526 lalu membuat ulang.
-- !! Semua data lama (akun lama, dll.) HILANG. Pakai hanya kalau memang mau mulai bersih.
--
-- Cara: phpMyAdmin -> tab Import (atau SQL) -> pilih file ini -> Go.
-- Login awal  ->  email: admin@bankmini.com   password: admin123
-- (segera ganti password lewat menu "akun pengguna" -> edit)
-- =====================================================================
DROP DATABASE IF EXISTS `bankmini_rafiq2526`;
CREATE DATABASE `bankmini_rafiq2526` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bankmini_rafiq2526`;

-- ---------------------------------------------------------------------
-- Tabel akun & login
-- ---------------------------------------------------------------------
CREATE TABLE `tbl_tipe_user` (
  `id_tipe_user` INT NOT NULL,
  `tipe_user`    VARCHAR(100) NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipe_user`),
  UNIQUE KEY `uk_tipe_user` (`tipe_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_tipe_user` (`id_tipe_user`, `tipe_user`) VALUES
  (1, 'Admin'), (2, 'Petugas'), (3, 'Pengawas'), (4, 'Nasabah');

CREATE TABLE `tbl_users` (
  `id_user`    VARCHAR(20)  NOT NULL,
  `email`      VARCHAR(100) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       INT NOT NULL DEFAULT 4,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `role` (`role`),
  CONSTRAINT `tbl_users_ibfk_1` FOREIGN KEY (`role`) REFERENCES `tbl_tipe_user` (`id_tipe_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_admin` (
  `id_admin`        INT NOT NULL AUTO_INCREMENT,
  `nama_admin`      VARCHAR(100) NOT NULL DEFAULT '',
  `telepon_admin`   VARCHAR(100) NOT NULL DEFAULT '',
  `alamat_admin`    VARCHAR(255) NOT NULL DEFAULT '',
  `jenis_kelamin`   ENUM('perempuan','Laki-laki') NOT NULL DEFAULT 'Laki-laki',
  `path_poto_admin` VARCHAR(255) NOT NULL DEFAULT '',
  `id_user`         VARCHAR(20) NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `uk_admin_user` (`id_user`),
  CONSTRAINT `tbl_admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tbl_users` (`id_user`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Akun admin awal
INSERT INTO `tbl_users` (`id_user`, `email`, `password`, `role`)
VALUES ('ADM0001', 'admin@bankmini.com', '$2y$10$HN/k2dEmJ6DxX5sRoKv8Je7CItnf7yZc/kTBOi/fOE/wOcSiTvy7m', 1);

INSERT INTO `tbl_admin` (`nama_admin`, `telepon_admin`, `alamat_admin`, `jenis_kelamin`, `id_user`)
VALUES ('Administrator', '081234567890', 'SMK MVP', 'Laki-laki', 'ADM0001');

-- ---------------------------------------------------------------------
-- Tabel lain (nasabah, setoran, dll.) - sama persis dengan database lama kamu
-- ---------------------------------------------------------------------
CREATE TABLE `jenis_setoran` (
  `id_js` int(10) NOT NULL,
  `nama_setoran` varchar(100) NOT NULL,
  `nilai_setoran` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `jurusan` (
  `id_jur` int(10) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL,
  `singkat_jur` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kelas` (
  `id_kel` int(10) NOT NULL,
  `tingkat_kelas` enum('1','2','3','4','5','6','7','8','9','10','11','12') NOT NULL,
  `singkat_jur` varchar(100) NOT NULL,
  `rombel` varchar(100) NOT NULL,
  `id_jur` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `nasabah` (
  `id_nasabah` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jk` enum('perempuan','Laki_laki') NOT NULL,
  `tmpt_lahir` varchar(100) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `no_tlpn` varchar(100) NOT NULL,
  `tanda_pengenal` varchar(100) NOT NULL,
  `id_jurusan` varchar(100) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `saldo` int(10) NOT NULL,
  `transaksi_terakhir` date NOT NULL,
  `nama_ibu` varchar(25) NOT NULL,
  `nama_ayah` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `no_pembayaran` varchar(100) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `no_pengenal` varchar(100) NOT NULL,
  `nama_nasabah` varchar(100) NOT NULL,
  `kelas` varchar(100) NOT NULL,
  `nama_setoran` varchar(100) NOT NULL,
  `jumlah` varchar(100) NOT NULL,
  `status` enum('Lunas','Belum lunas') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `penarikan` (
  `id_penarikan` int(10) NOT NULL,
  `no_penarikan` varchar(100) NOT NULL,
  `id_nasabah` varchar(100) NOT NULL,
  `nama_nasabah` varchar(100) NOT NULL,
  `jk` varchar(100) NOT NULL,
  `no_pengenal` varchar(100) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `ket` varchar(100) NOT NULL,
  `tanggal_penarikan` date NOT NULL,
  `jumlah` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `setoran` (
  `id_setoran` int(10) NOT NULL,
  `no_setoran` varchar(100) NOT NULL,
  `nama_nasabah` varchar(100) NOT NULL,
  `id_nasabah` varchar(100) NOT NULL,
  `tgl_setoran` date NOT NULL,
  `jumlah` varchar(100) NOT NULL,
  `no_pengenal` varchar(100) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `nama_setoran` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tanda_pengenal` (
  `id` int(10) NOT NULL,
  `tanda_pengenal` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tarik_bayar` (
  `id_tarik` int(11) NOT NULL,
  `no_tarik` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tanggal_tarik` date NOT NULL,
  `ket` varchar(100) NOT NULL,
  `jumlah` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `total_tarikbayar` (
  `id_total` int(10) NOT NULL,
  `no_pembayaran` varchar(100) NOT NULL,
  `no_tarik` varchar(100) NOT NULL,
  `no_pengenal` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(100) NOT NULL,
  `nama_setoran` varchar(100) NOT NULL,
  `ket` varchar(100) NOT NULL,
  `jumlah_b` varchar(100) NOT NULL,
  `jumlah|_t` varchar(100) NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transaksi_semua` (
  `id_semua` int(10) NOT NULL,
  `noo_setoran` varchar(100) NOT NULL,
  `no_penarikan` varchar(100) NOT NULL,
  `id_nasabah` varchar(100) NOT NULL,
  `nama_nasabah` varchar(100) NOT NULL,
  `no_pengenal` varchar(100) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `nama_setoran` varchar(100) NOT NULL,
  `ket` varchar(100) NOT NULL,
  `jumlah_setoran` varchar(100) NOT NULL,
  `jumlah_penarikan` varchar(100) NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `hak_akses` enum('admin','operator','teller') NOT NULL,
  `foto` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `jenis_setoran`
  ADD PRIMARY KEY (`id_js`);

ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kel`);

ALTER TABLE `nasabah`
  ADD PRIMARY KEY (`id_nasabah`);

ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`);

ALTER TABLE `penarikan`
  ADD PRIMARY KEY (`id_penarikan`);

ALTER TABLE `setoran`
  ADD PRIMARY KEY (`id_setoran`);

ALTER TABLE `tanda_pengenal`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tarik_bayar`
  ADD PRIMARY KEY (`id_tarik`);

ALTER TABLE `total_tarikbayar`
  ADD PRIMARY KEY (`id_total`);

ALTER TABLE `transaksi_semua`
  ADD PRIMARY KEY (`id_semua`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

