<?php
class AuthMiddleware
{
    public function requireLogin()
    {
        $this->startSecureSession();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
        return true;
    }

    public function requireAdmin()
    {
        $this->requireLogin();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('Access denied');
        }
        return true;
    }

    public function getCurrentUser()
    {
        return $_SESSION['user_id'] ?? null;
    }

    private function startSecureSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (getenv('SESSION_COOKIE_SECURE') === '1');

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        session_set_cookie_params([
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Strict',
        ]);

        session_start();

        if (empty($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
    }
}
?>
