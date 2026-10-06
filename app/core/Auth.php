<?php
/**
 * Auth — session-based authentication helper.
 * Handles login/logout, password hashing/verification, and role checks.
 * Passwords are always stored using PHP's password_hash() (bcrypt).
 */
class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $userModel = new UserModel();
        $user = $userModel->first(['email' => $email]);

        if (!$user) {
            return false;
        }
        if ($user['status'] !== 'active') {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::login($user);
        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['avatar']    = $user['avatar'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isAgent(): bool
    {
        return self::role() === 'agent';
    }

    public static function isCustomer(): bool
    {
        return self::role() === 'customer';
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        static $cached = null;
        if ($cached === null) {
            $cached = (new UserModel())->find(self::id());
        }
        return $cached;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
