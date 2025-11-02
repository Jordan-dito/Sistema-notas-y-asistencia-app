<?php
/**
 * API de Material de Reforzamiento
 * Endpoints para manejar material de reforzamiento para estudiantes reprobados
 * Sistema: Profesor sube material (texto, imágenes, PDF) para estudiantes reprobados (promedio < 60)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../controllers/MaterialReforzamientoController.php';

try {
    $controller = new MaterialReforzamientoController();
    
    // Obtener la acción de la URL
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'subir':
            $controller->subirMaterial();
            break;
            
        case 'obtener_estudiante':
            $controller->obtenerMaterialEstudiante();
            break;
            
        case 'estudiantes_reprobados':
            $controller->obtenerEstudiantesReprobados();
            break;
            
        case 'material_por_estudiante':
            $controller->obtenerMaterialPorEstudiante();
            break;
            
        case 'eliminar':
            $controller->eliminarMaterial();
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no encontrada',
                'available_actions' => [
                    'subir' => 'POST - Subir material de reforzamiento (profesor)',
                    'obtener_estudiante' => 'GET - Obtener material para estudiante reprobado',
                    'estudiantes_reprobados' => 'GET - Obtener lista de estudiantes reprobados (profesor)',
                    'material_por_estudiante' => 'GET - Obtener material de un estudiante específico (profesor)',
                    'eliminar' => 'DELETE - Eliminar material de reforzamiento'
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

