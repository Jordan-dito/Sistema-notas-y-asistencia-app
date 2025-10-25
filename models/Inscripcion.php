<?php
/**
 * Modelo de Inscripciones
 * Maneja las operaciones de inscripciones de estudiantes en materias
 */

require_once '../config/connection.php';

class Inscripcion {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Crear una nueva inscripción
     */
    public function createInscripcion($estudianteId, $materiaId) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el estudiante existe y está activo
            $sql = "SELECT id, nombre, apellido FROM estudiantes WHERE id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            $estudiante = $stmt->fetch();
            
            if (!$estudiante) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ];
            }
            
            // Verificar que la materia existe y está activa
            $sql = "SELECT m.id, m.nombre, m.grado, m.seccion, 
                           CONCAT(p.nombre, ' ', p.apellido) as profesor_nombre
                    FROM materias m
                    JOIN profesores p ON m.profesor_id = p.id
                    WHERE m.id = ? AND m.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId]);
            $materia = $stmt->fetch();
            
            if (!$materia) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Materia no encontrada'
                ];
            }
            
            // Verificar si ya está inscrito
            $sql = "SELECT id FROM inscripciones WHERE estudiante_id = ? AND materia_id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $materiaId]);
            
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'El estudiante ya está inscrito en esta materia'
                ];
            }
            
            // Crear la inscripción
            $sql = "INSERT INTO inscripciones (estudiante_id, materia_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $materiaId]);
            $inscripcionId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Estudiante inscrito exitosamente',
                'data' => [
                    'inscripcion_id' => $inscripcionId,
                    'estudiante' => [
                        'id' => $estudiante['id'],
                        'nombre' => $estudiante['nombre'],
                        'apellido' => $estudiante['apellido']
                    ],
                    'materia' => [
                        'id' => $materia['id'],
                        'nombre' => $materia['nombre'],
                        'grado' => $materia['grado'],
                        'seccion' => $materia['seccion'],
                        'profesor' => $materia['profesor_nombre']
                    ]
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear inscripción: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener todas las inscripciones
     */
    public function getAllInscripciones() {
        try {
            $sql = "SELECT 
                        i.id as inscripcion_id,
                        i.fecha_inscripcion,
                        i.estado as estado_inscripcion,
                        e.id as estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        e.grado as grado_estudiante,
                        e.seccion as seccion_estudiante,
                        m.id as materia_id,
                        m.nombre as nombre_materia,
                        m.grado as grado_materia,
                        m.seccion as seccion_materia,
                        m.año_academico,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor
                    FROM inscripciones i
                    JOIN estudiantes e ON i.estudiante_id = e.id
                    JOIN materias m ON i.materia_id = m.id
                    JOIN profesores p ON m.profesor_id = p.id
                    WHERE i.estado = 'activo'
                    ORDER BY e.grado, e.seccion, e.nombre, m.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $inscripciones = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Inscripciones obtenidas exitosamente',
                'data' => $inscripciones,
                'total' => count($inscripciones)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener inscripciones: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener inscripciones de un estudiante
     */
    public function getInscripcionesByEstudiante($estudianteId) {
        try {
            $sql = "SELECT 
                        i.id as inscripcion_id,
                        i.fecha_inscripcion,
                        m.id as materia_id,
                        m.nombre as nombre_materia,
                        m.grado as grado_materia,
                        m.seccion as seccion_materia,
                        m.año_academico,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor
                    FROM inscripciones i
                    JOIN materias m ON i.materia_id = m.id
                    JOIN profesores p ON m.profesor_id = p.id
                    WHERE i.estudiante_id = ? AND i.estado = 'activo'
                    ORDER BY m.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            $inscripciones = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Inscripciones del estudiante obtenidas exitosamente',
                'data' => $inscripciones,
                'total' => count($inscripciones)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener inscripciones del estudiante: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener inscripciones de materias de un profesor
     */
    public function getInscripcionesByProfesor($profesorId) {
        try {
            $sql = "SELECT 
                        i.id as inscripcion_id,
                        i.fecha_inscripcion,
                        e.id as estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        e.grado as grado_estudiante,
                        e.seccion as seccion_estudiante,
                        m.id as materia_id,
                        m.nombre as nombre_materia,
                        m.grado as grado_materia,
                        m.seccion as seccion_materia,
                        m.año_academico
                    FROM inscripciones i
                    JOIN estudiantes e ON i.estudiante_id = e.id
                    JOIN materias m ON i.materia_id = m.id
                    WHERE m.profesor_id = ? AND i.estado = 'activo'
                    ORDER BY m.nombre, e.grado, e.seccion, e.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            $inscripciones = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Inscripciones del profesor obtenidas exitosamente',
                'data' => $inscripciones,
                'total' => count($inscripciones)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener inscripciones del profesor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar inscripción
     */
    public function updateInscripcion($inscripcionId, $updateFields) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que la inscripción existe
            $sql = "SELECT i.id, i.estudiante_id, i.materia_id, i.estado,
                           CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                           m.nombre as nombre_materia
                    FROM inscripciones i
                    JOIN estudiantes e ON i.estudiante_id = e.id
                    JOIN materias m ON i.materia_id = m.id
                    WHERE i.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inscripcionId]);
            $inscripcion = $stmt->fetch();
            
            if (!$inscripcion) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Inscripción no encontrada'
                ];
            }
            
            // Si se está actualizando el estudiante, verificar que existe
            if (isset($updateFields['estudiante_id'])) {
                $sql = "SELECT id, nombre, apellido FROM estudiantes WHERE id = ? AND estado = 'activo'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$updateFields['estudiante_id']]);
                $estudiante = $stmt->fetch();
                
                if (!$estudiante) {
                    $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Estudiante no encontrado'
                    ];
                }
            }
            
            // Si se está actualizando la materia, verificar que existe
            if (isset($updateFields['materia_id'])) {
                $sql = "SELECT id, nombre FROM materias WHERE id = ? AND estado = 'activo'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$updateFields['materia_id']]);
                $materia = $stmt->fetch();
                
                if (!$materia) {
                    $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Materia no encontrada'
                    ];
                }
            }
            
            // Si se está cambiando estudiante o materia, verificar que no existe otra inscripción
            if (isset($updateFields['estudiante_id']) || isset($updateFields['materia_id'])) {
                $estudianteId = isset($updateFields['estudiante_id']) ? $updateFields['estudiante_id'] : $inscripcion['estudiante_id'];
                $materiaId = isset($updateFields['materia_id']) ? $updateFields['materia_id'] : $inscripcion['materia_id'];
                
                $sql = "SELECT id FROM inscripciones WHERE estudiante_id = ? AND materia_id = ? AND id != ? AND estado = 'activo'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$estudianteId, $materiaId, $inscripcionId]);
                
                if ($stmt->fetch()) {
                    $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => 'El estudiante ya está inscrito en esta materia'
                    ];
                }
            }
            
            // Construir la consulta de actualización
            $setParts = [];
            $values = [];
            
            foreach ($updateFields as $field => $value) {
                $setParts[] = "$field = ?";
                $values[] = $value;
            }
            
            $values[] = $inscripcionId;
            
            $sql = "UPDATE inscripciones SET " . implode(', ', $setParts) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Inscripción actualizada exitosamente',
                'data' => [
                    'inscripcion_id' => $inscripcionId,
                    'updated_fields' => array_keys($updateFields)
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar inscripción: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar inscripción (cambiar estado a inactivo)
     */
    public function deleteInscripcion($inscripcionId) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que la inscripción existe
            $sql = "SELECT i.id, 
                           CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                           m.nombre as nombre_materia
                    FROM inscripciones i
                    JOIN estudiantes e ON i.estudiante_id = e.id
                    JOIN materias m ON i.materia_id = m.id
                    WHERE i.id = ? AND i.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inscripcionId]);
            $inscripcion = $stmt->fetch();
            
            if (!$inscripcion) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Inscripción no encontrada'
                ];
            }
            
            // Cambiar estado a inactivo
            $sql = "UPDATE inscripciones SET estado = 'inactivo' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inscripcionId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Inscripción eliminada exitosamente',
                'data' => [
                    'inscripcion_id' => $inscripcionId,
                    'estudiante' => $inscripcion['nombre_estudiante'],
                    'materia' => $inscripcion['nombre_materia']
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar inscripción: ' . $e->getMessage()
            ];
        }
    }
}
?>
