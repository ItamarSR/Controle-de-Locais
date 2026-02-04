<?php

declare(strict_types=1);

namespace Core;

final class Http
{
    public static function basePath(): string
    {
        // Override explícito (subpasta): ex.: /almoxarifado
        $override = Env::getString('APP_BASE_PATH', '');
        if ($override !== '') {
            $override = '/' . ltrim($override, '/');
            return rtrim($override, '/');
        }

        // Reverse proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_PREFIX']) && is_string($_SERVER['HTTP_X_FORWARDED_PREFIX'])) {
            $p = '/' . ltrim($_SERVER['HTTP_X_FORWARDED_PREFIX'], '/');
            return rtrim($p, '/');
        }

        // Inferir a partir do script
        $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '') {
            $dir = str_replace('\\', '/', dirname($scriptName));
            $dir = $dir === '/' ? '' : rtrim($dir, '/');
            return $dir;
        }

        return '';
    }

    public static function url(string $path = ''): string
    {
        $base = self::basePath();
        if ($path === '' || $path === '/') {
            return ($base !== '' ? $base : '') . '/';
        }
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }

    public static function redirect(string $path, int $code = 302): never
    {
        header('Location: ' . self::url($path), true, $code);
        exit;
    }

    public static function method(): string
    {
        return strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    public static function path(): string
    {
        $uriPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/');
        $base = self::basePath();
        if ($base !== '' && str_starts_with($uriPath, $base)) {
            $uriPath = substr($uriPath, strlen($base)) ?: '/';
        }
        $uriPath = '/' . ltrim($uriPath, '/');

        // Suporte a /index.php/... (hosts sem rewrite)
        if ($uriPath === '/index.php') return '/';
        if (str_starts_with($uriPath, '/index.php/')) {
            $uriPath = '/' . ltrim(substr($uriPath, strlen('/index.php/')), '/');
        }

        return $uriPath;
    }
}

