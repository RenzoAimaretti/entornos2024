-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: veterinaria2
-- ------------------------------------------------------
-- Server version	8.0.37
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!50503 SET NAMES utf8 */
;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */
;
/*!40103 SET TIME_ZONE='+00:00' */
;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */
;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */
;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */
;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */
;
--
-- Table structure for table `atenciones`
--
DROP TABLE IF EXISTS `atenciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `atenciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mascota` int NOT NULL,
  `id_serv` int NOT NULL,
  `id_pro` int NOT NULL,
  `fecha` datetime NOT NULL,
  `detalle` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_mascota_atencion` (`id_mascota`),
  KEY `fk_serv` (`id_serv`),
  KEY `fk_prof` (`id_pro`),
  CONSTRAINT `fk_mascota_atencion` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_prof` FOREIGN KEY (`id_pro`) REFERENCES `profesionales` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_serv` FOREIGN KEY (`id_serv`) REFERENCES `servicios` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 103 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `atenciones`
--
LOCK TABLES `atenciones` WRITE;
/*!40000 ALTER TABLE `atenciones` DISABLE KEYS */
;
INSERT INTO `atenciones`
VALUES (
    7,
    2,
    1,
    1,
    '2025-08-10 15:30:00',
    'Toto tiene que tomar pastillas para la presion arterial'
  ),
(12, 2, 1, 6, '2025-08-10 16:19:00', ''),
(
    15,
    2,
    1,
    6,
    '2025-08-12 11:00:00',
    'Atención pendiente de actualizacion por el Especialista'
  ),
(
    16,
    2,
    1,
    6,
    '2025-08-15 12:30:00',
    'Atención pendiente de actualizacion por el Especialista'
  ),
(
    17,
    2,
    1,
    6,
    '2025-08-12 11:00:00',
    'Atención pendiente de actualizacion por el Especialista'
  ),
(
    18,
    2,
    1,
    6,
    '2025-08-14 10:30:00',
    'Atención pendiente de actualizacion por el Especialista'
  ),
(25, 2, 1, 6, '2025-08-18 08:00:00', 'Presencial'),
(27, 2, 1, 6, '2025-08-18 11:00:00', 'A domicilio'),
(28, 9, 9, 1, '2025-08-18 12:00:00', 'Presencial'),
(29, 2, 1, 9, '2025-08-20 12:00:00', 'Presencial'),
(30, 2, 1, 6, '2025-08-18 10:30:00', 'Presencial'),
(31, 2, 1, 9, '2025-08-20 11:30:00', 'Presencial'),
(32, 2, 1, 6, '2025-08-18 08:15:00', 'Presencial'),
(34, 2, 1, 6, '2026-01-19 08:00:00', 'Presencial'),
(35, 2, 9, 1, '2026-01-19 08:00:00', 'Presencial'),
(36, 9, 8, 9, '2026-02-25 11:00:00', 'A domicilio'),
(37, 15, 8, 9, '2026-01-21 13:00:00', 'A domicilio'),
(38, 2, 1, 6, '2026-01-22 17:30:00', 'Presencial'),
(39, 2, 1, 6, '2026-01-26 11:30:00', 'Presencial'),
(40, 2, 1, 6, '2026-01-22 14:00:00', 'Presencial'),
(41, 2, 1, 6, '2026-01-26 09:30:00', 'Presencial'),
(
    47,
    10,
    9,
    10,
    '2026-01-22 12:30:00',
    'Atención pendiente de actualización por el Especialista'
  ),
(
    48,
    2,
    18,
    50,
    '2026-01-13 09:00:00',
    'Atención pendiente de actualización por el Especialista'
  ),
(
    49,
    2,
    19,
    51,
    '2026-01-08 15:00:00',
    'Atención pendiente de actualización por el Especialista'
  ),
(
    64,
    10,
    18,
    10,
    '2026-01-23 15:30:00',
    'Atención pendiente de actualización por el Especialista'
  ),
(
    66,
    14,
    1,
    9,
    '2026-01-28 14:00:00',
    'Atención programada'
  ),
(
    67,
    17,
    1,
    6,
    '2026-01-22 17:00:00',
    'Atención programada'
  ),
(
    68,
    8,
    9,
    6,
    '2026-01-26 08:00:00',
    'Atención programada'
  ),
(
    69,
    13,
    9,
    10,
    '2026-01-24 09:00:00',
    'Atención programada'
  ),
(
    70,
    2,
    9,
    10,
    '2026-01-24 09:30:00',
    'Atención programada'
  ),
(
    71,
    12,
    15,
    10,
    '2026-01-24 11:45:00',
    'Atención programada'
  ),
(
    72,
    17,
    18,
    6,
    '2026-01-29 16:30:00',
    'Atención programada'
  ),
(
    73,
    8,
    9,
    1,
    '2026-01-26 08:00:00',
    'Atención programada'
  ),
(
    74,
    8,
    10,
    1,
    '2026-01-26 09:30:00',
    'Atención programada'
  ),
(75, 8, 15, 1, '2026-01-23 10:30:00', ''),
(79, 2, 1, 6, '2026-01-26 12:00:00', 'Presencial'),
(80, 2, 1, 6, '2026-01-26 11:00:00', 'Presencial'),
(81, 2, 15, 1, '2026-01-26 10:00:00', 'Presencial'),
(82, 2, 1, 6, '2026-01-26 10:00:00', 'Presencial'),
(83, 2, 1, 6, '2026-01-26 10:30:00', 'Presencial'),
(84, 2, 1, 6, '2026-01-26 10:45:00', 'Presencial'),
(85, 2, 15, 1, '2026-01-26 10:45:00', 'Presencial'),
(86, 2, 1, 6, '2026-01-26 10:15:00', 'Presencial'),
(87, 17, 1, 9, '2026-01-28 13:30:00', 'Presencial'),
(91, 17, 8, 6, '2026-01-26 11:15:00', 'Presencial'),
(92, 17, 1, 6, '2026-01-26 08:15:00', 'A domicilio'),
(93, 20, 1, 9, '2026-01-28 14:30:00', 'Presencial'),
(94, 17, 1, 6, '2026-01-26 08:30:00', 'Presencial'),
(95, 20, 1, 6, '2026-01-26 12:45:00', 'Presencial'),
(96, 17, 1, 6, '2026-01-29 17:15:00', 'A domicilio'),
(
    97,
    27,
    1,
    6,
    '2026-01-27 10:45:00',
    'Atención programada'
  ),
(
    98,
    25,
    9,
    1,
    '2026-01-27 11:00:00',
    'Atención programada'
  ),
(
    99,
    9,
    9,
    1,
    '2026-01-26 17:45:00',
    'Atención programada'
  ),
(100, 9, 19, 1, '2026-01-26 17:30:00', 'Hola'),
(101, 9, 10, 1, '2026-01-26 17:15:00', 'Hola'),
(102, 9, 10, 1, '2026-01-26 17:00:00', 'QD');
/*!40000 ALTER TABLE `atenciones` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `clientes`
--
DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `ciudad` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_id_cliente` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `clientes`
--
LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */
;
INSERT INTO `clientes`
VALUES (3, NULL, 'Tucuman 1232', '43423232'),
(4, 'Rosario', 'San Lorenzo 1250', '3415026781'),
(5, NULL, NULL, NULL),
(7, 'Rosario', 'San Martin 2000', '3416789012'),
(8, 'Funes', 'Cordoba 500', '3415987654'),
(52, NULL, NULL, NULL),
(53, NULL, NULL, NULL),
(54, NULL, NULL, NULL),
(55, NULL, NULL, NULL),
(56, NULL, NULL, NULL),
(57, NULL, NULL, NULL),
(62, 'Rosario', 'Av. Pellegrini 1200', '3415550101'),
(63, 'Rosario', 'Bv. Oroño 450', '3415550102'),
(64, 'Rosario', 'San Luis 2300', '3415550103'),
(65, 'Rosario', 'Córdoba 1500', '3415550104'),
(66, 'Rosario', 'Mendoza 3400', '3415550105'),
(67, 'Rosario', 'Laprida 800', '3415550106'),
(68, 'Rosario', 'San Juan 1100', '3415550107'),
(69, 'Rosario', 'Maipú 200', '3415550108'),
(70, 'Rosario', 'Rioja 500', '3415550109'),
(71, 'Rosario', 'Paraguay 900', '3415550110'),
(
    72,
    'Granadero Baigorria',
    'Entre Ríos 600',
    '3415550111'
  ),
(73, 'Rosario', 'Corrientes 1300', '3415550112'),
(74, 'Rosario', 'Santa Fe 2100', '3415550113'),
(75, 'Rosario', 'Urquiza 1800', '3415550114'),
(76, 'Rosario', 'Tucumán 2500', '3415550115');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `especialidad`
--
DROP TABLE IF EXISTS `especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `especialidad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 11 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `especialidad`
--
LOCK TABLES `especialidad` WRITE;
/*!40000 ALTER TABLE `especialidad` DISABLE KEYS */
;
INSERT INTO `especialidad`
VALUES (1, 'Cardiologia'),
(2, 'Traumatologia'),
(3, 'Estética Canina'),
(4, 'Veterinaria'),
(5, 'Traumatología'),
(7, 'Clínica Médica'),
(8, 'Cirugía'),
(9, 'Cardiología'),
(10, 'Dermatología');
/*!40000 ALTER TABLE `especialidad` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `hospitalizaciones`
--
DROP TABLE IF EXISTS `hospitalizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `hospitalizaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mascota` int NOT NULL,
  `id_pro_deriva` int NOT NULL,
  `fecha_ingreso` datetime NOT NULL,
  `fecha_egreso_prevista` datetime DEFAULT NULL,
  `fecha_egreso_real` datetime DEFAULT NULL,
  `motivo` text NOT NULL,
  `estado` enum('Activa', 'Finalizada') DEFAULT 'Activa',
  PRIMARY KEY (`id`),
  KEY `id_mascota` (`id_mascota`),
  KEY `id_pro_deriva` (`id_pro_deriva`),
  CONSTRAINT `hospitalizaciones_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id`),
  CONSTRAINT `hospitalizaciones_ibfk_2` FOREIGN KEY (`id_pro_deriva`) REFERENCES `usuarios` (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 34 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `hospitalizaciones`
--
LOCK TABLES `hospitalizaciones` WRITE;
/*!40000 ALTER TABLE `hospitalizaciones` DISABLE KEYS */
;
INSERT INTO `hospitalizaciones`
VALUES (
    2,
    7,
    1,
    '2026-01-21 21:25:55',
    NULL,
    '2026-01-21 21:25:59',
    'Engripado',
    'Finalizada'
  ),
(
    5,
    11,
    1,
    '2026-01-21 21:33:28',
    NULL,
    '2026-01-26 16:28:49',
    'Dolor de panza',
    'Finalizada'
  ),
(
    7,
    7,
    1,
    '2026-01-22 23:55:00',
    NULL,
    '2026-01-27 20:03:53',
    'Fractura',
    'Finalizada'
  ),
(
    9,
    2,
    1,
    '2026-01-22 20:59:17',
    '2026-01-24 20:59:00',
    '2026-01-22 20:59:24',
    'Pancreatitis',
    'Finalizada'
  ),
(
    10,
    9,
    1,
    '2026-01-26 15:05:44',
    '2026-01-27 15:05:00',
    '2026-01-27 20:03:55',
    'dsv',
    'Finalizada'
  ),
(
    11,
    26,
    1,
    '2026-01-26 19:17:19',
    NULL,
    '2026-01-27 20:03:58',
    'zcv',
    'Finalizada'
  ),
(
    12,
    23,
    1,
    '2026-01-26 19:17:49',
    NULL,
    '2026-01-27 20:04:00',
    'dfv',
    'Finalizada'
  ),
(
    13,
    26,
    1,
    '2026-01-27 20:06:59',
    '2026-01-29 20:06:00',
    '2026-01-27 20:08:19',
    'fdsg',
    'Finalizada'
  ),
(
    24,
    31,
    122,
    '2026-01-25 20:10:41',
    '2026-01-28 20:10:41',
    NULL,
    'Postoperatorio cirugía de fémur, requiere observación 48hs.',
    'Activa'
  ),
(
    25,
    28,
    122,
    '2026-01-27 08:10:41',
    '2026-01-29 20:10:41',
    NULL,
    'Gastroenteritis hemorrágica, hidratación IV continua.',
    'Activa'
  ),
(
    26,
    25,
    123,
    '2026-01-24 20:10:41',
    '2026-01-28 08:10:41',
    NULL,
    'Insuficiencia renal crónica, manejo de fluidos.',
    'Activa'
  ),
(
    27,
    23,
    124,
    '2026-01-26 20:10:41',
    '2026-01-30 20:10:41',
    NULL,
    'Traumatismo encéfalo craneal, monitoreo neurológico.',
    'Activa'
  ),
(
    28,
    6,
    125,
    '2026-01-27 15:10:41',
    '2026-01-28 20:10:41',
    NULL,
    'Reacción alérgica severa, monitoreo de vías respiratorias.',
    'Activa'
  ),
(
    29,
    7,
    126,
    '2026-01-23 20:10:41',
    '2026-01-29 20:10:41',
    NULL,
    'Cuadro de Parvovirus, aislamiento y soporte vital.',
    'Activa'
  ),
(
    30,
    23,
    127,
    '2026-01-26 20:10:41',
    '2026-01-31 20:10:41',
    NULL,
    'Recuperación de cesárea compleja y cuidado de neonatos.',
    'Activa'
  ),
(
    31,
    29,
    128,
    '2026-01-27 14:10:41',
    '2026-01-30 20:10:41',
    NULL,
    'Obstrucción uretral, control diurético post-sondaje.',
    'Activa'
  ),
(
    32,
    29,
    129,
    '2026-01-25 20:10:41',
    '2026-01-28 20:10:41',
    NULL,
    'Post-endoscopía por cuerpo extraño en esófago.',
    'Activa'
  ),
(
    33,
    27,
    130,
    '2026-01-27 10:10:41',
    '2026-02-01 20:10:41',
    NULL,
    'Neumonía bacteriana grave, oxigenoterapia.',
    'Activa'
  );
/*!40000 ALTER TABLE `hospitalizaciones` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `mascotas`
--
DROP TABLE IF EXISTS `mascotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `mascotas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(320) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `raza` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `fecha_mue` date DEFAULT NULL,
  `pesoMascota` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_id_cliente_mascota` (`id_cliente`),
  CONSTRAINT `fk_id_cliente_mascota` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 32 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `mascotas`
--
LOCK TABLES `mascotas` WRITE;
/*!40000 ALTER TABLE `mascotas` DISABLE KEYS */
;
INSERT INTO `mascotas`
VALUES (
    2,
    3,
    'Tobyewe',
    NULL,
    'Perro machoewe',
    '2026-01-20',
    NULL,
    NULL
  ),
(
    6,
    7,
    'Fido',
    NULL,
    'Golden Retriever',
    '2023-05-15',
    NULL,
    25
  ),
(
    7,
    7,
    'Luna',
    NULL,
    'Gato Siames',
    '2022-10-20',
    NULL,
    5
  ),
(
    8,
    8,
    'Max',
    NULL,
    'Pastor Aleman',
    '2021-03-01',
    NULL,
    35
  ),
(9, 3, 'Ciro', NULL, 'golden', NULL, NULL, NULL),
(10, 3, 'Mateo', NULL, 'Golden', NULL, NULL, NULL),
(
    11,
    3,
    'Martin',
    NULL,
    'Golden',
    '2026-01-17',
    NULL,
    NULL
  ),
(
    12,
    3,
    'Tomas',
    NULL,
    'Golden',
    '2026-01-17',
    NULL,
    NULL
  ),
(13, 3, 'Tomi', NULL, 'Golden', '2026-01-17', NULL, NULL),
(
    14,
    3,
    'Simon',
    NULL,
    'Golden',
    '2026-01-17',
    NULL,
    NULL
  ),
(
    15,
    3,
    'Alfon',
    NULL,
    'Golden',
    '2026-01-17',
    '2026-01-26',
    NULL
  ),
(
    17,
    52,
    'Saimon',
    NULL,
    'golden',
    '2025-01-24',
    NULL,
    NULL
  ),
(
    19,
    54,
    'Profe',
    NULL,
    'Labrador',
    '2026-01-23',
    NULL,
    NULL
  ),
(
    20,
    52,
    'Tobyewe',
    NULL,
    'Golden',
    '2026-01-23',
    NULL,
    NULL
  ),
(
    23,
    7,
    'Diosito',
    NULL,
    'golden',
    '2026-01-20',
    NULL,
    NULL
  ),
(24, 7, 'GOd', NULL, 'Golden', '2026-01-19', NULL, NULL),
(25, 7, 'sga', NULL, 'asf', '2026-01-20', NULL, NULL),
(26, 7, 'Didi', NULL, 'WEF', '2026-01-20', NULL, NULL),
(27, 7, 'sDf', NULL, 'wEF', '2026-01-20', NULL, NULL),
(
    28,
    7,
    'Mario',
    NULL,
    'Caniche toy',
    '2026-01-20',
    NULL,
    NULL
  ),
(
    29,
    7,
    'Veronicaf',
    NULL,
    'Mestizo',
    '2026-01-16',
    NULL,
    NULL
  ),
(30, 7, 'Dami', NULL, 'eaga', '2026-01-23', NULL, NULL),
(31, 52, 'sdgv', NULL, 'dg', '2026-01-22', NULL, NULL);
/*!40000 ALTER TABLE `mascotas` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `profesionales`
--
DROP TABLE IF EXISTS `profesionales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `profesionales` (
  `id` int NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_esp` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_profesional_esp` (`id_esp`),
  CONSTRAINT `fk_profesional_esp` FOREIGN KEY (`id_esp`) REFERENCES `especialidad` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_profesional_usuario` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `profesionales`
--
LOCK TABLES `profesionales` WRITE;
/*!40000 ALTER TABLE `profesionales` DISABLE KEYS */
;
INSERT INTO `profesionales`
VALUES (1, '212121', 2),
(6, '3212124', 1),
(9, '3414567890', 1),
(10, '3415123456', 2),
(50, '123456789', 3),
(51, '987654321', 4),
(58, 'sdf', 3),
(59, '1234', 1),
(61, '1234567890', 2),
(122, '3414440001', 1),
(123, '3414440002', 2),
(124, '3414440003', 1),
(125, '3414440004', 3),
(126, '3414440005', 4),
(127, '3414440006', 2),
(128, '3414440007', 5),
(129, '3414440008', 3),
(130, '3414440009', 4),
(131, '3414440010', 5);
/*!40000 ALTER TABLE `profesionales` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `profesionales_horarios`
--
DROP TABLE IF EXISTS `profesionales_horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `profesionales_horarios` (
  `idPro` int NOT NULL,
  `diaSem` varchar(4) COLLATE utf8mb4_general_ci NOT NULL,
  `horaIni` time NOT NULL,
  `horaFin` time NOT NULL,
  PRIMARY KEY (`idPro`, `diaSem`),
  CONSTRAINT `fk_idPro` FOREIGN KEY (`idPro`) REFERENCES `profesionales` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `profesionales_horarios`
--
LOCK TABLES `profesionales_horarios` WRITE;
/*!40000 ALTER TABLE `profesionales_horarios` DISABLE KEYS */
;
INSERT INTO `profesionales_horarios`
VALUES (1, 'Lun', '08:00:00', '18:00:00'),
(1, 'Mar', '08:00:00', '12:00:00'),
(6, 'Jue', '14:00:00', '18:00:00'),
(6, 'Lun', '08:00:00', '13:00:00'),
(6, 'Mar', '09:00:00', '12:00:00'),
(9, 'Mie', '10:00:00', '17:00:00'),
(10, 'Sab', '09:00:00', '13:00:00'),
(10, 'Vie', '08:00:00', '12:00:00'),
(58, 'Dom', '18:00:00', '20:00:00'),
(59, 'Sáb', '18:00:00', '19:00:00'),
(61, 'Jue', '17:00:00', '19:00:00');
/*!40000 ALTER TABLE `profesionales_horarios` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `servicios`
--
DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id_esp` int NOT NULL,
  `precio` float(10, 2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_esp_id` (`id_esp`),
  CONSTRAINT `fk_esp_id` FOREIGN KEY (`id_esp`) REFERENCES `especialidad` (`id`) ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 20 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `servicios`
--
LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */
;
INSERT INTO `servicios`
VALUES (1, 'Control de corazon', 1, 5000.00),
(8, 'Ecografia del corazon', 1, 7500.00),
(9, 'Cirugia de rodilla', 2, 15000.00),
(10, 'Curación de heridas', 2, 2500.00),
(15, 'Radiografia', 2, 6000.00),
(18, 'Corte de pelo', 3, 1500.00),
(19, 'Vacunación Antirrábica', 2, 2500.00);
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `usuarios`
--
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('admin', 'cliente', 'especialista') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB AUTO_INCREMENT = 132 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `usuarios`
--
LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */
;
INSERT INTO `usuarios`
VALUES (1, 'pro', 'pro@gmail.com', 'Pro123', 'especialista'),
(2, 'admin', 'admin@gmail.com', 'Admin123', 'admin'),
(
    3,
    'Cristiano Fernandez',
    'c1@gmail.com',
    'C1',
    'cliente'
  ),
(4, 'Damian Perez', 'd@gmail.com', 'D123', 'cliente'),
(
    5,
    'renzo12',
    'renzo12@gmail.com',
    'Renzo12',
    'cliente'
  ),
(
    6,
    'Juancito',
    'juancito@gmail.com',
    'Juancito12',
    'especialista'
  ),
(
    7,
    'Ana Lopez',
    'ana.lopez@gmail.com',
    'Ana123',
    'cliente'
  ),
(
    8,
    'Carlos Ruiz',
    'carlos.ruiz@gmail.com',
    'Carlos123',
    'cliente'
  ),
(
    9,
    'Laura Martinez',
    'laura.martinez@gmail.com',
    'Laura123',
    'especialista'
  ),
(
    10,
    'Diego Perez',
    'diego.perez@gmail.com',
    'Diego123',
    'especialista'
  ),
(
    50,
    'Juan Esteticista',
    'juan@sananton.com',
    'password123',
    'especialista'
  ),
(
    51,
    'Dra. Maria Veterinaria',
    'maria@sananton.com',
    'password123',
    'especialista'
  ),
(
    52,
    'Mateo',
    'mateospertino@gmail.com',
    'Mateo1',
    'cliente'
  ),
(
    53,
    'Juli',
    'julietaarguinzoniz@gmail.com',
    'Ju1',
    'cliente'
  ),
(
    54,
    'Profe',
    'profesocratesss@gmail.com',
    'Profe1',
    'cliente'
  ),
(
    55,
    'Jorge Almiron',
    'joralmiron@gmail.com',
    'Jor1',
    'cliente'
  ),
(
    56,
    'maria',
    'maria@gmail.com',
    'Mariaaaaa1',
    'cliente'
  ),
(
    57,
    'Martin',
    'martoreda@gmail.com',
    'Martinreda1',
    'cliente'
  ),
(58, 'Manolo', 'asfa@gm.com', 'Dios', 'especialista'),
(59, 'sdfa', 'af@g.com', 'Mateo123', 'especialista'),
(61, 'sdfa', 'dfgs@s.com', 'Mateo123', 'especialista'),
(
    62,
    'Juan Pérez',
    'juan.perez@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    63,
    'María González',
    'maria.gonzalez@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    64,
    'Carlos Rodríguez',
    'carlos.rod@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    65,
    'Ana López',
    'ana.lopez@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    66,
    'Lucas Fernández',
    'lucas.fer@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    67,
    'Sofía Martínez',
    'sofia.mar@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    68,
    'Miguel Torres',
    'miguel.torres@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    69,
    'Laura Díaz',
    'laura.diaz@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    70,
    'Martín Castro',
    'martin.castro@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    71,
    'Julia Romero',
    'julia.romero@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    72,
    'Pedro Sánchez',
    'pedro.sanchez@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    73,
    'Valentina Ruiz',
    'valen.ruiz@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    74,
    'Diego Morales',
    'diego.morales@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    75,
    'Camila Ortega',
    'camila.ortega@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    76,
    'Andrés Silva',
    'andres.silva@mail.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'cliente'
  ),
(
    122,
    'Dr. Alberto Rossi',
    'alberto.rossi@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    123,
    'Dra. Elena Gómez',
    'elena.gomez@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    124,
    'Dr. Ricardo Lugano',
    'ricardo.lugano@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    125,
    'Dra. Marina Silva',
    'marina.silva@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    126,
    'Dr. Sergio Massa',
    'sergio.massa@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    127,
    'Dra. Patricia Bullrich',
    'patricia.b@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    128,
    'Dr. Javier Milei',
    'javier.m@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    129,
    'Dra. Victoria Villarruel',
    'victoria.v@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    130,
    'Dr. Axel Kicillof',
    'axel.k@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  ),
(
    131,
    'Dra. Myriam Bregman',
    'myriam.b@sananton.com',
    '$2y$10$RunR/W3.N4.N/..hashed..example',
    'especialista'
  );
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */
;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */
;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */
;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */
;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */
;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */
;
-- Dump completed on 2026-01-27 20:14:45