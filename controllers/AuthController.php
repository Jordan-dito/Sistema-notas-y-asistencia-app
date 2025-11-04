<?php
/**
 * Controlador de Autenticación
 * Maneja login, registro y operaciones de autenticación
 */

require_once '../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Procesar login
     */
    public function login() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (!isset($input['email']) || !isset($input['password'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Email y contraseña son requeridos'
            ]);
            return;
        }
        
        $email = trim($input['email']);
        $password = $input['password'];
        
        // Validaciones básicas
        if (empty($email) || empty($password)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Email y contraseña no pueden estar vacíos'
            ]);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de email inválido'
            ]);
            return;
        }
        
        // Intentar login
        $result = $this->userModel->login($email, $password);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(401, $result);
        }
    }
    
    /**
     * Procesar registro
     */
    public function register() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        $requiredFields = ['email', 'password', 'rol'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $email = trim($input['email']);
        $password = $input['password'];
        $rol = $input['rol'];
        
        // Validaciones
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de email inválido'
            ]);
            return;
        }
        
        if (strlen($password) < 6) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ]);
            return;
        }
        
        if (!in_array($rol, ['estudiante', 'profesor'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Rol inválido'
            ]);
            return;
        }
        
        // Validar datos específicos según el rol
        $userData = $this->validateUserData($rol, $input);
        if (!$userData['valid']) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => $userData['message']
            ]);
            return;
        }
        
        // ============================================
        // VALIDACIÓN: Email duplicado (mejorado)
        // ============================================
        $validacion_email = $this->userModel->validarEmailDuplicado($email);
        if ($validacion_email['existe']) {
            $this->sendResponse(409, [
                'success' => false,
                'message' => $validacion_email['mensaje']
            ]);
            return;
        }
        
        // ============================================
        // VALIDACIÓN: Estudiante duplicado (solo para estudiantes)
        // Valida nombre + apellido + grado + sección
        // ============================================
        if ($rol === 'estudiante') {
            $nombre = $userData['data']['nombre'];
            $apellido = $userData['data']['apellido'];
            $grado = $userData['data']['grado'];
            $seccion = $userData['data']['seccion'];
            
            $validacion_estudiante = $this->userModel->validarEstudianteDuplicado($nombre, $apellido, $grado, $seccion);
            if ($validacion_estudiante['existe']) {
                $this->sendResponse(409, [
                    'success' => false,
                    'message' => $validacion_estudiante['mensaje']
                ]);
                return;
            }
        }
        
        // Registrar usuario
        $result = $this->userModel->register($email, $password, $rol, $userData['data']);
        
        if ($result['success']) {
            $this->sendResponse(201, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Validar datos específicos según el rol
     */
    private function validateUserData($rol, $input) {
        switch ($rol) {
            case 'estudiante':
                $required = ['nombre', 'apellido', 'grado', 'seccion'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty(trim($input[$field]))) {
                        return [
                            'valid' => false,
                            'message' => "El campo $field es requerido para estudiantes"
                        ];
                    }
                }
                
                return [
                    'valid' => true,
                    'data' => [
                        'nombre' => trim($input['nombre']),
                        'apellido' => trim($input['apellido']),
                        'grado' => trim($input['grado']),
                        'seccion' => trim($input['seccion']),
                        'telefono' => $input['telefono'] ?? null,
                        'direccion' => $input['direccion'] ?? null,
                        'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null
                    ]
                ];
                
            case 'profesor':
                $required = ['nombre', 'apellido'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty(trim($input[$field]))) {
                        return [
                            'valid' => false,
                            'message' => "El campo $field es requerido para profesores"
                        ];
                    }
                }
                
                return [
                    'valid' => true,
                    'data' => [
                        'nombre' => trim($input['nombre']),
                        'apellido' => trim($input['apellido']),
                        'telefono' => $input['telefono'] ?? null,
                        'direccion' => $input['direccion'] ?? null,
                        'fecha_contratacion' => $input['fecha_contratacion'] ?? null
                    ]
                ];
                
            default:
                return [
                    'valid' => false,
                    'message' => 'Rol no válido'
                ];
        }
    }
    
    /**
     * Obtener datos del usuario autenticado
     */
    public function getProfile() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Aquí podrías implementar verificación de token JWT
        // Por ahora, requerir email como parámetro
        if (!isset($_GET['email'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Email es requerido'
            ]);
            return;
        }
        
        $email = trim($_GET['email']);
        
        // Obtener datos del usuario
        $result = $this->userModel->login($email, ''); // Solo para obtener datos, no validar password
        
        if ($result['success']) {
            unset($result['data']['password']); // No enviar password
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(404, [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
    }
    
    /**
     * Obtener todos los estudiantes
     */
    public function getStudents() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener parámetro opcional (por defecto true - incluir inactivos)
        $includeInactive = isset($_GET['include_inactive']) 
            ? filter_var($_GET['include_inactive'], FILTER_VALIDATE_BOOLEAN) 
            : true;
        
        // Obtener estudiantes
        $result = $this->userModel->getAllStudents($includeInactive);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener todos los profesores
     */
    public function getTeachers() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener profesores
        $result = $this->userModel->getAllTeachers();
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Editar estudiante
     */
    public function editStudent() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use PUT'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar ID del estudiante
        if (!isset($input['estudiante_id']) || empty($input['estudiante_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del estudiante es requerido'
            ]);
            return;
        }
        
        $estudianteId = $input['estudiante_id'];
        
        // Validar datos requeridos
        $requiredFields = ['nombre', 'apellido', 'grado', 'seccion'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        // Preparar datos para actualizar
        $studentData = [
            'nombre' => trim($input['nombre']),
            'apellido' => trim($input['apellido']),
            'grado' => trim($input['grado']),
            'seccion' => trim($input['seccion']),
            'telefono' => $input['telefono'] ?? null,
            'direccion' => $input['direccion'] ?? null,
            'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null
        ];
        
        // ============================================
        // VALIDACIÓN: Estudiante duplicado (excluyendo el estudiante actual)
        // Valida nombre + apellido + grado + sección
        // ============================================
        $validacion_estudiante = $this->userModel->validarEstudianteDuplicado(
            $studentData['nombre'], 
            $studentData['apellido'],
            $studentData['grado'],
            $studentData['seccion'],
            $estudianteId
        );
        if ($validacion_estudiante['existe']) {
            $this->sendResponse(409, [
                'success' => false,
                'message' => $validacion_estudiante['mensaje']
            ]);
            return;
        }
        
        // Actualizar estudiante
        $result = $this->userModel->updateStudent($estudianteId, $studentData);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Eliminar estudiante (cambiar estado a inactivo)
     */
    public function deleteStudent() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use DELETE'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar ID del estudiante
        if (!isset($input['estudiante_id']) || empty($input['estudiante_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del estudiante es requerido'
            ]);
            return;
        }
        
        $estudianteId = intval($input['estudiante_id']);
        
        // Eliminar estudiante (cambiar estado a inactivo)
        $result = $this->userModel->deleteStudent($estudianteId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Estudiante no encontrado') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Editar profesor
     */
    public function editTeacher() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use PUT'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar ID del profesor
        if (!isset($input['profesor_id']) || empty($input['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del profesor es requerido'
            ]);
            return;
        }
        
        $profesorId = $input['profesor_id'];
        
        // Validar datos requeridos
        $requiredFields = ['nombre', 'apellido'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        // Preparar datos para actualizar
        $teacherData = [
            'nombre' => trim($input['nombre']),
            'apellido' => trim($input['apellido']),
            'telefono' => $input['telefono'] ?? null,
            'direccion' => $input['direccion'] ?? null,
            'fecha_contratacion' => $input['fecha_contratacion'] ?? null
        ];
        
        // Actualizar profesor
        $result = $this->userModel->updateTeacher($profesorId, $teacherData);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Profesor no encontrado') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Eliminar profesor (cambiar estado a inactivo)
     */
    public function deleteTeacher() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use DELETE'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar ID del profesor
        if (!isset($input['profesor_id']) || empty($input['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del profesor es requerido'
            ]);
            return;
        }
        
        $profesorId = intval($input['profesor_id']);
        
        // Eliminar profesor (cambiar estado a inactivo)
        $result = $this->userModel->deleteTeacher($profesorId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Profesor no encontrado') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?>
