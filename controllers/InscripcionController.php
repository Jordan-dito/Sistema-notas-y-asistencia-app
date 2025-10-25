<?php
/**
 * Controlador de Inscripciones
 * Maneja las operaciones de inscripciones de estudiantes en materias
 */

require_once '../models/Inscripcion.php';

class InscripcionController {
    private $inscripcionModel;
    
    public function __construct() {
        $this->inscripcionModel = new Inscripcion();
    }
    
    /**
     * Crear una nueva inscripción
     */
    public function createInscripcion() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use POST'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        $requiredFields = ['estudiante_id', 'materia_id'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $estudianteId = intval($input['estudiante_id']);
        $materiaId = intval($input['materia_id']);
        
        // Crear inscripción
        $result = $this->inscripcionModel->createInscripcion($estudianteId, $materiaId);
        
        if ($result['success']) {
            $this->sendResponse(201, $result);
        } else {
            $statusCode = ($result['message'] == 'Estudiante no encontrado' || $result['message'] == 'Materia no encontrada') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Obtener todas las inscripciones
     */
    public function getAllInscripciones() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        $result = $this->inscripcionModel->getAllInscripciones();
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener inscripciones de un estudiante
     */
    public function getInscripcionesByEstudiante() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener estudiante_id de la URL
        if (!isset($_GET['estudiante_id']) || empty($_GET['estudiante_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del estudiante es requerido'
            ]);
            return;
        }
        
        $estudianteId = intval($_GET['estudiante_id']);
        
        $result = $this->inscripcionModel->getInscripcionesByEstudiante($estudianteId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener inscripciones de materias de un profesor
     */
    public function getInscripcionesByProfesor() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener profesor_id de la URL
        if (!isset($_GET['profesor_id']) || empty($_GET['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del profesor es requerido'
            ]);
            return;
        }
        
        $profesorId = intval($_GET['profesor_id']);
        
        $result = $this->inscripcionModel->getInscripcionesByProfesor($profesorId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Actualizar inscripción
     */
    public function updateInscripcion() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use PUT'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (!isset($input['inscripcion_id']) || empty($input['inscripcion_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID de la inscripción es requerido'
            ]);
            return;
        }
        
        $inscripcionId = intval($input['inscripcion_id']);
        
        // Validar que al menos un campo a actualizar esté presente
        $updateFields = [];
        if (isset($input['estudiante_id']) && !empty($input['estudiante_id'])) {
            $updateFields['estudiante_id'] = intval($input['estudiante_id']);
        }
        if (isset($input['materia_id']) && !empty($input['materia_id'])) {
            $updateFields['materia_id'] = intval($input['materia_id']);
        }
        if (isset($input['estado']) && !empty($input['estado'])) {
            $updateFields['estado'] = $input['estado'];
        }
        
        if (empty($updateFields)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Al menos un campo debe ser proporcionado para actualizar'
            ]);
            return;
        }
        
        // Actualizar inscripción
        $result = $this->inscripcionModel->updateInscripcion($inscripcionId, $updateFields);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Inscripción no encontrada') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Eliminar inscripción
     */
    public function deleteInscripcion() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use DELETE'
            ]);
            return;
        }
        
        // Obtener datos del request
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar ID de la inscripción
        if (!isset($input['inscripcion_id']) || empty($input['inscripcion_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID de la inscripción es requerido'
            ]);
            return;
        }
        
        $inscripcionId = intval($input['inscripcion_id']);
        
        // Eliminar inscripción (cambiar estado a inactivo)
        $result = $this->inscripcionModel->deleteInscripcion($inscripcionId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Inscripción no encontrada') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
?>
