<?php
/**
 * API de Autenticación
 * Endpoint principal para login y registro
 */

require_once '../config/database.php';
require_once '../controllers/AuthController.php';

// Crear instancia del controlador
$authController = new AuthController();

// Obtener la acción desde la URL
$action = $_GET['action'] ?? '';

// Enrutar las peticiones
switch ($action) {
    case 'login':
        $authController->login();
        break;
        
    case 'register':
        $authController->register();
        break;
        
    case 'profile':
        $authController->getProfile();
        break;
        
    case 'students':
        $authController->getStudents();
        break;
        
    case 'teachers':
        $authController->getTeachers();
        break;
        
    case 'edit-student':
        $authController->editStudent();
        break;
        
    case 'edit-teacher':
        $authController->editTeacher();
        break;
        
    case 'delete-student':
        $authController->deleteStudent();
        break;
        
    case 'delete-teacher':
        $authController->deleteTeacher();
        break;
        
    default:
        // Respuesta por defecto
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'available_endpoints' => [
                'login' => BASE_URL . '/api/auth.php?action=login',
                'register' => BASE_URL . '/api/auth.php?action=register',
                'profile' => BASE_URL . '/api/auth.php?action=profile',
                'students' => BASE_URL . '/api/auth.php?action=students',
                'teachers' => BASE_URL . '/api/auth.php?action=teachers',
                'edit-student' => BASE_URL . '/api/auth.php?action=edit-student',
                'edit-teacher' => BASE_URL . '/api/auth.php?action=edit-teacher',
                'delete-student' => BASE_URL . '/api/auth.php?action=delete-student',
                'delete-teacher' => BASE_URL . '/api/auth.php?action=delete-teacher'
            ]
        ], JSON_UNESCAPED_UNICODE);
        break;
}
?>
