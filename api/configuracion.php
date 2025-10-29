<?php
/**
 * API de Configuración de Materia
 * Endpoints para configurar parámetros de asistencia
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../controllers/ConfiguracionController.php';

try {
    $controller = new ConfiguracionController();
    
    // Obtener la acción de la URL
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'guardar':
            $controller->guardarConfiguracion();
            break;
            
        case 'obtener':
            $controller->obtenerConfiguracion();
            break;
            
        case 'profesor':
            $controller->obtenerConfiguracionesProfesor();
            break;
            
        case 'verificar_dia':
            $controller->verificarDiaClase();
            break;
            
        case 'eliminar':
            $controller->eliminarConfiguracion();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no encontrada',
                'available_actions' => [
                    'guardar' => 'POST - Guardar configuración de materia',
                    'obtener' => 'GET - Obtener configuración de materia',
                    'profesor' => 'GET - Obtener configuraciones de profesor',
                    'verificar_dia' => 'GET - Verificar si es día de clase',
                    'eliminar' => 'DELETE - Eliminar configuración'
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
