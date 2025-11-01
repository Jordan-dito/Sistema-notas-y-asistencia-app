<?php
/**
 * Modelo de Notas
 * Maneja las operaciones de base de datos para notas de estudiantes
 */

require_once '../config/connection.php';

class Notas {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    /**
     * Crear o actualizar notas de un estudiante
     */
    public function guardarNotas($estudianteId, $materiaId, $profesorId, $añoAcademico, $nota1, $nota2, $nota3, $nota4) {
        try {
            // Validar que las notas estén en el rango 0-100
            $notas = ['nota_1' => $nota1, 'nota_2' => $nota2, 'nota_3' => $nota3, 'nota_4' => $nota4];
            foreach ($notas as $key => $nota) {
                if ($nota !== null && ($nota < 0 || $nota > 100)) {
                    return [
                        'success' => false,
                        'message' => "$key debe estar entre 0 y 100"
                    ];
                }
            }
            
            // Calcular promedio
            $notasArray = array_filter([$nota1, $nota2, $nota3, $nota4], function($n) {
                return $n !== null;
            });
            
            $promedio = null;
            if (!empty($notasArray)) {
                $promedio = round(array_sum($notasArray) / count($notasArray), 2);
            }
            
            // Verificar si ya existe registro
            $sqlCheck = "SELECT id FROM notas 
                        WHERE estudiante_id = ? AND materia_id = ? AND año_academico = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$estudianteId, $materiaId, $añoAcademico]);
            $existente = $stmtCheck->fetch();
            
            if ($existente) {
                // Actualizar
                $sql = "UPDATE notas SET 
                        nota_1 = ?, 
                        nota_2 = ?, 
                        nota_3 = ?, 
                        nota_4 = ?,
                        promedio = ?,
                        profesor_id = ?,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                        WHERE id = ?";
                
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $nota1, $nota2, $nota3, $nota4, $promedio, $profesorId, $existente['id']
                ]);
                
                $notaId = $existente['id'];
                $accion = 'actualizadas';
            } else {
                // Crear nuevo
                $sql = "INSERT INTO notas (estudiante_id, materia_id, profesor_id, año_academico, nota_1, nota_2, nota_3, nota_4, promedio) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    $estudianteId, $materiaId, $profesorId, $añoAcademico, 
                    $nota1, $nota2, $nota3, $nota4, $promedio
                ]);
                
                $notaId = $this->db->lastInsertId();
                $accion = 'registradas';
            }
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => "Notas $accion correctamente",
                    'data' => [
                        'id' => $notaId,
                        'estudiante_id' => $estudianteId,
                        'materia_id' => $materiaId,
                        'nota_1' => $nota1,
                        'nota_2' => $nota2,
                        'nota_3' => $nota3,
                        'nota_4' => $nota4,
                        'promedio' => $promedio
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al guardar notas'
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
     * Obtener notas de un estudiante en una materia
     */
    public function obtenerNotasEstudiante($estudianteId, $materiaId, $añoAcademico = null) {
        try {
            // Si no se especifica año, usar el año actual
            if ($añoAcademico === null) {
                $añoAcademico = date('Y');
            }
            
            $sql = "SELECT 
                        n.id,
                        n.estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        n.materia_id,
                        m.nombre as nombre_materia,
                        n.profesor_id,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor,
                        n.año_academico,
                        n.nota_1,
                        n.nota_2,
                        n.nota_3,
                        n.nota_4,
                        n.promedio,
                        n.fecha_registro,
                        n.fecha_actualizacion
                    FROM notas n
                    JOIN estudiantes e ON n.estudiante_id = e.id
                    JOIN materias m ON n.materia_id = m.id
                    LEFT JOIN profesores p ON n.profesor_id = p.id
                    WHERE n.estudiante_id = ? AND n.materia_id = ? AND n.año_academico = ? AND n.estado = 'activo'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $materiaId, $añoAcademico]);
            $notas = $stmt->fetch();
            
            if ($notas) {
                return [
                    'success' => true,
                    'message' => 'Notas obtenidas correctamente',
                    'data' => $notas
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'No hay notas registradas para este estudiante en esta materia',
                    'data' => null
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
     * Obtener todas las notas de estudiantes en una materia (para profesor)
     */
    public function obtenerNotasMateria($materiaId, $profesorId, $añoAcademico = null) {
        try {
            // Si no se especifica año, usar el año actual
            if ($añoAcademico === null) {
                $añoAcademico = date('Y');
            }
            
            $sql = "SELECT 
                        n.id,
                        n.estudiante_id,
                        CONCAT(e.nombre, ' ', e.apellido) as nombre_estudiante,
                        e.grado,
                        e.seccion,
                        n.nota_1,
                        n.nota_2,
                        n.nota_3,
                        n.nota_4,
                        n.promedio,
                        n.fecha_registro,
                        n.fecha_actualizacion
                    FROM notas n
                    JOIN estudiantes e ON n.estudiante_id = e.id
                    WHERE n.materia_id = ? AND n.profesor_id = ? AND n.año_academico = ? AND n.estado = 'activo'
                    ORDER BY e.nombre, e.apellido";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId, $profesorId, $añoAcademico]);
            $notas = $stmt->fetchAll();
            
            // Calcular estadísticas
            $estadisticas = [
                'total_estudiantes' => count($notas),
                'promedio_general' => null,
                'mayor_promedio' => null,
                'menor_promedio' => null
            ];
            
            if (!empty($notas)) {
                $promedios = array_filter(array_column($notas, 'promedio'), function($p) {
                    return $p !== null;
                });
                
                if (!empty($promedios)) {
                    $estadisticas['promedio_general'] = round(array_sum($promedios) / count($promedios), 2);
                    $estadisticas['mayor_promedio'] = max($promedios);
                    $estadisticas['menor_promedio'] = min($promedios);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Notas obtenidas correctamente',
                'data' => [
                    'materia_id' => $materiaId,
                    'año_academico' => $añoAcademico,
                    'estadisticas' => $estadisticas,
                    'notas' => $notas
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
     * Obtener todas las notas de un estudiante en todas sus materias
     */
    public function obtenerTodasNotasEstudiante($estudianteId, $añoAcademico = null) {
        try {
            // Si no se especifica año, usar el año actual
            if ($añoAcademico === null) {
                $añoAcademico = date('Y');
            }
            
            $sql = "SELECT 
                        n.id,
                        n.materia_id,
                        m.nombre as nombre_materia,
                        m.grado,
                        m.seccion,
                        CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor,
                        n.nota_1,
                        n.nota_2,
                        n.nota_3,
                        n.nota_4,
                        n.promedio,
                        n.fecha_actualizacion
                    FROM notas n
                    JOIN materias m ON n.materia_id = m.id
                    LEFT JOIN profesores p ON n.profesor_id = p.id
                    WHERE n.estudiante_id = ? AND n.año_academico = ? AND n.estado = 'activo'
                    ORDER BY m.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId, $añoAcademico]);
            $notas = $stmt->fetchAll();
            
            // Calcular promedio general
            $promedios = array_filter(array_column($notas, 'promedio'), function($p) {
                return $p !== null;
            });
            
            $promedioGeneral = null;
            if (!empty($promedios)) {
                $promedioGeneral = round(array_sum($promedios) / count($promedios), 2);
            }
            
            return [
                'success' => true,
                'message' => 'Notas obtenidas correctamente',
                'data' => [
                    'estudiante_id' => $estudianteId,
                    'año_academico' => $añoAcademico,
                    'promedio_general' => $promedioGeneral,
                    'total_materias' => count($notas),
                    'notas' => $notas
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

