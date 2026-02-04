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
// Estratégia:
// 1) respeita override via APP_BASE_PATH ou X-Forwarded-Prefix (reverse proxies)
// 2) tenta inferir via SCRIPT_NAME (padrão)
// 3) heurística: se REQUEST_URI começa com o nome da pasta do projeto, usa isso (ex.: /almoxarifado)
$basePathOverride = getenv('APP_BASE_PATH') ?: '';
if (isset($_SERVER['HTTP_X_FORWARDED_PREFIX']) && is_string($_SERVER['HTTP_X_FORWARDED_PREFIX'])) {
    $basePathOverride = $_SERVER['HTTP_X_FORWARDED_PREFIX'];
}

$basePathOverride = trim((string)$basePathOverride);
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = $basePathOverride !== '' ? $basePathOverride : ($scriptName !== '' ? rtrim(str_replace('\\', '/', dirname($scriptName)), '/') : '');

if ($basePath === '' && isset($_SERVER['REQUEST_URI'])) {
    $reqPath = (string)(parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
    $projectDir = basename(dirname(__DIR__)); // pasta do projeto (ex.: almoxarifado)
    if ($projectDir !== '' && preg_match('#^/' . preg_quote($projectDir, '#') . '(/|$)#', $reqPath)) {
        $basePath = '/' . $projectDir;
    }
}

// Normaliza: sempre começa com '/', sem barra final (exceto raiz vazia)
$basePath = trim($basePath);
if ($basePath !== '' && $basePath[0] !== '/') {
    $basePath = '/' . $basePath;
}
$basePath = rtrim($basePath, '/');
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

