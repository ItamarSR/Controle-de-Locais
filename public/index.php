<?php

declare(strict_types=1);

session_start();

$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
} else {
    require_once __DIR__ . '/../src/autoload_fallback.php';
}

use Core\Http;
use Core\Router;

set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    echo \Core\View::render('errors/500', ['title' => 'Erro', 'message' => $e->getMessage()]);
});

$router = new Router();

// Rotas (serão implementadas nos próximos commits)
$router->get('/', [\App\Controllers\PublicController::class, 'index']);
$router->get('/etiqueta/{id}', [\App\Controllers\PublicController::class, 'etiqueta']);

$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
$router->get('/reset-senha', [\App\Controllers\AuthController::class, 'showResetSenha']);
$router->post('/reset-senha', [\App\Controllers\AuthController::class, 'resetSenha']);

$router->get('/admin', [\App\Controllers\AdminController::class, 'index']);
$router->get('/admin/dashboard', [\App\Controllers\AdminController::class, 'dashboard']);

$router->dispatch(Http::method(), Http::path());