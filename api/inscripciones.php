<?php
/**
 * API de Inscripciones
 * Endpoint principal para gestión de inscripciones de estudiantes en materias
 */

require_once '../config/database.php';
require_once '../controllers/InscripcionController.php';

// Crear instancia del controlador
$inscripcionController = new InscripcionController();

// Obtener la acción desde la URL
$action = $_GET['action'] ?? '';

// Enrutar las peticiones
switch ($action) {
    case 'create':
        $inscripcionController->createInscripcion();
        break;
        
    case 'all':
        $inscripcionController->getAllInscripciones();
        break;
        
    case 'by-estudiante':
        $inscripcionController->getInscripcionesByEstudiante();
        break;
        
    case 'by-profesor':
        $inscripcionController->getInscripcionesByProfesor();
        break;
        
    case 'delete':
        $inscripcionController->deleteInscripcion();
        break;
        
    case 'update':
        $inscripcionController->updateInscripcion();
        break;
        
    default:
        // Respuesta por defecto
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'available_endpoints' => [
                'create' => BASE_URL . '/api/inscripciones.php?action=create',
                'all' => BASE_URL . '/api/inscripciones.php?action=all',
                'by-estudiante' => BASE_URL . '/api/inscripciones.php?action=by-estudiante&estudiante_id={id}',
                'by-profesor' => BASE_URL . '/api/inscripciones.php?action=by-profesor&profesor_id={id}',
                'delete' => BASE_URL . '/api/inscripciones.php?action=delete',
                'update' => BASE_URL . '/api/inscripciones.php?action=update'
            ]
        ], JSON_UNESCAPED_UNICODE);
        break;
}
?>
