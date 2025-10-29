<?php
/**
 * API de Asistencia
 * Endpoints para manejar asistencia de estudiantes
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

require_once '../controllers/AsistenciaController.php';

try {
    $controller = new AsistenciaController();
    
    // Obtener la acción de la URL
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'tomar':
            $controller->tomarAsistencia();
            break;
            
        case 'obtener_clase':
            $controller->obtenerAsistenciaClase();
            break;
            
        case 'estadisticas_estudiante':
            $controller->obtenerEstadisticasEstudiante();
            break;
            
        case 'estudiantes_inscritos':
            $controller->obtenerEstudiantesInscritos();
            break;
            
        case 'actualizar':
            $controller->actualizarAsistencia();
            break;
            
        case 'resumen_clase':
            $controller->obtenerResumenClase();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no encontrada',
                'available_actions' => [
                    'tomar' => 'POST - Tomar asistencia de toda la clase',
                    'obtener_clase' => 'GET - Obtener asistencia de una clase específica',
                    'estadisticas_estudiante' => 'GET - Obtener estadísticas de asistencia de un estudiante',
                    'estudiantes_inscritos' => 'GET - Obtener estudiantes inscritos en una materia',
                    'actualizar' => 'PUT - Actualizar estado de asistencia individual',
                    'resumen_clase' => 'GET - Obtener resumen de asistencia por fecha'
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

