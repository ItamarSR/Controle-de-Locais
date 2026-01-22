<?php

declare(strict_types=1);

// Fallback simples quando não existe vendor/autoload.php.
// Mantém o sistema funcionando (exceto dependências externas, ex.: PhpSpreadsheet).

spl_autoload_register(function (string $class): void {
    $class = ltrim($class, '\\');

    $prefixes = [
        'Core\\' => __DIR__ . '/Core/',
        'App\\' => __DIR__ . '/App/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
    }
});

