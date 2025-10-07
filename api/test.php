<?php
/**
 * API de Prueba
 * Para probar la conexión y configuración
 */

require_once '../config/database.php';
require_once '../config/connection.php';

// Función para enviar respuesta JSON
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Obtener información del servidor
$serverInfo = [
    'success' => true,
    'message' => 'API funcionando correctamente',
    'data' => [
        'app_name' => APP_NAME,
        'app_version' => APP_VERSION,
        'environment' => $environment ?? 'unknown',
        'base_url' => BASE_URL,
        'base_path' => BASE_PATH,
        'server_time' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'server_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ]
];

// Probar conexión a base de datos
try {
    $db = DatabaseConnection::getInstance();
    if ($db->testConnection()) {
        $serverInfo['data']['database'] = [
            'status' => 'connected',
            'host' => DB_HOST,
            'database' => DB_NAME
        ];
    } else {
        $serverInfo['data']['database'] = [
            'status' => 'error',
            'message' => 'No se pudo conectar a la base de datos'
        ];
    }
} catch (Exception $e) {
    $serverInfo['data']['database'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Mostrar información
sendResponse(200, $serverInfo);
?>
