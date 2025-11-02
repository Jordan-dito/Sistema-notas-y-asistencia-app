-- --------------------------------------------------------
--
-- Table structure for table `material_reforzamiento`
-- Sistema de material de reforzamiento para estudiantes reprobados
-- Profesor sube material (texto, imágenes, PDF) para estudiantes reprobados
--
CREATE TABLE IF NOT EXISTS `material_reforzamiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materia_id` int(11) NOT NULL,
  `estudiante_id` int(11) DEFAULT NULL COMMENT 'NULL = material para todos los reprobados de la materia',
  `profesor_id` int(11) NOT NULL,
  `año_academico` year(4) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_contenido` enum('texto','imagen','pdf','link','video') NOT NULL DEFAULT 'texto',
  `contenido` text DEFAULT NULL COMMENT 'Texto o ruta del archivo subido',
  `archivo_nombre` varchar(255) DEFAULT NULL COMMENT 'Nombre original del archivo',
  `archivo_ruta` varchar(500) DEFAULT NULL COMMENT 'Ruta del archivo en el servidor',
  `archivo_tipo` varchar(100) DEFAULT NULL COMMENT 'MIME type del archivo',
  `archivo_tamaño` int(11) DEFAULT NULL COMMENT 'Tamaño en bytes',
  `url_externa` varchar(500) DEFAULT NULL COMMENT 'URL si tipo_contenido es link o video',
  `fecha_publicacion` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL COMMENT 'Opcional, basado en fecha_fin de configuracion_materia',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reforzamiento_materia` (`materia_id`),
  KEY `idx_reforzamiento_estudiante` (`estudiante_id`),
  KEY `idx_reforzamiento_profesor` (`profesor_id`),
  KEY `idx_reforzamiento_fecha` (`fecha_publicacion`),
  CONSTRAINT `reforzamiento_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reforzamiento_ibfk_2` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reforzamiento_ibfk_3` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Vista: Estudiantes reprobados con material disponible
-- Muestra estudiantes reprobados (promedio < 60) que tienen material de reforzamiento
--
CREATE OR REPLACE VIEW `vista_reprobados_con_material` AS
SELECT 
    n.estudiante_id,
    CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
    n.materia_id,
    m.nombre as nombre_materia,
    n.promedio,
    n.año_academico,
    COUNT(DISTINCT mr.id) as total_materiales,
    MAX(mr.fecha_publicacion) as ultimo_material_fecha
FROM notas n
JOIN estudiantes e ON n.estudiante_id = e.id
JOIN materias m ON n.materia_id = m.id
LEFT JOIN material_reforzamiento mr ON n.estudiante_id = mr.estudiante_id 
    AND n.materia_id = mr.materia_id 
    AND n.año_academico = mr.año_academico
    AND mr.estado = 'activo'
WHERE n.promedio < 60.00
    AND n.estado = 'activo'
    AND e.estado = 'activo'
GROUP BY n.estudiante_id, n.materia_id, n.año_academico;

-- --------------------------------------------------------
--
-- Ejemplo de datos
--
-- Material general para todos los reprobados de una materia
INSERT INTO `material_reforzamiento` (`materia_id`, `estudiante_id`, `profesor_id`, `año_academico`, `titulo`, `descripcion`, `tipo_contenido`, `contenido`, `fecha_publicacion`) VALUES
(5, NULL, 5, '2025', 'Material de Refuerzo - Ciencias Naturales', 'Guía de estudio para estudiantes reprobados', 'texto', 'Contenido del material de refuerzo...', CURDATE());

-- Material específico para un estudiante
INSERT INTO `material_reforzamiento` (`materia_id`, `estudiante_id`, `profesor_id`, `año_academico`, `titulo`, `tipo_contenido`, `contenido`, `fecha_publicacion`) VALUES
(5, 19, 5, '2025', 'Ejercicios personalizados para Dilan', 'texto', 'Ejercicios específicos basados en tus debilidades', CURDATE());

