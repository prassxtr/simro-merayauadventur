-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 16, 2026 at 11:01 AM
-- Server version: 10.9.2-MariaDB-log
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_merayau_adventure`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_penyewaan`
--

CREATE TABLE `detail_penyewaan` (
  `id` int(11) NOT NULL,
  `penyewaan_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_penyewaan`
--

INSERT INTO `detail_penyewaan` (`id`, `penyewaan_id`, `produk_id`, `jumlah`, `subtotal`) VALUES
(1, 1, 2, 1, '90000.00'),
(2, 2, 2, 1, '60000.00'),
(3, 2, 4, 2, '160000.00'),
(4, 3, 2, 1, '30000.00'),
(5, 4, 1, 2, '90000.00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `deskripsi`, `gambar`) VALUES
(1, 'Carrier/Tas', 'Tas carrier dan tas pendakian berbagai ukuran', 'carrier.jpg'),
(2, 'Camping Gear', 'Perlengkapan camping lengkap dan berkualitas', 'camping.jpg'),
(3, 'Tenda/Dome', 'Berbagai jenis tenda untuk camping', 'tenda.jpg'),
(4, 'Trekking Gear', 'Peralatan trekking dan hiking', 'trekking.jpg'),
(5, 'Cooking Set', 'Peralatan masak outdoor portable', 'cooking.jpg'),
(6, 'Lampu & ACC', 'Lampu dan aksesoris camping', 'lampu.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `penyewaan`
--

CREATE TABLE `penyewaan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nomor_pesanan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_sewa` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status_pembayaran` enum('pending','lunas','dibatalkan') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `status_sewa` enum('diproses','disewa','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci DEFAULT 'diproses',
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penyewaan`
--

INSERT INTO `penyewaan` (`id`, `user_id`, `nomor_pesanan`, `tanggal_sewa`, `tanggal_kembali`, `total_harga`, `status_pembayaran`, `status_sewa`, `bukti_pembayaran`, `created_at`) VALUES
(1, 2, 'MRW-20260716-001', '2026-07-17', '2026-07-19', '90000.00', 'pending', 'diproses', NULL, '2026-07-16 08:57:02'),
(2, 2, 'MRW-20260716-002', '2026-07-22', '2026-07-23', '220000.00', 'pending', 'diproses', NULL, '2026-07-16 09:34:25'),
(3, 2, 'MRW-20260716-003', '2026-07-24', '2026-07-24', '30000.00', 'pending', 'diproses', NULL, '2026-07-16 09:39:11'),
(4, 2, 'MRW-20260716-004', '2026-07-17', '2026-07-17', '90000.00', 'pending', 'diproses', 'bukti_MRW-20260716-004_1784195684.png', '2026-07-16 09:54:44');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_sewa` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('tersedia','disewa','maintenance') COLLATE utf8mb4_unicode_ci DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama_produk`, `kategori_id`, `deskripsi`, `harga_sewa`, `stok`, `gambar`, `status`, `created_at`) VALUES
(1, 'Tenda Dome Eiger 4 Orang', 3, 'Water-resistant 800-fill power down for freezing nights. Kapasitas 4 orang.', '45000.00', 3, 'tenda-merah.jpg', 'tersedia', '2026-07-16 08:21:09'),
(2, 'Tenda Dome Eiger 4 Orang', 3, 'Water-resistant 800-fill power down for freezing nights. Kapasitas 4 orang.', '30000.00', 0, 'carrier-biru.jpg', 'tersedia', '2026-07-16 08:21:09'),
(3, 'Tenda Dome Eiger 4 Orang', 3, 'Water-resistant 800-fill power down for freezing nights. Kapasitas 4 orang.', '35000.00', 4, 'tenda-kuning.jpg', 'tersedia', '2026-07-16 08:21:09'),
(4, 'Carrier 60L', 1, 'Tas carrier 60 liter dengan frame aluminum yang kuat dan nyaman', '40000.00', 6, 'carrier-hijau.jpg', 'tersedia', '2026-07-16 08:21:09'),
(5, 'Kompor Portable', 5, 'Kompor gas portable untuk outdoor yang efisien dan hemat', '25000.00', 6, 'kompor.jpg', 'tersedia', '2026-07-16 08:21:09'),
(6, 'Nesting 3 Susun', 5, 'Peralatan masak 3 susun anti lengket untuk camping', '30000.00', 4, 'nesting.jpg', 'tersedia', '2026-07-16 08:21:09'),
(7, 'Sleeping Bag', 2, 'Sleeping bag untuk suhu dingin hingga 5°C. Nyaman dan hangat', '35000.00', 10, 'sleeping.jpg', 'tersedia', '2026-07-16 08:21:09'),
(8, 'Sepatu Gunung', 4, 'Sepatu hiking anti slip dengan sol yang kuat dan tahan lama', '45000.00', 6, 'sepatu.jpg', 'tersedia', '2026-07-16 08:21:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telepon` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `no_telepon`, `alamat`, `role`, `created_at`) VALUES
(1, 'Admin Merayau', 'admin@merayau.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jl. Adventure No. 123', 'admin', '2026-07-16 08:21:09'),
(2, 'prasss', 'msyifaprasetyo@gmail.com', '$2y$10$JZf2i7hTjjYz/DF8m3wkIeynNvjecHkH1ngkuNrPdrGeo6DnHst6y', '089506701661', 'Ayani', 'customer', '2026-07-16 08:23:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_penyewaan`
--
ALTER TABLE `detail_penyewaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penyewaan_id` (`penyewaan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_penyewaan`
--
ALTER TABLE `detail_penyewaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penyewaan`
--
ALTER TABLE `penyewaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_penyewaan`
--
ALTER TABLE `detail_penyewaan`
  ADD CONSTRAINT `detail_penyewaan_ibfk_1` FOREIGN KEY (`penyewaan_id`) REFERENCES `penyewaan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_penyewaan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD CONSTRAINT `penyewaan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
