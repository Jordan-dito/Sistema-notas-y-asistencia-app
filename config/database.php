<?php
/**
 * Configuración de Base de Datos
 * Configuración flexible para local y hosting
 */

// Configuración para diferentes entornos
$config = [
    'local' => [
        'host' => 'localhost',
        'dbname' => 'colegio_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'hosting' => [
        'host' => 'localhost', // o la IP del servidor
        'dbname' => 'tu_base_datos_hosting',
        'username' => 'tu_usuario_hosting',
        'password' => 'tu_password_hosting',
        'charset' => 'utf8mb4'
    ]
];

// Detectar entorno automáticamente
$environment = 'local'; // Cambiar a 'hosting' cuando subas al servidor

// O detectar automáticamente por dominio
if (isset($_SERVER['HTTP_HOST'])) {
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
        $environment = 'local';
    } else {
        $environment = 'hosting';
    }
}

// Configuración actual
$db_config = $config[$environment];

// Constantes para usar en toda la aplicación
define('DB_HOST', $db_config['host']);
define('DB_NAME', $db_config['dbname']);
define('DB_USER', $db_config['username']);
define('DB_PASS', $db_config['password']);
define('DB_CHARSET', $db_config['charset']);

// Base path para URLs
$base_path = '';
if ($environment === 'local') {
    $base_path = '/controladores-api-flutter'; // Ajustar según tu carpeta local
} else {
    $base_path = ''; // En hosting suele ser la raíz
}

define('BASE_PATH', $base_path);
define('BASE_URL', 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $base_path);

// Configuración de la aplicación
define('APP_NAME', 'Colegio API');
define('APP_VERSION', '1.0.0');
define('JWT_SECRET', 'tu_clave_secreta_jwt_aqui'); // Cambiar por una clave segura

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
