-- ========================================================
-- BASE DE DATOS - PROYECTO FINAL DAM - TIENDA URBANA
-- Stack: MySQL + PHP + HTML5 + CSS3 + Bootstrap + JS
-- Entorno recomendado: MAMP
-- ========================================================

DROP DATABASE IF EXISTS tienda;
CREATE DATABASE tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    imagen VARCHAR(255) NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    telefono VARCHAR(30) NULL,
    direccion VARCHAR(255) NULL,
    ciudad VARCHAR(100) NULL,
    provincia VARCHAR(100) NULL,
    codigo_postal VARCHAR(15) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    precio_anterior DECIMAL(10,2) NULL,
    imagen_principal VARCHAR(255) NOT NULL,
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categorias FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

CREATE TABLE producto_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_imagenes_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    talla VARCHAR(10) NOT NULL,
    color VARCHAR(40) NOT NULL,
    unidades INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_stock_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_stock_producto_variante (producto_id, talla, color)
) ENGINE=InnoDB;

CREATE TABLE cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    tipo ENUM('porcentaje','fijo') NOT NULL DEFAULT 'porcentaje',
    valor DECIMAL(10,2) NOT NULL,
    minimo DECIMAL(10,2) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    caduca_en DATE NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    numero VARCHAR(40) NOT NULL UNIQUE,
    estado ENUM('pendiente','pagado','preparando','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    nombre_envio VARCHAR(120) NOT NULL,
    email_envio VARCHAR(160) NOT NULL,
    telefono_envio VARCHAR(30) NOT NULL,
    direccion_envio VARCHAR(255) NOT NULL,
    ciudad_envio VARCHAR(100) NOT NULL,
    provincia_envio VARCHAR(100) NOT NULL,
    cp_envio VARCHAR(15) NOT NULL,
    metodo_pago ENUM('tarjeta_demo','paypal_demo','contrareembolso') NOT NULL DEFAULT 'tarjeta_demo',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0,
    envio DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    cupon_codigo VARCHAR(40) NULL,
    notas TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pedido_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NULL,
    nombre_producto VARCHAR(160) NOT NULL,
    talla VARCHAR(10) NOT NULL,
    color VARCHAR(40) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    total_linea DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalles_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalles_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE favoritos (
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, producto_id),
    CONSTRAINT fk_fav_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_fav_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    puntuacion TINYINT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT NOT NULL,
    aprobado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categorias (nombre, slug, descripcion, imagen) VALUES
('Camisetas', 'camisetas', 'Camisetas urbanas oversize con diseño streetwear.', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1200&q=80'),
('Sudaderas', 'sudaderas', 'Sudaderas premium para outfits urbanos.', 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=1200&q=80'),
('Pantalones', 'pantalones', 'Cargo, denim y pantalón ancho.', 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?auto=format&fit=crop&w=1200&q=80'),
('Accesorios', 'accesorios', 'Gorras, bolsos y complementos.', 'https://images.unsplash.com/photo-1521369909029-2afed882baee?auto=format&fit=crop&w=1200&q=80');

INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion, ciudad, provincia, codigo_postal) VALUES
('Administrador', 'admin@tienda.local', '$2y$12$BIEvtmbPfWo8H2wBfDSs2ePI84hTcyzppNCeU84Sg/7iB3YyjmGvS', 'admin', '600000000', 'Calle Admin 1', 'Madrid', 'Madrid', '28001'),
('Cliente Demo', 'cliente@tienda.local', '$2y$12$BIEvtmbPfWo8H2wBfDSs2ePI84hTcyzppNCeU84Sg/7iB3YyjmGvS', 'cliente', '611111111', 'Calle Cliente 10', 'Madrid', 'Madrid', '28002');

INSERT INTO productos (categoria_id, nombre, slug, descripcion, precio, precio_anterior, imagen_principal, destacado) VALUES
(1, 'Camiseta Oversize Graffiti', 'camiseta-oversize-graffiti', 'Camiseta oversize de algodón orgánico con print estilo graffiti. Corte amplio, cuello reforzado y tacto premium.', 29.90, 39.90, 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?auto=format&fit=crop&w=900&q=80', 1),
(1, 'Camiseta Minimal Logo', 'camiseta-minimal-logo', 'Camiseta negra minimalista con logo bordado. Ideal para combinar con cargo o denim.', 24.90, NULL, 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?auto=format&fit=crop&w=900&q=80', 0),
(2, 'Sudadera Heavyweight Smoke', 'sudadera-heavyweight-smoke', 'Sudadera heavyweight con capucha, bolsillo canguro y acabado lavado. Gramaje alto.', 59.90, 74.90, 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?auto=format&fit=crop&w=900&q=80', 1),
(2, 'Hoodie Urban Night', 'hoodie-urban-night', 'Hoodie negro con bordado frontal y fit relajado. Perfecto para temporada fría.', 54.90, NULL, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=80', 1),
(3, 'Cargo Wide Fit Arena', 'cargo-wide-fit-arena', 'Pantalón cargo ancho con bolsillos laterales, cintura ajustable y tejido resistente.', 49.90, 64.90, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80', 1),
(3, 'Denim Baggy Washed', 'denim-baggy-washed', 'Vaquero baggy efecto lavado con pierna ancha. Inspiración skate y streetwear.', 52.90, NULL, 'https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=900&q=80', 0),
(4, 'Gorra Five Panel', 'gorra-five-panel', 'Gorra five panel con cierre ajustable y bordado frontal.', 19.90, NULL, 'https://images.unsplash.com/photo-1521369909029-2afed882baee?auto=format&fit=crop&w=900&q=80', 1),
(4, 'Bolso Crossbody Utility', 'bolso-crossbody-utility', 'Bolso crossbody con compartimentos, tejido técnico y correa regulable.', 34.90, 44.90, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80', 0);

INSERT INTO producto_imagenes (producto_id, ruta, orden) VALUES
(1, 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?auto=format&fit=crop&w=900&q=80', 1),
(1, 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?auto=format&fit=crop&w=900&q=80', 2),
(3, 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?auto=format&fit=crop&w=900&q=80', 1),
(5, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80', 1);

INSERT INTO stock (producto_id, talla, color, unidades) VALUES
(1, 'S', 'Negro', 8),(1, 'M', 'Negro', 12),(1, 'L', 'Negro', 6),(1, 'XL', 'Blanco', 3),
(2, 'S', 'Negro', 5),(2, 'M', 'Blanco', 9),(2, 'L', 'Gris', 4),
(3, 'S', 'Gris', 4),(3, 'M', 'Gris', 10),(3, 'L', 'Negro', 7),(3, 'XL', 'Negro', 2),
(4, 'S', 'Negro', 8),(4, 'M', 'Negro', 6),(4, 'L', 'Negro', 5),
(5, 'S', 'Arena', 4),(5, 'M', 'Arena', 6),(5, 'L', 'Verde', 5),
(6, 'S', 'Azul', 3),(6, 'M', 'Azul', 7),(6, 'L', 'Negro', 4),
(7, 'Única', 'Negro', 20),(7, 'Única', 'Beige', 10),
(8, 'Única', 'Negro', 8),(8, 'Única', 'Verde', 6);

INSERT INTO cupones (codigo, tipo, valor, minimo, caduca_en) VALUES
('DAM10', 'porcentaje', 10.00, 30.00, '2027-12-31'),
('ENVIO5', 'fijo', 5.00, 50.00, '2027-12-31');

INSERT INTO reviews (producto_id, usuario_id, nombre, puntuacion, comentario) VALUES
(1, 2, 'Cliente Demo', 5, 'Muy buena calidad y corte oversize real.'),
(3, 2, 'Cliente Demo', 4, 'La sudadera pesa bastante y abriga mucho.'),
(5, 2, 'Cliente Demo', 5, 'El cargo queda ancho y cómodo.');
