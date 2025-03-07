-- MySQL dump 10.13  Distrib 8.0.33, for Win64 (x86_64)
--
-- Host: localhost    Database: rotiseria
-- ------------------------------------------------------
-- Server version	5.5.24-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `idClientes` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` tinytext NOT NULL,
  `Apellido` tinytext NOT NULL,
  `Direccion` varchar(45) DEFAULT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Email` varchar(45) DEFAULT NULL,
  `DNI` int(8) DEFAULT NULL,
  PRIMARY KEY (`idClientes`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `idPedidos` int(11) NOT NULL AUTO_INCREMENT,
  `idProductos` int(11) DEFAULT NULL,
  `idClientes` int(11) DEFAULT NULL,
  `FechaPedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Cantidad` int(11) NOT NULL,
  `Fecha_Entrega` datetime DEFAULT NULL,
  `reservado` tinyint(1) DEFAULT '0',
  `Mitades` tinyint(1) DEFAULT '0',
  `PrecioTotal` float DEFAULT NULL,
  PRIMARY KEY (`idPedidos`),
  KEY `idClientes` (`idClientes`),
  KEY `fk_Pedidos_Productos1` (`idProductos`),
  CONSTRAINT `fk_Pedidos_Productos1` FOREIGN KEY (`idProductos`) REFERENCES `productos` (`idProductos`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`idProductos`) REFERENCES `productos` (`idProductos`),
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`idClientes`) REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personas`
--

DROP TABLE IF EXISTS `personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personas` (
  `Id_Persona` int(11) NOT NULL AUTO_INCREMENT,
  `Tipo` varchar(45) DEFAULT NULL,
  `Usuario` varchar(45) NOT NULL,
  `Clave` int(45) NOT NULL,
  `Domicilio` varchar(45) DEFAULT NULL,
  `DNI` int(13) NOT NULL,
  PRIMARY KEY (`Id_Persona`)
) ENGINE=InnoDB AUTO_INCREMENT=2147483647 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personas`
--

LOCK TABLES `personas` WRITE;
/*!40000 ALTER TABLE `personas` DISABLE KEYS */;
INSERT INTO `personas` VALUES (1,'Programador','Nico',12345,'Av. Pte. Peron 843',43932822),(2,'Programador','Mora',1234,'Av. Pte. Peron 845',43932824),(3,'Programador','Ponce',1,'Barrio Norte',12843655),(2147483647,'Prueba','Prueba',1,'Prueba 1',12345678);
/*!40000 ALTER TABLE `personas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `idProductos` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(45) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idProductos`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Hamburguesa Clasica',4500.00,'Carne, jamon, queso barra, lechuga, tomate, huevo frito y mayonesa casera'),(2,'Hamburguesa Doble queso',3800.00,'Carne, queso barra, cheddar, tomates y mayonesa casera'),(3,'Hamburguesa Burger ceta',5500.00,'Carne, queso barra, cheddar, jamon, panceta, cebolla caramelizadas y mayonesa casera'),(4,'Hamburguesa La buitre',5500.00,'Carne + chorizo, jamon, queso barra, cheddar, panceta, morrones asados, lechuga, tomates, huevo y ma'),(5,'Hamburguesa Mini cheese',3500.00,'Carne, cheddar, y mayonesa casera'),(89,'Hamburguesa Dylan',3800.00,'Carne, cebolla caramelizada, queso roquefort y mayonesa casera'),(90,'Hamburguesa Cooper',4200.00,'Carne, doble cheddar, salsa barbacoa, doble panceta, y mayonesa casera'),(91,'Hamburguesa Tropical',3500.00,'Carne, cheddar, aros de cebolla, lechuga, tomate y mayonesa casera'),(92,'Hamburguesa Figazza',8500.00,'Pan de figazza, triple carne, doble jamón, doble queso barra, triple cheddar, lechuga, tomate, dos h'),(93,'Hamburguesa Doblete',5500.00,'Carne x2, cheddar, panceta, cebolla caramelizada y mayonesa casera '),(94,'Hamburguesa Chesse doble',4200.00,'Carne x2, cheddar x4, mayonesa casera'),(95,'Hamburguesa Americana',4500.00,'Carne x2, cheddar x4, cebolla natural, mayonesa casera'),(96,'Lomito Clasico',6500.00,'Lomito, jamón, queso barra, lechuga, tomate, huevo frito y mayonesa casera'),(97,'Lomito Super',7000.00,'Lomito, jamón, queso barra, cheddar, morrones asados, lechuga, tomate, huevo frito y mayonesa casera'),(98,'Lomito Lomo Ceta',7500.00,'Lomito, queso barra, cheddar, jamón, panceta, cebolla, caramelizada, morrones asados, tomate, lechug'),(99,'Lomito Figazza',9500.00,'Pan de figazza, doble lomito, doble queso barra, triple cheddar, doble jamón, huevos, lechuga, tomat'),(100,'Lomito Clasico en Pan de Burguer',4500.00,'Lomito, jamón, queso barra, cheddar, lechuga, tomate, huevo frito y mayonesa casera'),(101,'Pizza Muzzarella',5500.00,'Salsa de tomate, queso muzzarella, aceitunas y oregano'),(102,'Pizza Especial',6000.00,'Salsa de tomate, queso muzzarella, jamón, morrones, huevo duro, aceitunas y orégano'),(103,'Pizza Jamon y morrones',6000.00,'Salsa de tomate, queso muzzarella,  jamón, morrones, aceitunas y orégano'),(104,'Pizza Calabresa',7000.00,'Salsa de tomate, queso muzzarella, tomate, calabresa, aceitunas y orégano'),(105,'Pizza Margarita',6000.00,'Salsa de tomate, queso muzzarella, tomate, albahaca, aceitunas y orégano'),(106,'Pizza Fugazzeta',6000.00,'Queso muzzarella, cebolla, aceitunas y orégano'),(107,'Pizza Napolitana',6000.00,'Salsa de tomate, jamón, queso muzzarella, tomate, aceitunas y orégano'),(108,'Milanesa Clasica',3500.00,'Mila de carne'),(109,'Milanesa Clasica (Para 2 personas)',6000.00,'Mila de carne'),(110,'Milanesa Napolitana (Individual)',5000.00,'Mila, salsa de tomate, muzzarella, jamón, tomate, orégano, aceitunas'),(111,'Milanesa Napolitana (Para 2 personas)',9000.00,NULL),(112,'Milanesa  Criolla (Individual)',4500.00,'Mila, 2 huevos fritos'),(113,'Milanesa Criolla (Para 2 personas)',8000.00,NULL),(114,'Milanesa Bacon (Individual)',4500.00,'Mila, cheddar, panceta y verdeo'),(115,'Milanesa Sandwich',6000.00,'Mila, queso barra, cheddar, jamón, lechuga, tomate, huevo frito, mayonesa casera'),(116,'Milanesa Sandwich en pan Burger',4000.00,'Mila, queso barra, chedar, jamón, lechuga, tomate, huevo frito, mayonesa casera'),(117,'Milanesa Figazza de Mila',9500.00,'Pan de figazza, mila, doble queso barra, triple cheddar, doble jamón, lechuga, tomate, huevo frito, '),(119,'Papas Clasicas gratinadas',5500.00,NULL),(120,'Papas Gratinadas con cheddar',6000.00,NULL),(121,'Papas Gratinadas con cheddar y panceta',7000.00,NULL),(122,'Papas Clasicas',4500.00,'');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `total`
--

DROP TABLE IF EXISTS `total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `total` (
  `idTotal` int(11) NOT NULL AUTO_INCREMENT,
  `idPedidos` int(11) DEFAULT NULL,
  `idProductos` int(11) DEFAULT NULL,
  `idClientes` int(11) DEFAULT NULL,
  `FechaPedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Cantidad` int(11) DEFAULT NULL,
  `Total` int(11) NOT NULL,
  `Mitades` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idTotal`),
  KEY `idPedidos` (`idPedidos`),
  KEY `idProductos` (`idProductos`),
  KEY `idClientes` (`idClientes`),
  CONSTRAINT `total_ibfk_1` FOREIGN KEY (`idPedidos`) REFERENCES `pedidos` (`idPedidos`) ON DELETE CASCADE,
  CONSTRAINT `total_ibfk_2` FOREIGN KEY (`idProductos`) REFERENCES `productos` (`idProductos`) ON DELETE CASCADE,
  CONSTRAINT `total_ibfk_3` FOREIGN KEY (`idClientes`) REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `total`
--

LOCK TABLES `total` WRITE;
/*!40000 ALTER TABLE `total` DISABLE KEYS */;
/*!40000 ALTER TABLE `total` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-07 18:57:45
