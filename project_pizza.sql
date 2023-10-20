-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 17, 2023 at 01:25 AM
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
-- Database: `project_pizza`
--

-- --------------------------------------------------------

--
-- Table structure for table `Basket`
--

CREATE TABLE `Basket` (
  `basket_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Crust`
--

CREATE TABLE `Crust` (
  `crust_id` int(11) NOT NULL,
  `crust_name` varchar(255) NOT NULL,
  `crust_price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `Crust`
--

INSERT INTO `Crust` (`crust_id`, `crust_name`, `crust_price`) VALUES
(1, 'บางกรอบ', 0),
(2, 'หนานุ่ม', 10),
(3, 'ขอบชีส', 20);

-- --------------------------------------------------------

--
-- Table structure for table `Item`
--

CREATE TABLE `Item` (
  `item_id` int(11) NOT NULL,
  `pizza_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `crust_id` int(11) NOT NULL,
  `Price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Order`
--

CREATE TABLE `Order` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` date DEFAULT NULL,
  `order_name` varchar(255) DEFAULT NULL,
  `order_phone` varchar(255) DEFAULT NULL,
  `order_address` text DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Pizza`
--

CREATE TABLE `Pizza` (
  `pizza_id` int(11) NOT NULL,
  `pizza_name` varchar(255) NOT NULL,
  `pizza_price` int(11) NOT NULL,
  `pizza_image` varchar(255) NOT NULL,
  `detail` varchar(255) NOT NULL,
  `guide` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `Pizza`
--

INSERT INTO `Pizza` (`pizza_id`, `pizza_name`, `pizza_price`, `pizza_image`, `detail`, `guide`) VALUES
(1, 'หมาล่าหมูสไลด์', 299, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Aug23/102783.png', 'เนื้อหมูสไลซ์, น้ำมันฮวาเจียว, พริกแห้งอบ, ต้นหอม, ผักมิกซ์, มอสซาเรลล่าชีส และซอสหมาล่า', NULL),
(2, 'กริลล์ฮาวายเอี้ยน', 509, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Nov2022/199446.png', 'แฮม, เบคอน, สับปะรด และซอสพิซซ่า', NULL),
(3, 'ดับเบิ้ลเปปเปอโรนี', 279, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/162217.png', 'เป๊ปเปอโรนี, มอสซาเรลล่าชีส และซอสพิซซ่า', NULL),
(4, 'สไปซี่ ซุปเปอร์ซีฟู๊ด', 439, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102734.png', 'ปลาหมึก, กุ้งกระเทียม, พริกแดง พริกเขียว, พริกหวาน, หอมใหญ่, อิตาเลี่ยน เบซิล, มอส', NULL),
(5, 'ไก่สามรส', 379, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102203.png', 'ไก่บาร์บีคิว, ไก่เนยกระเทียม, ไก่อบซอส, เห็ด, พริกแดง พริกเขียว, มอสซาเรลล่าชีส และซอส', NULL),
(6, 'ซีฟู้ดเดอลุกซ์', 439, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102228.png', 'กุ้ง, ปูอัด, หอมใหญ่, พริกหวาน, มอสซาเรลล่าชีส และซอสมารินาร่า', NULL),
(7, 'ค็อกเทลกุ้ง', 399, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102209.png', 'กุ้ง, เห็ด, สับปะรด, มะเขือเทศ, มอสซาเรลล่าชีส และซอสเทาซันไอส์แลนด์', NULL),
(8, 'ต้มยำกุ้ง', 329, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102212.png', 'กุ้ง, ปลาหมึก, เห็ด, มอสซาเรลล่าชีส และซอสต้มยำ', NULL),
(9, 'มีทเดอลุกซ์', 429, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102210.png', 'แฮม, เบคอน, เป๊ปเปอโรนี, ไส้กรอกรมควัน, เบคอนไดซ์, มอสซาเรลล่าชีส และซอสพิซซ่า', NULL),
(10, 'โฟร์ชีสและเบคอน', 379, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102726.png', 'เบคอนรมควัน, อเมริกันชีส, เอมเมนทอลชีส, แดรี่ วอลเลย์ พาเมซานชีส, มอสซาเรลล่าชีส', NULL),
(11, 'ผักโขมอบและมะเขือเทศ', 379, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102725.png', 'ผักโขม, เห็ด, หอมใหญ่, มะเขือเทศ, พริกแดง พริกเขียว, มอสซาเรลล่าชีส และซอสพิซซ่า', NULL),
(12, 'หมูรวมฮิต', 339, 'https://cdn.1112delivery.com/1112one/public/images/products/pizza/Topping/102723.png', 'แฮม, ไส้กรอกรมควัน, เป๊ปเปอโรนี, เห็ด, สับปะรด, มอสซาเรลล่าชีส และซอสพิซซ่า', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Size`
--

CREATE TABLE `Size` (
  `size_id` int(11) NOT NULL,
  `size_name` varchar(255) NOT NULL,
  `size_price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `Size`
--

INSERT INTO `Size` (`size_id`, `size_name`, `size_price`) VALUES
(1, 'S', 0),
(2, 'M', 10),
(3, 'L', 20),
(4, 'XL', 30);

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(15) NOT NULL,
  `position` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`user_id`, `user_name`, `email`, `password`, `address`, `phone`, `position`) VALUES
(1, 'อัษฎาวุธ', 'kkk@gmail.com', '1234', 'อินเตอร์แมนชั่น', '0959379712', '2'),
(2, 'เจ้าของร้าน', 'ajm@gmail.com', '1234', 'มหาสารคาม', '1234567890', '1'),
(3, 'กฤตสนัย', 'ttt@gmail.com', '1234', 'ไม่รู้วววว', '0987654321', '2'),
(4, 'ภูริ', 'puri@gmail.com', '1234', 'หอใน', '0981125167', '2'),
(5, 'สิริวัฒน์', 'siriwat@gmail.com', '1234', 'ไม่รู้ววววว', '0829835221', '2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Basket`
--
ALTER TABLE `Basket`
  ADD PRIMARY KEY (`basket_id`),
  ADD KEY `basket_fk_item` (`item_id`),
  ADD KEY `basket_fk_order` (`order_id`);

--
-- Indexes for table `Crust`
--
ALTER TABLE `Crust`
  ADD PRIMARY KEY (`crust_id`);

--
-- Indexes for table `Item`
--
ALTER TABLE `Item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `item_fk_pizza` (`pizza_id`),
  ADD KEY `item_fk_crust` (`crust_id`),
  ADD KEY `item_fk_size` (`size_id`);

--
-- Indexes for table `Order`
--
ALTER TABLE `Order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `order_fk_user` (`user_id`);

--
-- Indexes for table `Pizza`
--
ALTER TABLE `Pizza`
  ADD PRIMARY KEY (`pizza_id`);

--
-- Indexes for table `Size`
--
ALTER TABLE `Size`
  ADD PRIMARY KEY (`size_id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Basket`
--
ALTER TABLE `Basket`
  MODIFY `basket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Crust`
--
ALTER TABLE `Crust`
  MODIFY `crust_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Item`
--
ALTER TABLE `Item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Order`
--
ALTER TABLE `Order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Pizza`
--
ALTER TABLE `Pizza`
  MODIFY `pizza_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `Size`
--
ALTER TABLE `Size`
  MODIFY `size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Basket`
--
ALTER TABLE `Basket`
  ADD CONSTRAINT `basket_fk_item` FOREIGN KEY (`item_id`) REFERENCES `Item` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `basket_fk_order` FOREIGN KEY (`order_id`) REFERENCES `Order` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_basket_order` FOREIGN KEY (`order_id`) REFERENCES `Order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Item`
--
ALTER TABLE `Item`
  ADD CONSTRAINT `item_fk_crust` FOREIGN KEY (`crust_id`) REFERENCES `Crust` (`crust_id`),
  ADD CONSTRAINT `item_fk_pizza` FOREIGN KEY (`pizza_id`) REFERENCES `Pizza` (`pizza_id`),
  ADD CONSTRAINT `item_fk_size` FOREIGN KEY (`size_id`) REFERENCES `Size` (`size_id`);

--
-- Constraints for table `Order`
--
ALTER TABLE `Order`
  ADD CONSTRAINT `order_fk_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
