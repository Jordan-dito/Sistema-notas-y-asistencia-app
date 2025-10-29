<?php
/**
 * Modelo de Configuración de Materia
 * Maneja las operaciones de base de datos para configuración de asistencia
 */

require_once '../config/connection.php';

class ConfiguracionMateria {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    /**
     * Crear configuración de materia
     */
    public function crearConfiguracion($materiaId, $añoAcademico, $fechaInicio, $fechaFin, $diasClase, $horaClase = null, $metaAsistencia = 80.00) {
        try {
            $sql = "INSERT INTO configuracion_materia 
                    (materia_id, año_academico, fecha_inicio, fecha_fin, dias_clase, hora_clase, meta_asistencia) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$materiaId, $añoAcademico, $fechaInicio, $fechaFin, $diasClase, $horaClase, $metaAsistencia]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Configuración creada correctamente',
                    'data' => [
                        'id' => $this->db->lastInsertId(),
                        'materia_id' => $materiaId,
                        'año_academico' => $añoAcademico,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'dias_clase' => $diasClase,
                        'hora_clase' => $horaClase,
                        'meta_asistencia' => $metaAsistencia
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear configuración'
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
     * Actualizar configuración de materia
     */
    public function actualizarConfiguracion($materiaId, $añoAcademico, $fechaInicio, $fechaFin, $diasClase, $horaClase = null, $metaAsistencia = 80.00) {
        try {
            $sql = "UPDATE configuracion_materia 
                    SET fecha_inicio = ?, fecha_fin = ?, dias_clase = ?, hora_clase = ?, meta_asistencia = ?
                    WHERE materia_id = ? AND año_academico = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$fechaInicio, $fechaFin, $diasClase, $horaClase, $metaAsistencia, $materiaId, $añoAcademico]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Configuración actualizada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Configuración no encontrada'
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
     * Obtener configuración de una materia
     */
    public function obtenerConfiguracion($materiaId, $añoAcademico = null) {
        try {
            if ($añoAcademico) {
                $sql = "SELECT 
                            c.*,
                            m.nombre as nombre_materia,
                            m.grado,
                            m.seccion,
                            CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor
                        FROM configuracion_materia c
                        JOIN materias m ON c.materia_id = m.id
                        JOIN profesores p ON m.profesor_id = p.id
                        WHERE c.materia_id = ? AND c.año_academico = ? AND c.estado = 'activo'";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$materiaId, $añoAcademico]);
            } else {
                $sql = "SELECT 
                            c.*,
                            m.nombre as nombre_materia,
                            m.grado,
                            m.seccion,
                            CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor
                        FROM configuracion_materia c
                        JOIN materias m ON c.materia_id = m.id
                        JOIN profesores p ON m.profesor_id = p.id
                        WHERE c.materia_id = ? AND c.estado = 'activo'
                        ORDER BY c.año_academico DESC
                        LIMIT 1";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$materiaId]);
            }
            
            $configuracion = $stmt->fetch();
            
            if ($configuracion) {
                return [
                    'success' => true,
                    'message' => 'Configuración obtenida correctamente',
                    'data' => $configuracion
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Configuración no encontrada'
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
     * Obtener todas las configuraciones de un profesor
     */
    public function obtenerConfiguracionesProfesor($profesorId, $añoAcademico = null) {
        try {
            if ($añoAcademico) {
                $sql = "SELECT 
                            c.*,
                            m.nombre as nombre_materia,
                            m.grado,
                            m.seccion
                        FROM configuracion_materia c
                        JOIN materias m ON c.materia_id = m.id
                        WHERE m.profesor_id = ? AND c.año_academico = ? AND c.estado = 'activo'
                        ORDER BY m.nombre";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$profesorId, $añoAcademico]);
            } else {
                $sql = "SELECT 
                            c.*,
                            m.nombre as nombre_materia,
                            m.grado,
                            m.seccion
                        FROM configuracion_materia c
                        JOIN materias m ON c.materia_id = m.id
                        WHERE m.profesor_id = ? AND c.estado = 'activo'
                        ORDER BY c.año_academico DESC, m.nombre";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$profesorId]);
            }
            
            $configuraciones = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Configuraciones obtenidas correctamente',
                'data' => $configuraciones
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si una fecha es día de clase
     */
    public function esDiaDeClase($materiaId, $fecha) {
        try {
            $sql = "SELECT dias_clase FROM configuracion_materia 
                    WHERE materia_id = ? AND estado = 'activo'
                    ORDER BY año_academico DESC LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$materiaId]);
            $config = $stmt->fetch();
            
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Configuración no encontrada'
                ];
            }
            
            $diasClase = explode(',', $config['dias_clase']);
            $diaSemana = strtolower(date('l', strtotime($fecha))); // lunes, martes, etc.
            
            $diaEnEspanol = $this->convertirDiaInglesAEspanol($diaSemana);
            
            $esDiaClase = in_array($diaEnEspanol, $diasClase);
            
            return [
                'success' => true,
                'message' => 'Verificación completada',
                'data' => [
                    'es_dia_clase' => $esDiaClase,
                    'dia_semana' => $diaEnEspanol,
                    'dias_configurados' => $diasClase
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
     * Convertir día en inglés a español
     */
    private function convertirDiaInglesAEspanol($diaIngles) {
        $dias = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        ];
        
        return $dias[$diaIngles] ?? $diaIngles;
    }
    
    /**
     * Eliminar configuración (cambiar estado a inactivo)
     */
    public function eliminarConfiguracion($materiaId, $añoAcademico) {
        try {
            $sql = "UPDATE configuracion_materia 
                    SET estado = 'inactivo' 
                    WHERE materia_id = ? AND año_academico = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$materiaId, $añoAcademico]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Configuración eliminada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Configuración no encontrada'
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
