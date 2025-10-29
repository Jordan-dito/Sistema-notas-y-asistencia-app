<?php
/**
 * API de Vista de Estudiantes y Materias
 * Endpoint para obtener estudiantes inscritos en materias específicas
 * Este endpoint es usado por el dashboard para mostrar la vista de estudiantes por materia
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

require_once '../models/VistaEstudiantesMaterias.php';

try {
    $modelo = new VistaEstudiantesMaterias();
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido. Use GET'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Obtener parámetros de la URL
    $materiaId = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : null;
    $profesorId = isset($_GET['profesor_id']) ? intval($_GET['profesor_id']) : null;
    
    // Obtener datos usando el modelo
    $resultado = $modelo->obtenerEstudiantesPorMaterias($materiaId, $profesorId);
    
    // Si no hay datos, mostrar mensaje informativo
    if ($resultado['success'] && empty($resultado['data']['materias'])) {
        $resultado['message'] = 'No se encontraron estudiantes inscritos con los filtros aplicados';
    }
    
    // Enviar respuesta
    if ($resultado['success']) {
        http_response_code(200);
    } else {
        http_response_code(500);
    }
    
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>