-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 08:08 AM
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
-- Database: `cannabis_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_anomaly_reports`
--

CREATE TABLE `ai_anomaly_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_anomaly` tinyint(1) NOT NULL DEFAULT 0,
  `confidence` decimal(5,4) DEFAULT NULL COMMENT '0.0 to 1.0',
  `detected_issue` varchar(255) DEFAULT NULL,
  `issue_description` text DEFAULT NULL,
  `recommended_action` text DEFAULT NULL,
  `severity` varchar(255) DEFAULT NULL COMMENT 'low, medium, high, critical',
  `provider` varchar(255) NOT NULL DEFAULT 'openai' COMMENT 'openai, local',
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `reviewed` tinyint(1) NOT NULL DEFAULT 0,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_logs`
--

CREATE TABLE `ai_chat_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `query` text NOT NULL COMMENT 'User question',
  `response` longtext NOT NULL COMMENT 'AI response',
  `context_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'RAG chunks used' CHECK (json_valid(`context_used`)),
  `embeddings_ref` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Embedding IDs used' CHECK (json_valid(`embeddings_ref`)),
  `context_snapshot` text DEFAULT NULL COMMENT 'Simplified context summary',
  `provider` varchar(255) NOT NULL DEFAULT 'openai',
  `tokens_used` int(11) DEFAULT NULL,
  `response_time_seconds` decimal(8,3) DEFAULT NULL,
  `was_helpful` tinyint(1) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_classification_results`
--

CREATE TABLE `ai_classification_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `room_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `classifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Multi-class predictions with scores' CHECK (json_valid(`classifications`)),
  `top_label` varchar(255) DEFAULT NULL,
  `top_category` varchar(255) DEFAULT NULL,
  `confidence` decimal(5,4) DEFAULT NULL COMMENT '0.0 to 1.0',
  `growth_stage` varchar(255) DEFAULT NULL,
  `health_status` varchar(255) DEFAULT NULL,
  `leaf_issues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`leaf_issues`)),
  `strain_type_prediction` varchar(255) DEFAULT NULL,
  `provider` varchar(255) NOT NULL DEFAULT 'openai' COMMENT 'openai, local',
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_embeddings`
--

CREATE TABLE `ai_embeddings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_table` varchar(255) NOT NULL COMMENT 'batches, strains, batch_logs, etc.',
  `source_id` bigint(20) UNSIGNED NOT NULL,
  `content_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash for idempotency',
  `content` longtext NOT NULL COMMENT 'Original text content',
  `embedding_vector` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Vector embeddings as JSON array' CHECK (json_valid(`embedding_vector`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Tags, filters, batch_code, etc.' CHECK (json_valid(`metadata`)),
  `embedding_model` varchar(255) NOT NULL DEFAULT 'text-embedding-3-large',
  `vector_dimensions` int(11) NOT NULL DEFAULT 3072,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `changes`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'created', 'App\\Models\\Batch', 1, '{\"before\":null,\"after\":{\"organization_id\":\"1\",\"strain_id\":\"1\",\"room_id\":\"1\",\"parent_batch_id\":null,\"status\":\"clone\",\"initial_plant_count\":100,\"current_plant_count\":300,\"mortality_count\":0,\"planting_date\":\"2025-11-10T00:00:00.000000Z\",\"clone_date\":null,\"veg_start_date\":null,\"flower_start_date\":null,\"expected_harvest_date\":null,\"supervisor_id\":\"2\",\"notes\":null,\"is_active\":true,\"created_by\":1,\"batch_code\":\"B-2025-0001\",\"updated_at\":\"2025-11-10T11:25:03.000000Z\",\"created_at\":\"2025-11-10T11:25:03.000000Z\",\"id\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 06:25:03', '2025-11-10 06:25:03'),
(2, 1, 'updated', 'App\\Filament\\Resources\\BatchResource\\Pages\\Batch', 1, '{\"status\":{\"before\":\"clone\",\"after\":\"vegetative\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 06:26:00', '2025-11-10 06:26:00'),
(3, 1, 'created', 'App\\Models\\BatchLog', 1, '{\"before\":null,\"after\":{\"batch_id\":\"1\",\"room_id\":1,\"log_date\":\"2025-11-10T00:00:00.000000Z\",\"activities\":[{\"activity\":\"Watering\",\"details\":\"ggff\",\"time\":\"10 AM\"},{\"activity\":\"Pruning\",\"details\":null,\"time\":null}],\"temperature_avg\":null,\"temperature_min\":null,\"temperature_max\":null,\"humidity_avg\":null,\"humidity_min\":null,\"humidity_max\":null,\"co2_avg\":null,\"ph_avg\":null,\"ec_avg\":null,\"plant_count\":100,\"mortality_count\":10,\"notes\":null,\"logged_by\":1,\"updated_at\":\"2025-11-10T11:27:20.000000Z\",\"created_at\":\"2025-11-10T11:27:20.000000Z\",\"id\":1,\"batch\":{\"id\":1,\"organization_id\":1,\"batch_code\":\"B-2025-0001\",\"strain_id\":1,\"room_id\":1,\"parent_batch_id\":null,\"status\":\"vegetative\",\"initial_plant_count\":100,\"current_plant_count\":100,\"mortality_count\":10,\"planting_date\":\"2025-11-10T00:00:00.000000Z\",\"clone_date\":null,\"veg_start_date\":\"2025-11-10T00:00:00.000000Z\",\"flower_start_date\":null,\"harvest_date\":null,\"expected_harvest_date\":null,\"progress_percentage\":\"3.33\",\"expected_yield\":null,\"actual_yield\":null,\"yield_percentage\":null,\"notes\":null,\"metadata\":null,\"created_by\":1,\"supervisor_id\":2,\"is_active\":true,\"created_at\":\"2025-11-10T11:25:03.000000Z\",\"updated_at\":\"2025-11-10T11:27:20.000000Z\",\"deleted_at\":null,\"strain\":{\"id\":1,\"organization_id\":1,\"name\":\"strain 1\",\"code\":\"st123\",\"type\":\"sativa\",\"genetics\":\"jhgjhgjgkgkjg\",\"description\":\"nkjgkghggjh\",\"thc_min\":null,\"thc_max\":null,\"cbd_min\":null,\"cbd_max\":null,\"expected_yield_per_plant\":null,\"expected_flowering_days\":null,\"expected_vegetative_days\":null,\"growth_notes\":null,\"nutrient_requirements\":null,\"is_active\":true,\"created_at\":\"2025-11-10T06:12:45.000000Z\",\"updated_at\":\"2025-11-10T06:12:45.000000Z\",\"deleted_at\":null}}}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 06:27:20', '2025-11-10 06:27:20'),
(4, 1, 'created', 'App\\Models\\Batch', 3, '{\"before\":null,\"after\":{\"organization_id\":\"1\",\"growth_cycle_id\":\"1\",\"strain_id\":\"1\",\"room_id\":\"1\",\"parent_batch_id\":null,\"status\":\"clone\",\"initial_plant_count\":10,\"current_plant_count\":10,\"mortality_count\":0,\"planting_date\":\"2025-11-13T00:00:00.000000Z\",\"clone_date\":\"2025-11-13T00:00:00.000000Z\",\"veg_start_date\":null,\"flower_start_date\":\"2025-11-13T00:00:00.000000Z\",\"expected_harvest_date\":\"2025-11-30T00:00:00.000000Z\",\"supervisor_id\":\"1\",\"notes\":null,\"is_active\":true,\"created_by\":1,\"batch_code\":\"B-2025-0001\",\"updated_at\":\"2025-11-13T07:25:52.000000Z\",\"created_at\":\"2025-11-13T07:25:52.000000Z\",\"id\":3}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 02:25:53', '2025-11-13 02:25:53'),
(5, 1, 'updated', 'App\\Models\\Batch', 3, '{\"status\":{\"before\":\"clone\",\"after\":\"vegetative\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 02:26:39', '2025-11-13 02:26:39'),
(6, 1, 'updated', 'App\\Models\\Batch', 3, '{\"status\":{\"before\":\"vegetative\",\"after\":\"flower\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 02:41:08', '2025-11-13 02:41:08'),
(7, 1, 'created', 'App\\Models\\BatchLog', 2, '{\"before\":null,\"after\":{\"batch_id\":\"3\",\"room_id\":1,\"log_date\":\"2025-11-13T00:00:00.000000Z\",\"activities\":[{\"activity\":\"watering\",\"details\":\"5L water\",\"time\":\"9 AM\"}],\"temperature_avg\":\"10.00\",\"temperature_min\":\"454.00\",\"temperature_max\":\"446.00\",\"humidity_avg\":\"4.00\",\"humidity_min\":\"15.00\",\"humidity_max\":\"4.00\",\"co2_avg\":\"654.00\",\"ph_avg\":\"4.00\",\"ec_avg\":\"10.00\",\"plant_count\":8,\"mortality_count\":0,\"notes\":null,\"logged_by\":1,\"updated_at\":\"2025-11-13T08:24:26.000000Z\",\"created_at\":\"2025-11-13T08:24:26.000000Z\",\"id\":2,\"batch\":{\"id\":3,\"organization_id\":1,\"growth_cycle_id\":1,\"batch_code\":\"B-2025-0001\",\"strain_id\":1,\"room_id\":1,\"parent_batch_id\":null,\"status\":\"flower\",\"initial_plant_count\":10,\"current_plant_count\":8,\"mortality_count\":0,\"planting_date\":\"2025-11-13T00:00:00.000000Z\",\"clone_date\":\"2025-11-13T00:00:00.000000Z\",\"veg_start_date\":\"2025-11-13T00:00:00.000000Z\",\"flower_start_date\":\"2025-11-13T00:00:00.000000Z\",\"harvest_date\":null,\"expected_harvest_date\":\"2025-11-30T00:00:00.000000Z\",\"progress_percentage\":\"2.00\",\"expected_yield\":null,\"actual_yield\":null,\"yield_percentage\":null,\"notes\":null,\"metadata\":null,\"created_by\":1,\"supervisor_id\":1,\"is_active\":true,\"created_at\":\"2025-11-13T07:25:52.000000Z\",\"updated_at\":\"2025-11-13T08:24:26.000000Z\",\"deleted_at\":null,\"strain\":{\"id\":1,\"organization_id\":1,\"name\":\"strain 1\",\"code\":\"st123\",\"type\":\"sativa\",\"genetics\":\"jhgjhgjgkgkjg\",\"description\":\"nkjgkghggjh\",\"thc_min\":null,\"thc_max\":null,\"cbd_min\":null,\"cbd_max\":null,\"expected_yield_per_plant\":null,\"expected_flowering_days\":50,\"expected_vegetative_days\":null,\"growth_notes\":null,\"nutrient_requirements\":null,\"is_active\":true,\"created_at\":\"2025-11-10T06:12:45.000000Z\",\"updated_at\":\"2025-11-12T05:08:25.000000Z\",\"deleted_at\":null}}}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 03:24:26', '2025-11-13 03:24:26'),
(8, 1, 'updated', 'App\\Models\\Batch', 3, '{\"status\":{\"before\":\"flower\",\"after\":\"harvest\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:12:10', '2025-11-19 01:12:10'),
(9, 1, 'created', 'App\\Models\\BatchLog', 3, '{\"before\":null,\"after\":{\"batch_id\":\"3\",\"room_id\":1,\"log_date\":\"2025-11-19T00:00:00.000000Z\",\"activities\":[{\"activity\":\"Ntient \",\"details\":\"5L water\",\"time\":null}],\"temperature_avg\":null,\"temperature_min\":null,\"temperature_max\":null,\"humidity_avg\":null,\"humidity_min\":null,\"humidity_max\":null,\"co2_avg\":null,\"ph_avg\":null,\"ec_avg\":null,\"plant_count\":null,\"mortality_count\":0,\"notes\":null,\"logged_by\":1,\"updated_at\":\"2025-11-19T06:16:29.000000Z\",\"created_at\":\"2025-11-19T06:16:29.000000Z\",\"id\":3,\"batch\":{\"id\":3,\"organization_id\":1,\"growth_cycle_id\":1,\"batch_code\":\"B-2025-0001\",\"strain_id\":1,\"room_id\":1,\"parent_batch_id\":null,\"status\":\"harvest\",\"initial_plant_count\":10,\"current_plant_count\":8,\"mortality_count\":0,\"planting_date\":\"2025-11-13T00:00:00.000000Z\",\"clone_date\":\"2025-11-13T00:00:00.000000Z\",\"veg_start_date\":\"2025-11-13T00:00:00.000000Z\",\"flower_start_date\":\"2025-11-13T00:00:00.000000Z\",\"harvest_date\":\"2025-11-19T00:00:00.000000Z\",\"expected_harvest_date\":\"2025-11-30T00:00:00.000000Z\",\"progress_percentage\":\"2.00\",\"expected_yield\":null,\"actual_yield\":null,\"yield_percentage\":null,\"notes\":null,\"metadata\":null,\"created_by\":1,\"supervisor_id\":1,\"is_active\":true,\"created_at\":\"2025-11-13T07:25:52.000000Z\",\"updated_at\":\"2025-11-19T06:12:10.000000Z\",\"deleted_at\":null}}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:16:29', '2025-11-19 01:16:29'),
(10, 1, 'updated', 'App\\Models\\Batch', 3, '{\"status\":{\"before\":\"harvest\",\"after\":\"packaging\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:44:27', '2025-11-19 01:44:27'),
(11, 1, 'created', 'App\\Models\\BatchLog', 4, '{\"before\":null,\"after\":{\"batch_id\":3,\"room_id\":\"1\",\"log_date\":\"2025-11-18T00:00:00.000000Z\",\"stage\":\"packaging\",\"activities\":[{\"activity\":\"Purning\",\"details\":\"NPK\",\"time\":\"10 AM\"}],\"temperature_avg\":\"23.00\",\"temperature_min\":\"23.00\",\"temperature_max\":\"23.00\",\"humidity_avg\":\"25.00\",\"humidity_min\":\"50.00\",\"humidity_max\":\"50.00\",\"co2_avg\":\"10.00\",\"ph_avg\":\"6.00\",\"ec_avg\":null,\"plant_count\":9,\"mortality_count\":0,\"notes\":\"happy\",\"logged_by\":1,\"updated_at\":\"2025-11-19T07:20:09.000000Z\",\"created_at\":\"2025-11-19T07:20:09.000000Z\",\"id\":4,\"batch\":{\"id\":3,\"organization_id\":1,\"growth_cycle_id\":1,\"batch_code\":\"B-2025-0001\",\"strain_id\":1,\"room_id\":1,\"parent_batch_id\":null,\"status\":\"packaging\",\"initial_plant_count\":10,\"current_plant_count\":9,\"mortality_count\":0,\"planting_date\":\"2025-11-13T00:00:00.000000Z\",\"clone_date\":\"2025-11-13T00:00:00.000000Z\",\"veg_start_date\":\"2025-11-13T00:00:00.000000Z\",\"flower_start_date\":\"2025-11-13T00:00:00.000000Z\",\"harvest_date\":\"2025-11-19T00:00:00.000000Z\",\"expected_harvest_date\":\"2025-11-30T00:00:00.000000Z\",\"progress_percentage\":\"2.00\",\"expected_yield\":null,\"actual_yield\":null,\"yield_percentage\":null,\"notes\":null,\"metadata\":null,\"created_by\":1,\"supervisor_id\":1,\"is_active\":true,\"created_at\":\"2025-11-13T07:25:52.000000Z\",\"updated_at\":\"2025-11-19T07:20:09.000000Z\",\"deleted_at\":null}}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:20:09', '2025-11-19 02:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED NOT NULL,
  `growth_cycle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_code` varchar(255) NOT NULL,
  `strain_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `parent_batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('clone','propagation','vegetative','flower','harvest','packaging','completed','cancelled') NOT NULL DEFAULT 'clone',
  `initial_plant_count` int(11) NOT NULL DEFAULT 0,
  `current_plant_count` int(11) NOT NULL DEFAULT 0,
  `mortality_count` int(11) NOT NULL DEFAULT 0,
  `planting_date` date NOT NULL,
  `clone_date` date DEFAULT NULL,
  `veg_start_date` date DEFAULT NULL,
  `flower_start_date` date DEFAULT NULL,
  `harvest_date` date DEFAULT NULL,
  `expected_harvest_date` date DEFAULT NULL,
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentage complete',
  `expected_yield` decimal(10,2) DEFAULT NULL COMMENT 'Expected total yield in grams',
  `actual_yield` decimal(10,2) DEFAULT NULL COMMENT 'Actual yield in grams',
  `yield_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Actual vs expected yield %',
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional structured data' CHECK (json_valid(`metadata`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `supervisor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `organization_id`, `growth_cycle_id`, `batch_code`, `strain_id`, `room_id`, `parent_batch_id`, `status`, `initial_plant_count`, `current_plant_count`, `mortality_count`, `planting_date`, `clone_date`, `veg_start_date`, `flower_start_date`, `harvest_date`, `expected_harvest_date`, `progress_percentage`, `expected_yield`, `actual_yield`, `yield_percentage`, `notes`, `metadata`, `created_by`, `supervisor_id`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 1, 1, 'B-2025-0001', 1, 1, NULL, 'packaging', 10, 8, 1, '2025-11-13', '2025-11-13', '2025-11-13', '2025-11-13', '2025-11-19', '2025-11-30', 2.00, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, '2025-11-13 02:25:52', '2025-11-24 01:07:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `batch_logs`
--

CREATE TABLE `batch_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `room_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tunnel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `log_date` date NOT NULL,
  `activities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Watering, pruning, nutrients, etc.' CHECK (json_valid(`activities`)),
  `notes` text DEFAULT NULL,
  `temperature_avg` decimal(5,2) DEFAULT NULL,
  `temperature_min` decimal(5,2) DEFAULT NULL,
  `temperature_max` decimal(5,2) DEFAULT NULL,
  `humidity_avg` decimal(5,2) DEFAULT NULL,
  `humidity_min` decimal(5,2) DEFAULT NULL,
  `humidity_max` decimal(5,2) DEFAULT NULL,
  `co2_avg` decimal(5,2) DEFAULT NULL,
  `ph_avg` decimal(3,2) DEFAULT NULL,
  `ec_avg` decimal(5,2) DEFAULT NULL,
  `plant_count` int(11) DEFAULT NULL,
  `mortality_count` int(11) NOT NULL DEFAULT 0,
  `logged_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_logs`
--

INSERT INTO `batch_logs` (`id`, `batch_id`, `stage`, `room_id`, `tunnel_id`, `log_date`, `activities`, `notes`, `temperature_avg`, `temperature_min`, `temperature_max`, `humidity_avg`, `humidity_min`, `humidity_max`, `co2_avg`, `ph_avg`, `ec_avg`, `plant_count`, `mortality_count`, `logged_by`, `created_at`, `updated_at`) VALUES
(2, 3, 'harvest', 1, NULL, '2025-11-13', '[{\"activity\":\"watering\",\"details\":\"5L water\",\"time\":\"9 AM\"}]', NULL, 10.00, 454.00, 446.00, 4.00, 15.00, 4.00, 654.00, 4.00, 10.00, 8, 0, 1, '2025-11-13 03:24:26', '2025-11-13 03:24:26'),
(3, 3, 'harvest', 1, NULL, '2025-11-19', '[{\"activity\":\"Ntient \",\"details\":\"5L water\",\"time\":null}]', NULL, 15.00, 10.00, 15.00, 10.00, 20.00, 10.00, 25.00, 6.40, NULL, 8, 1, 1, '2025-11-19 01:16:29', '2025-11-24 01:07:06'),
(4, 3, 'packaging', 1, NULL, '2025-11-18', '[{\"activity\":\"Purning\",\"details\":\"NPK\",\"time\":\"10 AM\"}]', 'happy', 23.00, 23.00, 23.00, 25.00, 50.00, 50.00, 10.00, 6.00, NULL, 9, 0, 1, '2025-11-19 02:20:09', '2025-11-19 02:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `batch_stage_history`
--

CREATE TABLE `batch_stage_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `from_stage` enum('clone','propagation','vegetative','flower','harvest','packaging','completed') DEFAULT NULL,
  `to_stage` enum('clone','propagation','vegetative','flower','harvest','packaging','completed') NOT NULL,
  `transition_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_stage_history`
--

INSERT INTO `batch_stage_history` (`id`, `batch_id`, `from_stage`, `to_stage`, `transition_date`, `reason`, `notes`, `approved_by`, `approved_at`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 3, 'clone', 'vegetative', '2025-11-13', 'clone completed', NULL, 1, '2025-11-13 02:26:39', 1, '2025-11-13 02:26:39', '2025-11-13 02:26:39'),
(3, 3, 'vegetative', 'flower', '2025-11-13', '', NULL, 1, '2025-11-13 02:41:08', 1, '2025-11-13 02:41:08', '2025-11-13 02:41:08'),
(4, 3, 'flower', 'harvest', '2025-11-19', 'flowswer completed', NULL, 1, '2025-11-19 01:12:10', 1, '2025-11-19 01:12:10', '2025-11-19 01:12:10'),
(5, 3, 'harvest', 'packaging', '2025-11-19', 'Harvest completed', NULL, 1, '2025-11-19 01:41:55', 1, '2025-11-19 01:41:55', '2025-11-19 01:41:55'),
(6, 3, 'harvest', 'packaging', '2025-11-19', 'harvest completed', NULL, 1, '2025-11-19 01:44:27', 1, '2025-11-19 01:44:27', '2025-11-19 01:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `batch_transfers`
--

CREATE TABLE `batch_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `from_room_id` bigint(20) UNSIGNED NOT NULL,
  `to_room_id` bigint(20) UNSIGNED NOT NULL,
  `transfer_date` date NOT NULL,
  `transfer_time` time DEFAULT NULL,
  `plant_count` int(11) NOT NULL DEFAULT 0,
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_planned` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Planned vs unplanned transfer',
  `triggered_deviation` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If unplanned, did it trigger deviation',
  `transferred_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1764658479),
('laravel-cache-livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1764658479;', 1764658479),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:59:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"access admin\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:11:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;i:7;i:8;i:8;i:9;i:9;i:10;i:10;i:11;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:11:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;i:7;i:8;i:8;i:9;i:9;i:10;i:10;i:11;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"manage users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"manage roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:18:\"manage permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:20:\"manage organizations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:9:\"manage qa\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:18:\"manage cultivation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:4;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:20:\"manage manufacturing\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:6;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:16:\"manage inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:7;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"manage sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:18:\"manage procurement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:10;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:10:\"view users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:11;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:10:\"view roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:11;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:16:\"view permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:11;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:7:\"view qa\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:11;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:16:\"view cultivation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:7;i:5;i:11;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:18:\"view manufacturing\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;i:6;i:4;i:7;i:5;i:11;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:14:\"view inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:9:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;i:6;i:4;i:7;i:5;i:8;i:6;i:9;i:7;i:10;i:8;i:11;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:10:\"view sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:7;i:2;i:8;i:3;i:9;i:4;i:11;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"view procurement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:10;i:2;i:11;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:12:\"create users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:12:\"create roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:18:\"create permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:9:\"create qa\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:18:\"create cultivation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:4;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:20:\"create manufacturing\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:6;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:16:\"create inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:7;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:12:\"create sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:18:\"create procurement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:10;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:10:\"edit users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:10:\"edit roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:16:\"edit permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:7:\"edit qa\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:16:\"edit cultivation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:4;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:18:\"edit manufacturing\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:6;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:14:\"edit inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:7;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:10:\"edit sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:16:\"edit procurement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:10;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:12:\"delete users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:12:\"delete roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:18:\"delete permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:9:\"delete qa\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:18:\"delete cultivation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:20:\"delete manufacturing\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:16:\"delete inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:12:\"delete sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:18:\"delete procurement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:10:\"approve qa\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:19:\"approve cultivation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:21:\"approve manufacturing\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:17:\"approve inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:13:\"approve sales\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:19:\"approve procurement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:6:\"ai.use\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:17:\"ai.detect.anomaly\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"ai.classify.plant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:7:\"ai.chat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:9:\"ai.manage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:11:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"Administrator\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"QA Manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:20:\"Cultivation Operator\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:22:\"Cultivation Supervisor\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:24:\"Manufacturing Technician\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:21:\"Manufacturing Manager\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:20:\"Inventory Controller\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"Sales Executive\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"Sales Manager\";s:1:\"c\";s:3:\"web\";}i:9;a:3:{s:1:\"a\";i:10;s:1:\"b\";s:19:\"Procurement Officer\";s:1:\"c\";s:3:\"web\";}i:10;a:3:{s:1:\"a\";i:11;s:1:\"b\";s:6:\"Viewer\";s:1:\"c\";s:3:\"web\";}}}', 1764744820);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `environmental_readings`
--

CREATE TABLE `environmental_readings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `facility_id` bigint(20) UNSIGNED NOT NULL,
  `space_type` varchar(255) NOT NULL,
  `space_id` bigint(20) UNSIGNED NOT NULL,
  `temperature` decimal(6,2) DEFAULT NULL,
  `humidity` decimal(6,2) DEFAULT NULL,
  `co2` decimal(10,2) DEFAULT NULL,
  `ph` decimal(5,2) DEFAULT NULL,
  `ec` decimal(8,2) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `environmental_thresholds`
--

CREATE TABLE `environmental_thresholds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(255) NOT NULL,
  `temperature_min` decimal(6,2) DEFAULT NULL,
  `temperature_max` decimal(6,2) DEFAULT NULL,
  `humidity_min` decimal(6,2) DEFAULT NULL,
  `humidity_max` decimal(6,2) DEFAULT NULL,
  `co2_min` decimal(10,2) DEFAULT NULL,
  `co2_max` decimal(10,2) DEFAULT NULL,
  `ph_min` decimal(5,2) DEFAULT NULL,
  `ph_max` decimal(5,2) DEFAULT NULL,
  `ec_min` decimal(8,2) DEFAULT NULL,
  `ec_max` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `organization_id`, `name`, `code`, `address`, `city`, `state`, `country`, `postal_code`, `is_active`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'F1', 'f100', NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-10 06:23:22', '2025-11-10 06:23:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `growth_cycles`
--

CREATE TABLE `growth_cycles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `facility_id` bigint(20) UNSIGNED NOT NULL,
  `primary_strain_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'planning',
  `start_date` date NOT NULL,
  `expected_end_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `growth_cycles`
--

INSERT INTO `growth_cycles` (`id`, `organization_id`, `facility_id`, `primary_strain_id`, `name`, `status`, `start_date`, `expected_end_date`, `actual_end_date`, `notes`, `metadata`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'November', 'planning', '2025-11-12', '2025-11-29', '2025-11-28', NULL, NULL, 1, '2025-11-12 02:50:01', '2025-11-12 02:50:01');

-- --------------------------------------------------------

--
-- Table structure for table `growth_cycle_strain`
--

CREATE TABLE `growth_cycle_strain` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `growth_cycle_id` bigint(20) UNSIGNED NOT NULL,
  `strain_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `growth_cycle_strain`
--

INSERT INTO `growth_cycle_strain` (`id`, `growth_cycle_id`, `strain_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-11-12 02:50:01', '2025-11-12 02:50:01'),
(2, 1, 2, '2025-11-12 03:00:30', '2025-11-12 03:00:30');

-- --------------------------------------------------------

--
-- Table structure for table `harvests`
--

CREATE TABLE `harvests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED DEFAULT NULL,
  `harvest_date` date NOT NULL,
  `harvest_time` time DEFAULT NULL,
  `wet_weight` decimal(10,2) DEFAULT NULL COMMENT 'Total wet weight',
  `trim_weight` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Trim weight',
  `waste_weight` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Waste weight',
  `dry_weight` decimal(10,2) DEFAULT NULL COMMENT 'Dry weight after curing',
  `harvested_plant_count` int(11) NOT NULL DEFAULT 0,
  `expected_yield` decimal(10,2) DEFAULT NULL,
  `actual_yield` decimal(10,2) DEFAULT NULL,
  `yield_percentage` decimal(5,2) DEFAULT NULL,
  `quality_notes` text DEFAULT NULL,
  `harvest_notes` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `low_yield_deviation_raised` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If yield <85%, deviation raised',
  `lots_created` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Material lots created from harvest',
  `harvested_by` bigint(20) UNSIGNED NOT NULL,
  `supervisor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supervisor_approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_15_000001_create_organizations_table', 1),
(5, '2025_01_15_000002_add_organization_to_users_table', 1),
(6, '2025_01_15_000003_create_audit_logs_table', 1),
(7, '2025_11_07_044642_create_permission_tables', 1),
(8, '2025_01_15_100001_create_facilities_table', 2),
(9, '2025_01_15_100002_create_rooms_table', 2),
(10, '2025_01_15_100003_create_strains_table', 2),
(11, '2025_01_15_100004_create_batches_table', 2),
(12, '2025_01_15_100005_create_batch_logs_table', 2),
(13, '2025_01_15_100006_create_batch_stage_history_table', 2),
(14, '2025_01_15_100007_create_batch_transfers_table', 2),
(15, '2025_01_15_100008_create_harvests_table', 2),
(16, '2025_11_12_120000_create_growth_cycles_table', 3),
(17, '2025_11_12_120100_add_growth_cycle_id_to_batches_table', 3),
(18, '2025_11_19_121000_add_stage_to_batch_logs_table', 4),
(19, '2025_11_19_123000_expand_stage_enum_on_history', 5),
(20, '2025_11_19_124000_expand_status_enum_on_batches', 6),
(21, '2025_11_20_000000_create_tunnels_table', 7),
(22, '2025_11_20_000001_create_environmental_readings_table', 8),
(23, '2025_11_25_000001_add_tunnel_to_batch_logs_table', 9),
(24, '2025_11_27_000002_create_environmental_thresholds_table', 10),
(25, '2025_11_25_000001_create_ai_anomaly_reports_table', 11),
(26, '2025_11_25_000002_create_ai_classification_results_table', 11),
(27, '2025_11_25_000003_create_ai_chat_logs_table', 11),
(28, '2025_11_25_000004_create_ai_embeddings_table', 11);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(55, 'App\\Models\\User', 1),
(56, 'App\\Models\\User', 1),
(57, 'App\\Models\\User', 1),
(58, 'App\\Models\\User', 1),
(59, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `timezone` varchar(255) NOT NULL DEFAULT 'UTC',
  `country` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `code`, `timezone`, `country`, `is_active`, `settings`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Default Organization', 'DEFAULT', 'UTC', 'US', 1, NULL, '2025-11-09 13:39:30', '2025-11-09 13:39:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'access admin', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(2, 'view dashboard', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(3, 'manage users', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(4, 'manage roles', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(5, 'manage permissions', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(6, 'manage organizations', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(7, 'manage qa', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(8, 'manage cultivation', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(9, 'manage manufacturing', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(10, 'manage inventory', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(11, 'manage sales', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(12, 'manage procurement', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(13, 'view users', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(14, 'view roles', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(15, 'view permissions', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(16, 'view qa', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(17, 'view cultivation', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(18, 'view manufacturing', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(19, 'view inventory', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(20, 'view sales', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(21, 'view procurement', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(22, 'create users', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(23, 'create roles', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(24, 'create permissions', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(25, 'create qa', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(26, 'create cultivation', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(27, 'create manufacturing', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(28, 'create inventory', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(29, 'create sales', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(30, 'create procurement', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(31, 'edit users', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(32, 'edit roles', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(33, 'edit permissions', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(34, 'edit qa', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(35, 'edit cultivation', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(36, 'edit manufacturing', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(37, 'edit inventory', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(38, 'edit sales', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(39, 'edit procurement', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(40, 'delete users', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(41, 'delete roles', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(42, 'delete permissions', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(43, 'delete qa', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(44, 'delete cultivation', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(45, 'delete manufacturing', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(46, 'delete inventory', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(47, 'delete sales', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(48, 'delete procurement', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(49, 'approve qa', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(50, 'approve cultivation', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(51, 'approve manufacturing', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(52, 'approve inventory', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(53, 'approve sales', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(54, 'approve procurement', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(55, 'ai.use', 'web', '2025-11-27 23:53:19', '2025-11-27 23:53:19'),
(56, 'ai.detect.anomaly', 'web', '2025-11-27 23:53:19', '2025-11-27 23:53:19'),
(57, 'ai.classify.plant', 'web', '2025-11-27 23:53:19', '2025-11-27 23:53:19'),
(58, 'ai.chat', 'web', '2025-11-27 23:53:19', '2025-11-27 23:53:19'),
(59, 'ai.manage', 'web', '2025-11-27 23:53:19', '2025-11-27 23:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'web', '2025-11-09 13:39:31', '2025-11-09 13:39:31'),
(2, 'QA Manager', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(3, 'Cultivation Operator', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(4, 'Cultivation Supervisor', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(5, 'Manufacturing Technician', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(6, 'Manufacturing Manager', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(7, 'Inventory Controller', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(8, 'Sales Executive', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(9, 'Sales Manager', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(10, 'Procurement Officer', 'web', '2025-11-09 13:39:32', '2025-11-09 13:39:32'),
(11, 'Viewer', 'web', '2025-11-09 13:39:33', '2025-11-09 13:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(7, 2),
(8, 1),
(8, 3),
(8, 4),
(9, 1),
(9, 5),
(9, 6),
(10, 1),
(10, 7),
(11, 1),
(11, 8),
(11, 9),
(12, 1),
(12, 10),
(13, 1),
(13, 11),
(14, 1),
(14, 11),
(15, 1),
(15, 11),
(16, 1),
(16, 2),
(16, 3),
(16, 4),
(16, 5),
(16, 6),
(16, 11),
(17, 1),
(17, 2),
(17, 3),
(17, 4),
(17, 7),
(17, 11),
(18, 1),
(18, 2),
(18, 5),
(18, 6),
(18, 7),
(18, 11),
(19, 1),
(19, 2),
(19, 5),
(19, 6),
(19, 7),
(19, 8),
(19, 9),
(19, 10),
(19, 11),
(20, 1),
(20, 7),
(20, 8),
(20, 9),
(20, 11),
(21, 1),
(21, 10),
(21, 11),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(25, 2),
(26, 1),
(26, 3),
(26, 4),
(27, 1),
(27, 5),
(27, 6),
(28, 1),
(28, 7),
(29, 1),
(29, 8),
(29, 9),
(30, 1),
(30, 10),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(34, 2),
(35, 1),
(35, 3),
(35, 4),
(36, 1),
(36, 5),
(36, 6),
(37, 1),
(37, 7),
(38, 1),
(38, 8),
(38, 9),
(39, 1),
(39, 10),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(43, 2),
(44, 1),
(44, 4),
(45, 1),
(45, 6),
(46, 1),
(47, 1),
(47, 9),
(48, 1),
(49, 1),
(49, 2),
(50, 1),
(50, 4),
(51, 1),
(51, 6),
(52, 1),
(53, 1),
(53, 9),
(54, 1),
(55, 1),
(55, 2),
(55, 3),
(55, 4),
(55, 6),
(56, 1),
(56, 2),
(56, 3),
(56, 4),
(57, 1),
(57, 2),
(57, 3),
(57, 4),
(58, 1),
(58, 2),
(58, 3),
(58, 4),
(58, 6),
(59, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `facility_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `type` enum('nursery','veg','flower','cure','packaging','warehouse','quarantine') NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0 COMMENT 'Maximum plant/batch capacity',
  `temperature_min` decimal(5,2) DEFAULT NULL,
  `temperature_max` decimal(5,2) DEFAULT NULL,
  `humidity_min` decimal(5,2) DEFAULT NULL,
  `humidity_max` decimal(5,2) DEFAULT NULL,
  `co2_min` decimal(5,2) DEFAULT NULL,
  `co2_max` decimal(5,2) DEFAULT NULL,
  `ph_min` decimal(3,2) DEFAULT NULL,
  `ph_max` decimal(3,2) DEFAULT NULL,
  `ec_min` decimal(5,2) DEFAULT NULL,
  `ec_max` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `facility_id`, `name`, `code`, `type`, `capacity`, `temperature_min`, `temperature_max`, `humidity_min`, `humidity_max`, `co2_min`, `co2_max`, `ph_min`, `ph_max`, `ec_min`, `ec_max`, `is_active`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'R1', 'R100', 'veg', 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-10 06:23:53', '2025-11-10 06:23:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('uPeOn3JNRldoswvXyH8ADAVrdGPB4GsgKzuI0Mh2', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoidERDS1c2MWJYdFF0MWFHQkJvUGJKQTNwZ0VnT01OYm81VTNKU2VjbSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUyOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vZW52aXJvbm1lbnRhbC1tb25pdG9yaW5nIjtzOjU6InJvdXRlIjtzOjQ1OiJmaWxhbWVudC5hZG1pbi5wYWdlcy5lbnZpcm9ubWVudGFsLW1vbml0b3JpbmciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkR1U0bmN4Yy9jZ0xPTXltZlVCaVVoLnhhbTRWQzJsNlF0NlQ1ekZJQkJkQ2RzdE01OEpOWlMiO30=', 1764659240);

-- --------------------------------------------------------

--
-- Table structure for table `strains`
--

CREATE TABLE `strains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `type` enum('indica','sativa','hybrid') DEFAULT NULL,
  `genetics` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thc_min` decimal(5,2) DEFAULT NULL COMMENT 'Expected THC % minimum',
  `thc_max` decimal(5,2) DEFAULT NULL COMMENT 'Expected THC % maximum',
  `cbd_min` decimal(5,2) DEFAULT NULL COMMENT 'Expected CBD % minimum',
  `cbd_max` decimal(5,2) DEFAULT NULL COMMENT 'Expected CBD % maximum',
  `expected_yield_per_plant` decimal(8,2) DEFAULT NULL COMMENT 'Expected yield in grams per plant',
  `expected_flowering_days` int(11) DEFAULT NULL,
  `expected_vegetative_days` int(11) DEFAULT NULL,
  `growth_notes` text DEFAULT NULL,
  `nutrient_requirements` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `strains`
--

INSERT INTO `strains` (`id`, `organization_id`, `name`, `code`, `type`, `genetics`, `description`, `thc_min`, `thc_max`, `cbd_min`, `cbd_max`, `expected_yield_per_plant`, `expected_flowering_days`, `expected_vegetative_days`, `growth_notes`, `nutrient_requirements`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'strain 1', 'st123', 'sativa', 'jhgjhgjgkgkjg', 'nkjgkghggjh', NULL, NULL, NULL, NULL, NULL, 50, NULL, NULL, NULL, 1, '2025-11-10 01:12:45', '2025-11-12 00:08:25', NULL),
(2, 1, 'strain 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 100, NULL, NULL, NULL, 1, '2025-11-12 03:00:14', '2025-11-12 03:00:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tunnels`
--

CREATE TABLE `tunnels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `facility_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `type` enum('nursery','veg','flower','cure','packaging','warehouse','quarantine') NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0 COMMENT 'Maximum plant/batch capacity',
  `temperature_min` decimal(5,2) DEFAULT NULL,
  `temperature_max` decimal(5,2) DEFAULT NULL,
  `humidity_min` decimal(5,2) DEFAULT NULL,
  `humidity_max` decimal(5,2) DEFAULT NULL,
  `co2_min` decimal(5,2) DEFAULT NULL,
  `co2_max` decimal(5,2) DEFAULT NULL,
  `ph_min` decimal(3,2) DEFAULT NULL,
  `ph_max` decimal(3,2) DEFAULT NULL,
  `ec_min` decimal(5,2) DEFAULT NULL,
  `ec_max` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tunnels`
--

INSERT INTO `tunnels` (`id`, `facility_id`, `name`, `code`, `type`, `capacity`, `temperature_min`, `temperature_max`, `humidity_min`, `humidity_max`, `co2_min`, `co2_max`, `ph_min`, `ph_max`, `ec_min`, `ec_max`, `is_active`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'T1', '10', 'packaging', 10, 23.00, 10.00, 10.00, 10.00, 10.00, NULL, 6.00, NULL, NULL, NULL, 1, NULL, '2025-11-20 02:19:28', '2025-11-20 02:56:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `organization_id`, `name`, `email`, `phone`, `position`, `is_active`, `last_login_at`, `last_login_ip`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrator', 'admin@admin.com', NULL, NULL, 1, '2025-12-02 02:06:28', '127.0.0.1', NULL, '$2y$12$GU4ncxc/cgLOMymfUBiUh.xam4VC2l6Qt6T5zFIBBdCdstM58JNZS', NULL, '2025-11-09 13:39:33', '2025-12-02 02:06:28'),
(2, 1, 'Hafiz Azeem', 'hafizazeem55@gmail.com', '+923218800532', 'Cultivation Operator', 1, NULL, NULL, NULL, '$2y$12$pkSptbdKlaOtV/3nODYW/uXoZyHrY8JTI5JQG7pRNj.jcMk.R05uG', NULL, '2025-11-09 23:07:42', '2025-11-09 23:07:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_anomaly_reports`
--
ALTER TABLE `ai_anomaly_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_anomaly_reports_room_id_foreign` (`room_id`),
  ADD KEY `ai_anomaly_reports_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `ai_anomaly_reports_created_by_foreign` (`created_by`),
  ADD KEY `ai_anomaly_reports_batch_id_created_at_index` (`batch_id`,`created_at`),
  ADD KEY `ai_anomaly_reports_is_anomaly_severity_index` (`is_anomaly`,`severity`),
  ADD KEY `ai_anomaly_reports_detected_issue_index` (`detected_issue`),
  ADD KEY `ai_anomaly_reports_reviewed_index` (`reviewed`);

--
-- Indexes for table `ai_chat_logs`
--
ALTER TABLE `ai_chat_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_chat_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `ai_chat_logs_batch_id_created_at_index` (`batch_id`,`created_at`),
  ADD KEY `ai_chat_logs_organization_id_index` (`organization_id`);
ALTER TABLE `ai_chat_logs` ADD FULLTEXT KEY `ai_chat_logs_query_response_fulltext` (`query`,`response`);

--
-- Indexes for table `ai_classification_results`
--
ALTER TABLE `ai_classification_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_classification_results_room_id_foreign` (`room_id`),
  ADD KEY `ai_classification_results_created_by_foreign` (`created_by`),
  ADD KEY `ai_classification_results_batch_id_created_at_index` (`batch_id`,`created_at`),
  ADD KEY `ai_classification_results_growth_stage_index` (`growth_stage`),
  ADD KEY `ai_classification_results_health_status_index` (`health_status`),
  ADD KEY `ai_classification_results_top_label_index` (`top_label`);

--
-- Indexes for table `ai_embeddings`
--
ALTER TABLE `ai_embeddings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_embedding` (`source_table`,`source_id`,`content_hash`),
  ADD KEY `ai_embeddings_source_table_source_id_index` (`source_table`,`source_id`),
  ADD KEY `ai_embeddings_content_hash_index` (`content_hash`),
  ADD KEY `ai_embeddings_created_at_index` (`created_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `audit_logs_user_id_index` (`user_id`),
  ADD KEY `audit_logs_created_at_index` (`created_at`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `batches_batch_code_unique` (`batch_code`),
  ADD KEY `batches_created_by_foreign` (`created_by`),
  ADD KEY `batches_supervisor_id_foreign` (`supervisor_id`),
  ADD KEY `batches_organization_id_index` (`organization_id`),
  ADD KEY `batches_strain_id_index` (`strain_id`),
  ADD KEY `batches_room_id_index` (`room_id`),
  ADD KEY `batches_status_index` (`status`),
  ADD KEY `batches_batch_code_index` (`batch_code`),
  ADD KEY `batches_parent_batch_id_index` (`parent_batch_id`),
  ADD KEY `batches_growth_cycle_id_foreign` (`growth_cycle_id`);

--
-- Indexes for table `batch_logs`
--
ALTER TABLE `batch_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_batch_log_date` (`batch_id`,`log_date`),
  ADD KEY `batch_logs_room_id_foreign` (`room_id`),
  ADD KEY `batch_logs_logged_by_foreign` (`logged_by`),
  ADD KEY `batch_logs_batch_id_index` (`batch_id`),
  ADD KEY `batch_logs_log_date_index` (`log_date`),
  ADD KEY `batch_logs_batch_id_log_date_index` (`batch_id`,`log_date`),
  ADD KEY `batch_logs_stage_index` (`stage`),
  ADD KEY `batch_logs_tunnel_id_index` (`tunnel_id`);

--
-- Indexes for table `batch_stage_history`
--
ALTER TABLE `batch_stage_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `batch_stage_history_approved_by_foreign` (`approved_by`),
  ADD KEY `batch_stage_history_created_by_foreign` (`created_by`),
  ADD KEY `batch_stage_history_batch_id_index` (`batch_id`),
  ADD KEY `batch_stage_history_transition_date_index` (`transition_date`);

--
-- Indexes for table `batch_transfers`
--
ALTER TABLE `batch_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `batch_transfers_to_room_id_foreign` (`to_room_id`),
  ADD KEY `batch_transfers_transferred_by_foreign` (`transferred_by`),
  ADD KEY `batch_transfers_approved_by_foreign` (`approved_by`),
  ADD KEY `batch_transfers_batch_id_index` (`batch_id`),
  ADD KEY `batch_transfers_transfer_date_index` (`transfer_date`),
  ADD KEY `batch_transfers_from_room_id_to_room_id_index` (`from_room_id`,`to_room_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `environmental_readings`
--
ALTER TABLE `environmental_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `environmental_readings_space_type_space_id_index` (`space_type`,`space_id`),
  ADD KEY `environmental_readings_facility_id_index` (`facility_id`),
  ADD KEY `environmental_readings_recorded_at_index` (`recorded_at`);

--
-- Indexes for table `environmental_thresholds`
--
ALTER TABLE `environmental_thresholds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `environmental_thresholds_stage_unique` (`stage`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facilities_code_unique` (`code`),
  ADD KEY `facilities_organization_id_index` (`organization_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `growth_cycles`
--
ALTER TABLE `growth_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `growth_cycles_organization_id_foreign` (`organization_id`),
  ADD KEY `growth_cycles_facility_id_foreign` (`facility_id`),
  ADD KEY `growth_cycles_primary_strain_id_foreign` (`primary_strain_id`),
  ADD KEY `growth_cycles_created_by_foreign` (`created_by`);

--
-- Indexes for table `growth_cycle_strain`
--
ALTER TABLE `growth_cycle_strain`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `growth_cycle_strain_growth_cycle_id_strain_id_unique` (`growth_cycle_id`,`strain_id`),
  ADD KEY `growth_cycle_strain_strain_id_foreign` (`strain_id`);

--
-- Indexes for table `harvests`
--
ALTER TABLE `harvests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `harvests_room_id_foreign` (`room_id`),
  ADD KEY `harvests_harvested_by_foreign` (`harvested_by`),
  ADD KEY `harvests_supervisor_id_foreign` (`supervisor_id`),
  ADD KEY `harvests_batch_id_index` (`batch_id`),
  ADD KEY `harvests_harvest_date_index` (`harvest_date`),
  ADD KEY `harvests_status_index` (`status`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organizations_code_unique` (`code`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rooms_facility_id_index` (`facility_id`),
  ADD KEY `rooms_type_index` (`type`),
  ADD KEY `rooms_is_active_index` (`is_active`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `strains`
--
ALTER TABLE `strains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `strains_organization_id_index` (`organization_id`),
  ADD KEY `strains_is_active_index` (`is_active`);

--
-- Indexes for table `tunnels`
--
ALTER TABLE `tunnels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tunnels_facility_id_index` (`facility_id`),
  ADD KEY `tunnels_type_index` (`type`),
  ADD KEY `tunnels_is_active_index` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_organization_id_foreign` (`organization_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_anomaly_reports`
--
ALTER TABLE `ai_anomaly_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_chat_logs`
--
ALTER TABLE `ai_chat_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_classification_results`
--
ALTER TABLE `ai_classification_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_embeddings`
--
ALTER TABLE `ai_embeddings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `batch_logs`
--
ALTER TABLE `batch_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `batch_stage_history`
--
ALTER TABLE `batch_stage_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `batch_transfers`
--
ALTER TABLE `batch_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `environmental_readings`
--
ALTER TABLE `environmental_readings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `environmental_thresholds`
--
ALTER TABLE `environmental_thresholds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `growth_cycles`
--
ALTER TABLE `growth_cycles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `growth_cycle_strain`
--
ALTER TABLE `growth_cycle_strain`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `harvests`
--
ALTER TABLE `harvests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `strains`
--
ALTER TABLE `strains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tunnels`
--
ALTER TABLE `tunnels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_anomaly_reports`
--
ALTER TABLE `ai_anomaly_reports`
  ADD CONSTRAINT `ai_anomaly_reports_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_anomaly_reports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_anomaly_reports_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_anomaly_reports_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_chat_logs`
--
ALTER TABLE `ai_chat_logs`
  ADD CONSTRAINT `ai_chat_logs_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_chat_logs_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_chat_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_classification_results`
--
ALTER TABLE `ai_classification_results`
  ADD CONSTRAINT `ai_classification_results_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_classification_results_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ai_classification_results_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_growth_cycle_id_foreign` FOREIGN KEY (`growth_cycle_id`) REFERENCES `growth_cycles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batches_parent_batch_id_foreign` FOREIGN KEY (`parent_batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batches_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `batches_strain_id_foreign` FOREIGN KEY (`strain_id`) REFERENCES `strains` (`id`),
  ADD CONSTRAINT `batches_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batch_logs`
--
ALTER TABLE `batch_logs`
  ADD CONSTRAINT `batch_logs_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_logs_logged_by_foreign` FOREIGN KEY (`logged_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `batch_logs_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batch_logs_tunnel_id_foreign` FOREIGN KEY (`tunnel_id`) REFERENCES `tunnels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batch_stage_history`
--
ALTER TABLE `batch_stage_history`
  ADD CONSTRAINT `batch_stage_history_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batch_stage_history_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_stage_history_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `batch_transfers`
--
ALTER TABLE `batch_transfers`
  ADD CONSTRAINT `batch_transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `batch_transfers_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_transfers_from_room_id_foreign` FOREIGN KEY (`from_room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `batch_transfers_to_room_id_foreign` FOREIGN KEY (`to_room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `batch_transfers_transferred_by_foreign` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `environmental_readings`
--
ALTER TABLE `environmental_readings`
  ADD CONSTRAINT `environmental_readings_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `facilities_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `growth_cycles`
--
ALTER TABLE `growth_cycles`
  ADD CONSTRAINT `growth_cycles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `growth_cycles_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `growth_cycles_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `growth_cycles_primary_strain_id_foreign` FOREIGN KEY (`primary_strain_id`) REFERENCES `strains` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `growth_cycle_strain`
--
ALTER TABLE `growth_cycle_strain`
  ADD CONSTRAINT `growth_cycle_strain_growth_cycle_id_foreign` FOREIGN KEY (`growth_cycle_id`) REFERENCES `growth_cycles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `growth_cycle_strain_strain_id_foreign` FOREIGN KEY (`strain_id`) REFERENCES `strains` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `harvests`
--
ALTER TABLE `harvests`
  ADD CONSTRAINT `harvests_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `harvests_harvested_by_foreign` FOREIGN KEY (`harvested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `harvests_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `harvests_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `strains`
--
ALTER TABLE `strains`
  ADD CONSTRAINT `strains_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tunnels`
--
ALTER TABLE `tunnels`
  ADD CONSTRAINT `tunnels_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
