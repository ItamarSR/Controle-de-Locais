<?php

declare(strict_types=1);

// Guardrails para evitar HTTP 500 "mudo" em hospedagem
if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>Erro</h1><p>Este sistema requer PHP 8.1+. Versão atual: " . htmlspecialchars(PHP_VERSION) . "</p>";
    exit;
}
if (!extension_loaded('pdo_mysql')) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>Erro</h1><p>A extensão <code>pdo_mysql</code> não está habilitada no servidor.</p>";
    exit;
}

session_start();

// Isola o Composer: o runtime do sistema NÃO depende de vendor/autoload.php.
// Isso evita 500 em hospedagens onde o vendor foi enviado incompleto ou com permissões erradas.
require_once __DIR__ . '/../src/autoload_fallback.php';

use Core\Http;
use Core\Router;

set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    echo \Core\View::render('errors/500', ['title' => 'Erro', 'message' => $e->getMessage()]);
});

$router = new Router();

// Rotas (serão implementadas nos próximos commits)
$router->get('/', [\App\Controllers\PublicController::class, 'index']);
$router->get('/powerbi', [\App\Controllers\PublicController::class, 'powerbi']);
$router->get('/etiqueta/{id}', [\App\Controllers\PublicController::class, 'etiqueta']);
$router->get('/etiquetas', [\App\Controllers\PublicController::class, 'etiquetas']);
$router->get('/api/locais', [\App\Controllers\PublicController::class, 'apiLocais']);
$router->get('/api/consulta/{codigo}', [\App\Controllers\PublicController::class, 'apiConsulta']);
$router->get('/api/dash-producao', [\App\Controllers\PublicController::class, 'apiDashProducao']);
$router->get('/api/powerbi/ops', [\App\Controllers\PublicController::class, 'apiPowerbiOps']);

$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
$router->get('/reset-senha', [\App\Controllers\AuthController::class, 'showResetSenha']);
$router->post('/reset-senha', [\App\Controllers\AuthController::class, 'resetSenha']);

$router->get('/admin', [\App\Controllers\AdminController::class, 'index']);
$router->get('/admin/dashboard', [\App\Controllers\AdminController::class, 'dashboard']);

// Admin - Locais
$router->get('/admin/locais', [\App\Controllers\LocaisController::class, 'index']);
$router->get('/admin/locais/api/codigo/{codigo}', [\App\Controllers\LocaisController::class, 'apiByCodigo']);
$router->get('/admin/locais/novo', [\App\Controllers\LocaisController::class, 'createForm']);
$router->post('/admin/locais/novo', [\App\Controllers\LocaisController::class, 'create']);
$router->get('/admin/locais/{id}/editar', [\App\Controllers\LocaisController::class, 'editForm']);
$router->post('/admin/locais/{id}/editar', [\App\Controllers\LocaisController::class, 'edit']);
$router->post('/admin/locais/{id}/excluir', [\App\Controllers\LocaisController::class, 'delete']);

// Admin - Usuários (EditorPro/Admin)
$router->get('/admin/usuarios', [\App\Controllers\UsuariosController::class, 'index']);
$router->get('/admin/usuarios/novo', [\App\Controllers\UsuariosController::class, 'createForm']);
$router->post('/admin/usuarios/novo', [\App\Controllers\UsuariosController::class, 'create']);
$router->get('/admin/usuarios/{id}/editar', [\App\Controllers\UsuariosController::class, 'editForm']);
$router->post('/admin/usuarios/{id}/editar', [\App\Controllers\UsuariosController::class, 'edit']);
$router->post('/admin/usuarios/{id}/excluir', [\App\Controllers\UsuariosController::class, 'delete']);

// Admin - Importação MPs
$router->get('/admin/importacao', [\App\Controllers\ImportacaoController::class, 'form']);
$router->post('/admin/importacao', [\App\Controllers\ImportacaoController::class, 'import']);

// Admin - Configurações (EditorPro/Admin; tema somente Admin)
$router->get('/admin/configuracoes', [\App\Controllers\ConfiguracoesController::class, 'form']);
$router->post('/admin/configuracoes', [\App\Controllers\ConfiguracoesController::class, 'save']);

// Conferência - OP
$router->get('/conferencia', [\App\Controllers\ConferenciaController::class, 'dashboard']);
$router->get('/conferencia/op', [\App\Controllers\OpController::class, 'form']);
$router->post('/conferencia/op', [\App\Controllers\OpController::class, 'insert']);
$router->get('/conferencia/op/consulta', [\App\Controllers\OpController::class, 'consulta']);
$router->get('/conferencia/api/op/{op}', [\App\Controllers\OpController::class, 'apiStatus']);

$router->dispatch(Http::method(), Http::path());