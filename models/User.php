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
     * Verificar si email existe (case-insensitive)
     */
    public function emailExists($email) {
        try {
            $email_normalizado = trim(strtolower($email));
            $sql = "SELECT COUNT(*) FROM usuarios WHERE LOWER(TRIM(email)) = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email_normalizado]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Valida si ya existe un estudiante con el mismo nombre, apellido, grado y sección
     * @param string $nombre Nombre del estudiante
     * @param string $apellido Apellido del estudiante
     * @param string $grado Grado del estudiante
     * @param string $seccion Sección del estudiante
     * @param int|null $excluir_id ID del estudiante a excluir (útil para actualizaciones)
     * @return array Array con 'existe' (bool), 'mensaje' (string) y opcionalmente 'estudiante_id' (int)
     */
    public function validarEstudianteDuplicado($nombre, $apellido, $grado = null, $seccion = null, $excluir_id = null) {
        try {
            $nombre_normalizado = trim(strtolower($nombre));
            $apellido_normalizado = trim(strtolower($apellido));
            
            // Si se proporcionan grado y sección, validar que no exista en el mismo grado/sección
            // Si no se proporcionan, validar solo por nombre y apellido (más restrictivo)
            if ($grado !== null && $seccion !== null) {
                $grado_normalizado = trim($grado);
                $seccion_normalizado = trim(strtoupper($seccion));
                
                $sql = "SELECT id, nombre, apellido, grado, seccion, estado 
                        FROM estudiantes 
                        WHERE LOWER(TRIM(nombre)) = ? 
                        AND LOWER(TRIM(apellido)) = ?
                        AND TRIM(grado) = ?
                        AND UPPER(TRIM(seccion)) = ?";
                
                $params = [$nombre_normalizado, $apellido_normalizado, $grado_normalizado, $seccion_normalizado];
                
                if ($excluir_id !== null) {
                    $sql .= " AND id != ?";
                    $params[] = $excluir_id;
                }
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $result = $stmt->fetch();
                
                if ($result) {
                    $estado = $result['estado'];
                    $nombre_original = $result['nombre'];
                    $apellido_original = $result['apellido'];
                    $grado_original = $result['grado'];
                    $seccion_original = $result['seccion'];
                    $mensaje = $estado === 'activo' 
                        ? "Ya existe un estudiante activo con el nombre '$nombre_original $apellido_original' en el $grado_original sección $seccion_original"
                        : "Ya existe un estudiante inactivo con el nombre '$nombre_original $apellido_original' en el $grado_original sección $seccion_original (ID: {$result['id']})";
                    
                    return [
                        'existe' => true,
                        'mensaje' => $mensaje,
                        'estudiante_id' => $result['id']
                    ];
                }
            } else {
                // Validación más restrictiva: solo nombre y apellido (sin grado/sección)
                $sql = "SELECT id, nombre, apellido, estado 
                        FROM estudiantes 
                        WHERE LOWER(TRIM(nombre)) = ? 
                        AND LOWER(TRIM(apellido)) = ?";
                
                $params = [$nombre_normalizado, $apellido_normalizado];
                
                if ($excluir_id !== null) {
                    $sql .= " AND id != ?";
                    $params[] = $excluir_id;
                }
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $result = $stmt->fetch();
                
                if ($result) {
                    $estado = $result['estado'];
                    $nombre_original = $result['nombre'];
                    $apellido_original = $result['apellido'];
                    $mensaje = $estado === 'activo' 
                        ? "Ya existe un estudiante activo con el nombre '$nombre_original $apellido_original'"
                        : "Ya existe un estudiante inactivo con el nombre '$nombre_original $apellido_original' (ID: {$result['id']})";
                    
                    return [
                        'existe' => true,
                        'mensaje' => $mensaje,
                        'estudiante_id' => $result['id']
                    ];
                }
            }
            
            return [
                'existe' => false,
                'mensaje' => 'Estudiante único'
            ];
            
        } catch (PDOException $e) {
            return [
                'existe' => false,
                'mensaje' => 'Error al validar estudiante: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Valida si ya existe un usuario con el mismo email (mejorado)
     * @param string $email Email a validar
     * @param int|null $excluir_id ID del usuario a excluir (útil para actualizaciones)
     * @return array Array con 'existe' (bool) y 'mensaje' (string)
     */
    public function validarEmailDuplicado($email, $excluir_id = null) {
        try {
            $email_normalizado = trim(strtolower($email));
            
            $sql = "SELECT id, email, rol, estado 
                    FROM usuarios 
                    WHERE LOWER(TRIM(email)) = ?";
            
            $params = [$email_normalizado];
            
            if ($excluir_id !== null) {
                $sql .= " AND id != ?";
                $params[] = $excluir_id;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            if ($result) {
                return [
                    'existe' => true,
                    'mensaje' => "El email '$email' ya está registrado en el sistema"
                ];
            }
            
            return [
                'existe' => false,
                'mensaje' => 'Email único'
            ];
            
        } catch (PDOException $e) {
            return [
                'existe' => false,
                'mensaje' => 'Error al validar email: ' . $e->getMessage()
            ];
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
     * @param bool $includeInactive Por defecto true, incluye estudiantes inactivos
     */
    public function getAllStudents($includeInactive = true) {
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
                    WHERE u.rol = 'estudiante'";
            
            // Si includeInactive es false, filtrar solo activos
            if (!$includeInactive) {
                $sql .= " AND u.estado = 'activo' AND e.estado = 'activo'";
            }
            
            $sql .= " ORDER BY e.fecha_creacion DESC, e.grado, e.seccion, e.apellido, e.nombre";
            
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
     * Obtener datos de un estudiante por ID
     */
    public function getStudentById($estudianteId) {
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
                    WHERE e.id = ? AND e.estado = 'activo'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estudianteId]);
            $student = $stmt->fetch();
            
            if ($student) {
                return [
                    'success' => true,
                    'message' => 'Estudiante obtenido exitosamente',
                    'data' => $student
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estudiante: ' . $e->getMessage()
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
