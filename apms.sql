-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 04:45 AM
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
-- Database: `apms`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `action` enum('INSERT','UPDATE','DELETE_LOGICAL') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`) VALUES
(4, 'Barranquilla'),
(1, 'Bogotá'),
(6, 'Bucaramanga'),
(3, 'Cali'),
(5, 'Cartagena'),
(10, 'Cúcuta'),
(8, 'Manizales'),
(2, 'Medellín'),
(7, 'Pereira'),
(9, 'Santa Marta');

-- --------------------------------------------------------

--
-- Table structure for table `cleanings`
--

CREATE TABLE `cleanings` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `property_id` int(11) NOT NULL,
  `cleaning_date` datetime NOT NULL,
  `responsible_id` int(11) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cleanings`
--

INSERT INTO `cleanings` (`id`, `reservation_id`, `property_id`, `cleaning_date`, `responsible_id`, `observations`, `cost`, `is_active`, `deleted_at`) VALUES
(1, 1, 1, '2025-06-14 00:00:00', 1, 'Limpieza general posterior a la salida del huésped.', 50000.00, 1, NULL),
(2, 2, 2, '2025-07-10 00:00:00', 2, 'Desinfección y cambio de sábanas.', 60000.00, 1, NULL),
(3, 3, 3, '2025-08-06 00:00:00', 3, 'Limpieza profunda de cocina y baño.', 45000.00, 1, NULL),
(4, 4, 4, '2025-08-19 00:00:00', 4, 'Limpieza estándar después de check-out.', 40000.00, 1, NULL),
(5, 5, 5, '2025-09-08 00:00:00', 5, 'Revisión de inventario y limpieza completa.', 70000.00, 1, NULL),
(6, 6, 6, '2025-09-14 00:00:00', 1, 'Limpieza completa de sala y habitaciones.', 55000.00, 1, NULL),
(7, 7, 7, '2025-09-26 00:00:00', 2, 'Desinfección por huésped prolongado.', 60000.00, 1, NULL),
(8, 8, 8, '2025-10-04 00:00:00', 3, 'Limpieza ligera, estancia corta.', 35000.00, 1, NULL),
(9, 9, 9, '2025-10-15 00:00:00', 4, 'Limpieza profunda después de evento familiar.', 80000.00, 1, NULL),
(10, 10, 10, '2025-10-24 00:00:00', 5, 'Limpieza general, sin incidencias reportadas.', 50000.00, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_responsibles`
--

CREATE TABLE `cleaning_responsibles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cleaning_responsibles`
--

INSERT INTO `cleaning_responsibles` (`id`, `user_id`, `name`, `created_at`, `is_active`, `deleted_at`) VALUES
(1, 2, 'María López', '2025-10-28 22:12:36', 1, NULL),
(2, 4, 'Juan Pérez', '2025-10-28 22:29:13', 1, NULL),
(3, 5, 'Pedro Navaja', '2025-10-28 22:29:42', 1, NULL),
(4, 6, 'Miguel Angel Hincapie', '2025-10-28 22:30:21', 1, NULL),
(5, 7, 'Sandra Martinez', '2025-10-28 22:30:44', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(14) DEFAULT NULL,
  `document_type` enum('CC','CE','PAS') NOT NULL,
  `document_number` bigint(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `name`, `email`, `phone`, `document_type`, `document_number`, `created_at`, `is_active`, `deleted_at`) VALUES
(1, 'Sofía Hernández', 'sofia.hernandez@example.com', '3001122334', 'CC', 1032456789, '2025-10-29 03:27:32', 1, NULL),
(2, 'Mateo Ríos', 'mateo.rios@example.com', '3112233445', 'CC', 1029384756, '2025-10-29 03:27:32', 1, NULL),
(3, 'Valentina Gómez', 'valentina.gomez@example.com', '3123344556', 'PAS', 1098765432, '2025-10-29 03:27:32', 1, NULL),
(4, 'Samuel Rodríguez', 'samuel.rodriguez@example.com', '3134455667', 'CC', 1012345678, '2025-10-29 03:27:32', 1, NULL),
(5, 'Isabella Morales', 'isabella.morales@example.com', '3145566778', 'CC', 1009876543, '2025-10-29 03:27:32', 1, NULL),
(6, 'Lucas Fernández', 'lucas.fernandez@example.com', '3156677889', 'CE', 2003456789, '2025-10-29 03:27:32', 1, NULL),
(7, 'Mariana Pérez', 'mariana.perez@example.com', '3167788990', 'CC', 1011122233, '2025-10-29 03:27:32', 1, NULL),
(8, 'Emilio Vargas', 'emilio.vargas@example.com', '3178899001', 'PAS', 1087654321, '2025-10-29 03:27:32', 1, NULL),
(9, 'Gabriela Torres', 'gabriela.torres@example.com', '3189900112', 'CC', 1020304050, '2025-10-29 03:27:32', 1, NULL),
(10, 'Tomás Castillo', 'tomas.castillo@example.com', '3191001223', 'CE', 2012345678, '2025-10-29 03:27:32', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(14) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `email`, `phone`, `address`, `created_at`, `is_active`, `deleted_at`) VALUES
(1, 'Carlos Ramírez', 'carlos.ramirez@example.com', '3004567890', 'Cra 12 #45-23, Bogotá', '2025-10-29 03:22:34', 1, NULL),
(2, 'María Fernanda López', 'maria.lopez@example.com', '3109876543', 'Calle 8 #22-16, Medellín', '2025-10-29 03:22:34', 1, NULL),
(3, 'Juan Sebastián Torres', 'juan.torres@example.com', '3201234567', 'Av 6 #18-45, Cali', '2025-10-29 03:22:34', 1, NULL),
(4, 'Laura Pérez', 'laura.perez@example.com', '3017896543', 'Cra 65 #84-32, Barranquilla', '2025-10-29 03:22:34', 1, NULL),
(5, 'Andrés Gómez', 'andres.gomez@example.com', '3112223344', 'Calle del Arsenal #8B-10, Cartagena', '2025-10-29 03:22:34', 1, NULL),
(6, 'Paola Rodríguez', 'paola.rodriguez@example.com', '3157788990', 'Cra 33 #45-67, Bucaramanga', '2025-10-29 03:22:34', 1, NULL),
(7, 'Diego Martínez', 'diego.martinez@example.com', '3189988776', 'Km 4 vía Cerritos, Pereira', '2025-10-29 03:22:34', 1, NULL),
(8, 'Camila Vargas', 'camila.vargas@example.com', '3173344556', 'Vereda La Linda, Manizales', '2025-10-29 03:22:34', 1, NULL),
(9, 'Felipe Castillo', 'felipe.castillo@example.com', '3124455667', 'Cra 2 #9-18, Santa Marta', '2025-10-29 03:22:34', 1, NULL),
(10, 'Daniela Correa', 'daniela.correa@example.com', '3196655443', 'Av Libertadores #12-30, Cúcuta', '2025-10-29 03:22:34', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `method` enum('cash','card','transfer','other') DEFAULT 'transfer',
  `status` enum('pending','completed','refunded') DEFAULT 'completed',
  `created_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reservation_id`, `amount`, `payment_date`, `method`, `status`, `created_by`, `is_active`, `deleted_at`) VALUES
(1, 1, 600000.00, '2025-10-01 00:00:00', 'card', 'completed', 1, 1, NULL),
(2, 2, 450000.00, '2025-10-03 00:00:00', 'transfer', 'completed', 1, 1, NULL),
(3, 3, 720000.00, '2025-10-05 00:00:00', 'cash', 'pending', 2, 1, NULL),
(4, 4, 540000.00, '2025-10-06 00:00:00', 'card', 'completed', 1, 1, NULL),
(5, 5, 880000.00, '2025-10-08 00:00:00', 'transfer', 'completed', 2, 1, NULL),
(6, 6, 350000.00, '2025-10-10 00:00:00', 'cash', 'refunded', 1, 1, NULL),
(7, 7, 970000.00, '2025-10-12 00:00:00', 'other', 'pending', 3, 1, NULL),
(8, 8, 420000.00, '2025-10-13 00:00:00', 'card', 'completed', 2, 1, NULL),
(9, 9, 1050000.00, '2025-10-15 00:00:00', 'transfer', 'completed', 1, 1, NULL),
(10, 10, 480000.00, '2025-10-17 00:00:00', 'cash', 'pending', 3, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `nightly_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cleaning_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_guests` int(11) DEFAULT 1,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `owner_id`, `title`, `description`, `city_id`, `address`, `type_id`, `nightly_price`, `cleaning_cost`, `max_guests`, `status`, `created_at`, `is_active`, `deleted_at`) VALUES
(1, 1, 'Apartamento Central Bogotá', 'Moderno apartamento en el centro de Bogotá, ideal para viajes de trabajo.', 1, 'Calle 45 #7-22', 1, 150000.00, 40000.00, 3, 'active', '2025-10-29 03:39:15', 1, NULL),
(2, 2, 'Casa Colonial Medellín', 'Hermosa casa de estilo colonial con patio interior y jardín.', 2, 'Carrera 80 #32-14', 2, 220000.00, 50000.00, 6, 'active', '2025-10-29 03:39:15', 1, NULL),
(3, 3, 'Cabaña Vista al Mar Cartagena', 'Cabaña frente al mar con acceso privado a la playa.', 3, 'Playa Blanca Sector 3', 3, 300000.00, 70000.00, 5, 'maintenance', '2025-10-29 03:39:15', 1, NULL),
(4, 4, 'Loft Moderno Cali', 'Espacioso loft con diseño industrial y excelente ubicación.', 4, 'Avenida 6N #25-40', 1, 180000.00, 35000.00, 2, 'active', '2025-10-29 03:39:15', 1, NULL),
(5, 5, 'Casa Campestre Bucaramanga', 'Casa rodeada de naturaleza, ideal para descansar en familia.', 5, 'Vereda Ruitoque Alto', 2, 250000.00, 60000.00, 8, 'active', '2025-10-29 03:39:15', 1, NULL),
(6, 6, 'Apartamento Ejecutivo Barranquilla', 'Departamento totalmente amoblado cerca del centro financiero.', 6, 'Calle 85 #50-23', 1, 190000.00, 40000.00, 4, 'inactive', '2025-10-29 03:39:15', 1, NULL),
(7, 7, 'Cabaña Ecológica Santa Marta', 'Cabaña sostenible con paneles solares y vista a la Sierra Nevada.', 7, 'Camino Taganga Km 2', 3, 270000.00, 50000.00, 5, 'active', '2025-10-29 03:39:15', 1, NULL),
(8, 8, 'Apartamento Familiar Pereira', 'Amplio apartamento con balcón y vista a las montañas.', 8, 'Calle 18 #10-45', 1, 160000.00, 35000.00, 4, 'active', '2025-10-29 03:39:15', 1, NULL),
(9, 9, 'Casa de Playa San Andrés', 'Casa privada con piscina y acceso directo al mar.', 9, 'Sector Spratt Bight #12', 2, 350000.00, 80000.00, 7, 'maintenance', '2025-10-29 03:39:15', 1, NULL),
(10, 10, 'Cabaña del Bosque Manizales', 'Acogedora cabaña entre pinos, perfecta para escapadas románticas.', 10, 'Km 5 vía al Nevado del Ruiz', 3, 200000.00, 45000.00, 3, 'active', '2025-10-29 03:39:15', 1, NULL);

--
-- Triggers `properties`
--
DELIMITER $$
CREATE TRIGGER `trg_delete_log` AFTER UPDATE ON `properties` FOR EACH ROW BEGIN
  IF OLD.is_active = 1 AND NEW.is_active = 0 THEN
    INSERT INTO activity_log (table_name, record_id, action, user_id, details)
    VALUES ('properties', NEW.id, 'DELETE_LOGICAL', 1, 'Propiedad desactivada');
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_update_log` AFTER UPDATE ON `properties` FOR EACH ROW BEGIN
  IF OLD.title <> NEW.title OR OLD.status <> NEW.status THEN
    INSERT INTO activity_log (table_name, record_id, action, user_id, details)
    VALUES ('properties', NEW.id, 'UPDATE', 1, CONCAT('Cambios en propiedad: ', OLD.title, ' → ', NEW.title));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`) VALUES
(1, 'Apartamento'),
(3, 'Cabaña'),
(2, 'Casa');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `nights` int(11) NOT NULL DEFAULT 0,
  `total_cost` decimal(12,2) NOT NULL,
  `status` enum('confirmed','checked_in','completed','cancelled') DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `property_id`, `guest_id`, `start_date`, `end_date`, `nights`, `total_cost`, `status`, `created_at`, `is_active`, `deleted_at`) VALUES
(1, 1, 1, '2025-06-10', '2025-06-13', 3, 450000.00, 'completed', '2025-10-29 03:40:32', 1, NULL),
(2, 2, 2, '2025-07-05', '2025-07-09', 4, 600000.00, 'checked_in', '2025-10-29 03:40:32', 1, NULL),
(3, 3, 3, '2025-08-01', '2025-08-05', 4, 720000.00, 'confirmed', '2025-10-29 03:40:32', 1, NULL),
(4, 4, 4, '2025-08-15', '2025-08-18', 3, 390000.00, 'cancelled', '2025-10-29 03:40:32', 1, NULL),
(5, 5, 5, '2025-09-02', '2025-09-07', 5, 950000.00, 'completed', '2025-10-29 03:40:32', 1, NULL),
(6, 6, 6, '2025-09-10', '2025-09-13', 3, 420000.00, 'checked_in', '2025-10-29 03:40:32', 1, NULL),
(7, 7, 7, '2025-09-20', '2025-09-25', 5, 850000.00, 'confirmed', '2025-10-29 03:40:32', 1, NULL),
(8, 8, 8, '2025-10-01', '2025-10-03', 2, 280000.00, 'cancelled', '2025-10-29 03:40:32', 1, NULL),
(9, 9, 9, '2025-10-10', '2025-10-14', 4, 680000.00, 'completed', '2025-10-29 03:40:32', 1, NULL),
(10, 10, 10, '2025-10-20', '2025-10-23', 3, 510000.00, 'confirmed', '2025-10-29 03:40:32', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(3, 'cleaner'),
(2, 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role_id`, `created_at`, `is_active`, `deleted_at`) VALUES
(1, 'admin', '$2y$10$uQWRSAcbe8OJ6zxr9JuZUeuaJQsAkGU7yZeM0y6fK4TZQgHg7xybu', 'Administrador General', 'admin@apms.com', 1, '2025-10-29 03:10:48', 1, NULL),
(2, 'María', '$2y$10$Jrs1FWKC4cHfP9xpTgFW5udr4YlHOxax3SxG0TDvkaMHn91AxwyuC', 'María López', 'maria@gmail.com', 3, '2025-10-29 03:12:36', 1, NULL),
(3, 'Vane', '$2y$10$1nUPr2gFVms9Ct4LP5kvA.HEtXyHalVl9Pl2GNrC5.qXSV6scKV5K', 'Vanessa Osorio', 'vane@gmail.com', 2, '2025-10-29 03:13:05', 1, NULL),
(4, 'Juan', '$2y$10$sGVTZCEGBH6YLbp4vMxR8Onq8KOT8cg5254E.aClmHAl7JwEb6ytS', 'Juan Pérez', 'juan@gmail.com', 3, '2025-10-29 03:29:13', 1, NULL),
(5, 'Pedro', '$2y$10$bkmasR76AJ5RMvejpKeuQuRNqWbJGEC0bmAiPJAhIHqtBY05wlaTa', 'Pedro Navaja', 'pedro@gmail.com', 3, '2025-10-29 03:29:42', 1, NULL),
(6, 'Miguelito', '$2y$10$G7u.QA3AMYRWTFvuPxzZlO.ILNbhZQswPYI/YN2K6fd3fAOFlmqRK', 'Miguel Angel Hincapie', 'migue@gmail.com', 3, '2025-10-29 03:30:21', 1, NULL),
(7, 'Sandra', '$2y$10$o1PR4HLXM.5wOXCmCZoPm.DPxd7hKrC4eDp93346Fi.rwfbjQ79he', 'Sandra Martinez', 'sandra@gmail.com', 3, '2025-10-29 03:30:44', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `cleanings`
--
ALTER TABLE `cleanings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `fk_cleanings_responsible` (`responsible_id`);

--
-- Indexes for table `cleaning_responsibles`
--
ALTER TABLE `cleaning_responsibles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_responsible_user` (`user_id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_type` (`document_type`,`document_number`),
  ADD UNIQUE KEY `uq_document` (`document_number`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `guest_id` (`guest_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cleanings`
--
ALTER TABLE `cleanings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cleaning_responsibles`
--
ALTER TABLE `cleaning_responsibles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cleanings`
--
ALTER TABLE `cleanings`
  ADD CONSTRAINT `cleanings_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `cleanings_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `fk_cleanings_responsible` FOREIGN KEY (`responsible_id`) REFERENCES `cleaning_responsibles` (`id`);

--
-- Constraints for table `cleaning_responsibles`
--
ALTER TABLE `cleaning_responsibles`
  ADD CONSTRAINT `fk_responsible_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`),
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `property_types` (`id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
