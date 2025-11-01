<?php
/**
 * Modelo de Asistencia
 * Maneja las operaciones de base de datos para asistencia
 */

require_once '../config/connection.php';

class Asistencia {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    /**
     * Crear registro de asistencia
     */
    public function crearAsistencia($materiaId, $estudianteId, $fechaClase, $estado, $profesorId) {
        try {
            $sql = "INSERT INTO asistencia (materia_id, estudiante_id, fecha_clase, estado, profesor_id) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$materiaId, $estudianteId, $fechaClase, $estado, $profesorId]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Asistencia registrada correctamente',
                    'data' => [
                        'id' => $this->db->lastInsertId(),
                        'materia_id' => $materiaId,
                        'estudiante_id' => $estudianteId,
                        'fecha_clase' => $fechaClase,
                        'estado' => $estado,
                        'profesor_id' => $profesorId
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar asistencia'
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
     * Tomar asistencia de toda la clase
     */
    public function tomarAsistenciaClase($materiaId, $fechaClase, $asistencias, $profesorId) {
        try {
            $this->db->beginTransaction();
            
            // Eliminar asistencias existentes para esta fecha
            $sqlDelete = "DELETE FROM asistencia WHERE materia_id = ? AND fecha_clase = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$materiaId, $fechaClase]);
            
            $registrosInsertados = 0;
            $errores = [];
            
            // Insertar nuevas asistencias
            $sqlInsert = "INSERT INTO asistencia (materia_id, estudiante_id, fecha_clase, estado, profesor_id) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $this->db->prepare($sqlInsert);
            
            foreach ($asistencias as $asistencia) {
                try {
                    $result = $stmtInsert->execute([
                        $materiaId,
                        $asistencia['estudiante_id'],
                        $fechaClase,
                        $asistencia['estado'],
                        $profesorId
                    ]);
                    
                    if ($result) {
                        $registrosInsertados++;
                    }
                } catch (PDOException $e) {
                    $errores[] = "Error con estudiante {$asistencia['estudiante_id']}: " . $e->getMessage();
                }
            }
            
            if (empty($errores)) {
                $this->db->commit();
                return [
                    'success' => true,
                    'message' => "Asistencia registrada correctamente para $registrosInsertados estudiantes",
                    'data' => [
                        'fecha_clase' => $fechaClase,
                        'materia_id' => $materiaId,
                        'registros_insertados' => $registrosInsertados
                    ]
                ];
            } else {
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Error al registrar algunas asistencias',
                    'errors' => $errores
                ];
            }
            
        } catch (PDOException $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener asistencia de una clase específica
     */
    public function obtenerAsistenciaClase($materiaId, $fechaClase) {
        try {
            $sql = "SELECT 
                        a.id,
                        a.estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        a.estado,
                        a.fecha_registro
                    FROM asistencia a
                    JOIN estudiantes e ON a.estudiante_id = e.id
                    WHERE a.materia_id = ? AND a.fecha_clase = ?
                    ORDER BY e.nombre, e.apellido";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $fechaClase]);
            $asistencias = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Asistencia obtenida correctamente',
                'data' => $asistencias
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas de asistencia de un estudiante
     */
    public function obtenerEstadisticasEstudiante($estudianteId, $materiaId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_clases,
                        SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes,
                        SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                        SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
                        ROUND((SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as porcentaje_asistencia
                    FROM asistencia 
                    WHERE estudiante_id = ? AND materia_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $materiaId]);
            $estadisticas = $stmt->fetch();
            
            // Obtener historial reciente
            $sqlHistorial = "SELECT 
                                fecha_clase,
                                estado,
                                fecha_registro
                            FROM asistencia 
                            WHERE estudiante_id = ? AND materia_id = ?
                            ORDER BY fecha_clase DESC
                            LIMIT 10";
            
            $stmtHistorial = $this->db->prepare($sqlHistorial);
            $stmtHistorial->execute([$estudianteId, $materiaId]);
            $historial = $stmtHistorial->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'estadisticas' => $estadisticas,
                    'historial' => $historial
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estudiantes inscritos en una materia
     */
    public function obtenerEstudiantesInscritos($materiaId) {
        try {
            $sql = "SELECT 
                        e.id as estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        e.grado,
                        e.seccion
                    FROM estudiantes e
                    JOIN inscripciones i ON e.id = i.estudiante_id
                    WHERE i.materia_id = ? AND i.estado = 'activo' AND e.estado = 'activo'
                    ORDER BY e.nombre, e.apellido";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId]);
            $estudiantes = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Estudiantes obtenidos correctamente',
                'data' => $estudiantes
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar estado de asistencia
     */
    public function actualizarAsistencia($asistenciaId, $estado) {
        try {
            $sql = "UPDATE asistencia SET estado = ?, fecha_actualizacion = CURRENT_TIMESTAMP 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$estado, $asistenciaId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Asistencia actualizada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
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
     * Obtener resumen de asistencia por fecha
     */
    public function obtenerResumenClase($materiaId, $fechaClase) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_estudiantes,
                        SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes,
                        SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                        SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
                        ROUND((SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as porcentaje_asistencia
                    FROM asistencia 
                    WHERE materia_id = ? AND fecha_clase = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $fechaClase]);
            $resumen = $stmt->fetch();
            
            return [
                'success' => true,
                'message' => 'Resumen obtenido correctamente',
                'data' => $resumen
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si existe asistencia para una materia y fecha
     */
    public function verificarAsistencia($materiaId, $fechaClase) {
        try {
            $sql = "SELECT COUNT(*) as total_registros 
                    FROM asistencia 
                    WHERE materia_id = ? AND fecha_clase = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $fechaClase]);
            $resultado = $stmt->fetch();
            
            $existe = $resultado['total_registros'] > 0;
            
            return [
                'success' => true,
                'message' => 'Verificación completada',
                'data' => [
                    'existe' => $existe,
                    'total_registros' => (int)$resultado['total_registros'],
                    'materia_id' => $materiaId,
                    'fecha_clase' => $fechaClase
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Listar todas las asistencias de un estudiante en una materia
     */
    public function listarAsistenciasEstudiante($estudianteId, $materiaId) {
        try {
            $sql = "SELECT 
                        a.id,
                        a.estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        a.materia_id,
                        m.nombre as nombre_materia,
                        a.fecha_clase,
                        a.estado,
                        a.fecha_registro,
                        a.profesor_id,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor
                    FROM asistencia a
                    JOIN estudiantes e ON a.estudiante_id = e.id
                    JOIN materias m ON a.materia_id = m.id
                    LEFT JOIN profesores p ON a.profesor_id = p.id
                    WHERE a.estudiante_id = ? AND a.materia_id = ?
                    ORDER BY a.fecha_clase DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $materiaId]);
            $asistencias = $stmt->fetchAll();
            
            // Calcular resumen
            $resumen = [
                'total' => count($asistencias),
                'presentes' => 0,
                'ausentes' => 0,
                'tardanzas' => 0
            ];
            
            foreach ($asistencias as $asistencia) {
                switch ($asistencia['estado']) {
                    case 'presente':
                        $resumen['presentes']++;
                        break;
                    case 'ausente':
                        $resumen['ausentes']++;
                        break;
                    case 'tardanza':
                        $resumen['tardanzas']++;
                        break;
                }
            }
            
            // Calcular porcentaje de asistencia
            if ($resumen['total'] > 0) {
                $resumen['porcentaje_asistencia'] = round(($resumen['presentes'] / $resumen['total']) * 100, 2);
            } else {
                $resumen['porcentaje_asistencia'] = 0;
            }
            
            return [
                'success' => true,
                'message' => 'Asistencias del estudiante listadas correctamente',
                'data' => [
                    'estudiante_id' => $estudianteId,
                    'materia_id' => $materiaId,
                    'resumen' => $resumen,
                    'asistencias' => $asistencias
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Listar todas las asistencias de una fecha específica
     */
    public function listarAsistenciasPorFecha($materiaId, $fechaClase) {
        try {
            $sql = "SELECT 
                        a.id,
                        a.estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        e.grado,
                        e.seccion,
                        a.estado,
                        a.fecha_clase,
                        a.fecha_registro,
                        a.profesor_id,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor
                    FROM asistencia a
                    JOIN estudiantes e ON a.estudiante_id = e.id
                    LEFT JOIN profesores p ON a.profesor_id = p.id
                    WHERE a.materia_id = ? AND a.fecha_clase = ?
                    ORDER BY e.nombre, e.apellido";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $fechaClase]);
            $asistencias = $stmt->fetchAll();
            
            // Calcular resumen
            $resumen = [
                'total' => count($asistencias),
                'presentes' => 0,
                'ausentes' => 0,
                'tardanzas' => 0
            ];
            
            foreach ($asistencias as $asistencia) {
                switch ($asistencia['estado']) {
                    case 'presente':
                        $resumen['presentes']++;
                        break;
                    case 'ausente':
                        $resumen['ausentes']++;
                        break;
                    case 'tardanza':
                        $resumen['tardanzas']++;
                        break;
                }
            }
            
            return [
                'success' => true,
                'message' => 'Asistencias listadas correctamente',
                'data' => [
                    'fecha_clase' => $fechaClase,
                    'materia_id' => $materiaId,
                    'resumen' => $resumen,
                    'asistencias' => $asistencias
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
}
?>
