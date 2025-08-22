<?php
// config.php - Archivo de configuración para personalizar la aplicación

// Configuración de la aplicación
define('APP_NAME', 'Pallacanestro Management');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Sistema de gestión para equipos de baloncesto');

// Configuración de la base de datos (puede sobrescribir Connection.php)
define('DB_HOST', 'localhost');
define('DB_NAME', 'Indes_goldslide');
define('DB_USER', 'Indes_goldslide');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de paginación
define('RECORDS_PER_PAGE', 10);
define('MAX_RECORDS_PER_PAGE', 100);

// Configuración de archivos
define('MAX_LOGO_SIZE', 2097152); // 2MB en bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('LOGO_UPLOAD_PATH', 'uploads/logos/');

// Configuración de validación
define('MIN_TEAM_NAME_LENGTH', 2);
define('MAX_TEAM_NAME_LENGTH', 100);
define('MIN_FOUNDED_YEAR', 1800);
define('MAX_FOUNDED_YEAR', date('Y'));

// Configuración de la interfaz
define('THEME_PRIMARY_COLOR', '#0078d4');
define('THEME_SECONDARY_COLOR', '#106ebe');
define('THEME_SUCCESS_COLOR', '#107c10');
define('THEME_WARNING_COLOR', '#ff8c00');
define('THEME_DANGER_COLOR', '#d13438');

// Configuración de mensajes
define('MSG_SUCCESS_CREATE', 'Equipo creado exitosamente');
define('MSG_SUCCESS_UPDATE', 'Equipo actualizado exitosamente');
define('MSG_SUCCESS_DELETE', 'Equipo eliminado exitosamente');
define('MSG_ERROR_CREATE', 'Error al crear el equipo');
define('MSG_ERROR_UPDATE', 'Error al actualizar el equipo');
define('MSG_ERROR_DELETE', 'Error al eliminar el equipo');
define('MSG_ERROR_NOT_FOUND', 'Equipo no encontrado');
define('MSG_ERROR_REQUIRED_NAME', 'El nombre del equipo es requerido');

// Configuración de seguridad
define('ENABLE_CSRF_PROTECTION', true);
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOGIN_TIMEOUT', 900); // 15 minutos

// Configuración de logs
define('ENABLE_LOGGING', true);
define('LOG_FILE', 'logs/app.log');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de correo (para futuras funcionalidades)
define('MAIL_HOST', 'localhost');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_FROM_ADDRESS', 'noreply@pallacanestro.com');
define('MAIL_FROM_NAME', APP_NAME);

// Funciones de utilidad para la configuración
function getThemeColors() {
    return [
        'primary' => THEME_PRIMARY_COLOR,
        'secondary' => THEME_SECONDARY_COLOR,
        'success' => THEME_SUCCESS_COLOR,
        'warning' => THEME_WARNING_COLOR,
        'danger' => THEME_DANGER_COLOR
    ];
}

function getAppConfig() {
    return [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'description' => APP_DESCRIPTION
    ];
}

function getValidationConfig() {
    return [
        'min_team_name_length' => MIN_TEAM_NAME_LENGTH,
        'max_team_name_length' => MAX_TEAM_NAME_LENGTH,
        'min_founded_year' => MIN_FOUNDED_YEAR,
        'max_founded_year' => MAX_FOUNDED_YEAR
    ];
}

function getDatabaseConfig() {
    return [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => DB_CHARSET
    ];
}

// Función para generar un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Función para verificar un token CSRF
function verifyCSRFToken($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para logging
function writeLog($level, $message, $context = []) {
    if (!ENABLE_LOGGING) {
        return;
    }
    
    $logLevels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3];
    $currentLevel = $logLevels[LOG_LEVEL] ?? 1;
    $messageLevel = $logLevels[$level] ?? 1;
    
    if ($messageLevel < $currentLevel) {
        return;
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    
    // Crear directorio de logs si no existe
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND | LOCK_EX);
}

// Función para formatear fechas
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return '';
    }
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

// Función para sanitizar entrada
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Función para validar URL
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Función para validar año
function isValidYear($year) {
    return is_numeric($year) && 
           $year >= MIN_FOUNDED_YEAR && 
           $year <= MAX_FOUNDED_YEAR;
}

// Función para generar breadcrumbs
function generateBreadcrumbs($pages) {
    $breadcrumbs = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($pages as $page => $url) {
        if ($url === null) {
            $breadcrumbs .= '<li class="breadcrumb-item active" aria-current="page">' . $page . '</li>';
        } else {
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="' . $url . '">' . $page . '</a></li>';
        }
    }
    
    $breadcrumbs .= '</ol></nav>';
    return $breadcrumbs;
}
?>
