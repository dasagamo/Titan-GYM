-- ==========================================================
-- TABLA: especialidad
-- ==========================================================
CREATE TABLE especialidad (
  Id_Especialidad int NOT NULL AUTO_INCREMENT,
  Nombre_Especialidad varchar(100) NOT NULL,
  Descripcion text,
  PRIMARY KEY (Id_Especialidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `especialidad` (`Id_Especialidad`, `Nombre_Especialidad`, `Descripcion`) VALUES
(1, 'pilates', 'en su era pilates\r\n');

-- ==========================================================
-- TABLA: tipo_usuario
-- ==========================================================
CREATE TABLE tipo_usuario (
  Id_Tipo INT NOT NULL AUTO_INCREMENT,
  Tipo VARCHAR(50) NOT NULL,
  PRIMARY KEY (Id_Tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `tipo_usuario` (`Id_Tipo`, `Tipo`) VALUES
(1, 'administrador'),
(2, 'entrenador'),
(3, 'cliente');

-- ==========================================================
-- TABLA: cliente
-- ==========================================================
CREATE TABLE cliente (
  Id_Cliente INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(100) NOT NULL,
  Apellido VARCHAR(100) NOT NULL,
  Telefono VARCHAR(20),
  Correo VARCHAR(100) UNIQUE NOT NULL,
  Contrasena VARCHAR(255) NOT NULL,
  Id_Tipo INT NOT NULL,
  PRIMARY KEY (Id_Cliente),
  FOREIGN KEY (Id_Tipo) REFERENCES tipo_usuario(Id_Tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: entrenador
-- ==========================================================
CREATE TABLE entrenador (
  Id_Entrenador INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(100) NOT NULL,
  Apellido VARCHAR(100) NOT NULL,
  Especialidad VARCHAR(100),
  Telefono VARCHAR(20),
  Correo VARCHAR(100) UNIQUE,
  Contrasena VARCHAR(255),
  Id_Tipo INT NOT NULL,
  Id_Especialidad INT,
  PRIMARY KEY (Id_Entrenador),
  FOREIGN KEY (Id_Especialidad) REFERENCES especialidad(Id_Especialidad),
  FOREIGN KEY (Id_Tipo) REFERENCES tipo_usuario(Id_Tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: administrador
-- ==========================================================
CREATE TABLE administrador (
  Id_Admin INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(100) NOT NULL,
  Correo VARCHAR(100) UNIQUE,
  Contrasena VARCHAR(255),
  Id_Tipo INT NOT NULL,
  PRIMARY KEY (Id_Admin),
  FOREIGN KEY (Id_Tipo) REFERENCES tipo_usuario(Id_Tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==========================================================
-- TABLA: tipo_membrecia  
-- ==========================================================
CREATE TABLE tipo_membrecia (
  Id_Tipo_Membrecia int NOT NULL AUTO_INCREMENT,
  Nombre_Tipo varchar(50) NOT NULL,
  Precio decimal(10,2) NOT NULL,
  PRIMARY KEY (Id_Tipo_Membrecia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tipo_membrecia` (`Id_Tipo_Membrecia`, `Nombre_Tipo`, `Precio`) VALUES
(1, 'basica', 50.00),
(2, 'premium', 80.00);

-- ==========================================================
-- TABLA: membrecia
-- ==========================================================
CREATE TABLE membrecia (
  Id_Membrecia int NOT NULL AUTO_INCREMENT,
  Id_Tipo_Membrecia int NOT NULL,
  Duracion int NOT NULL,
  Fecha_Inicio date DEFAULT NULL,
  Fecha_Fin date DEFAULT NULL,
  PRIMARY KEY (Id_Membrecia),
  FOREIGN KEY (Id_Tipo_Membrecia) REFERENCES tipo_membrecia(Id_Tipo_Membrecia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `membrecia` (`Id_Membrecia`, `Id_Tipo_Membrecia`, `Duracion`, `Fecha_Inicio`, `Fecha_Fin`) VALUES
(1, 1, 30, '2025-10-01', '2025-10-31'),
(2, 2, 30, NULL, NULL);


-- ==========================================================
-- TABLA: clase
-- ==========================================================
CREATE TABLE clase (
  Id_Clase INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(100) NOT NULL,
  Horario VARCHAR(50) NOT NULL,
  Cupo_Maximo INT NOT NULL,
  Id_Entrenador INT NOT NULL,
  PRIMARY KEY (Id_Clase),
  FOREIGN KEY (Id_Entrenador) REFERENCES entrenador(Id_Entrenador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: Clase_Personalizada
-- ==========================================================
CREATE TABLE clase_personalizada (
  Id_Solicitud int NOT NULL AUTO_INCREMENT,
  Id_Entrenador int NOT NULL,
  Id_Cliente int NOT NULL,
  Id_Membrecia int DEFAULT NULL,
  Fecha datetime NOT NULL,
  Estado tinyint(1) NOT NULL DEFAULT '1',
  Observaciones text,
  PRIMARY KEY (Id_Solicitud),
  FOREIGN KEY (Id_Entrenador) REFERENCES entrenador(Id_Entrenador),
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente),
  FOREIGN KEY (Id_Membrecia) REFERENCES membrecia(Id_Membrecia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA : cliente_clase
-- ==========================================================
CREATE TABLE cliente_clase (
  Id_Cliente int NOT NULL,
  Id_Clase int NOT NULL,
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente),
  FOREIGN KEY (Id_Clase) REFERENCES clase(Id_Clase),
  Fecha_Inscripcion datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: metodo_de_pago
-- ==========================================================
CREATE TABLE metodo_de_pago (
  Id_Metodo_Pago int NOT NULL AUTO_INCREMENT,
  Metodo varchar(50) NOT NULL,
  PRIMARY KEY (Id_Metodo_Pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `metodo_de_pago` (`Id_Metodo_Pago`, `Metodo`) VALUES
(1, 'tarjeta_credito'),
(2, 'paypal'),
(3, 'transferencia_bancaria');

-- ==========================================================
-- TABLA: ejercicios
-- ==========================================================
CREATE TABLE Ejercicios(
  Id_Ejercicio INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(100) NOT NULL,
  Descripcion TEXT,
  PRIMARY KEY (Id_Ejercicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: imagen_ejercicio
-- ==========================================================
CREATE TABLE imagen_ejercicio (
  Id_Imagen_Ejercicio INT NOT NULL AUTO_INCREMENT,
  Id_Ejercicio INT NOT NULL,
  Ruta_Imagen VARCHAR(255) NOT NULL,
  PRIMARY KEY (Id_Imagen_Ejercicio),
  FOREIGN KEY (Id_Ejercicio) REFERENCES Ejercicios(Id_Ejercicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: rutina
-- ==========================================================
CREATE TABLE rutina (
  Id_Rutina INT NOT NULL AUTO_INCREMENT,
  Id_Entrenador INT NOT NULL,
  Id_Cliente INT NOT NULL,
  Id_Ejercicio INT NOT NULL,
  Repeticiones INT,
  Series INT,
  Descripcion TEXT,
  Fecha DATE,
  PRIMARY KEY (Id_Rutina),
  FOREIGN KEY (Id_Entrenador) REFERENCES entrenador(Id_Entrenador),
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente),
  FOREIGN KEY (Id_Ejercicio) REFERENCES Ejercicios(Id_Ejercicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: progreso_cliente
-- ==========================================================
CREATE TABLE progreso_cliente (
  Id_Progreso int NOT NULL AUTO_INCREMENT,
  Id_Cliente int NOT NULL,
  Fecha datetime NOT NULL,
  Peso decimal(6,2) DEFAULT NULL,
  Medidas_Cintura decimal(6,2) DEFAULT NULL,
  Medidas_Pecho decimal(6,2) DEFAULT NULL,
  Medidas_Brazo decimal(6,2) DEFAULT NULL,
  Observaciones text,
  PRIMARY KEY (Id_Progreso),
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: ubicacion
-- ==========================================================
CREATE TABLE ubicacion (
  Id_Ubicacion INT NOT NULL AUTO_INCREMENT,
  Ciudad VARCHAR(100),
  Direccion VARCHAR(200),
  PRIMARY KEY (Id_Ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: acceso
-- ==========================================================
CREATE TABLE acceso (
  Id_Acceso int NOT NULL,
  Id_Cliente int DEFAULT NULL,
  Id_Administrador int DEFAULT NULL,
  Id_Entrenador int DEFAULT NULL,
  Estado_Acceso enum('activo','inactivo') DEFAULT 'activo',
  Codigo varchar(255) DEFAULT NULL,
  Fecha datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Fecha_Generado datetime DEFAULT NULL,
  PRIMARY KEY (Id_Acceso),
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente),
  FOREIGN KEY (Id_Administrador) REFERENCES administrador(Id_Admin),
  FOREIGN KEY (Id_Entrenador) REFERENCES entrenador(Id_Entrenador)
  )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

  -- ==========================================================
-- TABLA: categoria producto
-- ==========================================================
CREATE TABLE categoria_producto (
  Id_Categoria INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(100) NOT NULL,
  PRIMARY KEY (Id_Categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==========================================================
-- TABLA: producto
-- ==========================================================
CREATE TABLE producto (
  Id_Producto INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(150) NOT NULL,
  Descripcion TEXT,
  Precio DECIMAL(10,2) NOT NULL,
  Stock INT NOT NULL,
  Id_Categoria INT,
  PRIMARY KEY (Id_Producto),
  FOREIGN KEY (Id_Categoria) REFERENCES categoria_producto(Id_Categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: proveedor
-- ==========================================================
CREATE TABLE `proveedor` (
  Id_Proveedor int NOT NULL AUTO_INCREMENT,
  Nombre varchar(150) NOT NULL,
  Telefono varchar(30) DEFAULT NULL,
  Correo varchar(150) DEFAULT NULL,
  Id_Producto int NOT NULL,
  Precio_Proveedor decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (Id_Proveedor),
  FOREIGN KEY (Id_Producto) REFERENCES producto(Id_Producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: inventario
-- ==========================================================
CREATE TABLE inventario (
  Id_Inventario int NOT NULL,
  Id_Producto int NOT NULL,
  Cantidad int NOT NULL DEFAULT '0',
  Ubicacion varchar(150) DEFAULT NULL,
  PRIMARY KEY (Id_Inventario),
  FOREIGN KEY (Id_Producto) REFERENCES producto(Id_Producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: descuento
-- ==========================================================
CREATE TABLE descuento (
  Id_Descuento INT NOT NULL AUTO_INCREMENT,
  Id_Membresia INT DEFAULT NULL,
  Id_Tipo_Usuario INT DEFAULT NULL,
  Id_Producto INT DEFAULT NULL,
  Porcentaje INT NOT NULL,
  PRIMARY KEY (Id_Descuento),
  FOREIGN KEY (Id_Membresia) REFERENCES membrecia(Id_Membrecia),
  FOREIGN KEY (Id_Tipo_Usuario) REFERENCES tipo_usuario(Id_Tipo),
  FOREIGN KEY (Id_Producto) REFERENCES producto(Id_Producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==========================================================
-- TABLA: pago Membrecia
-- ==========================================================
CREATE TABLE pago_membrecia (
  Id_Pago int NOT NULL,
  Id_Cliente int DEFAULT NULL,
  Id_Tipo_Usuario int DEFAULT NULL,
  Id_Metodo_Pago int NOT NULL,
  Id_Membrecia int NOT NULL,
  Id_Descuento int DEFAULT NULL,
  IVA decimal(5,2) DEFAULT '0.00',
  Valor decimal(10,2) NOT NULL,
  Valor_Total decimal(12,2) NOT NULL,
  Fecha datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (Id_Pago),
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente),
  FOREIGN KEY (Id_Tipo_Usuario) REFERENCES tipo_usuario(Id_Tipo),
  FOREIGN KEY (Id_Metodo_Pago) REFERENCES metodo_de_pago(Id_Metodo_Pago),
  FOREIGN KEY (Id_Membrecia) REFERENCES membrecia(Id_Membrecia),
  FOREIGN KEY (Id_Descuento) REFERENCES descuento(Id_Descuento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: pago_producto
-- ==========================================================
CREATE TABLE pago_producto (
  Id_Pago int NOT NULL AUTO_INCREMENT,
  Id_Producto int NOT NULL,
  Cantidad int NOT NULL DEFAULT '1',
  PRIMARY KEY (Id_Pago),
  FOREIGN KEY (Id_Producto) REFERENCES producto(Id_Producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: pedido
-- ==========================================================
CREATE TABLE pedido (
  Id_Pedido INT NOT NULL AUTO_INCREMENT,
  Id_Cliente INT NOT NULL,
  Fecha_Pedido DATE NOT NULL,
  Total DECIMAL(10,2) NOT NULL,
  Estado VARCHAR(50) DEFAULT 'Pendiente',
  PRIMARY KEY (Id_Pedido),
  FOREIGN KEY (Id_Cliente) REFERENCES cliente(Id_Cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================================
-- TABLA: detalle_pedido
-- ==========================================================
CREATE TABLE detalle_pedido (
  Id_Detalle INT NOT NULL AUTO_INCREMENT,
  Id_Pedido INT NOT NULL,
  Id_Producto INT NOT NULL,
  Cantidad INT NOT NULL,
  Precio_Unitario DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (Id_Detalle),
  FOREIGN KEY (Id_Pedido) REFERENCES pedido(Id_Pedido),
  FOREIGN KEY (Id_Producto) REFERENCES producto(Id_Producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
