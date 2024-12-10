-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-12-09 20:05
-- 서버 버전: 10.4.24-MariaDB
-- PHP 버전: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `my_bvl`
--

-- --------------------------------------------------------

--
-- 뷰 구조 `history_counter`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `history_counter`  AS SELECT `c`.`nemonico` AS `nemonico`, count(`h`.`history_id`) AS `record_count`, min(`h`.`date`) AS `min_date`, max(`h`.`date`) AS `max_date`, CASE WHEN to_days(max(`h`.`date`)) - to_days(min(`h`.`date`)) + 1 > 0 THEN count(`h`.`history_id`) / (to_days(max(`h`.`date`)) - to_days(min(`h`.`date`)) + 1) ELSE 0 END AS `factor` FROM (`company` `c` left join `history` `h` on(`c`.`nemonico` = `h`.`nemonico`)) GROUP BY `c`.`nemonico` ;

--
-- VIEW `history_counter`
-- 데이터: 없음
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
