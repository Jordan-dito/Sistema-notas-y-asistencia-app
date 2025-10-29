<?php
/**
 * Modelo VistaEstudiantesMaterias
 * Maneja las operaciones para obtener estudiantes organizados por materias
 */

require_once '../config/connection.php';

class VistaEstudiantesMaterias {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Obtener estudiantes organizados por materias
     */
    public function obtenerEstudiantesPorMaterias($materiaId = null, $profesorId = null) {
        try {
            // Construir consulta SQL base
            $sql = "SELECT 
                        e.id as estudiante_id,
                        e.usuario_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        m.id as materia_id,
                        m.nombre as nombre_materia,
                        m.grado,
                        m.seccion,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor,
                        m.año_academico,
                        i.fecha_inscripcion,
                        i.estado as estado_inscripcion
                    FROM estudiantes e
                    JOIN inscripciones i ON e.id = i.estudiante_id
                    JOIN materias m ON i.materia_id = m.id
                    JOIN profesores p ON m.profesor_id = p.id
                    WHERE i.estado = 'activo' 
                    AND m.estado = 'activo'
                    AND e.estado = 'activo'";
            
            $params = [];
            
            // Agregar filtros según los parámetros
            if ($materiaId) {
                $sql .= " AND m.id = ?";
                $params[] = $materiaId;
            }
            
            if ($profesorId) {
                $sql .= " AND p.id = ?";
                $params[] = $profesorId;
            }
            
            $sql .= " ORDER BY m.nombre, e.apellido, e.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $estudiantes = $stmt->fetchAll();
            
            // Agrupar por materia para mejor organización
            $materias = [];
            foreach ($estudiantes as $estudiante) {
                $materiaKey = $estudiante['materia_id'];
                if (!isset($materias[$materiaKey])) {
                    $materias[$materiaKey] = [
                        'materia_id' => $estudiante['materia_id'],
                        'nombre_materia' => $estudiante['nombre_materia'],
                        'grado' => $estudiante['grado'],
                        'seccion' => $estudiante['seccion'],
                        'nombre_profesor' => $estudiante['nombre_profesor'],
                        'año_academico' => $estudiante['año_academico'],
                        'total_estudiantes' => 0,
                        'estudiantes' => []
                    ];
                }
                
                $materias[$materiaKey]['estudiantes'][] = [
                    'estudiante_id' => $estudiante['estudiante_id'],
                    'usuario_id' => $estudiante['usuario_id'],
                    'nombre_estudiante' => $estudiante['nombre_estudiante'],
                    'fecha_inscripcion' => $estudiante['fecha_inscripcion'],
                    'estado_inscripcion' => $estudiante['estado_inscripcion']
                ];
                
                $materias[$materiaKey]['total_estudiantes']++;
            }
            
            return [
                'success' => true,
                'message' => 'Vista de estudiantes y materias obtenida correctamente',
                'data' => [
                    'filtros_aplicados' => [
                        'materia_id' => $materiaId,
                        'profesor_id' => $profesorId
                    ],
                    'total_materias' => count($materias),
                    'total_estudiantes' => count($estudiantes),
                    'materias' => array_values($materias)
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estudiantes de una materia específica
     */
    public function obtenerEstudiantesDeMateria($materiaId) {
        return $this->obtenerEstudiantesPorMaterias($materiaId, null);
    }
    
    /**
     * Obtener estudiantes de las materias de un profesor
     */
    public function obtenerEstudiantesDeProfesor($profesorId) {
        return $this->obtenerEstudiantesPorMaterias(null, $profesorId);
    }
}
?>
