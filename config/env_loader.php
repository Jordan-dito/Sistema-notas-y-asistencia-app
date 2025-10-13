<?php
/**
 * Cargador de Variables de Entorno
 * Lee el archivo .env y define las constantes
 */

function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        throw new Exception("Archivo .env no encontrado en: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Separar clave y valor
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si las tiene
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            // Definir la constante si no existe
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Cargar variables de entorno
try {
    loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    // Si no existe .env, usar valores por defecto
    define('ENVIRONMENT', 'local');
    define('DB_HOST_LOCAL', 'localhost');
    define('DB_NAME_LOCAL', 'colegio_db');
    define('DB_USER_LOCAL', 'root');
    define('DB_PASS_LOCAL', '');
    define('DB_CHARSET_LOCAL', 'utf8mb4');
    define('APP_NAME', 'Colegio API');
    define('APP_VERSION', '1.0.0');
    define('JWT_SECRET', 'default_secret_key');
    define('BASE_PATH_LOCAL', '/controladores api flutter');
    define('BASE_PATH_HOSTING', '');
    define('DOMAIN_HOSTING', 'tu-dominio.com');
    define('DOMAIN_LOCAL', 'localhost');
}
?>
