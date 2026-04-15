-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 02 Feb 2026 pada 03.45
-- Versi server: 10.6.24-MariaDB-cll-lve
-- Versi PHP: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uwjqdfka_noric_management`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `produksi_borongan`
--

CREATE TABLE `produksi_borongan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_pekerjaan` varchar(100) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_upah` decimal(15,0) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produksi_borongan`
--

INSERT INTO `produksi_borongan` (`id`, `user_id`, `tanggal`, `jenis_pekerjaan`, `jumlah`, `total_upah`, `status`, `created_at`) VALUES
(6, 36, '2026-01-29', 'leter s rofi - n yamaha', 50, 55000, 'Approved', '2026-01-29 15:27:52'),
(7, 36, '2026-01-29', 'leter s rofi - k yamaha', 100, 120000, 'Approved', '2026-01-29 15:28:08'),
(8, 36, '2026-01-29', 'leter s rofi - k yamaha', 50, 60000, 'Approved', '2026-01-29 15:29:07'),
(9, 36, '2026-01-29', 'leter s rofi - tiger p', 50, 75000, 'Approved', '2026-01-29 15:29:45'),
(10, 36, '2026-01-29', 'leter s rofi - n yamaha', 100, 110000, 'Approved', '2026-01-29 15:29:58'),
(11, 36, '2026-01-30', 'leter s rofi - fu postret', 100, 150000, 'Rejected', '2026-01-30 03:06:54'),
(15, 38, '2026-01-30', 'las cacing - n honda', 650, 845000, 'Rejected', '2026-01-30 04:11:48'),
(16, 38, '2026-01-30', 'las cacing - k honda', 200, 220000, 'Rejected', '2026-01-30 04:12:09'),
(17, 38, '2026-01-30', 'las cacing - fu', 50, 75000, 'Rejected', '2026-01-30 04:12:27'),
(18, 38, '2026-01-30', 'las cacing - mp b', 150, 240000, 'Rejected', '2026-01-30 04:13:36'),
(19, 38, '2026-01-30', 'las cacing - tiger b', 50, 80000, 'Rejected', '2026-01-30 04:13:51'),
(20, 38, '2026-01-30', 'las cacing - mx king', 50, 75000, 'Rejected', '2026-01-30 04:14:49'),
(21, 38, '2026-01-30', 'las cacing - fu jengat', 100, 150000, 'Rejected', '2026-01-30 05:03:07'),
(22, 38, '2026-01-30', 'las cacing - fu postret', 200, 240000, 'Rejected', '2026-01-30 05:03:46'),
(23, 36, '2026-01-30', 'leter s rofi - fu postret', 100, 120000, 'Approved', '2026-01-30 06:32:45'),
(24, 38, '2026-01-30', 'las cacing - n honda', 650, 715000, 'Approved', '2026-01-30 10:29:46'),
(25, 38, '2026-01-30', 'las cacing - k honda', 350, 420000, 'Approved', '2026-01-30 10:30:52'),
(26, 38, '2026-01-30', 'las cacing - fu jengat', 100, 150000, 'Approved', '2026-01-30 10:31:04'),
(27, 38, '2026-01-30', 'las cacing - fu postret', 300, 360000, 'Approved', '2026-01-30 10:31:30'),
(28, 38, '2026-01-30', 'las cacing - Mp p', 150, 240000, 'Approved', '2026-01-30 10:32:18'),
(29, 38, '2026-01-30', 'las cacing - Tiger p', 50, 80000, 'Approved', '2026-01-30 10:32:30'),
(30, 38, '2026-01-30', 'las cacing - fu', 50, 75000, 'Approved', '2026-01-30 10:32:42'),
(31, 38, '2026-01-30', 'las cacing - sonic', 50, 75000, 'Approved', '2026-01-30 10:33:19'),
(32, 38, '2026-01-30', 'las cacing - L semi cacing', 100, 100000, 'Approved', '2026-01-30 10:33:48'),
(33, 38, '2026-01-30', 'las cacing - mx king', 50, 75000, 'Approved', '2026-01-30 10:34:03'),
(34, 38, '2026-01-30', 'las cacing - mb', 700, 350000, 'Approved', '2026-01-30 10:34:20'),
(35, 38, '2026-01-30', 'las cacing - m balung', 200, 100000, 'Approved', '2026-01-30 10:35:33'),
(36, 38, '2026-01-30', 'las cacing - Adaptor', 50, 50000, 'Approved', '2026-01-30 10:56:18'),
(37, 36, '2026-01-30', 'leter s rofi - k yamaha', 50, 60000, 'Approved', '2026-01-30 11:25:06'),
(38, 36, '2026-01-30', 'leter s rofi - fu postret', 50, 60000, 'Approved', '2026-01-30 11:25:22'),
(41, 35, '2026-01-30', 'leter s heri - mx king', 150, 225000, 'Approved', '2026-01-30 16:43:05'),
(42, 35, '2026-01-30', 'leter s heri - n samping', 250, 275000, 'Approved', '2026-01-30 16:44:11'),
(43, 35, '2026-01-30', 'leter s heri - kolong', 200, 240000, 'Approved', '2026-01-30 16:44:47'),
(44, 35, '2026-01-30', 'leter s heri - sonic sambung', 50, 80000, 'Approved', '2026-01-30 16:45:58'),
(45, 35, '2026-01-30', 'leter s heri - maten', 200, 160000, 'Approved', '2026-01-30 16:46:18'),
(46, 35, '2026-01-30', 'leter s heri - fu jengat', 50, 75000, 'Approved', '2026-01-30 16:46:43'),
(47, 35, '2026-01-30', 'leter s heri - Fu postret', 150, 180000, 'Approved', '2026-01-30 16:54:20'),
(48, 35, '2026-01-31', 'leter s heri - mp p', 150, 240000, 'Approved', '2026-01-31 04:36:34'),
(49, 37, '2026-01-31', 'halim - fu bending 32 35', 280, 1120000, 'Approved', '2026-01-31 05:06:31'),
(50, 36, '2026-01-31', 'leter s rofi - n yamaha', 75, 82500, 'Approved', '2026-01-31 07:39:02'),
(51, 36, '2026-01-31', 'leter s rofi - k yamaha', 50, 60000, 'Approved', '2026-01-31 07:39:15'),
(52, 38, '2026-01-31', 'las cacing - mx king', 100, 150000, 'Approved', '2026-01-31 08:14:20'),
(53, 38, '2026-01-31', 'las cacing - N yamaha', 100, 110000, 'Approved', '2026-01-31 08:14:34'),
(54, 38, '2026-01-31', 'las cacing - n honda', 100, 110000, 'Approved', '2026-01-31 08:14:45'),
(55, 35, '2026-01-31', 'leter s heri - kolong', 100, 120000, 'Approved', '2026-01-31 09:12:34'),
(56, 35, '2026-01-31', 'leter s heri - n samping', 50, 55000, 'Approved', '2026-01-31 09:12:47'),
(57, 41, '2026-01-31', 'monel - mp p', 150, 525000, 'Approved', '2026-01-31 09:35:33'),
(58, 41, '2026-01-31', 'monel - mx king', 150, 450000, 'Approved', '2026-01-31 09:36:10'),
(59, 41, '2026-01-31', 'monel - s yamaha', 675, 1350000, 'Approved', '2026-01-31 09:36:35'),
(60, 41, '2026-01-31', 'monel - k yamaha', 350, 525000, 'Approved', '2026-01-31 09:36:58'),
(61, 41, '2026-01-31', 'monel - k honda', 200, 300000, 'Approved', '2026-01-31 09:37:21'),
(62, 41, '2026-01-31', 'monel - k honda', 200, 300000, 'Rejected', '2026-01-31 09:39:54'),
(63, 41, '2026-01-31', 'Monel - Tiger', 50, 150000, 'Approved', '2026-01-31 09:40:15');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `produksi_borongan`
--
ALTER TABLE `produksi_borongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tanggal` (`tanggal`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `produksi_borongan`
--
ALTER TABLE `produksi_borongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `produksi_borongan`
--
ALTER TABLE `produksi_borongan`
  ADD CONSTRAINT `fk_produksi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
