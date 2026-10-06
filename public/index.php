<?php
/**
 * AquaFlow — Front Controller
 * Every request is routed through this single entry point.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/autoload.php';

// --- Secure session configuration -------------------------------------------------
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', '1');
}
session_set_cookie_params((int) Env::get('SESSION_LIFETIME', 120) * 60);
session_start();

// --- Basic security headers -------------------------------------------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// --- Routes --------------------------------------------------------------------
$router = require __DIR__ . '/../config/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
