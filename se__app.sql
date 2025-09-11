-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-09-2025 a las 00:29:13
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
  `id` int(100) NOT NULL,
  `unidad` int(100) NOT NULL,
  `nivel` int(100) NOT NULL,
  `rtaA` varchar(100) NOT NULL,
  `rtaB` varchar(100) NOT NULL,
  `rtaC` varchar(100) NOT NULL,
  `rtaD` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicio`
--

INSERT INTO `ejercicio` (`id`, `unidad`, `nivel`, `rtaA`, `rtaB`, `rtaC`, `rtaD`) VALUES
(1, 1, 1, '', '', '', ''),
(2, 2, 1, '', '', '', ''),
(3, 3, 1, '', '', '', ''),
(4, 4, 1, '', '', '', ''),
(5, 1, 2, '', '', '', ''),
(6, 2, 2, '', '', '', ''),
(7, 3, 2, '', '', '', ''),
(8, 4, 2, '', '', '', ''),
(9, 1, 3, '', '', '', ''),
(10, 2, 3, '', '', '', ''),
(11, 3, 3, '', '', '', ''),
(12, 4, 3, '', '', '', ''),
(13, 1, 4, '', '', '', ''),
(14, 2, 4, '', '', '', ''),
(15, 3, 4, '', '', '', ''),
(16, 4, 4, '', '', '', ''),
(17, 1, 5, '', '', '', ''),
(18, 2, 5, '', '', '', ''),
(19, 3, 5, '', '', '', ''),
(20, 4, 5, '', '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `User_ID` int(100) NOT NULL,
  `User_Mail` varchar(100) NOT NULL,
  `User_Name` varchar(100) NOT NULL,
  `User_Pass` varchar(100) NOT NULL,
  `User_Lvl` int(100) NOT NULL,
  `User_Point` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`User_ID`, `User_Mail`, `User_Name`, `User_Pass`, `User_Lvl`, `User_Point`) VALUES
(1, 'EmiAppell@gmail.com', 'EmiAppella', '$2y$10$VtP4RR86QYaaUZLosETPgeq/bFchK6/4teeb1CxN8B64nMq7ci/5G', 0, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `User_ID` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
