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
}
?>
