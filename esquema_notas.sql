-- --------------------------------------------------------
--
-- Table structure for table `notas`
-- Sistema de 4 notas por estudiante/materia con promedio automático
--

CREATE TABLE IF NOT EXISTS `notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `año_academico` year(4) NOT NULL,
  `nota_1` decimal(5,2) DEFAULT NULL COMMENT 'Primera nota (0-100)',
  `nota_2` decimal(5,2) DEFAULT NULL COMMENT 'Segunda nota (0-100)',
  `nota_3` decimal(5,2) DEFAULT NULL COMMENT 'Tercera nota (0-100)',
  `nota_4` decimal(5,2) DEFAULT NULL COMMENT 'Cuarta nota (0-100)',
  `promedio` decimal(5,2) DEFAULT NULL COMMENT 'Promedio calculado automáticamente',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `estudiante_materia_año_unico` (`estudiante_id`,`materia_id`,`año_academico`),
  KEY `idx_notas_estudiante` (`estudiante_id`),
  KEY `idx_notas_materia` (`materia_id`),
  KEY `idx_notas_profesor` (`profesor_id`),
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_ibfk_3` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Trigger para calcular promedio automáticamente
-- Se actualiza cuando cambian las notas
--

DELIMITER $$

CREATE TRIGGER `calcular_promedio_notas`
BEFORE INSERT ON `notas`
FOR EACH ROW
BEGIN
    DECLARE total_notas INT DEFAULT 0;
    DECLARE suma_notas DECIMAL(10,2) DEFAULT 0;
    
    -- Contar notas no nulas
    IF NEW.nota_1 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_1;
    END IF;
    
    IF NEW.nota_2 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_2;
    END IF;
    
    IF NEW.nota_3 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_3;
    END IF;
    
    IF NEW.nota_4 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_4;
    END IF;
    
    -- Calcular promedio
    IF total_notas > 0 THEN
        SET NEW.promedio = ROUND(suma_notas / total_notas, 2);
    ELSE
        SET NEW.promedio = NULL;
    END IF;
END$$

CREATE TRIGGER `actualizar_promedio_notas`
BEFORE UPDATE ON `notas`
FOR EACH ROW
BEGIN
    DECLARE total_notas INT DEFAULT 0;
    DECLARE suma_notas DECIMAL(10,2) DEFAULT 0;
    
    -- Contar notas no nulas
    IF NEW.nota_1 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_1;
    END IF;
    
    IF NEW.nota_2 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_2;
    END IF;
    
    IF NEW.nota_3 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_3;
    END IF;
    
    IF NEW.nota_4 IS NOT NULL THEN
        SET total_notas = total_notas + 1;
        SET suma_notas = suma_notas + NEW.nota_4;
    END IF;
    
    -- Calcular promedio
    IF total_notas > 0 THEN
        SET NEW.promedio = ROUND(suma_notas / total_notas, 2);
    ELSE
        SET NEW.promedio = NULL;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
--
-- Ejemplo de datos
--

INSERT INTO `notas` (`estudiante_id`, `materia_id`, `profesor_id`, `año_academico`, `nota_1`, `nota_2`, `nota_3`, `nota_4`) VALUES
(16, 5, 5, '2025', 85.00, 90.00, 88.00, 92.00),
(19, 5, 5, '2025', 75.00, 80.00, 78.00, NULL),
(20, 6, 5, '2025', 95.00, 88.00, 92.00, 90.00);

