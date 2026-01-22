<?php
// src/bootstrap.php — bootstrap comum (web/CLI)
// - inicia sessão (web)
// - carrega conexão com banco (config/database.php)
// - carrega autoload local (src/autoload.php) e, se existir, Composer (vendor/autoload.php)
// - define BASE_PATH para suporte a subdiretório e helpers url()/redirect()

declare(strict_types=1);

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Se existir, habilita dependências opcionais (ex.: PhpSpreadsheet)
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

require_once __DIR__ . '/autoload.php';

// Evita "tela branca": renderiza um erro amigável em caso de exceção não tratada.
if (PHP_SAPI !== 'cli') {
    set_exception_handler(function (Throwable $e): void {
        http_response_code(500);
        $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo "<!doctype html><html lang=\"pt-BR\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
        echo "<title>Erro no sistema</title>";
        echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">";
        echo "</head><body class=\"container py-5\">";
        echo "<div class=\"alert alert-danger\"><h1 class=\"h4 mb-2\">O sistema encontrou um erro</h1><div>{$msg}</div></div>";
        echo "<p class=\"text-muted mb-0\">Se o erro for de banco, verifique DB_HOST/DB_NAME/DB_USER/DB_PASS e a conexão com o MySQL.</p>";
        echo "</body></html>";
    });
}

// BASE_PATH: permite o app rodar em subdiretório (ex.: /almoxarifado)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = $scriptName !== '' ? rtrim(str_replace('\\', '/', dirname($scriptName)), '/') : '';
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $basePath);
}

/**
 * Monta URL interna respeitando BASE_PATH.
 * Aceita path com ou sem barra inicial. Também suporta query string.
 */
function url(string $path = ''): string
{
    $base = defined('BASE_PATH') ? (string)BASE_PATH : '';

    if ($path === '' || $path === '/') {
        return $base !== '' ? $base . '/' : '/';
    }

    // Preserva query string
    $q = '';
    $pos = strpos($path, '?');
    if ($pos !== false) {
        $q = substr($path, $pos);
        $path = substr($path, 0, $pos);
    }

    if ($path !== '' && $path[0] !== '/') {
        $path = '/' . $path;
    }

    return $base . $path . $q;
}

/**
 * Redirect interno respeitando BASE_PATH.
 */
function redirect(string $path, int $code = 302): void
{
    header('Location: ' . url($path), true, $code);
    exit;
}

