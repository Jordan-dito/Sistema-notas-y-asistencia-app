<?php
/**
 * API de Materias del Estudiante
 * Obtiene las materias inscritas de un estudiante usando su usuario_id
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

require_once '../config/database.php';

try {
    $db = DatabaseConnection::getInstance()->getConnection();
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido. Use GET'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Obtener usuario_id de la URL
    if (!isset($_GET['usuario_id']) || empty($_GET['usuario_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'usuario_id es requerido'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $usuarioId = intval($_GET['usuario_id']);
    
    // Consulta SQL para obtener materias del estudiante
    $sql = "SELECT 
                m.id as materia_id,
                m.nombre as nombre_materia,
                m.grado,
                m.seccion,
                CONCAT(p.nombre, ' ', p.apellido) as nombre_profesor,
                m.año_academico,
                i.fecha_inscripcion,
                i.estado as estado_inscripcion
            FROM materias m
            JOIN inscripciones i ON m.id = i.materia_id
            JOIN estudiantes e ON i.estudiante_id = e.id
            JOIN profesores p ON m.profesor_id = p.id
            WHERE e.usuario_id = ? 
            AND i.estado = 'activo' 
            AND m.estado = 'activo'
            ORDER BY m.nombre";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$usuarioId]);
    $materias = $stmt->fetchAll();
    
    // Formatear respuesta
    $response = [
        'success' => true,
        'message' => 'Materias obtenidas correctamente',
        'data' => [
            'usuario_id' => $usuarioId,
            'total_materias' => count($materias),
            'materias' => $materias
        ]
    ];
    
    // Si no hay materias, mostrar mensaje informativo
    if (empty($materias)) {
        $response['message'] = 'El estudiante no tiene materias inscritas';
        $response['data']['materias'] = [];
    }
    
    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

