-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Bulan Mei 2026 pada 08.28
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_sayur_pangan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_pesanan` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `harga_satuan` int(10) UNSIGNED NOT NULL,
  `subtotal` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 1, 5000, 5000),
(2, 2, 7, 15, 7000, 105000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_kategori` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `slug`, `created_at`) VALUES
(1, 'Sayuran', 'sayuran', '2026-05-11 17:05:11'),
(2, 'Buah', 'buah', '2026-05-11 17:05:11'),
(3, 'Rempah', 'rempah', '2026-05-11 17:05:11'),
(4, 'Pangan', 'pangan', '2026-05-11 17:05:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id`, `id_user`, `id_produk`, `jumlah`, `created_at`) VALUES
(3, 3, 7, 1, '2026-05-12 04:50:46'),
(5, 5, 1, 2, '2026-05-12 05:04:42'),
(6, 5, 8, 4, '2026-05-12 05:18:38'),
(7, 2, 8, 1, '2026-05-12 05:23:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_toko` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_toko`, `deskripsi`, `telepon`, `alamat`, `logo`, `created_at`) VALUES
(1, 'FapertaFarmShop', 'Produk segar langsung ke meja Anda.', '628123456789', 'Jl. Contoh No. 1, Medan', 'logo_a1a3c0c5e7ef50a6.png', '2026-05-11 17:05:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `total_harga` int(10) UNSIGNED NOT NULL,
  `nama_penerima` varchar(150) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `telepon` varchar(30) NOT NULL,
  `metode_pembayaran` enum('transfer_bank','cod','e_wallet') NOT NULL DEFAULT 'transfer_bank',
  `status` enum('menunggu','diproses','dikirim','selesai') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `id_user`, `total_harga`, `nama_penerima`, `alamat_pengiriman`, `telepon`, `status`, `created_at`) VALUES
(1, 3, 5000, 'saifu', 'jalani aja dulu', '098', 'selesai', '2026-05-12 04:47:53'),
(2, 5, 105000, 'anisa', 'medan', '098763456111', 'selesai', '2026-05-12 05:01:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(200) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` int(10) UNSIGNED NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `foto` longblob DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `id_kategori`, `nama_produk`, `deskripsi`, `harga`, `stok`, `foto`, `status`, `created_at`) VALUES
(1, 2, 'bayam', 'bayam segar', 5000, 3, 0x70726f64756b5f366236373064376632653064623165302e6a7067, 'aktif', '2026-05-12 04:25:58'),
(2, 1, 'terong', 'Sayur segar', 5000, 10, 0x70726f64756b5f333739386466366232643433653437652e6a7067, 'aktif', '2026-05-12 04:45:27'),
(3, 2, 'anggur', 'Buah segar', 15000, 10, 0x70726f64756b5f613565373832363332393534613433382e6a7067, 'aktif', '2026-05-12 04:47:53'),
(4, 2, 'pepaya', 'buah segar', 5000, 15, 0x70726f64756b5f383165666334393436393736616534382e6a7067, 'aktif', '2026-05-12 04:48:21'),
(5, 1, 'wortel', 'sayur segar', 10000, 20, 0x70726f64756b5f356466353462306439323466323939342e6a7067, 'aktif', '2026-05-12 04:48:41'),
(6, 1, 'tomat', 'sayur segar', 8000, 30, 0x70726f64756b5f656639313862323865376530666566362e6a7067, 'aktif', '2026-05-12 04:49:05'),
(7, 2, 'jeruk', 'buah segar', 7000, 15, 0x70726f64756b5f343238633935613865303131326639312e6a7067, 'aktif', '2026-05-12 04:49:31'),
(8, 2, 'Nenas', 'Buah segar', 7000, 10, 0x70726f64756b5f663066613739653461393962623536382e6a7067, 'aktif', '2026-05-12 04:51:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `telepon` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `telepon`, `alamat`, `status`, `created_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', 'admin', NULL, NULL, 'aktif', '2026-05-11 17:05:11'),
(2, 'Saifu', 'saifu@gmail.com', '$2y$10$1oHZsNA8Weun5xBOiyouietmLCrJ.eIIVWA4M0xddhn9H.U4ZOlcu', 'admin', NULL, NULL, 'aktif', '2026-05-12 03:48:34'),
(3, 'Badawi', 'badawi@gmail.com', '$2y$10$tAR1kY5Nr1I2fbxhCuZY6exy26URANhOgLO57JQtuWUVVKBoPlKOC', 'user', NULL, NULL, 'aktif', '2026-05-12 04:10:30'),
(4, 'khairul', 'khairul@gmail.com', '$2y$10$Fjj/CgL6JM7FFOXYePFRpuK36zKpDVULNUxecCc.SSaJlYCMnNcbi', 'admin', '0000', 'medan', 'aktif', '2026-05-12 04:16:57'),
(5, 'anisa', 'anisa@gmail.com', '$2y$10$DZpi5SOXApucBF3650.WE.p5tvfcVAURDuGs5MYqtud66IlnkcP1S', 'user', '098763456111', 'medan', 'aktif', '2026-05-12 05:00:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_pesanan_pesanan` (`id_pesanan`),
  ADD KEY `fk_detail_pesanan_produk` (`id_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_keranjang_user` (`id_user`),
  ADD KEY `fk_keranjang_produk` (`id_produk`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pesanan_user` (`id_user`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_pesanan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
