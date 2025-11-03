<?php
/**
 * Controlador de Material de Reforzamiento
 * Maneja las operaciones de material de reforzamiento para estudiantes reprobados
 */

require_once '../models/MaterialReforzamiento.php';

class MaterialReforzamientoController {
    private $materialModel;
    
    public function __construct() {
        $this->materialModel = new MaterialReforzamiento();
    }
    
    /**
     * Subir material de reforzamiento (profesor)
     */
    public function subirMaterial() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use POST'
            ]);
            return;
        }
        
        // Obtener datos del request
        $materiaId = isset($_POST['materia_id']) ? intval($_POST['materia_id']) : null;
        $estudianteId = isset($_POST['estudiante_id']) && $_POST['estudiante_id'] !== '' ? intval($_POST['estudiante_id']) : null;
        $profesorId = isset($_POST['profesor_id']) ? intval($_POST['profesor_id']) : null;
        $añoAcademico = isset($_POST['año_academico']) ? $_POST['año_academico'] : date('Y');
        $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : null;
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        $tipoContenido = isset($_POST['tipo_contenido']) ? $_POST['tipo_contenido'] : 'texto';
        $contenido = isset($_POST['contenido']) ? $_POST['contenido'] : null;
        
        // Validar campos requeridos
        if (!$materiaId || !$profesorId || !$titulo) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'materia_id, profesor_id y titulo son requeridos'
            ]);
            return;
        }
        
        // Validar tipo de contenido (solo texto y link permitidos)
        $tiposValidos = ['texto', 'link'];
        if (!in_array($tipoContenido, $tiposValidos)) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'Tipo de contenido inválido. Solo se permite: texto o link'
            ]);
            return;
        }
        
        // No se aceptan archivos, solo link y texto
        $archivo = null;
        
        // Si es link, necesita url_externa
        $urlExterna = null;
        if ($tipoContenido === 'link') {
            if (!isset($_POST['url_externa']) || empty($_POST['url_externa'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'url_externa es requerida para tipo link'
                ]);
                return;
            }
            $urlExterna = trim($_POST['url_externa']);
        }
        
        // Si es texto, necesita contenido
        if ($tipoContenido === 'texto') {
            if (!isset($_POST['contenido']) || empty(trim($_POST['contenido']))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'contenido es requerido para tipo texto'
                ]);
                return;
            }
        }
        
        // Subir material
        $result = $this->materialModel->subirMaterial(
            $materiaId,
            $estudianteId,
            $profesorId,
            $añoAcademico,
            $titulo,
            $descripcion,
            $tipoContenido,
            $contenido,
            $archivo,
            $urlExterna
        );
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = strpos($result['message'], 'reprobado') !== false ? 400 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Obtener material para estudiante reprobado
     */
    public function obtenerMaterialEstudiante() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros
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
        
        // Obtener material
        $result = $this->materialModel->obtenerMaterialEstudiante($estudianteId, $materiaId, $añoAcademico);
        
        $this->sendResponse(200, $result);
    }
    
    /**
     * Obtener estudiantes reprobados de una materia (profesor)
     */
    public function obtenerEstudiantesReprobados() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros
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
        
        // Obtener estudiantes reprobados
        $result = $this->materialModel->obtenerEstudiantesReprobados($materiaId, $profesorId, $añoAcademico);
        
        $this->sendResponse(200, $result);
    }
    
    /**
     * Obtener material de un estudiante específico (profesor)
     */
    public function obtenerMaterialPorEstudiante() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros
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
        
        // Obtener material
        $result = $this->materialModel->obtenerMaterialPorEstudiante($estudianteId, $materiaId, $añoAcademico);
        
        $this->sendResponse(200, $result);
    }
    
    /**
     * Eliminar material de reforzamiento
     */
    public function eliminarMaterial() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use DELETE'
            ]);
            return;
        }
        
        // Obtener parámetros
        if (!isset($_GET['material_id']) || !isset($_GET['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'material_id y profesor_id son requeridos'
            ]);
            return;
        }
        
        $materialId = intval($_GET['material_id']);
        $profesorId = intval($_GET['profesor_id']);
        
        // Eliminar material
        $result = $this->materialModel->eliminarMaterial($materialId, $profesorId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = strpos($result['message'], 'no encontrado') !== false ? 404 : 500;
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

