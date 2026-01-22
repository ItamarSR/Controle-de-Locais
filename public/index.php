<?php
// public/index.php (ponto de entrada único)

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/autoload.php';

// Detectar e remover um possível base path (suporte a execução em subdiretório)
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($basePath !== '' && strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}
$path = trim($requestPath, '/');

$segments = explode('/', $path);

switch ($segments[0]) {
    case '':
        require __DIR__ . '/../src/views/public/index.php'; // Dashboard público
        break;

    case 'login':
        (new \AuthController())->login();
        break;

    case 'reset-senha':
        (new \AuthController())->resetSenha();
        break;

    case 'logout':
        (new \AuthController())->logout();
        break;

    case 'admin':
        if (isset($segments[1])) {
            switch ($segments[1]) {
                case 'dashboard':
                    require __DIR__ . '/../src/views/admin/dashboard.php';
                    break;

                case 'locais':
                    $controller = new \LocalController();
                    if (isset($segments[2])) {
                        if ($segments[2] === 'criar') {
                            $controller->criar();
                        } elseif ($segments[2] === 'editar' && isset($segments[3])) {
                            $controller->editar((int)$segments[3]);
                        } elseif ($segments[2] === 'excluir' && isset($segments[3])) {
                            $controller->excluir((int)$segments[3]);
                        } else {
                            http_response_code(404);
                            echo "Página não encontrada";
                        }
                    } else {
                        $controller->index();
                    }
                    break;

                case 'usuarios':
                    $controller = new \UsuarioController();
                    if (isset($segments[2])) {
                        if ($segments[2] === 'criar') {
                            $controller->criar();
                        } elseif ($segments[2] === 'editar' && isset($segments[3])) {
                            $controller->editar((int)$segments[3]);
                        } elseif ($segments[2] === 'excluir' && isset($segments[3])) {
                            $controller->excluir((int)$segments[3]);
                        } else {
                            http_response_code(404);
                            echo "Página não encontrada";
                        }
                    } else {
                        $controller->index();
                    }
                    break;

                case 'import-excel':
                    (new \ImportController())->index();
                    break;

                default:
                    http_response_code(404);
                    echo "Página não encontrada";
            }
        } else {
            header('Location: /admin/dashboard');
            exit;
        }
        break;

    default:
        http_response_code(404);
        echo "Página não encontrada";
}