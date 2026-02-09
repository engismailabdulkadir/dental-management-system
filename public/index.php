<?php
require_once __DIR__ . '/../config/database.php';

session_start();

$routes = require_once __DIR__ . '/../src/Routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/dental-management-system/public', '', $uri);
$uri = $uri ?: '/';

// Force login for all pages except login
$publicRoutes = ['/login', '/login/store'];
if (!isset($_SESSION['user']) && !in_array($uri, $publicRoutes, true)) {
    header("Location: /dental-management-system/public/login");
    exit;
}

if (!isset($routes[$uri])) {
    http_response_code(404);
    echo "404 - Page Not Found";
    exit;
}

[$controller, $method] = $routes[$uri];

require_once __DIR__ . "/../src/Controllers/$controller.php";

$controllerInstance = new $controller();
$controllerInstance->$method();
?>
