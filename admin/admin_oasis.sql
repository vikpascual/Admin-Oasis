-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-04-2020 a las 20:57:56
-- Versión del servidor: 10.4.8-MariaDB
-- Versión de PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `admin_oasis`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunidades`
--

CREATE TABLE `comunidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `version` varchar(7) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nivel_seguridad` varchar(15) DEFAULT NULL,
  `protocolo_autenticacion` varchar(4) DEFAULT NULL,
  `pass_autenticacion` varchar(255) DEFAULT NULL,
  `protocolo_privacidad` varchar(4) DEFAULT NULL,
  `pass_privacidad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunidades_equipos`
--

CREATE TABLE `comunidades_equipos` (
  `id_comunidad` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `discos`
--

CREATE TABLE `discos` (
  `id` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `espacio_libre` int(11) DEFAULT NULL,
  `espacio_total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `mac` varchar(50) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fabricante` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `numero_de_serie` varchar(70) DEFAULT NULL,
  `ram` int(11) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `tiempo_encendido` varchar(50) DEFAULT NULL,
  `procesador` varchar(50) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado` timestamp NULL DEFAULT NULL,
  `ultimo_usuario` varchar(50) DEFAULT NULL,
  `bios` varchar(50) DEFAULT NULL,
  `arquitectura` varchar(10) DEFAULT NULL,
  `tipo_dispositivo` varchar(30) DEFAULT NULL,
  `localizacion` varchar(50) DEFAULT NULL,
  `contacto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `ip` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `puerto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usuario` varchar(255) NOT NULL,
  `password` char(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wmi`
--

CREATE TABLE `wmi` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `pass` varchar(50) DEFAULT NULL,
  `dominio` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wmi_equipos`
--

CREATE TABLE `wmi_equipos` (
  `id_equipo` int(11) NOT NULL,
  `id_wmi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comunidades`
--
ALTER TABLE `comunidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunidades_equipos`
--
ALTER TABLE `comunidades_equipos`
  ADD KEY `id_comunidad` (`id_comunidad`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `discos`
--
ALTER TABLE `discos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario`);

--
-- Indices de la tabla `wmi`
--
ALTER TABLE `wmi`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `wmi_equipos`
--
ALTER TABLE `wmi_equipos`
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_wmi` (`id_wmi`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comunidades`
--
ALTER TABLE `comunidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `discos`
--
ALTER TABLE `discos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `wmi`
--
ALTER TABLE `wmi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comunidades_equipos`
--
ALTER TABLE `comunidades_equipos`
  ADD CONSTRAINT `comunidades` FOREIGN KEY (`id_comunidad`) REFERENCES `comunidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipos` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `discos`
--
ALTER TABLE `discos`
  ADD CONSTRAINT `discos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `wmi_equipos`
--
ALTER TABLE `wmi_equipos`
  ADD CONSTRAINT `wmi_equipos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wmi_equipos_ibfk_2` FOREIGN KEY (`id_wmi`) REFERENCES `wmi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
