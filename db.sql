-- ==========================================
-- SCRIPT DE BASE DE DATOS PARA "SOLO COMPU"
-- ==========================================
-- Diseñado para MySQL / MariaDB (Compatible con XAMPP, phpMyAdmin, etc.)

CREATE DATABASE IF NOT EXISTS solocompu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE solocompu_db;

-- 1. TABLA DE USUARIOS
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Actualización para bases de datos creadas con una versión anterior.
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS rol ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario' AFTER password;

UPDATE usuarios
SET rol = 'admin'
WHERE correo = 'quinteroadan012@gmail.com';

-- 2. TABLA DE PRODUCTOS
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(150) NOT NULL,
    categoria ENUM('componentes', 'portatiles', 'computadores') NOT NULL,
    precio INT NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    descripcion_corta VARCHAR(255) NOT NULL,
    descripcion_completa TEXT NOT NULL,
    calidad ENUM('Economica', 'Media', 'Gama Alta') NOT NULL,
    marca VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- 3. INSERCIÓN DE PRODUCTOS DE PRUEBA (Precios en COP de $500,000 a $10,000,000)
INSERT INTO productos (nombre_producto, categoria, precio, imagen, descripcion_corta, descripcion_completa, calidad, marca) VALUES
(
    'Procesador AMD Ryzen 5 5600X', 
    'componentes', 
    850000, 
    'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=500&auto=format&fit=crop&q=60',
    '6 núcleos y 12 hilos, 4.6GHz Max Boost, ideal para gaming de entrada y productividad.', 
    'El procesador AMD Ryzen 5 5600X es el estándar de oro para gaming y multitarea de gama media. Con una arquitectura Zen 3 de 7nm de alta eficiencia energética, ofrece un rendimiento térmico espectacular y velocidades de reloj de hasta 4.6 GHz de fábrica.', 
    'Media', 
    'AMD'
),
(
    'Tarjeta Gráfica ASUS ROG Strix RTX 4070 Ti', 
    'componentes', 
    4300000, 
    'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=500&auto=format&fit=crop&q=80',
    '12GB GDDR6X, DLSS 3.0, diseño térmico de triple ventilador axial ROG.', 
    'Lleva tu rendimiento visual al límite con la ASUS ROG Strix GeForce RTX 4070 Ti. Diseñada con la arquitectura Ada Lovelace de NVIDIA, esta tarjeta gráfica soporta Ray Tracing de tercera generación y DLSS 3 para tasas de cuadros ultra fluidas en 1440p y 4K.', 
    'Gama Alta', 
    'ASUS'
),
(
    'Portátil Lenovo IdeaPad Slim 3 AMD', 
    'portatiles', 
    1850000, 
    'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&auto=format&fit=crop&q=60',
    'AMD Ryzen 5, 8GB RAM, 512GB SSD PCIe, Pantalla de 15.6 FHD.', 
    'El Lenovo IdeaPad Slim 3 es perfecto para el estudio y trabajo diario. Su diseño delgado y liviano se combina con un potente procesador AMD Ryzen y almacenamiento de estado sólido para encendidos instantáneos y navegación fluida sin retrasos.', 
    'Economica', 
    'Lenovo'
),
(
    'Portátil Gamer ASUS ROG Zephyrus G14', 
    'portatiles', 
    7200000, 
    'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500&auto=format&fit=crop&q=60',
    'AMD Ryzen 9, 16GB DDR5, RTX 4060, Pantalla ROG Nebula 120Hz.', 
    'Compacto, potente y elegante. El ASUS ROG Zephyrus G14 redefine el gaming portátil con una asombrosa pantalla de alta fidelidad, chasis metálico ultraligero y el poder del Ryzen 9 emparejado con una GPU RTX 4060 para jugar en cualquier lugar.', 
    'Gama Alta', 
    'ASUS'
),
(
    'Computador de Escritorio Gamer Intel Core i7', 
    'computadores', 
    5500000, 
    'https://images.unsplash.com/photo-1547082299-de196ea013d6?w=500&auto=format&fit=crop&q=60',
    'Intel Core i7-13700F, 32GB RAM, 1TB NVMe, RTX 3060, Refrigeración Líquida.', 
    'Un monstruo listo para jugar y transmitir. Este ensamble personalizado cuenta con refrigeración líquida RGB de 240mm, chasis de vidrio templado con ventiladores controlables y componentes premium para garantizar la máxima estabilidad y flujo de aire.', 
    'Gama Alta', 
    'Intel'
),
(
    'Portátil HP ProBook 440 G10', 
    'portatiles', 
    3200000, 
    'https://images.unsplash.com/photo-1496181130204-7552cc1524e2?w=500&auto=format&fit=crop&q=60',
    'Intel Core i5, 16GB DDR4, 512GB SSD, Windows 11 Pro.', 
    'El HP ProBook 440 ofrece a las empresas en crecimiento rendimiento de nivel profesional, seguridad multicapa y durabilidad en un diseño liviano pero resistente para trabajar con confianza desde la oficina o de viaje.', 
    'Media', 
    'HP'
),
(
    'Memoria RAM Corsair Vengeance RGB Pro 16GB', 
    'componentes', 
    520000, 
    'https://images.unsplash.com/photo-1562976540-1502c2145186?w=500&auto=format&fit=crop&q=60',
    'DDR4 3200MHz (2x8GB) con iluminación dinámica RGB multi-zona.', 
    'Mejora el rendimiento de tu sistema con estilo. La memoria Corsair Vengeance RGB Pro ofrece un ancho de banda superior y tiempos de respuesta optimizados para las plataformas AMD e Intel más recientes, acompañada de un difusor térmico de aluminio.', 
    'Media', 
    'Corsair'
),
(
    'Computador de Oficina Completo Lenovo', 
    'computadores', 
    1650000, 
    'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60',
    'Intel Core i3, 8GB RAM, 256GB SSD, Monitor de 21.5, Teclado y Mouse.', 
    'La solución de productividad de escritorio todo en uno para tu negocio u oficina en casa. Este combo incluye una torre compacta Lenovo de bajo consumo y un monitor Full HD de fatiga visual ultra-baja para largas jornadas de trabajo.', 
    'Economica', 
    'Lenovo'
);
