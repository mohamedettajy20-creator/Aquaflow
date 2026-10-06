<?php
/**
 * Controller — base class for all controllers.
 * Provides view rendering (with layout support), JSON responses for AJAX,
 * and simple redirect helpers.
 */
abstract class Controller
{
    /**
     * Render a view file wrapped in the given layout.
     * @param string $view   e.g. "admin/dashboard" -> app/views/admin/dashboard.php
     * @param array  $data   variables extracted into the view's scope
     * @param string|null $layout  layout file name (without .php) in app/views/layouts, or null for none
     */
    protected function view(string $view, array $data = [], ?string $layout = 'app'): void
    {
        extract($data);
        $viewFile = __DIR__ . "/../views/{$view}.php";

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View not found: {$view}");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        // The layout includes $content by capturing the view's output first.
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . "/../views/layouts/{$layout}.php";
        require $layoutFile;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    /** Read JSON or form input uniformly for AJAX + classic form posts. */
    protected function input(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        return $_POST;
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function flash(string $key, ?string $message = null)
    {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}
