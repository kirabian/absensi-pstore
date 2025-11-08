-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 08 Nov 2025 pada 03.37
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pstore_absensi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `check_in_time` timestamp NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scanned_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `verified_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `attendances`
--

INSERT INTO `attendances` (`id`, `user_id`, `check_in_time`, `status`, `photo_path`, `longitude`, `latitude`, `scanned_by_user_id`, `verified_by_user_id`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 189, '2025-10-25 17:47:44', 'pending_verification', NULL, NULL, NULL, NULL, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(2, 187, '2025-10-28 22:39:34', 'pending_verification', NULL, NULL, NULL, NULL, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(3, 186, '2025-10-19 06:15:02', 'pending_verification', NULL, NULL, NULL, NULL, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(4, 187, '2025-10-25 06:37:16', 'pending_verification', NULL, NULL, NULL, NULL, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(5, 191, '2025-10-12 06:19:10', 'pending_verification', NULL, NULL, NULL, NULL, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(6, 192, '2025-10-11 01:39:50', 'verified', 'https://via.placeholder.com/640x480.png/0011bb?text=people+et', NULL, NULL, 4, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(7, 190, '2025-10-14 12:45:29', 'verified', 'https://via.placeholder.com/640x480.png/00dd44?text=people+officiis', NULL, NULL, 97, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(8, 191, '2025-10-19 01:24:47', 'verified', 'https://via.placeholder.com/640x480.png/00ee66?text=people+non', NULL, NULL, 103, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(9, 189, '2025-10-31 18:20:28', 'verified', 'https://via.placeholder.com/640x480.png/0055aa?text=people+nisi', NULL, NULL, 28, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(10, 191, '2025-10-22 12:39:18', 'verified', 'https://via.placeholder.com/640x480.png/000033?text=people+nemo', NULL, NULL, 64, NULL, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_teams`
--

CREATE TABLE `audit_teams` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `division_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `audit_teams`
--

INSERT INTO `audit_teams` (`id`, `user_id`, `division_id`) VALUES
(1, 159, 12),
(2, 183, 184),
(3, 18, 169),
(4, 30, 244),
(5, 105, 206),
(6, 87, 160),
(7, 51, 257),
(8, 180, 38),
(9, 39, 299),
(10, 36, 233);

-- --------------------------------------------------------

--
-- Struktur dari tabel `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `created_at`, `updated_at`) VALUES
(1, 'PStore Evelineland', '8968 Hunter Causeway Apt. 032\nEast Edwin, NE 07177', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(2, 'PStore New Carliemouth', '52880 Ziemann Way\nNorth Artfurt, HI 69429', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(3, 'PStore Tristianhaven', '178 Rodriguez Mews\nSouth Ava, MO 12600', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(4, 'PStore Lorenport', '20314 Satterfield Shores Apt. 464\nNorth Brooklyn, DE 87075', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(5, 'PStore Jonatanfort', '242 Kreiger Drives Suite 995\nDickinsonberg, NV 37562-2305', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(6, 'PStore Lake Tarynland', '324 Casper Greens Apt. 533\nKelleyfurt, NV 40386', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(7, 'PStore Luemouth', '618 Daniel Islands Apt. 370\nLake Lindaberg, DE 69332', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(8, 'PStore East Adam', '9432 Dietrich Stravenue\nLake Pablo, HI 28242-9785', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(9, 'PStore Hillborough', '72595 German Station Apt. 371\nMarlonfurt, MI 10714-1693', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(10, 'PStore South Nikitamouth', '6613 Dan Creek Suite 378\nSouth Sydniborough, CO 51829', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(11, 'PStore North Johathan', '8467 Jalon Underpass\nNew Raoul, TX 74644', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(12, 'PStore Lake Jovan', '6868 Schumm Hill Apt. 374\nWest Thea, SC 98748', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(13, 'PStore New Pearlie', '2277 Icie Mountains Suite 271\nGermanside, VT 53036-3096', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(14, 'PStore Lake Camilla', '882 Abshire Vista Suite 362\nWest Aydenland, NY 56667', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(15, 'PStore New Calista', '91018 Magdalena Cove\nOlafmouth, OK 19833', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(16, 'PStore Port Sylvester', '668 Estel Mountain\nKochland, MD 44240', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(17, 'PStore Zemlakstad', '82743 Omer Walks\nDickensstad, NE 56990', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(18, 'PStore East Corrinehaven', '70818 Funk Bridge Suite 732\nSouth Ramona, NH 04263-3007', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(19, 'PStore West Myrtlefort', '349 Borer Fork\nYundtville, GA 59759', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(20, 'PStore South Carterberg', '573 Cole Creek\nReynoldshire, NY 13841', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(21, 'PStore Port Nathanielfort', '34694 Carolina Lodge Suite 528\nPort Mallie, LA 51922-9454', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(22, 'PStore West Sadyemouth', '523 Lind Greens Apt. 877\nNew Zachariahfurt, TN 34351-5200', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(23, 'PStore Aryannafort', '47106 Koelpin Hill\nWest Sethburgh, WV 18888', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(24, 'PStore Lelandside', '6643 Balistreri Light\nWintheiserview, MD 94983', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(25, 'PStore Heidenreichberg', '927 Wunsch Plaza\nNorth Leathatown, NM 10643', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(26, 'PStore West Jaclyn', '5934 Bashirian Mountains Apt. 251\nGenesisland, VT 62267', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(27, 'PStore Soledadbury', '779 Carolyn Lake\nPort Carlie, KS 18773', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(28, 'PStore Larsonfurt', '8866 Sienna Throughway Suite 859\nNorth Penelope, SD 37934', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(29, 'PStore East Kallie', '879 Hauck Courts Suite 645\nWest Bell, PA 57490-9051', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(30, 'PStore South Darrellberg', '6313 Elta Forks Suite 471\nPort Madelineville, VA 48084', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(31, 'PStore Leschmouth', '91871 Thaddeus Ports Suite 196\nEast Destanyview, CA 62790-4582', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(32, 'PStore Harveyville', '38507 Abshire Coves Apt. 966\nSouth Alek, KY 43094', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(33, 'PStore Lake Carolanne', '9831 Powlowski Cove Apt. 334\nLake Trace, TX 27348-2355', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(34, 'PStore Conroyport', '86325 Cristobal Path\nDietrichstad, MI 81255-9662', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(35, 'PStore Lake Jaylinton', '971 Ratke Mills\nSouth Kaylachester, VT 62633-4043', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(36, 'PStore North Rogeliochester', '452 Dooley Coves Suite 835\nClaireborough, NJ 00688', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(37, 'PStore Lake Rey', '81939 Leonard Tunnel Apt. 996\nNew Sherwood, IA 25844-8614', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(38, 'PStore Osinskimouth', '11230 Rolfson Square Suite 256\nPort Clairshire, NC 66753-4134', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(39, 'PStore Port Providenci', '710 Wehner Motorway\nLake Jed, NV 35688-5591', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(40, 'PStore Spencerview', '602 Dexter Lake Suite 199\nNorth Myrtice, MO 13681', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(41, 'PStore Haagborough', '28336 Kavon Shores Apt. 731\nDaphneside, VA 82396-6466', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(42, 'PStore Genesisfort', '17597 Domenico Valleys Suite 185\nAufderharbury, NM 41926-8297', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(43, 'PStore East Kenyon', '7051 Hilpert Hollow\nEast Emmett, OR 23411-8161', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(44, 'PStore New Daphne', '47550 Reynold Lodge Apt. 215\nKutchburgh, TN 40176-8871', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(45, 'PStore North Frederic', '357 Johnson Mission\nBaronfurt, SC 02180-2410', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(46, 'PStore New Brionna', '36718 McDermott Grove Suite 363\nSouth Lessie, MT 50442', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(47, 'PStore Macejkovicside', '991 Dolly Pine Apt. 244\nRaleighhaven, FL 95504-6101', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(48, 'PStore North Reidside', '4103 Mills Stream Apt. 349\nNorth Samantamouth, TX 26133-6612', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(49, 'PStore New Keaganburgh', '1639 Green Underpass\nWest Madisyn, WI 19431', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(50, 'PStore West Arch', '541 Mann Pass\nJasperburgh, OH 66708', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(51, 'PStore Kameronside', '52749 Rodriguez Corners\nKertzmannborough, NE 39003-9970', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(52, 'PStore East Laurianne', '576 Ruby Estate Apt. 159\nNorth Brisa, NJ 47101', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(53, 'PStore North Miraclemouth', '860 Beatty Mountain\nVonberg, MI 47588-6246', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(54, 'PStore Glovermouth', '56588 Antwon Parkways\nNew Fannie, MD 94435-7033', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(55, 'PStore East Fernandofort', '1717 Harvey Turnpike Suite 694\nNew Demario, CA 53570-8654', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(56, 'PStore Lake Wilfred', '717 Paula Forge Apt. 948\nReillyborough, IA 05241', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(57, 'PStore Lethachester', '18702 Reichel Harbors Suite 561\nRexmouth, RI 86552', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(58, 'PStore Ethanport', '9123 Jaiden Row\nNorth Waldo, KY 27176', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(59, 'PStore Binsside', '7284 DuBuque Coves Suite 226\nWest Kolbytown, TN 13538-8499', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(60, 'PStore Cliftonside', '826 Merlin Prairie\nSimonisburgh, CT 50912', '2025-11-07 00:53:05', '2025-11-07 00:53:05'),
(61, 'PStore East Finn', '70201 McKenzie Ridges Suite 648\nLake Jodie, MO 69778', '2025-11-07 00:53:05', '2025-11-07 00:53:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `divisions`
--

INSERT INTO `divisions` (`id`, `name`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'Tim Data Processing Equipment Repairer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 1),
(2, 'Tim Dental Assistant', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 1),
(3, 'Tim Public Transportation Inspector', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 1),
(4, 'Tim Vice President Of Marketing', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 1),
(5, 'Tim Millwright', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 1),
(6, 'Tim Shipping and Receiving Clerk', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 2),
(7, 'Tim Hairdresser OR Cosmetologist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 2),
(8, 'Tim Agricultural Manager', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 2),
(9, 'Tim Production Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 2),
(10, 'Tim Postal Service Mail Sorter', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 2),
(11, 'Tim Operating Engineer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 3),
(12, 'Tim Surveyor', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 3),
(13, 'Tim Court Reporter', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 3),
(14, 'Tim Materials Scientist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 3),
(15, 'Tim Plating Machine Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 3),
(16, 'Tim Data Entry Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 4),
(17, 'Tim Drilling and Boring Machine Tool Setter', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 4),
(18, 'Tim Order Clerk', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 4),
(19, 'Tim Orthodontist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 4),
(20, 'Tim Physicist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 4),
(21, 'Tim Accountant', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 5),
(22, 'Tim Woodworking Machine Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 5),
(23, 'Tim Extraction Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 5),
(24, 'Tim Interpreter OR Translator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 5),
(25, 'Tim Athletes and Sports Competitor', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 5),
(26, 'Tim General Practitioner', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 6),
(27, 'Tim Fish Hatchery Manager', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 6),
(28, 'Tim Plumber OR Pipefitter OR Steamfitter', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 6),
(29, 'Tim Streetcar Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 6),
(30, 'Tim Mining Engineer OR Geological Engineer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 6),
(31, 'Tim Transportation Inspector', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 7),
(32, 'Tim Gauger', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 7),
(33, 'Tim Railroad Yard Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 7),
(34, 'Tim Percussion Instrument Repairer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 7),
(35, 'Tim State', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 7),
(36, 'Tim Precision Instrument Repairer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 8),
(37, 'Tim Communication Equipment Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 8),
(38, 'Tim Precision Aircraft Systems Assemblers', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 8),
(39, 'Tim Automatic Teller Machine Servicer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 8),
(40, 'Tim Health Technologist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 8),
(41, 'Tim Media and Communication Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 9),
(42, 'Tim City', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 9),
(43, 'Tim Diesel Engine Specialist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 9),
(44, 'Tim Archivist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 9),
(45, 'Tim Prosthodontist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 9),
(46, 'Tim Answering Service', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 10),
(47, 'Tim Auditor', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 10),
(48, 'Tim Automotive Glass Installers', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 10),
(49, 'Tim Educational Counselor OR Vocationall Counselor', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 10),
(50, 'Tim Psychiatric Technician', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 10),
(51, 'Tim Mathematician', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 11),
(52, 'Tim CTO', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 11),
(53, 'Tim Hand Sewer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 11),
(54, 'Tim Extraction Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 11),
(55, 'Tim Movers', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 11),
(56, 'Tim Educational Counselor OR Vocationall Counselor', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 12),
(57, 'Tim Cooling and Freezing Equipment Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 12),
(58, 'Tim Mining Machine Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 12),
(59, 'Tim Petroleum Engineer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 12),
(60, 'Tim Immigration Inspector OR Customs Inspector', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 12),
(61, 'Tim Multi-Media Artist', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 13),
(62, 'Tim Graphic Designer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 13),
(63, 'Tim Logging Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 13),
(64, 'Tim Transportation Worker', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 13),
(65, 'Tim User Experience Researcher', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 13),
(66, 'Tim Transportation and Material-Moving', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 14),
(67, 'Tim Drywall Ceiling Tile Installer', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 14),
(68, 'Tim Drycleaning Machine Operator', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 14),
(69, 'Tim Furniture Finisher', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 14),
(70, 'Tim Coaches and Scout', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 14),
(71, 'Tim Jewelry Model OR Mold Makers', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 15),
(72, 'Tim Computer Systems Analyst', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 15),
(73, 'Tim Production Planner', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 15),
(74, 'Tim Rehabilitation Counselor', '2025-11-07 00:53:05', '2025-11-07 00:53:05', 15),
(75, 'Tim Counselor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 15),
(76, 'Tim Forming Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 16),
(77, 'Tim Talent Acquisition Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 16),
(78, 'Tim Industrial-Organizational Psychologist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 16),
(79, 'Tim Postal Service Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 16),
(80, 'Tim Home', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 16),
(81, 'Tim Electronic Drafter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 17),
(82, 'Tim Biochemist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 17),
(83, 'Tim Visual Designer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 17),
(84, 'Tim Forest and Conservation Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 17),
(85, 'Tim Spraying Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 17),
(86, 'Tim Metal Pourer and Caster', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 18),
(87, 'Tim Range Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 18),
(88, 'Tim Tax Examiner', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 18),
(89, 'Tim Eligibility Interviewer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 18),
(90, 'Tim Emergency Medical Technician and Paramedic', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 18),
(91, 'Tim Buyer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 19),
(92, 'Tim Pharmacy Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 19),
(93, 'Tim Radio Mechanic', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 19),
(94, 'Tim Credit Analyst', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 19),
(95, 'Tim Artillery Officer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 19),
(96, 'Tim Courier', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 20),
(97, 'Tim Transportation Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 20),
(98, 'Tim Electric Motor Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 20),
(99, 'Tim Weapons Specialists', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 20),
(100, 'Tim Commercial Pilot', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 20),
(101, 'Tim Aircraft Rigging Assembler', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 21),
(102, 'Tim Locksmith', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 21),
(103, 'Tim Shuttle Car Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 21),
(104, 'Tim Registered Nurse', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 21),
(105, 'Tim Boiler Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 21),
(106, 'Tim Soil Conservationist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 22),
(107, 'Tim Gas Pumping Station Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 22),
(108, 'Tim Fabric Mender', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 22),
(109, 'Tim Media and Communication Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 22),
(110, 'Tim Calibration Technician OR Instrumentation Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 22),
(111, 'Tim Freight and Material Mover', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 23),
(112, 'Tim Financial Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 23),
(113, 'Tim Administrative Services Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 23),
(114, 'Tim Graduate Teaching Assistant', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 23),
(115, 'Tim Credit Checkers Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 23),
(116, 'Tim Cooling and Freezing Equipment Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 24),
(117, 'Tim Railroad Inspector', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 24),
(118, 'Tim Alteration Tailor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 24),
(119, 'Tim Glass Blower', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 24),
(120, 'Tim Hydrologist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 24),
(121, 'Tim Production Helper', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 25),
(122, 'Tim Webmaster', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 25),
(123, 'Tim Material Moving Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 25),
(124, 'Tim Geological Sample Test Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 25),
(125, 'Tim Police Identification OR Records Officer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 25),
(126, 'Tim Storage Manager OR Distribution Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 26),
(127, 'Tim Retail Salesperson', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 26),
(128, 'Tim Law Enforcement Teacher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 26),
(129, 'Tim Marking Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 26),
(130, 'Tim Decorator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 26),
(131, 'Tim Psychiatric Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 27),
(132, 'Tim Bench Jeweler', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 27),
(133, 'Tim Sales and Related Workers', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 27),
(134, 'Tim Interior Designer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 27),
(135, 'Tim Production Control Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 27),
(136, 'Tim Fish Game Warden', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 28),
(137, 'Tim Potter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 28),
(138, 'Tim Secondary School Teacher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 28),
(139, 'Tim Motion Picture Projectionist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 28),
(140, 'Tim Gaming Dealer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 28),
(141, 'Tim Bartender Helper', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 29),
(142, 'Tim Home', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 29),
(143, 'Tim Aircraft Engine Specialist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 29),
(144, 'Tim Refractory Materials Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 29),
(145, 'Tim Transformer Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 29),
(146, 'Tim Coil Winders', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 30),
(147, 'Tim Rental Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 30),
(148, 'Tim Gas Pumping Station Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 30),
(149, 'Tim Fence Erector', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 30),
(150, 'Tim Payroll Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 30),
(151, 'Tim Benefits Specialist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 31),
(152, 'Tim Library Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 31),
(153, 'Tim Furniture Finisher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 31),
(154, 'Tim Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 31),
(155, 'Tim Council', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 31),
(156, 'Tim Motion Picture Projectionist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 32),
(157, 'Tim Streetcar Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 32),
(158, 'Tim Pharmacy Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 32),
(159, 'Tim Technical Writer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 32),
(160, 'Tim Animal Trainer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 32),
(161, 'Tim Medical Assistant', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 33),
(162, 'Tim Ship Carpenter and Joiner', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 33),
(163, 'Tim Mechanical Door Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 33),
(164, 'Tim Sculptor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 33),
(165, 'Tim Gaming Service Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 33),
(166, 'Tim Receptionist and Information Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 34),
(167, 'Tim Computer Systems Analyst', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 34),
(168, 'Tim Financial Services Sales Agent', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 34),
(169, 'Tim Aircraft Cargo Handling Supervisor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 34),
(170, 'Tim Massage Therapist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 34),
(171, 'Tim Optical Instrument Assembler', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 35),
(172, 'Tim Webmaster', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 35),
(173, 'Tim Painting Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 35),
(174, 'Tim Railroad Yard Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 35),
(175, 'Tim Insurance Appraiser', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 35),
(176, 'Tim Automotive Master Mechanic', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 36),
(177, 'Tim Recyclable Material Collector', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 36),
(178, 'Tim Pharmaceutical Sales Representative', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 36),
(179, 'Tim Storage Manager OR Distribution Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 36),
(180, 'Tim Welfare Eligibility Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 36),
(181, 'Tim Pipelaying Fitter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 37),
(182, 'Tim CTO', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 37),
(183, 'Tim Food Batchmaker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 37),
(184, 'Tim Cartoonist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 37),
(185, 'Tim Paperhanger', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 37),
(186, 'Tim Gaming Surveillance Officer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 38),
(187, 'Tim Computer-Controlled Machine Tool Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 38),
(188, 'Tim City Planning Aide', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 38),
(189, 'Tim Environmental Scientist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 38),
(190, 'Tim Answering Service', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 38),
(191, 'Tim Transformer Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 39),
(192, 'Tim HVAC Mechanic', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 39),
(193, 'Tim Naval Architects', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 39),
(194, 'Tim Postmasters', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 39),
(195, 'Tim Poultry Cutter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 39),
(196, 'Tim Sports Book Writer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 40),
(197, 'Tim City Planning Aide', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 40),
(198, 'Tim Precision Pattern and Die Caster', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 40),
(199, 'Tim Tool and Die Maker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 40),
(200, 'Tim Waste Treatment Plant Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 40),
(201, 'Tim Hand Trimmer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 41),
(202, 'Tim Screen Printing Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 41),
(203, 'Tim Job Printer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 41),
(204, 'Tim Art Teacher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 41),
(205, 'Tim Sheet Metal Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 41),
(206, 'Tim Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 42),
(207, 'Tim Compliance Officers', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 42),
(208, 'Tim Semiconductor Processor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 42),
(209, 'Tim Librarian', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 42),
(210, 'Tim Municipal Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 42),
(211, 'Tim Sound Engineering Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 43),
(212, 'Tim Record Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 43),
(213, 'Tim Trainer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 43),
(214, 'Tim Pharmaceutical Sales Representative', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 43),
(215, 'Tim Soldering Machine Setter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 43),
(216, 'Tim Speech-Language Pathologist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 44),
(217, 'Tim Landscaper', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 44),
(218, 'Tim Forest Fire Inspector', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 44),
(219, 'Tim Mail Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 44),
(220, 'Tim Agricultural Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 44),
(221, 'Tim Speech-Language Pathologist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 45),
(222, 'Tim Stonemason', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 45),
(223, 'Tim Opticians', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 45),
(224, 'Tim Punching Machine Setters', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 45),
(225, 'Tim Structural Metal Fabricator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 45),
(226, 'Tim Foreign Language Teacher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 46),
(227, 'Tim Architectural Drafter OR Civil Drafter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 46),
(228, 'Tim Alteration Tailor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 46),
(229, 'Tim Shipping and Receiving Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 46),
(230, 'Tim General Practitioner', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 46),
(231, 'Tim Radiologic Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 47),
(232, 'Tim Mechanical Engineer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 47),
(233, 'Tim Numerical Control Machine Tool Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 47),
(234, 'Tim Board Of Directors', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 47),
(235, 'Tim Metal Fabricator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 47),
(236, 'Tim Statistician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 48),
(237, 'Tim Electrical and Electronics Drafter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 48),
(238, 'Tim Loan Interviewer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 48),
(239, 'Tim Purchasing Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 48),
(240, 'Tim Plating Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 48),
(241, 'Tim Fire-Prevention Engineer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 49),
(242, 'Tim Marine Oiler', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 49),
(243, 'Tim Nuclear Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 49),
(244, 'Tim Aviation Inspector', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 49),
(245, 'Tim Plasterer OR Stucco Mason', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 49),
(246, 'Tim Industrial Engineering Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 50),
(247, 'Tim Patternmaker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 50),
(248, 'Tim Computer Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 50),
(249, 'Tim Psychiatric Aide', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 50),
(250, 'Tim Refinery Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 50),
(251, 'Tim Motorcycle Mechanic', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 51),
(252, 'Tim Insurance Policy Processing Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 51),
(253, 'Tim Library Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 51),
(254, 'Tim Law Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 51),
(255, 'Tim Brickmason', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 51),
(256, 'Tim Typesetter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 52),
(257, 'Tim Technical Specialist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 52),
(258, 'Tim GED Teacher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 52),
(259, 'Tim Stonemason', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 52),
(260, 'Tim Bookkeeper', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 52),
(261, 'Tim Title Abstractor', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 53),
(262, 'Tim Financial Manager', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 53),
(263, 'Tim Industrial Engineer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 53),
(264, 'Tim Benefits Specialist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 53),
(265, 'Tim Pharmacist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 53),
(266, 'Tim Freight Agent', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 54),
(267, 'Tim Manager of Weapons Specialists', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 54),
(268, 'Tim Command Control Center Specialist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 54),
(269, 'Tim Poultry Cutter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 54),
(270, 'Tim Electronic Masking System Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 54),
(271, 'Tim CEO', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 55),
(272, 'Tim Taxi Drivers and Chauffeur', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 55),
(273, 'Tim Automotive Specialty Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 55),
(274, 'Tim Religious Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 55),
(275, 'Tim Fabric Pressers', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 55),
(276, 'Tim Jeweler', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 56),
(277, 'Tim Radiologic Technologist and Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 56),
(278, 'Tim Waitress', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 56),
(279, 'Tim Musician OR Singer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 56),
(280, 'Tim Roof Bolters Mining', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 56),
(281, 'Tim Telephone Station Installer and Repairer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 57),
(282, 'Tim Refinery Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 57),
(283, 'Tim Broadcast Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 57),
(284, 'Tim Supervisor Correctional Officer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 57),
(285, 'Tim Rock Splitter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 57),
(286, 'Tim Electrical and Electronic Inspector and Tester', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 58),
(287, 'Tim Food Servers', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 58),
(288, 'Tim Tour Guide', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 58),
(289, 'Tim Speech-Language Pathologist', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 58),
(290, 'Tim Molding Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 58),
(291, 'Tim Stevedore', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 59),
(292, 'Tim Animal Husbandry Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 59),
(293, 'Tim Marking Clerk', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 59),
(294, 'Tim Prepress Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 59),
(295, 'Tim House Cleaner', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 59),
(296, 'Tim Municipal Fire Fighter', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 60),
(297, 'Tim Umpire and Referee', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 60),
(298, 'Tim Ship Engineer', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 60),
(299, 'Tim Maintenance and Repair Worker', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 60),
(300, 'Tim Butcher', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 60),
(301, 'Tim Electronic Masking System Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 61),
(302, 'Tim Postmasters', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 61),
(303, 'Tim Milling Machine Operator', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 61),
(304, 'Tim Mechanical Engineering Technician', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 61),
(305, 'Tim Bench Jeweler', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 61);

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `late_notifications`
--

CREATE TABLE `late_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `late_notifications`
--

INSERT INTO `late_notifications` (`id`, `user_id`, `message`, `is_active`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 188, 'Lagi macet di Marvin Causeway', 0, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(2, 187, 'Lagi macet di Weber Course', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(3, 189, 'Lagi macet di Marina Meadow', 0, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(4, 187, 'Lagi macet di Winona Mountains', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(5, 188, 'Lagi macet di Victoria Centers', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(6, 190, 'Lagi macet di Drake Land', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(7, 188, 'Lagi macet di Hills Locks', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(8, 192, 'Lagi macet di Waters Oval', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(9, 190, 'Lagi macet di Ratke Lodge', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(10, 192, 'Lagi macet di Cruickshank Pass', 1, '2025-11-07 00:53:32', '2025-11-07 00:53:32', NULL),
(11, 186, 'Sibuk', 1, '2025-11-07 00:57:31', '2025-11-07 00:57:31', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_11_000000_create_divisions_table', 1),
(2, '2014_10_12_000000_create_users_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2025_11_06_041136_create_audit_teams_table', 1),
(7, '2025_11_06_041204_create_attendances_table', 1),
(8, '2025_11_06_041226_create_late_notifications_table', 1),
(9, '2025_11_06_141849_add_location_to_attendances_table', 1),
(10, '2025_11_07_002601_create_user_device_tokens_table', 1),
(11, '2025_11_07_035502_create_branches_table', 1),
(12, '2025_11_07_035536_add_branch_id_to_users_table', 1),
(13, '2025_11_07_035555_add_branch_id_to_divisions_table', 1),
(14, '2025_11_07_035611_add_branch_id_to_attendances_table', 1),
(15, '2025_11_07_035633_add_branch_id_to_late_notifications_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `division_id` bigint UNSIGNED DEFAULT NULL,
  `qr_code_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `division_id`, `qr_code_value`, `remember_token`, `created_at`, `updated_at`, `branch_id`) VALUES
(1, 'Super Admin PStore', 'superadmin@pstore.com', '2025-11-07 00:53:06', '$2y$10$eClBz/yUxPsbBGtZIm8g0.It7fK68JSNwMxiQysHNvbipTX1XdI72', 'admin', NULL, 'a34f6053-00c3-395c-98f6-3689ed839600', 'LaNslMC3cGCrb6Y96bTbC09I9KusmwNREEfwdJAGLhRU2Ju4IcdW101jAxqZ', '2025-11-07 00:53:06', '2025-11-07 00:53:06', NULL),
(2, 'Admin PStore Evelineland', 'admin.pstoreevelineland@pstore.com', '2025-11-07 00:53:06', '$2y$10$FZKbmIH7dvQn.IuljWuj2eC8gdwtYKQ72vN2SxzeXGRpVWMerK2eO', 'admin', NULL, 'e3c7fb0d-7ec5-3cc1-b0b6-8f70cda68f5f', 'ZoeS5oz5Gm', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 1),
(3, 'Audit PStore Evelineland', 'audit.pstoreevelineland@pstore.com', '2025-11-07 00:53:06', '$2y$10$zd/kc4QMgXZE8rL1LCC3heGnokjGB.XA2Lp9Vw0yM6qnMZQDYWC3y', 'audit', 1, '25f2db75-12fb-3848-8e74-3bd9e4f18f29', 'lfWPVPyvlz', '2025-11-07 00:53:06', '2025-11-07 00:53:06', 1),
(4, 'Security PStore Evelineland', 'security.pstoreevelineland@pstore.com', '2025-11-07 00:53:06', '$2y$10$YjycIdcemr8KvkpP3pO/e.PLhY9gqmq.6l6.Hr3kbVFQRkyFBbRaa', 'security', NULL, '09200582-22c3-3735-940e-9928461965af', 'LGHghMW2t1', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 1),
(5, 'Admin PStore New Carliemouth', 'admin.pstorenewcarliemouth@pstore.com', '2025-11-07 00:53:07', '$2y$10$cA9vM0saeVVYhJSQrSDQ8ujqNbkwRezsDFvYpdsz/eiSxpilE0/e6', 'admin', NULL, '77f93d4d-e098-3fa7-adcb-c0cfb301a6a2', 'Z7SNMEtigT', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 2),
(6, 'Audit PStore New Carliemouth', 'audit.pstorenewcarliemouth@pstore.com', '2025-11-07 00:53:07', '$2y$10$PFme3MMiOgunohzhjO71Du1mqM2.knEREN8jCMjokl7zhpsAuGXDW', 'audit', 6, '468920b3-65f8-378d-8002-06c1917c5747', 'nNR1iqZKXD', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 2),
(7, 'Security PStore New Carliemouth', 'security.pstorenewcarliemouth@pstore.com', '2025-11-07 00:53:07', '$2y$10$4BVsNigw1EpbGw4IZ3PvSOkWHWViDVJMt4X6BVJy3t58BH1hS1EAC', 'security', NULL, '90d57642-99ae-3ba9-b2ef-d124058f5fb7', 'n4rQk82yZW', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 2),
(8, 'Admin PStore Tristianhaven', 'admin.pstoretristianhaven@pstore.com', '2025-11-07 00:53:07', '$2y$10$QBekEyIn3J.bqid7bFxKfe7nCFkdecUrtdNbOl8QqEqLJmmYjgj06', 'admin', NULL, '14aea5ac-ec5d-3104-b459-f05066603ef7', 'gOhREmUWAJ', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 3),
(9, 'Audit PStore Tristianhaven', 'audit.pstoretristianhaven@pstore.com', '2025-11-07 00:53:07', '$2y$10$kUh0Y/wuqgbl2oQCfacyp.aijV7IiuiNGkFnOHGtJIgUuMb/aLmbK', 'audit', 11, 'cd139fa4-2377-3518-9075-a577e36367e7', 'sRDu35FTxi', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 3),
(10, 'Security PStore Tristianhaven', 'security.pstoretristianhaven@pstore.com', '2025-11-07 00:53:07', '$2y$10$SMvtLh/RyZhx836P94a6TeJmMcKdaJUHs8lRJji0JrXej2bCRxz0K', 'security', NULL, '40317612-6cc1-3da2-bc0b-4f8ee9135c7d', 'IVhAf5wtBO', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 3),
(11, 'Admin PStore Lorenport', 'admin.pstorelorenport@pstore.com', '2025-11-07 00:53:07', '$2y$10$cAIUwD8KYerTseJPassqEev7b0hpTtRvUo6j9muSq0QXNvFTPxHC.', 'admin', NULL, 'c4879e87-8f1a-355c-99c9-9e240ee572f6', 'EQlwvYQzez', '2025-11-07 00:53:07', '2025-11-07 00:53:07', 4),
(12, 'Audit PStore Lorenport', 'audit.pstorelorenport@pstore.com', '2025-11-07 00:53:07', '$2y$10$DtN1/ZlKXw5tgUn5sN/Iy.gNBB021OT/G8b57RdEiZ6NaS4kpcuTK', 'audit', 16, '4e140d33-01c2-34e4-8344-327162f27e0e', 'TrZQJUSm5EtkxS441ZHNRYeZqFinsRyaMnp99zhOInWlnksrgDz9wdYvf0A8', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 4),
(13, 'Security PStore Lorenport', 'security.pstorelorenport@pstore.com', '2025-11-07 00:53:08', '$2y$10$yGL95IlcVcU7PyPIly4nOO1Y9o0HAwNE9aVfYc2FdEW5vomy2btqe', 'security', NULL, 'e1013482-fac5-34e2-8af1-52d9b4288bbe', '9LKcO1h3Rs', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 4),
(14, 'Admin PStore Jonatanfort', 'admin.pstorejonatanfort@pstore.com', '2025-11-07 00:53:08', '$2y$10$x3C.u0NyXAt4YhPDOW6UeOypz8QtYSfD9HMZgx6DGbP6ggOrbBfWq', 'admin', NULL, 'eba88947-5a85-33dc-b032-3cc30c0a529f', '77WNu65N4z', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 5),
(15, 'Audit PStore Jonatanfort', 'audit.pstorejonatanfort@pstore.com', '2025-11-07 00:53:08', '$2y$10$8KUtT3DcZVl.1uQVTa6DVeCzn9hFXLg.BgSqnWu/d.GyrBzZ.Z1Le', 'audit', 21, '04c1b165-24f4-3399-8a0c-985fa9788ad1', 'wxsD7e9AjS', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 5),
(16, 'Security PStore Jonatanfort', 'security.pstorejonatanfort@pstore.com', '2025-11-07 00:53:08', '$2y$10$6HuVfk0gks5wa6xTuigEAOf8V3GE7C6qBsTthHF.9wO9tVFxmmMU2', 'security', NULL, 'a723ed01-93d8-3e82-93cd-49befd53f5a8', 'O89Fi8IPwH', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 5),
(17, 'Admin PStore Lake Tarynland', 'admin.pstorelaketarynland@pstore.com', '2025-11-07 00:53:08', '$2y$10$Kzq3JA2K2YVucQMzj2MRPebWjTUJRvnFNze4FZhuH5NhDC6O.WLzS', 'admin', NULL, 'c1cdd45c-17ac-39f4-aaf1-6262c0ddef53', 'JF3ch8fTod', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 6),
(18, 'Audit PStore Lake Tarynland', 'audit.pstorelaketarynland@pstore.com', '2025-11-07 00:53:08', '$2y$10$puIqNHlApw/G2TUPZwiYIeh9SlJeLTy3h2aWdf/vNl9XoqeX9Hbje', 'audit', 26, '92a09064-2429-37c8-bf2c-3f62947285cf', '8CqAZqEK6b', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 6),
(19, 'Security PStore Lake Tarynland', 'security.pstorelaketarynland@pstore.com', '2025-11-07 00:53:08', '$2y$10$wp1Hp4jGBTcbw0lBLrkbiutGEfvuoP8T56tzEorcXkvEl25lsBgmG', 'security', NULL, 'cf8da9c2-558e-3f6b-beca-1095a0f40a9e', '8UvZuXqQxo', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 6),
(20, 'Admin PStore Luemouth', 'admin.pstoreluemouth@pstore.com', '2025-11-07 00:53:08', '$2y$10$cOfYrBFraiLC5pakYAR34uZO.j.yoU/WysoRG2yOv7ZVfEfUiWheu', 'admin', NULL, 'b8ca5192-c3d8-34f0-92ae-8cb9af9a530a', '5Z5L4NH0ln', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 7),
(21, 'Audit PStore Luemouth', 'audit.pstoreluemouth@pstore.com', '2025-11-07 00:53:08', '$2y$10$XLOlcab7sLbZo1JS73ljn.lyrK21pBiG6vrha.OFD76nufJCAuTlq', 'audit', 31, '377eb366-ce3f-3fe8-9ee2-f745c4042a1d', 'Y7PrgwYgtU', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 7),
(22, 'Security PStore Luemouth', 'security.pstoreluemouth@pstore.com', '2025-11-07 00:53:08', '$2y$10$WXI.B6B1/VwotxzEc21pd.iV3Nyqkx3c0vGmStOX5RTsGkhy/2Pxi', 'security', NULL, '6c0cfb86-e22a-3fe7-9c53-7671ceb4527c', 'IhngMpLawh', '2025-11-07 00:53:08', '2025-11-07 00:53:08', 7),
(23, 'Admin PStore East Adam', 'admin.pstoreeastadam@pstore.com', '2025-11-07 00:53:08', '$2y$10$RKPuxph7IlG8POIm1Hy3WuhYCbOHxKLOR6jNo8vIHfc5QjnM9uAPq', 'admin', NULL, '12bb3423-6851-3342-aeda-984d97e35cf5', 'CmN2feOyI4', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 8),
(24, 'Audit PStore East Adam', 'audit.pstoreeastadam@pstore.com', '2025-11-07 00:53:09', '$2y$10$ynsLMusChjaSnH7OXs9btOuQ0DZ7py7w5hP6W9Lm/ugSFLoNGkoc6', 'audit', 36, 'ee8fb745-ffda-329b-bc1a-977f3c91c6ba', '0ixk7qsm4v', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 8),
(25, 'Security PStore East Adam', 'security.pstoreeastadam@pstore.com', '2025-11-07 00:53:09', '$2y$10$LV0rUCS6Bbt720HRc9Z5.OT/AF9ActQUeB6xVA5SfoArkwrmAnUKy', 'security', NULL, 'e3aeab83-04b9-3612-978c-ac3288dccf8c', 'q6aVaH4RKA', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 8),
(26, 'Admin PStore Hillborough', 'admin.pstorehillborough@pstore.com', '2025-11-07 00:53:09', '$2y$10$U2sP7NMV50yWPtMTeif8H.8Cmv9KPl7RUUKGMdn7oZ9Jhdkcd6pii', 'admin', NULL, '5ac83f9f-3aa7-3bd1-a4a4-3c54bd4aa0db', 'WDnwh7jlHA', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 9),
(27, 'Audit PStore Hillborough', 'audit.pstorehillborough@pstore.com', '2025-11-07 00:53:09', '$2y$10$iRgELMhSiiA7gzIHeFn/6uiRnsJ1k8xt5Pn/lvWoVRR3.st57Q1KK', 'audit', 41, 'f039f755-97f2-3dc0-bc3b-e46021866f1a', 'Lv4HFKERgr', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 9),
(28, 'Security PStore Hillborough', 'security.pstorehillborough@pstore.com', '2025-11-07 00:53:09', '$2y$10$7/wG44SN8mf.xfc3gIqjB.eyX1/bHBBpeFmvyLC3eGjHBlsG75GvG', 'security', NULL, '3575ea62-7731-366c-aab7-3d61d868215b', '1iT68uOR6P', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 9),
(29, 'Admin PStore South Nikitamouth', 'admin.pstoresouthnikitamouth@pstore.com', '2025-11-07 00:53:09', '$2y$10$fWkgLYlBbzQ.y13jYjbiB.hHr7gEiGuc0du5go.I7mwJ8GHs1UFc.', 'admin', NULL, '488e0fe0-ea90-3d57-9bbe-e5d567b903cd', 'wV4mkbG6a4', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 10),
(30, 'Audit PStore South Nikitamouth', 'audit.pstoresouthnikitamouth@pstore.com', '2025-11-07 00:53:09', '$2y$10$KZKk.Nmiqwrdld5SID/.N.t7DGGtpr68mbjJBdiYaLdiviwIftbKC', 'audit', 46, '34970b51-e6be-387f-9ebf-167fa4c61728', '3pGyQJZzUB', '2025-11-07 00:53:09', '2025-11-07 00:53:09', 10),
(31, 'Security PStore South Nikitamouth', 'security.pstoresouthnikitamouth@pstore.com', '2025-11-07 00:53:09', '$2y$10$Z8L6VJH8UjtqxvgkvwGYA.NGeXLAnk3dMo39n5kTSTSQVpzKaTLbq', 'security', NULL, 'c8a33d97-6a28-36f0-a2d8-19ffd4b09628', '7xPBxAEYEI', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 10),
(32, 'Admin PStore North Johathan', 'admin.pstorenorthjohathan@pstore.com', '2025-11-07 00:53:10', '$2y$10$4wrj2d0D0uXFf.Fa8ba3YutDxuidJHhkfNsVdig5VMWlv2Tk5K9tO', 'admin', NULL, '771a7a09-8edc-35fb-8d8c-2b437af1d5d3', 'GvPvGWX4fe', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 11),
(33, 'Audit PStore North Johathan', 'audit.pstorenorthjohathan@pstore.com', '2025-11-07 00:53:10', '$2y$10$R8.2lECN/zDuNFB4v9ZZNuneA/nPaqBWV3Q14/VHLlvNIsGapezM6', 'audit', 51, 'd212094f-66e5-3b39-bbc9-4c1ad1cf739f', 'nHINy9a3UH', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 11),
(34, 'Security PStore North Johathan', 'security.pstorenorthjohathan@pstore.com', '2025-11-07 00:53:10', '$2y$10$Sp8eUvXwxpfpp09C/0o77.Wr2pgQyuNvQWrndAJG/OEuL/NlkvZw.', 'security', NULL, '101c3976-d16f-37ca-8791-05ca0f84943f', '3yEYCysfGi', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 11),
(35, 'Admin PStore Lake Jovan', 'admin.pstorelakejovan@pstore.com', '2025-11-07 00:53:10', '$2y$10$nB14NvQYo.QGJJd7eBcIKuVpPfdcc88bUP8K/LEQHYUK4t2UaX1Va', 'admin', NULL, 'd5123d30-e26c-34a4-8b35-1f41ff9bf463', 'VOxfb0egsX', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 12),
(36, 'Audit PStore Lake Jovan', 'audit.pstorelakejovan@pstore.com', '2025-11-07 00:53:10', '$2y$10$sCxIVO/edXXo1/smXv9vK.l3GDL6PQVS94iqphbjv6ilLWChhpGBO', 'audit', 56, 'e0438b6f-96ea-3924-8594-14bc0491e6f9', 'teeiyvjNQz', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 12),
(37, 'Security PStore Lake Jovan', 'security.pstorelakejovan@pstore.com', '2025-11-07 00:53:10', '$2y$10$OJo57UozSYaFuBB5SMxhPuKiPe3HQ5lnlkCHdDlIUnW7M7CWeDQ7y', 'security', NULL, '71784eab-df46-3e21-9b98-6cb88664e3d1', 'WIc0ZqzqWx', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 12),
(38, 'Admin PStore New Pearlie', 'admin.pstorenewpearlie@pstore.com', '2025-11-07 00:53:10', '$2y$10$/M/LFIvvqXch6STyHOcbou2noGDvLUlurMMrY7sfPWtKlLjEsTox.', 'admin', NULL, '4aae52e6-2013-3ef1-85d2-8a94822f5362', 'R9QP2xlFO3', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 13),
(39, 'Audit PStore New Pearlie', 'audit.pstorenewpearlie@pstore.com', '2025-11-07 00:53:10', '$2y$10$GSq8XaNYjjM2brSUYzgQte5kF/MmIF9IheHbTTnwNHXb.YK8NwLi6', 'audit', 61, 'a31f5757-88e0-34cc-a05f-cfd93a06f1a1', 'Ovg8Ajq22A', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 13),
(40, 'Security PStore New Pearlie', 'security.pstorenewpearlie@pstore.com', '2025-11-07 00:53:10', '$2y$10$1yELyeTSuGQLTF9YAdNXjObcU2Ju5DxOXCqJ0Suh7RCyCYgC0gLLO', 'security', NULL, '07a4d927-025d-38ce-ba29-27ca7b8d9bcd', 'hdJpWuqEih', '2025-11-07 00:53:10', '2025-11-07 00:53:10', 13),
(41, 'Admin PStore Lake Camilla', 'admin.pstorelakecamilla@pstore.com', '2025-11-07 00:53:10', '$2y$10$AS82i9mWIGKd2DsV1sI9P.J6HTaMuYtBsoDYzMlzLdOqRuWl3hEqu', 'admin', NULL, '88dccd07-b9eb-332d-99b5-c3cb803196a8', 'uibYLtqEYz', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 14),
(42, 'Audit PStore Lake Camilla', 'audit.pstorelakecamilla@pstore.com', '2025-11-07 00:53:11', '$2y$10$GIK5zWh/y4m.gsK69oC2Le4ph0Bg33Co4RLRWPw0PdCsS/7inTviu', 'audit', 66, '1e77ffcc-c67d-3e8d-8393-800b1e25d75e', 'HHGim3FTBy', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 14),
(43, 'Security PStore Lake Camilla', 'security.pstorelakecamilla@pstore.com', '2025-11-07 00:53:11', '$2y$10$BzpRI1LLhd2K4zEBeTxR1u2Zt0oqNbme8mMrE/U7UBArAsQMXN9r2', 'security', NULL, 'f6739e12-a182-3988-b5f2-09086e0a6ab6', 'thByGRpKK5', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 14),
(44, 'Admin PStore New Calista', 'admin.pstorenewcalista@pstore.com', '2025-11-07 00:53:11', '$2y$10$6b5PvkNM6DJnAn5myC2i6eZq8x3X80BW17VboHWCpzTe18Vlrcqri', 'admin', NULL, '6ba54bf1-d3ed-35e4-ab96-681965be5b3b', 'Bqk7wNCPBY', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 15),
(45, 'Audit PStore New Calista', 'audit.pstorenewcalista@pstore.com', '2025-11-07 00:53:11', '$2y$10$q1UxA8QqXB9ewVkZ6eSi..RoCzYyp0yiOzyE.BljuvGcYqvEaprdG', 'audit', 71, '8e7644fd-7bb7-3f40-be7e-d55db9a03226', 'ZnyU18840F', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 15),
(46, 'Security PStore New Calista', 'security.pstorenewcalista@pstore.com', '2025-11-07 00:53:11', '$2y$10$VbvAn5PSufZsRmCs42G5dOIoioTH47k301CbXH7fknQ24i4D40FNm', 'security', NULL, '6992382a-dcb4-33f0-a506-7f6a5c750972', 'x0HVMZMjFR', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 15),
(47, 'Admin PStore Port Sylvester', 'admin.pstoreportsylvester@pstore.com', '2025-11-07 00:53:11', '$2y$10$42ddIUPRAjEbVUT6XFMxk.sMca566LuDNXjtuipTHfflZ5LLubjgy', 'admin', NULL, 'ea5e17c0-ea35-3fcc-b6e7-926f27e598ce', 'QYkW2YzDjc', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 16),
(48, 'Audit PStore Port Sylvester', 'audit.pstoreportsylvester@pstore.com', '2025-11-07 00:53:11', '$2y$10$YMO3UrTrVRTpQCBAHqzE2OKzsg.9121uvXxKX0751XoDgOaPnVIO6', 'audit', 76, '4edaafa0-7705-37a0-baa3-76aeda5684a1', '8KCTsjk8Vv', '2025-11-07 00:53:11', '2025-11-07 00:53:11', 16),
(49, 'Security PStore Port Sylvester', 'security.pstoreportsylvester@pstore.com', '2025-11-07 00:53:11', '$2y$10$jDuQvpzM.U0sdZJI8zcrpuRGsnYLtZKv67ZftDiXFlhPPkijP6BmC', 'security', NULL, 'aabb5402-d4ea-352e-b853-89f3aa95cb02', 'EwOYIqnPu3', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 16),
(50, 'Admin PStore Zemlakstad', 'admin.pstorezemlakstad@pstore.com', '2025-11-07 00:53:12', '$2y$10$GakJ1ni7aQVInl7Kpq/q.eMDY9TzRGz0.uIOmakblPSlHeS0X.7PO', 'admin', NULL, '8ed08f56-9227-3867-a95c-6bdda33b8ec3', 'd4eExOLe26', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 17),
(51, 'Audit PStore Zemlakstad', 'audit.pstorezemlakstad@pstore.com', '2025-11-07 00:53:12', '$2y$10$KlmyQtJaFFkOMlLJZnXcNOk0L0JmDF0i8/5sXXdQAsmCCrRMTLegy', 'audit', 81, '1df7cedb-c7f2-31c3-bd9c-bdcd619a2c25', 'FH7fP0uj7q', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 17),
(52, 'Security PStore Zemlakstad', 'security.pstorezemlakstad@pstore.com', '2025-11-07 00:53:12', '$2y$10$e8heKophnfVT4W91EB1zFu1HI5BCpAB4M4PM1fuATFaZq1JVaj9Sy', 'security', NULL, '8e77c40d-da98-3848-8cdd-71da94a7a533', 'diqCmUT7dp', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 17),
(53, 'Admin PStore East Corrinehaven', 'admin.pstoreeastcorrinehaven@pstore.com', '2025-11-07 00:53:12', '$2y$10$KuYn9tQlv8dA1iTIwIgzbOSIu8h5.wAAWGMH30jfBfOy5gnE4BeAy', 'admin', NULL, '3a8d7066-dba7-38fe-a002-4add665c59dd', 'YGEPWh21E1', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 18),
(54, 'Audit PStore East Corrinehaven', 'audit.pstoreeastcorrinehaven@pstore.com', '2025-11-07 00:53:12', '$2y$10$2Lj18sho8fiRU6FBho.aAe9DhTYDGKDTwpvUB2DaSQWY6s.FLT1C2', 'audit', 86, 'f06b0510-b85e-36e4-a75c-85ae6244b264', 'i7QGUQIFi4', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 18),
(55, 'Security PStore East Corrinehaven', 'security.pstoreeastcorrinehaven@pstore.com', '2025-11-07 00:53:12', '$2y$10$0uJnanHXTzWH7oRiZdZjGOHJjgKnldYSHn3HVdLm/3/N1NWN8pGBe', 'security', NULL, 'a12c3b9a-6002-3733-871b-7a7e073b1bfe', '5RxpEBp84p', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 18),
(56, 'Admin PStore West Myrtlefort', 'admin.pstorewestmyrtlefort@pstore.com', '2025-11-07 00:53:12', '$2y$10$EeA1GiuaTZfdbJ7tXhhxzufiE3dDpkkKJjkhL2zzRcE23bA8fICu.', 'admin', NULL, 'dc29e8a2-42cc-331f-a4ec-eb500f95b55f', 'rZNDRG7xEG', '2025-11-07 00:53:12', '2025-11-07 00:53:12', 19),
(57, 'Audit PStore West Myrtlefort', 'audit.pstorewestmyrtlefort@pstore.com', '2025-11-07 00:53:12', '$2y$10$/HKvZzAhUtWZDNQVegy8/uTf7s.e.X2VvDDVWo2XXNi5hmGNLzCem', 'audit', 91, '674f34d7-25e0-3a2f-b855-b9d59ad752ff', 'iLw1p9TGol', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 19),
(58, 'Security PStore West Myrtlefort', 'security.pstorewestmyrtlefort@pstore.com', '2025-11-07 00:53:13', '$2y$10$79VTKNeYjja8jUDf4WknD.xcK0TL8ijhEIAS6iF1DKP3cptys2FrO', 'security', NULL, '6fa4bd3c-5d96-3e81-bad3-62f26baf1352', '9i3clvDVAp', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 19),
(59, 'Admin PStore South Carterberg', 'admin.pstoresouthcarterberg@pstore.com', '2025-11-07 00:53:13', '$2y$10$VXu6ofHKes8AZmPeXyzrXetc91EVjQ3UH7vu64hqBzsh7KnSe.Frq', 'admin', NULL, 'cf9b12cc-9353-3f8e-ba73-ba1031243b3c', 'LcosB7O7Jn', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 20),
(60, 'Audit PStore South Carterberg', 'audit.pstoresouthcarterberg@pstore.com', '2025-11-07 00:53:13', '$2y$10$P4SggeO7kzCUgw1u.7RPZO6jwjCLRhPFjRkbD4exCxJ24/XkomOuO', 'audit', 96, 'dcba9255-1c55-329a-a4d9-d51479df00f0', '21OT9xiPxh', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 20),
(61, 'Security PStore South Carterberg', 'security.pstoresouthcarterberg@pstore.com', '2025-11-07 00:53:13', '$2y$10$jNB5SggcRTDnAhiro9hUcu7y8ntwDRpmtBifOZaVycsIBZeW1Subi', 'security', NULL, '8a4dd25e-512c-3c83-b410-6c8a65cd7fca', 'O0VLFeLvN5', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 20),
(62, 'Admin PStore Port Nathanielfort', 'admin.pstoreportnathanielfort@pstore.com', '2025-11-07 00:53:13', '$2y$10$KH358fzO1WV8UzEHR.2dTuBjq1.4ZOtVO0KVTN0psRPJu/cMfHTae', 'admin', NULL, '90c4d178-7352-3021-ba53-43ec3dcff89f', 'mIaHscRVzk', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 21),
(63, 'Audit PStore Port Nathanielfort', 'audit.pstoreportnathanielfort@pstore.com', '2025-11-07 00:53:13', '$2y$10$/UNc0TkxyHIMRPmbodL4TO9HuWVdoVOOb.wbuWmkYOyeq5FJ0cPuC', 'audit', 101, '41428cb3-7fa8-32dc-a2b9-b37d4d071997', 'jK0XZ8Q9f2', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 21),
(64, 'Security PStore Port Nathanielfort', 'security.pstoreportnathanielfort@pstore.com', '2025-11-07 00:53:13', '$2y$10$8UV2tNex/Z3iGsPZRhWvoOUf.LDK5zT2wfyYeWnzT3fmEx823.dee', 'security', NULL, 'ddd8d087-5e4d-3848-816f-f8c058d90d50', '94qVBpL3pc', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 21),
(65, 'Admin PStore West Sadyemouth', 'admin.pstorewestsadyemouth@pstore.com', '2025-11-07 00:53:13', '$2y$10$.cAyoRqgIh7XS2rxjFY3QuaM3Pn2G5qpJvxLVgR3VBJjGnYYmCOYe', 'admin', NULL, 'be748822-7108-35f4-9299-be3f1f862918', 'apZNln4gyN', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 22),
(66, 'Audit PStore West Sadyemouth', 'audit.pstorewestsadyemouth@pstore.com', '2025-11-07 00:53:13', '$2y$10$Nf9NOTqw8ZIFhbFjbWNuYOlRaE2rGrreU0oYKC0hC1UfIv97pWHG6', 'audit', 106, '2bf95b37-b0dd-3e5f-82b5-16b83883ee42', '5nTGoaT4HI', '2025-11-07 00:53:13', '2025-11-07 00:53:13', 22),
(67, 'Security PStore West Sadyemouth', 'security.pstorewestsadyemouth@pstore.com', '2025-11-07 00:53:13', '$2y$10$xXRyemMmBioisnLlseWs4.eUNyGryJSd1rlqUhuqWP7ULor0BMuWG', 'security', NULL, '3a122f94-e292-35f2-9be0-2cfec28e5100', 'OG3yzoJ3jR', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 22),
(68, 'Admin PStore Aryannafort', 'admin.pstorearyannafort@pstore.com', '2025-11-07 00:53:14', '$2y$10$zaTsIffFDH98zsCjD91GkefkmF/GV5LBWO20VOlH8rpDUXUChVZQ2', 'admin', NULL, '948a0d9e-bf9e-3a37-b73d-d4761a275b28', 'fw9HDD90RS', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 23),
(69, 'Audit PStore Aryannafort', 'audit.pstorearyannafort@pstore.com', '2025-11-07 00:53:14', '$2y$10$n1.PvHclDFQqUZUybdyVoewglizTrE.Oq30zR/4Vc0X1tHd94WIBK', 'audit', 111, '78f8eb5e-0b14-3214-aa3d-19256d9460d8', 'RrQVEjFzNl', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 23),
(70, 'Security PStore Aryannafort', 'security.pstorearyannafort@pstore.com', '2025-11-07 00:53:14', '$2y$10$RLgFyMthAgMYls1cI.WHcOa9PGcAzdGegGnPSf8wP3943oDid8T8O', 'security', NULL, '67405b28-685d-3366-886c-0f6570cde566', 'WeJoo6Xxsp', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 23),
(71, 'Admin PStore Lelandside', 'admin.pstorelelandside@pstore.com', '2025-11-07 00:53:14', '$2y$10$2QLKQ2VJpTq8vYi3K0aZqeBhQZDS29TAvz4JtpdcQV1JmvTnoiYBe', 'admin', NULL, '06482109-4b6c-3f37-8253-7f3ef3f5e662', 'KntHbif6c4', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 24),
(72, 'Audit PStore Lelandside', 'audit.pstorelelandside@pstore.com', '2025-11-07 00:53:14', '$2y$10$s4AzlzxYGU8oupPZYNrs8.sj8WzzKNpIeQKNeKN74ux9aZwkIaM7W', 'audit', 116, '790062f2-ba26-3019-b94f-148434584422', '0l7nbOuaWJ', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 24),
(73, 'Security PStore Lelandside', 'security.pstorelelandside@pstore.com', '2025-11-07 00:53:14', '$2y$10$etrMwn7ZC3T/i6FsfJsskOqmkicm8emVB5REdOs2RyV5h68BcqxYG', 'security', NULL, 'cd9c1ff3-4943-35f3-8192-8cf237b31de6', '1us8OzfcWZ', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 24),
(74, 'Admin PStore Heidenreichberg', 'admin.pstoreheidenreichberg@pstore.com', '2025-11-07 00:53:14', '$2y$10$haRjLDOYyoWkHzteShRS8eaB3tQyl0gINlflv55Y4a.Osvv9cFnmq', 'admin', NULL, 'add2e268-1476-3d34-9bf7-21d90e3de39b', 'wgoZf1CJeR', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 25),
(75, 'Audit PStore Heidenreichberg', 'audit.pstoreheidenreichberg@pstore.com', '2025-11-07 00:53:14', '$2y$10$2hk76.RS2MRvAZwcCioSj.21BlLEbT7rwAN9fdX8mKjFQDuhBdX7K', 'audit', 121, '1eb97ca6-dd3b-3e6c-993d-a0438833e47a', '4ppVJvCZiz', '2025-11-07 00:53:14', '2025-11-07 00:53:14', 25),
(76, 'Security PStore Heidenreichberg', 'security.pstoreheidenreichberg@pstore.com', '2025-11-07 00:53:14', '$2y$10$d6WDEmVIk0PHH99GJJ9YM.ivPqCdhzxp2cJO8A8Ew2GiHZPJXvSEy', 'security', NULL, '9cebfea4-9cb1-33e5-884a-0405ba8e9724', '6KGlp3DOoM', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 25),
(77, 'Admin PStore West Jaclyn', 'admin.pstorewestjaclyn@pstore.com', '2025-11-07 00:53:15', '$2y$10$R/P4Dn2BlfF57M5a1mphnezWX4VPmr1V7sxSDIWUtc8hi83vyrkzK', 'admin', NULL, '26983647-bbda-34b5-ac81-4628f1c86261', '4fdOJ3Moqg', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 26),
(78, 'Audit PStore West Jaclyn', 'audit.pstorewestjaclyn@pstore.com', '2025-11-07 00:53:15', '$2y$10$0zsy7jkOdcF9vFNVHbNkdu0iYAbz6DZ07OACaQ3wgZkJukSGvvcPC', 'audit', 126, 'e3913da6-0ade-3cfe-bfe4-397a9045f192', 'BaVmndjaIz', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 26),
(79, 'Security PStore West Jaclyn', 'security.pstorewestjaclyn@pstore.com', '2025-11-07 00:53:15', '$2y$10$upZgrwid67JaxhglJvcaYOVrkZXe2xlPKjHGGtmGjj8eBPOtiJIhi', 'security', NULL, '4a24cfd2-3cdd-37e8-aeb4-a42a040887f6', 'Qs6qMtCTjL', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 26),
(80, 'Admin PStore Soledadbury', 'admin.pstoresoledadbury@pstore.com', '2025-11-07 00:53:15', '$2y$10$ueUfjiCbKdpM6wQk/3IHjeyYJ71LoHmT4rbOgbiQWk6Grds937Eo2', 'admin', NULL, 'e947a825-c89a-39a6-ae9e-a5a19d2f1acc', '5YtofIXBqV', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 27),
(81, 'Audit PStore Soledadbury', 'audit.pstoresoledadbury@pstore.com', '2025-11-07 00:53:15', '$2y$10$lrYby283qkjL736/CJ/RY.CnalX0Lf2OG.k2ySY/G.Ve.5wva.HkC', 'audit', 131, '0941627b-75f0-38e7-9e7f-f882415e06a9', 'mQJbJwXuw0', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 27),
(82, 'Security PStore Soledadbury', 'security.pstoresoledadbury@pstore.com', '2025-11-07 00:53:15', '$2y$10$AkO2r9yFZAan9zyZPFFpW.Tqe7mYTusIbNNCok.0Ci9SO1saMkfyq', 'security', NULL, '8bd52d1f-b607-37d1-af9a-6563f7ca5d60', 'peH57RLsuY', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 27),
(83, 'Admin PStore Larsonfurt', 'admin.pstorelarsonfurt@pstore.com', '2025-11-07 00:53:15', '$2y$10$qDozHNoM2cf0C486k6HiFefLhd7K87tU45z6wnyeLC8iKdXJYWdPq', 'admin', NULL, '988fa218-1be4-301c-a8b4-44234da7c381', 'Y4Asb2shx1', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 28),
(84, 'Audit PStore Larsonfurt', 'audit.pstorelarsonfurt@pstore.com', '2025-11-07 00:53:15', '$2y$10$byu4LmVOwgKKaUJZsoCKbOA8qoTqHWJVCU0Bh0eCIkPsmi1r6zzfi', 'audit', 136, '190d0955-a0d2-3b1a-8435-004c52eb89bb', '9wS1fZLOPy', '2025-11-07 00:53:15', '2025-11-07 00:53:15', 28),
(85, 'Security PStore Larsonfurt', 'security.pstorelarsonfurt@pstore.com', '2025-11-07 00:53:15', '$2y$10$CMW/d4dR58WFrFFi2.FPbOGBgGOUed50z9xbEZWGkicOEsPlI0qLe', 'security', NULL, 'cae353ba-f443-38e5-a922-5b2b7b923015', '23G50h07pZ', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 28),
(86, 'Admin PStore East Kallie', 'admin.pstoreeastkallie@pstore.com', '2025-11-07 00:53:16', '$2y$10$UvikpB8LCFqu.AUmyE6x2O1il6j7ILK5yU.21O2qj4aMlkpQoVmt6', 'admin', NULL, 'f1cb43ed-6882-3ab9-8c81-92404d41ba75', '4BzJzxCt9h', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 29),
(87, 'Audit PStore East Kallie', 'audit.pstoreeastkallie@pstore.com', '2025-11-07 00:53:16', '$2y$10$mukaVikEFfGLvqBTLleOMu1/xywDJHzorZL/1r2Yawk/yeIrAmYQq', 'audit', 141, '7cb09277-d98d-3918-96ed-f23fc2db11d5', 'MYd5WObf2L', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 29),
(88, 'Security PStore East Kallie', 'security.pstoreeastkallie@pstore.com', '2025-11-07 00:53:16', '$2y$10$sZ4ji.1DnchD1oZTAvwUzehShKkB9.cDFwlGZG3NG7/DzVopJm5Cq', 'security', NULL, '22dcd98a-661f-3579-834d-520f5e95a208', 'uVkpat2M05', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 29),
(89, 'Admin PStore South Darrellberg', 'admin.pstoresouthdarrellberg@pstore.com', '2025-11-07 00:53:16', '$2y$10$js1/.s5rLQa3WTRuxb3ypOytk6Kb6E98DqxbL7EAwdqtrbYGHPj0.', 'admin', NULL, 'cf498fb4-8902-3009-8c61-aa58929d2890', 'TfM7bPK5mS', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 30),
(90, 'Audit PStore South Darrellberg', 'audit.pstoresouthdarrellberg@pstore.com', '2025-11-07 00:53:16', '$2y$10$6sZwHveNEmEtTIkP2LuwiuePqKgCNW9LQUgRo43fVJhRcrwt4Yvyy', 'audit', 146, '254fdc58-971b-3dd8-bb0a-c5e2df3d778f', '9dvKI5X8iw', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 30),
(91, 'Security PStore South Darrellberg', 'security.pstoresouthdarrellberg@pstore.com', '2025-11-07 00:53:16', '$2y$10$CI8NRktKadYtTtvc75.lUuS3RL6anpugUMW6N1aVXApJyrhVsLihe', 'security', NULL, '874c3bf9-5f4b-395e-80a7-0885fd56fa04', 'fNP9eXel1P', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 30),
(92, 'Admin PStore Leschmouth', 'admin.pstoreleschmouth@pstore.com', '2025-11-07 00:53:16', '$2y$10$/bIPpj4DODHaYKBBpFyMKeo28X0eaHvG1pZwcMXJyhv48leB8Uhvu', 'admin', NULL, '2c8a526a-fa8a-33ca-861d-cc917fcf2483', '53vjd39FiR', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 31),
(93, 'Audit PStore Leschmouth', 'audit.pstoreleschmouth@pstore.com', '2025-11-07 00:53:16', '$2y$10$I8FW4MaM39KVViZpD.ry2OsKDOvGEqtJQKCQd7Yk7d/oIK2aFPVrG', 'audit', 151, 'dc5169a0-963a-3aa4-acc1-74633da237a9', 'iirvZImJjb', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 31),
(94, 'Security PStore Leschmouth', 'security.pstoreleschmouth@pstore.com', '2025-11-07 00:53:16', '$2y$10$O5M.W1cI1ESLBUWceEymIO3z.iQvQojLhO5r4cxIwSw9Xoo4ahHQ6', 'security', NULL, '732743b2-6b8a-32bc-a480-8051ed35df35', '9PUlbK8fbD', '2025-11-07 00:53:16', '2025-11-07 00:53:16', 31),
(95, 'Admin PStore Harveyville', 'admin.pstoreharveyville@pstore.com', '2025-11-07 00:53:16', '$2y$10$oA30YpzoWG3lw6Sk0RrmiOlNKq4lduqOZc3Hj1zGF1hjblre.V/MC', 'admin', NULL, 'd408d2b7-1593-36c8-b72e-25ea4967def6', 'RKLGP5dXJE', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 32),
(96, 'Audit PStore Harveyville', 'audit.pstoreharveyville@pstore.com', '2025-11-07 00:53:17', '$2y$10$wNVlv0yIra3oXhnmPJKVc.hIagkjRWGgQaA3q1VuhsP6XjIGnhTze', 'audit', 156, '55d4a5a8-2816-380a-91b4-f9292db26717', 'A8FJnTrIs7', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 32),
(97, 'Security PStore Harveyville', 'security.pstoreharveyville@pstore.com', '2025-11-07 00:53:17', '$2y$10$qgyvhzfl08e70U8d4JPaQeQjWnSfoqyFJ/ItBDvDPVe/htvwnjpju', 'security', NULL, '29f32ee2-b14c-3a55-91ef-8db7de22e3bd', 'VfsBbdkL11', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 32),
(98, 'Admin PStore Lake Carolanne', 'admin.pstorelakecarolanne@pstore.com', '2025-11-07 00:53:17', '$2y$10$jqfjE21BD1YldnqUt2hIs.92bCd.Qgl16ffiDqnpDhrC8atTPbqVe', 'admin', NULL, '74235f2d-1270-37ef-be4f-88a9269c504e', 'ngssXKX0Tz', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 33),
(99, 'Audit PStore Lake Carolanne', 'audit.pstorelakecarolanne@pstore.com', '2025-11-07 00:53:17', '$2y$10$sv/niN1diC65qb/8hS0YVO7wAarYk66tA/ji4xO8n3BP2ygxhHUXq', 'audit', 161, 'd9fb7512-bb5b-3935-a362-3142ac8db001', 'c08jDil4uO', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 33),
(100, 'Security PStore Lake Carolanne', 'security.pstorelakecarolanne@pstore.com', '2025-11-07 00:53:17', '$2y$10$9Knc5ZJ8k/7SH7eyJf.n..4FtYlancLSRQe5ozXF3TmkIHWz/kEaW', 'security', NULL, 'a5962651-c51d-39c3-9c7b-63c076873d80', 'QblYCir9Yt', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 33),
(101, 'Admin PStore Conroyport', 'admin.pstoreconroyport@pstore.com', '2025-11-07 00:53:17', '$2y$10$IOBeAVB3ZL4t69LO6Keefug0ddSihuN3binWP50oZLWOQHFROWXgG', 'admin', NULL, 'c5cacdc1-058f-37e2-96d3-96b6f236b30a', 'L0e7d1DMDL', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 34),
(102, 'Audit PStore Conroyport', 'audit.pstoreconroyport@pstore.com', '2025-11-07 00:53:17', '$2y$10$K7XnBKaUDx7No85K2IWWNumrLLcrurR8JV81RYGe0ASxg7z1ccMrq', 'audit', 166, '02ae6669-f80d-30d5-a8e5-b2e756f9194d', 'F2nK4a2Nyp', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 34),
(103, 'Security PStore Conroyport', 'security.pstoreconroyport@pstore.com', '2025-11-07 00:53:17', '$2y$10$QXPVU1vaGazRX/mV2TvcD.NefalUHMbEKT0/p1VzjehePjpthp6ki', 'security', NULL, 'e65a9951-ae05-3873-9664-740201fc6579', 'f5E8VzgO7f', '2025-11-07 00:53:17', '2025-11-07 00:53:17', 34),
(104, 'Admin PStore Lake Jaylinton', 'admin.pstorelakejaylinton@pstore.com', '2025-11-07 00:53:17', '$2y$10$xuvrNdSjjuLTZtaXNFgq8uZNfEBjYumqnJPGb0cZ/rCrrV7JtIVvm', 'admin', NULL, '9145d36c-719b-3ca5-9b5f-7f2e96d36340', 'nZ0ncrRGPG', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 35),
(105, 'Audit PStore Lake Jaylinton', 'audit.pstorelakejaylinton@pstore.com', '2025-11-07 00:53:18', '$2y$10$hLq42Vq89atQuZqCtCzpFeVhNVls6ZXuR8zfSYJabRvmtwyrqm/O6', 'audit', 171, 'e09037c7-e7ae-3128-9fd2-20ff2f9e5d1c', 'cHmpqRw3mW', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 35),
(106, 'Security PStore Lake Jaylinton', 'security.pstorelakejaylinton@pstore.com', '2025-11-07 00:53:18', '$2y$10$XPYrUVhIJUTh4W8Ao/6XiOELLhGRHAtXTRu.706Fq1NwhxjBQ9ry.', 'security', NULL, '5ed78bc4-7de6-3444-ab51-10f8e2e0069a', 'DfyxxsrOjf', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 35),
(107, 'Admin PStore North Rogeliochester', 'admin.pstorenorthrogeliochester@pstore.com', '2025-11-07 00:53:18', '$2y$10$aDsoQZhYD14/.iPsppc3Ue.SpKPl2ORnGr3VzhehzrhV1IZUybz9K', 'admin', NULL, 'c1c8dbaf-c0d5-3dff-9063-0f6b192a9f7a', 'QEj7myJSVn', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 36),
(108, 'Audit PStore North Rogeliochester', 'audit.pstorenorthrogeliochester@pstore.com', '2025-11-07 00:53:18', '$2y$10$MbabYDqZVk7ZG3D1mrdBH.8Cw2Xt02neeN1ADlgqt43l4fNK0SfTy', 'audit', 176, '6b100fe3-f49e-379e-b077-687c34e1162e', '4VGaxdY8Si', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 36),
(109, 'Security PStore North Rogeliochester', 'security.pstorenorthrogeliochester@pstore.com', '2025-11-07 00:53:18', '$2y$10$/UDocno6WrufXZb1gvb/Q.dMnsFQ.EXVbjnrUi6SVExahfHrr2oeq', 'security', NULL, '24881923-2a36-3c5d-954a-364c3eb57bb7', 'yI3GqdnwkM', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 36),
(110, 'Admin PStore Lake Rey', 'admin.pstorelakerey@pstore.com', '2025-11-07 00:53:18', '$2y$10$xtIIoszKwdXiBfqJDY/Np.jhmuvkIdinCOy/cY3tk.uq537/21TgG', 'admin', NULL, 'a192b301-b65a-343c-8122-a521b57d4fa0', '788EV7xrr1', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 37),
(111, 'Audit PStore Lake Rey', 'audit.pstorelakerey@pstore.com', '2025-11-07 00:53:18', '$2y$10$ZslBTWtstWVzjifUFSAr5eVtpWBoCAODOA4N19sK91Y5/Xw1CNJZW', 'audit', 181, 'a266bb7a-3bb8-38e5-b944-a0323928d77e', 'sSWhGQHS5X', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 37),
(112, 'Security PStore Lake Rey', 'security.pstorelakerey@pstore.com', '2025-11-07 00:53:18', '$2y$10$UnSPCD5VCcsfMn/CmvtyQuC.5tSFI8NXlyObb/z5Qs5dChaDMufpK', 'security', NULL, '3517a808-3a11-372b-960a-ccb31ffc6d69', 'w405iguy3Q', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 37),
(113, 'Admin PStore Osinskimouth', 'admin.pstoreosinskimouth@pstore.com', '2025-11-07 00:53:18', '$2y$10$uwGaLEeyJq9LpM2nuw2Wvu0eOeS5LMMp/Xv8TA2xzImPH3Xjny.3y', 'admin', NULL, 'e58d97cd-7dc2-3cf0-9dc8-798a809f918f', 'sYM6i8peuT', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 38),
(114, 'Audit PStore Osinskimouth', 'audit.pstoreosinskimouth@pstore.com', '2025-11-07 00:53:18', '$2y$10$OgF1ASCz8WmGgME73m0ah.gSdoDv//54OlhnowqqKCahZZ1a1mwq2', 'audit', 186, '7e08fcf4-3e19-304a-b557-140b4aed0acf', 'ffmUR5H65B', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 38),
(115, 'Security PStore Osinskimouth', 'security.pstoreosinskimouth@pstore.com', '2025-11-07 00:53:18', '$2y$10$H47Hl14AK0kj0TFn9fdYy.3uNo.gpWTKR7yzbr7JU8cnEPTLUBoMy', 'security', NULL, '1f38c941-f308-3ce0-899f-7aa00607cc86', 'KEH1vv1PgL', '2025-11-07 00:53:18', '2025-11-07 00:53:18', 38),
(116, 'Admin PStore Port Providenci', 'admin.pstoreportprovidenci@pstore.com', '2025-11-07 00:53:18', '$2y$10$JFx1Vfl/g8q.kp2dtEQ.qeKC0SropbVVzUrj7tupgWHzSmIvMuRHm', 'admin', NULL, '9f42a1f1-4eb2-340d-a7cf-d26b5f4d1b63', 'trMzhTDtgk', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 39),
(117, 'Audit PStore Port Providenci', 'audit.pstoreportprovidenci@pstore.com', '2025-11-07 00:53:19', '$2y$10$gcFMo7XEcchVHoTC.6D2a.yVowYmfivDkfQC.nMopUcsHd3pX81Mi', 'audit', 191, '3904b9f6-1462-3d4c-82ae-0442f41ffde6', 'zfKlOOjJcT', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 39),
(118, 'Security PStore Port Providenci', 'security.pstoreportprovidenci@pstore.com', '2025-11-07 00:53:19', '$2y$10$OQ.NYQOHnjjsiE1qHnvcteM5UnwZ5GpcrZUAx3NN3nTvC4lxbmlr6', 'security', NULL, '795c88ba-ff12-378c-b3ff-5817c4738769', 'PUaeWaVO5l', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 39),
(119, 'Admin PStore Spencerview', 'admin.pstorespencerview@pstore.com', '2025-11-07 00:53:19', '$2y$10$JTRBGZYQtGu74pB8Bqqiy./y2DOQEdeuCgvIHZuIcHcfTsHuDr1bC', 'admin', NULL, '5f7cf399-daac-34c3-bb97-6bf07e3a9682', 'PghBFahdtF', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 40),
(120, 'Audit PStore Spencerview', 'audit.pstorespencerview@pstore.com', '2025-11-07 00:53:19', '$2y$10$Q7fN5oIv8eDMW7DASyp9LuEV3YOm/hTDX4N5YlutzXFj3Cv68aA6K', 'audit', 196, 'f627807a-fc19-3291-b3f9-9ff0af2ce767', 'YJGgXYMtBe', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 40),
(121, 'Security PStore Spencerview', 'security.pstorespencerview@pstore.com', '2025-11-07 00:53:19', '$2y$10$JDWDtP3644V8PMdDr6PIwuFLsZZxXgRylR6a.TGRn8jY5OaDjcku6', 'security', NULL, '38830ede-1a7a-3a18-9cdc-d285ce925e71', '7r4cIJ79pC', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 40),
(122, 'Admin PStore Haagborough', 'admin.pstorehaagborough@pstore.com', '2025-11-07 00:53:19', '$2y$10$bNgdUQKNwe0KKExA2JukUOutiGDpCvNeLH9jMlzbuUa.jWiMI17Ge', 'admin', NULL, '9f9e4561-fa23-344c-b8cd-6de195f11e42', 'mqxgsVBVGs', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 41),
(123, 'Audit PStore Haagborough', 'audit.pstorehaagborough@pstore.com', '2025-11-07 00:53:19', '$2y$10$RHsTH/G/ORU2oVzn0oaw9ePWKjzSuVDvrXW5.YTS2B6bFCSeR3APy', 'audit', 201, '114e3db4-3fce-3898-a14c-ebdd0f925e16', '8XrjwwPV2D', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 41),
(124, 'Security PStore Haagborough', 'security.pstorehaagborough@pstore.com', '2025-11-07 00:53:19', '$2y$10$SIVpNT.kLw38h.E.yRXH2e6Nbrktwm4Utxmw8W1.upkmemgOhOdX6', 'security', NULL, 'b2200894-b5a3-3041-9e13-5ea565591b1c', 'Bni3SycgVI', '2025-11-07 00:53:19', '2025-11-07 00:53:19', 41),
(125, 'Admin PStore Genesisfort', 'admin.pstoregenesisfort@pstore.com', '2025-11-07 00:53:19', '$2y$10$rIMc6aR6rfJ/zLdQSnXQYeMk4ru4ZNaofx4DAVSDHGU.hN/H3UUcy', 'admin', NULL, '95ecea1c-3854-393a-bdab-838632443f36', 'efgJ8j0rt9', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 42),
(126, 'Audit PStore Genesisfort', 'audit.pstoregenesisfort@pstore.com', '2025-11-07 00:53:20', '$2y$10$ecUMzMNcVf8aLYVmFWtKguZlAZWvgxldHcJ0VnsJHHooFa7B.L4XW', 'audit', 206, 'e944a7ba-9df4-3436-a7d4-7c428f763649', 'li9f7KPumI', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 42),
(127, 'Security PStore Genesisfort', 'security.pstoregenesisfort@pstore.com', '2025-11-07 00:53:20', '$2y$10$PH7ASJ6uMVk5cBCetNFyce0iW0R6OULC/Sm/a2/7NpybKW5sOK3nC', 'security', NULL, 'f10ad9a0-2a29-3381-b40e-9ee360184957', 'PKXwouzk5V', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 42),
(128, 'Admin PStore East Kenyon', 'admin.pstoreeastkenyon@pstore.com', '2025-11-07 00:53:20', '$2y$10$/MNP29DexpOkFSRUZhDrSeN4Tfw9hUaajBfGE3/zFDcvYXMCnoPlG', 'admin', NULL, 'f1b2bdfc-7355-38b0-9f8a-20ea134cf6f4', 'kTszkF7xlW', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 43),
(129, 'Audit PStore East Kenyon', 'audit.pstoreeastkenyon@pstore.com', '2025-11-07 00:53:20', '$2y$10$dHGpgRufkgDtFIiBXeRSf.yMdSGqIvLhtZg3JsMgG6L/ytZKo7dLO', 'audit', 211, '1af45f55-18e0-30ba-b8ba-b77a358cad80', 'lHWmliuXki', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 43),
(130, 'Security PStore East Kenyon', 'security.pstoreeastkenyon@pstore.com', '2025-11-07 00:53:20', '$2y$10$/FmB4WZq3/Vzij8.0f9q.OIxf2pK6IaAHMKb0N9lVnKeyh/b33PJO', 'security', NULL, '66ab7f98-e4bd-3ae9-9327-2d7c0df47b21', '7hRN7d3JtI', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 43),
(131, 'Admin PStore New Daphne', 'admin.pstorenewdaphne@pstore.com', '2025-11-07 00:53:20', '$2y$10$iXENU0F2t9HXc5JT5QSGy.AknDWVArQd/NDBlRbFMRnhcj70mFCLa', 'admin', NULL, '3e6eec53-01f4-312f-bc3c-b59d751e315c', 'lPvqES9lrm', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 44),
(132, 'Audit PStore New Daphne', 'audit.pstorenewdaphne@pstore.com', '2025-11-07 00:53:20', '$2y$10$z9WTZUvgP86Yt0r8zXspLe3gCzudDNibKdEBahQBKHb/8wTdsgb1m', 'audit', 216, '5ce9339e-13c0-3dab-b629-c1d7a6c321d3', '74kJR9G9qb', '2025-11-07 00:53:20', '2025-11-07 00:53:20', 44),
(133, 'Security PStore New Daphne', 'security.pstorenewdaphne@pstore.com', '2025-11-07 00:53:20', '$2y$10$i4dgeTKC8kKrrLCpe6iuv.1qAilWi6i7Ge1i.XH3auaA9yWLpUT0W', 'security', NULL, '03d04fe8-0fda-3b9e-9ee8-795cdaf9323f', 'FXdjhgNTHL', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 44),
(134, 'Admin PStore North Frederic', 'admin.pstorenorthfrederic@pstore.com', '2025-11-07 00:53:21', '$2y$10$ADlNbF/aPrloNlvmc7B/QuxUO/HsqgsBokgC0ZwoZbql4tw4luBpS', 'admin', NULL, '21b1c6af-077a-3b94-91d5-8de831e98349', 'PH4Nd1BDjq', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 45),
(135, 'Audit PStore North Frederic', 'audit.pstorenorthfrederic@pstore.com', '2025-11-07 00:53:21', '$2y$10$NhR8jtLZTsduVSxiI36CSeuFxunOWyd2NthUi22rDTuSaQxkX8X/C', 'audit', 221, 'dcd1b831-198d-39f6-8bc7-fcb32475cf1f', 'xe5Vu3uB1U', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 45),
(136, 'Security PStore North Frederic', 'security.pstorenorthfrederic@pstore.com', '2025-11-07 00:53:21', '$2y$10$FoJRMHIR8f3x5r.xzMX2juX1UGlPKqVrFlxoNsHcSEZYOAVjdb7Ke', 'security', NULL, 'e68ae7de-2e2f-3664-a824-49ca3ddd9c81', 'TjBV9BemEv', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 45),
(137, 'Admin PStore New Brionna', 'admin.pstorenewbrionna@pstore.com', '2025-11-07 00:53:21', '$2y$10$/YD1sB0c3pnyHyqhNJr01..BK9eVRzpQNKbJsLwGRf9.Vziwyu4cW', 'admin', NULL, 'c1c838cf-a106-35e6-b821-d3a68d42fc28', 'mngymKT71c', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 46),
(138, 'Audit PStore New Brionna', 'audit.pstorenewbrionna@pstore.com', '2025-11-07 00:53:21', '$2y$10$kJGfcEwopaMgRyIcBkg9XuWvsrwMcwKxdh8xard4jKD.jBCwCDAoW', 'audit', 226, '339be8f5-9a4c-3b03-ab51-564681d89645', 'fM04jyJy43', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 46),
(139, 'Security PStore New Brionna', 'security.pstorenewbrionna@pstore.com', '2025-11-07 00:53:21', '$2y$10$bpWokPF7admdD4RGqG/sleWBnxKUwyE3K0Si3F79J2baeF5igUYP.', 'security', NULL, '7d199ca8-fd2c-3aad-a9f0-34738be74bd2', 'svT9ny67q3', '2025-11-07 00:53:21', '2025-11-07 00:53:21', 46),
(140, 'Admin PStore Macejkovicside', 'admin.pstoremacejkovicside@pstore.com', '2025-11-07 00:53:21', '$2y$10$xPKm0uJB8owdNDtDW.SL7ucrUdhFcN/GIcgIsNuMkR0uvz3lf1yTe', 'admin', NULL, '65ee9b88-0ee6-3e11-8d77-184bd5ed0899', 'YvmxvEaiQ7', '2025-11-07 00:53:22', '2025-11-07 00:53:22', 47),
(141, 'Audit PStore Macejkovicside', 'audit.pstoremacejkovicside@pstore.com', '2025-11-07 00:53:22', '$2y$10$PJVqO9ekCtOuk3AMorw73.0kJXiXUNlVX8HIsBP/CH6qwKw/U0Uu.', 'audit', 231, 'aeddfdfe-1c95-361c-8580-8e3fcf3a4f89', 'raq4LbElBr', '2025-11-07 00:53:22', '2025-11-07 00:53:22', 47),
(142, 'Security PStore Macejkovicside', 'security.pstoremacejkovicside@pstore.com', '2025-11-07 00:53:22', '$2y$10$Vtfv66OtU.tGr0YfcOMq0u.3erXMbzyt5NlFCUpbI64aAjvGHE12i', 'security', NULL, '872bf2ef-d486-34c8-8427-a9c09a16928b', 'yE1Eg2opqg', '2025-11-07 00:53:22', '2025-11-07 00:53:22', 47),
(143, 'Admin PStore North Reidside', 'admin.pstorenorthreidside@pstore.com', '2025-11-07 00:53:22', '$2y$10$LVbwRnMjEpanfifFoHFwk.fEf2H01njoj4wKegW7ZDQx17qGrN8Ba', 'admin', NULL, '3f72a442-e9d9-348a-9626-0a0949709096', '5zBhYgocLZ', '2025-11-07 00:53:22', '2025-11-07 00:53:22', 48),
(144, 'Audit PStore North Reidside', 'audit.pstorenorthreidside@pstore.com', '2025-11-07 00:53:22', '$2y$10$iuvJeyGSO6mqj/TlSoxtMe9msSif5duoOV1KjIOWGd0d7DV8G8a0q', 'audit', 236, 'b8552c04-355e-33b7-9fa4-1b26e3c2877b', 'W562CebJjH', '2025-11-07 00:53:22', '2025-11-07 00:53:22', 48),
(145, 'Security PStore North Reidside', 'security.pstorenorthreidside@pstore.com', '2025-11-07 00:53:22', '$2y$10$rdUwYfXSIy.89R8pYGN4eO8yIBq3EJWZwJZmYNB3UrCpympqPOrHO', 'security', NULL, 'cca617e3-911f-32e4-b3b0-1fe3dca03728', 'mQYSXL4weY', '2025-11-07 00:53:23', '2025-11-07 00:53:23', 48),
(146, 'Admin PStore New Keaganburgh', 'admin.pstorenewkeaganburgh@pstore.com', '2025-11-07 00:53:23', '$2y$10$GUVzbDg1ytmNm4ZDbABH8O9fA1Fn4BsWPvJAWu2mxaLivBLMRrY2u', 'admin', NULL, '026f83c7-c9f6-3e0c-8f97-93eba7adb4b1', 'o1YCAx502z', '2025-11-07 00:53:23', '2025-11-07 00:53:23', 49),
(147, 'Audit PStore New Keaganburgh', 'audit.pstorenewkeaganburgh@pstore.com', '2025-11-07 00:53:23', '$2y$10$Nahzpncxz3wi90pSMdWWjeelh3tG/d6IfQac9WU8ZsyiisWG1P1bG', 'audit', 241, 'aac46d2f-ac63-375c-a155-d3a43e58b243', '3jjSyruxPO', '2025-11-07 00:53:24', '2025-11-07 00:53:24', 49),
(148, 'Security PStore New Keaganburgh', 'security.pstorenewkeaganburgh@pstore.com', '2025-11-07 00:53:24', '$2y$10$6/kfATU4TaqJTwSF52MKMu4hoUwADH3Ol0yEHrPjeWMLVAbqDlOT6', 'security', NULL, '6ebe1b7c-6a93-3412-9993-3fda015542e8', 'eyK9zwdQqe', '2025-11-07 00:53:24', '2025-11-07 00:53:24', 49),
(149, 'Admin PStore West Arch', 'admin.pstorewestarch@pstore.com', '2025-11-07 00:53:24', '$2y$10$VBTV5SZraD3syOQUCO5K6OEPebJeeoULETYt0Emztu4YP6t56rNsG', 'admin', NULL, '00620d89-4909-3170-bcd9-c303745dfa9b', 'T4st3o0BPT', '2025-11-07 00:53:24', '2025-11-07 00:53:24', 50),
(150, 'Audit PStore West Arch', 'audit.pstorewestarch@pstore.com', '2025-11-07 00:53:24', '$2y$10$jvHVarJdjonZBsOz1gv73eolAIFhDvMUrbWyYs1655J6j2qZXjfDa', 'audit', 246, '5892ef93-374a-3c6f-a447-13921214a66e', 'gQ9EKSSaMt', '2025-11-07 00:53:24', '2025-11-07 00:53:24', 50),
(151, 'Security PStore West Arch', 'security.pstorewestarch@pstore.com', '2025-11-07 00:53:24', '$2y$10$10E99Nt4Ov2LEwNaw.tXrO.rirTntC5DnFxJPGDq6NGorpcyhILSu', 'security', NULL, '6477eacc-3777-36aa-8b1a-2fb75964066d', 'whH18RkKfh', '2025-11-07 00:53:24', '2025-11-07 00:53:24', 50),
(152, 'Admin PStore Kameronside', 'admin.pstorekameronside@pstore.com', '2025-11-07 00:53:24', '$2y$10$3C2RfUFK13avWTZYf/ywVeu7hnGKW2R.mqIpyJwtzBXr/Puni0aLq', 'admin', NULL, '5c4fd558-4e16-3a9e-a2c4-f44d01ff7dbc', 'D9t58EHTDD', '2025-11-07 00:53:24', '2025-11-07 00:53:24', 51),
(153, 'Audit PStore Kameronside', 'audit.pstorekameronside@pstore.com', '2025-11-07 00:53:24', '$2y$10$K54wATe1aIlhYYDmmN3igOVpQ/17Qnu/8EaXDGNuhd5xkr2X1xqta', 'audit', 251, '5e81763f-41bc-383d-830c-edd4d7450eb5', '0UtNMgzkuR', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 51),
(154, 'Security PStore Kameronside', 'security.pstorekameronside@pstore.com', '2025-11-07 00:53:25', '$2y$10$p3KJWoBjdcC55I5wayTlze5FMaPItRfUpyzyjTvd6cLO0taYeisf.', 'security', NULL, '58e561a2-68cf-3efc-bcbe-0f2229242c88', 'TSfX1FoMRc', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 51),
(155, 'Admin PStore East Laurianne', 'admin.pstoreeastlaurianne@pstore.com', '2025-11-07 00:53:25', '$2y$10$VIN0nFIzxUwCajnf8S8eme5EXf0HcVZxCZ0qgxJ/dvWkUfp3axpJK', 'admin', NULL, '82f6ac87-8bb8-375c-beeb-2ba6d2d3ca1a', 'JxkC6qdG4a', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 52),
(156, 'Audit PStore East Laurianne', 'audit.pstoreeastlaurianne@pstore.com', '2025-11-07 00:53:25', '$2y$10$Wa5KkCDs.9RhgO7XtdD5Y.a6U.riXqnXjarHIuULyS5ThKPpXzcP2', 'audit', 256, '7a7067ea-8c87-3c4a-af4b-fe0cc5cbd8c9', '9AVPMHcbyO', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 52),
(157, 'Security PStore East Laurianne', 'security.pstoreeastlaurianne@pstore.com', '2025-11-07 00:53:25', '$2y$10$6/iObq8SYMvsLw1xRzjtbujnkORxN01Mzr5pBUALqhNelOfC8usne', 'security', NULL, '60537173-5621-3d0f-b9d9-d21d81740b87', 'YTGgaa6OIW', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 52),
(158, 'Admin PStore North Miraclemouth', 'admin.pstorenorthmiraclemouth@pstore.com', '2025-11-07 00:53:25', '$2y$10$j6OgzDKl.EKQMYkaSl9ijebxceC.Vr0FYBkiNpRJQlACbCwKtz6cm', 'admin', NULL, '91d41392-05cc-3721-b45c-48dd9a23d9d6', 'E9jzmhHpg6', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 53),
(159, 'Audit PStore North Miraclemouth', 'audit.pstorenorthmiraclemouth@pstore.com', '2025-11-07 00:53:25', '$2y$10$aK69g/fTQJgVP38obmeRauQ/Umhz1CmesChroFp0EMLyZL5BBz3zS', 'audit', 261, '74397e28-18cd-333a-80f9-639f429bd6d8', 'UG5HPthbmC', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 53),
(160, 'Security PStore North Miraclemouth', 'security.pstorenorthmiraclemouth@pstore.com', '2025-11-07 00:53:25', '$2y$10$M5hb5yjJoQrJA7pTJRInWeEvfz.MwdXNTk0FBCH4DxWAsHNJxc7Qe', 'security', NULL, '685a4f07-d4be-350a-a18b-25b286e774a3', 'yH493hn97W', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 53),
(161, 'Admin PStore Glovermouth', 'admin.pstoreglovermouth@pstore.com', '2025-11-07 00:53:25', '$2y$10$y8/Wl4ej9VlOnQposP0g0.ji92/PE0fcnHsRzHZEz54g7AlupqlOy', 'admin', NULL, '64733ac7-258f-3346-b0ef-e26482f08eaa', 'QTfB2xFjvh', '2025-11-07 00:53:25', '2025-11-07 00:53:25', 54),
(162, 'Audit PStore Glovermouth', 'audit.pstoreglovermouth@pstore.com', '2025-11-07 00:53:25', '$2y$10$Udp1XDEe0ZUMUlzhM3L7wOJgkCqInW2WrPdH9vKHYq/XmlLEMLzsG', 'audit', 266, '1aa87497-d180-3dba-a74d-858271c09de9', 'r4iwQ4PCMN', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 54),
(163, 'Security PStore Glovermouth', 'security.pstoreglovermouth@pstore.com', '2025-11-07 00:53:26', '$2y$10$YyZ1Yn/QPG8RgX5EXnEmwOCHAKuP.LCit3BAhyF/2AmI/xE1me7ne', 'security', NULL, '77417e30-0a10-3122-a37f-7f232a21b72e', '98YtErRsE4', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 54),
(164, 'Admin PStore East Fernandofort', 'admin.pstoreeastfernandofort@pstore.com', '2025-11-07 00:53:26', '$2y$10$PXeJA31L7hOYcN.WjmXkAeiKRgr8Uvlw7PALZVNz295FQwYskdc66', 'admin', NULL, '23fb5ef7-3791-38ee-b4eb-fc91d3be7765', 'y7EBZFGRVi', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 55),
(165, 'Audit PStore East Fernandofort', 'audit.pstoreeastfernandofort@pstore.com', '2025-11-07 00:53:26', '$2y$10$bAWHQ/O2RlXZFPbrJTKqYe45Pmqx27OdUIOW/G1XdHWZTpQ3CH7ee', 'audit', 271, 'b246a0f4-7873-3cbf-8c65-ce5280d57df8', 'DpjGkORjed', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 55),
(166, 'Security PStore East Fernandofort', 'security.pstoreeastfernandofort@pstore.com', '2025-11-07 00:53:26', '$2y$10$MK.naDJ84U3S.0BPq..3TuJBLIRO3Kawfx8iBPpWOClwMzOH/Hk1q', 'security', NULL, '02ccac92-7128-3f24-a3af-b4ba263729e7', 'fz5WcrMAtn', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 55),
(167, 'Admin PStore Lake Wilfred', 'admin.pstorelakewilfred@pstore.com', '2025-11-07 00:53:26', '$2y$10$G4s81l4j/3TjH0f9V.dHgu7lMsh0pmkrSMS/FfBo58NI6QfVBT/6e', 'admin', NULL, '0523cde1-ca7f-3da1-89af-76a6560afbc6', 'pPKmMKgloQ', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 56),
(168, 'Audit PStore Lake Wilfred', 'audit.pstorelakewilfred@pstore.com', '2025-11-07 00:53:26', '$2y$10$FYhQqqz71B5CvQvfYyUQCus0vCKWIau6piem4JnzIHZjjTa4oJfou', 'audit', 276, '76f4ec8e-4cb6-3a94-81dd-4553803502c3', 'fqsbM6OLhL', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 56),
(169, 'Security PStore Lake Wilfred', 'security.pstorelakewilfred@pstore.com', '2025-11-07 00:53:26', '$2y$10$Gp/8EPQ2VU9U2j3vwvYbWexK81iaXXuoNoluo0/artp01kbdum5WG', 'security', NULL, '6d6de1d4-27a7-3c0c-a390-550254f7258a', 'dn5zWy6pMY', '2025-11-07 00:53:26', '2025-11-07 00:53:26', 56),
(170, 'Admin PStore Lethachester', 'admin.pstorelethachester@pstore.com', '2025-11-07 00:53:26', '$2y$10$c5gV55IfIVFElmCyUe8cxu1z4DBd5qt/DZO2zeL0kYBY2aOoE.Na2', 'admin', NULL, '5888480d-28c3-36d1-90e5-f65438715f35', '6KADbYxqio', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 57),
(171, 'Audit PStore Lethachester', 'audit.pstorelethachester@pstore.com', '2025-11-07 00:53:27', '$2y$10$wbr6q.ykk.PRF7MGs06DmOyY7bm4Lt33tAHGrKGV2BG6hbrEtbouC', 'audit', 281, '684f13e3-d1b7-3452-ac4f-63accd1d2aa9', 'FMkc7z7cm3', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 57),
(172, 'Security PStore Lethachester', 'security.pstorelethachester@pstore.com', '2025-11-07 00:53:27', '$2y$10$1P3OpOG/7E5U/X62ZqpkLueQ.FC1KQf03QKnjdn.Q5IHRBvNWpdBq', 'security', NULL, '377086b7-ca4c-3fae-b98f-b3cb72e60697', 'lskfvSltBI', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 57),
(173, 'Admin PStore Ethanport', 'admin.pstoreethanport@pstore.com', '2025-11-07 00:53:27', '$2y$10$PfGmTQqpNQ2WbqR8HV7HvOJHTzsIBFxw7NXS.2S.cuA4zBofhh2Qu', 'admin', NULL, '770b3da4-9194-3c6a-80c7-5e9492222138', 'rBgmZb28m1', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 58),
(174, 'Audit PStore Ethanport', 'audit.pstoreethanport@pstore.com', '2025-11-07 00:53:27', '$2y$10$KUeUDTD6bT4Pd5M7BJeWs.Xm4lmSBJHSBk4svUBa7kaejS2OQf1Di', 'audit', 286, 'f6a3ebd7-f527-3aad-a5dd-95dab7de9f04', 'hg5NbPznkD', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 58),
(175, 'Security PStore Ethanport', 'security.pstoreethanport@pstore.com', '2025-11-07 00:53:27', '$2y$10$oqday9IlPe7VwNYCgnfeROCXyZn93Qr8tnisH38u5DGVSoiE99ELq', 'security', NULL, '4ac15355-db99-330f-a514-6118ee96f5e8', 'tD0W39N2cf', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 58),
(176, 'Admin PStore Binsside', 'admin.pstorebinsside@pstore.com', '2025-11-07 00:53:27', '$2y$10$7K7HyPyythw0d.Z3OA3tx..vGbGoaBOpezRmgbkMLZQoBRlj0g3q.', 'admin', NULL, '54c2bc8d-145e-329d-8160-c63909123f59', 'W8aPMTpfjE', '2025-11-07 00:53:27', '2025-11-07 00:53:27', 59);
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `division_id`, `qr_code_value`, `remember_token`, `created_at`, `updated_at`, `branch_id`) VALUES
(177, 'Audit PStore Binsside', 'audit.pstorebinsside@pstore.com', '2025-11-07 00:53:27', '$2y$10$txZ6RY0mYalvKkRnxJ1hMe0l11qut3yAUQV.tBhA5AjS3XdGcis76', 'audit', 291, '0f4bb6ef-e689-37d8-a3ec-eb599a9f4453', 'XDMKQZszvY', '2025-11-07 00:53:28', '2025-11-07 00:53:28', 59),
(178, 'Security PStore Binsside', 'security.pstorebinsside@pstore.com', '2025-11-07 00:53:28', '$2y$10$CRNIocgjsv5pP6zwkh5wAuaO1wnpB8re38u.AAJ3THZpHz7No5YzG', 'security', NULL, '6bbeb972-86cd-31ca-888e-75a1e32b4a30', '1ZEVGu3Rii', '2025-11-07 00:53:28', '2025-11-07 00:53:28', 59),
(179, 'Admin PStore Cliftonside', 'admin.pstorecliftonside@pstore.com', '2025-11-07 00:53:28', '$2y$10$x3K2KPP68vLUgwvc4Bi3q.2u5r9VsbdtGa97T36CIc43lcmOS6WDO', 'admin', NULL, 'c5a6676a-7a76-3229-babd-52a9b220a6be', 'VlauXHwvY8', '2025-11-07 00:53:28', '2025-11-07 00:53:28', 60),
(180, 'Audit PStore Cliftonside', 'audit.pstorecliftonside@pstore.com', '2025-11-07 00:53:28', '$2y$10$b8oJhhNqi.BcKLEru1OIWeRnDyUrKGGEgVHO2.Ct5sgyVi2UF/Izu', 'audit', 296, '102c70b3-2334-3533-a432-2862f1e6a49e', '56gtHRaGaA', '2025-11-07 00:53:29', '2025-11-07 00:53:29', 60),
(181, 'Security PStore Cliftonside', 'security.pstorecliftonside@pstore.com', '2025-11-07 00:53:29', '$2y$10$ccez9l5KyObj5yszA5K2PeC3hnjhkHte19lXOExeYrn4b6f.bQKkm', 'security', NULL, '919f9c54-291d-3b9f-bb17-d748c9fbd1b5', 'FUawleNWYT', '2025-11-07 00:53:30', '2025-11-07 00:53:30', 60),
(182, 'Admin PStore East Finn', 'admin.pstoreeastfinn@pstore.com', '2025-11-07 00:53:30', '$2y$10$LBktWX.7W5e/gwCp15Pmauqw/NlV2DdcRfFltn30AomKu6uy8N/rG', 'admin', NULL, 'b71fedc6-7b9e-3a58-9c98-5757c022d516', 'AndcSybZBD', '2025-11-07 00:53:30', '2025-11-07 00:53:30', 61),
(183, 'Audit PStore East Finn', 'audit.pstoreeastfinn@pstore.com', '2025-11-07 00:53:30', '$2y$10$WzTJeDUDfmbQmHdJliaCMeZ5q0x5YsFp1WUJkV7X519okze2IqdXO', 'audit', 301, '39a4c044-df52-319e-a49c-b91b1dcb7908', '88oBMC1xeL', '2025-11-07 00:53:30', '2025-11-07 00:53:30', 61),
(184, 'Security PStore East Finn', 'security.pstoreeastfinn@pstore.com', '2025-11-07 00:53:30', '$2y$10$fuTwl1iq/oc.YW6dQiIkJuvMK8uYNcnfaPvkZrAYPsbju5xSeiqfO', 'security', NULL, 'bf440f03-482e-3bba-9164-11ec9ffd1c5a', '41hlOfgwrl', '2025-11-07 00:53:30', '2025-11-07 00:53:30', 61),
(185, 'Leader Tim A', 'leader.tim.a@pstore.com', '2025-11-07 00:53:30', '$2y$10$eoSiAnfLAg62rjYQDXRPgekY3iEyZ6l2u4nEo8uGZV2BjDENvVzy2', 'leader', 1, 'a1e70da4-d315-378b-966a-7343054cedfc', 'gPcogdMpGr', '2025-11-07 00:53:30', '2025-11-07 00:53:30', 1),
(186, 'Nadia Christiansen', 'larkin.pasquale@example.net', '2025-11-07 00:53:30', '$2y$10$X9EGf1e4C.nVpn0M3HL/2.cJhGhiFGIB8dJB0Fm18krQ.PLPHScnq', 'user_biasa', 1, '79ee447f-2bda-359c-99ea-76a0954d9aea', 'lJdtcuP9KFs9SExufXeCdobGg0x6KIynifogqdDzh8nkWNAXqZorzjxpETMd', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1),
(187, 'Bridie Will', 'maxwell09@example.com', '2025-11-07 00:53:31', '$2y$10$FIhWJuMfkcsqbTSadjT4MuRUxzirBWCAuZj7OW4ncTrH3N7wuHHA2', 'user_biasa', 1, '4a13363d-d34f-3824-ba01-01c3f55d48a1', 'pEFDyYjqu3', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1),
(188, 'Luciano Haley', 'kane.smith@example.org', '2025-11-07 00:53:31', '$2y$10$P/kiP/.FcrU40wMyhR5qsOfRXjU4ffp/3Llu5Yre8MP/mbV6jb8vG', 'user_biasa', 1, '7d1a48cb-2ff6-355e-aea2-61ba0421b67a', '61Cx6nGnsV', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1),
(189, 'Grant O\'Reilly', 'kris.brady@example.net', '2025-11-07 00:53:31', '$2y$10$jrIu2NF.aNYbeWxUxQnyBexegi9p60ssr8lsqNT4sXI0TC89mSIkG', 'user_biasa', 1, '056e4cb0-a1f4-3b26-b297-0ba71c1c9a69', 'qg9xwSgDt9', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1),
(190, 'Coralie Smitham', 'jany90@example.com', '2025-11-07 00:53:31', '$2y$10$Q3ER.5ksEDGzSBw8VLQRluRKMUwE4MFN8DGqxqU7Ko7OXHh0.LOTW', 'user_biasa', 1, '8fc82953-9536-3449-a7ea-f3e2767a0ce7', 'R7wI1xOS5O', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1),
(191, 'Dell Keeling', 'arnulfo96@example.com', '2025-11-07 00:53:31', '$2y$10$ge7IhPH20l5izpzoSOFThuAYVoOxKaIKqxnT6cit8yAyvOxqi1eA6', 'user_biasa', 1, '1fc5d548-129b-3e97-965f-c9638a82e18c', 'QuAgCQBqVg', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1),
(192, 'Prof. Braden McCullough I', 'lgrant@example.com', '2025-11-07 00:53:31', '$2y$10$puhLM1lGo2ORyVeaXCC5f.MyoAxWKAoydKUYX2bVRX14Iz3X48I3a', 'user_biasa', 1, '93f83613-f5c2-3184-bc9f-b8eb3af56f98', 'u0ECwJoLUG', '2025-11-07 00:53:32', '2025-11-07 00:53:32', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_device_tokens`
--

CREATE TABLE `user_device_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_user_id_foreign` (`user_id`),
  ADD KEY `attendances_scanned_by_user_id_foreign` (`scanned_by_user_id`),
  ADD KEY `attendances_verified_by_user_id_foreign` (`verified_by_user_id`),
  ADD KEY `attendances_branch_id_foreign` (`branch_id`);

--
-- Indeks untuk tabel `audit_teams`
--
ALTER TABLE `audit_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_teams_user_id_foreign` (`user_id`),
  ADD KEY `audit_teams_division_id_foreign` (`division_id`);

--
-- Indeks untuk tabel `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `divisions_branch_id_foreign` (`branch_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `late_notifications`
--
ALTER TABLE `late_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `late_notifications_user_id_foreign` (`user_id`),
  ADD KEY `late_notifications_branch_id_foreign` (`branch_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_qr_code_value_unique` (`qr_code_value`),
  ADD KEY `users_division_id_foreign` (`division_id`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- Indeks untuk tabel `user_device_tokens`
--
ALTER TABLE `user_device_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_device_tokens_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `audit_teams`
--
ALTER TABLE `audit_teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `late_notifications`
--
ALTER TABLE `late_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT untuk tabel `user_device_tokens`
--
ALTER TABLE `user_device_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `attendances_scanned_by_user_id_foreign` FOREIGN KEY (`scanned_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendances_verified_by_user_id_foreign` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `audit_teams`
--
ALTER TABLE `audit_teams`
  ADD CONSTRAINT `audit_teams_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_teams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Ketidakleluasaan untuk tabel `late_notifications`
--
ALTER TABLE `late_notifications`
  ADD CONSTRAINT `late_notifications_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `late_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `users_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`);

--
-- Ketidakleluasaan untuk tabel `user_device_tokens`
--
ALTER TABLE `user_device_tokens`
  ADD CONSTRAINT `user_device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
