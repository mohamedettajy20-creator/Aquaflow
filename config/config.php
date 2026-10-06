<?php
/**
 * Global application configuration.
 * Loaded once by public/index.php before anything else.
 */

require_once __DIR__ . '/../app/core/Env.php';
Env::load(__DIR__ . '/../.env');

define('APP_NAME', Env::get('APP_NAME', 'AquaFlow'));
define('APP_ENV', Env::get('APP_ENV', 'local'));
define('APP_DEBUG', filter_var(Env::get('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));
define('BASE_URL', rtrim(Env::get('APP_URL', 'http://localhost/aquaflow/public'), '/'));

define('ROOT_PATH', dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOADS_PATH', ROOT_PATH . '/public/uploads');

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

date_default_timezone_set('Africa/Casablanca');
