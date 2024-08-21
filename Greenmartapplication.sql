-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2024 at 07:51 AM
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
-- Database: `greenmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`) VALUES
(1, 'Fruits & Vegetables', 'fruits_vegetables.jpg'),
(2, 'Dairy Products', 'dairy_products.jpg'),
(3, 'Bakery Items', 'bakery_items.jpg'),
(4, 'Beverages', 'beverages.jpg'),
(5, 'Snacks', 'snacks.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `complaint_text` text NOT NULL,
  `reply_text` text DEFAULT NULL,
  `complaint_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reply_date` timestamp NULL DEFAULT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `customer_id`, `complaint_text`, `reply_text`, `complaint_date`, `reply_date`, `status`) VALUES
(2, 2, 'I have not received my order yet.', 'ok', '2024-08-20 05:39:53', '2024-08-20 05:53:47', 'resolved'),
(5, 2, 'I have not received my package yet despite the expected delivery date being passed.', 'hii', '2024-08-20 05:57:02', '2024-08-20 06:14:19', 'resolved'),
(6, 2, 'need other thing', NULL, '2024-08-20 02:30:44', NULL, 'pending'),
(8, 1, 'i have got the defective product', 'we will take care of that i team will connect with you', '2024-08-20 03:49:10', '2024-08-20 09:38:09', 'resolved'),
(9, 1, 'i have got the defective product', NULL, '2024-08-20 03:49:23', NULL, 'pending'),
(10, 1, 'i have got the defective product', NULL, '2024-08-20 03:49:45', NULL, 'pending'),
(11, 1, 'i have got the defective product', NULL, '2024-08-20 03:50:23', NULL, 'pending'),
(12, 1, 'i have got the defective product', NULL, '2024-08-20 03:50:50', NULL, 'pending'),
(13, 1, 'i have got the defective product', NULL, '2024-08-20 03:51:06', NULL, 'pending'),
(14, 1, 'i have recived the product with diffrent quality', 'it will resonve by my teammy team will contact you thankyou', '2024-08-20 06:06:55', '2024-08-20 09:39:13', 'resolved'),
(15, 1, 'please exchange the product', NULL, '2024-08-20 06:07:08', NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `discount_type` enum('category','subcategory','product') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement_units`
--

CREATE TABLE `measurement_units` (
  `id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `measurement_units`
--

INSERT INTO `measurement_units` (`id`, `subcategory_id`, `unit`) VALUES
(1, 1, 'kg'),
(2, 1, 'g'),
(3, 1, 'box'),
(4, 1, 'bag'),
(5, 1, 'bunch'),
(6, 2, 'liter'),
(7, 2, 'piece'),
(8, 2, 'cup'),
(9, 2, 'pack'),
(10, 2, 'tub'),
(11, 3, 'loaf'),
(12, 3, 'piece'),
(13, 3, 'packet'),
(14, 3, 'cake'),
(15, 3, 'box'),
(16, 4, 'bottle'),
(17, 4, 'can'),
(18, 4, 'cup'),
(19, 4, 'packet'),
(20, 4, 'bottle'),
(21, 5, 'packet'),
(22, 5, 'bag'),
(23, 5, 'box'),
(24, 5, 'box'),
(25, 5, 'bag');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `payment_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_date` date DEFAULT NULL,
  `shipping_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_status`, `created_at`, `order_date`, `shipping_id`, `payment_method`, `payment_reference`) VALUES
(1, 3, 420.00, 'delivered', 'Paid', '2024-08-11 11:41:02', '2024-08-11', 1, 'UPI', 'REF-1723376438047-UJ4QSQ'),
(2, 3, 580.00, 'canceled', 'Paid', '2024-08-11 11:48:59', '2024-08-11', 1, 'Credit Card', 'REF-1723376934296-H2958D'),
(3, 3, 580.00, 'delivered', 'Paid', '2024-08-11 12:15:56', '2024-08-11', 1, 'Credit Card', 'REF-1723378473313-NYL2H5'),
(4, 3, 200.00, 'delivered', 'Paid', '2024-08-11 12:42:35', '2024-08-11', 1, 'UPI', 'REF-1723380123931-EEPPVL'),
(5, 2, 80.00, 'delivered', 'Paid', '2024-08-11 13:30:18', '2024-08-11', 2, 'UPI', 'REF-1723382950051-EX63W2'),
(6, 2, 240.00, 'delivered', 'Paid', '2024-08-12 05:21:55', '2024-08-12', 2, 'Credit Card', 'REF-1723440104233-XCRIZY'),
(7, 2, 100.00, 'delivered', 'Paid', '2024-08-12 05:26:29', '2024-08-12', 2, 'UPI', 'REF-1723440381760-G7P0OF'),
(8, 2, 40.00, 'delivered', 'Paid', '2024-08-12 07:24:28', '2024-08-12', 2, 'UPI', 'REF-1723447447042-XH2699'),
(9, 2, 100.00, 'canceled', 'Paid', '2024-08-12 07:26:40', '2024-08-12', 2, 'Credit Card', 'REF-1723447588234-9MFJPD'),
(10, 2, 40.00, 'delivered', 'Paid', '2024-08-12 07:27:39', '2024-08-12', 2, 'Credit Card', 'REF-1723447648771-5KLULU'),
(11, 2, 580.00, 'delivered', 'Paid', '2024-08-12 07:29:14', '2024-08-12', 2, 'UPI', 'REF-1723447746107-35TTT0'),
(12, 2, 40.00, 'delivered', 'Paid', '2024-08-12 08:33:09', '2024-08-12', 2, 'UPI', 'REF-1723451583692-JWNN0C'),
(13, 2, 200.00, 'delivered', 'Paid', '2024-08-16 07:21:51', '2024-08-16', 2, 'Credit Card', 'REF-1723792903610-3VQGEE'),
(14, 2, 160.00, 'delivered', 'Paid', '2024-08-16 07:22:50', '2024-08-16', 2, 'Credit Card', 'REF-1723792962185-K7S1JL'),
(15, 2, 160.00, 'delivered', 'Paid', '2024-08-16 07:23:48', '2024-08-16', 2, 'Credit Card', 'REF-1723793021349-VERTP9'),
(16, 2, 160.00, 'delivered', 'Paid', '2024-08-16 07:25:10', '2024-08-16', 2, 'UPI', 'REF-1723793103652-2QHVET'),
(17, 2, 240.00, 'delivered', 'Paid', '2024-08-16 07:26:20', '2024-08-16', 2, 'Credit Card', 'REF-1723793165837-XJF3KV'),
(18, 2, 480.00, 'canceled', 'Paid', '2024-08-16 07:28:21', '2024-08-16', 2, 'Credit Card', 'REF-1723793294868-IGLCIL'),
(19, 2, 120.00, 'canceled', 'Paid', '2024-08-16 07:31:15', '2024-08-16', 2, 'Credit Card', 'REF-1723793468906-MTHYGQ'),
(20, 2, 40.00, 'canceled', 'Paid', '2024-08-16 07:46:59', '2024-08-16', 2, 'Credit Card', 'REF-1723794412033-HMUG5C'),
(21, 2, 160.00, 'canceled', 'Paid', '2024-08-16 07:48:28', '2024-08-16', 2, 'UPI', 'REF-1723794502215-K10P0R'),
(22, 2, 120.00, 'delivered', 'Paid', '2024-08-16 07:48:58', '2024-08-16', 2, 'Credit Card', 'REF-1723794532813-JSQZKD'),
(23, 2, 120.00, 'delivered', 'Paid', '2024-08-16 07:50:06', '2024-08-16', 2, 'Credit Card', 'REF-1723794600689-SCMKTP'),
(24, 2, 120.00, 'delivered', 'Paid', '2024-08-16 07:52:34', '2024-08-16', 2, 'Credit Card', 'REF-1723794748493-N1AODU'),
(25, 2, 650.00, 'delivered', 'Paid', '2024-08-16 10:00:06', '2024-08-16', 2, 'Credit Card', 'REF-1723802382016-0UDEQI'),
(26, 2, 890.00, 'delivered', 'Paid', '2024-08-18 07:02:50', '2024-08-18', 3, 'Credit Card', 'REF-1723964554739-KM49OZ'),
(27, 2, 200.00, 'delivered', 'Paid', '2024-08-18 07:52:22', '2024-08-18', 2, 'Credit Card', ''),
(28, 2, 690.00, 'delivered', 'Paid', '2024-08-18 07:55:54', '2024-08-18', 3, 'UPI', ''),
(29, 2, 300.00, 'delivered', 'Paid', '2024-08-18 07:57:43', '2024-08-18', 2, 'UPI', ''),
(30, 2, 910.00, 'delivered', 'Paid', '2024-08-18 08:54:42', '2024-08-18', 2, 'UPI', ''),
(31, 2, 860.00, 'delivered', 'Paid', '2024-08-19 06:37:45', '2024-08-19', 2, 'Credit Card', 'REF-1724049415858-6KW41N'),
(32, 2, 130.00, 'delivered', 'Paid', '2024-08-19 06:40:21', '2024-08-19', 2, 'Credit Card', 'REF-1724049610337-7W22CC'),
(33, 2, 100.00, 'delivered', 'Paid', '2024-08-19 06:56:54', '2024-08-19', 3, 'Credit Card', 'REF-1724050591976-EGQVI3'),
(34, 2, 130.00, 'shipped', 'Paid', '2024-08-19 06:59:45', '2024-08-19', 2, 'Credit Card', 'REF-1724050759729-SIP0VY'),
(35, 2, 300.00, 'Cancelled', 'Paid', '2024-08-20 06:36:11', '2024-08-20', 2, 'UPI', 'REF-1724135761894-VFYHDF'),
(36, 2, 520.00, 'Cancelled', 'Paid', '2024-08-20 08:27:22', '2024-08-20', 2, 'UPI', 'REF-1724142432338-HRN9DH'),
(37, 2, 600.00, 'Pending', 'Paid', '2024-08-20 08:30:27', '2024-08-20', 2, 'UPI', 'REF-1724142618345-NIYW9A'),
(38, 2, 190.00, 'Pending', 'Paid', '2024-08-20 08:31:46', '2024-08-20', 3, 'Credit Card', 'REF-1724142682983-BNL71F'),
(39, 2, 2700.00, 'Pending', 'Paid', '2024-08-20 08:59:39', '2024-08-20', 2, 'Credit Card', 'REF-1724144336409-KCVFEK'),
(40, 2, 100.00, 'Pending', 'Paid', '2024-08-20 09:01:34', '2024-08-20', 2, 'UPI', 'REF-1724144483911-10LRN7');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 5, 5, 40.00),
(2, 1, 6, 1, 100.00),
(3, 1, 4, 1, 80.00),
(4, 1, 3, 1, 40.00),
(5, 2, 4, 2, 80.00),
(6, 2, 5, 3, 40.00),
(7, 2, 6, 3, 100.00),
(8, 3, 9, 1, 580.00),
(9, 4, 5, 5, 40.00),
(10, 5, 4, 1, 80.00),
(11, 6, 4, 3, 80.00),
(12, 7, 6, 1, 100.00),
(13, 8, 3, 1, 40.00),
(14, 9, 10, 1, 100.00),
(15, 10, 3, 1, 40.00),
(16, 11, 9, 1, 580.00),
(17, 12, 3, 1, 40.00),
(18, 13, 3, 5, 40.00),
(19, 14, 4, 2, 80.00),
(20, 15, 3, 4, 40.00),
(21, 16, 3, 4, 40.00),
(22, 17, 3, 6, 40.00),
(23, 18, 4, 6, 80.00),
(24, 19, 3, 3, 40.00),
(25, 20, 3, 1, 40.00),
(26, 21, 3, 4, 40.00),
(27, 22, 3, 3, 40.00),
(28, 23, 3, 3, 40.00),
(29, 24, 3, 3, 40.00),
(30, 25, 11, 5, 100.00),
(31, 25, 3, 5, 30.00),
(32, 26, 6, 3, 100.00),
(33, 26, 3, 3, 30.00),
(34, 26, 11, 5, 100.00),
(35, 27, 6, 2, 100.00),
(36, 28, 6, 6, 100.00),
(37, 28, 3, 3, 30.00),
(38, 29, 6, 3, 100.00),
(39, 30, 3, 5, 30.00),
(40, 30, 4, 5, 80.00),
(41, 30, 6, 2, 100.00),
(42, 30, 11, 1, 100.00),
(43, 30, 8, 1, 60.00),
(44, 31, 7, 5, 100.00),
(45, 31, 8, 6, 60.00),
(46, 32, 3, 1, 30.00),
(47, 32, 11, 1, 100.00),
(48, 33, 6, 1, 100.00),
(49, 34, 3, 1, 30.00),
(50, 34, 11, 1, 100.00),
(51, 35, 6, 1, 100.00),
(52, 35, 5, 5, 40.00),
(53, 36, 5, 3, 40.00),
(54, 36, 6, 4, 100.00),
(55, 37, 6, 6, 100.00),
(56, 38, 11, 1, 100.00),
(57, 38, 8, 1, 60.00),
(58, 38, 3, 1, 30.00),
(59, 39, 6, 2, 100.00),
(60, 39, 9, 3, 580.00),
(61, 39, 3, 6, 30.00),
(62, 39, 11, 1, 100.00),
(63, 39, 4, 6, 80.00),
(64, 40, 6, 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `measurement_unit` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `description`, `price`, `measurement_unit`, `quantity`) VALUES
(3, 1, 1, 'Apple', 'Red apple', 30.00, 'kg', 30),
(4, 1, 1, 'Pomegranate', 'Fresh fruit', 80.00, 'liters', 32),
(5, 2, 6, 'Heritage milk', 'Fresh Milk', 40.00, 'liters', 50),
(6, 2, 9, ' Amul Butter', 'Butter super taste\r\n', 100.00, 'pieces', 50),
(7, 3, 11, 'hot-dog-bun', 'Fresh Multigrain', 100.00, 'pieces', 50),
(8, 3, 11, 'Brown Bread', 'Fresh Bread', 60.00, 'pieces', 0),
(9, 4, 18, 'Wah Taj', 'tea', 580.00, NULL, 0),
(10, 4, 17, 'Thumbsup', '1liter', 100.00, NULL, 0),
(11, 5, 25, 'Biscits Barbone', 'tasty', 100.00, NULL, 0),
(12, 5, 21, 'Peper chips banana', 'banana chips', 80.00, NULL, 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_display_view`
-- (See below for the actual view)
--
CREATE TABLE `product_display_view` (
`product_id` int(11)
,`product_name` varchar(255)
,`product_price` decimal(10,2)
,`category_name` varchar(255)
,`subcategory_name` varchar(255)
,`discount_percentage` decimal(5,2)
,`product_image` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`) VALUES
(2, 3, 'C:xampphtdocs	esting/uploads/fresho-apple-red.webp'),
(3, 4, 'C:xampphtdocs	esting/uploads/fresho-pomegranate.webp'),
(6, 7, 'C:xampphtdocs	esting/uploads/40087522_2-fresho-multigrain-hot-dog-bun-safe-preservative-free.webp'),
(7, 8, 'C:xampphtdocs	esting/uploads/40232042_3-tasties-brown-bread.webp'),
(8, 9, 'C:xampphtdocs	esting/uploads/1204484_1-taj-mahal-tea.webp'),
(9, 10, 'C:xampphtdocs	esting/uploads/1203614_2-thums-up-soft-drink.webp'),
(10, 11, 'C:xampphtdocs	esting/uploads/280474_16-britannia-bourbon-chocolate-cream-biscuits.webp'),
(11, 12, 'C:xampphtdocs	esting/uploads/40200874_3-tasties-namkeen-banana-pepper-chips.webp'),
(22, 6, 'C:\\xampp\\htdocs\\priya/uploads/butter new.webp'),
(23, 5, 'C:\\xampp\\htdocs\\priya/uploads/special-milk.png');

-- --------------------------------------------------------

--
-- Table structure for table `product_rating`
--

CREATE TABLE `product_rating` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_rating`
--

INSERT INTO `product_rating` (`id`, `user_id`, `productid`, `rating`, `comment`) VALUES
(1, 2, 3, 5, 'Red Apple is juicy and sweet'),
(2, 2, 4, 4, 'Pomegranate is fresh and delicious'),
(3, 2, 5, 5, 'Heritage milk is fresh and pure'),
(4, 2, 6, 4, 'Amul Butter has a rich taste'),
(5, 2, 7, 3, 'Hot-dog bun is soft but lacks flavor'),
(6, 2, 8, 4, 'Brown Bread is fresh and nutritious'),
(7, 2, 9, 5, 'Wah Taj tea has a great aroma and taste'),
(8, 2, 10, 4, 'Thumbsup is refreshing and fizzy');

-- --------------------------------------------------------

--
-- Stand-in structure for view `sales`
-- (See below for the actual view)
--
CREATE TABLE `sales` (
`product_id` int(11)
,`quantity_sold` decimal(32,0)
,`sale_price` decimal(42,2)
,`sale_date` date
);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`address_id`, `user_id`, `name`, `mobile_number`, `address`, `city`, `postal_code`) VALUES
(1, 3, 'Teja', '7095692221', 'Road no 2 depak nagar karkana', 'Hydrabad', '505544'),
(2, 2, 'Gilakathula Arun', '7095692691', 'thirumalagiri', 'Hydrabad', '505555'),
(3, 2, 'Sreenivasulareddy undela Reddy', '07893958616', 'Kapulapalli village, banganapalli mandal kurnool district', 'KURNOOL', '5181869000000');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`) VALUES
(1, 1, 'Fresh Fruits'),
(2, 1, 'Fresh Vegetables'),
(3, 1, 'Organic Fruits & Vegetables'),
(4, 1, 'Cut & Peeled Veggies'),
(5, 1, 'Herbs & Seasonings'),
(6, 2, 'Milk'),
(7, 2, 'Cheese'),
(8, 2, 'Yogurt'),
(9, 2, 'Butter'),
(10, 2, 'Cream'),
(11, 3, 'Breads'),
(12, 3, 'Pastries'),
(13, 3, 'Cookies'),
(14, 3, 'Cakes'),
(15, 3, 'Muffins'),
(16, 4, 'Juices'),
(17, 4, 'Soft Drinks'),
(18, 4, 'Tea'),
(19, 4, 'Coffee'),
(20, 4, 'Energy Drinks'),
(21, 5, 'Chips'),
(22, 5, 'Nuts'),
(23, 5, 'Popcorn'),
(24, 5, 'Crackers'),
(25, 5, 'Pretzels');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `password`, `role`, `email`, `mobile`, `country`, `state`, `city`, `address`, `created_at`, `status`) VALUES
(1, 'admin', 'admin', 'admin', 'adminpassword', 'admin', 'admin@example.com', '123-456-7890', 'india', 'telangana', 'hydrabad', '456 Admin ', '2024-08-06 09:31:54', 'approved'),
(2, 'Nithin', 'Gilakathula', 'Nithin', 'Nanipriya@143', 'user', 'ngilakathula666@gmail.com', '7095692691', 'India', 'Telangana', 'Hydrabad', 'Thirumalagiri', '2024-08-06 09:55:37', 'approved'),
(3, 'Teja', 'Borra', 'Teja', 'Teja@143', 'user', 'Teja@123gmail.com', '8142429165', 'India', 'Telangana', 'Hydrabad', 'Suryapet', '2024-08-06 10:03:17', 'approved'),
(4, 'Sreenivasulareddy', 'Reddy', 'Vasu@123', 'Vasu@143', 'user', 'undelavasureddy@gmail.com', '7893958616', 'India', 'Andhra Pradesh', 'Thirupati', 'Kapulapalli village, banganapalli mandal kurnool district', '2024-08-20 06:15:25', 'approved'),
(5, 'Sreenivasulareddy', 'Reddy', 'Sree@123', 'Vasu@143', 'user', 'undelavasureddy12@gmail.com', '9010495570', 'India', 'Telangana', 'Hyderabad', 'Kapulapalli village, banganapalli mandal kurnool district', '2024-08-20 05:48:22', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `website_rating`
--

CREATE TABLE `website_rating` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_rating`
--

INSERT INTO `website_rating` (`id`, `user_id`, `rating`, `comment`) VALUES
(1, 2, 5, 'good');

-- --------------------------------------------------------

--
-- Structure for view `product_display_view`
--
DROP TABLE IF EXISTS `product_display_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_display_view`  AS SELECT `p`.`id` AS `product_id`, `p`.`name` AS `product_name`, `p`.`price` AS `product_price`, `c`.`name` AS `category_name`, `s`.`name` AS `subcategory_name`, coalesce(`d`.`discount_percentage`,0) AS `discount_percentage`, `pi`.`image_path` AS `product_image` FROM ((((`products` `p` join `categories` `c` on(`p`.`category_id` = `c`.`id`)) join `subcategories` `s` on(`p`.`subcategory_id` = `s`.`id`)) left join `discounts` `d` on(`p`.`id` = `d`.`product_id`)) left join `product_images` `pi` on(`p`.`id` = `pi`.`product_id`)) GROUP BY `p`.`id`, `p`.`name`, `p`.`price`, `c`.`name`, `s`.`name`, `d`.`discount_percentage`, `pi`.`image_path` ;

-- --------------------------------------------------------

--
-- Structure for view `sales`
--
DROP TABLE IF EXISTS `sales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sales`  AS SELECT `oi`.`product_id` AS `product_id`, sum(`oi`.`quantity`) AS `quantity_sold`, sum(`oi`.`quantity` * `oi`.`price`) AS `sale_price`, max(`o`.`order_date`) AS `sale_date` FROM (`order_items` `oi` join `orders` `o` on(`oi`.`order_id` = `o`.`id`)) GROUP BY `oi`.`product_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `measurement_units`
--
ALTER TABLE `measurement_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shipping_id` (`shipping_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_rating`
--
ALTER TABLE `product_rating`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `website_rating`
--
ALTER TABLE `website_rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement_units`
--
ALTER TABLE `measurement_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_rating`
--
ALTER TABLE `product_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `website_rating`
--
ALTER TABLE `website_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `discounts_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`),
  ADD CONSTRAINT `discounts_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `measurement_units`
--
ALTER TABLE `measurement_units`
  ADD CONSTRAINT `measurement_units_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_id`) REFERENCES `shipping_addresses` (`address_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `website_rating`
--
ALTER TABLE `website_rating`
  ADD CONSTRAINT `website_rating_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
