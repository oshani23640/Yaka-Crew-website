-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 28, 2025 at 02:22 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yaka_crew_band`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `spotify_link` varchar(500) DEFAULT NULL,
  `apple_music_link` varchar(500) DEFAULT NULL,
  `youtube_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `band_members`
--

CREATE TABLE `band_members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `is_leader` tinyint(1) NOT NULL DEFAULT 0,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `band_members`
--

INSERT INTO `band_members` (`id`, `name`, `role`, `is_leader`, `image_path`, `created_at`, `updated_at`) VALUES
(3, 'Chanku', 'Lead vocalist', 1, 'uploads/YCHome-uploads/band_members/68a7f88a0dd76_Chanuka Mora.jpg', '2025-08-22 04:56:42', '2025-08-22 04:56:42'),
(4, 'Khanna', '2nd Lead Vocalist', 0, 'uploads/YCHome-uploads/band_members/68a7f8f57f640_Khanna.png', '2025-08-22 04:58:29', '2025-08-22 14:33:01'),
(5, 'Dilo', 'Rap Artist', 0, 'uploads/YCHome-uploads/band_members/68a8b75941dda_Dilo.png', '2025-08-22 14:29:45', '2025-08-22 18:30:49'),
(6, 'Chenna', 'Percussionist', 0, 'uploads/YCHome-uploads/band_members/68a8b76484c5f_Chenna.png', '2025-08-22 14:30:23', '2025-08-22 18:31:00'),
(7, 'Nalan', 'Bassist', 0, 'uploads/YCHome-uploads/band_members/68a8b7706ea95_Nalan.png', '2025-08-22 14:30:58', '2025-08-22 18:31:12'),
(8, 'Harith', 'Lead Guitarist', 0, 'uploads/YCHome-uploads/band_members/68a8b779c8cea_Harith.png', '2025-08-22 14:31:39', '2025-08-22 18:31:21'),
(9, 'Shavi', 'Keyboardist', 0, 'uploads/YCHome-uploads/band_members/68a8b78217812_Shavi.png', '2025-08-22 14:32:02', '2025-08-22 18:31:30'),
(10, 'Kavi', 'Drummer', 0, 'uploads/YCHome-uploads/band_members/68a8b78d717e9_Kavi.png', '2025-08-22 14:32:25', '2025-08-22 18:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `event_type` enum('concert','festival','private','charity','workshop') NOT NULL,
  `is_past_event` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `additional_info` text DEFAULT NULL,
  `is_whats_new` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--

-- --------------------------------------------------------

-- Table structure for table `event_sales`

CREATE TABLE `event_sales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `buyer_name` VARCHAR(255),
  `buyer_email` VARCHAR(255),
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example insert:
-- INSERT INTO event_sales (event_id, quantity, unit_price, total_price, buyer_name, buyer_email, sale_date)
-- VALUES (1, 2, 5000.00, 10000.00, 'John Doe', 'john@example.com', NOW());
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `location`, `price`, `event_type`, `is_past_event`, `created_at`, `updated_at`, `additional_info`, `is_whats_new`) VALUES
(5, 'Kande Sajje', 'Experience an unforgettable night of music, passion, and pure entertainment!\r\nThis live concert brings together a star-studded lineup of your favorite performers, ready to set the stage on fire with powerful vocals, energetic beats, and captivating performances.\r\n\r\nFrom soulful melodies to electrifying hits, every moment is designed to keep you singing, dancing, and living the rhythm. This isn’t just a concert—it’s a celebration of music, unity, and unforgettable vibes.\r\n\r\n✨ Don’t miss your chance to be part of this one-of-a-kind musical journey. Secure your tickets today and get ready for an evening you’ll remember forever!', '2025-09-13', '18:00:00', '00:00:00', 'Kandy', 3000.00, 'concert', 0, '2025-08-23 04:27:13', '2025-08-23 04:27:13', NULL, 1),
(8, 'Mathakayan', 'Get ready for a night where legends, rising stars, and powerhouse performers share one stage! 🌟 This live concert promises an extraordinary blend of timeless classics, high-energy hits, and unforgettable collaborations that will light up the night with pure musical magic. 🎤🔥\r\n\r\nFrom dynamic bands to sensational solo acts, the lineup is stacked with the biggest names and fresh talents who will keep the crowd on their feet from start to finish. Expect dazzling performances, explosive energy, and a musical journey like no other.\r\n\r\n✨ Don’t miss your chance to witness this unforgettable celebration of music, rhythm, and entertainment. Secure your tickets now and be part of an experience that will be remembered long after the final note.', '2025-09-20', '18:00:00', '23:00:00', 'Lotus Tower Colombo', 2000.00, 'concert', 0, '2025-08-24 02:47:54', '2025-08-24 02:47:54', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `event_images`
--

CREATE TABLE `event_images` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_images`
--

INSERT INTO `event_images` (`id`, `event_id`, `image_path`, `is_primary`) VALUES
(1, 1, 'event_689d5491d2cc31.12851061.jpeg', 1),
(2, 2, 'event_689dde184cc262.95007773.jpeg', 1),
(3, 3, 'event_689e2568944a82.13055791.jpeg', 1),
(4, 4, 'event_68a93f57b1c2a9.51076446.jpeg', 1),
(9, 5, 'event_68a945b70159e9.33428504.jpg', 1),
(10, 6, 'event_68a94727d02c51.80630674.jpg', 1),
(11, 7, 'event_68aa7a2a1255f7.22272471.jpg', 1),
(12, 8, 'event_68aa7d5acf5137.99507970.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'event',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_published` tinyint(1) DEFAULT 1,
  `likes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `content`, `event_date`, `event_time`, `location`, `image_path`, `category`, `created_at`, `is_published`, `likes`) VALUES
(36, 'Coke Kottu Beat Party', '', '2024-05-14', NULL, 'Ambalangoda', 'uploads/Gallery/YCposts/post_68a8b8cc5e50b4.43223844.jpeg', 'event', '2025-08-22 15:10:57', 1, 0),
(37, 'Wannama', '', '2025-06-20', NULL, 'Katunayake', 'uploads/Gallery/YCposts/post_68a8b8c3235892.33741434.jpg', 'event', '2025-08-22 15:14:42', 1, 0),
(39, 'Wannama', '', '2025-06-20', NULL, 'Katunayake', 'uploads/Gallery/YCposts/post_68a8b8bbb29d32.21500598.jpg', 'event', '2025-08-22 15:18:28', 1, 0),
(40, 'Wannama', '', '2025-06-20', NULL, 'Katunayake', 'uploads/Gallery/YCposts/post_68a8b83e3374e2.71852623.jpg', 'event', '2025-08-22 15:19:59', 1, 0),
(41, 'Coke Kottu Beat Party', '', '2024-05-24', NULL, 'Ambalangoda', 'uploads/Gallery/YCposts/post_68a8b83589ada1.11506246.jpg', 'event', '2025-08-22 15:31:30', 1, 0),
(42, 'Battle of Maroons', '', '2025-03-04', NULL, 'Colombo', 'uploads/Gallery/YCposts/post_68a8b82d2832d6.17637525.jpg', 'concert', '2025-08-22 15:32:28', 1, 0),
(43, 'Battle of Maroons', '', '2025-03-04', NULL, 'Colombo', 'uploads/Gallery/YCposts/post_68a8b823802015.41102562.jpg', 'event', '2025-08-22 15:34:21', 1, 0),
(44, 'Battle of Maroons', '', '2025-03-04', NULL, 'Colombo', 'uploads/Gallery/YCposts/post_68a8b81b6fa6c1.44814964.jpg', 'event', '2025-08-22 15:34:57', 1, 0),
(45, 'ODYSSEY MMXXV', '', '2025-03-07', NULL, 'SLIIT Malabe', 'uploads/Gallery/YCposts/post_68a8b80dcc59d0.31894808.jpeg', 'concert', '2025-08-22 17:24:03', 1, 0),
(46, 'Ravana', '', '2024-11-25', NULL, 'Nelum Pokuna Mahinda Rajapaksa Theatre', 'uploads/Gallery/YCposts/post_68a8b801d1a4d4.75961863.jpg', 'concert', '2025-08-22 17:25:11', 1, 1),
(47, 'InterFlash', '', '2023-12-29', NULL, 'Nelum Kuluna Colombo', 'uploads/Gallery/YCposts/post_68a8b7f27a61c6.99927124.jpeg', 'concert', '2025-08-22 17:27:29', 1, 1),
(48, 'Yaka', '', '2024-08-08', NULL, 'Colombo', 'uploads/Gallery/YCposts/post_68a8b7df8b5551.51396178.jpeg', 'event', '2025-08-22 17:31:28', 1, 1),
(50, 'Padavi Sri Pura Yaka Live in Concert', '', '2025-05-29', NULL, 'Padavi Sri Pura ', 'uploads/Gallery/YCposts/1756221729_post_image13.jpg', 'concert', '2025-08-26 15:22:09', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `slider_images`
--

CREATE TABLE `slider_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `slider_images`
--

INSERT INTO `slider_images` (`id`, `image_path`, `caption`, `is_active`, `created_at`) VALUES
(8, 'slider_68a93f7e7e83b1.25612130.jpeg', 'Slide 1', 1, '2025-08-23 04:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `artist_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `album_id` int(11) DEFAULT NULL,
  `duration` varchar(10) DEFAULT NULL,
  `track_number` int(11) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `audio_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `category` varchar(100) DEFAULT NULL,
  `music_category` varchar(100) DEFAULT NULL,
  `hits` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `title`, `artist_name`, `description`, `album_id`, `duration`, `track_number`, `cover_image`, `audio_path`, `created_at`, `updated_at`, `is_active`, `category`, `music_category`, `hits`) VALUES
(75, 'Pinna pipena', 'Chanuka Mora with Naduni Yameesha', 'Yaka Crew, a vibrant musical ensemble, combines diverse talents to create a unique sonic experience.  @ChanukaMora  , the driving force behind Yaka Crew, leads with captivating vocals and musical prowess.  @dilohiphop    the rap virtuoso of the band, injects dynamic energy into Yaka Crew performances. Known for their versatility, Yaka Crew thrives in both concert halls and wedding celebrations, delivering an unforgettable musical backdrop. Brimming with youthful vigor and accomplished musicians, Yaka Crew embodies a harmonious fusion of emerging talent and seasoned expertise.\r\n', NULL, NULL, NULL, 'uploads/covers/YCaudio/audio_cover_5.jpeg', 'uploads/YCaudio/1755053753_media_Pinna Pipena Medley .mp3', '2025-08-13 02:55:53', '2025-08-13 02:56:04', 1, 'top', 'country', 300000),
(76, 'Ravana', 'Yaka Crew', '\r\nYaka Crew, a vibrant musical ensemble, combines diverse talents to create a unique sonic experience.  @ChanukaMora  , the driving force behind Yaka Crew, leads with captivating vocals and musical prowess.  @dilohiphop    the rap virtuoso of the band, injects dynamic energy into Yaka Crew performances. Known for their versatility, Yaka Crew thrives in both concert halls and wedding celebrations, delivering an unforgettable musical backdrop. Brimming with youthful vigor and accomplished musicians, Yaka Crew embodies a harmonious fusion of emerging talent and seasoned expertise.\r\n', NULL, NULL, NULL, 'uploads/covers/YCaudio/audio_cover_4.jpeg', 'uploads/YCaudio/1755056046_media_Ravana .mp3', '2025-08-13 03:34:06', '2025-08-13 03:34:06', 1, 'latest', 'hip_hop', 0),
(78, 'Nona', 'Yaka Crew', 'Music by - Chanuka Mora\r\nMelody by - Chanuka Mora\r\nLyrics - Ushani Wijewantha\r\nMusic Arrangments - Yaka crew\r\nMixed & Mastered - Ravindra Srinath\r\nDance Choreography - Ramod Malaka\r\nDirected by - Chanuka Mora\r\nDirector of Photography - Viyath Deelaka\r\nVideo Edit - Chanuka Mora\r\n\r\nCast\r\nAmaya Nanayakkara\r\nPabasara Samodana', NULL, NULL, NULL, 'uploads/covers/YCaudio/audio_cover_7.jpeg', 'uploads/YCaudio/1755069103_media_nona-yaka-crew.mp3', '2025-08-13 07:11:43', '2025-08-13 07:11:43', 1, 'latest', 'rock', 0),
(79, 'Deviyo Wadi', 'Yaka Crew', 'Music - Chanuka Mora\r\nMelody - Chanuka Mora\r\nLyrics - Chanuka Mora\r\nMusic Arrangments - Yakacrew\r\nBass - Nisal Jayaweera\r\nLead - Harith Wijayawardane\r\nKeys - Nadun Bandara\r\nMixed & Mastered - Azim Ousman\r\nArt Work - Viyath Deelaka ', NULL, NULL, NULL, 'uploads/covers/YCaudio/audio_cover_8.jpeg', 'uploads/YCaudio/1755070316_media_deviyo wadi.mp3', '2025-08-13 07:31:56', '2025-08-13 07:31:56', 1, 'latest', 'hip_hop', 0),
(82, 'AWURUDDAI HINAHENA', 'Yaka Crew', 'Music by - Chanuka Mora\r\nMelody by - Chanuka Mora\r\nLyrics - Chanuka Mora\r\nRap Lyrics - Dilo\r\nMusic Arrangments - Yakacrew\r\nMixed & Mastered - Azim Ousman\r\n\r\nVideo Concept - Pasindu Chamara\r\nVideo Director - Viyath Deelaka\r\nDirector of Photography - Viyath Deelaka', NULL, NULL, NULL, 'uploads/covers/YCaudio/audio_cover_9.jpeg', 'uploads/YCaudio/1755075294_media_Awuruddai hinahena.mp3', '2025-08-13 08:54:54', '2025-08-13 08:54:54', 1, 'latest', 'pop', 0),
(87, 'Sinha Naade ', 'Yaka Crew', 'We\'ve been through good times and tough times\r\nBut the Lions always get back up\r\nThis one is for the legends at Sri Lanka Cricket\r\nAll the best for the ICC Men\'s T20 World Cup 2021\r\n\r\nPerformed by - Yaka Crew\r\nMusic Composed & Produced by - Chanuka Mora\r\nLyrics -  Yaka Crew | Ushani Wijewantha\r\nMusic Arrangments - Yaka Crew\r\n\r\nMixing & Mastering - Azim Ousman\r\n\r\nCinematographer - Viyath Deelaka | Nisitha Weerasinghe\r\nEditor - Chanuka Mora | Viyath Deelaka | Nisitha Weerasinghe\r\n\r\nSpecial Thanks\r\n\r\nAmma \r\nThaththa\r\nTharaka Senevirathne\r\nMy Boys (Yaka Family)\r\nWaruna\r\n', NULL, NULL, NULL, 'uploads/Gallery/covers/YCaudio/audio_cover_68a8b3fb1d3f16.24551833.jpg', 'uploads/Gallery/YCaudio/1755886526_media_Sinha Naade .mp3', '2025-08-22 18:15:26', '2025-08-22 18:40:58', 1, 'latest', 'pop', 0),
(88, 'Sangeeth Tribute Medley ', 'Chanuka Mora', '\r\nThis is a heartfelt tribute to the one and only living legend, Sangeeth Wijesooriya. For the first time ever, a rising young band Yaka Crew takes on these legendary songs with respect, energy, and soul.\r\nWe grew up listening to his voice. Now we honor it with ours.\r\nHope you feel the love in every note. \r\n\r\nA massive thank you to every single soul who stood behind this project.\r\nI won’t list names here, not out of disrespect, but to preserve the clean focus of this tribute.\r\nBut know this: you are the reason this happened.\r\nTo the Lighting crew, LED team, Sound crew, Engineers, Video team, Backing Vocal Crew and my beloved friends! you are the best.Your effort, your energy, your belief… I’ll carry it with me always.', NULL, NULL, NULL, 'uploads/Gallery/covers/YCaudio/audio_cover_12.jpeg', 'uploads/Gallery/YCaudio/1755886835_media_Sangeeth .mp3', '2025-08-22 18:20:35', '2025-08-22 18:39:33', 1, 'top', 'jazz', 700000),
(90, 'Neththara Medley', 'Yaka Crew', 'Yaka Crew, a vibrant musical ensemble, combines diverse talents to create a unique sonic experience. ‪@ChanukaMora‬  , the driving force behind Yaka Crew, leads with captivating vocals and musical prowess. ‪@dilohiphop‬ the rap virtuoso of the band, injects dynamic energy into Yaka Crew performances. Known for their versatility, Yaka Crew thrives in both concert halls and wedding celebrations, delivering an unforgettable musical backdrop. Brimming with youthful vigor and accomplished musicians, Yaka Crew embodies a harmonious fusion of emerging talent and seasoned expertise.', NULL, NULL, NULL, 'uploads/Gallery/covers/YCaudio/audio_cover_14.jpeg', 'uploads/Gallery/YCaudio/1755889435_media_Neththara Medley (නේත්තරා) - Yaka Crew 2025 - Yaka Crew.mp3', '2025-08-22 19:03:55', '2025-08-22 19:03:55', 1, 'top', 'rock', 1400000),
(91, 'Kanda Gena ', 'Chanuka Mora ft. Shavindya', ' Artist -  Chanuka Mora | @ShavindyaKariyawasam \r\n♪ Music & Melody - Chanuka Mora\r\n♪ Lyrics - Ushani Wijewantha | Chanuka Mora\r\n♪ Producer - Chanuka Mora\r\n♪ Bass - Visler\r\n♪ Flute - Sasrika Randimal\r\n♪ Keys - Shavinda Madugallage\r\n♪ Mix & Master - Ravindra Srinath\r\n\r\nEpisode Script - Chanuka Mora\r\nDirector / Thulara Bethmage\r\nAssistant Director / Hashan De Alwis\r\nCinematography / Isa Asif\r\nEdit & Colorgrade / Thulara Bethmage\r\nStarring - Raleesha Fernando\r\nLocation Partner - Tunnel Lounge ', NULL, NULL, NULL, 'uploads/Gallery/covers/YCaudio/audio_cover_15.jpeg', 'uploads/Gallery/YCaudio/audio_68a94f6a7f6949.77842411.mp3', '2025-08-22 19:14:59', '2025-08-23 05:19:38', 1, 'top', 'classical', 8500000),
(92, 'Sundara Landa', 'Chanuka Mora with Zany Inzane, Khanna ', '\r\n♪ Artist - ‪@ChanukaMora‬  X ‪@ZanyInzane‬  X ‪@khannasl‬ \r\n♪ Music & Melody - Chanuka Mora\r\n♪ Lyrics - Chanuka Mora | Ushani Wijewantha\r\n♪ Rap Lyrics - Zany Inzane\r\n♪ Producer - Chanuka Mora\r\n♪ Co-Producer - Visler\r\n♪ Mix & Master - Lahiru De Costa\r\n\r\nVideo Director - Dopie\r\nCinematographer - Dopie\r\nEditor - Dopie\r\n\r\nCast- \r\nNamal Santhu\r\nCharmini\r\n', NULL, NULL, NULL, 'uploads/Gallery/covers/YCaudio/audio_cover_16.jpeg', 'uploads/Gallery/YCaudio/1755890729_media_Chanuka Mora - Sundara Landa සුන්දර ලන්දා  Zany Inzane, Khanna (Official Music Video) - Chanuka Mora.mp3', '2025-08-22 19:25:29', '2025-08-22 19:25:29', 1, 'top', 'classical', 7100000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video`
--

CREATE TABLE `video` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cover_image` varchar(1024) DEFAULT NULL,
  `music_category` varchar(100) DEFAULT NULL,
  `artist_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `video_path` varchar(1024) DEFAULT NULL,
  `upload_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video`
--

INSERT INTO `video` (`id`, `title`, `cover_image`, `music_category`, `artist_name`, `description`, `video_path`, `upload_date`) VALUES
(3, 'Ravana', 'uploads/Gallery/covers/YCvideos/video_cover_7.jpeg', 'traditional', 'Yaka Crew', 'Music - Chanuka Mora\r\nMelody - Chanuka Mora\r\nLyrics - Chanuka Mora\r\nRap Lyrics - Dilo\r\nMix Mastered - Ravindra Srinath\r\nVideo - Senura Jay\r\n\r\n\r\nYaka Crew, a vibrant musical ensemble, combines diverse talents to create a unique sonic experience.  @ChanukaMora  , the driving force behind Yaka Crew, leads with captivating vocals and musical prowess.  @dilohiphop    the rap virtuoso of the band, injects dynamic energy into Yaka Crew performances. Known for their versatility, Yaka Crew thrives in both concert halls and wedding celebrations, delivering an unforgettable musical backdrop. Brimming with youthful vigor and accomplished musicians, Yaka Crew embodies a harmonious fusion of emerging talent and seasoned expertise.', 'uploads/Gallery/YCvideos/1755787667_media_videoplayback.mp4', '2025-08-21 20:17:47'),
(5, 'Sangeeth Tribute Medley ', 'uploads/Gallery/covers/YCvideos/video_cover_9.jpeg', 'jazz', 'Chanuka Mora', 'This is a heartfelt tribute to the one and only living legend, Sangeeth Wijesooriya. For the first time ever, a rising young band Yaka Crew takes on these legendary songs with respect, energy, and soul.\r\nWe grew up listening to his voice. Now we honor it with ours.\r\nHope you feel the love in every note. \r\n\r\nA massive thank you to every single soul who stood behind this project.\r\nI won’t list names here, not out of disrespect, but to preserve the clean focus of this tribute.\r\nBut know this: you are the reason this happened.\r\nTo the Lighting crew, LED team, Sound crew, Engineers, Video team, Backing Vocal Crew and my beloved friends! you are the best.Your effort, your energy, your belief… I’ll carry it with me always.', 'uploads/Gallery/YCvideos/1755887147_media_Sangeeth Tribute Medley (සංගීත්) - Yaka Crew 2025  EP 01 - Yaka Crew (720p, h264).mp4', '2025-08-22 23:55:47'),
(6, 'Sundara Landa', 'uploads/Gallery/covers/YCvideos/video_cover_10.jpeg', 'hip_hop', 'Chanuka Mora with Zany Inzane, Khanna ', '♪ Artist - ‪@ChanukaMora‬  X ‪@ZanyInzane‬  X ‪@khannasl‬ \r\n♪ Music & Melody - Chanuka Mora\r\n♪ Lyrics - Chanuka Mora | Ushani Wijewantha\r\n♪ Rap Lyrics - Zany Inzane\r\n♪ Producer - Chanuka Mora\r\n♪ Co-Producer - Visler\r\n♪ Mix & Master - Lahiru De Costa\r\n\r\nVideo Director - Dopie\r\nCinematographer - Dopie\r\nEditor - Dopie\r\n\r\nCast- \r\nNamal Santhu\r\nCharmini', 'uploads/Gallery/YCvideos/1755890956_media_Chanuka Mora - Sundara Landa සුන්දර ලන්දා  Zany Inzane, Khanna (Official Music Video) - Chanuka Mora (720p, h264).mp4', '2025-08-23 00:59:16');

-- --------------------------------------------------------

--
-- Table structure for table `whats_new`
--

CREATE TABLE `whats_new` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whats_new`
--

INSERT INTO `whats_new` (`id`, `title`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'New Lead Vocalist Joins Yaka Crew!', 'We are thrilled to announce that Khanna has joined Yaka Crew as our new lead vocalist! With a powerful voice and a passion for music, Khanna brings fresh energy and excitement to the band. Get ready for an amazing new era of performances and original music with Khanna leading the way. Stay tuned for upcoming shows and releases!', 'uploads/YCHome-uploads/whats_new/68a8b79739a4d_Khanna-2.png', 1, '2025-08-22 14:33:57', '2025-08-22 18:31:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `band_members`
--
ALTER TABLE `band_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_images`
--
ALTER TABLE `event_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_date` (`event_date`),
  ADD KEY `idx_posts_category` (`category`);

--
-- Indexes for table `slider_images`
--
ALTER TABLE `slider_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_songs_album` (`album_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `whats_new`
--
ALTER TABLE `whats_new`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `band_members`
--
ALTER TABLE `band_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `event_images`
--
ALTER TABLE `event_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `slider_images`
--
ALTER TABLE `slider_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video`
--
ALTER TABLE `video`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `whats_new`
--
ALTER TABLE `whats_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
