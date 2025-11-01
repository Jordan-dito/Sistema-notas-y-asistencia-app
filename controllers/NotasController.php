<?php
/**
 * Controlador de Notas
 * Maneja las operaciones de notas de estudiantes
 */

require_once '../models/Notas.php';

class NotasController {
    private $notasModel;
    
    public function __construct() {
        $this->notasModel = new Notas();
    }
    
    /**
     * Guardar o actualizar notas (profesor)
     */
    public function guardarNotas() {
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
        $requiredFields = ['estudiante_id', 'materia_id', 'profesor_id', 'año_academico'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $estudianteId = intval($input['estudiante_id']);
        $materiaId = intval($input['materia_id']);
        $profesorId = intval($input['profesor_id']);
        $añoAcademico = $input['año_academico'];
        $nota1 = isset($input['nota_1']) ? ($input['nota_1'] === null ? null : floatval($input['nota_1'])) : null;
        $nota2 = isset($input['nota_2']) ? ($input['nota_2'] === null ? null : floatval($input['nota_2'])) : null;
        $nota3 = isset($input['nota_3']) ? ($input['nota_3'] === null ? null : floatval($input['nota_3'])) : null;
        $nota4 = isset($input['nota_4']) ? ($input['nota_4'] === null ? null : floatval($input['nota_4'])) : null;
        
        // Validar año académico
        if (!preg_match('/^\d{4}$/', $añoAcademico)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de año académico inválido. Use YYYY'
            ]);
            return;
        }
        
        // Guardar notas
        $result = $this->notasModel->guardarNotas(
            $estudianteId, $materiaId, $profesorId, $añoAcademico, 
            $nota1, $nota2, $nota3, $nota4
        );
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener notas de un estudiante en una materia (estudiante)
     */
    public function obtenerNotasEstudiante() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['estudiante_id']) || !isset($_GET['materia_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'estudiante_id y materia_id son requeridos'
            ]);
            return;
        }
        
        $estudianteId = intval($_GET['estudiante_id']);
        $materiaId = intval($_GET['materia_id']);
        $añoAcademico = isset($_GET['año_academico']) ? $_GET['año_academico'] : null;
        
        // Obtener notas
        $result = $this->notasModel->obtenerNotasEstudiante($estudianteId, $materiaId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener todas las notas de estudiantes en una materia (profesor)
     */
    public function obtenerNotasMateria() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id']) || !isset($_GET['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y profesor_id son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        $profesorId = intval($_GET['profesor_id']);
        $añoAcademico = isset($_GET['año_academico']) ? $_GET['año_academico'] : null;
        
        // Obtener notas
        $result = $this->notasModel->obtenerNotasMateria($materiaId, $profesorId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener todas las notas de un estudiante en todas sus materias
     */
    public function obtenerTodasNotasEstudiante() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['estudiante_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'estudiante_id es requerido'
            ]);
            return;
        }
        
        $estudianteId = intval($_GET['estudiante_id']);
        $añoAcademico = isset($_GET['año_academico']) ? $_GET['año_academico'] : null;
        
        // Obtener notas
        $result = $this->notasModel->obtenerTodasNotasEstudiante($estudianteId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
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

