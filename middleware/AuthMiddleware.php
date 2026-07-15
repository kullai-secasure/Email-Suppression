<?php
class AuthMiddleware
{
    public function requireLogin()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
        return true;
    }

    public function requireAdmin()
    {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die('Access denied');
        }
        return true;
    }

    public function getCurrentUser()
    {
        return $_SESSION['user_id'] ?? null;
    }
}
?>
