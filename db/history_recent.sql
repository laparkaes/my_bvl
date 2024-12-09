-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-12-08 23:53
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
-- 뷰 구조 `history_recent`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `history_recent`  AS SELECT `h`.`history_id` AS `history_id`, `h`.`nemonico` AS `nemonico`, `h`.`date` AS `date`, `h`.`open` AS `open`, `h`.`close` AS `close`, `h`.`high` AS `high`, `h`.`low` AS `low`, `h`.`average` AS `average`, `h`.`quantityNegotiated` AS `quantityNegotiated`, `h`.`solAmountNegotiated` AS `solAmountNegotiated`, `h`.`dollarAmountNegotiated` AS `dollarAmountNegotiated`, `h`.`yesterday` AS `yesterday`, `h`.`yesterdayClose` AS `yesterdayClose`, `h`.`currencySymbol` AS `currencySymbol` FROM (`history` `h` join (select `history`.`nemonico` AS `nemonico`,max(`history`.`date`) AS `max_date` from `history` where `history`.`close` > 0 group by `history`.`nemonico`) `latest` on(`h`.`nemonico` = `latest`.`nemonico` and `h`.`date` = `latest`.`max_date`)) WHERE `h`.`close` > 00  ;

--
-- VIEW `history_recent`
-- 데이터: 없음
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
