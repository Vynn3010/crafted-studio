-- =============================================
-- Crafted Studio Management System
-- Database Schema + Seed Data
-- =============================================

CREATE DATABASE IF NOT EXISTS `crafted_studio` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;
USE `crafted_studio`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `invoice`;
DROP TABLE IF EXISTS `review`;
DROP TABLE IF EXISTS `galeri_foto`;
DROP TABLE IF EXISTS `pembayaran`;
DROP TABLE IF EXISTS `editing_foto`;
DROP TABLE IF EXISTS `jadwal_fotografer`;
DROP TABLE IF EXISTS `booking`;
DROP TABLE IF EXISTS `studio`;
DROP TABLE IF EXISTS `fotografer`;
DROP TABLE IF EXISTS `editor`;
DROP TABLE IF EXISTS `staf`;
DROP TABLE IF EXISTS `paket_foto`;
DROP TABLE IF EXISTS `pelanggan`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Staf Table (Admin & Staff)
CREATE TABLE `staf` (
  `id_staf` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Editor Table
CREATE TABLE `editor` (
  `id_editor` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Pelanggan Table
CREATE TABLE `pelanggan` (
  `id_pelanggan` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `alamat` TEXT NULL,
  `tanggal_daftar` DATE NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Paket Foto Table
CREATE TABLE `paket_foto` (
  `id_paket` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_paket` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT NULL,
  `harga` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `durasi_jam` INT NOT NULL DEFAULT 1,
  `jumlah_foto` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Fotografer Table
CREATE TABLE `fotografer` (
  `id_fotografer` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `spesialisasi` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('aktif', 'cuti', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Studio Table
CREATE TABLE `studio` (
  `id_studio` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_ruangan` VARCHAR(100) NOT NULL,
  `kapasitas` INT NOT NULL DEFAULT 1,
  `background` VARCHAR(100) NOT NULL,
  `status` ENUM('tersedia', 'dipakai', 'maintenance') NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Booking Table
CREATE TABLE `booking` (
  `id_booking` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pelanggan` INT NOT NULL,
  `id_paket` INT NOT NULL,
  `id_fotografer` INT NULL,
  `id_studio` INT NULL,
  `tanggal_booking` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `status` ENUM('menunggu', 'dikonfirmasi', 'diproses', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu',
  `total_harga` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `catatan` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_booking_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_paket` FOREIGN KEY (`id_paket`) REFERENCES `paket_foto` (`id_paket`) ON DELETE RESTRICT,
  CONSTRAINT `fk_booking_fotografer` FOREIGN KEY (`id_fotografer`) REFERENCES `fotografer` (`id_fotografer`) ON DELETE SET NULL,
  CONSTRAINT `fk_booking_studio` FOREIGN KEY (`id_studio`) REFERENCES `studio` (`id_studio`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Jadwal Fotografer Table
CREATE TABLE `jadwal_fotografer` (
  `id_jadwal` INT AUTO_INCREMENT PRIMARY KEY,
  `id_fotografer` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `status` ENUM('tersedia', 'booking', 'libur') NOT NULL DEFAULT 'tersedia',
  CONSTRAINT `fk_jadwal_fotografer` FOREIGN KEY (`id_fotografer`) REFERENCES `fotografer` (`id_fotografer`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Editing Foto Table
CREATE TABLE `editing_foto` (
  `id_editing` INT AUTO_INCREMENT PRIMARY KEY,
  `id_booking` INT NOT NULL,
  `id_editor` INT NOT NULL,
  `tanggal_edit` DATETIME NULL,
  `status` ENUM('menunggu', 'editing', 'revisi', 'selesai') NOT NULL DEFAULT 'menunggu',
  `catatan` TEXT NULL,
  CONSTRAINT `fk_editing_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE CASCADE,
  CONSTRAINT `fk_editing_editor` FOREIGN KEY (`id_editor`) REFERENCES `editor` (`id_editor`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Pembayaran Table
CREATE TABLE `pembayaran` (
  `id_pembayaran` INT AUTO_INCREMENT PRIMARY KEY,
  `id_booking` INT NOT NULL,
  `tanggal_bayar` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `metode` VARCHAR(50) NOT NULL,
  `status` ENUM('DP', 'Lunas', 'Refund') NOT NULL DEFAULT 'DP',
  `bukti_bayar` VARCHAR(255) NULL,
  CONSTRAINT `fk_pembayaran_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Galeri Foto Table
CREATE TABLE `galeri_foto` (
  `id_foto` INT AUTO_INCREMENT PRIMARY KEY,
  `id_booking` INT NOT NULL,
  `nama_file` VARCHAR(255) NOT NULL,
  `url_file` VARCHAR(255) NOT NULL,
  `tanggal_upload` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  CONSTRAINT `fk_galeri_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Review Table
CREATE TABLE `review` (
  `id_review` INT AUTO_INCREMENT PRIMARY KEY,
  `id_booking` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `komentar` TEXT NULL,
  `tanggal_review` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_review_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Invoice Table
CREATE TABLE `invoice` (
  `id_invoice` INT AUTO_INCREMENT PRIMARY KEY,
  `id_booking` INT NOT NULL,
  `nomor_invoice` VARCHAR(50) NOT NULL UNIQUE,
  `tanggal_invoice` DATE NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `pajak` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`id_booking`) REFERENCES `booking` (`id_booking`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================
-- SEED DATA
-- =============================================

-- Admin (password: admin123)
INSERT INTO `staf` (`nama`, `no_hp`, `email`, `password`, `role`) VALUES
('Admin Crafted', '081234567890', 'admin@crafted.studio', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Fotografer (password: foto123)
INSERT INTO `fotografer` (`nama`, `no_hp`, `spesialisasi`, `email`, `password`) VALUES
('Budi Santoso', '081234567891', 'Wedding & Prewedding', 'budi@crafted.studio', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Rina Kartika', '081234567892', 'Portrait & Fashion', 'rina@crafted.studio', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Editor (password: edit123)
INSERT INTO `editor` (`nama`, `no_hp`, `email`, `password`) VALUES
('Dian Permata', '081234567893', 'dian@crafted.studio', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Paket Foto
INSERT INTO `paket_foto` (`nama_paket`, `deskripsi`, `harga`, `durasi_jam`, `jumlah_foto`) VALUES
('Paket Basic', 'Sesi foto 1 jam dengan 10 foto edit. Cocok untuk foto personal atau headshot profesional.', 350000.00, 1, 10),
('Paket Standard', 'Sesi foto 2 jam dengan 25 foto edit + 5 cetak. Ideal untuk keluarga atau couple.', 750000.00, 2, 25),
('Paket Premium', 'Sesi foto 3 jam dengan 50 foto edit + 10 cetak + album mini. Sempurna untuk prewedding.', 1500000.00, 3, 50),
('Paket Exclusive', 'Sesi foto full day dengan unlimited foto + album premium + video highlight.', 3500000.00, 8, 100);

-- Studio
INSERT INTO `studio` (`nama_ruangan`, `kapasitas`, `background`, `status`) VALUES
('Studio A - White Room', 5, 'Putih Polos', 'tersedia'),
('Studio B - Natural Light', 8, 'Jendela Besar + Tanaman', 'tersedia'),
('Studio C - Dark Mood', 4, 'Hitam Premium', 'tersedia');
