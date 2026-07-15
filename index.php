<?php
require_once 'config/database.php';
require_once 'controllers/SuppressionController.php';
require_once 'middleware/AuthMiddleware.php';

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
    default:
        $controller->showList();
}
?>
