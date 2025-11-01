<?php
/**
 * API de Notas
 * Endpoints para manejar notas de estudiantes
 * Sistema de 4 notas por estudiante/materia con promedio automático
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

require_once '../controllers/NotasController.php';

try {
    $controller = new NotasController();
    
    // Obtener la acción de la URL
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'guardar':
            $controller->guardarNotas();
            break;
            
        case 'obtener_estudiante':
            $controller->obtenerNotasEstudiante();
            break;
            
        case 'obtener_materia':
            $controller->obtenerNotasMateria();
            break;
            
        case 'obtener_todas':
            $controller->obtenerTodasNotasEstudiante();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no encontrada',
                'available_actions' => [
                    'guardar' => 'POST - Guardar o actualizar notas de un estudiante (profesor)',
                    'obtener_estudiante' => 'GET - Obtener notas de un estudiante en una materia (estudiante)',
                    'obtener_materia' => 'GET - Obtener todas las notas de estudiantes en una materia (profesor)',
                    'obtener_todas' => 'GET - Obtener todas las notas de un estudiante en todas sus materias'
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

