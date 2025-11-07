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
     * Obtener material de reforzamiento por ID
     */
    public function obtenerMaterialPorId() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use GET'
            ]);
            return;
        }
        
        // Obtener parámetros
        if (!isset($_GET['material_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'material_id es requerido'
            ]);
            return;
        }
        
        $materialId = intval($_GET['material_id']);
        $profesorId = isset($_GET['profesor_id']) ? intval($_GET['profesor_id']) : null;
        
        // Obtener material
        $result = $this->materialModel->obtenerMaterialPorId($materialId, $profesorId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = strpos($result['message'], 'no encontrado') !== false || 
                         strpos($result['message'], 'permisos') !== false ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Actualizar material de reforzamiento
     */
    public function editarMaterial() {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use PUT'
            ]);
            return;
        }
        
        // Obtener datos del request (JSON)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input === null) {
            // Si no es JSON, intentar con $_POST (form-data)
            $input = $_POST;
        }
        
        // Validar campos requeridos
        if (!isset($input['material_id']) || empty($input['material_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'material_id es requerido'
            ]);
            return;
        }
        
        if (!isset($input['profesor_id']) || empty($input['profesor_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'profesor_id es requerido'
            ]);
            return;
        }
        
        $materialId = intval($input['material_id']);
        $profesorId = intval($input['profesor_id']);
        
        // Preparar datos para actualizar (solo los campos que se envíen)
        $data = [];
        
        if (isset($input['titulo'])) {
            $data['titulo'] = trim($input['titulo']);
        }
        
        if (isset($input['descripcion'])) {
            $data['descripcion'] = trim($input['descripcion']);
        }
        
        if (isset($input['tipo_contenido'])) {
            // Validar tipo de contenido
            if (!in_array($input['tipo_contenido'], ['texto', 'link'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => 'Tipo de contenido inválido. Solo se permite: texto o link'
                ]);
                return;
            }
            $data['tipo_contenido'] = $input['tipo_contenido'];
            
            // Validar campos requeridos según el tipo
            if ($input['tipo_contenido'] === 'link') {
                if (!isset($input['url_externa']) || empty(trim($input['url_externa']))) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'message' => 'url_externa es requerida para tipo link'
                    ]);
                    return;
                }
            }
            
            if ($input['tipo_contenido'] === 'texto') {
                if (!isset($input['contenido']) || empty(trim($input['contenido']))) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'message' => 'contenido es requerido para tipo texto'
                    ]);
                    return;
                }
            }
        }
        
        if (isset($input['contenido'])) {
            $data['contenido'] = $input['contenido'];
        }
        
        if (isset($input['url_externa'])) {
            $data['url_externa'] = trim($input['url_externa']);
        }
        
        if (isset($input['estudiante_id'])) {
            $data['estudiante_id'] = $input['estudiante_id'] !== '' && $input['estudiante_id'] !== null 
                ? intval($input['estudiante_id']) 
                : null;
        }
        
        if (isset($input['fecha_vencimiento'])) {
            $data['fecha_vencimiento'] = $input['fecha_vencimiento'] !== '' && $input['fecha_vencimiento'] !== null
                ? $input['fecha_vencimiento']
                : null;
        }
        
        if (isset($input['año_academico'])) {
            $data['año_academico'] = $input['año_academico'];
        }
        
        if (isset($input['materia_id'])) {
            $data['materia_id'] = intval($input['materia_id']);
        }
        
        // Actualizar material
        $result = $this->materialModel->actualizarMaterial($materialId, $profesorId, $data);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = strpos($result['message'], 'no encontrado') !== false || 
                         strpos($result['message'], 'permisos') !== false ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
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
     * Obtener datos de un estudiante
     */
    public function getEstudianteData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            return;
        }
        
        if (!isset($_GET['estudiante_id']) || empty($_GET['estudiante_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del estudiante es requerido'
            ]);
            return;
        }
        
        $estudianteId = intval($_GET['estudiante_id']);
        
        require_once '../models/User.php';
        $userModel = new User();
        
        $result = $userModel->getStudentById($estudianteId);
        
        if ($result['success']) {
            $this->sendResponse(200, $result);
        } else {
            $statusCode = ($result['message'] == 'Estudiante no encontrado') ? 404 : 500;
            $this->sendResponse($statusCode, $result);
        }
    }
    
    /**
     * Actualizar datos de un estudiante
     */
    public function updateEstudianteData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendResponse(405, [
                'success' => false,
                'message' => 'Método no permitido. Use PUT'
            ]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['estudiante_id']) || empty($input['estudiante_id'])) {
            $this->sendResponse(400, [
                'success' => false,
                'message' => 'ID del estudiante es requerido'
            ]);
            return;
        }
        
        $estudianteId = $input['estudiante_id'];
        
        // Validar campos requeridos
        $requiredFields = ['nombre', 'apellido', 'grado', 'seccion'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'message' => "El campo $field es requerido"
                ]);
                return;
            }
        }
        
        $studentData = [
            'nombre' => trim($input['nombre']),
            'apellido' => trim($input['apellido']),
            'grado' => trim($input['grado']),
            'seccion' => trim($input['seccion']),
            'telefono' => $input['telefono'] ?? null,
            'direccion' => $input['direccion'] ?? null,
            'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null
        ];
        
        require_once '../models/User.php';
        $userModel = new User();
        
        // Validar estudiante duplicado (excluyendo el actual)
        $validacion_estudiante = $userModel->validarEstudianteDuplicado(
            $studentData['nombre'], 
            $studentData['apellido'],
            $studentData['grado'],
            $studentData['seccion'],
            $estudianteId
        );
        
        if ($validacion_estudiante['existe']) {
            $this->sendResponse(409, [
                'success' => false,
                'message' => $validacion_estudiante['mensaje']
            ]);
            return;
        }
        
        $result = $userModel->updateStudent($estudianteId, $studentData);
        
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

