-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2025 a las 04:36:34
-- Versión del servidor: 9.3.0
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gymdb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acceso`
--

CREATE TABLE `acceso` (
  `Id_Acceso` int NOT NULL,
  `Id_Cliente` int DEFAULT NULL,
  `Id_Administrador` int DEFAULT NULL,
  `Id_Entrenador` int DEFAULT NULL,
  `Estado_Acceso` enum('activo','inactivo') DEFAULT 'activo',
  `Codigo` varchar(255) DEFAULT NULL,
  `Fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Fecha_Generado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `acceso`
--

INSERT INTO `acceso` (`Id_Acceso`, `Id_Cliente`, `Id_Administrador`, `Id_Entrenador`, `Estado_Acceso`, `Codigo`, `Fecha`, `Fecha_Generado`) VALUES
(1, 4, 5, NULL, 'activo', 'GYM68f654742219d7.36473755', '2025-10-20 10:25:40', '2025-10-20 10:25:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `Id_Administrador` int NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `Contrasena` varchar(255) DEFAULT NULL,
  `Id_Tipo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`Id_Administrador`, `Nombre`, `Apellido`, `Telefono`, `Correo`, `Contrasena`, `Id_Tipo`) VALUES
(5, 'Admin', 'Principal', '1234567890', 'admin@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clase`
--

CREATE TABLE `clase` (
  `Id_Clase` int NOT NULL,
  `Horario` datetime NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Cupo_Maximo` int NOT NULL,
  `Id_Entrenador` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clase_personalizada`
--

CREATE TABLE `clase_personalizada` (
  `Id_Solicitud` int NOT NULL,
  `Id_Entrenador` int NOT NULL,
  `Id_Cliente` int NOT NULL,
  `Id_Membrecia` int DEFAULT NULL,
  `Fecha` datetime NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT '1',
  `Observaciones` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `Id_Cliente` int NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `Contrasena` varchar(255) DEFAULT NULL,
  `Id_Tipo` int NOT NULL,
  `Id_Membrecia` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`Id_Cliente`, `Nombre`, `Apellido`, `Telefono`, `Correo`, `Contrasena`, `Id_Tipo`, `Id_Membrecia`) VALUES
(3, 'santiago', 'dksmkl', '65313213', 'entrenador@ejemplo.com', '$2y$10$0OcQCOd4lAubF3KPnzhTNepuxWbw8dfg8nzWimuwRKqrC4Wl7k00m', 3, NULL),
(4, 'David', 'Morroy', '3105235696', 'Dasagamo1@gmail.com', '$2y$10$IT87Zd6jBfXfC9VM4wNGouXZ0bHncW.Iw8Ako6C93yiHyoXgVVng2', 3, 1),
(5, 'santiago', 'cabezas', '414191591', 'hyjknibh@gmail.com', '$2y$10$0RVwasDJv37Z2ijm.W0EXOJtWTgwDDvSNt5PeQDz04PWi9wk2W72C', 3, NULL),
(6, 'laura', 'tovar', '544554', 'lau@gmail.com', '123', 3, NULL),
(7, 'sakls', 'slaskl', '5615', 'david@david', '$2y$10$8wOavW9wlcI4mzBXNlRfu.SzczRNWiJyIw6THQCzdTFxL0SDMWJYa', 3, NULL),
(8, 'dsad', 'dsadsa', '156154', 'dsa@dsadas', '$2y$10$qwnI/n0mj/TP3su7aahtMudnaoLS6NDJ2Wr2ykYCVy46NatN6L4tm', 3, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_clase`
--

CREATE TABLE `cliente_clase` (
  `Id_Cliente` int NOT NULL,
  `Id_Clase` int NOT NULL,
  `Fecha_Inscripcion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuento`
--

CREATE TABLE `descuento` (
  `Id_Descuento` int NOT NULL,
  `Id_Membrecia` int DEFAULT NULL,
  `Id_Tipo_Usuario` int DEFAULT NULL,
  `Id_Producto` int DEFAULT NULL,
  `Porcentaje` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenador`
--

CREATE TABLE `entrenador` (
  `Id_Entrenador` int NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `Contrasena` varchar(255) DEFAULT NULL,
  `Id_Especialidad` int DEFAULT NULL,
  `Id_Tipo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `entrenador`
--

INSERT INTO `entrenador` (`Id_Entrenador`, `Nombre`, `Apellido`, `Telefono`, `Correo`, `Contrasena`, `Id_Especialidad`, `Id_Tipo`) VALUES
(4, 'dsadsa', 'dsadas', '16516541', 'entrenador@ejeplo.com', '$2y$10$oiAAcXyQhz7aIurFqpFa6OS7VwMZvPUW.5FE9vwO0YtFGpBQKQvju', 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidad`
--

CREATE TABLE `especialidad` (
  `Id_Especialidad` int NOT NULL,
  `Nombre_Especialidad` varchar(100) NOT NULL,
  `Descripcion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `especialidad`
--

INSERT INTO `especialidad` (`Id_Especialidad`, `Nombre_Especialidad`, `Descripcion`) VALUES
(1, 'pilates', 'en su era pilates\r\n');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `Id_Inventario` int NOT NULL,
  `Id_Producto` int NOT NULL,
  `Cantidad` int NOT NULL DEFAULT '0',
  `Ubicacion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membrecia`
--

CREATE TABLE `membrecia` (
  `Id_Membrecia` int NOT NULL,
  `Id_Tipo_Membrecia` int NOT NULL,
  `Duracion` int NOT NULL,
  `Fecha_Inicio` date DEFAULT NULL,
  `Fecha_Fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `membrecia`
--

INSERT INTO `membrecia` (`Id_Membrecia`, `Id_Tipo_Membrecia`, `Duracion`, `Fecha_Inicio`, `Fecha_Fin`) VALUES
(1, 1, 30, '2025-10-01', '2025-10-31'),
(2, 2, 30, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_de_pago`
--

CREATE TABLE `metodo_de_pago` (
  `Id_Metodo_Pago` int NOT NULL,
  `Metodo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `Id_Pago` int NOT NULL,
  `Id_Cliente` int DEFAULT NULL,
  `Id_Tipo_Usuario` int DEFAULT NULL,
  `Id_Metodo_Pago` int NOT NULL,
  `Id_Membrecia` int DEFAULT NULL,
  `Id_Descuento` int DEFAULT NULL,
  `IVA` decimal(5,2) DEFAULT '0.00',
  `Valor` decimal(10,2) NOT NULL,
  `Valor_Total` decimal(12,2) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_producto`
--

CREATE TABLE `pago_producto` (
  `Id_Pago` int NOT NULL,
  `Id_Producto` int NOT NULL,
  `Cantidad` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `Id_Producto` int NOT NULL,
  `Nombre` varchar(150) NOT NULL,
  `Marca` varchar(100) DEFAULT NULL,
  `Id_Administrador` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_cliente`
--

CREATE TABLE `progreso_cliente` (
  `Id_Progreso` int NOT NULL,
  `Id_Clase_Personalizada` int NOT NULL,
  `Fecha` datetime NOT NULL,
  `Peso` decimal(6,2) DEFAULT NULL,
  `Medidas_Cintura` decimal(6,2) DEFAULT NULL,
  `Medidas_Pecho` decimal(6,2) DEFAULT NULL,
  `Medidas_Brazo` decimal(6,2) DEFAULT NULL,
  `Peso_Sentadilla` decimal(7,2) DEFAULT NULL,
  `Peso_PressBanca` decimal(7,2) DEFAULT NULL,
  `Peso_Deadlift` decimal(7,2) DEFAULT NULL,
  `Observaciones` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `Id_Proveedor` int NOT NULL,
  `Nombre` varchar(150) NOT NULL,
  `Telefono` varchar(30) DEFAULT NULL,
  `Correo` varchar(150) DEFAULT NULL,
  `Id_Producto` int NOT NULL,
  `Precio_Proveedor` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_membrecia`
--

CREATE TABLE `tipo_membrecia` (
  `Id_Tipo_Membrecia` int NOT NULL,
  `Nombre_Tipo` varchar(50) NOT NULL,
  `Precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tipo_membrecia`
--

INSERT INTO `tipo_membrecia` (`Id_Tipo_Membrecia`, `Nombre_Tipo`, `Precio`) VALUES
(1, 'Básica', 30.00),
(2, 'Premium', 60.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `Id_Tipo` int NOT NULL,
  `Tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`Id_Tipo`, `Tipo`) VALUES
(1, 'administrador'),
(2, 'entrenador'),
(3, 'cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acceso`
--
ALTER TABLE `acceso`
  ADD PRIMARY KEY (`Id_Acceso`),
  ADD KEY `Id_Cliente` (`Id_Cliente`),
  ADD KEY `Id_Administrador` (`Id_Administrador`),
  ADD KEY `Id_Entrenador` (`Id_Entrenador`);

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`Id_Administrador`),
  ADD UNIQUE KEY `Telefono` (`Telefono`),
  ADD KEY `Id_Tipo` (`Id_Tipo`);

--
-- Indices de la tabla `clase`
--
ALTER TABLE `clase`
  ADD PRIMARY KEY (`Id_Clase`),
  ADD KEY `Id_Entrenador` (`Id_Entrenador`);

--
-- Indices de la tabla `clase_personalizada`
--
ALTER TABLE `clase_personalizada`
  ADD PRIMARY KEY (`Id_Solicitud`),
  ADD KEY `Id_Entrenador` (`Id_Entrenador`),
  ADD KEY `Id_Cliente` (`Id_Cliente`),
  ADD KEY `Id_Membrecia` (`Id_Membrecia`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`Id_Cliente`),
  ADD UNIQUE KEY `Telefono` (`Telefono`),
  ADD KEY `Id_Tipo` (`Id_Tipo`),
  ADD KEY `Id_Membrecia` (`Id_Membrecia`);

--
-- Indices de la tabla `cliente_clase`
--
ALTER TABLE `cliente_clase`
  ADD PRIMARY KEY (`Id_Cliente`,`Id_Clase`),
  ADD KEY `Id_Clase` (`Id_Clase`);

--
-- Indices de la tabla `descuento`
--
ALTER TABLE `descuento`
  ADD PRIMARY KEY (`Id_Descuento`),
  ADD KEY `Id_Membrecia` (`Id_Membrecia`),
  ADD KEY `Id_Tipo_Usuario` (`Id_Tipo_Usuario`),
  ADD KEY `Id_Producto` (`Id_Producto`);

--
-- Indices de la tabla `entrenador`
--
ALTER TABLE `entrenador`
  ADD PRIMARY KEY (`Id_Entrenador`),
  ADD UNIQUE KEY `Telefono` (`Telefono`),
  ADD KEY `Id_Tipo` (`Id_Tipo`),
  ADD KEY `fk_entrenador_especialidad` (`Id_Especialidad`);

--
-- Indices de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  ADD PRIMARY KEY (`Id_Especialidad`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`Id_Inventario`),
  ADD KEY `Id_Producto` (`Id_Producto`);

--
-- Indices de la tabla `membrecia`
--
ALTER TABLE `membrecia`
  ADD PRIMARY KEY (`Id_Membrecia`),
  ADD KEY `Id_Tipo_Membrecia` (`Id_Tipo_Membrecia`);

--
-- Indices de la tabla `metodo_de_pago`
--
ALTER TABLE `metodo_de_pago`
  ADD PRIMARY KEY (`Id_Metodo_Pago`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`Id_Pago`),
  ADD KEY `Id_Cliente` (`Id_Cliente`),
  ADD KEY `Id_Tipo_Usuario` (`Id_Tipo_Usuario`),
  ADD KEY `Id_Metodo_Pago` (`Id_Metodo_Pago`),
  ADD KEY `Id_Membrecia` (`Id_Membrecia`),
  ADD KEY `Id_Descuento` (`Id_Descuento`);

--
-- Indices de la tabla `pago_producto`
--
ALTER TABLE `pago_producto`
  ADD PRIMARY KEY (`Id_Pago`,`Id_Producto`),
  ADD KEY `Id_Producto` (`Id_Producto`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`Id_Producto`),
  ADD KEY `Id_Administrador` (`Id_Administrador`);

--
-- Indices de la tabla `progreso_cliente`
--
ALTER TABLE `progreso_cliente`
  ADD PRIMARY KEY (`Id_Progreso`),
  ADD KEY `Id_Clase_Personalizada` (`Id_Clase_Personalizada`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`Id_Proveedor`,`Id_Producto`),
  ADD KEY `Id_Producto` (`Id_Producto`);

--
-- Indices de la tabla `tipo_membrecia`
--
ALTER TABLE `tipo_membrecia`
  ADD PRIMARY KEY (`Id_Tipo_Membrecia`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`Id_Tipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acceso`
--
ALTER TABLE `acceso`
  MODIFY `Id_Acceso` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `Id_Administrador` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `Id_Clase` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clase_personalizada`
--
ALTER TABLE `clase_personalizada`
  MODIFY `Id_Solicitud` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `Id_Cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `descuento`
--
ALTER TABLE `descuento`
  MODIFY `Id_Descuento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrenador`
--
ALTER TABLE `entrenador`
  MODIFY `Id_Entrenador` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `especialidad`
--
ALTER TABLE `especialidad`
  MODIFY `Id_Especialidad` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `Id_Inventario` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membrecia`
--
ALTER TABLE `membrecia`
  MODIFY `Id_Membrecia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `metodo_de_pago`
--
ALTER TABLE `metodo_de_pago`
  MODIFY `Id_Metodo_Pago` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `Id_Pago` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `Id_Producto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `progreso_cliente`
--
ALTER TABLE `progreso_cliente`
  MODIFY `Id_Progreso` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `Id_Proveedor` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_membrecia`
--
ALTER TABLE `tipo_membrecia`
  MODIFY `Id_Tipo_Membrecia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `Id_Tipo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acceso`
--
ALTER TABLE `acceso`
  ADD CONSTRAINT `acceso_ibfk_1` FOREIGN KEY (`Id_Cliente`) REFERENCES `cliente` (`Id_Cliente`),
  ADD CONSTRAINT `acceso_ibfk_2` FOREIGN KEY (`Id_Administrador`) REFERENCES `administrador` (`Id_Administrador`),
  ADD CONSTRAINT `acceso_ibfk_3` FOREIGN KEY (`Id_Entrenador`) REFERENCES `entrenador` (`Id_Entrenador`);

--
-- Filtros para la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `administrador_ibfk_1` FOREIGN KEY (`Id_Tipo`) REFERENCES `tipo_usuario` (`Id_Tipo`);

--
-- Filtros para la tabla `clase`
--
ALTER TABLE `clase`
  ADD CONSTRAINT `clase_ibfk_1` FOREIGN KEY (`Id_Entrenador`) REFERENCES `entrenador` (`Id_Entrenador`);

--
-- Filtros para la tabla `clase_personalizada`
--
ALTER TABLE `clase_personalizada`
  ADD CONSTRAINT `clase_personalizada_ibfk_1` FOREIGN KEY (`Id_Entrenador`) REFERENCES `entrenador` (`Id_Entrenador`),
  ADD CONSTRAINT `clase_personalizada_ibfk_2` FOREIGN KEY (`Id_Cliente`) REFERENCES `cliente` (`Id_Cliente`),
  ADD CONSTRAINT `clase_personalizada_ibfk_3` FOREIGN KEY (`Id_Membrecia`) REFERENCES `membrecia` (`Id_Membrecia`);

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`Id_Tipo`) REFERENCES `tipo_usuario` (`Id_Tipo`),
  ADD CONSTRAINT `cliente_ibfk_2` FOREIGN KEY (`Id_Membrecia`) REFERENCES `membrecia` (`Id_Membrecia`);

--
-- Filtros para la tabla `cliente_clase`
--
ALTER TABLE `cliente_clase`
  ADD CONSTRAINT `cliente_clase_ibfk_1` FOREIGN KEY (`Id_Cliente`) REFERENCES `cliente` (`Id_Cliente`),
  ADD CONSTRAINT `cliente_clase_ibfk_2` FOREIGN KEY (`Id_Clase`) REFERENCES `clase` (`Id_Clase`);

--
-- Filtros para la tabla `descuento`
--
ALTER TABLE `descuento`
  ADD CONSTRAINT `descuento_ibfk_1` FOREIGN KEY (`Id_Membrecia`) REFERENCES `membrecia` (`Id_Membrecia`),
  ADD CONSTRAINT `descuento_ibfk_2` FOREIGN KEY (`Id_Tipo_Usuario`) REFERENCES `tipo_usuario` (`Id_Tipo`),
  ADD CONSTRAINT `descuento_ibfk_3` FOREIGN KEY (`Id_Producto`) REFERENCES `producto` (`Id_Producto`);

--
-- Filtros para la tabla `entrenador`
--
ALTER TABLE `entrenador`
  ADD CONSTRAINT `entrenador_ibfk_1` FOREIGN KEY (`Id_Tipo`) REFERENCES `tipo_usuario` (`Id_Tipo`),
  ADD CONSTRAINT `fk_entrenador_especialidad` FOREIGN KEY (`Id_Especialidad`) REFERENCES `especialidad` (`Id_Especialidad`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`Id_Producto`) REFERENCES `producto` (`Id_Producto`);

--
-- Filtros para la tabla `membrecia`
--
ALTER TABLE `membrecia`
  ADD CONSTRAINT `membrecia_ibfk_1` FOREIGN KEY (`Id_Tipo_Membrecia`) REFERENCES `tipo_membrecia` (`Id_Tipo_Membrecia`);

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`Id_Cliente`) REFERENCES `cliente` (`Id_Cliente`),
  ADD CONSTRAINT `pago_ibfk_2` FOREIGN KEY (`Id_Tipo_Usuario`) REFERENCES `tipo_usuario` (`Id_Tipo`),
  ADD CONSTRAINT `pago_ibfk_3` FOREIGN KEY (`Id_Metodo_Pago`) REFERENCES `metodo_de_pago` (`Id_Metodo_Pago`),
  ADD CONSTRAINT `pago_ibfk_4` FOREIGN KEY (`Id_Membrecia`) REFERENCES `membrecia` (`Id_Membrecia`),
  ADD CONSTRAINT `pago_ibfk_5` FOREIGN KEY (`Id_Descuento`) REFERENCES `descuento` (`Id_Descuento`);

--
-- Filtros para la tabla `pago_producto`
--
ALTER TABLE `pago_producto`
  ADD CONSTRAINT `pago_producto_ibfk_1` FOREIGN KEY (`Id_Pago`) REFERENCES `pago` (`Id_Pago`),
  ADD CONSTRAINT `pago_producto_ibfk_2` FOREIGN KEY (`Id_Producto`) REFERENCES `producto` (`Id_Producto`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`Id_Administrador`) REFERENCES `administrador` (`Id_Administrador`);

--
-- Filtros para la tabla `progreso_cliente`
--
ALTER TABLE `progreso_cliente`
  ADD CONSTRAINT `progreso_cliente_ibfk_1` FOREIGN KEY (`Id_Clase_Personalizada`) REFERENCES `clase_personalizada` (`Id_Solicitud`);

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`Id_Producto`) REFERENCES `producto` (`Id_Producto`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
