<?php
// src/autoload.php — autoloader mínimo para este projeto (substitui o autoload do Composer para classes do diretório src/)
// - procura em src/controllers, src/models e src/
// - mantém compatibilidade com chamadas sem namespace (ex.: new \AuthController())

spl_autoload_register(function (string $class) {
    $class = ltrim($class, "\\");

    // nomes simples (controllers/models sem namespace)
    $candidates = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/' . $class . '.php',
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // suporta nomes com namespace -> src/Namespace/Name.php
    $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
        return;
    }

    // não encontrou — silêncio (comportamento similar ao Composer: não lança)
});
