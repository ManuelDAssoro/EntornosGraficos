-- Crear la base de datos, si no existe
-- CREATE DATABASE shopping_db;

-- Conectarse a la base de datos recién creada
-- \c shopping_db;

-- Crear tabla usuarios
CREATE TABLE usuarios (
  codUsuario SERIAL PRIMARY KEY,
  nombreUsuario VARCHAR(100) NOT NULL,
  claveUsuario VARCHAR(255) NOT NULL,
  tipoUsuario VARCHAR(50) CHECK (tipoUsuario IN ('administrador', 'dueno', 'cliente')) NOT NULL,
  categoriaCliente VARCHAR(50) CHECK (categoriaCliente IN ('Inicial', 'Medium', 'Premium')),
  estado VARCHAR(20) DEFAULT 'pendiente'
);

-- Insertar datos de prueba en usuarios
INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, categoriaCliente, estado) VALUES
('admin1@shopping.com', '$2y$10$MiHi21t44MX93RaiktkrTORUMkMpG.gX2dGb7YcOBJgNNVqMI.GYi', 'administrador', NULL, 'aprobado'),
('cliente1@shopping.com', '$2y$10$MiHi21t44MX93RaiktkrTORUMkMpG.gX2dGb7YcOBJgNNVqMI.GYi', 'cliente', 'Premium', 'activo'),
('cliente2@shopping.com', '$2y$10$MiHi21t44MX93RaiktkrTORUMkMpG.gX2dGb7YcOBJgNNVqMI.GYi', 'cliente', 'Medium', 'activo'),
('dueno1@shopping.com', '$2y$10$MiHi21t44MX93RaiktkrTORUMkMpG.gX2dGb7YcOBJgNNVqMI.GYi', 'dueno', NULL, 'activo');

-- Crear tabla locales
CREATE TABLE locales (
  codLocal SERIAL PRIMARY KEY,
  nombreLocal VARCHAR(100) NOT NULL,
  ubicacionLocal VARCHAR(50),
  rubroLocal VARCHAR(20),
  codUsuario INT,
  FOREIGN KEY (codUsuario) REFERENCES usuarios(codUsuario)
);

-- Insertar datos de prueba en locales
INSERT INTO locales (nombreLocal, ubicacionLocal, rubroLocal, codUsuario) VALUES
('Tienda A', 'Centro', 'Ropa', 3),
('Tienda B', 'Norte', 'Electrónica', 4),
('Tienda C', 'Sur', 'Muebles', 3),
('Tienda D', 'Este', 'Alimentos', 4);

-- Crear tabla novedades
CREATE TABLE novedades (
  codNovedad SERIAL PRIMARY KEY,
  textoNovedad VARCHAR(200) NOT NULL,
  fechaDesdeNovedad DATE NOT NULL,
  fechaHastaNovedad DATE NOT NULL,
  tipoUsuario VARCHAR(50) CHECK (tipoUsuario IN ('administrador', 'dueno', 'cliente')) NOT NULL
);

-- Insertar datos de prueba en novedades
INSERT INTO novedades (textoNovedad, fechaDesdeNovedad, fechaHastaNovedad, tipoUsuario) VALUES
('Promoción de verano', '2025-01-01', '2025-02-01', 'cliente'),
('Descuento del 20% en ropa', '2025-03-01', '2025-04-01', 'dueno');

-- Crear tabla promociones
CREATE TABLE promociones (
  codPromo SERIAL PRIMARY KEY,
  textoPromo VARCHAR(200) NOT NULL,
  fechaDesdePromo DATE NOT NULL,
  fechaHastaPromo DATE NOT NULL,
  categoriaCliente VARCHAR(50) CHECK (categoriaCliente IN ('Inicial', 'Medium', 'Premium')) NOT NULL,
  diasSemana VARCHAR(50) NOT NULL,  -- Aumentar el tamaño de la columna
  estadoPromo VARCHAR(50) CHECK (estadoPromo IN ('pendiente', 'aprobada', 'denegada')) DEFAULT 'pendiente',
  codLocal INT,
  FOREIGN KEY (codLocal) REFERENCES locales(codLocal)
);

-- Insertar datos de prueba en promociones
INSERT INTO promociones (textoPromo, fechaDesdePromo, fechaHastaPromo, categoriaCliente, diasSemana, estadoPromo, codLocal) VALUES
('20% OFF en toda la tienda', '2025-07-01', '2025-07-31', 'Premium', 'Lunes, Miércoles, Viernes', 'aprobada', 1),
('Descuento 10% en tecnología', '2025-07-01', '2025-07-31', 'Medium', 'Martes, Jueves', 'pendiente', 2),
('Compra 1, lleva 2 en productos seleccionados', '2025-07-01', '2025-07-31', 'Inicial', 'Sábados, Domingo', 'pendiente', 3);


-- Crear tabla uso_promociones
CREATE TABLE uso_promociones (
  codCliente INT NOT NULL,
  codPromo INT NOT NULL,
  fechaUsoPromo DATE NOT NULL,
  estado VARCHAR(50) CHECK (estado IN ('enviada', 'aceptada', 'rechazada')) DEFAULT 'enviada',
  PRIMARY KEY (codCliente, codPromo),
  FOREIGN KEY (codCliente) REFERENCES usuarios(codUsuario),
  FOREIGN KEY (codPromo) REFERENCES promociones(codPromo)
);

-- Insertar datos de prueba en uso_promociones
INSERT INTO uso_promociones (codCliente, codPromo, fechaUsoPromo, estado) VALUES
(2, 1, '2025-07-01', 'aceptada'),
(3, 2, '2025-07-03', 'rechazada');

