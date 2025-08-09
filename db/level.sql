-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-05-20 07:12:15
-- 伺服器版本： 8.0.42
-- PHP 版本： 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `sports`
--

-- --------------------------------------------------------

--
-- 資料表結構 `level`
--

CREATE TABLE `level` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `level`
--
-- 
INSERT INTO `level` (`id`, `name`) VALUES
(1, '新手級'),
(2, '熟手級'),
(3, '老手級'),
(4, '高手級');


--
-- 資料表索引 `level`
--
ALTER TABLE `level`
  ADD PRIMARY KEY (`id`);


--
-- 使用資料表自動遞增(AUTO_INCREMENT) `level`
--
ALTER TABLE `level`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

