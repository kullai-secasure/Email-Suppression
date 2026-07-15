<?php
require_once 'config/database.php';
require_once 'controllers/SuppressionController.php';
require_once 'middleware/AuthMiddleware.php';

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$auth = new AuthMiddleware();
$auth->requireLogin();

$action = $_GET['action'] ?? 'list';
$controller = new SuppressionController();

switch ($action) {
    case 'import':
        $controller->importList();
        break;
    case 'export':
        $controller->exportList();
        break;
    case 'list':
        $controller->showList();
        break;
    default:
        http_response_code(400);
        exit;
}
?>
