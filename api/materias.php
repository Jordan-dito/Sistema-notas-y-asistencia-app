<?php
/**
 * API de Materias
 * Endpoint principal para gestión de materias
 */

require_once '../config/database.php';
require_once '../controllers/MateriaController.php';

// Crear instancia del controlador
$materiaController = new MateriaController();

// Obtener la acción desde la URL
$action = $_GET['action'] ?? '';

// Enrutar las peticiones
switch ($action) {
    case 'create':
        $materiaController->createMateria();
        break;
        
    case 'all':
        $materiaController->getAllMaterias();
        break;
        
    case 'by-profesor':
        $materiaController->getMateriasByProfesor();
        break;
        
    case 'edit':
        $materiaController->editMateria();
        break;
        
    case 'delete':
        $materiaController->deleteMateria();
        break;
        
    default:
        // Respuesta por defecto
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'available_endpoints' => [
                'create' => BASE_URL . '/api/materias.php?action=create',
                'all' => BASE_URL . '/api/materias.php?action=all',
                'by-profesor' => BASE_URL . '/api/materias.php?action=by-profesor&profesor_id={id}',
                'edit' => BASE_URL . '/api/materias.php?action=edit',
                'delete' => BASE_URL . '/api/materias.php?action=delete'
            ]
        ], JSON_UNESCAPED_UNICODE);
        break;
}
?>

