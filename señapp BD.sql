-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-10-2025 a las 16:16:15
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
  `id_ej` int(255) NOT NULL,
  `nivel` int(255) NOT NULL,
  `unidad` varchar(155) NOT NULL,
  `rtaAcorrect` varchar(155) NOT NULL,
  `rtaB` varchar(155) NOT NULL,
  `rtaC` varchar(155) NOT NULL,
  `rtaD` varchar(155) NOT NULL,
  `video` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicio`
--

INSERT INTO `ejercicio` (`id_ej`, `nivel`, `unidad`, `rtaAcorrect`, `rtaB`, `rtaC`, `rtaD`, `video`, `type`) VALUES
(1, 1, '1 Abecedario', 'A', 'B', 'C', 'D', 'SeñaA.gif', 'Elegir'),
(24, 1, '1 Abecedario', 'B', 'A', 'C', 'D', '69015c4500042_SeaB.gif', 'Elegir'),
(25, 1, '1 Abecedario', 'C', 'B', 'C', 'D', '69015c6b2cd17_SeaC.gif', 'Elegir'),
(26, 2, '1 Abecedario', 'D', 'B', 'C', 'A', '69015ce0c6b82_SeaD.gif', 'Elegir'),
(27, 1, '2 prueba', 'P', '', '', '', '69015e351eb92_SeaA.gif', 'Escribir'),
(28, 1, '1 Abecedario', 'D', 'A', 'B', 'C', '6902277e80b80_SeaD.gif', 'Elegir');

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
  `User_Points` int(11) DEFAULT 0,
  `User_Progress` text DEFAULT NULL,
  `User_IsAdmin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`User_ID`, `User_Mail`, `User_Name`, `User_Pass`, `User_Lvl`, `User_Points`, `User_Progress`, `User_IsAdmin`) VALUES
(21, 'tester@tester.com.ar', 'Juan', '$2y$10$hX6AgPtXMhZbMMjqbh1zae.uC6.Sr8RC7Nw3YmqdWN9u4rRB38k9.', 2, 100, '[1,17,22]', 0),
(22, 'admin123@gmail.com', 'YoAdmin', '$2y$10$KyFaayixR.2kqqD2YZ1hhedP2Eu9bkSvDcNP6bUHyFFdMS3iqv2nK', 3, 250, '[19,18,26,22,1,24,25,28]', 1);

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
  MODIFY `id_ej` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
