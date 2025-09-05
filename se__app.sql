-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-09-2025 a las 02:33:35
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `señapp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicio`
--

CREATE TABLE `ejercicio` (
  `id_ej` int(111) NOT NULL,
  `nivel` int(11) NOT NULL,
  `rtaA` varchar(11) NOT NULL,
  `rtaB` varchar(11) NOT NULL,
  `rtaC` varchar(11) NOT NULL,
  `rtaD` varchar(11) NOT NULL,
  `video` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `User_ID` int(11) NOT NULL,
  `User_Mail` varchar(100) NOT NULL,
  `User_Name` varchar(50) NOT NULL,
  `User_Pass` varchar(255) NOT NULL,
  `User_Lvl` int(11) DEFAULT 1,
  `User_Points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`User_ID`, `User_Mail`, `User_Name`, `User_Pass`, `User_Lvl`, `User_Points`) VALUES
(1, 'KevinMc@gmail.com', 'Lolo', '$2y$10$bE9BB6mD3meOBOVIxaAQ2OhqstEdaBLgeNknsrXKpFNfCDsllIpDS', 1, 0),
(3, 'Grasita@gmail.com', 'Grasa', '$2y$10$.oZFFEEStGui6omclwLcR.Y.UdsSuV0n/abNdrachjkN/N7EegciS', 1, 0),
(4, 'Lololala@gmail.com', 'PEPE', '$2y$10$iQEL7nSJfM3qw9GRPKqVk.sZ0SSKExbzYo4587nGLpmr8rc.yQD2e', 1, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id_ej`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `User_Mail` (`User_Mail`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  MODIFY `id_ej` int(111) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
