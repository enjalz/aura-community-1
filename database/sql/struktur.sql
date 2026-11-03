-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: aura_community_1
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `idPengguna` int NOT NULL,
  PRIMARY KEY (`idPengguna`),
  CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`idPengguna`) REFERENCES `pengguna` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aksescampaign`
--

DROP TABLE IF EXISTS `aksescampaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aksescampaign` (
  `idAkses` int NOT NULL AUTO_INCREMENT,
  `idCampaign` int NOT NULL,
  `idPengguna` int NOT NULL,
  PRIMARY KEY (`idAkses`),
  UNIQUE KEY `uq_akses_campaign_owner` (`idCampaign`,`idPengguna`),
  KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `aksescampaign_ibfk_1` FOREIGN KEY (`idCampaign`) REFERENCES `campaign` (`idCampaign`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `aksescampaign_ibfk_2` FOREIGN KEY (`idPengguna`) REFERENCES `ownerbrand` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `akunsosmed`
--

DROP TABLE IF EXISTS `akunsosmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akunsosmed` (
  `idAkun` int NOT NULL AUTO_INCREMENT,
  `idPengguna` int NOT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkProfil` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlahPengikut` int NOT NULL,
  `domisiliAudiens` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usiaDominan` enum('13-17','18-24','25-34','35-44','45+') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genderDominan` enum('pria','wanita') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fileDemografi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idAkun`),
  UNIQUE KEY `uq_akun_platform_username` (`platform`,`username`),
  KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `akunsosmed_ibfk_1` FOREIGN KEY (`idPengguna`) REFERENCES `influencer` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `campaign`
--

DROP TABLE IF EXISTS `campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign` (
  `idCampaign` int NOT NULL AUTO_INCREMENT,
  `idPengguna` int NOT NULL,
  `namaCampaign` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `targetAudiens` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategoriInfluencer` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lokasiCampaign` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `targetUsia` enum('13-17','18-24','25-34','35-44','45+') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimalPengikut` int NOT NULL DEFAULT '0',
  `feePerInfluencer` decimal(12,2) NOT NULL,
  `tanggalMulai` date NOT NULL,
  `tanggalSelesai` date NOT NULL,
  `tanggalUnggahMulai` date DEFAULT NULL,
  `tanggalUnggahSelesai` date DEFAULT NULL,
  `statusCampaign` enum('draft','berjalan','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `kuotaInfluencer` int NOT NULL,
  PRIMARY KEY (`idCampaign`),
  KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `campaign_ibfk_1` FOREIGN KEY (`idPengguna`) REFERENCES `admin` (`idPengguna`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `idFeedback` int NOT NULL AUTO_INCREMENT,
  `idHasil` int NOT NULL,
  `idPengguna` int NOT NULL,
  `isiFeedback` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalFeedback` date NOT NULL,
  PRIMARY KEY (`idFeedback`),
  KEY `idHasil` (`idHasil`),
  KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`idHasil`) REFERENCES `hasilpengerjaan` (`idHasil`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`idPengguna`) REFERENCES `admin` (`idPengguna`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hasilpengerjaan`
--

DROP TABLE IF EXISTS `hasilpengerjaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hasilpengerjaan` (
  `idHasil` int NOT NULL AUTO_INCREMENT,
  `idPendaftaran` int NOT NULL,
  `idAkun` int NOT NULL,
  `draftKonten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalPengirimanDraft` date NOT NULL,
  `linkKonten` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fileInsight` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlahViews` int DEFAULT NULL,
  `jumlahLikes` int DEFAULT NULL,
  `jumlahKomentar` int DEFAULT NULL,
  `jumlahShare` int DEFAULT NULL,
  `jumlahSave` int DEFAULT NULL,
  `jumlahPengikut` int DEFAULT NULL,
  `tanggalPengirimanInsight` date DEFAULT NULL,
  `statusDraft` enum('menunggu','revisi','disetujui') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `statusInsight` enum('belum_dikirim','menunggu','ditolak','disetujui') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_dikirim',
  PRIMARY KEY (`idHasil`),
  KEY `idPendaftaran` (`idPendaftaran`),
  KEY `idAkun` (`idAkun`),
  CONSTRAINT `hasilpengerjaan_ibfk_1` FOREIGN KEY (`idPendaftaran`) REFERENCES `pendaftarancampaign` (`idPendaftaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hasilpengerjaan_ibfk_2` FOREIGN KEY (`idAkun`) REFERENCES `akunsosmed` (`idAkun`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `influencer`
--

DROP TABLE IF EXISTS `influencer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `influencer` (
  `idPengguna` int NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statusVerifikasi` enum('menunggu','diverifikasi','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `statusBlacklist` tinyint(1) NOT NULL DEFAULT '0',
  `alasanBlacklist` text COLLATE utf8mb4_unicode_ci,
  `tanggalBlacklist` date DEFAULT NULL,
  PRIMARY KEY (`idPengguna`),
  CONSTRAINT `influencer_ibfk_1` FOREIGN KEY (`idPengguna`) REFERENCES `pengguna` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ketentuanplatform`
--

DROP TABLE IF EXISTS `ketentuanplatform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ketentuanplatform` (
  `idKetentuan` int NOT NULL AUTO_INCREMENT,
  `idCampaign` int NOT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenisKonten` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlahPostingan` int NOT NULL,
  PRIMARY KEY (`idKetentuan`),
  KEY `idCampaign` (`idCampaign`),
  CONSTRAINT `ketentuanplatform_ibfk_1` FOREIGN KEY (`idCampaign`) REFERENCES `campaign` (`idCampaign`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `laporancampaign`
--

DROP TABLE IF EXISTS `laporancampaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporancampaign` (
  `idLaporan` int NOT NULL AUTO_INCREMENT,
  `idCampaign` int NOT NULL,
  `idPengguna` int NOT NULL,
  `catatanEvaluasi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalSelesaiCampaign` date NOT NULL,
  `statusLaporan` enum('draft','final') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  PRIMARY KEY (`idLaporan`),
  UNIQUE KEY `idCampaign` (`idCampaign`),
  KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `laporancampaign_ibfk_1` FOREIGN KEY (`idCampaign`) REFERENCES `campaign` (`idCampaign`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `laporancampaign_ibfk_2` FOREIGN KEY (`idPengguna`) REFERENCES `admin` (`idPengguna`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ownerbrand`
--

DROP TABLE IF EXISTS `ownerbrand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ownerbrand` (
  `idPengguna` int NOT NULL,
  `namaBrand` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namaPerusahaan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idPengguna`),
  CONSTRAINT `ownerbrand_ibfk_1` FOREIGN KEY (`idPengguna`) REFERENCES `pengguna` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran` (
  `idPembayaran` int NOT NULL AUTO_INCREMENT,
  `idPendaftaran` int NOT NULL,
  `idPengguna` int NOT NULL,
  `idRekening` int NOT NULL,
  `jumlahPembayaran` decimal(12,2) NOT NULL,
  `tanggalPembayaran` date NOT NULL,
  `statusPembayaran` enum('pending','berhasil','gagal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `idTransaksiGateway` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktuKonfirmasi` datetime DEFAULT NULL,
  PRIMARY KEY (`idPembayaran`),
  UNIQUE KEY `idPendaftaran` (`idPendaftaran`),
  UNIQUE KEY `idTransaksiGateway` (`idTransaksiGateway`),
  KEY `idPengguna` (`idPengguna`),
  KEY `idRekening` (`idRekening`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`idPendaftaran`) REFERENCES `pendaftarancampaign` (`idPendaftaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`idPengguna`) REFERENCES `admin` (`idPengguna`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`idRekening`) REFERENCES `rekeninginfluencer` (`idRekening`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pendaftarancampaign`
--

DROP TABLE IF EXISTS `pendaftarancampaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftarancampaign` (
  `idPendaftaran` int NOT NULL AUTO_INCREMENT,
  `idCampaign` int NOT NULL,
  `idPengguna` int NOT NULL,
  `tanggalPendaftaran` date NOT NULL,
  `statusPendaftaran` enum('menunggu','diterima','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  PRIMARY KEY (`idPendaftaran`),
  UNIQUE KEY `uq_pendaftaran_campaign_influencer` (`idCampaign`,`idPengguna`),
  KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `pendaftarancampaign_ibfk_1` FOREIGN KEY (`idCampaign`) REFERENCES `campaign` (`idCampaign`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pendaftarancampaign_ibfk_2` FOREIGN KEY (`idPengguna`) REFERENCES `influencer` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna` (
  `idPengguna` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `noTelepon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `statusAkun` enum('aktif','nonaktif','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`idPengguna`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rekeninginfluencer`
--

DROP TABLE IF EXISTS `rekeninginfluencer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rekeninginfluencer` (
  `idRekening` int NOT NULL AUTO_INCREMENT,
  `idPengguna` int NOT NULL,
  `jenisRekening` enum('bank','ewallet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `namaPenyedia` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomorRekening` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namaPemilik` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idRekening`),
  UNIQUE KEY `idPengguna` (`idPengguna`),
  CONSTRAINT `rekeninginfluencer_ibfk_1` FOREIGN KEY (`idPengguna`) REFERENCES `influencer` (`idPengguna`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-03 13:08:08
