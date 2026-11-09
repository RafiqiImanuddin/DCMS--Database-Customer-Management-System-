-- =========================================================================
-- DCMS GADGETMART - CORE DATABASE STRUCTURE & INITIAL DATA
-- Generated: 2026-11-09 11:45:44
-- File: dcms_core_database.sql (Ultra-fast phpMyAdmin Import)
-- Target DB on cPanel: `dcmssosm_gadgemart`
-- =========================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;
SET time_zone = '+00:00';

/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `broadcast_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `broadcast_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image_url` varchar(1000) DEFAULT NULL,
  `total_recipients` int(11) NOT NULL DEFAULT 0,
  `total_sent` int(11) NOT NULL DEFAULT 0,
  `total_failed` int(11) NOT NULL DEFAULT 0,
  `delivery_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `broadcast_batches_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `broadcast_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `broadcast_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `data` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `broadcast_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `broadcast_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `repeat_order_prediction_id` bigint(20) unsigned DEFAULT NULL,
  `broadcast_batch_id` bigint(20) unsigned DEFAULT NULL,
  `customer_vendor_code` varchar(100) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `channel` varchar(30) NOT NULL DEFAULT 'whatsapp',
  `message` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `provider_response` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `broadcast_reports_user_id_foreign` (`user_id`),
  KEY `broadcast_reports_repeat_order_prediction_id_foreign` (`repeat_order_prediction_id`),
  KEY `broadcast_reports_broadcast_batch_id_foreign` (`broadcast_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `broadcast_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `broadcast_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cross_selling_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cross_selling_imports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `batch_label` varchar(255) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'processing',
  `row_count` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `error_log` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cross_selling_imports_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cross_sellings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cross_sellings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cross_selling_import_id` bigint(20) unsigned DEFAULT NULL,
  `cabang` varchar(100) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `waktu` varchar(20) DEFAULT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `customer_vendor_code` varchar(100) DEFAULT NULL,
  `customer_vendor_name` varchar(255) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `dscription` text DEFAULT NULL,
  `itms_grp_nam` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `whs_code` varchar(100) DEFAULT NULL,
  `quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `harga_jual` decimal(18,2) NOT NULL DEFAULT 0.00,
  `harga_sebelum_potongan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `selisih_total_potongan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_bonus` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_voucher` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_nego` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_leasing` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_program` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_edc` decimal(18,2) NOT NULL DEFAULT 0.00,
  `p_otorisasi_posvit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `price_list` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `sc_hl_code` varchar(255) DEFAULT NULL,
  `slp_name` varchar(255) DEFAULT NULL,
  `promo` text DEFAULT NULL,
  `user_input` varchar(255) DEFAULT NULL,
  `no_hp_customer` varchar(50) DEFAULT NULL,
  `harga_modal` decimal(18,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `reseller` varchar(255) DEFAULT NULL,
  `beban_subsidi_leasing` decimal(18,2) NOT NULL DEFAULT 0.00,
  `transaksi_customer` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `no_reff` varchar(100) DEFAULT NULL,
  `no_po` varchar(100) DEFAULT NULL,
  `source_file` varchar(255) DEFAULT NULL,
  `source_row` int(11) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `period_label` varchar(7) DEFAULT NULL,
  `cleaning_hash` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cross_sellings_customer_item_date_idx` (`customer_vendor_code`,`item_code`,`posting_date`),
  KEY `cross_sellings_phone_date_idx` (`no_hp_customer`,`posting_date`),
  KEY `cross_sellings_cabang_date_idx` (`cabang`,`posting_date`),
  KEY `cross_sellings_doc_date_idx` (`document_number`,`posting_date`),
  KEY `cross_sellings_import_id_index` (`cross_selling_import_id`),
  KEY `cross_sellings_period_label_index` (`period_label`),
  KEY `cross_sellings_cleaning_hash_index` (`cleaning_hash`),
  KEY `cross_sellings_brand_index` (`brand`),
  KEY `cross_sellings_brand_period_idx` (`brand`,`period_label`)
) ENGINE=InnoDB AUTO_INCREMENT=195807 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'Karyawan',
  `target` decimal(18,2) NOT NULL DEFAULT 867261886.00,
  `cabang` varchar(50) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `joined_date` date DEFAULT NULL,
  `avatar_color` varchar(20) NOT NULL DEFAULT '#0284c7',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_nik_unique` (`nik`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kpi_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kpi_criteria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,2) NOT NULL DEFAULT 0.00,
  `target_benchmark` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kpi_criteria_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `repeat_order_prediction_id` bigint(20) unsigned DEFAULT NULL,
  `customer_vendor_code` varchar(100) DEFAULT NULL,
  `customer_vendor_name` varchar(255) DEFAULT NULL,
  `no_hp_customer` varchar(50) DEFAULT NULL,
  `classification` varchar(30) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `dscription` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'assigned',
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_customers_user_id_foreign` (`user_id`),
  KEY `lead_customers_repeat_order_prediction_id_foreign` (`repeat_order_prediction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `repeat_order_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `repeat_order_predictions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cross_selling_import_id` bigint(20) unsigned DEFAULT NULL,
  `customer_vendor_code` varchar(100) DEFAULT NULL,
  `customer_vendor_name` varchar(255) DEFAULT NULL,
  `no_hp_customer` varchar(50) DEFAULT NULL,
  `cabang` varchar(100) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `dscription` text DEFAULT NULL,
  `first_order_date` date DEFAULT NULL,
  `last_order_date` date DEFAULT NULL,
  `transaction_count` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `total_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `average_gap_days` decimal(8,2) NOT NULL DEFAULT 0.00,
  `predicted_repeat_date` date DEFAULT NULL,
  `confidence` smallint(6) NOT NULL DEFAULT 0,
  `classification` varchar(30) DEFAULT NULL,
  `period_label` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `repeat_order_predictions_customer_code_idx` (`customer_vendor_code`),
  KEY `repeat_order_predictions_no_hp_idx` (`no_hp_customer`),
  KEY `repeat_order_predictions_cabang_idx` (`cabang`),
  KEY `repeat_order_predictions_item_code_idx` (`item_code`),
  KEY `repeat_order_predictions_predicted_date_idx` (`predicted_repeat_date`),
  KEY `repeat_order_predictions_classification_idx` (`classification`),
  KEY `repeat_order_predictions_period_label_idx` (`period_label`),
  KEY `repeat_order_predictions_import_id_index` (`cross_selling_import_id`)
) ENGINE=InnoDB AUTO_INCREMENT=194302 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_assessments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `period` varchar(10) NOT NULL,
  `final_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grade` varchar(2) NOT NULL DEFAULT 'C',
  `scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scores`)),
  `evaluator_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_assessments_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sosmed_keeps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sosmed_keeps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` varchar(100) DEFAULT NULL,
  `keep_timestamp` datetime DEFAULT NULL,
  `nomor_sender` varchar(35) DEFAULT NULL,
  `grup_id` varchar(120) DEFAULT NULL,
  `sender` varchar(120) DEFAULT NULL,
  `grup` varchar(150) DEFAULT NULL,
  `jenis_chat` varchar(20) NOT NULL DEFAULT 'GRUP',
  `nama` varchar(150) DEFAULT NULL,
  `nomor_wa` varchar(35) DEFAULT '',
  `barang_keep` text DEFAULT NULL,
  `tanggal_ambil` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `remarks` varchar(120) DEFAULT NULL,
  `kode_sosmed` varchar(60) DEFAULT NULL,
  `report_ar` varchar(50) DEFAULT NULL,
  `tanggal_input_ar` datetime DEFAULT NULL,
  `ar_vlookup` varchar(50) DEFAULT NULL,
  `tanggal_vlookup` varchar(50) DEFAULT NULL,
  `cabang` varchar(50) DEFAULT NULL,
  `evidence` text DEFAULT NULL,
  `nomor_follow_up` varchar(35) DEFAULT NULL,
  `nama_tim_follow_up` varchar(120) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `feedback_tim_fu` text DEFAULT NULL,
  `nama_toko_kompetitor` varchar(150) DEFAULT NULL,
  `user_kredit` varchar(50) DEFAULT NULL,
  `periode` varchar(35) DEFAULT NULL,
  `item` varchar(150) DEFAULT NULL,
  `nomor_fu` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sosmed_keeps_cabang_periode_index` (`cabang`,`periode`),
  KEY `sosmed_keeps_status_periode_index` (`status`,`periode`),
  KEY `sosmed_keeps_message_id_index` (`message_id`),
  KEY `sosmed_keeps_keep_timestamp_index` (`keep_timestamp`),
  KEY `sosmed_keeps_nomor_wa_index` (`nomor_wa`),
  KEY `sosmed_keeps_remarks_index` (`remarks`),
  KEY `sosmed_keeps_kode_sosmed_index` (`kode_sosmed`),
  KEY `sosmed_keeps_report_ar_index` (`report_ar`),
  KEY `sosmed_keeps_cabang_index` (`cabang`),
  KEY `sosmed_keeps_status_index` (`status`),
  KEY `sosmed_keeps_periode_index` (`periode`),
  KEY `sosmed_keeps_nomor_fu_index` (`nomor_fu`)
) ENGINE=InnoDB AUTO_INCREMENT=13185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `turnover_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `turnover_imports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `batch_label` varchar(255) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'processing',
  `row_count` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `error_log` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnover_imports_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `turnovers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `turnovers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `turnover_import_id` bigint(20) unsigned DEFAULT NULL,
  `cabang` varchar(100) DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `slp_code` varchar(50) DEFAULT NULL,
  `slp_name` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `sales_cabang` varchar(100) DEFAULT NULL,
  `dpp` decimal(18,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `period_label` varchar(7) DEFAULT NULL,
  `cleaning_hash` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnovers_turnover_import_id_foreign` (`turnover_import_id`),
  KEY `turnovers_cabang_index` (`cabang`),
  KEY `turnovers_posting_date_index` (`posting_date`),
  KEY `turnovers_document_number_index` (`document_number`),
  KEY `turnovers_slp_name_index` (`slp_name`),
  KEY `turnovers_period_label_index` (`period_label`),
  KEY `turnovers_cleaning_hash_index` (`cleaning_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=63159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_chats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_vendor_code` varchar(100) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `cabang` varchar(100) DEFAULT NULL,
  `folder` varchar(30) NOT NULL DEFAULT 'inbox',
  `assigned_user_id` bigint(20) unsigned DEFAULT NULL,
  `format_type` varchar(50) DEFAULT NULL,
  `barang_keep` varchar(255) DEFAULT NULL,
  `tanggal_pengambilan` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `tags_json` text DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `unread_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_by` varchar(100) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_chats_customer_vendor_code_index` (`customer_vendor_code`),
  KEY `whatsapp_chats_phone_number_index` (`phone_number`),
  KEY `whatsapp_chats_assigned_user_id_index` (`assigned_user_id`),
  KEY `whatsapp_chats_is_resolved_index` (`is_resolved`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `whatsapp_chat_id` bigint(20) unsigned NOT NULL,
  `sender_type` varchar(20) NOT NULL DEFAULT 'customer',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `agent_name` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `media_type` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'delivered',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `reply_to` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reply_to`)),
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_payload`)),
  PRIMARY KEY (`id`),
  KEY `whatsapp_messages_whatsapp_chat_id_foreign` (`whatsapp_chat_id`),
  KEY `whatsapp_messages_message_id_index` (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;



-- =========================================================================
-- CORE INITIAL DATA (Users, Employees, KPI, Targets, Templates, Migrations)
-- =========================================================================

-- Dumping data for table `users`
LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
('1', 'Administrator DCMS', 'rafiqirfq24@gmail.com', NULL, '$2y$12$bpQ1nQhHv1yjC05Nwe5VC.3FbE62XH.a/6J/ZtRtzR/7vTKj/rGmq', NULL, '2026-11-02 16:41:15', '2026-11-02 16:41:15', 'superadmin'),
('2', 'Superadmin DCMS', 'superadmin@gmail.com', NULL, '$2y$12$cLIf0c8idZKCQkO.ZUaElOFBcc8Iq2pOh..D3ivb98FrAAU61WuTO', 'Gmvm6sjbcBf851SYPvKoyRlvkMCCBUsRkxTdsilvmh1tFcMhXcNyO1NAmuSE', '2026-11-03 09:47:30', '2026-11-03 11:34:26', 'superadmin'),
('3', 'Staff Admin DCMS', 'admin@gmail.com', NULL, '$2y$12$kF.DUKkn0vmq8RvS9.ImquDrPMRVnYbqyBjG7JvinxovBOK1Sj9N.', 'FyPPqwQhOZZbd8GyAOO2LHKkl7lLgfYGMQqamepfNXoefNIwagMYrykVPmeO', '2026-11-03 11:34:27', '2026-11-03 11:34:27', 'admin');
UNLOCK TABLES;

-- Dumping data for table `employees`
LOCK TABLES `employees` WRITE;
INSERT INTO `employees` (`id`, `nik`, `name`, `role`, `level`, `target`, `cabang`, `phone`, `email`, `status`, `joined_date`, `avatar_color`, `notes`, `created_at`, `updated_at`) VALUES
('1', 'BJM.SOS.018', 'MARISA', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '081250112233', 'N@gmail.com', 'active', '2024-01-15', '#2563eb', 'Fokus penjualan unit iPhone & Samsung flagship di cabang AMTC.', '2026-11-05 10:35:50', '2026-11-09 06:26:10'),
('2', 'BJM.SOS.004', 'BINTANG', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '085248991122', 'rizky.bjma@gadgetmart.id', 'active', '2024-03-01', '#059669', 'Spesialis follow-up broadcast customer repeat order area Banjarmasin.', '2026-11-05 10:35:50', '2026-11-07 10:12:09'),
('3', 'BJM.SOS.022', 'QONA', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '082155447788', 'N@gmail.com', 'active', '2023-11-10', '#7c3aed', 'Tingkat closing aksesoris dan add-on warranty tinggi.', '2026-11-05 10:35:50', '2026-11-09 06:25:55'),
('4', 'BJM.SOS.015', 'DANIEL', 'Admin Sales', 'Karyawan', '1181261572.00', 'Head Office', '081349887766', 'N@gmail.com', 'active', '2024-05-20', '#d97706', 'Aktif koordinasi broadcast promo cicilan 0% dan tukar tambah.', '2026-11-05 10:35:50', '2026-11-09 06:23:46'),
('5', 'BJM.SOS.017', 'MARLINA', 'Admin Sales', 'Karyawan', '1181261572.00', 'Head Office', '087811224455', 'N@gmail.com', 'active', '2024-02-18', '#db2777', 'Responsif terhadap pertanyaan customer WhatsApp dan keep order.', '2026-11-05 10:35:50', '2026-11-09 06:24:01'),
('6', 'BJM.SOS.005', 'SIFA', 'Admin Sales', 'Karyawan', '1181261572.00', 'Head Office', '081955331199', 'N@gmail.com', 'active', '2024-06-01', '#0891b2', 'Mengelola database customer baru cabang Tanjung Tabalong.', '2026-11-05 10:35:50', '2026-11-09 06:24:20'),
('7', 'BJM.SOS.007', 'FEBRI', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '123124512', 'N@gmail.com', 'active', NULL, '#059669', NULL, '2026-11-05 11:39:30', '2026-11-09 06:27:43'),
('8', 'BJM.SOS.016', 'DEA', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '12345', 'N@gmail.com', 'active', NULL, '#4f46e5', NULL, '2026-11-05 11:39:55', '2026-11-09 06:24:52'),
('9', 'BJM.SOS.019', 'ZOFIA', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '1234', 'N@gmail.com', 'active', NULL, '#ea580c', NULL, '2026-11-05 11:40:23', '2026-11-09 06:25:19'),
('10', 'BJM.SOS.021', 'RARA', 'Admin Sales', 'Internship', '784983749.00', 'Head Office', '1234', 'N@gmail.com', 'active', NULL, '#7c3aed', NULL, '2026-11-05 11:40:34', '2026-11-09 06:25:36');
UNLOCK TABLES;

-- Dumping data for table `kpi_criteria`
LOCK TABLES `kpi_criteria` WRITE;
INSERT INTO `kpi_criteria` (`id`, `code`, `name`, `description`, `weight`, `target_benchmark`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', 'resp_time', 'Respon Time (Kecepatan Balas)', 'Kecepatan membalas pesan masuk calon pembeli dan ketepatan waktu follow-up prospek saat jam kerja.', '25.00', 'Respon cepat < 3-5 menit, standby aktif', '1', '2', '2026-11-05 10:35:50', '2026-11-05 11:25:25'),
('2', 'broadcast_vol', 'Pencapaian Broadcast & Outreach', 'Kepatuhan kuota harian broadcast WhatsApp dan volume pelanggan yang dijangkau.', '20.00', 'Min. 500 kontak terjangkau/bulan', '0', '2', '2026-11-05 10:35:50', '2026-11-05 10:35:50'),
('3', 'closing_rate', 'Tingkat Konversi & Closing Leads', 'Persentase customer dari repeat order prediction / leads yang sukses belanja ulang.', '25.00', 'Tingkat konversi leads > 15%', '0', '3', '2026-11-05 10:35:50', '2026-11-05 10:35:50'),
('4', 'crm_discipline', 'Ketertiban CRM & Format Keep', 'Kedisiplinan input format keep, update status klaim/tracking AR, dan validitas nomor.', '15.00', '100% data terisi rapi & tepat waktu', '0', '4', '2026-11-05 10:35:50', '2026-11-05 10:35:50'),
('5', 'csat_quality', 'Kualitas Layanan & Etika Chat', 'Keramahan, kesopanan bahasa, ketepatan solusi promo, dan kepuasan pelanggan.', '15.00', 'Rating kepuasan pelanggan > 4.5/5', '0', '5', '2026-11-05 10:35:50', '2026-11-05 10:35:50'),
('6', 'chat_reply', 'Cara Balas Chat Konsumen', 'Kejelasan solusi, kecocokan jawaban dengan kebutuhan konsumen, gaya komunikasi persuasif dan tidak kaku.', '25.00', 'Jawaban solutif, komunikatif & mudah dipahami', '1', '1', '2026-11-05 11:25:25', '2026-11-05 11:25:25'),
('7', 'ethics', 'Etika & Keramahan', 'Sopan santun bahasa, greeting pembuka & penutup yang hangat, kesabaran melayani, dan nada bicara profesional.', '25.00', 'Greeting lengkap, ramah, sopan & solutif', '1', '3', '2026-11-05 11:25:25', '2026-11-05 11:25:25'),
('8', 'promo_check', 'Kesesuaian Penawaran Promo', 'Kepatuhan menawarkan program promo yang sedang berlangsung (bundling gadget, voucher, diskon aksesoris, cicilan) dan keakuratan syarat.', '25.00', 'Aktif tawarkan promo yang berlaku & info akurat', '1', '4', '2026-11-05 11:25:25', '2026-11-05 11:25:25');
UNLOCK TABLES;

-- Dumping data for table `broadcast_templates`
LOCK TABLES `broadcast_templates` WRITE;
INSERT INTO `broadcast_templates` (`id`, `user_id`, `title`, `message`, `image_url`, `created_at`, `updated_at`) VALUES
('1', NULL, 'Follow Up Repeat Order (Jadwal Beli Kembali)', 'Halo Kak {nama}! Terima kasih telah setia bersama Gadgetmart. Berdasarkan riwayat belanja, kemungkinan saatnya Kakak memperbarui kebutuhan seputar {item}. Dapatkan promo tukar tambah menarik & garansi resmi hanya di Gadgetmart. Balas pesan ini untuk info promo hari ini ya! 📱✨', NULL, '2026-11-03 15:32:18', '2026-11-03 15:32:18'),
('2', NULL, 'Promo Khusus Aksesoris & Proteksi (ACC)', 'Halo Kak {nama}, gimana kabar gadget-nya? Supaya perangkat Kakak selalu terlindungi, Gadgetmart ada promo diskon spesial untuk tempered glass, case original, adaptor fast charging, & audio. Yuk mampir ke toko atau balas pesan ini untuk pilihan katalognya! 🎧⚡', NULL, '2026-11-03 15:32:19', '2026-11-03 15:32:19'),
('3', NULL, 'Greeting & Cek Kepuasan Konsumen', 'Halo Kak {nama}, salam hangat dari tim Gadgetmart! Kami ingin memastikan apakah perangkat atau belanjaan Kakak berfungsi dengan nyaman. Jika ada pertanyaan, servis, atau kebutuhan aksesoris, tim kami siap membantu sepenuh hati. Have a wonderful day! 😊🙌', NULL, '2026-11-03 15:32:20', '2026-11-03 15:32:20'),
('4', NULL, 'Voucher Eksklusif Pelanggan Setia (VIP)', 'Spesial untuk Kak {nama}! Sebagai apresiasi pelanggan setia Gadgetmart, kami siapkan voucher cashback belanja untuk transaksi berikutnya. Tunjukkan pesan WhatsApp resmi ini ke kasir store Gadgetmart terdekat untuk klaim keuntungannya ya Kak! 🎁🛍️', NULL, '2026-11-03 15:32:21', '2026-11-03 15:32:21');
UNLOCK TABLES;

-- Dumping data for table `migrations`
LOCK TABLES `migrations` WRITE;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_11_02_030358_create_cross_sellings_table', '1'),
('5', '2026_11_02_031528_create_cross_selling_imports_table', '1'),
('6', '2026_11_02_031529_create_repeat_order_predictions_table', '1'),
('7', '2026_11_02_031530_create_broadcast_reports_table', '1'),
('8', '2026_11_02_031829_add_period_and_import_to_cross_sellings_table', '1'),
('9', '2026_11_02_055749_add_cross_selling_prediction_performance_indexes', '1'),
('10', '2026_11_02_063458_add_brand_filter_indexes_to_cross_sellings_table', '1'),
('11', '2026_11_02_063858_add_prediction_item_brand_filter_index', '1'),
('12', '2026_11_02_065250_create_lead_customers_table', '1'),
('13', '2026_11_02_123503_add_cleaning_hash_to_cross_sellings_table', '1'),
('14', '2026_11_03_033307_add_role_to_users_table', '2'),
('15', '2026_11_03_041500_add_lookup_index_to_cross_sellings_table', '3'),
('16', '2026_11_03_043000_create_settings_table', '4'),
('17', '2026_11_03_044500_create_whatsapp_chats_tables', '5'),
('18', '2026_11_03_050000_create_broadcast_templates_and_media_tables', '6'),
('19', '2026_11_03_060000_create_broadcast_batches_table', '7'),
('20', '2026_11_04_010000_purge_empty_phone_records_and_backfill', '8'),
('21', '2026_11_05_020000_create_employees_and_sales_monitoring_tables', '9'),
('22', '2026_11_05_030000_update_kpi_criteria_to_daily_operational', '10'),
('23', '2026_11_05_060610_create_turnovers_and_turnover_imports_tables', '11'),
('24', '2026_11_05_140002_add_omnichannel_fields_to_whatsapp_tables', '12'),
('25', '2026_11_06_100000_add_target_to_employees_table', '13'),
('27', '2026_11_07_055147_create_sosmed_keeps_table', '14'),
('28', '2026_11_07_095427_add_level_to_employees_table', '15');
UNLOCK TABLES;

-- Dumping data for table `settings`
LOCK TABLES `settings` WRITE;
INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
('1', 'watzap_api_key', 'VMUXOT68QD5TQ84V', '2026-11-03 13:03:13', '2026-11-03 13:03:13'),
('2', 'watzap_number_key', 'pL46HTdCO6vUgNfC', '2026-11-03 13:03:13', '2026-11-03 13:03:13'),
('3', 'watzap_web_url', 'https://watzap.chat', '2026-11-03 13:03:14', '2026-11-03 13:03:14'),
('4', 'watzap_inbox_same_as_broadcast', '1', '2026-11-04 15:47:02', '2026-11-04 17:29:10'),
('5', 'watzap_inbox_api_key', '', '2026-11-04 15:47:02', '2026-11-04 16:44:20'),
('6', 'watzap_inbox_number_key', '', '2026-11-04 15:47:02', '2026-11-04 16:44:20'),
('7', 'broadcast_delay_min', '5', '2026-11-04 17:08:23', '2026-11-04 17:08:23'),
('8', 'broadcast_delay_max', '15', '2026-11-04 17:08:23', '2026-11-04 17:08:23'),
('9', 'broadcast_batch_size', '20', '2026-11-04 17:08:23', '2026-11-04 17:08:23'),
('10', 'broadcast_batch_cooldown', '30', '2026-11-04 17:08:23', '2026-11-04 17:08:23'),
('11', 'broadcast_spintax_enabled', '1', '2026-11-04 17:08:23', '2026-11-04 17:08:23'),
('12', 'broadcast_daily_limit', '20', '2026-11-04 17:08:23', '2026-11-04 18:03:36'),
('13', 'workspace_monthly_targets', '{\n    \"Banjarmasin\": {\n        \"target\": 50000000\n    },\n    \"2026-11\": {\n        \"month\": 11,\n        \"year\": 2026,\n        \"teamTarget\": 9038670959,\n        \"salesTargets\": {\n            \"BJM.SOS.015\": 1181261572,\n            \"DANIEL\": 1181261572,\n            \"BJM.SOS.017\": 1181261572,\n            \"MARLINA\": 1181261572,\n            \"BJM.SOS.005\": 1181261572,\n            \"SIFA\": 1181261572,\n            \"BJM.SOS.018\": 784983749,\n            \"MARISA\": 784983749,\n            \"BJM.SOS.004\": 784983749,\n            \"BINTANG\": 784983749,\n            \"BJM.SOS.022\": 784983749,\n            \"QONA\": 784983749,\n            \"BJM.SOS.007\": 784983749,\n            \"FEBRI\": 784983749,\n            \"BJM.SOS.016\": 784983749,\n            \"DEA\": 784983749,\n            \"BJM.SOS.019\": 784983749,\n            \"ZOFIA\": 784983749,\n            \"BJM.SOS.021\": 784983749,\n            \"RARA\": 784983749\n        }\n    }\n}', '2026-11-05 13:02:02', '2026-11-09 07:13:38'),
('14', 'workspace_sales_photos', '{\n    \"Sales Ahmad\": \"data:image\\/png;base64,samplephoto\"\n}', '2026-11-05 13:02:05', '2026-11-05 13:02:05'),
('15', 'workspace_monthly_archives', '{\n    \"2026-10\": {\n        \"total\": 150000000\n    }\n}', '2026-11-05 13:02:07', '2026-11-05 13:02:07'),
('16', 'workspace_croselling_mtime', '1794210496942', '2026-11-05 14:32:15', '2026-11-09 07:48:16'),
('17', 'workspace_croselling_uploader', 'Superadmin DCMS', '2026-11-05 14:32:16', '2026-11-05 14:32:16'),
('18', 'workspace_croselling_filename', 'CROSELLING.xlsx', '2026-11-05 14:32:17', '2026-11-09 07:48:16'),
('19', 'workspace_croselling_filesize', '3620045', '2026-11-05 14:32:17', '2026-11-09 07:48:16'),
('20', 'workspace_croselling_file_base64', '', '2026-11-05 14:32:18', '2026-11-09 07:48:16'),
('21', 'workspace_turnover_mtime', '1794205766670', '2026-11-05 14:34:17', '2026-11-09 06:29:26'),
('22', 'workspace_turnover_uploader', 'Superadmin DCMS', '2026-11-05 14:34:18', '2026-11-05 14:34:18'),
('23', 'workspace_turnover_filename', 'TURN OVER.xlsx', '2026-11-05 14:34:20', '2026-11-05 14:34:20'),
('24', 'workspace_turnover_filesize', '2760596', '2026-11-05 14:34:21', '2026-11-09 06:29:26'),
('25', 'workspace_turnover_file_base64', '', '2026-11-05 14:34:23', '2026-11-09 06:29:26'),
('26', 'workspace_matrix_weights', '{\"omzet\":35,\"gp\":35,\"cross_selling\":30}', '2026-11-06 02:05:19', '2026-11-06 02:05:19'),
('27', 'workspace_keep_last_sync', '2026-11-09T18:43:46+08:00', '2026-11-09 08:33:35', '2026-11-09 10:43:46'),
('28', 'workspace_keep_last_count', '13184', '2026-11-09 08:33:35', '2026-11-09 08:33:35');
UNLOCK TABLES;


SET FOREIGN_KEY_CHECKS=1;
-- =========================================================================
-- ALL TABLES & CORE SEEDS CREATED SUCCESSFULLY!
-- =========================================================================
