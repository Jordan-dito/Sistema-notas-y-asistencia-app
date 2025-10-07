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
        
    default:
        // Respuesta por defecto
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'available_endpoints' => [
                'login' => BASE_URL . '/api/auth.php?action=login',
                'register' => BASE_URL . '/api/auth.php?action=register',
                'profile' => BASE_URL . '/api/auth.php?action=profile'
            ]
        ], JSON_UNESCAPED_UNICODE);
        break;
}
?>
