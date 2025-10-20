<?php
/**
 * Modelo de Materia
 * Maneja las operaciones de materias e inscripciones
 */

require_once '../config/connection.php';

class Materia {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Crear una nueva materia
     */
    public function createMateria($nombre, $grado, $seccion, $profesorId, $añoAcademico) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el profesor existe
            $sql = "SELECT id, nombre, apellido FROM profesores WHERE id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            $profesor = $stmt->fetch();
            
            if (!$profesor) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ];
            }
            
            // Verificar si ya existe una materia igual
            $sql = "SELECT id FROM materias 
                    WHERE nombre = ? AND grado = ? AND seccion = ? AND año_academico = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $grado, $seccion, $añoAcademico]);
            
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Ya existe una materia con el mismo nombre, grado, sección y año académico'
                ];
            }
            
            // Crear la materia
            $sql = "INSERT INTO materias (nombre, grado, seccion, profesor_id, año_academico) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombre, $grado, $seccion, $profesorId, $añoAcademico]);
            $materiaId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Materia creada exitosamente',
                'data' => [
                    'materia_id' => $materiaId,
                    'nombre' => $nombre,
                    'grado' => $grado,
                    'seccion' => $seccion,
                    'año_academico' => $añoAcademico,
                    'profesor' => [
                        'id' => $profesor['id'],
                        'nombre' => $profesor['nombre'],
                        'apellido' => $profesor['apellido']
                    ]
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear materia: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener todas las materias
     */
    public function getAllMaterias() {
        try {
            $sql = "SELECT 
                        m.id as materia_id,
                        m.nombre,
                        m.grado,
                        m.seccion,
                        m.año_academico,
                        m.estado,
                        m.fecha_creacion,
                        p.id as profesor_id,
                        p.nombre as profesor_nombre,
                        p.apellido as profesor_apellido
                    FROM materias m
                    INNER JOIN profesores p ON m.profesor_id = p.id
                    WHERE m.estado = 'activo'
                    ORDER BY m.año_academico DESC, m.grado, m.seccion, m.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $materias = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Materias obtenidas exitosamente',
                'data' => $materias,
                'total' => count($materias)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener materias: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener materias de un profesor
     */
    public function getMateriasByProfesor($profesorId) {
        try {
            $sql = "SELECT 
                        m.id as materia_id,
                        m.nombre,
                        m.grado,
                        m.seccion,
                        m.año_academico,
                        m.estado,
                        m.fecha_creacion,
                        COUNT(i.id) as total_estudiantes
                    FROM materias m
                    LEFT JOIN inscripciones i ON m.id = i.materia_id AND i.estado = 'activo'
                    WHERE m.profesor_id = ? AND m.estado = 'activo'
                    GROUP BY m.id
                    ORDER BY m.año_academico DESC, m.grado, m.seccion, m.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            $materias = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Materias del profesor obtenidas exitosamente',
                'data' => $materias,
                'total' => count($materias)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener materias del profesor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar materia (cambiar estado a inactivo)
     */
    public function deleteMateria($materiaId) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que la materia existe y está activa
            $sql = "SELECT id, nombre, grado, seccion FROM materias WHERE id = ? AND estado = 'activo'";
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
            
            // Verificar si tiene inscripciones activas
            $sql = "SELECT COUNT(*) as total FROM inscripciones WHERE materia_id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId]);
            $inscripciones = $stmt->fetch();
            
            if ($inscripciones['total'] > 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar la materia porque tiene estudiantes inscritos'
                ];
            }
            
            // Cambiar estado a inactivo
            $sql = "UPDATE materias SET estado = 'inactivo' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Materia eliminada exitosamente',
                'data' => [
                    'materia_id' => $materiaId,
                    'nombre' => $materia['nombre'],
                    'grado' => $materia['grado'],
                    'seccion' => $materia['seccion']
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar materia: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si existe el profesor
     */
    public function verificarProfesor($profesorId) {
        try {
            $sql = "SELECT id FROM profesores WHERE id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>

