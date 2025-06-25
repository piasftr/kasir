
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 07:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kasir2`
--

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_cart` int(11) NOT NULL,
  `kode_barang` varchar(255) NOT NULL,
  `jenis` enum('makanan','minuman') NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `harga_barang` varchar(255) NOT NULL,
  `quantity` text NOT NULL,
  `subtotal` varchar(255) NOT NULL,
  `tgl_input` varchar(255) NOT NULL,
  `no_transaksi` varchar(255) NOT NULL,
  `bayar` varchar(255) NOT NULL,
  `kembalian` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporanku`
--

CREATE TABLE `laporanku` (
  `id_cart` int(11) NOT NULL,
  `kode_barang` varchar(255) NOT NULL,
  `jenis` enum('makanan','minuman') DEFAULT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `harga_barang` varchar(255) NOT NULL,
  `quantity` text NOT NULL,
  `subtotal` varchar(255) NOT NULL,
  `tgl_input` varchar(255) NOT NULL,
  `no_transaksi` varchar(255) NOT NULL,
  `bayar` varchar(255) NOT NULL,
  `kembalian` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporanku`
--

INSERT INTO `laporanku` (`id_cart`, `kode_barang`, `jenis`, `nama_barang`, `harga_barang`, `quantity`, `subtotal`, `tgl_input`, `no_transaksi`, `bayar`, `kembalian`) VALUES
(337, '4', 'makanan', 'Nasi Campur  + Telur', '12000', '1', '12000', '19 May 2025', 'AD19052025000052', '39000', '0'),
(338, '14', 'makanan', 'Nasi Mawut + Ayam', '13000', '1', '13000', '19 May 2025', 'AD19052025000052', '39000', '0'),
(339, '21', 'makanan', 'Gorengan', '2000', '2', '4000', '19 May 2025', 'AD19052025000052', '39000', '0'),
(340, '8', 'minuman', 'Energen', '5000', '2', '10000', '19 May 2025', 'AD19052025000052', '39000', '0');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id_login` int(11) NOT NULL,
  `nama_toko` varchar(50) NOT NULL,
  `user` varchar(250) NOT NULL,
  `pass` varchar(250) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `telp` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id_login`, `nama_toko`, `user`, `pass`, `alamat`, `telp`) VALUES
(1, 'KANTIN POLNES 2', 'admin', 'admin', 'Danau Polnes', '081293848895');

-- --------------------------------------------------------

--
-- Table structure for table `makanan`
--

CREATE TABLE `makanan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `kategori_lauk` enum('Telur','Ayam','Ayam Geprek') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `makanan`
--

INSERT INTO `makanan` (`id`, `nama`, `harga`, `kategori_lauk`) VALUES
(1, 'Soto Ayam', 13000, NULL),
(2, 'Lalapan Ayam', 13000, NULL),
(3, 'Nasi Ayam Geprek', 13000, NULL),
(4, 'Nasi Campur  + Telur', 12000, 'Telur'),
(5, 'Nasi Campur + Ayam', 13000, 'Ayam'),
(6, 'Nasi Campur +  Ayam Geprek', 13000, 'Ayam Geprek'),
(7, 'Nasi Pecel + Telur', 12000, 'Telur'),
(8, 'Nasi Pecel + Ayam', 13000, 'Ayam'),
(9, 'Nasi Pecel + Ayam Geprek', 13000, 'Ayam Geprek'),
(10, 'Nasi Goreng + Telur', 12000, 'Telur'),
(11, 'Nasi Goreng + Ayam', 13000, 'Ayam'),
(12, 'Nasi Goreng + Ayam Geprek', 13000, 'Ayam Geprek'),
(13, 'Nasi Mawut + Telur', 12000, 'Telur'),
(14, 'Nasi Mawut + Ayam', 13000, 'Ayam'),
(15, 'Nasi Mawut + Ayam Geprek', 13000, 'Ayam Geprek'),
(16, 'Pop Mie', 7000, NULL),
(17, 'Indomie Goreng / Kuah', 6000, NULL),
(18, 'Indomie + Telur', 10000, NULL),
(19, 'Indomie + Telur + Nasi', 13000, NULL),
(20, 'Gorengan ( 4 Biji )', 5000, NULL),
(21, 'Gorengan', 2000, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `minuman`
--

CREATE TABLE `minuman` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `minuman`
--

INSERT INTO `minuman` (`id`, `nama`, `harga`) VALUES
(1, 'Air Mineral', 5000),
(2, 'Nutrisari', 4000),
(3, 'Pop Ice', 4000),
(4, 'Hilo', 5000),
(5, 'Tora Cafe', 4000),
(6, 'Chocolatos', 5000),
(7, 'Beng Beng', 5000),
(8, 'Energen', 5000),
(9, 'Extra Joss / Kuku Bima', 5000),
(10, 'Extra Joss / Kuku Bima + Susu', 7000),
(11, 'Good Day Cappuccino', 5000),
(12, 'Tora Bika Capuccino', 5000),
(13, 'Kopi Hitam', 5000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_cart`);

--
-- Indexes for table `laporanku`
--
ALTER TABLE `laporanku`
  ADD PRIMARY KEY (`id_cart`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id_login`);

--
-- Indexes for table `makanan`
--
ALTER TABLE `makanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `minuman`
--
ALTER TABLE `minuman`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_cart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

--
-- AUTO_INCREMENT for table `laporanku`
--
ALTER TABLE `laporanku`
  MODIFY `id_cart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id_login` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `makanan`
--
ALTER TABLE `makanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `minuman`
--
ALTER TABLE `minuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- --------------------------------------------------------
-- Tabel untuk kategori pengeluaran
CREATE TABLE `kategori_pengeluaran` (
  `id_kategori` INT NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabel untuk mencatat pengeluaran
CREATE TABLE `pengeluaran` (
  `id_pengeluaran` INT NOT NULL AUTO_INCREMENT,
  `id_kategori` INT NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `jumlah` DECIMAL(10,2) NOT NULL,
  `tanggal` DATE NOT NULL,
  `waktu` TIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengeluaran`),
  FOREIGN KEY (`id_kategori`) REFERENCES `kategori_pengeluaran`(`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Insert default kategori pengeluaran
INSERT INTO kategori_pengeluaran (nama_kategori, deskripsi) VALUES
('Bahan Baku', 'Pembelian bahan baku untuk makanan dan minuman'),
('Gaji Karyawan', 'Gaji dan tunjangan karyawan'),
('Listrik & Air', 'Tagihan listrik dan air'),
('Gas', 'Pembelian gas untuk memasak'),
('Peralatan', 'Pembelian peralatan dapur dan restoran'),
('Transportasi', 'Biaya transportasi dan pengiriman'),
('Perbaikan', 'Biaya perbaikan dan maintenance'),
('Lain-lain', 'Pengeluaran lainnya');

-- --------------------------------------------------------
-- Insert contoh data pengeluaran
INSERT INTO `pengeluaran` (`id_kategori`, `deskripsi`, `jumlah`, `tanggal`, `waktu`) VALUES
(1, 'Beli beras 25kg', 150000, '2024-01-15', '08:30:00'),
(1, 'Beli sayuran segar', 75000, '2024-01-15', '09:00:00'),
(4, 'Isi ulang gas 12kg', 25000, '2024-01-16', '10:00:00'),
(2, 'Gaji karyawan mingguan', 500000, '2024-01-20', '17:00:00'),
(3, 'Bayar tagihan listrik', 120000, '2024-01-25', '14:30:00');
