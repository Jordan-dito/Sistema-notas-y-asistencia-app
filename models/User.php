<?php
/**
 * Modelo de Usuario
 * Maneja las operaciones de usuarios, estudiantes y profesores
 */

require_once '../config/connection.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Login de usuario
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT id, email, password, rol, estado FROM usuarios WHERE email = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Obtener datos específicos según el rol
                $userData = $this->getUserData($user['id'], $user['rol']);
                
                return [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'data' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'rol' => $user['rol'],
                        'user_data' => $userData
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en el login: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener datos específicos del usuario según su rol
     */
    private function getUserData($userId, $rol) {
        try {
            switch ($rol) {
                case 'estudiante':
                    $sql = "SELECT e.id, e.nombre, e.apellido, e.grado, e.seccion, e.telefono, e.direccion, e.fecha_nacimiento
                            FROM estudiantes e 
                            WHERE e.usuario_id = ?";
                    break;
                    
                case 'profesor':
                    $sql = "SELECT p.id, p.nombre, p.apellido, p.telefono, p.direccion, p.fecha_contratacion
                            FROM profesores p 
                            WHERE p.usuario_id = ?";
                    break;
                    
                case 'admin':
                    return [
                        'id' => $userId,
                        'nombre' => 'Administrador',
                        'apellido' => 'Sistema'
                    ];
                    
                default:
                    return null;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($email, $password, $rol, $userData) {
        try {
            $this->db->beginTransaction();
            
            // Crear usuario
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (email, password, rol) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $hashedPassword, $rol]);
            $userId = $this->db->lastInsertId();
            
            // Crear registro específico según el rol
            if ($rol === 'estudiante') {
                $this->createStudent($userId, $userData);
            } elseif ($rol === 'profesor') {
                $this->createTeacher($userId, $userData);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => ['id' => $userId]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear estudiante
     */
    private function createStudent($userId, $data) {
        $sql = "INSERT INTO estudiantes (usuario_id, nombre, apellido, grado, seccion, telefono, direccion, fecha_nacimiento) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['nombre'],
            $data['apellido'],
            $data['grado'],
            $data['seccion'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['fecha_nacimiento'] ?? null
        ]);
    }
    
    /**
     * Crear profesor
     */
    private function createTeacher($userId, $data) {
        $sql = "INSERT INTO profesores (usuario_id, nombre, apellido, telefono, direccion, fecha_contratacion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['nombre'],
            $data['apellido'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['fecha_contratacion'] ?? null
        ]);
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExists($email) {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Obtener materias de un estudiante
     */
    public function getStudentSubjects($studentId) {
        try {
            $sql = "SELECT m.id, m.nombre, m.grado, m.seccion, 
                           CONCAT(p.nombre, ' ', p.apellido) as profesor_nombre
                    FROM inscripciones i
                    JOIN materias m ON i.materia_id = m.id
                    JOIN profesores p ON m.profesor_id = p.id
                    WHERE i.estudiante_id = ? AND i.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$studentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener materias de un profesor
     */
    public function getTeacherSubjects($teacherId) {
        try {
            $sql = "SELECT id, nombre, grado, seccion, año_academico, estado
                    FROM materias 
                    WHERE profesor_id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$teacherId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener todos los estudiantes
     */
    public function getAllStudents() {
        try {
            $sql = "SELECT 
                        u.id as user_id,
                        u.email,
                        u.rol,
                        u.estado as user_estado,
                        e.id as estudiante_id,
                        e.nombre,
                        e.apellido,
                        e.grado,
                        e.seccion,
                        e.telefono,
                        e.direccion,
                        e.fecha_nacimiento,
                        e.estado as estudiante_estado,
                        e.fecha_creacion
                    FROM usuarios u
                    INNER JOIN estudiantes e ON u.id = e.usuario_id
                    WHERE u.rol = 'estudiante' AND u.estado = 'activo' AND e.estado = 'activo'
                    ORDER BY e.grado, e.seccion, e.apellido, e.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $students = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Estudiantes obtenidos exitosamente',
                'data' => $students,
                'total' => count($students)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estudiantes: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener todos los profesores
     */
    public function getAllTeachers() {
        try {
            $sql = "SELECT 
                        u.id as user_id,
                        u.email,
                        u.rol,
                        u.estado as user_estado,
                        p.id as profesor_id,
                        p.nombre,
                        p.apellido,
                        p.telefono,
                        p.direccion,
                        p.fecha_contratacion,
                        p.estado as profesor_estado,
                        p.fecha_creacion
                    FROM usuarios u
                    INNER JOIN profesores p ON u.id = p.usuario_id
                    WHERE u.rol = 'profesor' AND u.estado = 'activo' AND p.estado = 'activo'
                    ORDER BY p.apellido, p.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $teachers = $stmt->fetchAll();
            
            return [
                'success' => true,
                'message' => 'Profesores obtenidos exitosamente',
                'data' => $teachers,
                'total' => count($teachers)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener profesores: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar datos de un estudiante
     */
    public function updateStudent($estudianteId, $data) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el estudiante existe
            $sql = "SELECT id, usuario_id FROM estudiantes WHERE id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ];
            }
            
            // Actualizar datos del estudiante
            $sql = "UPDATE estudiantes SET 
                        nombre = ?, 
                        apellido = ?, 
                        grado = ?, 
                        seccion = ?, 
                        telefono = ?, 
                        direccion = ?, 
                        fecha_nacimiento = ?,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['grado'],
                $data['seccion'],
                $data['telefono'],
                $data['direccion'],
                $data['fecha_nacimiento'],
                $estudianteId
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Estudiante actualizado exitosamente',
                'data' => [
                    'estudiante_id' => $estudianteId,
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'grado' => $data['grado'],
                    'seccion' => $data['seccion']
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar estudiante: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar estudiante (cambiar estado a inactivo)
     */
    public function deleteStudent($estudianteId) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el estudiante existe y está activo
            $sql = "SELECT e.id, e.nombre, e.apellido, e.usuario_id 
                    FROM estudiantes e 
                    WHERE e.id = ? AND e.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ];
            }
            
            // Cambiar estado del estudiante a inactivo
            $sql = "UPDATE estudiantes SET 
                        estado = 'inactivo',
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            
            // Cambiar estado del usuario a inactivo
            $sql = "UPDATE usuarios SET 
                        estado = 'inactivo',
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$student['usuario_id']]);
            
            // Cambiar estado de las inscripciones a inactivo
            $sql = "UPDATE inscripciones SET 
                        estado = 'inactivo'
                    WHERE estudiante_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Estudiante eliminado exitosamente',
                'data' => [
                    'estudiante_id' => $estudianteId,
                    'nombre' => $student['nombre'],
                    'apellido' => $student['apellido'],
                    'estado' => 'inactivo'
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar estudiante: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar datos de un profesor
     */
    public function updateTeacher($profesorId, $data) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el profesor existe
            $sql = "SELECT id, usuario_id FROM profesores WHERE id = ? AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            $teacher = $stmt->fetch();
            
            if (!$teacher) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ];
            }
            
            // Actualizar datos del profesor
            $sql = "UPDATE profesores SET 
                        nombre = ?, 
                        apellido = ?, 
                        telefono = ?, 
                        direccion = ?, 
                        fecha_contratacion = ?,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['telefono'],
                $data['direccion'],
                $data['fecha_contratacion'],
                $profesorId
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Profesor actualizado exitosamente',
                'data' => [
                    'profesor_id' => $profesorId,
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido']
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar profesor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar profesor (cambiar estado a inactivo)
     */
    public function deleteTeacher($profesorId) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el profesor existe y está activo
            $sql = "SELECT p.id, p.nombre, p.apellido, p.usuario_id 
                    FROM profesores p 
                    WHERE p.id = ? AND p.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            $teacher = $stmt->fetch();
            
            if (!$teacher) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ];
            }
            
            // Cambiar estado del profesor a inactivo
            $sql = "UPDATE profesores SET 
                        estado = 'inactivo',
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            
            // Cambiar estado del usuario a inactivo
            $sql = "UPDATE usuarios SET 
                        estado = 'inactivo',
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$teacher['usuario_id']]);
            
            // Cambiar estado de las materias a inactivo
            $sql = "UPDATE materias SET 
                        estado = 'inactivo'
                    WHERE profesor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$profesorId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Profesor eliminado exitosamente',
                'data' => [
                    'profesor_id' => $profesorId,
                    'nombre' => $teacher['nombre'],
                    'apellido' => $teacher['apellido'],
                    'estado' => 'inactivo'
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar profesor: ' . $e->getMessage()
            ];
        }
    }
}
?>
