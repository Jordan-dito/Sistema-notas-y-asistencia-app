<?php
/**
 * Controlador de Asistencia
 * Maneja las operaciones de asistencia
 */

require_once '../models/Asistencia.php';

class AsistenciaController {
    private $asistenciaModel;
    
    public function __construct() {
        $this->asistenciaModel = new Asistencia();
    }
    
    /**
     * Tomar asistencia de toda la clase
     */
    public function tomarAsistencia() {
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
        $requiredFields = ['materia_id', 'fecha_clase', 'asistencias', 'profesor_id'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $materiaId = intval($input['materia_id']);
        $fechaClase = $input['fecha_clase'];
        $asistencias = $input['asistencias'];
        $profesorId = intval($input['profesor_id']);
        
        // Validar fecha
        if (!$this->validarFecha($fechaClase)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Validar asistencias
        if (!is_array($asistencias) || empty($asistencias)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Debe proporcionar al menos una asistencia'
            ]);
            return;
        }
        
        // Validar cada asistencia
        foreach ($asistencias as $asistencia) {
            if (!isset($asistencia['estudiante_id']) || !isset($asistencia['estado'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Cada asistencia debe tener estudiante_id y estado'
                ]);
                return;
            }
            
            if (!in_array($asistencia['estado'], ['presente', 'ausente', 'tardanza'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Estado inválido. Use: presente, ausente, tardanza'
                ]);
                return;
            }
        }
        
        // Tomar asistencia
        $result = $this->asistenciaModel->tomarAsistenciaClase($materiaId, $fechaClase, $asistencias, $profesorId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener asistencia de una clase específica
     */
    public function obtenerAsistenciaClase() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id']) || !isset($_GET['fecha_clase'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y fecha_clase son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        $fechaClase = $_GET['fecha_clase'];
        
        // Validar fecha
        if (!$this->validarFecha($fechaClase)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Obtener asistencia
        $result = $this->asistenciaModel->obtenerAsistenciaClase($materiaId, $fechaClase);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener estadísticas de asistencia de un estudiante
     */
    public function obtenerEstadisticasEstudiante() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
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
        
        // Obtener estadísticas
        $result = $this->asistenciaModel->obtenerEstadisticasEstudiante($estudianteId, $materiaId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener estudiantes inscritos en una materia
     */
    public function obtenerEstudiantesInscritos() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id es requerido'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        
        // Obtener estudiantes
        $result = $this->asistenciaModel->obtenerEstudiantesInscritos($materiaId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Actualizar estado de asistencia individual
     */
    public function actualizarAsistencia() {
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
        if (!isset($input['asistencia_id']) || !isset($input['estado'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'asistencia_id y estado son requeridos'
            ]);
            return;
        }
        
        $asistenciaId = intval($input['asistencia_id']);
        $estado = $input['estado'];
        
        // Validar estado
        if (!in_array($estado, ['presente', 'ausente', 'tardanza'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Estado inválido. Use: presente, ausente, tardanza'
            ]);
            return;
        }
        
        // Actualizar asistencia
        $result = $this->asistenciaModel->actualizarAsistencia($asistenciaId, $estado);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Asistencia no encontrada') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Obtener resumen de asistencia por fecha
     */
    public function obtenerResumenClase() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id']) || !isset($_GET['fecha_clase'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y fecha_clase son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        $fechaClase = $_GET['fecha_clase'];
        
        // Validar fecha
        if (!$this->validarFecha($fechaClase)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Obtener resumen
        $result = $this->asistenciaModel->obtenerResumenClase($materiaId, $fechaClase);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Verificar si existe asistencia para una materia y fecha
     */
    public function verificarAsistencia() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id']) || !isset($_GET['fecha_clase'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y fecha_clase son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        $fechaClase = $_GET['fecha_clase'];
        
        // Validar fecha
        if (!$this->validarFecha($fechaClase)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Verificar asistencia
        $result = $this->asistenciaModel->verificarAsistencia($materiaId, $fechaClase);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Listar todas las asistencias de una fecha específica
     */
    public function listarAsistencias() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id']) || !isset($_GET['fecha_clase'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y fecha_clase son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        $fechaClase = $_GET['fecha_clase'];
        
        // Validar fecha
        if (!$this->validarFecha($fechaClase)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Listar asistencias
        $result = $this->asistenciaModel->listarAsistenciasPorFecha($materiaId, $fechaClase);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Validar formato de fecha
     */
    private function validarFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
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
