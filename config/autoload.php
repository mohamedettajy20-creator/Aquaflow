<?php
/**
 * Simple PSR-0-ish autoloader — no Composer dependency required.
 * Searches core/, models/, controllers/, services/, middleware/ in order.
 */
spl_autoload_register(function (string $class) {
    $dirs = [
        ROOT_PATH . '/app/core/',
        ROOT_PATH . '/app/models/',
        ROOT_PATH . '/app/controllers/',
        ROOT_PATH . '/app/services/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/helpers/functions.php';
