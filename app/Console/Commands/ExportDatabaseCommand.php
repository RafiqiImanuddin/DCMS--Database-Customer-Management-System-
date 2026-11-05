<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ExportDatabaseCommand extends Command
{
    protected $signature = 'db:export-cpanel {--full : Export full database including 112k+ transactions}';
    protected $description = 'Export database schema and data to MySQL-compatible SQL files for cPanel / phpMyAdmin';

    public function handle(): int
    {
        $this->info('🚀 Memulai ekspor database DCMS untuk hosting cPanel (MySQL / MariaDB)...');

        $dumpsDir = database_path('dumps');
        if (! is_dir($dumpsDir)) {
            mkdir($dumpsDir, 0755, true);
        }

        $coreSqlPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_cpanel_core.sql';
        $fullSqlPath = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_cpanel_full.sql';
        $zipPath     = $dumpsDir . DIRECTORY_SEPARATOR . 'dcms_cpanel_full.sql.zip';

        // 1. Generate Core SQL (Schema + Users + Settings + Core Master Data)
        $this->info('📦 1. Menghasilkan dcms_cpanel_core.sql (Struktur tabel lengkap + Akun + Master Data)...');
        $coreHandle = fopen($coreSqlPath, 'w');
        $this->writeHeader($coreHandle);
        $this->writeSchema($coreHandle);

        $coreTables = [
            'migrations',
            'users',
            'settings',
            'kpi_criteria',
            'employees',
            'broadcast_templates',
            'cross_selling_imports',
            'turnover_imports',
            'whatsapp_chats',
            'whatsapp_messages',
        ];

        foreach ($coreTables as $table) {
            $this->exportTableData($table, $coreHandle);
        }

        $this->writeFooter($coreHandle);
        fclose($coreHandle);
        $this->info("   ✅ Tersimpan: {$coreSqlPath} (" . $this->formatSize(filesize($coreSqlPath)) . ')');

        // 2. Generate Full SQL (Includes cross_sellings, turnovers, repeat_order_predictions)
        $this->info('📦 2. Menghasilkan dcms_cpanel_full.sql (Termasuk 112k+ baris Transaksi SAP)...');
        $fullHandle = fopen($fullSqlPath, 'w');
        $this->writeHeader($fullHandle);
        $this->writeSchema($fullHandle);

        $allTables = [
            'migrations',
            'users',
            'settings',
            'kpi_criteria',
            'employees',
            'broadcast_templates',
            'cross_selling_imports',
            'turnover_imports',
            'whatsapp_chats',
            'whatsapp_messages',
            'turnovers',
            'cross_sellings',
            'repeat_order_predictions',
            'lead_customers',
            'broadcast_batches',
            'broadcast_reports',
            'sales_assessments',
        ];

        foreach ($allTables as $table) {
            $this->exportTableData($table, $fullHandle);
        }

        $this->writeFooter($fullHandle);
        fclose($fullHandle);
        $this->info("   ✅ Tersimpan: {$fullSqlPath} (" . $this->formatSize(filesize($fullSqlPath)) . ')');

        // 3. Compress to ZIP for easy phpMyAdmin upload
        $this->info('🗜️ 3. Mengompresi dcms_cpanel_full.sql.zip untuk upload lancar di cPanel phpMyAdmin...');
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($fullSqlPath, 'dcms_cpanel_full.sql');
            $zip->close();
            $this->info("   ✅ Tersimpan ZIP: {$zipPath} (" . $this->formatSize(filesize($zipPath)) . ')');
        } else {
            $this->warn('   ⚠️ Gagal membuat file ZIP, gunakan file .sql langsung.');
        }

        $this->newLine();
        $this->info('🎉 Ekspor Database Berhasil!');
        $this->line('File siap diimpor di phpMyAdmin cPanel:');
        $this->line("1. Core Data (Ringan, ~200KB): <info>{$coreSqlPath}</info>");
        $this->line("2. Full Data (.sql, ~40MB): <info>{$fullSqlPath}</info>");
        $this->line("3. Full Data Compressed (.zip, ~6MB): <info>{$zipPath}</info>");

        return Command::SUCCESS;
    }

    private function writeHeader($handle): void
    {
        $date = now()->toDateTimeString();
        $header = <<<SQL
-- ========================================================
-- Database Customer Management System (DCMS)
-- Database Export for cPanel / phpMyAdmin (MySQL & MariaDB)
-- Generated Date: {$date}
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


SQL;
        fwrite($handle, $header);
    }

    private function writeFooter($handle): void
    {
        $footer = <<<SQL

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- Ekspor Database Selesai
-- ========================================================

SQL;
        fwrite($handle, $footer);
    }

    private function writeSchema($handle): void
    {
        $schema = <<<SQL
-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `cross_selling_imports`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cross_selling_imports`;
CREATE TABLE `cross_selling_imports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batch_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `row_count` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `error_log` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cross_selling_imports_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `turnover_imports`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `turnover_imports`;
CREATE TABLE `turnover_imports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batch_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `row_count` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `error_log` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnover_imports_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `cross_sellings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cross_sellings`;
CREATE TABLE `cross_sellings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cross_selling_import_id` bigint(20) unsigned DEFAULT NULL,
  `cabang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info` text COLLATE utf8mb4_unicode_ci,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `waktu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_vendor_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dscription` text COLLATE utf8mb4_unicode_ci,
  `itms_grp_nam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whs_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `price_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `sc_hl_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slp_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promo` text COLLATE utf8mb4_unicode_ci,
  `user_input` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_customer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_modal` decimal(18,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `reseller` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beban_subsidi_leasing` decimal(18,2) NOT NULL DEFAULT 0.00,
  `transaksi_customer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_reff` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_po` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_row` int(11) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `period_label` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleaning_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `turnovers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `turnovers`;
CREATE TABLE `turnovers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `turnover_import_id` bigint(20) unsigned DEFAULT NULL,
  `cabang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `document_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slp_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slp_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `sales_cabang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dpp` decimal(18,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `period_label` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleaning_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `repeat_order_predictions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `repeat_order_predictions`;
CREATE TABLE `repeat_order_predictions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cross_selling_import_id` bigint(20) unsigned DEFAULT NULL,
  `customer_vendor_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_customer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cabang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dscription` text COLLATE utf8mb4_unicode_ci,
  `first_order_date` date DEFAULT NULL,
  `last_order_date` date DEFAULT NULL,
  `transaction_count` int(11) NOT NULL DEFAULT 0,
  `total_quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `total_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `average_gap_days` decimal(8,2) NOT NULL DEFAULT 0.00,
  `predicted_repeat_date` date DEFAULT NULL,
  `confidence` smallint(6) NOT NULL DEFAULT 0,
  `classification` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `period_label` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `lead_customers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lead_customers`;
CREATE TABLE `lead_customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `repeat_order_prediction_id` bigint(20) unsigned DEFAULT NULL,
  `customer_vendor_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_customer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classification` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dscription` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_customers_user_id_foreign` (`user_id`),
  KEY `lead_customers_repeat_order_prediction_id_foreign` (`repeat_order_prediction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `broadcast_templates`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `broadcast_templates`;
CREATE TABLE `broadcast_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `broadcast_media`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `broadcast_media`;
CREATE TABLE `broadcast_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `broadcast_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `broadcast_batches`;
CREATE TABLE `broadcast_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_recipients` int(11) NOT NULL DEFAULT 0,
  `total_sent` int(11) NOT NULL DEFAULT 0,
  `total_failed` int(11) NOT NULL DEFAULT 0,
  `delivery_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `broadcast_batches_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `broadcast_reports`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `broadcast_reports`;
CREATE TABLE `broadcast_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `repeat_order_prediction_id` bigint(20) unsigned DEFAULT NULL,
  `broadcast_batch_id` bigint(20) unsigned DEFAULT NULL,
  `customer_vendor_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'whatsapp',
  `message` text COLLATE utf8mb4_unicode_ci,
  `image_url` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_response` text COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `broadcast_reports_user_id_foreign` (`user_id`),
  KEY `broadcast_reports_repeat_order_prediction_id_foreign` (`repeat_order_prediction_id`),
  KEY `broadcast_reports_broadcast_batch_id_foreign` (`broadcast_batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `employees`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cabang` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `joined_date` date DEFAULT NULL,
  `avatar_color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0284c7',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_nik_unique` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `kpi_criteria`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `kpi_criteria`;
CREATE TABLE `kpi_criteria` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `weight` decimal(5,2) NOT NULL DEFAULT 0.00,
  `target_benchmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kpi_criteria_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `sales_assessments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sales_assessments`;
CREATE TABLE `sales_assessments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `period` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `final_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grade` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'C',
  `scores` json DEFAULT NULL,
  `evaluator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_assessments_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `whatsapp_chats`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `whatsapp_chats`;
CREATE TABLE `whatsapp_chats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_vendor_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cabang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `folder` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inbox',
  `assigned_user_id` bigint(20) unsigned DEFAULT NULL,
  `format_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barang_keep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_pengambilan` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `tags_json` text COLLATE utf8mb4_unicode_ci,
  `last_message` text COLLATE utf8mb4_unicode_ci,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `unread_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_chats_customer_vendor_code_index` (`customer_vendor_code`),
  KEY `whatsapp_chats_phone_number_index` (`phone_number`),
  KEY `whatsapp_chats_assigned_user_id_index` (`assigned_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `whatsapp_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `whatsapp_messages`;
CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `whatsapp_chat_id` bigint(20) unsigned NOT NULL,
  `sender_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `agent_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `media_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'delivered',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_messages_whatsapp_chat_id_foreign` (`whatsapp_chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SQL;
        fwrite($handle, $schema);
    }

    private function exportTableData(string $tableName, $handle): void
    {
        $count = DB::table($tableName)->count();
        if ($count === 0) {
            return;
        }

        $this->line("   -> Mengekspor tabel `{$tableName}` ({$count} baris)...");
        fwrite($handle, "\n-- --------------------------------------------------------\n");
        fwrite($handle, "-- Dumping data for table `{$tableName}` ({$count} rows)\n");
        fwrite($handle, "-- --------------------------------------------------------\n");

        $chunkSize = 1000;
        $batch = [];
        $columns = [];

        DB::table($tableName)->orderBy('id')->chunk($chunkSize, function ($rows) use ($handle, $tableName, &$columns) {
            if ($rows->isEmpty()) {
                return;
            }

            if (empty($columns)) {
                $first = (array) $rows->first();
                $columns = array_keys($first);
            }

            $colList = '`' . implode('`, `', $columns) . '`';
            $valuesList = [];

            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $rowValues = [];
                foreach ($columns as $col) {
                    $val = $rowArray[$col] ?? null;
                    if ($val === null) {
                        $rowValues[] = 'NULL';
                    } elseif (is_bool($val)) {
                        $rowValues[] = $val ? '1' : '0';
                    } elseif (is_numeric($val) && ! is_string($val)) {
                        $rowValues[] = $val;
                    } else {
                        // string / date / json
                        $escaped = str_replace(
                            ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"],
                            ["\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z"],
                            (string) $val
                        );
                        $rowValues[] = "'{$escaped}'";
                    }
                }
                $valuesList[] = '(' . implode(', ', $rowValues) . ')';
            }

            $sql = "INSERT INTO `{$tableName}` ({$colList}) VALUES\n" . implode(",\n", $valuesList) . ";\n";
            fwrite($handle, $sql);
        });
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

