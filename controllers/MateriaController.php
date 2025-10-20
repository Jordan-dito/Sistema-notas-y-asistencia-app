<?php
/**
 * Controlador de Materias
 * Maneja las operaciones de materias e inscripciones
 */

require_once '../models/Materia.php';

class MateriaController {
    private $materiaModel;
    
    public function __construct() {
        $this->materiaModel = new Materia();
    }
    
    /**
     * Crear una nueva materia
     */
    public function createMateria() {
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
        $requiredFields = ['nombre', 'grado', 'seccion', 'profesor_id', 'año_academico'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $nombre = trim($input['nombre']);
        $grado = trim($input['grado']);
        $seccion = trim($input['seccion']);
        $profesorId = intval($input['profesor_id']);
        $añoAcademico = intval($input['año_academico']);
        
        // Validar año académico
        $currentYear = date('Y');
        if ($añoAcademico < 2020 || $añoAcademico > ($currentYear + 1)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Año académico inválido'
            ]);
            return;
        }
        
        // Crear materia
        $result = $this->materiaModel->createMateria($nombre, $grado, $seccion, $profesorId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(201, $result);
        } else {
            $statusCode = ($result['message'] == 'Profesor no encontrado') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Obtener todas las materias
     */
    public function getAllMaterias() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        $result = $this->materiaModel->getAllMaterias();
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener materias de un profesor
     */
    public function getMateriasByProfesor() {
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
        
        $result = $this->materiaModel->getMateriasByProfesor($profesorId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Eliminar materia
     */
    public function deleteMateria() {
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
        
        // Validar ID de la materia
        if (!isset($input['materia_id']) || empty($input['materia_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID de la materia es requerido'
            ]);
            return;
        }
        
        $materiaId = intval($input['materia_id']);
        
        // Eliminar materia (cambiar estado a inactivo)
        $result = $this->materiaModel->deleteMateria($materiaId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Materia no encontrada') ? 404 : 500;
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

