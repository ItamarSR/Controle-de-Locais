<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Http;
use Core\Router;

set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    echo \Core\View::render('errors/500', ['title' => 'Erro', 'message' => $e->getMessage()]);
});

$router = new Router();

// Rotas (serão implementadas nos próximos commits)
$router->get('/', function () {
    echo \Core\View::render('public/home', ['title' => 'Locais']);
});

$router->dispatch(Http::method(), Http::path());