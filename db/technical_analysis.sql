-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 24-12-09 20:08
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
-- 테이블 구조 `technical_analysis`
--

CREATE TABLE `technical_analysis` (
  `analysis_id` int(11) NOT NULL,
  `nemonico` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `adx` double DEFAULT NULL,
  `adx_pdi` double DEFAULT NULL,
  `adx_mdi` double DEFAULT NULL,
  `atr` double DEFAULT NULL,
  `bb_u` double DEFAULT NULL,
  `bb_m` double DEFAULT NULL,
  `bb_l` double DEFAULT NULL,
  `cci` double DEFAULT NULL,
  `ema_5` double DEFAULT NULL,
  `ema_20` double DEFAULT NULL,
  `ema_60` double DEFAULT NULL,
  `ema_120` double DEFAULT NULL,
  `ema_200` double DEFAULT NULL,
  `env_u` double DEFAULT NULL,
  `env_l` double DEFAULT NULL,
  `ich_a` double DEFAULT NULL,
  `ich_b` double DEFAULT NULL,
  `macd` double DEFAULT NULL,
  `macd_sig` double DEFAULT NULL,
  `macd_div` double DEFAULT NULL,
  `mfi` double DEFAULT NULL,
  `mom` double DEFAULT NULL,
  `mom_sig` double DEFAULT NULL,
  `psar` double DEFAULT NULL,
  `pch_u` double DEFAULT NULL,
  `pch_l` double DEFAULT NULL,
  `ppo` double DEFAULT NULL,
  `rsi` double DEFAULT NULL,
  `sma_5` double DEFAULT NULL,
  `sma_20` double DEFAULT NULL,
  `sma_60` double DEFAULT NULL,
  `sma_120` double DEFAULT NULL,
  `sma_200` double DEFAULT NULL,
  `sto_k` double DEFAULT NULL,
  `sto_d` double DEFAULT NULL,
  `trix` double DEFAULT NULL,
  `trix_sig` double DEFAULT NULL,
  `last_year_min` double DEFAULT NULL,
  `last_year_max` double DEFAULT NULL,
  `last_year_per` double DEFAULT NULL,
  `buy_signal` varchar(200) DEFAULT NULL,
  `buy_signal_qty` int(11) DEFAULT NULL,
  `sell_signal` varchar(200) DEFAULT NULL,
  `sell_signal_qty` int(11) DEFAULT NULL,
  `jw_factor` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `technical_analysis`
--
ALTER TABLE `technical_analysis`
  ADD PRIMARY KEY (`analysis_id`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `technical_analysis`
--
ALTER TABLE `technical_analysis`
  MODIFY `analysis_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
