-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 20, 2026 at 11:58 AM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bike_rental`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bikes`
--

CREATE TABLE `bikes` (
  `id` int(11) NOT NULL,
  `frame_no` varchar(50) NOT NULL,
  `model` varchar(80) NOT NULL,
  `type` varchar(30) NOT NULL,
  `hour_price` decimal(10,2) NOT NULL,
  `status` enum('available','rented','service') NOT NULL DEFAULT 'available',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bikes`
--

INSERT INTO `bikes` (`id`, `frame_no`, `model`, `type`, `hour_price`, `status`, `notes`, `created_at`) VALUES
(1, 'Nr1', 'Model1', 'MTB', 20.00, 'available', 'Notatka1', '2026-01-20 10:41:29'),
(2, 'FR-001', 'Kross Hexagon 5.0', 'MTB', 15.00, 'available', 'Dobry stan', '2026-01-20 10:52:21'),
(3, 'FR-002', 'Trek FX 2', 'Miejski', 12.00, 'rented', '', '2026-01-20 10:52:21'),
(4, 'FR-003', 'Giant Talon 3', 'MTB', 18.00, 'rented', 'Wypożyczony', '2026-01-20 10:52:21'),
(5, 'FR-004', 'Specialized Allez', 'Szosowy', 20.00, 'available', '', '2026-01-20 10:52:21'),
(6, 'FR-005', 'Cannondale Topstone', 'Gravel', 22.00, 'service', 'Serwis hamulców', '2026-01-20 10:52:21');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `first_name`, `last_name`, `phone`, `email`, `created_at`) VALUES
(2, 'imie1', 'nazwisko1', '111111111', 'email1@email.com', '2026-01-20 10:44:09'),
(3, 'Jan', 'Kowalski', '600123456', 'jan.kowalski@test.pl', '2026-01-20 10:52:21'),
(4, 'Anna', 'Nowak', '601222333', 'anna.nowak@test.pl', '2026-01-20 10:52:21'),
(5, 'Piotr', 'Zieliński', '602444555', 'piotr.z@test.pl', '2026-01-20 10:52:21'),
(6, 'Maria', 'Wiśniewska', '603777888', 'maria.w@test.pl', '2026-01-20 10:52:21');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `bike_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_total` decimal(10,2) DEFAULT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `bike_id`, `client_id`, `user_id`, `start_at`, `end_at`, `deposit`, `price_total`, `status`) VALUES
(1, 1, 2, 1, '2026-01-20 11:45:14', '2026-01-20 11:45:20', 100.00, 20.00, 'closed'),
(2, 3, 3, 1, '2026-01-20 06:52:21', NULL, 50.00, NULL, 'active');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$SvB7YA9Yrx7I0VLND5UcQe4jSn.OWeOw8ZyqmjhhnbkFTNp3d1zKW', 'staff', '2026-01-20 10:40:40');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `bikes`
--
ALTER TABLE `bikes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `frame_no` (`frame_no`);

--
-- Indeksy dla tabeli `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rentals_bike` (`bike_id`),
  ADD KEY `fk_rentals_client` (`client_id`),
  ADD KEY `fk_rentals_user` (`user_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bikes`
--
ALTER TABLE `bikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `fk_rentals_bike` FOREIGN KEY (`bike_id`) REFERENCES `bikes` (`id`),
  ADD CONSTRAINT `fk_rentals_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `fk_rentals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
