<?php
/**
 * Controlador de Configuración de Materia
 * Maneja las operaciones de configuración de asistencia
 */

require_once '../models/ConfiguracionMateria.php';

class ConfiguracionController {
    private $configuracionModel;
    
    public function __construct() {
        $this->configuracionModel = new ConfiguracionMateria();
    }
    
    /**
     * Crear o actualizar configuración de materia
     */
    public function guardarConfiguracion() {
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
        $requiredFields = ['materia_id', 'año_academico', 'fecha_inicio', 'fecha_fin', 'dias_clase'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $materiaId = intval($input['materia_id']);
        $añoAcademico = intval($input['año_academico']);
        $fechaInicio = $input['fecha_inicio'];
        $fechaFin = $input['fecha_fin'];
        $diasClase = $input['dias_clase'];
        $horaClase = $input['hora_clase'] ?? null;
        $metaAsistencia = floatval($input['meta_asistencia'] ?? 80.00);
        
        // Validar fechas
        if (!$this->validarFecha($fechaInicio) || !$this->validarFecha($fechaFin)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Validar que fecha inicio sea anterior a fecha fin
        if (strtotime($fechaInicio) >= strtotime($fechaFin)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'La fecha de inicio debe ser anterior a la fecha de fin'
            ]);
            return;
        }
        
        // Validar días de clase
        if (!$this->validarDiasClase($diasClase)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Días de clase inválidos. Use: lunes,martes,miercoles,jueves,viernes'
            ]);
            return;
        }
        
        // Validar meta de asistencia
        if ($metaAsistencia < 0 || $metaAsistencia > 100) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'La meta de asistencia debe estar entre 0 y 100'
            ]);
            return;
        }
        
        // Validar año académico
        $currentYear = date('Y');
        if ($añoAcademico < 2020 || $añoAcademico > ($currentYear + 1)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Año académico inválido'
            ]);
            return;
        }
        
        // Verificar si ya existe configuración para esta materia y año
        $configExistente = $this->configuracionModel->obtenerConfiguracion($materiaId, $añoAcademico);
        
        if ($configExistente['success']) {
            // Actualizar configuración existente
            $result = $this->configuracionModel->actualizarConfiguracion(
                $materiaId, $añoAcademico, $fechaInicio, $fechaFin, $diasClase, $horaClase, $metaAsistencia
            );
        } else {
            // Crear nueva configuración
            $result = $this->configuracionModel->crearConfiguracion(
                $materiaId, $añoAcademico, $fechaInicio, $fechaFin, $diasClase, $horaClase, $metaAsistencia
            );
        }
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Obtener configuración de una materia
     */
    public function obtenerConfiguracion() {
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
        $añoAcademico = isset($_GET['año_academico']) ? intval($_GET['año_academico']) : null;
        
        // Obtener configuración
        $result = $this->configuracionModel->obtenerConfiguracion($materiaId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Configuración no encontrada') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Obtener configuraciones de un profesor
     */
    public function obtenerConfiguracionesProfesor() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'profesor_id es requerido'
            ]);
            return;
        }
        
        $profesorId = intval($_GET['profesor_id']);
        $añoAcademico = isset($_GET['año_academico']) ? intval($_GET['año_academico']) : null;
        
        // Obtener configuraciones
        $result = $this->configuracionModel->obtenerConfiguracionesProfesor($profesorId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Verificar si una fecha es día de clase
     */
    public function verificarDiaClase() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener parámetros de la URL
        if (!isset($_GET['materia_id']) || !isset($_GET['fecha'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y fecha son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($_GET['materia_id']);
        $fecha = $_GET['fecha'];
        
        // Validar fecha
        if (!$this->validarFecha($fecha)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
            ]);
            return;
        }
        
        // Verificar día de clase
        $result = $this->configuracionModel->esDiaDeClase($materiaId, $fecha);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(500, $result);
        }
    }
    
    /**
     * Eliminar configuración
     */
    public function eliminarConfiguracion() {
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
        
        // Validar datos requeridos
        if (!isset($input['materia_id']) || !isset($input['año_academico'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id y año_academico son requeridos'
            ]);
            return;
        }
        
        $materiaId = intval($input['materia_id']);
        $añoAcademico = intval($input['año_academico']);
        
        // Eliminar configuración
        $result = $this->configuracionModel->eliminarConfiguracion($materiaId, $añoAcademico);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Configuración no encontrada') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
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
     * Validar días de clase
     */
    private function validarDiasClase($diasClase) {
        $diasValidos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $dias = explode(',', $diasClase);
        
        foreach ($dias as $dia) {
            $dia = trim(strtolower($dia));
            if (!in_array($dia, $diasValidos)) {
                return false;
            }
        }
        
        return true;
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
