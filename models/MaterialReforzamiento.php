<?php
/**
 * Modelo de Material de Reforzamiento
 * Maneja las operaciones de base de datos para material de reforzamiento
 * Material que los profesores suben para estudiantes reprobados (promedio < 60)
 */

require_once '../config/connection.php';

class MaterialReforzamiento {
    private $db;
    private $uploadDir = '../uploads/reforzamiento/';
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
        // Crear directorio de uploads si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }
    
    /**
     * Subir material de reforzamiento
     */
    public function subirMaterial($materiaId, $estudianteId, $profesorId, $añoAcademico, $titulo, $descripcion, $tipoContenido, $contenido = null, $archivo = null, $urlExterna = null) {
        try {
            // Validar que el estudiante esté reprobado si es material específico
            if ($estudianteId !== null) {
                $sqlCheck = "SELECT promedio FROM notas 
                            WHERE estudiante_id = ? AND materia_id = ? AND año_academico = ? AND estado = 'activo'";
                $stmtCheck = $this->db->prepare($sqlCheck);
                $stmtCheck->execute([$estudianteId, $materiaId, $añoAcademico]);
                $nota = $stmtCheck->fetch();
                
                if (!$nota || ($nota['promedio'] !== null && $nota['promedio'] >= 60)) {
                    return [
                        'success' => false,
                        'message' => 'Este estudiante no está reprobado. El material de reforzamiento es solo para estudiantes reprobados.'
                    ];
                }
            }
            
            // Verificar que estamos dentro del período académico
            $sqlConfig = "SELECT fecha_fin FROM configuracion_materia 
                         WHERE materia_id = ? AND año_academico = ? AND estado = 'activo'";
            $stmtConfig = $this->db->prepare($sqlConfig);
            $stmtConfig->execute([$materiaId, $añoAcademico]);
            $config = $stmtConfig->fetch();
            
            $fechaVencimiento = null;
            if ($config) {
                $fechaVencimiento = $config['fecha_fin'];
            }
            
            // Insertar material (solo texto y link, sin archivos)
            $sql = "INSERT INTO material_reforzamiento 
                    (materia_id, estudiante_id, profesor_id, año_academico, titulo, descripcion, 
                     tipo_contenido, contenido, url_externa, fecha_publicacion, fecha_vencimiento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $materiaId,
                $estudianteId,
                $profesorId,
                $añoAcademico,
                $titulo,
                $descripcion,
                $tipoContenido,
                $contenido,
                $urlExterna,
                $fechaVencimiento
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Material de reforzamiento subido correctamente',
                    'data' => [
                        'id' => $this->db->lastInsertId(),
                        'materia_id' => $materiaId,
                        'estudiante_id' => $estudianteId,
                        'titulo' => $titulo,
                        'tipo_contenido' => $tipoContenido
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al guardar el material'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener material de reforzamiento para un estudiante reprobado
     */
    public function obtenerMaterialEstudiante($estudianteId, $materiaId, $añoAcademico = null) {
        try {
            if ($añoAcademico === null) {
                $añoAcademico = date('Y');
            }
            
            // Verificar que el estudiante esté reprobado
            $sqlCheck = "SELECT promedio FROM notas 
                        WHERE estudiante_id = ? AND materia_id = ? AND año_academico = ? AND estado = 'activo'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$estudianteId, $materiaId, $añoAcademico]);
            $nota = $stmtCheck->fetch();
            
            if (!$nota || ($nota['promedio'] !== null && $nota['promedio'] >= 60)) {
                return [
                    'success' => true,
                    'message' => 'No estás reprobado en esta materia',
                    'data' => [],
                    'reprobado' => false
                ];
            }
            
            // Obtener material (específico del estudiante y general de la materia)
            $sql = "SELECT 
                        mr.id,
                        mr.titulo,
                        mr.descripcion,
                        mr.tipo_contenido,
                        mr.contenido,
                        mr.url_externa,
                        mr.fecha_publicacion,
                        mr.fecha_vencimiento,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor,
                        m.nombre as nombre_materia
                    FROM material_reforzamiento mr
                    JOIN materias m ON mr.materia_id = m.id
                    JOIN profesores p ON mr.profesor_id = p.id
                    WHERE mr.materia_id = ?
                        AND mr.año_academico = ?
                        AND mr.estado = 'activo'
                        AND (mr.estudiante_id = ? OR mr.estudiante_id IS NULL)
                        AND (mr.fecha_vencimiento IS NULL OR mr.fecha_vencimiento >= CURDATE())
                    ORDER BY mr.estudiante_id DESC, mr.fecha_publicacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $añoAcademico, $estudianteId]);
            $materiales = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Material de reforzamiento obtenido correctamente',
                'data' => $materiales,
                'reprobado' => true,
                'promedio' => $nota['promedio']
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener todos los estudiantes reprobados de una materia (para profesor)
     */
    public function obtenerEstudiantesReprobados($materiaId, $profesorId, $añoAcademico = null) {
        try {
            if ($añoAcademico === null) {
                $añoAcademico = date('Y');
            }
            
            $sql = "SELECT 
                        n.estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        e.grado,
                        e.seccion,
                        n.promedio,
                        COUNT(DISTINCT mr.id) as total_materiales,
                        MAX(mr.fecha_publicacion) as ultimo_material_fecha
                    FROM notas n
                    JOIN estudiantes e ON n.estudiante_id = e.id
                    LEFT JOIN material_reforzamiento mr ON n.estudiante_id = mr.estudiante_id 
                        AND n.materia_id = mr.materia_id 
                        AND n.año_academico = mr.año_academico
                        AND mr.estado = 'activo'
                    WHERE n.materia_id = ?
                        AND n.profesor_id = ?
                        AND n.año_academico = ?
                        AND n.promedio < 60.00
                        AND n.estado = 'activo'
                        AND e.estado = 'activo'
                    GROUP BY n.estudiante_id, e.nombre, e.apellido, e.grado, e.seccion, n.promedio
                    ORDER BY n.promedio ASC, e.nombre, e.apellido";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $profesorId, $añoAcademico]);
            $estudiantes = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Estudiantes reprobados obtenidos correctamente',
                'data' => $estudiantes,
                'total' => count($estudiantes)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener material de reforzamiento de un estudiante específico (profesor)
     */
    public function obtenerMaterialPorEstudiante($estudianteId, $materiaId, $añoAcademico = null) {
        try {
            if ($añoAcademico === null) {
                $añoAcademico = date('Y');
            }
            
            $sql = "SELECT 
                        mr.id,
                        mr.titulo,
                        mr.descripcion,
                        mr.tipo_contenido,
                        mr.contenido,
                        mr.url_externa,
                        mr.fecha_publicacion,
                        mr.fecha_vencimiento,
                        mr.estado
                    FROM material_reforzamiento mr
                    WHERE mr.estudiante_id = ?
                        AND mr.materia_id = ?
                        AND mr.año_academico = ?
                    ORDER BY mr.fecha_publicacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $materiaId, $añoAcademico]);
            $materiales = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Material obtenido correctamente',
                'data' => $materiales
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar material de reforzamiento
     */
    public function eliminarMaterial($materialId, $profesorId) {
        try {
            // Verificar que el material pertenece al profesor
            $sqlCheck = "SELECT id FROM material_reforzamiento 
                        WHERE id = ? AND profesor_id = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$materialId, $profesorId]);
            $material = $stmtCheck->fetch();
            
            if (!$material) {
                return [
                    'success' => false,
                    'message' => 'Material no encontrado o no tienes permisos'
                ];
            }
            
            // Eliminar registro (no hay archivos físicos que eliminar)
            $sql = "DELETE FROM material_reforzamiento WHERE id = ? AND profesor_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$materialId, $profesorId]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Material eliminado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el material'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
}
?>

