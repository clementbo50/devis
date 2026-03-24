<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();


require_once __DIR__ . '/../config/database.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$path = rtrim($path, '/');

if ($path === '' || $path === '/') {
    $path = '/login';
}

$routes = [
    '/login' => ['App\Controllers\AuthController', 'login'],
    '/logout' => ['App\Controllers\AuthController', 'logout'],
    '/register' => ['App\Controllers\AuthController', 'register'],
    '/company/edit' => ['App\Controllers\CompanyController', 'edit'],
    '/clients' => ['App\Controllers\ClientController', 'index'],
    '/clients/create' => ['App\Controllers\ClientController', 'create'],
    '/clients/edit' => ['App\Controllers\ClientController', 'edit'],
    '/clients/delete' => ['App\Controllers\ClientController', 'delete'],
    '/quotes' => ['App\Controllers\QuoteController', 'index'],
    '/quotes/create' => ['App\Controllers\QuoteController', 'create'],
    '/quotes/edit' => ['App\Controllers\QuoteController', 'edit'],
    '/quotes/show' => ['App\Controllers\QuoteController', 'show'],
    '/quotes/delete' => ['App\Controllers\QuoteController', 'delete'],
    '/quotes/update-status' => ['App\Controllers\QuoteController', 'updateStatus'],
    // PDF generation is now done client-side with jsPDF
];

if (isset($routes[$path])) {
    [$controller, $method] = $routes[$path];
    $controllerInstance = new $controller();
    $controllerInstance->$method();
} else {
    http_response_code(404);
    echo "Page non trouvée";
}
