<?php
/**
 * Configuración de Base de Datos
 * Configuración flexible para local y hosting usando variables de entorno
 */

// Cargar variables de entorno
require_once __DIR__ . '/env_loader.php';

// Detectar entorno automáticamente
$environment = ENVIRONMENT;

// O detectar automáticamente por dominio si no está definido
if (!defined('ENVIRONMENT') || ENVIRONMENT === 'auto') {
    if (isset($_SERVER['HTTP_HOST'])) {
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
            $environment = 'local';
        } else {
            $environment = 'hosting';
        }
    } else {
        $environment = 'local';
    }
}

// Configuración para diferentes entornos usando variables de entorno
$config = [
    'local' => [
        'host' => DB_HOST_LOCAL,
        'dbname' => DB_NAME_LOCAL,
        'username' => DB_USER_LOCAL,
        'password' => DB_PASS_LOCAL,
        'charset' => DB_CHARSET_LOCAL
    ],
    'hosting' => [
        'host' => DB_HOST_HOSTING,
        'dbname' => DB_NAME_HOSTING,
        'username' => DB_USER_HOSTING,
        'password' => DB_PASS_HOSTING,
        'charset' => DB_CHARSET_HOSTING
    ]
];

// Configuración actual
$db_config = $config[$environment];

// Constantes para usar en toda la aplicación
define('DB_HOST', $db_config['host']);
define('DB_NAME', $db_config['dbname']);
define('DB_USER', $db_config['username']);
define('DB_PASS', $db_config['password']);
define('DB_CHARSET', $db_config['charset']);

// Base path para URLs usando variables de entorno
$base_path = '';
if ($environment === 'local') {
    $base_path = BASE_PATH_LOCAL;
} else {
    $base_path = BASE_PATH_HOSTING;
}

define('BASE_PATH', $base_path);

// Configurar URL base usando variables de entorno
if ($environment === 'local') {
    define('BASE_URL', 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . DOMAIN_LOCAL . $base_path);
} else {
    define('BASE_URL', 'https://' . DOMAIN_HOSTING . $base_path);
}

// Configuración de la aplicación usando variables de entorno
// Las constantes ya están definidas en env_loader.php

// Configuración de CORS para Flutter
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
