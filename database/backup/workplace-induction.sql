-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 25, 2026 at 11:30 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workplace-induction`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `user_id` int UNSIGNED NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`user_id`, `first_name`, `last_name`, `created_at`, `updated_at`) VALUES
(210, 'Mark Dionnie', 'Bulingit', '2026-09-25 16:41:47', '2026-09-25 16:41:47');

-- --------------------------------------------------------

--
-- Table structure for table `compliance_records`
--

CREATE TABLE `compliance_records` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `induction_id` int UNSIGNED NOT NULL,
  `exam_attempt_id` int UNSIGNED DEFAULT NULL,
  `verification_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('active','expired','superseded','revoked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `renewed_from_id` int UNSIGNED DEFAULT NULL,
  `legacy_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_reminder_sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `compliance_records`
--

INSERT INTO `compliance_records` (`id`, `user_id`, `induction_id`, `exam_attempt_id`, `verification_token`, `certificate_number`, `issue_date`, `expiry_date`, `status`, `renewed_from_id`, `legacy_id`, `expiry_reminder_sent_at`, `created_at`, `updated_at`) VALUES
(7, 24, 1, NULL, 'f79099986c17748e1a5a9e2f3665f7f5cb63e6c971993fb5a41856ec071c34c0', 'CERT-2025-000007', '2025-01-13', '2027-01-13', 'active', NULL, 'e0176989-c066-49ec-9c15-fa2cd9847a53', NULL, '2025-01-13 05:23:55', '2026-09-23 23:33:00'),
(8, 25, 1, NULL, '48908d117bc508fd885ff0b14ac43758e1c64cdcf923e360a340e31e6c603fab', 'CERT-2025-000008', '2025-01-13', '2027-01-13', 'active', NULL, '76c91cbf-0798-4713-9383-f020854bd986', NULL, '2025-01-13 06:27:52', '2026-09-23 23:33:00'),
(9, 26, 1, NULL, '6ef7e7997d6648f04850d02382f1aab9fed527bff20a43a265f52ca63aff5535', 'CERT-2025-000009', '2025-01-14', '2027-01-14', 'active', NULL, '56744adc-eb1d-4768-b11d-bb6da5853194', NULL, '2025-01-14 00:11:57', '2026-09-23 23:33:00'),
(10, 27, 1, NULL, 'e5cdf3dea611cb71c8244d60b531601fdae1ba6409836bc3b6941c3e6fe7df26', 'CERT-2025-000010', '2025-01-16', '2027-01-16', 'active', NULL, '2ab6fdb3-7f53-4335-938a-1d8eebe56253', NULL, '2025-01-16 07:01:48', '2026-09-23 23:33:00'),
(11, 29, 1, NULL, '1bbc29f957d45ce762798947cd61f70ae29f53e7d14f31efca0d86ba9ed3f85f', 'CERT-2025-000011', '2025-05-24', '2027-05-24', 'active', NULL, '94498907-1d12-4f0d-a033-ba5ce5e1d0c8', NULL, '2025-05-24 01:35:36', '2026-09-23 23:33:00'),
(12, 30, 1, NULL, 'aeffe4fede37c16d824eb0534c5e8bc2fb5e6238a8e21f02864958ee3a595648', 'CERT-2025-000012', '2025-01-15', '2027-01-15', 'active', NULL, '10b728e1-edc2-4cae-aa3c-92336d0793d1', NULL, '2025-01-15 10:25:24', '2026-09-23 23:33:00'),
(13, 32, 1, NULL, '1d3e5e432279b3c5b7d0c18fa98fb19d0350178530c6dccbf830d37ee99d53d4', 'CERT-2025-000013', '2025-01-15', '2027-01-15', 'active', NULL, '6967bc48-9fac-4d05-ab8b-85a2d21cd10b', NULL, '2025-01-15 07:57:40', '2026-09-23 23:33:00'),
(14, 34, 1, NULL, '4a02d29f0b26ea3cd982b1e865e48be56989bbb61e4a579ab1d7c79522bc361b', 'CERT-2025-000014', '2025-01-24', '2027-01-24', 'active', NULL, '8f46ebc3-95ea-4a41-9d1f-925af797f6f3', NULL, '2025-01-24 06:38:05', '2026-09-23 23:33:00'),
(15, 35, 1, NULL, '55cdc46bfe7f27142f89719db7aa555b355b5294f9411abef231cfc72f65517e', 'CERT-2025-000015', '2025-01-24', '2027-01-24', 'active', NULL, '448c2963-6d20-4232-b5b3-031a98deacde', NULL, '2025-01-24 02:40:01', '2026-09-23 23:33:00'),
(16, 36, 1, NULL, '43f70d4be9f3e01395d245f96cd279df9ab62ac8e68b6510a8f21d08d9ef53ba', 'CERT-2025-000016', '2025-01-20', '2027-01-20', 'active', NULL, 'e74e410e-0855-4276-b089-e3559c06d73a', NULL, '2025-01-20 09:05:53', '2026-09-23 23:33:01'),
(17, 39, 1, NULL, 'e57c5a91d419fc4ec5a274cde7f18e8f0f04a16f0657ec705f7eafaae3fa792d', 'CERT-2025-000017', '2025-01-22', '2027-01-22', 'active', NULL, '0dee0bf6-c345-40cd-acb1-2d09fcd481dc', NULL, '2025-01-22 22:02:10', '2026-09-23 23:33:01'),
(18, 40, 1, NULL, 'fe273344dc2ac8bf1c137aefad052ae8b1c31350a5e6d79281e9e8f80d029605', 'CERT-2025-000018', '2025-01-22', '2027-01-22', 'active', NULL, 'e6d34fcd-3d2b-4151-8c57-643b7cdca54e', NULL, '2025-01-22 23:46:55', '2026-09-23 23:33:01'),
(19, 41, 1, NULL, '5e4fbdff99db94275d5467f345868d8030d7bcc5cf0f7d65034f47c895c5674f', 'CERT-2025-000019', '2025-01-23', '2027-01-23', 'active', NULL, 'd0e73232-56da-4b2e-b0e5-7d14cfaf9a62', NULL, '2025-01-23 02:48:58', '2026-09-23 23:33:01'),
(20, 43, 1, NULL, 'fd08665fe1311f3e8ce6bf3d0ca34ecc512d3c85966808d4e97e437a40dbc466', 'CERT-2025-000020', '2025-01-23', '2027-01-23', 'active', NULL, '1bb7fe91-41e3-43a5-9da0-6d6c14647665', NULL, '2025-01-23 23:43:47', '2026-09-23 23:33:01'),
(21, 44, 1, NULL, '934ab765d7845ed21b499135fce4ade000b657ea7b62b411fef89495b1fa7afc', 'CERT-2025-000021', '2025-01-24', '2027-01-24', 'active', NULL, '5e6a350d-ef13-486e-9790-1fea8a4f2c67', NULL, '2025-01-24 05:01:58', '2026-09-23 23:33:01'),
(22, 45, 1, NULL, 'db61dd097b57fde7bbc445de87dfcc4f05d7a9dbef6728e43e269f0e8ac15346', 'CERT-2025-000022', '2025-01-26', '2027-01-26', 'active', NULL, 'f83ea6b3-5468-46c9-8d1a-03027a05d45d', NULL, '2025-01-26 06:38:59', '2026-09-23 23:33:01'),
(23, 46, 1, NULL, 'c3abeacc77e162b87b6f8aea1e563855344dedcd7f529cebfd39e6fd4fed777b', 'CERT-2025-000023', '2025-01-26', '2027-01-26', 'active', NULL, '9a76c2a0-b9bb-4301-8be8-cdbf204678ba', NULL, '2025-01-26 07:51:44', '2026-09-23 23:33:01'),
(24, 47, 1, NULL, '70b7e790de1ba999866e8e5f9f4581c17c0945446e9bfdc3c40a4149c99b1778', 'CERT-2025-000024', '2025-01-26', '2027-01-26', 'active', NULL, '6c89fe30-f20f-4ca5-b7fa-ae9dd6f46560', NULL, '2025-01-26 08:00:46', '2026-09-23 23:33:01'),
(25, 48, 1, NULL, '2dd4a0568caf987cebb1b9017bb579529cd6b80969a54fa3e41e3fc21f76adcf', 'CERT-2025-000025', '2025-01-26', '2027-01-26', 'active', NULL, 'ff6c6134-730e-49d3-8449-d00699d56495', NULL, '2025-01-26 08:20:57', '2026-09-23 23:33:01'),
(26, 49, 1, NULL, 'a744c0feaa52fefaff38ce5bb355f00dfa6f50d6ba06343dc97047cf403c61c6', 'CERT-2025-000026', '2025-01-26', '2027-01-26', 'active', NULL, 'bff5fbe9-523d-43db-9558-a9fcbc2f7d8f', NULL, '2025-01-26 10:16:07', '2026-09-23 23:33:01'),
(27, 50, 1, NULL, '076297f547a1876bd5e563434cbbb588e6efae72e7faa055975fac9143ac7295', 'CERT-2025-000027', '2025-01-27', '2027-01-27', 'active', NULL, '119346e3-023c-4fd2-8b13-3ba5a88b22a9', NULL, '2025-01-27 06:05:50', '2026-09-23 23:33:01'),
(28, 53, 1, NULL, '051cae932928ccd23d888b3ccd4a1a3aa2efb49207ad3b5515ce8cc67270f3b4', 'CERT-2025-000028', '2025-02-10', '2027-02-10', 'active', NULL, '65a561e1-1def-4db0-9520-f41f39feb251', NULL, '2025-02-10 23:59:00', '2026-09-23 23:33:01'),
(29, 54, 1, NULL, '9505f410fc968c2324c240ba39fcc4a9a62fa2adf4b8c668c84344a78cf8b828', 'CERT-2025-000029', '2025-01-27', '2027-01-27', 'active', NULL, '19f25018-627f-457f-8e23-69d82ae41c64', NULL, '2025-01-27 23:51:38', '2026-09-23 23:33:01'),
(30, 55, 1, NULL, 'bcd2ef1f66f91e49c1a2e4a3cdc85f799c268874ac2ec398d626e5d6d0dabef9', 'CERT-2025-000030', '2025-02-03', '2027-02-03', 'active', NULL, 'a5b6ae16-7031-4f3f-88f9-2285b91dc0da', NULL, '2025-02-03 06:16:33', '2026-09-23 23:33:02'),
(31, 56, 1, NULL, 'd1aa018df47fc86ddc52e96654c6ddc3e8c229c8bc091957ec7acd0bce534e52', 'CERT-2025-000031', '2025-02-03', '2027-02-03', 'active', NULL, 'd9c7d5f2-1a23-4882-99ee-257c7eb15668', NULL, '2025-02-03 06:33:50', '2026-09-23 23:33:02'),
(32, 57, 1, NULL, '60553ce860036378a91eaf86f64341d97f72e9998bb02cf8becb5cf6da2cc311', 'CERT-2025-000032', '2025-02-03', '2027-02-03', 'active', NULL, '1cda9682-224b-4354-9d60-dae8b2afa95b', NULL, '2025-02-03 06:28:47', '2026-09-23 23:33:02'),
(33, 58, 1, NULL, '075996ac8c88a5a2f686ead1cb09c3c54f6251f3effd9c1508c39be4a6dfa5d1', 'CERT-2025-000033', '2025-02-04', '2027-02-04', 'active', NULL, 'dd4136c1-179c-4490-a14b-8fd2055d9151', NULL, '2025-02-04 03:20:39', '2026-09-23 23:33:02'),
(34, 59, 1, NULL, '0275e9d8154edc250e831888e3404c759ff8d06ca9fe98402182516cd69a77da', 'CERT-2025-000034', '2025-02-04', '2027-02-04', 'active', NULL, '5b150cb2-83bb-44ad-b5a4-89c002fe65e5', NULL, '2025-02-04 22:35:01', '2026-09-23 23:33:02'),
(35, 60, 1, NULL, '163cc2acc18b10c3fbc5a01f570ede1a7a1839ebc80ee1ee49cddff56eb1aeb2', 'CERT-2025-000035', '2025-02-05', '2027-02-05', 'active', NULL, '742f1d93-9dfa-4271-a569-264c4325bc64', NULL, '2025-02-05 00:18:55', '2026-09-23 23:33:02'),
(36, 61, 1, NULL, 'e1789d360b953ca16dcebccfb72d6b29d37e6c1ee4c7365a57cebcde7d4d0c81', 'CERT-2025-000036', '2025-02-05', '2027-02-05', 'active', NULL, 'b5fc33b5-04ea-40e6-964e-f7887d7bead1', NULL, '2025-02-05 06:47:14', '2026-09-23 23:33:02'),
(37, 63, 1, NULL, '7bc7510c784e9c687aa5044fa39f659389332643586cd5d547117950e3d87d16', 'CERT-2025-000037', '2025-02-15', '2027-02-15', 'active', NULL, '87f7b867-2cf2-42d7-9913-ec2ae43d4c3c', NULL, '2025-02-15 10:26:34', '2026-09-23 23:33:02'),
(38, 64, 1, NULL, '9ec1b0b80c4fc26f2393b6c51f5a9511818f1acf798a6f1bad81574ebf7ef34d', 'CERT-2025-000038', '2025-04-30', '2027-04-30', 'active', NULL, 'fbc98ca9-3daf-4de3-b2af-4181215f0463', NULL, '2025-04-30 02:44:25', '2026-09-23 23:33:02'),
(39, 65, 1, NULL, 'e41885075d561c650a40b864d03742cd67422f3bfe2c26ff98831dd4f9767712', 'CERT-2025-000039', '2025-02-15', '2027-02-15', 'active', NULL, 'ff234ee9-8cb9-404a-aee9-1488068cccab', NULL, '2025-02-15 00:21:45', '2026-09-23 23:33:02'),
(40, 66, 1, NULL, '7ebfb5106e2e5f9072a7d7a4533e8079925d78b67b7ade9a8312c91e4117702c', 'CERT-2025-000040', '2025-02-18', '2027-02-18', 'active', NULL, 'f7dbe57c-d9ac-4cb5-aea6-719fd3ef54ff', NULL, '2025-02-18 04:23:17', '2026-09-23 23:33:02'),
(41, 67, 1, NULL, 'a9a5bfbf71c8d5e23d63ceb75718965b18907d5700e48bc2e08d1fbe97f6e7f8', 'CERT-2025-000041', '2025-02-19', '2027-02-19', 'active', NULL, '037e8839-032f-4517-9a82-759869f24396', NULL, '2025-02-19 07:46:26', '2026-09-23 23:33:02'),
(42, 68, 1, NULL, 'd85e89c8630753cb154b906081079ad2b47a3571bc22d67dc81faaced9ebb44d', 'CERT-2025-000042', '2025-02-27', '2027-02-27', 'active', NULL, 'ea95cb0d-c6db-47a8-931e-521ce8a1f82e', NULL, '2025-02-27 08:21:00', '2026-09-23 23:33:02'),
(43, 69, 1, NULL, 'ce1390f4fd0c60207a3ad2201f9f704fe2e71294f68298010a120bbb08d3d6d4', 'CERT-2025-000043', '2025-03-01', '2027-03-01', 'active', NULL, 'ccbe3ef5-48d1-417b-9fb3-6fddc1f07d21', NULL, '2025-03-01 18:25:16', '2026-09-23 23:33:02'),
(44, 70, 1, NULL, '33f219443f828e2e7ab4fbbf85ad6082c88b07fd36d02df7ad123676c9e45619', 'CERT-2025-000044', '2025-03-07', '2027-03-07', 'active', NULL, 'e5748539-d959-4b42-8ec2-c463e11c3b91', NULL, '2025-03-07 07:39:58', '2026-09-23 23:33:02'),
(45, 71, 1, NULL, '4324a940a91faf7d0b5367696f82c48f3f4a06799f207a585313a4f8b49f5b62', 'CERT-2025-000045', '2025-03-07', '2027-03-07', 'active', NULL, 'a2ebe340-60a8-4e23-9efd-420cb4adfd53', NULL, '2025-03-07 09:16:59', '2026-09-23 23:33:02'),
(46, 72, 1, NULL, '8b2a17972a50dfd68d7777edbe37f646ffe28e6b76b78879c2bc5ba1d585ae6b', 'CERT-2025-000046', '2025-03-13', '2027-03-13', 'active', NULL, '08a8dbad-d79e-43d3-a074-0f2de3e526ce', NULL, '2025-03-13 04:45:42', '2026-09-23 23:33:02'),
(47, 73, 1, NULL, '2d9d3a922893a33c1943ebe7f12b6a4cb993ba4f47a7f5d5e5ee939e6a4b9bce', 'CERT-2025-000047', '2025-03-17', '2027-03-17', 'active', NULL, 'ba40cf0b-4e5c-4e0a-a172-c41fd7ee4a18', NULL, '2025-03-17 01:44:33', '2026-09-23 23:33:03'),
(48, 74, 1, NULL, '331306763c86734e10d363b94e9fb4bc766a35ec53e2e73b9acdd8c03988c274', 'CERT-2025-000048', '2025-03-25', '2027-03-25', 'active', NULL, 'd661608d-276c-4ed0-a98c-173552af6320', NULL, '2025-03-25 04:16:36', '2026-09-23 23:33:03'),
(49, 76, 1, NULL, '0228fa794cc115db91a2f28e651c3d1ed2d756e98a6cdc988e99eae83a860a18', 'CERT-2025-000049', '2025-03-27', '2027-03-27', 'active', NULL, '7659d694-bec5-4a43-8279-36f39ba76aa3', NULL, '2025-03-27 00:32:45', '2026-09-23 23:33:03'),
(50, 77, 1, NULL, '637c3d77153420107a2ccc69f31e4a2cef1732ab23b32c3d53d00010c1408ff1', 'CERT-2025-000050', '2025-03-28', '2027-03-28', 'active', NULL, '26fe1a34-aa4f-4922-bc97-96fa432935c3', NULL, '2025-03-28 07:45:04', '2026-09-23 23:33:03'),
(51, 78, 1, NULL, 'b96add5f79f3cb959f36a1c7935330c898d5ce1c1721499844478510f39f6932', 'CERT-2025-000051', '2025-03-28', '2027-03-28', 'active', NULL, '6294f82b-84ab-48fe-b035-639c0363bf6a', NULL, '2025-03-28 21:32:21', '2026-09-23 23:33:03'),
(52, 79, 1, NULL, 'e250a2db147aaf8605dbc6ebce6a8327315928af72528e9fdc2360414ce42cca', 'CERT-2025-000052', '2025-04-12', '2027-04-12', 'active', NULL, 'db4ed655-4133-40f3-9c45-cc0c8e953751', NULL, '2025-04-12 11:25:08', '2026-09-23 23:33:03'),
(53, 81, 1, NULL, '4bbf6912e8bffcc938b6aa3914a8d15f76edb16c0ae669ccf5630dd193c86aee', 'CERT-2025-000053', '2025-05-19', '2027-05-19', 'active', NULL, 'c7d66fa5-b782-4178-9493-696e61b24065', NULL, '2025-05-19 07:30:07', '2026-09-23 23:33:03'),
(54, 82, 1, NULL, '06ce59c6b7cb8697f7381933aa20e159c8d021e574578e155038962aa433ae67', 'CERT-2025-000054', '2025-05-21', '2027-05-21', 'active', NULL, 'f7deab72-d713-46b2-828e-b317bd6ecfce', NULL, '2025-05-21 02:24:02', '2026-09-23 23:33:03'),
(55, 83, 1, NULL, 'b0ffd5240187ca292c73a3df6f2eb85c1292d6904d3184d90569472d3e421f15', 'CERT-2025-000055', '2025-05-28', '2027-05-28', 'active', NULL, '196e9142-ea3c-461c-83f1-536b51cb9e83', NULL, '2025-05-28 02:22:56', '2026-09-23 23:33:03'),
(56, 84, 1, NULL, 'da1683abe5c6da1a74259d493755eb5599ca129dea338cef04e8d3b9d33b8a3b', 'CERT-2025-000056', '2025-06-05', '2027-06-05', 'active', NULL, 'f6acf2f8-42c6-4e3e-adc5-700964c74871', NULL, '2025-06-05 13:02:34', '2026-09-23 23:33:03'),
(57, 85, 1, NULL, '23d6b144615a543ea395264ca3b3bd289131b01dd5051de8e283ff16e7452b6a', 'CERT-2025-000057', '2025-06-17', '2027-06-17', 'active', NULL, '6e4bb50e-d9a9-4259-89c0-bb89ae68836e', NULL, '2025-06-17 04:54:19', '2026-09-23 23:33:03'),
(58, 86, 1, NULL, 'cd03b16038680201ba3d6cd3e176db8c2b9528bb1894bfb77ecdc5a38602b8a6', 'CERT-2025-000058', '2025-06-23', '2027-06-23', 'active', NULL, '06e11d53-492f-4a72-8f14-368f4ca0636a', NULL, '2025-06-23 09:55:07', '2026-09-23 23:33:03'),
(59, 87, 1, NULL, 'a57061408293712cac2fb6dc1157bfe825b7e6a25a325835199382c879c39ccc', 'CERT-2025-000059', '2025-07-23', '2027-07-23', 'active', NULL, '6e99d6d7-81ba-4472-8f75-6bacbdaa90b7', NULL, '2025-07-23 01:56:48', '2026-09-23 23:33:03'),
(60, 88, 1, NULL, 'e2ce36c7180c477ad62c2650b3e1ea1ba8a504da6e482fa1ef5bbfaa5ba7cec1', 'CERT-2025-000060', '2025-07-23', '2027-07-23', 'active', NULL, 'fb7e8aa8-dfa1-45d9-835b-80a3229e3456', NULL, '2025-07-23 02:20:40', '2026-09-23 23:33:03'),
(61, 89, 1, NULL, 'cba46567561ae6e945324407bd7cb041c2199e130342a7771292b4dc2dab3a50', 'CERT-2025-000061', '2025-07-30', '2027-07-30', 'active', NULL, 'c7c44c63-c8fc-4d5e-85a7-e57874f63918', NULL, '2025-07-30 09:15:32', '2026-09-23 23:33:03'),
(62, 90, 1, NULL, '3ba810a9f897495712b3a21bf195d66caac8ee08f39f33e93ebd21f402723354', 'CERT-2025-000062', '2025-08-16', '2027-08-16', 'active', NULL, '8721de4d-bd35-4c0c-bc97-0ba51c2cede4', NULL, '2025-08-16 06:00:57', '2026-09-23 23:33:03'),
(63, 91, 1, NULL, 'e1bd4bdd6e351d77641c31a343f4ce390846dc184ad9df0f40dcf1ea558983c2', 'CERT-2025-000063', '2025-08-27', '2027-08-27', 'active', NULL, '4d7c2737-0a5c-4c9c-b873-de065db4f7ee', NULL, '2025-08-27 21:44:29', '2026-09-23 23:33:04'),
(64, 92, 1, NULL, '2a88b8ff153cd429c3f2559c9fd6dca856d5212526f2548d5bc01a567647ee8e', 'CERT-2025-000064', '2025-09-03', '2027-09-03', 'active', NULL, '2d6bdde9-add7-4a89-923e-d4d1bb660279', NULL, '2025-09-03 03:02:42', '2026-09-23 23:33:04'),
(65, 93, 1, NULL, '01613bd025a8638445e9dec82aa289ad8f85d5c5b4ab517aa2c7091373b4dbea', 'CERT-2025-000065', '2025-09-03', '2027-09-03', 'active', NULL, '300fefcc-a35e-4693-83cb-26f8b130937a', NULL, '2025-09-03 03:10:27', '2026-09-23 23:33:04'),
(66, 94, 1, NULL, '54c18459510fa59773e8e079912a8ee74b2017d894f78ee2be818ae43c27f12b', 'CERT-2025-000066', '2025-09-07', '2027-09-07', 'active', NULL, '8fd7763a-27b8-47b7-9738-05b7ee8e4bb2', NULL, '2025-09-07 06:32:46', '2026-09-23 23:33:04'),
(67, 95, 1, NULL, 'ec10432d10ecf6e300aadaa007388647e3da160c990b0f7095a185707fc5d068', 'CERT-2025-000067', '2025-09-13', '2027-09-13', 'active', NULL, '7fb97f99-0c54-4b8f-8708-7755a4affc6f', NULL, '2025-09-13 01:46:15', '2026-09-23 23:33:04'),
(68, 97, 1, NULL, '021661a9988f1cb827f7133a1c8ee77b808b9df20309e8cf3cf6945acded7f48', 'CERT-2025-000068', '2025-10-13', '2027-10-13', 'active', NULL, '8498ca5c-72c9-4abe-b0e4-c9d821b36f71', NULL, '2025-10-13 06:32:17', '2026-09-23 23:33:04'),
(69, 98, 1, NULL, 'c5bebed2d95e334891b933662ef2037f0f06c45e966e1903467b280402a43027', 'CERT-2025-000069', '2025-10-23', '2027-10-23', 'active', NULL, '4ea96212-1724-4783-8753-d553f9deed2b', NULL, '2025-10-23 08:10:18', '2026-09-23 23:33:04'),
(70, 101, 1, NULL, '590c0a43122c211e7e791753e909fbcdc620239296db01524db746e10d7e0ec9', 'CERT-2025-000070', '2025-10-31', '2027-10-31', 'active', NULL, 'a668a3cc-6b38-458a-952f-766c9b53f7e3', NULL, '2025-10-31 22:16:50', '2026-09-23 23:33:04'),
(71, 102, 1, NULL, '3b39a988dfcf80bc5c97d34e165f2eb75070d9494b5b7e0e6293f9241bdc4eae', 'CERT-2025-000071', '2025-10-31', '2027-10-31', 'active', NULL, '2ce8d87e-7a4d-4c2c-96d9-3df7ac788113', NULL, '2025-10-31 23:27:14', '2026-09-23 23:33:04'),
(72, 103, 1, NULL, '79c682a35bf3b72c77e8828e75a9b91778e63725edf466243182f91b4f206b21', 'CERT-2025-000072', '2025-11-02', '2027-11-02', 'active', NULL, '015367ad-c603-4c94-9f75-b9dcdce5c49f', NULL, '2025-11-02 07:47:56', '2026-09-23 23:33:04'),
(73, 104, 1, NULL, 'ec569c0d350f162f63a2112de5f2c8bc0adc8879677314d2899b65f7b1fd6131', 'CERT-2025-000073', '2025-11-03', '2027-11-03', 'active', NULL, '6a3b3ff9-e0e5-498f-bd7a-ca634b7d517c', NULL, '2025-11-03 04:49:35', '2026-09-23 23:33:04'),
(74, 105, 1, NULL, '9c28b22f2c3ad3c1eb88a9441313edd90f83784c7837b1b4610ec4bd3cb184e6', 'CERT-2025-000074', '2025-11-06', '2027-11-06', 'active', NULL, '991b66de-00b4-4686-a491-64110f721889', NULL, '2025-11-06 12:07:40', '2026-09-23 23:33:04'),
(75, 106, 1, NULL, '29e21fe98b5e7a3708fbf2281eeb3771b4ffc19e9555a7f17f39aa43ee9d2aa9', 'CERT-2025-000075', '2025-11-07', '2027-11-07', 'active', NULL, 'c20ad062-bf6d-4925-bebb-13e63b51fb5b', NULL, '2025-11-07 01:53:40', '2026-09-23 23:33:04'),
(76, 107, 1, NULL, '52533f9e52b4abdd42e89ea144aa6006736fd60d55fb891770e6f50e1ed2e339', 'CERT-2025-000076', '2025-11-10', '2027-11-10', 'active', NULL, 'ff461565-60b6-451c-aa45-1888b557f9c9', NULL, '2025-11-10 05:47:20', '2026-09-23 23:33:04'),
(77, 108, 1, NULL, '1b2807f9608b6e33e81e6c4995b3fc2420b6f0264c08669f813e53ea849f133d', 'CERT-2025-000077', '2025-11-12', '2027-11-12', 'active', NULL, '034e9312-9e41-48ff-9652-e4ee31b11c33', NULL, '2025-11-12 08:36:23', '2026-09-23 23:33:04'),
(78, 109, 1, NULL, '83424229e1078cc8d4a89752e3b19e4e371d3af494c730f14489835826c9db21', 'CERT-2025-000078', '2025-11-15', '2027-11-15', 'active', NULL, 'fcbc874e-6a87-442f-abd1-b01329e2bfab', NULL, '2025-11-15 23:36:10', '2026-09-23 23:33:04'),
(79, 111, 1, NULL, '395f9f7d918aafe1cdcfe02d7047c60da8cb6e372ee294ff5885f6d599c98477', 'CERT-2025-000079', '2025-12-01', '2027-12-01', 'active', NULL, '53afa551-526c-4f3c-b958-fe5d82678461', NULL, '2025-12-01 01:38:51', '2026-09-23 23:33:05'),
(80, 112, 1, NULL, '7315a93179e8e75a6756ee8a7ea986e3400bb00f9731458f0b9a02bc84f34ed7', 'CERT-2025-000080', '2025-12-01', '2028-06-30', 'active', NULL, '13aa06ec-e61e-4a90-8a0f-006d2d92d948', NULL, '2025-12-01 06:22:40', '2026-09-23 23:33:05'),
(81, 113, 1, NULL, '6f936fd63367edacd55bd1b433ba0bb9f9d74be054262c2ca2a2c4ebdf9a2ef3', 'CERT-2025-000081', '2025-12-09', '2027-12-09', 'active', NULL, 'e45ca34c-753b-4eb8-9006-fff1c95c1cfd', NULL, '2025-12-09 09:30:13', '2026-09-23 23:33:05'),
(82, 114, 1, NULL, '4d82ee13a1042bfc1f5bff01486d64f9abc2b5709e8aa2e5490a8752298f662f', 'CERT-2025-000082', '2025-12-19', '2027-12-19', 'active', NULL, '438908c8-001b-4bb3-8320-1bb2912e5837', NULL, '2025-12-19 22:01:59', '2026-09-23 23:33:05'),
(83, 115, 1, NULL, '6c684e8a2bd4f585a74bbc1894bd5fc6ce05ca765f7817c3645561faca8fe490', 'CERT-2025-000083', '2025-12-23', '2027-12-23', 'active', NULL, 'fd972e59-219e-4aee-8654-381c3a3baca6', NULL, '2025-12-23 23:49:52', '2026-09-23 23:33:05'),
(84, 116, 1, NULL, '7a27eeda811aa6931270a4e923caf8b2812b9a4bbe478abd3540c7b44c760e15', 'CERT-2026-000084', '2026-01-13', '2028-01-13', 'active', NULL, '0ec92e72-632d-4a83-b33d-29913a75dae7', NULL, '2026-01-13 23:43:46', '2026-09-23 23:33:05'),
(85, 117, 1, NULL, 'c9fd3199862359bb850b1c24422e5f994bca5f020df20f868406e33283ab80af', 'CERT-2026-000085', '2026-01-14', '2028-01-14', 'active', NULL, 'e870879e-b9f2-4613-b1b6-8da4c24248d2', NULL, '2026-01-14 05:59:18', '2026-09-23 23:33:05'),
(86, 118, 1, NULL, '7fc13a5ee4d09f8c507357f446d70797fe6b831d8109a8e494bf76cc99ffbe22', 'CERT-2026-000086', '2026-01-14', '2028-01-14', 'active', NULL, '22be3fe9-448e-4300-b3f1-b857589eaa26', NULL, '2026-01-14 06:48:46', '2026-09-23 23:33:05'),
(87, 119, 1, NULL, 'a3a90d5eb152d4117720b77d7ab9962c66379327c6c00904384118134fb2fe1f', 'CERT-2026-000087', '2026-01-30', '2028-01-30', 'active', NULL, 'bbf3e941-a10c-4fad-9d87-fb1f6471213f', NULL, '2026-01-30 10:28:17', '2026-09-23 23:33:05'),
(88, 120, 1, NULL, '2621969a9a50c77cc36d47ff3a2173f85313b2207249f0947cdf1c3690bfa7a4', 'CERT-2026-000088', '2026-01-29', '2028-01-29', 'active', NULL, 'f0c22d4a-732d-4c8e-81fd-97142a4e910f', NULL, '2026-01-29 09:25:17', '2026-09-23 23:33:05'),
(89, 121, 1, NULL, '429019b0c3908f9bab4da27922a707a87d89bce8ead071984c6a6c97d8a72397', 'CERT-2026-000089', '2026-02-07', '2028-02-07', 'active', NULL, '8538bab0-f017-4b9f-bd65-350a8a171b9c', NULL, '2026-02-07 00:28:24', '2026-09-23 23:33:05'),
(90, 122, 1, NULL, '148819f4dffef0086998e3d269f959c8d74731fe62f13fbd84010e57ecb0c0bb', 'CERT-2026-000090', '2026-02-09', '2028-02-09', 'active', NULL, '52970614-56a0-4cb3-aa6a-e3764c619173', NULL, '2026-02-09 00:30:14', '2026-09-23 23:33:05'),
(91, 123, 1, NULL, 'bd006b5e523ac8cc37416941ff79c9bcd69574b226d1498bc009f2369cb672ae', 'CERT-2026-000091', '2026-02-09', '2028-02-09', 'active', NULL, '440b0d4b-9560-4664-b843-a35d42bbd1d2', NULL, '2026-02-09 09:41:04', '2026-09-23 23:33:05'),
(92, 124, 1, NULL, '5293505718d5fef6ae27008ace7a28a6294d391865aaffd195d7190f4be7ebb4', 'CERT-2026-000092', '2026-02-24', '2028-02-24', 'active', NULL, '79abadf1-982f-48d3-9155-4397b025101d', NULL, '2026-02-24 12:03:02', '2026-09-23 23:33:05'),
(93, 126, 1, NULL, '8d36aaaccb2f1da322b0b6e403236948c8e67049cde538f0862248c075517ea5', 'CERT-2026-000093', '2026-03-14', '2028-03-14', 'active', NULL, '9fb240a0-bafd-455e-8a8c-739177db53a8', NULL, '2026-03-14 01:01:24', '2026-09-23 23:33:05'),
(94, 127, 1, NULL, 'ce2e592acf2f2c4e17627c1777d2095d40234dfbf20b7122638c4f2ec3c2bf28', 'CERT-2026-000094', '2026-03-19', '2028-03-19', 'active', NULL, '7afd3841-c515-4d3c-9151-a66dd88070dc', NULL, '2026-03-19 00:28:51', '2026-09-23 23:33:05'),
(95, 128, 1, NULL, 'b41bfccd99cb5c7e51f345889fbd63b8e6154a29796585d342bb9f207f82bf76', 'CERT-2026-000095', '2026-03-20', '2028-03-20', 'active', NULL, '9fba3a4c-4c6b-470d-b3e5-8388eccab5d6', NULL, '2026-03-20 09:33:34', '2026-09-23 23:33:06'),
(96, 129, 1, NULL, '666ee2717fa7c21af9626a06efb247a6ae572292d39b48c651994721d20f5fd8', 'CERT-2026-000096', '2026-03-23', '2028-03-23', 'active', NULL, '085acec5-3c2a-4cf3-a64d-9911c9ed6934', NULL, '2026-03-23 07:29:54', '2026-09-23 23:33:06'),
(97, 130, 1, NULL, 'afdb8b865b510862a9b84056a8b8bc1a17f98cb81945b573474b88f9def6bbfd', 'CERT-2026-000097', '2026-03-25', '2028-03-25', 'active', NULL, 'ff825d95-70d7-49ce-9326-6353348548fe', NULL, '2026-03-25 09:57:18', '2026-09-23 23:33:06'),
(98, 131, 1, NULL, '6d9f441cc226b133d67d432b8d5ac315f3b02ccdbbacd86b76f7808140d048b6', 'CERT-2026-000098', '2026-03-25', '2028-03-25', 'active', NULL, 'a662122f-ef4a-420d-afcc-cdc820b04626', NULL, '2026-03-25 23:46:23', '2026-09-23 23:33:06'),
(99, 132, 1, NULL, '665a1b6a2283496c0104cd7ca64322851a028c43d1a4c9fb742238d1c1bd2a5d', 'CERT-2026-000099', '2026-03-26', '2028-03-26', 'active', NULL, '3a4d16ef-c7d8-4340-baf4-3e0e924d1be2', NULL, '2026-03-26 11:27:22', '2026-09-23 23:33:06'),
(100, 133, 1, NULL, '2467bbe45c190cb1a92b62e4042047127c701e9e5be912d85a6a8d19e19627a5', 'CERT-2026-000100', '2026-03-27', '2028-03-27', 'active', NULL, '2defdb85-0d5c-42e9-9230-ad190b884f57', NULL, '2026-03-27 09:35:50', '2026-09-23 23:33:06'),
(101, 134, 1, NULL, 'f62a33a5d779f3eea1d99f7c36e490c6352b398057bcc0129f0b4a381ea45a06', 'CERT-2026-000101', '2026-04-02', '2028-04-02', 'active', NULL, 'd0ba6c99-b609-40a7-afda-b6c1e394a27e', NULL, '2026-04-02 05:40:30', '2026-09-23 23:33:06'),
(102, 135, 1, NULL, 'c906efa9d999caff3b626eb1828245857c1080c590744213920d6bc696b1a526', 'CERT-2026-000102', '2026-04-08', '2028-04-08', 'active', NULL, '08d0475b-8559-4899-9724-f04dc5ad69a2', NULL, '2026-04-08 07:16:19', '2026-09-23 23:33:06'),
(103, 136, 1, NULL, '1b40f9519cb9ddcc3600a8e47e2579d8b21c01df9fe46d9e5e2277905cdb62d6', 'CERT-2026-000103', '2026-04-27', '2028-04-27', 'active', NULL, 'd68719f8-1ed2-4b27-ad75-4d09257856d7', NULL, '2026-04-27 11:20:35', '2026-09-23 23:33:06'),
(104, 137, 1, NULL, '37e427be595f6012ca64b79724568ac633fbd2304e0b7c38be935fb147aefc69', 'CERT-2026-000104', '2026-04-27', '2028-04-27', 'active', NULL, '7ae17867-7bcb-484a-9210-6b0482e2a81c', NULL, '2026-04-27 21:57:50', '2026-09-23 23:33:06'),
(105, 139, 1, NULL, '397edaeabd871d156ec4c6daeab194086b8e37a15c0e3fffc71ad99db9ddc5d6', 'CERT-2026-000105', '2026-04-30', '2028-04-30', 'active', NULL, '7899356a-1edf-4412-9de5-c7926f66b6f2', NULL, '2026-04-30 05:24:26', '2026-09-23 23:33:06'),
(106, 140, 1, NULL, 'a33e75eda38dbc9c78512330d042eb6a8c040aac67ca01e2e69c4c75f27c8b98', 'CERT-2026-000106', '2026-04-30', '2028-04-30', 'active', NULL, '8040454d-49d5-45ff-9b3f-ee8253380496', NULL, '2026-04-30 08:15:37', '2026-09-23 23:33:06'),
(107, 141, 1, NULL, '3abd007e796a55829c7aef8d95f950b6710b5111b5cbf1e5b87389b1d01358b5', 'CERT-2026-000107', '2026-05-01', '2028-05-01', 'active', NULL, '3c66437e-a33e-4d79-9d14-f77f74d6062f', NULL, '2026-05-01 02:44:40', '2026-09-23 23:33:06'),
(108, 142, 1, NULL, '68e614f33d1fb48316ec493cb127d9ea013772304cc6b9578fd31762760794f8', 'CERT-2026-000108', '2026-05-01', '2028-05-01', 'active', NULL, 'ac6bcc63-9281-4c3a-860b-1ef3fd328134', NULL, '2026-05-01 05:48:54', '2026-09-23 23:33:06'),
(109, 143, 1, NULL, 'aa0ca29a31bddc2edf947bebf533265d1eff599afb4237a46d8f5dfb7f141c73', 'CERT-2026-000109', '2026-05-05', '2028-05-05', 'active', NULL, '353d00f8-91ed-4b2f-b842-32cc8fbf6ab6', NULL, '2026-05-05 09:45:31', '2026-09-23 23:33:06'),
(110, 144, 1, NULL, '728ee6d20b9db9850a1f3748266d743ac1acebe85481bbe6fc915d9b8d97f3a3', 'CERT-2026-000110', '2026-05-07', '2028-05-07', 'active', NULL, '3e9fbc15-e392-46ec-a7ee-e65ea0fcccb5', NULL, '2026-05-07 20:03:39', '2026-09-23 23:33:06'),
(111, 146, 1, NULL, '87c6590358261e4f981dddea540bf24a4bacbbfcaad8ce8a1d6bf2d6b4236983', 'CERT-2026-000111', '2026-05-22', '2028-05-22', 'active', NULL, '3c01c31d-d3b0-4e53-9fb1-55700e50e89e', NULL, '2026-05-22 22:12:37', '2026-09-23 23:33:07'),
(112, 147, 1, NULL, 'fdd2fe8528cc46b54c19788129650c20b74e8d9d42ce2c3d03f7946f34ae1499', 'CERT-2026-000112', '2026-05-15', '2028-05-15', 'active', NULL, 'ec9b307b-d14c-4350-be5c-f45dc7dcef8e', NULL, '2026-05-15 23:39:52', '2026-09-23 23:33:07'),
(113, 148, 1, NULL, 'c3cda66d3b93d5b6896f470257126d3dd46e1b2763866af01178f1c376597ab4', 'CERT-2026-000113', '2026-05-17', '2028-05-17', 'active', NULL, '05768987-e966-494d-bbf7-9d88512792f8', NULL, '2026-05-17 03:13:33', '2026-09-23 23:33:07'),
(114, 149, 1, NULL, '6c3718157fc7ea8104557fef94d84dfe03ae4a412b190b6ff91675ece877fe2e', 'CERT-2026-000114', '2026-05-27', '2028-05-27', 'active', NULL, 'ccf5e017-a2a7-483e-938d-99ec93cfd082', NULL, '2026-05-27 10:20:00', '2026-09-23 23:33:07'),
(115, 150, 1, NULL, 'adf85116cc38a80e872a400403bec44417f140df5ad6cfbf179bba771db93092', 'CERT-2026-000115', '2026-05-28', '2028-05-28', 'active', NULL, '479a39d1-3694-4fd2-b982-4abe0ec7ebd5', NULL, '2026-05-28 00:56:45', '2026-09-23 23:33:07'),
(116, 151, 1, NULL, '07e23840c3258f0611b12ded2ef76e11bc48cf6ea5b0c7880cb3a229bd08e379', 'CERT-2026-000116', '2026-05-29', '2028-05-29', 'active', NULL, 'caed37d8-248c-443f-8d0b-db56aae5bb73', NULL, '2026-05-29 03:31:38', '2026-09-23 23:33:07'),
(117, 152, 1, NULL, '8ba5817260a7f9e766185b007691c3101a8927d69df534026896d1037c42a233', 'CERT-2026-000117', '2026-05-29', '2028-05-29', 'active', NULL, 'b3db7263-eda3-471c-a252-033fa6ab9151', NULL, '2026-05-29 04:33:27', '2026-09-23 23:33:07'),
(118, 153, 1, NULL, '30e41ba64a41b34c4fc829053ec2df91afd0f5a6987b13deed039c7318680c72', 'CERT-2026-000118', '2026-05-29', '2028-05-29', 'active', NULL, '82cb55f2-21ac-4abd-9f39-823f907c398c', NULL, '2026-05-29 09:08:03', '2026-09-23 23:33:07'),
(119, 154, 1, NULL, '01412adc64b94618b4704e458c334b1718580b5ebdf8eded3c6797b34ce24ec5', 'CERT-2026-000119', '2026-06-02', '2028-06-02', 'active', NULL, 'f407912c-734b-4681-89ea-4fae57f4714d', NULL, '2026-06-02 00:31:35', '2026-09-23 23:33:07'),
(120, 155, 1, NULL, 'ab9cd0b75d624d2364354fe3c8cbd8dc3bb286ea292e2f9d6386b9d4942045cc', 'CERT-2026-000120', '2026-06-02', '2028-06-02', 'active', NULL, 'a0a494a3-ffc4-4998-8381-7fa174be3706', NULL, '2026-06-02 13:13:48', '2026-09-23 23:33:07'),
(121, 156, 1, NULL, 'd7d60c66b401c4851a39ec406753e9412c08a4f3d0f24267d95c9a4cb0ffcd7c', 'CERT-2026-000121', '2026-06-04', '2028-06-04', 'active', NULL, '9d16dfc6-e7ed-4e90-9805-66b723c44073', NULL, '2026-06-04 05:02:24', '2026-09-23 23:33:07'),
(122, 157, 1, NULL, '933d8398bdea57324703cd9534a0c4fec78089a50f2bebff6293c89679705bb5', 'CERT-2026-000122', '2026-06-04', '2028-06-04', 'active', NULL, 'c52b6e4a-1827-4277-8e88-135455bf78bf', NULL, '2026-06-04 05:29:27', '2026-09-23 23:33:07'),
(123, 158, 1, NULL, 'b49510e9ab69e693ccb5b7d95ee84a02c3917dae316999793d94db8b42d00aaf', 'CERT-2026-000123', '2026-06-04', '2028-06-04', 'active', NULL, 'ff8e4df5-6c0a-4f64-9b8b-92c1db17e770', NULL, '2026-06-04 23:29:24', '2026-09-23 23:33:07'),
(124, 159, 1, NULL, '263ad5df994d17e59db9f78de05b7b159b3f628355caf41cb70793e3f1d0f34c', 'CERT-2026-000124', '2026-06-05', '2028-06-05', 'active', NULL, '85fa861f-c7d6-4481-b6f4-a25710b3d850', NULL, '2026-06-05 01:01:35', '2026-09-23 23:33:07'),
(125, 160, 1, NULL, 'b6667ee23ac5532c7791c778ad46d37dfeae0e7cc79dd2eeb4ae026fb3340fe4', 'CERT-2026-000125', '2026-06-08', '2028-06-08', 'active', NULL, '45fb68f1-1fb2-4f9c-be75-0cff15bd5291', NULL, '2026-06-08 04:16:33', '2026-09-23 23:33:07'),
(126, 161, 1, NULL, 'df017f66bedb2a7b6b65cb7dd3d0021d575f0489ed9bf8cf9940b3980c2da734', 'CERT-2026-000126', '2026-06-12', '2028-06-12', 'active', NULL, '90f24819-75d5-4ffd-bdb9-b765236a8a7b', NULL, '2026-06-12 06:15:32', '2026-09-23 23:33:07'),
(127, 162, 1, NULL, '2d67aa2ede2c08772272f9f6d71cc62d6415885a67d84a9d0979799feb24b571', 'CERT-2026-000127', '2026-06-15', '2028-06-15', 'active', NULL, '1c65b2dc-a152-4f22-a328-4135a16263a0', NULL, '2026-06-15 10:30:43', '2026-09-23 23:33:07'),
(128, 163, 1, NULL, '60127a9f76f7bd9a3c7acd6445c916b2a085e44c256d53f100805c917e656fa7', 'CERT-2026-000128', '2026-06-26', '2028-06-26', 'active', NULL, '6217b40f-4f29-4344-96f0-7dd501a52f44', NULL, '2026-06-26 00:27:04', '2026-09-23 23:33:07'),
(129, 164, 1, NULL, '9880deb8100054858f14ec4c0bf0018bcb347a4042f5c0bf6f856c2f92105453', 'CERT-2026-000129', '2026-06-30', '2028-06-30', 'active', NULL, '7b463fc5-6f6e-4b8f-b610-2889550fdf34', NULL, '2026-06-30 08:01:24', '2026-09-23 23:33:07'),
(130, 165, 1, NULL, '4e454c423b1dd47d04de03a2f586841c4116863c56958e0bef4d277b841d0d74', 'CERT-2026-000130', '2026-07-30', '2028-07-30', 'active', NULL, '814bf8b2-e5be-477e-bdfa-1e23e0a2018f', NULL, '2026-07-30 06:15:25', '2026-09-23 23:33:08'),
(131, 166, 1, NULL, '40f2003055a71ff3dba7afa5f6b377fc73f5558d276b4bb1515f53f14e3afd00', 'CERT-2026-000131', '2026-08-07', '2028-08-07', 'active', NULL, '3d8a900f-179e-469a-be33-dc36c5230295', NULL, '2026-08-07 00:37:53', '2026-09-23 23:33:08'),
(132, 167, 1, NULL, 'dba236b0063fee6ef2ac33bb22ddacf9da25f937cd257cb30f28b66ef2ca9d0b', 'CERT-2026-000132', '2026-08-12', '2028-08-12', 'active', NULL, 'eb55f1d3-52ba-4293-a58b-01ad883fb2ed', NULL, '2026-08-12 10:55:03', '2026-09-23 23:33:08'),
(133, 168, 1, NULL, 'cd8aa710ca28007417cf88d220e3b157ba4f7c3bba81e3808c1aa7d389db8200', 'CERT-2026-000133', '2026-09-02', '2028-09-02', 'active', NULL, '8b6589ce-d99d-4856-9dd0-cbdd7831097b', NULL, '2026-09-02 05:07:23', '2026-09-23 23:33:08'),
(134, 169, 1, NULL, '4d2e6ebd7b12789f0f70e5d0ba3ec517cb38cdf1e9f1675b1b0cd1df68688de5', 'CERT-2026-000134', '2026-09-12', '2028-09-12', 'active', NULL, '412789f5-a2b2-44ba-8d91-33e659c0719a', NULL, '2026-09-12 00:15:26', '2026-09-23 23:33:08'),
(139, 213, 1, 11, 'cfd850010f93076c01e6b1c1b8609eef85b00dc6daefe1d9145f702e1888d706', 'CERT-2026-000139', '2026-09-25', '2028-09-25', 'active', NULL, NULL, NULL, '2026-09-25 19:26:43', '2026-09-25 19:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `email_settings`
--

CREATE TABLE `email_settings` (
  `id` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `inductee_sender_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inductee_sender_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inductee_cc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inductee_bcc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_sender_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_sender_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_to` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_cc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_bcc` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_notification_frequency` enum('instant','daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'weekly',
  `notify_admin_on_registration` tinyint(1) NOT NULL DEFAULT '1',
  `notify_admin_on_completion` tinyint(1) NOT NULL DEFAULT '1',
  `notify_inductee_on_completion` tinyint(1) NOT NULL DEFAULT '1',
  `notify_inductee_on_expiry` tinyint(1) NOT NULL DEFAULT '1',
  `notify_admin_on_expired` tinyint(1) NOT NULL DEFAULT '1',
  `expiry_reminder_days` smallint UNSIGNED NOT NULL DEFAULT '30',
  `admin_report_last_sent_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_settings`
--

INSERT INTO `email_settings` (`id`, `inductee_sender_name`, `inductee_sender_email`, `inductee_cc`, `inductee_bcc`, `admin_sender_name`, `admin_sender_email`, `admin_to`, `admin_cc`, `admin_bcc`, `admin_notification_frequency`, `notify_admin_on_registration`, `notify_admin_on_completion`, `notify_inductee_on_completion`, `notify_inductee_on_expiry`, `notify_admin_on_expired`, `expiry_reminder_days`, `admin_report_last_sent_at`, `updated_at`) VALUES
(1, 'Stark Food Systems', 'xxx@starkfoodsystems.com', 'yyy@starkfoodsystems.com', NULL, 'Stark Food Systems', 'info@starkfoodsystems.com', 'dionnie_bulingit@yahoo.com', 'hitesh@starkfoodsystems.com', NULL, 'instant', 1, 1, 1, 1, 1, 14, '2026-09-24 18:11:30', '2026-09-25 19:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `pass_percentage` tinyint UNSIGNED NOT NULL,
  `exam_blocks` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `title`, `description`, `status`, `pass_percentage`, `exam_blocks`, `created_at`, `updated_at`) VALUES
(1, 'Stark Food Systems Induction Quiz', 'Competency quiz for the Stark Food Systems Induction Program. Imported from a legacy export that only retained 4 of the original 20 questions.', 'active', 80, '[{\"id\": \"q_046_7cde17\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Agriculture and logistics\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"FMCG, manufacturing, and food processing\", \"correct\": true}, {\"id\": \"opt_3\", \"text\": \"Retail management and marketing\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Environmental sustainability\", \"correct\": false}], \"question\": \"What is the main expertise of Stark Food Systems\' core management group?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_047_1644d9\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"5 km/h\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"8 km/h\", \"correct\": true}, {\"id\": \"opt_3\", \"text\": \"10 km/h\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"20 km/h\", \"correct\": false}], \"question\": \"What is the default speed limit in areas without posted speed limits?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_048_12fa42\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Anywhere inside the plant\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Near fire equipment\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"In restrooms\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Designated smoking areas\", \"correct\": true}], \"question\": \"Where is smoking allowed in the plant?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_049_340381\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"In areas with continuous noise levels exceeding 85 dBA\", \"correct\": true}, {\"id\": \"opt_2\", \"text\": \"In areas with continuous noise levels exceeding 65 dBA\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Only during machinery operation\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Never required\", \"correct\": false}], \"question\": \"When is hearing protection required?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_050_8ecea9\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Continue working until instructed\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Evacuate to the nearest Major Safe Assembly Point\", \"correct\": true}, {\"id\": \"opt_3\", \"text\": \"Call supervisor\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Gather tools and evacuate\", \"correct\": false}], \"question\": \"What should personnel do when the emergency alarm is sounded?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_051_42d93a\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Increase productivity\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Avoid unnecessary meetings\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Minimize, monitor, and control risks\", \"correct\": true}, {\"id\": \"opt_4\", \"text\": \"Improve employee attendance\", \"correct\": false}], \"question\": \"What is the primary purpose of risk management?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_052_ee4c72\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"One car length\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Two car lengths\", \"correct\": true}, {\"id\": \"opt_3\", \"text\": \"Three car lengths\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Four car lengths\", \"correct\": false}], \"question\": \"What is the minimum safe distance you should maintain between your car and the car in front?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_053_0d0547\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"A warning is issued\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"A fine is imposed\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Counseling is offered\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Immediate expulsion and contract suspension\", \"correct\": true}], \"question\": \"What happens if alcohol or illegal drugs are found on-site?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_054_6712f6\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Bend your knees and keep your back straight\", \"correct\": true}, {\"id\": \"opt_2\", \"text\": \"Bend your back and keep your legs straight\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Use only your arms\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Lift as fast as possible\", \"correct\": false}], \"question\": \"What is the correct way to lift a heavy object?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_055_9d1901\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Continue using it carefully\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Report it immediately\", \"correct\": true}, {\"id\": \"opt_3\", \"text\": \"Ignore it\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Fix it yourself without approval\", \"correct\": false}], \"question\": \"What should you do if you find damaged equipment?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_056_879195\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Ensure it is properly locked out and tagged out\", \"correct\": true}, {\"id\": \"opt_2\", \"text\": \"Turn it on to check functionality\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Remove any safety guards\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Adjust it while it is running\", \"correct\": false}], \"question\": \"What should you do before starting work on a machine?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_057_71f588\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Ignore the behavior\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Confront them immediately\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Report it to your supervisor\", \"correct\": true}, {\"id\": \"opt_4\", \"text\": \"Join them to avoid conflict\", \"correct\": false}], \"question\": \"What should you do if you witness a co-worker violating safety protocols?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_058_433624\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Class A\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Class B\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Class C\", \"correct\": true}, {\"id\": \"opt_4\", \"text\": \"Class D\", \"correct\": false}], \"question\": \"Which fire class is associated with electrical fires?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_059_d4809c\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"General information\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Emergency equipment location\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Caution\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Prohibition or danger\", \"correct\": true}], \"question\": \"What does a red safety sign usually indicate?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_060_84edad\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Ensure it\'s properly inspected for defects\", \"correct\": true}, {\"id\": \"opt_2\", \"text\": \"Ensure it\'s newly purchased\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Paint it to ensure visibility\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Confirm it\'s made of lightweight material\", \"correct\": false}], \"question\": \"What is required before using a ladder?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_061_c29614\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Wait for them to decide what to do\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Ignore it unless it\'s severe\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Notify a first aider or supervisor\", \"correct\": true}, {\"id\": \"opt_4\", \"text\": \"Provide your own untrained assistance\", \"correct\": false}], \"question\": \"What is the appropriate action if a co-worker sustains a minor injury?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_062_16778a\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Red circular sign\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Green rectangular sign\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Yellow triangular sign Leave it\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Blue circular sign\", \"correct\": true}], \"question\": \"Which type of sign is typically used to indicate a mandatory action?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_063_03ca2e\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Leave it for the next shift\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Organize and clear the space immediately\", \"correct\": true}, {\"id\": \"opt_3\", \"text\": \"Wait until the supervisor notices\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Ignore it unless it impacts productivity\", \"correct\": false}], \"question\": \"What should you do if the workshop becomes cluttered with tools and materials?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_064_186372\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Continue working as normal\", \"correct\": false}, {\"id\": \"opt_2\", \"text\": \"Stand in its path to direct it\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Move to a safe distance until it passes\", \"correct\": true}, {\"id\": \"opt_4\", \"text\": \"Ignore it unless it\'s carrying hazardous materials\", \"correct\": false}], \"question\": \"What is the correct action when a forklift is approaching your work area?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}, {\"id\": \"q_065_f4a53f\", \"type\": \"question\", \"options\": [{\"id\": \"opt_1\", \"text\": \"Replace it immediately or report it for replacement\", \"correct\": true}, {\"id\": \"opt_2\", \"text\": \"Continue working with the damaged PPE\", \"correct\": false}, {\"id\": \"opt_3\", \"text\": \"Repair it yourself before using it\", \"correct\": false}, {\"id\": \"opt_4\", \"text\": \"Ignore it unless it impacts performance\", \"correct\": false}], \"question\": \"What should you do if personal protective equipment (PPE) is damaged?\", \"explanation\": \"\", \"diagram_img_url\": \"\"}]', '2026-09-23 18:24:48', '2026-09-25 00:47:55');

-- --------------------------------------------------------

--
-- Table structure for table `exam_attempts`
--

CREATE TABLE `exam_attempts` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `induction_id` int UNSIGNED NOT NULL,
  `exam_id` int UNSIGNED NOT NULL,
  `score` smallint UNSIGNED NOT NULL,
  `total_score` smallint UNSIGNED NOT NULL,
  `result` enum('passed','failed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_attempts`
--

INSERT INTO `exam_attempts` (`id`, `user_id`, `induction_id`, `exam_id`, `score`, `total_score`, `result`, `created_at`) VALUES
(11, 213, 1, 1, 20, 20, 'passed', '2026-09-25 19:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `inductee_profiles`
--

CREATE TABLE `inductee_profiles` (
  `user_id` int UNSIGNED NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_position` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_type` enum('Full-time','Part-time','Casual','Contractor','Sub-contractor','Apprentice','Trainee','Shift-worker','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inductee_profiles`
--

INSERT INTO `inductee_profiles` (`user_id`, `first_name`, `last_name`, `contact_number`, `job_position`, `company`, `employment_type`, `emergency_contact_name`, `emergency_contact_phone`, `created_at`, `updated_at`) VALUES
(22, 'Hitesh', 'Parekh', NULL, NULL, 'Global Webforce', 'Other', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(23, 'Starkfood', 'Systems', NULL, NULL, 'Stark Food Systems', 'Other', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(24, 'Harold', 'Howard', NULL, NULL, 'Stark Food Systems', 'Apprentice', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(25, 'Angela', 'Sunga', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(26, 'Hitesh', 'Parekh', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(27, 'Angela', 'Sunga', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(28, 'Carl', 'GWF', NULL, NULL, 'Stark Food Systems', 'Other', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(29, 'Mian Waqas', 'Masood', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(30, 'Abhimanyu', '', NULL, NULL, 'Abhimanyu Logistics', 'Contractor', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(31, 'Nathan', 'Taing', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(32, 'Nathan', 'Taing', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(33, 'Muhammad Naeem', 'Tufail', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(34, 'Alexander', 'Soterales', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(35, 'Ajaykumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(36, 'Kelly', 'Revell', NULL, NULL, 'Stark Food Systems', 'Other', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(37, 'Jack', 'Preedy', NULL, NULL, 'Stark Food Systems', 'Apprentice', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(38, 'Muhammad wajid', 'Masood', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(39, 'Shyama', 'Wijayathunga', NULL, NULL, 'SW Assurance Services Pty Ltd', 'Other', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(40, 'Shayne', 'Revell', NULL, NULL, '', 'Contractor', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(41, 'Sharneet', 'Chand', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(42, 'Ronil', 'Raju', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(43, 'Afroz', 'Mohammed', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(44, 'Akshay', 'Kumar', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(45, 'Muhammad Arslan', 'Masood', NULL, NULL, 'Techno Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(46, 'Rodrick Rajneil', 'Ratnam', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(47, 'Mahendra nand', 'Nair', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(48, 'Zahooruddin Ahmed', 'Mohammed', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(49, 'Jayshneel', 'Sivan', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(50, 'Heman', 'Sharma', NULL, NULL, 'Techno engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(51, 'Luiz', 'Silva', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(52, 'Aswindra', 'Nand', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(53, 'Ilaitia', 'Sotia', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(54, 'Jitesh', 'Sharma', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(55, 'Hitesh', 'Parekh', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(56, 'Harsheel', 'Mishra', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(57, 'Andrew', 'Rin', NULL, NULL, 'The Trustee for the Rin Engineering Trust', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(58, 'Fahad', 'Bin Saddique', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(59, 'Shiva', 'Chaudhary', NULL, NULL, 'Ziva industrial pty ltd', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(60, 'herbert', 'steiner', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(61, 'Kamlesh', 'Joshi', NULL, NULL, 'Stark Food Systems', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(62, 'Diane', 'Delia', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(63, 'Frankie', 'Delia', NULL, NULL, 'Tyax Group Pty Ltd', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(64, 'Mark Dionnie', 'Bulingit', NULL, NULL, 'Sample', 'Other', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(65, 'Uday singh', 'Gagaan', NULL, NULL, 'Stark Food Systems', 'Part-time', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(66, 'Tejas', 'Patel', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(67, 'HENRY', 'FORD', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(68, 'Ravikumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Part-time', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(69, 'Muhammad Mamoon', 'Rauf', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(70, 'Kane', 'Neve', NULL, NULL, 'KN Fabrications', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(71, 'Corey', 'Crole', NULL, NULL, 'Kn fabrications', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(72, 'Dale', 'Virgo', NULL, NULL, 'K n fabrication', 'Contractor', NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(73, 'Ashnil', 'Raju', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(74, 'Kyle', 'Loveday', NULL, NULL, 'Kn fabrication', 'Casual', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(75, 'gf', 'htdh', NULL, NULL, 'fdgf', 'Contractor', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(76, 'Trina', 'Dennison', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(77, 'Warren', 'Davidson', NULL, NULL, 'KN FAB', 'Casual', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(78, 'Eparama', 'Ravaga', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(79, 'Mohammad', 'Raffay', NULL, NULL, 'Techno Engineering Pty Ltd', 'Contractor', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(80, 'Simran', 'Kaur', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(81, 'James', 'Chaudhary', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(82, 'Chirag', 'Patel', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(83, 'Sachinkumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(84, 'Ranjan', 'Shrivastav', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(85, 'Chandra', 'Naidu', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(86, 'Adrian', 'Azzopardi', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(87, 'Karan', 'Kishore Chahal', NULL, NULL, 'Techno Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(88, 'Shaneel', 'Kumar', NULL, NULL, 'Logan collision centre', 'Contractor', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(89, 'Nitin', 'Kumaran', NULL, NULL, 'Nitin', 'Contractor', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(90, 'Ibrahim Ali', 'Syed', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(91, 'Prince', 'Kwakwa', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(92, 'Jack', 'Martin', NULL, NULL, 'Jsm Electrical', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(93, 'Cooper', 'Jessen', NULL, NULL, 'JSM Electrical', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(94, 'Nitesh', 'Kumar', NULL, NULL, 'Stark Food Systems', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(95, 'asad', 'hussain', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(96, 'test', 'test', NULL, NULL, 'test', 'Trainee', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(97, 'Durga prasad', 'Nakka', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(98, 'Mahendrasinh', 'Chauhan', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(99, 'Mahendrasinh', 'Chauhan', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(100, 'Mahendrasinh', 'Chauhan', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(101, 'Billy', 'Travers', NULL, NULL, 'JSM Electrical', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(102, 'Benjamin', 'Bennett', NULL, NULL, 'JSM electrical', 'Casual', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(103, 'Kieran', 'McNally', NULL, NULL, 'JSM Electrical Pty Ltd', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(104, 'Lleyton', 'Woolley', NULL, NULL, 'JSM Electrial', 'Casual', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(105, 'Muhammad Danish', 'Ejaz', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(106, 'SachinKumar Ambalal', 'Patel', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(107, 'Zohaib', 'Ahmed', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(108, 'Patrick Darcy', 'Malseed', NULL, NULL, 'JSM Electrical', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(109, 'Alexander', 'Shaw', NULL, NULL, 'JSM Electrical', 'Contractor', NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(111, 'Chanh Trinh', 'Nguyen', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(112, 'Ravneel ravitesh', 'Prasad', NULL, NULL, 'contractor', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(113, 'Maninderpal', 'Singh', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(114, 'Loveneet', 'Kumar', NULL, NULL, 'Baiada poultry Pty ltd', 'Full-time', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(115, 'Yogeshkumar', 'Patel', NULL, NULL, 'Baiada', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(116, 'Hamish', 'Teichert', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(117, 'Sahibjot', 'Singh', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(118, 'Aporosa', 'Qio', NULL, NULL, 'contractor', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(119, 'Sanjeet', 'Prasad', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(120, 'Albert', 'Copeland', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(121, 'Michael', 'Singh', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(122, 'Nick', 'Vincent', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(123, 'Sabitesh', 'Pal', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(124, 'Rajneel', 'Sami', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(125, 'Rahul', 'Ragireddy', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(126, 'Brad', 'Cooper', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(127, 'Trent', 'Ngawaka Ale', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(128, 'Rakeshkumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(129, 'Sahil', 'Patil', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(130, 'Nik', 'Bosevski', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(131, 'Harawal', 'Ehsan', NULL, NULL, 'Techno Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(132, 'Satwant', 'Singh', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(133, 'Miracle', 'Manuele', NULL, NULL, 'Baiada', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(134, 'Israel', 'Braga', NULL, NULL, 'Stark Food Systems', 'Part-time', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(135, 'Inderjeet', 'Rana', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(136, 'Nathan', 'Priestly', NULL, NULL, 'Priestly Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(137, 'brodie', 'muscat', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(138, 'Leah', 'Leschinkohl', NULL, NULL, 'Priestley Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(139, 'Paul', 'Atkinson', NULL, NULL, 'Priestly Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(140, 'Liam', 'O\'Loughlan-Green', NULL, NULL, 'Priestly', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(141, 'Andrew', 'Lowe', NULL, NULL, 'Priestly Engineering', 'Other', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(142, 'Aldo Fabian', 'Mendoza', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(143, 'Zeb', 'Tallon', NULL, NULL, 'Priestly Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(144, 'Harbhajan', 'Singh', NULL, NULL, 'Stark Food Systems', 'Sub-contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(145, 'Harbhajan', 'Singh', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(146, 'Daniel', 'Whitbread', NULL, NULL, 'Priestly Engineering', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(147, 'Manojkumar', 'Patel', NULL, NULL, 'Baiada', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(148, 'Deepkumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(149, 'VINODKUMAR', 'PATEL', NULL, NULL, 'Stark Food Systems', 'Shift-worker', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(150, 'Saikumar', 'Tummala', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(151, 'Dylan', 'Hunt', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(152, 'Maulikkumar', 'patel', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(153, 'Daksheshkumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(154, 'Ronakkumar', 'Patel', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(155, 'Alexander', 'Briones', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(156, 'Rayyan Fazle Mubeen', 'Shaikh', NULL, NULL, 'Techno Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(157, 'ZIAUDDIN', 'MOHAMMED', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(158, 'Rahul', 'patel', NULL, NULL, 'BIADA', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(159, 'Amit', 'Kumar', NULL, NULL, 'Baiada', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(160, 'Kartik', 'Sharma', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(161, 'Shane', 'Freegrove', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(162, 'Ravneel', 'Asre', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(163, 'Peter', 'Barclay', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(164, 'BRIGHTWELL', 'PANGANAYI', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(165, 'Harrison', 'Prout', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(166, 'Benjamin', 'Watts', NULL, NULL, 'Priestly Engineering', 'Contractor', NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(167, 'Jimmy', 'Ford', NULL, NULL, 'Stark Food Systems', 'Full-time', NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(168, 'Mantej singh', 'Notra', NULL, NULL, 'Stark Food Systems', 'Contractor', NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(169, 'Jake', 'Barclay', NULL, NULL, 'Stark Food Systems', 'Casual', NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(213, 'Mark Dionnie', 'Bulingit', '09238988128', NULL, 'Stark Food Systems', 'Other', 'Mother', '09238988128', '2026-09-25 19:24:59', '2026-09-25 19:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `inductions`
--

CREATE TABLE `inductions` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `exam_id` int UNSIGNED DEFAULT NULL,
  `validity_months` smallint UNSIGNED NOT NULL,
  `content_blocks` json NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inductions`
--

INSERT INTO `inductions` (`id`, `title`, `code`, `description`, `exam_id`, `validity_months`, `content_blocks`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Stark Food Systems Induction Program', 'STARK-FOOD-SYSTEMS', 'Site safety induction covering plant OHS rules, PPE, workplace hazards, risk management, aerial lift safety, and defensive driving for Stark Food Systems personnel and contractors. Imported from a legacy induction system.', 1, 24, '[{\"id\": \"sec_000_e01a46\", \"title\": \"Getting Started\", \"blocks\": [{\"id\": \"img_002_460577\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/f3b7aef56f023e515794ba39584b7f99.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"smaller\", \"aspect\": \"natural\", \"caption\": \"\"}, {\"id\": \"blk_slonh4uq\", \"type\": \"text\", \"content\": \"<p>Stark Food Systems started operations in 2016 with the core management group combining their expertise on Fast-Moving Consumer Goods (FMCG), manufacturing, food processing systems and skilled workforce management in food production.</p><p>The core group has a combined managerial and solutions portfolio of 45 years in the food manufacturing industry which formed the solid foundation of Stark Food Systems and it has allowed the company to propel its innovation with the evolving FMCG and F&amp;B industry as dictated by the convergence of multi-cultural beliefs, technology integration and the benchmarks of the industry when it comes to cost efficiency of the food manufacturing lifecycle.</p>\"}], \"lectures\": [{\"id\": \"lec_001_2835cd\", \"title\": \"Plant OHS Rules\", \"blocks\": [{\"id\": \"img_003_c17366\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/a804d4ef994d1dfe9e31de5e4f312c6b.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_004_689b33\", \"title\": \"Car Parking and Vehicle Access\", \"blocks\": [{\"id\": \"txt_d4319a5d\", \"type\": \"text\", \"content\": \"<p><strong>Vehicle Access and Parking Guidelines</strong></p>\\n<ul>\\n<li><strong>Access Restrictions</strong></li>\\n<li><strong>Permitted Vehicles:</strong><br>Delivering tools or materials.<br>Vehicles required for service completion.</li>\\n<li><strong>Other Vehicles:</strong><br>Park in the visitors\' car park or on the street.<br>Onsite parking requires authorization from the Client</li>\\n</ul>\\n<p><strong>Entry Protocol</strong></p>\\n<ul>\\n<li><strong>Sign-In Requirement:</strong><br>Entry is prohibited without signing in.</li>\\n</ul>\\n<p><strong>Safety and Conduct</strong></p>\\n<ul>\\n<li><strong>Speed Limits:</strong><br>Observe posted speed limits.<br>Default speed limit: 8 km/h where not posted</li>\\n<li><strong>Right of Way:</strong><br>Give way to pedestrians and forklift traffic.</li>\\n<li><strong>Vehicle Safety:</strong><br>Vehicles are parked on-site at the owner’s risk.</li>\\n</ul>\"}, {\"id\": \"img_005_5be6bb\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/7fe081ed7ddb7e083db10b3b81c62f66.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_006_64bb03\", \"title\": \"Identity\", \"blocks\": [{\"id\": \"txt_a2959edb\", \"type\": \"text\", \"content\": \"<p><strong>Daily Sign-In Procedure:</strong></p><p>•Report to administration/security to sign in upon arrival every day.</p>\\n<p><strong>Identity Passes<br></strong>•Required at some sites as advised by the Stark Food Systems Contract Manager.<br>•Issued by security/reception or authorized representatives on arrival.<br>•Must be displayed at all times while on site.<br>•Collected on departure each day.</p>\\n<p><strong>Sign-Out Procedure<br></strong>•It is mandatory to sign out when leaving the site.</p>\\n<p><strong>Access Restriction<br></strong>•Access to any area without prior approval is not permitted.</p>\"}, {\"id\": \"img_007_c09359\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/268abfa515b012fa162217fa16bcfcbf.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"16-9\", \"caption\": \"\"}]}, {\"id\": \"lec_008_a316ae\", \"title\": \"Personal Hygiene\", \"blocks\": [{\"id\": \"txt_d862eaf4\", \"type\": \"text\", \"content\": \"<p><strong></strong></p>\\n<p><strong>No Jewellery Allowed:</strong></p>\\n<ol>\\n<li>No watches, chains, necklaces, bracelets, rings with stones, brooches, earrings/nose rings, etc.</li>\\n<li>Exception: Plain, flat wedding band with no stones allowed; if unable to be removed, must be covered with a company-approved band-aid.</li>\\n<li>Items that may get entangled in equipment or lost are prohibited in manufacturing, warehouse, and maintenance areas</li>\\n</ol>\\n<p><strong>Hair Nets Mandatory:</strong></p>\\n<ol>\\n<li>.Company-supplied hair nets must be worn; caps/hats not allowed.</li>\\n<li>Bump caps are allowed only with a company-approved hair net worn underneath, covering ears and all hair.</li>\\n</ol>\\n<p><strong>Beard Nets Required:</strong></p>\\n<ol>\\n<li>.Company-supplied beard nets are mandatory for covering beards and moustaches.</li>\\n<li>Need for beard nets for other facial hair at the discretion of Plant and Contract Managers.</li>\\n</ol>\\n<p><strong>Hand Washing Protocol:</strong></p>\\n<ol>\\n<li>Mandatory hand washing before entering and after leaving production areas to minimize disease transmission risk.</li>\\n</ol>\"}, {\"id\": \"img_009_7f54a9\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/8feb2c79f85d5ab92b7a827110376b05.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_010_aeab2a\", \"title\": \"No Eating or Drinking in the Factory\", \"blocks\": [{\"id\": \"img_011_387bbf\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/717a19d19df8f6ea0b5e2991c8d39d30.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"natural\", \"caption\": \"\"}, {\"id\": \"txt_8efa8ff9\", \"type\": \"text\", \"content\": \"<ul>\\n<li><strong>Prohibited Consumption:</strong><br>Only water from drinking fountains is allowed.</li>\\n<li><strong>Product Line Restrictions:</strong><br>•Do not consume products from the production line.</li>\\n<li><strong>Prevention of Cross-Contamination:</strong><br>No raw product in cooked areas.<br>Wash hands after handling raw products.</li>\\n<li><strong>Ban on Chewing Gum:</strong><br>Chewing gum is prohibited.</li>\\n<li><strong>Smoking Regulations:</strong><br>Smoking only in designated areas.</li>\\n<li><strong>Restrictions on Drinking Cups:</strong><br>No drinking cups allowed.</li>\\n</ul>\"}]}, {\"id\": \"lec_012_00405e\", \"title\": \"Smoking\", \"blocks\": [{\"id\": \"img_013_4d8d77\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/67d2a982913276ba44a972dc6a7594a0.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"natural\", \"caption\": \"\"}, {\"id\": \"txt_0844a85f\", \"type\": \"text\", \"content\": \"<p>Smoking is prohibited in the In-Plant Area except in locations specifically designated as smoking areas. During emergency or fire alarm actuation smoking is not permitted till an all-clear message from the rescue team has been announced.</p>\"}]}, {\"id\": \"lec_014_5a504c\", \"title\": \"General Requirements\", \"blocks\": [{\"id\": \"gal_000_a23523\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_aa553e2c\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/abb72b699f6be1bf17a7fb4446aca885.webp\", \"caption\": \"\"}, {\"id\": \"img_3acc46a0\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/299be8686cb3850aefd1d564ab1afa03.webp\", \"caption\": \"\"}, {\"id\": \"img_1ccd30a9\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/abd6b616afbe1b1023f3701404ada68d.webp\", \"caption\": \"\"}], \"columns\": 3}, {\"id\": \"txt_951a37a4\", \"type\": \"text\", \"content\": \"<p><strong>MATCHES &amp; CIGARETTE  LIGHTERS</strong></p>\\n<p>Personnel shall not bring matches or cigarette lighters into the In-Plant Area. Security Section shall monitor compliance. </p>\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n<p><strong>CAMERAS </strong></p>\\n<p>Cameras are not permitted on Company Property without written authorization. </p>\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n<p><strong>WEAPONS</strong></p>\\n<p>Weapons or ammunition shall not be brought on Company Property except for those carried by law enforcement officers. </p>\"}]}, {\"id\": \"lec_018_7d7dc4\", \"title\": \"Personal Protective Equipment\", \"blocks\": [{\"id\": \"gal_001_66768e\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_a028a76e\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/dbcdd0adf8c1bfb0ca6dbff1a5f71795.webp\", \"caption\": \"\"}, {\"id\": \"img_9c6ca79d\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/2dd95a095795438fe793cc12827254c3.webp\", \"caption\": \"\"}, {\"id\": \"img_20fa4f09\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/8049a5f93148eae7ef37c5b7964e2334.webp\", \"caption\": \"\"}, {\"id\": \"img_5b0b3d6a\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/ac048f6feca1ae2f2cfeaada3ba198f2.webp\", \"caption\": \"\"}, {\"id\": \"img_e991deb3\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/95bd2499298aac6d0d684a4ad5433442.webp\", \"caption\": \"\"}, {\"id\": \"img_5ef47216\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/dbe36ad82f40f03098d87211fd668c7d.webp\", \"caption\": \"\"}, {\"id\": \"img_adfa2771\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/13cd98331c3b44cb81c3dddfb3dcc4e5.webp\", \"caption\": \"\"}, {\"id\": \"img_a4255162\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/be1747f258a5112bf5646a1c7b0cef7a.webp\", \"caption\": \"\"}, {\"id\": \"img_74656d8f\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/df681207ec77989a008a0095874495da.webp\", \"caption\": \"\"}], \"columns\": 3}, {\"id\": \"txt_64a9c5ce\", \"type\": \"text\", \"content\": \"<p><strong>PERSONAL  PROTECTIVE EQUIPMENT ( PPE )</strong></p>\\n<p>All employees, Long Term Contract Employees, and visitors will use the PPE issued from the warehouse only. Use of PPE obtained from any other source is not allowed.</p>\\n<p>OHS Department shall approve PPE used by contractor employees ( other than Long Term Contract Personnel)</p>\\n\\n\\n<p><strong>EYE  PROTECTION <br></strong>All personnel must wear approved clear safety glasses, or mono goggles, while in the Operating Area.</p>\\n<p><strong>EXCEPTION: <br></strong>Offices and control rooms other than when work is being performed with chemicals or hand tools. </p>\\n<p><strong>HEAD PROTECTION</strong><br>Safety helmets shall be worn at all times by all personnel in the Operating Area with the exception of offices, laboratories,  vehicles (excluding forklifts and cranes), control rooms, and specified Shop areas.</p>\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n<p><strong>HEARING PROTECTION  <br></strong>All areas with continuous noise levels exceeding 85-dBA shall be considered as high noise areas. During presence in a noise area of 85-dBA or higher, ear protection shall be required at all times. Hearing protection shall be worn in locations that are posted </p>\\n<p><strong>FOOT PROTECTION</strong><br>Personnel assigned to work in the Operating Areas shall wear approved safety shoes with the Exception of offices, on roads, and in vehicles. </p>\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n\\n<p><strong>H</strong><strong>AND PROTECTION<br></strong>Chemical-resistant gloves shall be worn when handling hazardous chemicals or contaminated equipment.<br>Leather gloves shall be worn when handling stationary equipment or materials with sharp edges.<br>Specialty gloves shall be used during work involving temperature extremes, electricity, etc.</p>\\n<p><strong>CLOTHING PROTECTION<br></strong>Personnel assigned to work in the In-Plant Area shall not wear guthras or thobes. Loose clothing shall not be worn and long hair must be covered and restrained when work is performed around machinery with exposed moving parts.</p>\\n<p><strong></strong></p>\\n\\n<p><strong>FACE PROTECTION <br></strong>Personnel shall wear face protection (face shield) when handling or working with chemicals. Use a welding helmet while doing hot work </p>\\n\\n\\n<p><strong>RESPIRATORY PROTECTION <br></strong>The use of respiratory protection is mandatory in activities where the release of toxic chemicals is possible. Such activities include </p>\\n<ul>\\n<li><strong>Sampling </strong></li>\\n<li><strong>Draining </strong></li>\\n<li><strong>Opening lines/vessels<br></strong></li>\\n</ul>\"}]}, {\"id\": \"lec_028_df15aa\", \"title\": \"General Rules for Client Site Work\", \"blocks\": [{\"id\": \"img_029_637d8f\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/594feeb8b0598f66af83eb965f467fac.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"circle\", \"width\": \"smaller\", \"aspect\": \"natural\", \"caption\": \"\"}, {\"id\": \"txt_d38e5083\", \"type\": \"text\", \"content\": \"<p><strong></strong></p>\\n<p><strong>Signing In Procedure:<br></strong>• Contractors must sign into the Visitors book at Reception/Security.</p><p><strong>Tool and Equipment Handling<br></strong>• Avoid placing tools or equipment on Food Manufacturing equipment or workbenches.<br>• Use a toolbox or suitable container for tools.</p>\\n<p><strong>Safety during Grinding, Drilling, and Welding<br></strong>• Preferably perform outside the manufacturing area.<br>• If not possible, ensure full protection of equipment and products.</p>\\n<p><strong>Grease and Oil Approval:<br></strong>• Client approval is required for any grease and oil used.</p>\\n<p><strong>Waste Management:<br>• </strong>Collect and dispose of scrap or waste outside the Food Manufacturing area.</p>\\n<p><strong>Personal Protective Equipment (PPE):<br></strong>• Wear company-supplied dust coat or disposable coveralls.</p>\"}]}, {\"id\": \"lec_030_8f9ab1\", \"title\": \"Behaviour & Workplace Policies\", \"blocks\": [{\"id\": \"img_031_f19b77\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/93a2f114f27e3129e6e73e9b1baff9cd.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"circle\", \"width\": \"smaller\", \"aspect\": \"natural\", \"caption\": \"\"}, {\"id\": \"txt_b5329033\", \"type\": \"text\", \"content\": \"<ul>\\n<li><strong>Behaviour Expectations:</strong><br>• Courteous and professional behaviour is required.<br>• No tolerance for abusive or threatening conduct.</li>\\n<li><strong>Alcohol and Drugs Policy:</strong><br>• Strict prohibition of alcohol and illegal drugs on site.<br>• Consumption or possession leads to immediate expulsion and contract suspension.</li>\\n<li><strong>Confidentiality Agreement:</strong><br>• Respect the confidentiality of company information.<br>• Do not disclose without authorization.</li>\\n<li><strong>Equal Opportunity Policy:</strong><br>• Stark Food Systems is an Equal Opportunity Employer.<br>• Discrimination based on any protected characteristic is prohibited.</li>\\n<li><strong>Workplace Bullying/Harassment Policy:</strong><br>•Harassment-free work environment.<br>•Discrimination or harassment not tolerated; adhere to Workplace Bullying Policy.</li>\\n</ul>\"}]}]}, {\"id\": \"sec_032_b10384\", \"title\": \"Workplace Hazard Safety\", \"blocks\": [], \"lectures\": [{\"id\": \"lec_033_b0a0dc\", \"title\": \"Road Transportation\", \"blocks\": [{\"id\": \"gal_002_620003\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_7fd549a5\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/0f319440fde4eca749aa5eaafda06dea.webp\", \"caption\": \"\"}, {\"id\": \"img_53571f95\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/a5be938065fdc52862af766e599c77ec.webp\", \"caption\": \"\"}], \"columns\": 2}, {\"id\": \"txt_0a58db9d\", \"type\": \"text\", \"content\": \"<p><strong>TRAFFIC RULES </strong></p>\\n<ul>\\n<li>Vehicle speed 10 km/hr.  in plant premises.</li>\\n<li>Vehicle speed limit 20 km/hr. in Main parking areas</li>\\n<li>It is important to note that no attempt must be made to re-start vehicles if the presence of vapour cloud is known, evident, or suspected</li>\\n</ul>\\n<p><strong></strong></p>\\n\\n<p><strong>Vehicle passengers shall not:</strong></p>\\n<ul>\\n<li>Exceed the vehicle\'s rated passenger capacity.</li>\\n<li>Get on or off while vehicles are in motion</li>\\n<li>Ride on running boards or in the bed of trucks or pick-ups.</li>\\n<li>Vehicles shall be parked in designated parking Locations only. </li>\\n<li>Keys shall be left in the ignition when vehicles are parked at a non-designated location in the In-Plant Area. </li>\\n</ul>\\n\\n<ul>\\n<li>Vehicles shall not be parked within 5 meters of fire/emergency equipment (alarm boxes, hydrants, hose reels etc.</li>\\n<li>Only persons having valid driving licenses shall operate vehicles on company property. </li>\\n<li>Vehicle radios/cassette players shall not be operated in the In-Plant Area. </li>\\n<li>Lighters shall be permanently removed from Company vehicles assigned to the In-Plant Area. </li>\\n</ul>\"}]}, {\"id\": \"lec_036_1d0482\", \"title\": \"Electrical Safety\", \"blocks\": [{\"id\": \"txt_a51eeaaf\", \"type\": \"text\", \"content\": \"<p><strong>ELECTRICAL SAFETY</strong></p>\\n<ol>\\n<li>Portable electrical tools shall be double insulated, or grounded before use. Screwdrivers shall be insulated up to the tip.</li>\\n<li>Only non-metallic ladders shall be used for work on electrical systems. </li>\\n</ol>\"}, {\"id\": \"gal_003_bca429\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_01df23b1\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/19fd4821dcfd066b767e1dda3c067c15.webp\", \"caption\": \"\"}, {\"id\": \"img_f47c2811\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/41e9d5182185d315b95dfd264830e792.webp\", \"caption\": \"\"}], \"columns\": 2}]}, {\"id\": \"lec_039_6bb116\", \"title\": \"Working at Heights\", \"blocks\": [{\"id\": \"blk_18umnobl\", \"type\": \"text\", \"content\": \"<p><strong>LADDERS SAFETY </strong></p>\\n<ul>\\n<li>Ladders shall be visually inspected before each use to ensure that there are no defects and that safety feet are in place. Step ladders shall not be used when a straight or extension ladder can be substituted. Step ladders shall be positioned on a firm, level surface while in use. </li>\\n<li>Personnel shall not stand on a ladder with their knees extending above the top rung or step.</li>\\n</ul>\"}, {\"id\": \"blk_rxcb0f14\", \"url\": \"https://workplace-induction.test/assets/uploads/media-library/728f73c5c551c756d4d815d341e5fc4d.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"natural\", \"caption\": \"\"}, {\"id\": \"blk_xms0hqb3\", \"type\": \"text\", \"content\": \"<ul><li>Straight and extension ladders must be held secure by a second person during the initial ascent and the top of the ladder firmly secured by rope before subsequent climbs.</li>\\n<li>Personnel shall keep both hands free of tools or other items and face the ladder when ascending or descending.</li>\\n</ul>\"}, {\"id\": \"blk_f1ube1vh\", \"url\": \"https://workplace-induction.test/assets/uploads/media-library/9664a368e34c92a71c39b16996051fb6.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}, {\"id\": \"blk_gdk8vwj0\", \"type\": \"text\", \"content\": \"<p><strong>SCAFFOLDING  SAFETY</strong></p>\\n<ul>\\n<li>Training: Ensure all workers are trained in scaffold safety.</li>\\n<li>Fall Protection: Use guardrails and personal fall arrest systems (PFAS).</li>\\n<li>Safe Access: Provide ladders, stair towers, or ramps.</li>\\n<li>Load Capacity: Never exceed the scaffold’s load capacity.</li>\\n<li>Inspections: Perform daily checks for damages and misalignment.</li>\\n<li>Guardrails &amp; Toe Boards: Prevent falls and falling objects.</li>\\n<li>Weather Conditions: Avoid use in high winds or adverse.</li>\\n</ul>\"}, {\"id\": \"blk_dyctbndf\", \"url\": \"https://workplace-induction.test/assets/uploads/media-library/a6466aaca70730170e7231a48d41d1fe.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}, {\"id\": \"blk_1ykskq1g\", \"type\": \"text\", \"content\": \"<ul><li>“ Do not use” tag shall be placed at a prominent place (on access ladders) on the scaffolding during the construction of scaffoldings.</li>\\n<li> Scaffolders shall use full-body harnesses in accordance with Personal Protective Equipment while erecting or dismantling scaffolding.<br></li>\\n</ul>\"}, {\"id\": \"blk_eeshc20u\", \"url\": \"https://workplace-induction.test/assets/uploads/media-library/bb52f08e5324854ba39420de22b782b1.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_044_91e344\", \"title\": \"Plant OHS Rules\", \"blocks\": [{\"id\": \"txt_9252bc0c\", \"type\": \"text\", \"content\": \"<p><strong>COMPRESSOR AIR RESTRICTION  </strong></p>\\n<ul>\\n<li>Compressed air shall not be directed at anyone or any part of the body.</li>\\n<li>Compressed air used to clean equipment shall be limited to 2. bar pressure.<br></li>\\n</ul>\"}, {\"id\": \"img_045_2046f5\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/0ca7528970a185beaba6be3f08109fcf.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_046_c13f90\", \"title\": \"Emergency Response\", \"blocks\": [{\"id\": \"txt_c7d50227\", \"type\": \"text\", \"content\": \"<p><strong>FIRE EQUIPMENT &amp; FACILITIES  </strong></p>\\n<p>Personnel shall actuate the nearest emergency Local fire alarm in case of fire, personnel injury/illness, vapour cloud formation, or any other emergency requiring immediate assistance.</p>\\n\\n<p>All non-emergency vehicle traffic in the In-Plant Area must come to a stop when the site emergency alarm is sounded. Drivers should park their vehicles on the right shoulder of the road, with the engine switched off, and remain parked for a full five minutes or until the ALL CLEAR is sounded.</p>\\n<p>All personnel shall have the emergency alarm code in their position at all times and gather at the safe assembly point of that area.<br></p>\\n<p>When an <strong>Emergency Alarm</strong> is sounded, all personnel shall evacuate to the nearest Major Safe Assembly Point. In case of a designated MAJOR SAFE ASSEMBLY POINT not being available in the immediate vicinity, personnel shall enter the nearest building. Personnel, who end up at a location other than their designated Major Safe Assembly Point, shall communicate their location to their building warden. <br></p>\"}, {\"id\": \"gal_005_644f6f\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_66277065\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/1cfa89e9deae4cf3889866cf3046778d.webp\", \"caption\": \"\"}, {\"id\": \"img_06248402\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/7170ec800cea7f89045a1040cda3387f.webp\", \"caption\": \"\"}, {\"id\": \"img_d81504e9\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/9a19ed8cb3d12ce36e378b055c7bd50c.webp\", \"caption\": \"\"}, {\"id\": \"img_dee355a2\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/4bd6272712f7e93ff56acbe40aa7db74.webp\", \"caption\": \"\"}], \"columns\": 2}]}, {\"id\": \"lec_051_2a6903\", \"title\": \"Forklift Safety\", \"blocks\": [{\"id\": \"txt_2af598bf\", \"type\": \"text\", \"content\": \"<p><strong>Working Near Forklifts:</strong></p>\\n<ul>\\n<li>Always remain alert and aware of moving forklifts in your work area.</li>\\n<li>Stay within designated pedestrian zones and avoid walking in forklift pathways.</li>\\n<li>Make eye contact with the forklift operator before crossing their path.</li>\\n<li>Do not assume the operator sees you; wait for a clear signal to proceed.</li>\\n</ul>\\n<p><strong>General Safety Guidelines:</strong></p>\\n<ul>\\n<li>Wear high-visibility clothing to ensure you are seen by operators.</li>\\n<li>Avoid standing or walking close to a parked or operating forklift.</li>\\n<li>Be cautious of blind spots and intersections where forklifts may appear suddenly.<br><br></li>\\n</ul>\\n<p><strong>Emergency Situations:</strong></p>\\n<ul>\\n<li>Move to a safe location immediately if a forklift is out of control or in an emergency stop situation.</li>\\n<li>Report any unsafe forklift behaviour or near-misses to your supervisor promptly.<br></li>\\n</ul>\"}, {\"id\": \"img_052_6139a1\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/f8e05e68ca35bd316948a1715710be67.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_053_a691ec\", \"title\": \"Workshop Safety & Rules\", \"blocks\": [{\"id\": \"txt_a5603e3e\", \"type\": \"text\", \"content\": \"<p><strong>Maintain a Clean and Organized Workspace:</strong></p>\\n<ul>\\n<li>Keep workstations and walkways clear of clutter and spills.</li>\\n<li>Store tools and materials in designated areas.</li>\\n<li>Report any damaged tools or equipment for repair.<br><br></li>\\n</ul>\\n<p><strong>Use Personal Protective Equipment (PPE):</strong></p>\\n<ul>\\n<li>Wear safety gear, including gloves, safety glasses, and steel-toe boots.</li>\\n<li>Regularly inspect PPE and replace it if damaged.<br><br></li>\\n</ul>\\n<p><strong>Emergency and Electrical Safety:</strong></p>\\n<ul>\\n<li>Familiarize yourself with emergency exits and fire extinguishers.</li>\\n<li>Use non-metallic ladders for electrical tasks</li>\\n<li>Ensure tools and systems are grounded and avoid overloading outlets<br></li>\\n</ul>\"}, {\"id\": \"img_054_26220e\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/166dee30d9b5cd49e6f3ba7f5fcaf2e6.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_055_ee048e\", \"title\": \"Health Safety\", \"blocks\": [{\"id\": \"txt_7e462532\", \"type\": \"text\", \"content\": \"<p><strong>INJURY </strong></p>\\n<ul>\\n<li>All occupational injuries/illnesses and chemical exposures shall be promptly reported to the immediate supervisor. </li>\\n<li>All occupational injuries/illnesses shall be immediately reported and treated at the Clinic.<br></li>\\n</ul>\"}, {\"id\": \"gal_006_ee21d9\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_506af929\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/64f15b6ac7272943f5d066d9b6e1a6cd.webp\", \"caption\": \"\"}, {\"id\": \"img_a3764f08\", \"url\": \"https://workplace-induction.test/assets/uploads/stark-induction/slide-19.png\", \"caption\": \"\"}, {\"id\": \"img_c80942d7\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/81d8125f77f14b1bc3f4c6d130c981bf.webp\", \"caption\": \"\"}], \"columns\": 3}]}, {\"id\": \"lec_059_53bfa7\", \"title\": \"General Plant OHS Rules\", \"blocks\": [{\"id\": \"txt_4bf0c597\", \"type\": \"text\", \"content\": \"<p><strong>USE  OF  HEAVY EQUIPMENT</strong></p>\\n<p>A separate Class safe work permit shall be processed for the use of a crane as part of any job in the in-plant areas.  A safe work permit for crane use shall be issued based on the availability of an approved rigging plan and crane inspection checklist, as per (Lifting Equipment).<br></p>\"}, {\"id\": \"img_060_1ee03e\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/0c576d285654a939bcdd486e1bbd0e96.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_061_d1d489\", \"title\": \"LOTO & Equipment Isolation\", \"blocks\": [{\"id\": \"txt_63080a31\", \"type\": \"text\", \"content\": \"<p><strong> </strong></p>\\n<p><strong>PURPOSE </strong></p>\\n<p><strong>⮚ </strong>To prevent injury to maintenance employees due to the unexpected energization or startup of machines and  equipment, or release of stored energy </p>\\n<p><strong>⮚ </strong>By ensuring equipment prepared for maintenance is properly isolated and made safe for work and that points of isolation, including requirements for Electrical Lockout/Tag out for work on electric equipment or systems, are identified and addressed. </p>\\n\\n\\n<p><strong>⮚ LOCK-OUT</strong><br>•  Placement of a lock-out on an energy-isolating device to ensure that the energy-isolating device and the equipment being controlled shall not be operated until the lock-out device is removed.</p>\\n\\n\\n<p><strong>⮚ TAG-OUT</strong><br>•  Placement of a tag-out device “Danger” tag on an energy-isolating device to indicate that the energy-isolating device and the equipment being controlled shall not be operated until the tag-out device is removed.</p>\\n<p><strong>⮚ GANG LOCKOUT DEVICE:</strong><br>• The device which can hold six locks and is used for one energy source.<br>• Locks &amp; Gang lockout devices shall be strong enough to prevent removal without the use of force or unusual techniques, such as with the use of bolt cutters or other metal cutting tools.</p>\"}, {\"id\": \"gal_007_fc4191\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_83209edf\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/d48573142e436163d58cd74ea6436f11.webp\", \"caption\": \"\"}, {\"id\": \"img_5033f1d5\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/74d91d302e330adb5f23eec2220739ce.webp\", \"caption\": \"\"}, {\"id\": \"img_34bbcfd5\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/4cb2ca00ed4a9ab038f1f90b2b0399fc.webp\", \"caption\": \"\"}], \"columns\": 3}]}]}, {\"id\": \"sec_065_210d01\", \"title\": \"Risk Management\", \"blocks\": [], \"lectures\": [{\"id\": \"lec_066_3230d4\", \"title\": \"What Is Risk Management?\", \"blocks\": [{\"id\": \"txt_cb26ebbb\", \"type\": \"text\", \"content\": \"<ul>\\n<li><strong>Risk management i</strong>s the identification, assessment, and prioritization of risks  followed by the coordinated and economical application of resources to minimize, monitor, and control the probability and/or impact of unfortunate events, or to maximize the realization of opportunities</li>\\n</ul>\"}, {\"id\": \"img_067_9524b5\", \"url\": \"https://workplace-induction.test/assets/uploads/stark-induction/slide-23.png\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"natural\", \"caption\": \"\"}]}, {\"id\": \"lec_068_3a063d\", \"title\": \"Why Do We Need Risk Managment?\", \"blocks\": [{\"id\": \"txt_9cc1feba\", \"type\": \"text\", \"content\": \"<p><strong>The purpose of risk management is to:</strong></p>\\n<ul>\\n<li>Identify possible risks.</li>\\n<li>Reduce or allocate risks.</li>\\n<li>Provide a rational basis for better decision-making regarding all risks.</li>\\n<li>Plan.</li>\\n</ul>\\n<p>Assessing and managing risks is the best weapon you have against catastrophes.</p>\\n<p>By evaluating your plan for potential problems and developing strategies to address them, you’ll improve your chances of a successful, if not perfect, project.</p>\\n<p>Additionally, continuous risk management will:</p>\\n<ul>\\n<li>Ensure that high-priority risks are aggressively managed and that all risks are cost-effectively managed throughout the project.</li>\\n<li>Provide management at all levels with the information required to make informed decisions on issues critical to project success.</li>\\n</ul>\\n<p>If you don’t actively attack risks, they will actively attack you!</p>\"}, {\"id\": \"img_069_2675b0\", \"url\": \"https://workplace-induction.test/assets/uploads/stark-induction/slide-25.png\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"natural\", \"caption\": \"\"}]}]}, {\"id\": \"sec_070_21ace7\", \"title\": \"Aerial Lifts & Elevated Platforms\", \"blocks\": [{\"id\": \"img_071_afbd2b\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/2d13cf81c52ac29bdd69a69556c14566.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}], \"lectures\": [{\"id\": \"lec_072_cc78e6\", \"title\": \"Preparing the Travel Path\", \"blocks\": [{\"id\": \"txt_6c8a52c5\", \"type\": \"text\", \"content\": \"<p><strong>Inspection</strong>:</p>\\n<p>Before using a lift, inspect your planned travel path for the following hazards:</p>\\n<ul>\\n<li>Holes, curbs, slopes, drop-offs, or other uneven surface features</li>\\n<li>Ground that is unstable, soft, or incapable of supporting the weight of the lift</li>\\n<li>Overhead obstructions, including cranes</li>\\n</ul>\\n<p>Choose a path that is free of these hazards.</p>\\n<p><strong>Safe practices:</strong></p>\\n<ul>\\n<li>If any overhead cranes are in your path, ensure that they are locked and tagged as out of service.</li>\\n<li>If using a boom lift, make sure to plan for the boom’s swing radius, and ensure that personnel is clear.</li>\\n<li>If working near traffic, set up work-zone warnings (e.g., cones or signs).</li>\\n</ul>\"}, {\"id\": \"img_073_cd3df9\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/2ad33fca0f300a30139e4d8a5be868e5.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_074_f03435\", \"title\": \"Moving the Lift\", \"blocks\": [{\"id\": \"txt_91e36ed8\", \"type\": \"text\", \"content\": \"<p><strong>Make sure that all components of the lift are properly secured before transport to the worksite.</strong></p>\\n<ul>\\n<li><strong>Drive slowly:</strong> Maintain a safe travel speed.</li>\\n<li>Always face the direction of travel.</li>\\n<li><strong>Stay aware of surroundings:</strong> Watch for any blind spots, and use a safety spotter whenever necessary.</li>\\n<li><strong>Avoid obstacles: </strong>Maintain a safe distance from holes, ramps, drop-offs, and any other features that could cause the platform to overturn.</li>\\n<li><strong>Lower the platform: </strong>When travelling between work areas, lower the platform to increase stability. If this is not possible, proceed slowly and always keep the travel surface in view.</li>\\n<li><strong>Be aware of platform orientation:</strong> Stay aware of changes in the platform’s orientation (e.g., if on a pivoting boom).</li>\\n</ul>\\n\\n\\n<p><strong>Backing up:</strong></p>\\n<p>In order to protect the other employees in the area, do not back up the lift <strong>unless </strong>one of the following is true:</p>\\n<ul>\\n<li>The driver has a clear rear view.</li>\\n<li>The lift has a backup alarm.</li>\\n<li>Another employee serves as a spotter.</li>\\n</ul>\"}, {\"id\": \"gal_008_c52889\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_bf8b95d3\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/2047114d0aa55016cdd5336717491b46.webp\", \"caption\": \"\"}, {\"id\": \"img_be4643c7\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/20be71d68bc1e611c343ff05c6a3fdbd.webp\", \"caption\": \"\"}], \"columns\": 2}]}, {\"id\": \"lec_077_34ab1e\", \"title\": \"Destabilizing Factors\", \"blocks\": [{\"id\": \"txt_c01fd334\", \"type\": \"text\", \"content\": \"<p><strong>Excessive horizontal load may knock the lift off-balance and cause it to tip over.</strong></p>\\n<ul>\\n<li><strong>Exceeding the load capacity: </strong>Do not overload the lift with excess personnel, tools, or other materials. This may result in a tip-over of the lift or structural failure.<br></li>\\n<li><strong>Uneven terrain: </strong>Setting the lift on uneven terrain may also cause tipping.<br></li>\\n<li><strong>Horizontal load capacity:</strong> Do not exceed your platform’s horizontal load capacity. This is any force applied to the platform from the side that could affect balance.</li>\\n<li><strong>Entanglement: </strong>Ropes, cords, or hoses hanging outside of the platform.</li></ul>\\n<p><strong>Best practices for maintaining stability:</strong></p>\\n<ul>\\n<li>Be sure that the lift’s capacities for weight and horizontal load are not exceeded.</li>\\n<li>Assure that no ropes, cords, or hoses are hanging outside of the platform. (Do not move the lift if they are.)</li>\\n<li>When raising or lowering the lift, do not allow the lift to contact or catch on any objects (e.g., exposed piping or wiring, walls, ceilings, or other vehicles).</li>\\n<li>Watch for overhead obstacles.</li>\\n<li>Do not hoist items from an elevated platform.</li>\\n<li>Do not use the lift as a crane or rigging device unless such activity is approved by the manufacturer.</li>\\n<li>If the lift includes outriggers, they must be properly set prior to use, unless the manufacturer’s recommendations indicate that the lift can be used safely without them.</li>\\n<li>Keep outriggers in view while setting and make sure that all objects or personnel are clear from their motion.</li>\\n</ul>\\n<p><strong>A platform’s horizontal load and weight capacities can be found in the operator’s manual and may also be indicated on the platform’s data plate or warning labels.</strong></p>\"}, {\"id\": \"img_078_f38c9e\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/5d5177462e258de95c5c1b4ac49c6dd6.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_079_14fb8e\", \"title\": \"Electrocution\", \"blocks\": [{\"id\": \"txt_ae5db454\", \"type\": \"text\", \"content\": \"<p><strong></strong></p>\\n<p><strong>Electrical structures, such as overhead power lines or extension cords, can present electrical hazards to workers in aerial lifts.</strong></p>\\n<p><strong>Precautions:</strong></p>\\n<ul>\\n<li>Do not allow equipment or materials to form a conduit between an electrical structure and an aerial lift while a worker is in the basket.</li>\\n<li>Maintain a safe distance from power lines.</li>\\n</ul>\\n<p><strong>Hazard assessments: </strong></p>\\n<ul>\\n<li>Conduct a hazard assessment.</li>\\n<li>The assessment will assist in the establishment of <strong>minimum approach distances (MADs)</strong>, which are based on the specific electrical sources and magnitude, altitude, and worker qualifications.</li>\\n</ul>\\n<p><strong>Controls:</strong></p>\\n<ul>\\n<li>Whenever possible, de-energize or insulate power lines. If that is not possible, use proper PPE and other electrical safety gear based on your hazard assessment.</li>\\n<li>Use insulated buckets near power lines, and regularly inspect the bucket insulation.</li>\\n</ul>\\n<p><strong>Distance: </strong></p>\\n<ul>\\n<li><strong>For non-electrical workers: </strong>Stay at least 10 feet away from overhead power lines.</li>\\n<li><strong>For electrical workers: </strong>Follow established MADs.</li>\\n</ul>\"}, {\"id\": \"gal_009_de3157\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_45e94b36\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/902017857f638caa5b8798d5b74a1185.webp\", \"caption\": \"\"}, {\"id\": \"img_b62d633a\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/fa81152e45630e758b1c3fd1164d6172.webp\", \"caption\": \"\"}], \"columns\": 2}]}, {\"id\": \"lec_082_e8da8a\", \"title\": \"Falls\", \"blocks\": [{\"id\": \"txt_7d1d3795\", \"type\": \"text\", \"content\": \"<p><strong> </strong></p>\\n<p><strong>Using fall protection:</strong></p>\\n<ul>\\n<li>Fall protection is required when using articulated or telescoping boom lifts.</li>\\n<li>Fall protection is a best practice when using any lift, including scissor lifts. </li>\\n<li>Refer to your employer’s policies and procedures to verify when fall protection is required.</li>\\n<li>When using fall protection:<br>‒Before use, inspect all components of the harness, lanyard, and anchor points.<br>‒Only connect to designated anchor points provided or approved by the manufacturer.<br>‒Never tie off outside the platform.</li>\\n</ul>\\n<p><strong>Falling hazards:</strong></p>\\n<ul>\\n<li>Personnel or equipment falling from an elevated platform</li>\\n<li>Ground-based personnel or equipment being struck by falling objects</li>\\n</ul>\\n\\n\\n<p><strong>To avoid falling from the lift:</strong></p>\\n<ul>\\n<li>Always stay inside the basket.</li>\\n<li>Keep both feet firmly planted on the floor.</li>\\n<li>While in motion, keep your arms and hands inside the basket at all times.</li>\\n<li>While elevated, do not transfer to other platforms.</li>\\n<li>Do not strain to reach items: if something is out of your reach with both feet planted, including overhead objects, position the lift closer to the item.<br><br><strong>Avoiding struck-by hazards:</strong></li>\\n<li>Use barriers and signs to keep personnel out from under lifts.</li>\\n<li>Personnel working around potential falling objects should wear necessary PPE, such as hard hats.</li>\\n</ul>\"}, {\"id\": \"gal_010_4084e3\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_ead98837\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/bca2aa90340f09f3c92359933e1204ba.webp\", \"caption\": \"\"}, {\"id\": \"img_35207a9f\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/9292bfda85b81d17226aae6d1be86a34.webp\", \"caption\": \"\"}], \"columns\": 2}]}]}, {\"id\": \"sec_085_fdad2c\", \"title\": \"Defensive Driving\", \"blocks\": [], \"lectures\": [{\"id\": \"lec_086_93953b\", \"title\": \"Defensive Driving\", \"blocks\": [{\"id\": \"txt_80fd3207\", \"type\": \"text\", \"content\": \"<p><strong>Vehicle Maintenance &amp; Safety</strong></p>\\n<ul>\\n<li>Regular check Oil and change if required </li>\\n<li>Fluid levels</li>\\n<li>Brakes</li>\\n<li>Tire treads and pressure</li>\\n<li>Lights, signals, and wipers</li>\\n</ul>\\n\\n<p><strong>Don’t Drink or Take Drugs and Drive</strong></p>\\n<p><strong>Alcohol and drugs impair:</strong></p>\\n<ul>\\n<li>Ability to determine distances</li>\\n<li>Reaction time</li>\\n<li>Judgment</li>\\n<li>Vision</li>\\n</ul>\\n<p><strong>Remember:</strong></p>\\n<ul>\\n<li>Only time, not coffee, will sober you up.</li>\\n<li>Ride with a sober driver.</li>\\n</ul>\"}, {\"id\": \"gal_011_253d5d\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_3872aa7d\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/d49a5d0a814e01c10c56bc895bb8509f.webp\", \"caption\": \"\"}, {\"id\": \"img_d9854a6a\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/9844e0f42b9fba21d1a5853afddc5e61.webp\", \"caption\": \"\"}], \"columns\": 2}]}, {\"id\": \"lec_089_d21a3d\", \"title\": \"Pre-Drive Inspection\", \"blocks\": [{\"id\": \"txt_fc17834b\", \"type\": \"text\", \"content\": \"<p><strong>Walk around inspection</strong></p>\\n<ul>\\n<li>Tires &amp; Radiators</li>\\n<li>Leaks under vehicle</li>\\n<li>Windows clean, nothing blocking vision</li>\\n<li>Mirrors properly adjusted</li>\\n<li>Lights working properly</li>\\n</ul>\"}, {\"id\": \"img_090_4ddc22\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/21db50f2d311e731f72405b6c8052578.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_091_a20294\", \"title\": \"Stay Alert\", \"blocks\": [{\"id\": \"txt_4c86783d\", \"type\": \"text\", \"content\": \"<p><strong>Stay Alert</strong></p>\\n<ul>\\n<li>Keep your mind on your driving and your hands on the wheel.</li>\\n<li>Scan the road ahead for problems.</li>\\n<li>Check mirrors frequently.</li>\\n<li>Expect the unexpected.</li>\\n<li>Yield to other drivers who are determined to get there first.</li>\\n</ul>\\n\\n\\n<p><strong>Be Prepared</strong></p>\\n<ul>\\n<li>Make sure you are well-rested before you get behind the wheel.</li>\\n<li>Commit to getting enough sleep the night before driving.</li>\\n<li>If you feel sleepy while driving, pull over and take a 30-minute nap.</li>\\n</ul>\"}, {\"id\": \"gal_012_691d68\", \"type\": \"gallery\", \"images\": [{\"id\": \"img_25c9f90f\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/bd69459c98e2eac8e0d41cc4d51245d3.webp\", \"caption\": \"\"}, {\"id\": \"img_8f1285ec\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/b9608e05ad1cd2c7257ae4f93bad9263.webp\", \"caption\": \"\"}, {\"id\": \"img_7491dfb7\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/63be4ff141378047e3b881d256a9c254.webp\", \"caption\": \"\"}], \"columns\": 3}]}, {\"id\": \"lec_095_b1b76a\", \"title\": \"Speed\", \"blocks\": [{\"id\": \"txt_55d0336e\", \"type\": \"text\", \"content\": \"<p><strong>According to statistics, speeding causes about 30% of roadway fatalities</strong></p>\\n<ol>\\n<li>Avoid speeding</li>\\n<li>Stay aware of the posted speed limit </li>\\n<li>Watch your speed<strong></strong></li>\\n</ol>\"}, {\"id\": \"img_096_4a2f8a\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/42f7c1d17293552194f1a456748ea9b6.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_097_e8e3da\", \"title\": \"Steps to Prevent Distracted Driving\", \"blocks\": [{\"id\": \"txt_1b1b21a0\", \"type\": \"text\", \"content\": \"<p><strong> </strong><strong>Take steps to prevent distracted driving </strong></p>\\n<ol>\\n<li>Send texts or make phone calls before you get on the road</li>\\n<li>Keep your phone on silent while you drive </li>\\n<li>Eat or drink before you drive </li>\\n<li>Keep conversations with passengers to a minimum </li>\\n<li>Plan and review your route beforehand </li>\\n<li>Pay attention to your surroundings</li>\\n</ol>\"}, {\"id\": \"img_098_299380\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/1aac24aa8eefd7150fc35e649fc18527.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_099_b71f40\", \"title\": \"Safe Practices\", \"blocks\": [{\"id\": \"txt_3d1b0f5e\", \"type\": \"text\", \"content\": \"<ol>\\n<li>Pay attention to your own driving </li>\\n<li>Leave at least two car lengths / 3-second stopping gap between your car and the car in front of you </li>\\n<li>Use your blinker and safely merge into another lane as needed Before overtaking check your back mirror to ensure that you have enough clearance to overtake.</li>\\n</ol>\"}, {\"id\": \"img_100_6733f3\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/80804f970a6e9b4efaefe3317261e626.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_101_636a3d\", \"title\": \"Aggressive Driving Behavior\", \"blocks\": [{\"id\": \"txt_8989e5e9\", \"type\": \"text\", \"content\": \"<p><strong></strong></p>\\n<p><strong>Avoid AGGRESSIVE DRIVING BEHAVIOUR like </strong></p>\\n<ul>\\n<li>Honking the horn, yelling, or making rude gestures.</li>\\n<li>Tailgating.</li>\\n<li>Wildly changing lanes to get around slower cars.</li>\\n<li>Speeding around other cars.</li>\\n<li>Ignore aggressive behaviour and give aggressive drivers a wide berth.</li>\\n</ul>\"}, {\"id\": \"img_102_852c49\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/46decb8e675a0a0cba86de6e4dc90e87.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_103_da4087\", \"title\": \"Severe Weather\", \"blocks\": [{\"id\": \"txt_f43f57fe\", \"type\": \"text\", \"content\": \"<ul>\\n<li>Slow down</li>\\n<li>Turn on lights </li>\\n<li>Be prepared to handle a skid / Push from a gush of wind safely</li>\\n<li>Keep the focus on the road </li>\\n<li>Stay aware of your surroundings </li>\\n<li>Go with the flow of traffic, and Avoid over-tack. </li>\\n</ul>\"}, {\"id\": \"img_104_222d8c\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/ca385763991b63341f7b20f58e3aab07.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_105_2f45b2\", \"title\": \"Carrying a Load\", \"blocks\": [{\"id\": \"txt_f7c37152\", \"type\": \"text\", \"content\": \"<ul>\\n<li>Don’t overload your vehicle</li>\\n<li>Make sure everyone has a seat and a seat belt</li>\\n<li>Make sure cargo is properly secured</li>\\n<li>Be certain your vision is not blocked</li>\\n</ul>\"}, {\"id\": \"img_106_fd5b07\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/dde4a252679c2b04d5838d52a9e5e1a7.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_107_11aa61\", \"title\": \"Night Driving\", \"blocks\": [{\"id\": \"txt_4978b2d5\", \"type\": \"text\", \"content\": \"<p><strong>Be Extra Careful at Night</strong></p>\\n<ul>\\n<li>Keep the windshield clean to improve vision.</li>\\n<li>Turn lights on 1/2 hour before sunset.</li>\\n<li>Increase the following distance to 4 seconds.</li>\\n<li>Be extra careful on curves and at intersections.</li>\\n<li>Switch from high to low beams to keep from blinding other drivers.</li>\\n<li>If you have trouble, pull completely off the road and use flashers.<br><strong></strong></li>\\n</ul>\"}, {\"id\": \"img_108_25cc07\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/4ec1cbec45caf6ece81a1b05d0c8ca44.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_109_040b4b\", \"title\": \"Driving Fatigue\", \"blocks\": [{\"id\": \"txt_60dcc988\", \"type\": \"text\", \"content\": \"<p><strong>Watch Out for Fatigue</strong></p>\\n<ul>\\n<li>Be especially careful during late night, early morning, and mid-afternoon hours.</li>\\n<li>Stop, take a nap, and drink coffee if you’re tired.</li>\\n<li>Stop every so often over long distances.</li>\\n<li>Avoid medications that can cause Sleep.<strong><br></strong></li>\\n</ul>\"}, {\"id\": \"img_110_929b94\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/1f6252481c33ba8275ee53db3da14008.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}, {\"id\": \"lec_111_94f004\", \"title\": \"Traffic Signs & Road Safety\", \"blocks\": [{\"id\": \"txt_cc05c094\", \"type\": \"text\", \"content\": \"<p>Traffic signs give information about the road conditions ahead, provide instructions to be followed at major crossroads or junctions, warn or guide drivers, and ensure the proper functioning of road traffic</p>\"}, {\"id\": \"img_112_9a8741\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/c92f213f020a8dade58e8184e057445c.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}]}, {\"id\": \"sec_113_dc6a70\", \"title\": \"Course Completion\", \"blocks\": [], \"lectures\": [{\"id\": \"lec_114_c6646c\", \"title\": \"Thank You\", \"blocks\": [{\"id\": \"txt_b52cb690\", \"type\": \"text\", \"content\": \"<p>Thank you for your time in completing the Stark Food Systems Induction.<br><br>The information contained in this induction will ensure that our safety, quality and environmental standards are met. <br><br>Please complete the short competency quiz. (4 Questions)</p>\\n<p><strong>Note: Satisfactory completion of the quiz is mandatory before work can commence.</strong></p>\"}, {\"id\": \"img_115_a6723d\", \"url\": \"http://workplace-induction.test/assets/uploads/media-library/b1f06f1f8a9be0aef942e60c9af6f940.webp\", \"type\": \"image\", \"align\": \"center\", \"shape\": \"as-is\", \"width\": \"content\", \"aspect\": \"4-3\", \"caption\": \"\"}]}]}]', 'active', '2026-09-23 18:24:48', '2026-09-25 12:03:16');

-- --------------------------------------------------------

--
-- Table structure for table `media_categories`
--

CREATE TABLE `media_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_categories`
--

INSERT INTO `media_categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(13, '@Global', '2026-09-24 02:07:35', '2026-09-24 02:07:35'),
(17, 'SFS Induction', '2026-09-25 02:14:13', '2026-09-25 02:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `media_items`
--

CREATE TABLE `media_items` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media_items`
--

INSERT INTO `media_items` (`id`, `category_id`, `filename`, `original_filename`, `mime_type`, `size`, `created_at`, `updated_at`) VALUES
(26, 17, 'f3b7aef56f023e515794ba39584b7f99.webp', 'induction intro.webp', 'image/webp', 78028, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(27, 17, 'a804d4ef994d1dfe9e31de5e4f312c6b.webp', 'page 02.webp', 'image/webp', 60500, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(28, 17, '7fe081ed7ddb7e083db10b3b81c62f66.webp', 'page 03.webp', 'image/webp', 35544, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(29, 17, '268abfa515b012fa162217fa16bcfcbf.webp', 'page 04.webp', 'image/webp', 52356, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(30, 17, '8feb2c79f85d5ab92b7a827110376b05.webp', 'page 05.webp', 'image/webp', 17522, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(31, 17, '717a19d19df8f6ea0b5e2991c8d39d30.webp', 'page 06.webp', 'image/webp', 9914, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(32, 17, '67d2a982913276ba44a972dc6a7594a0.webp', 'page 07.webp', 'image/webp', 67082, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(33, 17, 'abb72b699f6be1bf17a7fb4446aca885.webp', 'page 08.webp', 'image/webp', 22838, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(34, 17, '299be8686cb3850aefd1d564ab1afa03.webp', 'page 09.webp', 'image/webp', 23228, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(35, 17, 'abd6b616afbe1b1023f3701404ada68d.webp', 'page 10.webp', 'image/webp', 22406, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(36, 17, 'dbcdd0adf8c1bfb0ca6dbff1a5f71795.webp', 'page 11.webp', 'image/webp', 54006, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(37, 17, '2dd95a095795438fe793cc12827254c3.webp', 'page 12.webp', 'image/webp', 36578, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(38, 17, '8049a5f93148eae7ef37c5b7964e2334.webp', 'page 13.webp', 'image/webp', 40994, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(39, 17, 'ac048f6feca1ae2f2cfeaada3ba198f2.webp', 'page 14.webp', 'image/webp', 40140, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(40, 17, '95bd2499298aac6d0d684a4ad5433442.webp', 'page 15 (1).webp', 'image/webp', 40992, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(41, 17, 'dbe36ad82f40f03098d87211fd668c7d.webp', 'page 16.webp', 'image/webp', 64322, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(42, 17, '13cd98331c3b44cb81c3dddfb3dcc4e5.webp', 'page 17.webp', 'image/webp', 51238, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(43, 17, 'be1747f258a5112bf5646a1c7b0cef7a.webp', 'page 18.webp', 'image/webp', 77892, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(44, 17, 'df681207ec77989a008a0095874495da.webp', 'page 19.webp', 'image/webp', 39212, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(45, 17, '594feeb8b0598f66af83eb965f467fac.webp', 'page 20.webp', 'image/webp', 29878, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(46, 17, '93a2f114f27e3129e6e73e9b1baff9cd.webp', 'page 21.webp', 'image/webp', 32642, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(47, 17, '0f319440fde4eca749aa5eaafda06dea.webp', 'page 22.webp', 'image/webp', 33736, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(48, 17, 'a5be938065fdc52862af766e599c77ec.webp', 'page 23.webp', 'image/webp', 67342, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(49, 17, '19fd4821dcfd066b767e1dda3c067c15.webp', 'page 25.webp', 'image/webp', 87102, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(50, 17, '41e9d5182185d315b95dfd264830e792.webp', 'page 26.webp', 'image/webp', 78886, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(51, 17, '728f73c5c551c756d4d815d341e5fc4d.webp', 'page 27.webp', 'image/webp', 77584, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(52, 17, '9664a368e34c92a71c39b16996051fb6.webp', 'page 28.webp', 'image/webp', 34922, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(53, 17, 'a6466aaca70730170e7231a48d41d1fe.webp', 'page 29.webp', 'image/webp', 96958, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(54, 17, 'bb52f08e5324854ba39420de22b782b1.webp', 'page 30.webp', 'image/webp', 37972, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(55, 17, '0ca7528970a185beaba6be3f08109fcf.webp', 'page 31.webp', 'image/webp', 29246, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(56, 17, '1cfa89e9deae4cf3889866cf3046778d.webp', 'page 32.webp', 'image/webp', 41466, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(57, 17, '7170ec800cea7f89045a1040cda3387f.webp', 'page 33.webp', 'image/webp', 18358, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(58, 17, '9a19ed8cb3d12ce36e378b055c7bd50c.webp', 'page 34.webp', 'image/webp', 35938, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(59, 17, '4bd6272712f7e93ff56acbe40aa7db74.webp', 'page 35.webp', 'image/webp', 42724, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(60, 17, 'f8e05e68ca35bd316948a1715710be67.webp', 'Forklift Safety.webp', 'image/webp', 39286, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(61, 17, '166dee30d9b5cd49e6f3ba7f5fcaf2e6.webp', 'Workshop Safety and Rules.webp', 'image/webp', 40840, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(62, 17, '64f15b6ac7272943f5d066d9b6e1a6cd.webp', 'page 36.webp', 'image/webp', 15276, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(63, 17, '81d8125f77f14b1bc3f4c6d130c981bf.webp', 'page 38.webp', 'image/webp', 51026, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(64, 17, '0c576d285654a939bcdd486e1bbd0e96.webp', 'page 39.webp', 'image/webp', 47242, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(65, 17, 'd48573142e436163d58cd74ea6436f11.webp', 'page 40.webp', 'image/webp', 23674, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(66, 17, '74d91d302e330adb5f23eec2220739ce.webp', 'page 41.webp', 'image/webp', 29398, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(67, 17, '4cb2ca00ed4a9ab038f1f90b2b0399fc.webp', 'page 42.webp', 'image/webp', 44724, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(68, 17, '2d13cf81c52ac29bdd69a69556c14566.webp', 'page 48.webp', 'image/webp', 17884, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(69, 17, '2ad33fca0f300a30139e4d8a5be868e5.webp', 'page 49.webp', 'image/webp', 34054, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(70, 17, '2047114d0aa55016cdd5336717491b46.webp', 'page 50.webp', 'image/webp', 41216, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(71, 17, '20be71d68bc1e611c343ff05c6a3fdbd.webp', 'page 51.webp', 'image/webp', 33864, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(72, 17, '5d5177462e258de95c5c1b4ac49c6dd6.webp', 'page 52.webp', 'image/webp', 36822, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(73, 17, '902017857f638caa5b8798d5b74a1185.webp', 'page 54.webp', 'image/webp', 16752, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(74, 17, 'fa81152e45630e758b1c3fd1164d6172.webp', 'page 55.webp', 'image/webp', 29456, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(75, 17, 'bca2aa90340f09f3c92359933e1204ba.webp', 'page 56.webp', 'image/webp', 48644, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(76, 17, '9292bfda85b81d17226aae6d1be86a34.webp', 'page 57.webp', 'image/webp', 29012, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(77, 17, 'd49a5d0a814e01c10c56bc895bb8509f.webp', 'page 60.webp', 'image/webp', 17050, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(78, 17, '9844e0f42b9fba21d1a5853afddc5e61.webp', 'page 72.webp', 'image/webp', 16064, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(79, 17, '21db50f2d311e731f72405b6c8052578.webp', 'page 59.webp', 'image/webp', 28856, '2026-09-24 10:16:05', '2026-09-25 02:14:20'),
(80, 17, 'bd69459c98e2eac8e0d41cc4d51245d3.webp', 'page 61.webp', 'image/webp', 13758, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(81, 17, 'b9608e05ad1cd2c7257ae4f93bad9263.webp', 'page 69.webp', 'image/webp', 15904, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(82, 17, '63be4ff141378047e3b881d256a9c254.webp', 'page 62.webp', 'image/webp', 30252, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(83, 17, '42f7c1d17293552194f1a456748ea9b6.webp', 'page 63.webp', 'image/webp', 36598, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(84, 17, '1aac24aa8eefd7150fc35e649fc18527.webp', 'page 64.webp', 'image/webp', 32512, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(85, 17, '80804f970a6e9b4efaefe3317261e626.webp', 'page 65.webp', 'image/webp', 25478, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(86, 17, '46decb8e675a0a0cba86de6e4dc90e87.webp', 'page 66.webp', 'image/webp', 18450, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(87, 17, 'ca385763991b63341f7b20f58e3aab07.webp', 'page 67.webp', 'image/webp', 16066, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(88, 17, 'dde4a252679c2b04d5838d52a9e5e1a7.webp', 'Overloaded Ute.webp', 'image/webp', 12288, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(89, 17, '4ec1cbec45caf6ece81a1b05d0c8ca44.webp', 'page 70.webp', 'image/webp', 6230, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(90, 17, '1f6252481c33ba8275ee53db3da14008.webp', 'page 71.webp', 'image/webp', 21176, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(91, 17, 'c92f213f020a8dade58e8184e057445c.webp', 'page 74.webp', 'image/webp', 42708, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(92, 17, 'b1f06f1f8a9be0aef942e60c9af6f940.webp', 'page 76.webp', 'image/webp', 3732, '2026-09-24 10:16:06', '2026-09-25 02:14:20'),
(96, NULL, '28a4404b5fac70727c9a6306d7ef0281.png', 'ChatGPT_Image_Sep_25__2026__01_57_33_AM__1_-removebg-preview.png', 'image/png', 14222, '2026-09-25 02:13:29', '2026-09-25 02:13:29'),
(97, NULL, '6625c21503edee3431c674709e4da1db.png', 'sfs-logo.png', 'image/png', 14234, '2026-09-25 12:31:08', '2026-09-25 12:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `media_settings`
--

CREATE TABLE `media_settings` (
  `id` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `max_file_size_mb` smallint UNSIGNED NOT NULL DEFAULT '5',
  `allowed_types` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'jpg,jpeg,png,gif,webp',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_preset` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teal',
  `theme_primary` char(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_accent` char(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `company_name`, `logo_url`, `primary_email`, `theme_preset`, `theme_primary`, `theme_accent`, `updated_at`) VALUES
(1, 'Stark Food Systems', '/assets/uploads/media-library/28a4404b5fac70727c9a6306d7ef0281.png', 'info@starkfoodsystems.com', 'custom', '#0f427e', '#c51e1a', '2026-09-25 17:18:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','inductee') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `profile_completed` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` datetime DEFAULT NULL,
  `email_verification_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `password_reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `status`, `profile_completed`, `email_verified_at`, `email_verification_token`, `email_verification_expires_at`, `password_reset_token`, `password_reset_expires_at`, `created_at`, `updated_at`) VALUES
(22, 'supabase@globalwebforce.com', '$2y$10$fAJNXpbxw6TSm3qzR2oewu9enI3fUYyoPJeiSarVfDBLBzW.9aMxC', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(23, 'sfsadmin@example.com', '$2y$10$zslC6e2MInlGJSpVda81puroqljGcGaVHvG5YpIKsGKswmec2RJ.i', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(24, 'haroldb@globalwebforce.com', '$2y$10$grS4gwsv4No8FVgZZUXnye4DPg8I6S/yIOzuNJDcWPoq6NsHnZ1SW', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(25, 'angelas@globalwebforce.com', '$2y$10$p9cYJkTqA1giN6D2bskiVeIS1aMGZUB1FwrQ3vkgoTZoIEJAWlmau', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(26, 'hitesh.parekh@globalwebforce.com', '$2y$10$9bUCRbhY05hqjg8PYKYIyudBEsy7J8XU5JS1MSWhWclzhCeH8a6zC', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(27, 'angelas@ezyoutsourcinghub.com', '$2y$10$ynOwNhHqyvKYlqnekORKrO3uRh0sf75fU95hwG2yezkGPFVwxjuHG', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(28, 'carlpc@globalwebforce.com', '$2y$10$f2LhgPCWDgZ1O1xlYG.iHeHFS8pnNbx3YL5hDO6lRAfylzvjAHyRS', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(29, 'mian.ohs@starkfoodsystems.com', '$2y$10$D8mRyp0DBxtqN7vA1lpwL.9aAk3VvecHzThdwMG44iclxk2Ugcd5O', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(30, 'abhimanyu27@live.com', '$2y$10$c/NVx1Z7Jb6GtrVbwrVXxeQ2QSdg2Dwcep/.AQYM/F3vs/rzPpqry', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(31, 'nathan.t.8180@gmail.com', '$2y$10$IWAPhUU774zum5UiiCfNDu0.0vP0DSWxsl4blKhbQwNCCPaPT2vXa', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(32, 'nathan.t.8190@gmail.com', '$2y$10$eQES59jZD5QSs.EcxYOQc.6ov6fWQoB1UroZYim9kxW0oND0k79B2', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(33, 'asknaeemtufail@gmail.com', '$2y$10$2rNTDcxE/m43hdFLFy1fCeQheRnU/Q2G0CQLnmQubzucR17K7vLw2', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(34, 'asoterales@gmail.com', '$2y$10$jxVdpeiiGVbezCUJibrm3OMh63eBpyduX.JlpiUuGIFz10Non3ohC', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(35, '9094pajay@gmail.com', '$2y$10$m0huQTFOzj1Yo4bbgFoWzOvp5rN529ujgbvA2MjQi4Hbnh0GY1LPC', 'inductee', 'active', 1, '2026-09-23 23:33:00', NULL, NULL, NULL, NULL, '2026-09-23 23:33:00', '2026-09-23 23:33:00'),
(36, 'info@starkfoodsystems.com', '$2y$10$Ja34pM6UezxIYEmCs8IeMeb4ukxjpB1VC9BRZZrD.7PM5/Y5xuaAy', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(37, 'jackmpreedy@gmail.com', '$2y$10$OuaJ3DOXHbZAaAt5usbP8enPsYqGbJoWXB6fRuPVwaj4LQFPMGdsO', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(38, 'wajidmasood05@gmail.com', '$2y$10$ofeQQz9QV2Ho3VOik3uBLOJUE8C42LKhKvkq8kYiwiDZEB.YE4eJe', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(39, 'shyamawijayathunga@outlook.com', '$2y$10$MyKjb9KZL1je1cCfQMPiqu.1hwsmvaOe35oo/swRzv0.4h3KN0C7e', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(40, 'shaynerevell99@gmail.com', '$2y$10$8FfW8bFRHDalFQRtTFzWnOt.Qzggh4S9UA4KvmHMw.GsnY8CffLTq', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(41, 'sharn@starkfoodsystems.com', '$2y$10$5maIQgMnLJ8H9.yBgrv9H.EbaPGGHtwuU1rLcDpXdIOWSmtmoY3sy', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(42, 'ronilraju37@gmail.com', '$2y$10$D7/pMk7FYHcDpKOIe7Gd2OPhRpgvB7FDhYxbAhNrq3ISxqiR4uOfO', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(43, 'afroz.mohammed7890@gmail.com', '$2y$10$wJZk2/MfugcPkSbl6uEsw.OeL0PwWFubW8UdupNcgKZEPNU5VgUcu', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(44, 'akshaysandhu.jag@gmail.com', '$2y$10$f0evhqPjGncbYz.uxdMg8ui2PghOAHWDxE34hxxWSb1TioSOj1sIi', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(45, 'arslanmasood177@gmail.com', '$2y$10$gxNFRaZsMcxhyUKTjG5Px.jahIEX7FZKHZo5bCL6tgBOEXmCZjWpi', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(46, 'rodrickr069@gmail.com', '$2y$10$WLQH.0R3RKsMDp./QpCsR.QYWYXRWhbAdV/gySdeiZ6j.GFa3zd0e', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(47, 'mahennair26@gmail.com', '$2y$10$gF2oluojmZ9tFkg1th8jn.3X4ObpTGZ6xkxJ9237xITEVdYKoieQ2', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(48, 'ahmedfarooqui8315@gmail.com', '$2y$10$X.HgZsshzErKWDh0J5tXLOi0teiUjJjNBG4fARTt8uK1OL6Vf2yim', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(49, 'jayshneelsivan21@icloud.com', '$2y$10$1q6.wJXD/IUM0A89avUV9ewVtiP7HNU0zM6/jYuwVwrCZQU8Uy/76', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(50, 'hemansharma26@gmail.com', '$2y$10$VOZJGqf5JHQjqvUA4mJnBudZIaMcgeAtXWQIm1TilKHIETaxDKdN6', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(51, 'luizfelipe.peixotos@gmail.com', '$2y$10$QfSrtIM1ht5.KwUt9pnLteyXGFHp31yVbzy63/hdVmBkkvsXM0FZe', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(52, 'aswindranand68@gmail.com', '$2y$10$d6ws5G118or2iCrJNILsxupkYie/NUim9Xgrd8gIZZ2e0/EQGlz1u', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(53, 'ilaitiasotia@gmail.com', '$2y$10$h6NasinNtKxdFo.GbhpNjOsyZ0F5.pDwnySlFLwrXhOGyMJenc3Zm', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(54, 'jiteshsharmatafe20@gmail.com', '$2y$10$ZzoAXQOtDR7uRbiuwuZnZeDg.d9DUZZdFxGJWKNeE5kKLL4FoBNTi', 'inductee', 'active', 1, '2026-09-23 23:33:01', NULL, NULL, NULL, NULL, '2026-09-23 23:33:01', '2026-09-23 23:33:01'),
(55, 'hitesh@starkfoodsystems.com', '$2y$10$rhigoGLIVXTp2N7TyqdMceylXPdYHyiavt4CFD5eY5llA6QpVz1Za', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(56, 'harsheel@starkfoodsystems.com', '$2y$10$AhQiut/SuhK6gh07blwgSeInYpyclSgfpyOM306o075UWgUZvO17m', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(57, 'andrewrin@rinengineeringau.com', '$2y$10$2yDtyGpEEErOHfnGxk7AZu7mSYzn7BkjEaIKQETjp9vt/LpmOGvI6', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(58, 'fahadbinsaddique606@gmail.com', '$2y$10$FAGPn27xGWtZvywKe5s1a.3yokcRVOO9.BQhIki2JKiZ6qEdNMqMO', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(59, 'shivakhatkar@gmail.com', '$2y$10$Xv.OI52n2z73t4j.Cb/c2ewalSdRUSrcxlUEto1lr8fwIF0QbX15y', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(60, 'hsteiner000@gmail.com', '$2y$10$afD37lEoJNiZNYVE2iEndu29WfB/dzRLVdvmjVu5y88lb/lQPGMfC', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(61, 'kamleshpjoshi@hotmail.com', '$2y$10$LB/LL5dKO8KCSY/cij.6aOUp2jJQEcuU3sHzDrTNV0S7shCWqZ55m', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(62, 'diane.delia88@gmail.com', '$2y$10$gVIIKdNXI7mrU//MsKUH6u7GIBSq9qqhxRw3O604D1bB9dj8Zfq2y', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(63, 'tyax.grp@gmail.com', '$2y$10$py/AFMsOywxmzVnY.1/0L.J4HNyUwgx79prIX4dERLkAkS.4OTQVq', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(64, 'bulingitmarkdionnie@gmail.com', '$2y$10$fEryxYvxgA5c2uuvDA/U4.8TtmDIsTap.3kV.gArnaZtH6RLO2u.u', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(65, 'udays808182@gmail.com', '$2y$10$NOEt0iSHhC5obDw5KzQINepXYKRo8Zqf669VcezLTdV7kt7v6b8sK', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(66, 'pateltejas096@gmail.com', '$2y$10$QvMRzdZgbPRekS4k/EKUy.8jrvetjuS5namPaM0Yfkv40.Jfgtyp6', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(67, 'henry.ford@starkfoodsystems.com', '$2y$10$2Q5hB4EcpArc4QiXsegNFOccIuRbuZ1LdtDW1y3ExxsTf.ceKDFDW', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(68, 'ravi.6072@gmail.com', '$2y$10$5YpQtl1O4MO4S2byqpjN..GtsSTIH9CDXC/1USBZMQAR3DYOSB/pe', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(69, 'mamoonrauf8@gmail.com', '$2y$10$EwTUNYgUFfMRFQWVbYLL3.aMjTvD.6K3.1x7PAnJgtZtj671rmoY2', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(70, 'keno.neve@gmail.com', '$2y$10$c0IFFBewqlbnBzygfj5Nb.6q4QJOt8BTTCkUQzlit0GIwakXChrVi', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(71, 'corey_crole@hotmail.com', '$2y$10$G0L/2qt2yLElpFZkOL8hF.3iEKtBgZTUjJVSelYBTd.hzgukKgaiy', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(72, 'dale_virgo@hotmail.com', '$2y$10$GXL8ItIroi2qytdGD4Lkc.I4umaa1cmK4Ri4wK66g75DvAWitF8GC', 'inductee', 'active', 1, '2026-09-23 23:33:02', NULL, NULL, NULL, NULL, '2026-09-23 23:33:02', '2026-09-23 23:33:02'),
(73, 'ateshraj03@gmail.com', '$2y$10$FufpWxK04KyX6Q/UUG2WdeTU3aVviS1Lsp2/dGcOMCYrfj4VcIWta', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(74, 'kyleloveday40@gmail.com', '$2y$10$UVW2F7umqRT7cQ2HVxUev.c6ZUl.WZnkCNA9upQ/lh4uq0LgfrtnW', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(75, 'hggh@gmai.com', '$2y$10$h5Q/aNpvQv3e2K7.cFKsv.OA2racHI3ksTnWrS5c0amDuBfaPey/K', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(76, 'trina@starkfoodsystems.com', '$2y$10$9/kGiacMhiufByqnDQMm1uiGl8QYTO6R/562rt9DRILiOiHr1wPkW', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(77, 'wozstorm27@gmail.com', '$2y$10$OHAGuiqqelYYdTSRxGXUY.9TpLKRdNeGT5yAmXDTwSN2xU5hfqxjy', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(78, 'eravaga97@gmail.com', '$2y$10$WI/Xku5XYSrmGi8gliDcz.DtUdhDapQW4xT08viU6i1F3alUgu7S2', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(79, 'raffay121@gmail.com', '$2y$10$mQdV2ZIpQkvR2UMSPni4le/vkpeJzPTHNNLbxyHK8yQ2nlcZbrLBu', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(80, 'simran@starkfoodsystems.com', '$2y$10$UzQIkHDGWStgLmGBAzdBE.qZfptuFRzjQtTUqJvCkk2FeUx.HRWQq', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(81, 'jameschaudhary@yahoo.com', '$2y$10$DD5Zsl9pJZIG2zeI.8bGBuOPGX3vlZQMNr4tRpHbOPOP3oY6Fa2wu', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(82, 'patelchirag8799@gmail.com', '$2y$10$y0w3Xaby2V2hrZiPB/V68uox78zCL5skpNDADunHkixeIiWan3A9e', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(83, 'srmech9099@gmail.com', '$2y$10$6kUiucWwkcllAUtWu/HpD.ev1mP.upb/FyrdYHNdehw/dI9Gv7Tqa', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(84, 'shrivastavrg@gmail.com', '$2y$10$ZVsIot/Y6QpMt1YzO1x0c.ZcQyafgWueyf1FPY18GLUOZ9aPGGtoS', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(85, 'sunnynaidur34@gmail.com', '$2y$10$S3ATlO.J1iwXVQNmZF/OoOce9rKj5Qd.q7ZjiX3sVnABilhci9YMO', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(86, 'adrianazzopardi02@gmail.com', '$2y$10$V8p8OT8xHXqlv.JegAe66.ALIuZFMu.z2AlCKpxQO2KJ97uVaegIi', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(87, 'kchahal295@gmail.com', '$2y$10$4WyN6xmvW73XX1hEF/ppqeEHYJt.s4VFkDo5Kii/t13360KgWTMN6', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(88, 'sunny_kumar21@hotmail.com', '$2y$10$23qrjFEyEOy6zc5npt4FO.x8Vs2p.pvemylfCDYfbkMkEJvphyr.K', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(89, 'nitinkumaran21@gmail.com', '$2y$10$J4o70CCcw8oJaVAVU3STzew1atGJ8jWDUKu3nsvnq2dXkRn0z1RqG', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(90, 'syedibrahimali28@gmail.com', '$2y$10$PaD.9X9B/UWpET160vhGJ.uXUZ7Cyu8qDaDMFqEHDFYfz0FK6SCcC', 'inductee', 'active', 1, '2026-09-23 23:33:03', NULL, NULL, NULL, NULL, '2026-09-23 23:33:03', '2026-09-23 23:33:03'),
(91, 'princekwakwa@gmail.com', '$2y$10$EbbN7H1443lW7FAAXcwe/uMddXMjbYy28CUHaFun3znKVyk9EAT7q', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(92, 'jack@jsmelectrical.com.au', '$2y$10$PbeoeoLeM72dEYLB9bLeFuuC/9KCu/5q48SL4csGz36qv8wTmvI7G', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(93, 'jessencooper@gmail.com', '$2y$10$32N09WXLuwU8g7paZirrIeT682XsCfu9labsMlnEVLT09rj6eH5aS', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(94, 'niteshkumar3132001@gmail.com', '$2y$10$9AlRUSpw2JjKHZimnY90n.goU4imAmB2sLcEHMmu9WZur5pelpXm2', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(95, 'asadhuss@hotmail.com', '$2y$10$1tlb64u4vzdWE.modvoQwOTyZjtatcbn6522n6/qHdZynXPiB9SPu', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(96, 'tester@yahoo.com', '$2y$10$JLx59mEKJ02Dpe4JPAb.ee.xhPB4PPO4xnj3JMCkmyEHZtHsHb1HS', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(97, 'durgaprasad.dp95@gmail.com', '$2y$10$CmdofLlYzUAUcSG0GmFJnOLW7gj/7XPfqzZ8Ejg1JRcKPr3MiJoSO', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(98, 'chauhanmahendrasinh555@gmail.com', '$2y$10$fdygwIyn6e8EPtkYNC4wju/5Kk1S7wHR.KE8wNyOwR4L4/33xjOb.', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(99, 'bapu251297@gmail.com', '$2y$10$sRxZozZCle1fyNaMO7xRQuJLdUkak6nu5Nqpek5FldrI.GrGj/8jS', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(100, 'mahendrasinhchauhan591@gmail.com', '$2y$10$lysbKeL9codlAYWdtr2lkOSRmxv6X4al0hpM9/4Oc3Wfc1NjI1mkO', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(101, 'billytravers02@gmail.com', '$2y$10$X4EWTJIz6gyyDY41nL4fBOQHmAhykMQCdZiH.jsFsts4nCjDDlG.K', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(102, 'b18bennett@gmail.com', '$2y$10$UidOHT9lr7jRQgbD8j3PguXJMVs.BEb5Zm8j1/FcMeq1US5ootsBK', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(103, 'kieran95mcnally@gmail.com', '$2y$10$xg8kkfo/RhxP0QrGPSGp9uz0DY2rbDRwvEtUFbu3Vwb6NVOuTuRam', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(104, 'lleytonwoolley14@gmail.com', '$2y$10$ZPNFZmmcnb54o3VWcf4gOenOUXBO.XWZ.Yf2e6VNFtrQWrhQDLmP.', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(105, 'ejazdanish923@gmail.com', '$2y$10$cTBYJi8a98qYmiMbzlCg7.xy6Pxn3qFoG1Mt8/zlBF2CbBcygboWW', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(106, 'psachin2506@gmail.com', '$2y$10$Li5bMykzP68xnoejRqFha.flK8dw9FMqBzN5sutRv6MWSi56s4kjC', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(107, 'mazohaibahmed@gmail.com', '$2y$10$hj7lhd.AvgeowJbDuz0qoOl718jLx.42yAZgrlEHvPIAKIOp.6wci', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(108, 'pdmalseed@gmail.com', '$2y$10$aPkEu23yBECZSDLUhf7kPub4r3BT9SZ2gU8NT.IJTi4l0HjA6GcIa', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(109, 'alecjshaw@bigpond.com.au', '$2y$10$3BbJ03r4pp43YrB7xTKzbu.hi.HUn1qnkNC/Rf/crvcxM95epHDjK', 'inductee', 'active', 1, '2026-09-23 23:33:04', NULL, NULL, NULL, NULL, '2026-09-23 23:33:04', '2026-09-23 23:33:04'),
(111, 'chanhtrinh121199@gmail.com', '$2y$10$2B8en2RFY0speZvi7422RODNHpMYUQ80X4.7DNJYL9zZ5cQk8piCW', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(112, 'ravneelprasad3@gmail.com', '$2y$10$K9s3q2aa8vKIWy9GQ.h0P.ayeTCVxWsOnlAPzxklihDORxY4k9gmW', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(113, 'dhiman421@yahoo.in', '$2y$10$mm3Av.LaNHLgOQaaKMRN7OnqhYMsAwH8hEW0XCjVTZKoA3a2dnENS', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(114, 'lavisharma061996@gmail.com', '$2y$10$XTSUh82hoZSmalID9Mvlpu1NFrfAdf9.qyZu0V2nIxqCaCMoXCEGS', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(115, 'patelyogeshp81@gmail.com', '$2y$10$vbzqTX0STaGn5RN/EMvCH.VtyI3VeDmFDEjEJO4JcQN9p8T76A.yW', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(116, 'hamish.teichert02@gmail.com', '$2y$10$jj9IL1ZtBdZ.6Wayb/H31OVqoO2T2RieV6ARVuCxUw186IpzrC/my', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(117, 'sahibbsidhuusidhu@icloud.com', '$2y$10$b.Ggy9Sd41yUtf0Q0i.f.eyDVngYx8XbwtjyUrARBxpCc5Y8aT4uy', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(118, 'aboshark75@gmail.com', '$2y$10$9WcTCC./mSUD1lViFTsC5u8PSu.UrteQ93Sf9jBDKFWqU3sCKFK..', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(119, 'prasadsanjit660@gmail.com', '$2y$10$H8gLVMcjEJ8QXoK3.j/48OY51.x2nJo4WItPFC62.iYJYfiGwqDW2', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(120, 'ace.lecc1@gmail.com', '$2y$10$mtIrb1VJ87Mk/SYQgPlWI.loa0M6tzUtJJdyv5oCbO3ssWrMw/2ie', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(121, 'michaelsngh99@gmail.com', '$2y$10$RGxAae1mQnw4m1BAL01V1O5oTF9iGJKtpjRdBvF7fSO9uUANVF3dK', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(122, 'nicholasvincent00@gmail.com', '$2y$10$Ks8ilIrrAbCvoKv4XjUO6ez0IO0z4lsf4i0hdPf9aNrLTzKUc6mRW', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(123, 'sabiteshpal@gmail.com', '$2y$10$BkUvfv1uDutwScu.AFRIy.uGD7qbeUqqbSzux7FltkdCsGmhZM1tm', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(124, 'sami.arnav1265@yahoo.com', '$2y$10$Vgn5dl7dG2.XSU6hwClG6eWOQjuiaGvAln809U1Pv8HAfTzWf4LQi', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(125, 'ragireddyrahulreddy9@gmail.com', '$2y$10$C1ODiO.bmZ3bHmn52F3Sme9nT6ruKEeGQNYU5ZWh34BX5JIZMt23G', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(126, 'bradleycooper01@hotmail.com', '$2y$10$ROfJXZcefWhaP9qfPY2lAelzWl6nOzrpG4YFasJBbbH.PoEwfyCOq', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(127, 't.ngawaka92@gmail.com', '$2y$10$U06BN/T8FY/sgkdEAeiD/OKYAewNS6MQCGLH47/aq9lVcUfYZ62di', 'inductee', 'active', 1, '2026-09-23 23:33:05', NULL, NULL, NULL, NULL, '2026-09-23 23:33:05', '2026-09-23 23:33:05'),
(128, 'patel.rp786@gmail.com', '$2y$10$mWujtyf2GZs1WPm9lG9AoeqGkkhXMm.2iK2DEFvn5G2KJFgh27Cw6', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(129, 'patilsahil2397@gmail.com', '$2y$10$CjAptzjy0gyF9Db.AP9N2O2BNXe.TiCdYQ.rrvV4M11XZyXS3ZGuu', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(130, 'nikbosevskiwork@gmail.com', '$2y$10$vaVLAINT7/KuBOhzy46KiOoJHIGwPINclE6ZoXbK5P4rbVJarGybO', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(131, 'harawalehsan3@gmail.com', '$2y$10$7KswL8HHO3zHg4ZUb6wwVu43aFAu1veulSR9Ls.Wp3r6TjzKrlt2a', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(132, 'satwant50.singh@gmail.com', '$2y$10$hR2aUxAAu9tkhRwLXxTihetghmQyfb6.B/UG.9jQRrRol1U7BB4ku', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(133, 'miraclemanuele07@gmail.com', '$2y$10$XMrhIkx7lPg733GE.Q4Mo.dVlbE5z1iOLioGsiBdhRMz8VTEU4tpy', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(134, 'israelbraga9090@gmail.com', '$2y$10$KIUYlFOTPfKQrActn0Zswe3Mlw3zcosy39vUCgdgNhtAv2OTN/5/y', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(135, 'sr0573601@gmail.com', '$2y$10$1Ul1V6i/qgC9m82opKonrOo8RL/SR.xNZx12rHyJOijHmGWtTL/Em', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(136, 'nathan@priestlyengineering.com.au', '$2y$10$4UzzfiLRvtYxAeKELWAsw.AhrW/djT3KSIxrlwn14KFtLF3Yj.VOy', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(137, 'brodie.muscat1@gmail.com', '$2y$10$Es4F6SoyzinnJ.MJ/PsTeuSdaFhBH3fsUJIRCWZVJgRCFNF1lCWT2', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(138, 'leschinkohlleah@gmail.com', '$2y$10$bztRjbhi/VtQW7A/uL6lUOVw7yil6rUTQ/rHYpYDoa.nbBWfFHAkS', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(139, 'paulako66@gmail.com', '$2y$10$FHVit.sKpI9fEdXkzHbau.JKZptfd0DV/gsOAx8xCtt48Zr/g.mLy', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(140, 'liam62722@outlook.com', '$2y$10$t/BDv/IU4eMbyfoHyJBoIeawTnFbWekoJPm/vYZMVjScMqPUYMNjO', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(141, 'zcslowe@yahoo.com.au', '$2y$10$mRFnY7sdNWZj9EITD6NnaOnWBOi1PGdfmMbL7/SF7FFU.i77.Xoia', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(142, 'aldo.fmendoza@hotmail.com', '$2y$10$.9XrITR6QGqh.tunkAFXAedt3d0DTcYo99LeoTUngxzhZTngS2GEm', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(143, 'zebtallon565@gmail.com', '$2y$10$T0bDZy6BRdS2SSMmiV/Z8.OVtpWri5UgjhM6ZwXPXI1cRwhONeQna', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(144, 'harbhajansingh21515@gmail.com', '$2y$10$3//UzSt58u.H1geI5sBHJOLqh35DmqjNQj3XCoeqZDruTKQi/70Pm', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(145, 'harbhajansingh_71@yahoo.com.au', '$2y$10$HUOiALmX.3G72by6XlFZKOfu01FnaVOFHYtYtbUahj.bmLx18xeGe', 'inductee', 'active', 1, '2026-09-23 23:33:06', NULL, NULL, NULL, NULL, '2026-09-23 23:33:06', '2026-09-23 23:33:06'),
(146, 'dlwhitbread.88@gmail.com', '$2y$10$L1Sx5HqgWyGp/zCw7Cycae54ODPHzAoA4vHuSSN7G/sbZO.bV9l7W', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(147, 'manojpatel.4889@gmail.com', '$2y$10$Pnlb7.p6HjE6drEYAmEBzuiW3ITz5MFf6tJS2TDclII1U9RcSzr3.', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(148, 'deepp4851@gmail.com', '$2y$10$qlg.4ZOfZUU.xYwSMuAGneTTLQpP7monkAeV1ETKeVtvDvSyf7Pgq', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(149, 'vinodkp.mechanical@gmail.com', '$2y$10$mM6vDIsNMf35WHGRAAEV5u41kpYi3JX/uHxc81ZYT7RKk94K1VltK', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(150, 'stummala811@gmail.com', '$2y$10$630oWSPwawOsc9B6K5jZyOjL6fJ5boODINNZiqLfqqT0h/jrwm1R2', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(151, 'dylanhunt94@hotmail.com', '$2y$10$jnGlxQlRr8tWL0KJDaLGoefrmyG3cg85SL0tvOY9Re8Njj5j/zBC.', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(152, 'mittal.maulik019@gmail.com', '$2y$10$2WScRGg.y9G/9ELM7E6Hq.QD.iY6LnkwYQWi2pFciZnDhNmaoV5US', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(153, 'er.pateldakshesh14895@gmail.com', '$2y$10$UJvaqPPpgcxXQ01MKP3A5eQZYJHzZrfBzeXyu24aNBJkzo91sV4.W', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(154, 'ronakpatel12325@yahoo.com', '$2y$10$EN1s5Lvam./UIi0c9UUoVeIU62yBNbr66NYhJBkdfxkgd9S5OJFE6', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(155, 'xander0426@yahoo.com', '$2y$10$Du1wztOsD3rtbsYBwIhbBuoVMFi5DtC8C/ubPSzmCBope0w4hTh6q', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(156, 'rshaikh.005@gmail.com', '$2y$10$keQGTKtHC.JOZDcu8/xCW.YfV7a96s.vq3VfZP/98R1goqFnEwzHy', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(157, 'engmohammedzia@gmail.com', '$2y$10$Of2.xMcLNjeFSKnDWIxvOO/hNF5sBayMXWueMBQQ9omS5eeH3EECC', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(158, 'rahulptl10296@gmail.com', '$2y$10$xr8mHhJzO02DZ6iEuanz3uVYJdlYb4XMKjtqJVsN93Ne.CQlAVo4e', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(159, 'amitbhadu1666@gmail.com', '$2y$10$tglLD72CChZ6BiLL7iZiDeROM.JlQQ7pGzZ6E9tGYuNC3Fd6VpLPm', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(160, 'kartiksharma292002@gmail.com', '$2y$10$/jaoV.HHkQxAy07cb6xLq.e5cEUcdxE4Si2WagJE/4QXcRJp7fRVi', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(161, 'shanefreegrove@hotmail.com', '$2y$10$c7edUXk2BDp2du6hSANBnezeY3rLtx.3fy/GLhk2Xq9ghH2o5MFjK', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(162, 'ravneelasre@gmail.com', '$2y$10$w6IpXVqcJIEawn69kZrbQeizwTYFO.AViLBeIAdtWxszOLQtEya3C', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(163, 'peter@barclaygroupconsulting.com', '$2y$10$cXcDBfQoDcp51.IPSWNuee3878aNF98Ty1P.4DbyhtSSwUBlze2Xm', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(164, 'bpanganayi41@gmail.com', '$2y$10$DxDER60x/IZ.Er2H6ugoYOSIPgGmrd7BmgrsbQjVV2F.IQwq/ZPWW', 'inductee', 'active', 1, '2026-09-23 23:33:07', NULL, NULL, NULL, NULL, '2026-09-23 23:33:07', '2026-09-23 23:33:07'),
(165, 'harrypro18@gmail.com', '$2y$10$OtITcKiev3LgfzqMOIFRFO/iU.SG0no7QUl2hAaO2ANrJd45OZuke', 'inductee', 'active', 1, '2026-09-23 23:33:08', NULL, NULL, NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(166, 'btw4201995@gmail.com', '$2y$10$stAnSvj9r4FWYouIwRyLIOGrLJqFKFhZtqZyyfNxijlpCaMlNddla', 'inductee', 'active', 1, '2026-09-23 23:33:08', NULL, NULL, NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(167, 'jimmyford1418@gmail.com', '$2y$10$UjhbJjtlkFgzbi2yXQgy.erkXniQbvDkJN.8w9lTpUPERuVfunP82', 'inductee', 'active', 1, '2026-09-23 23:33:08', NULL, NULL, NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(168, 'mantejsingh00018@gmail.com', '$2y$10$jBH3JRzljBQ5ceMbBMDZB.lGMO727o8MkiJQM7bN.dWwzYUEF6KhS', 'inductee', 'active', 1, '2026-09-23 23:33:08', NULL, NULL, NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(169, 'jbarclay639@gmail.com', '$2y$10$w4GPoPJcwYh51zg3JygDb.ec6mhSmmfwDxIa1NzV0pH02S0udIXhm', 'inductee', 'active', 1, '2026-09-23 23:33:08', NULL, NULL, NULL, NULL, '2026-09-23 23:33:08', '2026-09-23 23:33:08'),
(210, 'markb@globalwebforce.com', '$2y$10$kuF6r2i9BeBCAStFXtxYt.O3hyaYbH9SVnOfUg/MmE5ujA16Djai.', 'admin', 'active', 1, '2026-09-25 16:40:59', NULL, NULL, NULL, NULL, '2026-09-25 16:40:59', '2026-09-25 16:40:59'),
(213, 'dionnie_bulingit@yahoo.com', '$2y$10$/LIi6ixqWtxe55vKYm.TheKL.UxEFwIk8xz6Rs6bjBHwpiPwt9Moe', 'inductee', 'active', 1, '2026-09-25 19:24:06', NULL, NULL, NULL, NULL, '2026-09-25 18:02:08', '2026-09-25 19:24:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `compliance_records`
--
ALTER TABLE `compliance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_compliance_verification_token` (`verification_token`),
  ADD UNIQUE KEY `uq_compliance_certificate_number` (`certificate_number`),
  ADD UNIQUE KEY `uq_compliance_legacy_id` (`legacy_id`),
  ADD KEY `idx_compliance_user_induction` (`user_id`,`induction_id`),
  ADD KEY `fk_compliance_induction` (`induction_id`),
  ADD KEY `fk_compliance_exam_attempt` (`exam_attempt_id`),
  ADD KEY `fk_compliance_renewed_from` (`renewed_from_id`);

--
-- Indexes for table `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam_attempts_user` (`user_id`),
  ADD KEY `idx_exam_attempts_induction` (`induction_id`),
  ADD KEY `fk_exam_attempts_exam` (`exam_id`);

--
-- Indexes for table `inductee_profiles`
--
ALTER TABLE `inductee_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `inductions`
--
ALTER TABLE `inductions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inductions_code` (`code`),
  ADD KEY `fk_inductions_exam` (`exam_id`);

--
-- Indexes for table `media_categories`
--
ALTER TABLE `media_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_media_categories_name` (`name`);

--
-- Indexes for table `media_items`
--
ALTER TABLE `media_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_media_items_category` (`category_id`);

--
-- Indexes for table `media_settings`
--
ALTER TABLE `media_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_email_verification_token` (`email_verification_token`),
  ADD KEY `idx_users_password_reset_token` (`password_reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `compliance_records`
--
ALTER TABLE `compliance_records`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inductions`
--
ALTER TABLE `inductions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `media_categories`
--
ALTER TABLE `media_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `media_items`
--
ALTER TABLE `media_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD CONSTRAINT `fk_admin_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compliance_records`
--
ALTER TABLE `compliance_records`
  ADD CONSTRAINT `fk_compliance_exam_attempt` FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts` (`id`),
  ADD CONSTRAINT `fk_compliance_induction` FOREIGN KEY (`induction_id`) REFERENCES `inductions` (`id`),
  ADD CONSTRAINT `fk_compliance_renewed_from` FOREIGN KEY (`renewed_from_id`) REFERENCES `compliance_records` (`id`),
  ADD CONSTRAINT `fk_compliance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD CONSTRAINT `fk_exam_attempts_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  ADD CONSTRAINT `fk_exam_attempts_induction` FOREIGN KEY (`induction_id`) REFERENCES `inductions` (`id`),
  ADD CONSTRAINT `fk_exam_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inductee_profiles`
--
ALTER TABLE `inductee_profiles`
  ADD CONSTRAINT `fk_inductee_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inductions`
--
ALTER TABLE `inductions`
  ADD CONSTRAINT `fk_inductions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);

--
-- Constraints for table `media_items`
--
ALTER TABLE `media_items`
  ADD CONSTRAINT `fk_media_items_category` FOREIGN KEY (`category_id`) REFERENCES `media_categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
