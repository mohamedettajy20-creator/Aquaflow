<?php
/**
 * Middleware — reusable route guards. Each returns a closure so it can be
 * passed directly into Router::get()/post() middleware arrays.
 */
class Middleware
{
    public static function auth(): callable
    {
        return function () {
            if (!Auth::check()) {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        };
    }

    public static function role(string ...$roles): callable
    {
        return function () use ($roles) {
            if (!Auth::check()) {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            if (!in_array(Auth::role(), $roles, true)) {
                http_response_code(403);
                require __DIR__ . '/../views/errors/403.php';
                exit;
            }
        };
    }

    public static function guest(): callable
    {
        return function () {
            if (Auth::check()) {
                $home = match (Auth::role()) {
                    'admin'    => '/admin/dashboard',
                    'agent'    => '/agent/dashboard',
                    'customer' => '/customer/dashboard',
                    default    => '/login',
                };
                header('Location: ' . BASE_URL . $home);
                exit;
            }
        };
    }
}
