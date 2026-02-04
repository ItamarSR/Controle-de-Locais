<?php

declare(strict_types=1);

namespace Core;

final class View
{
    public static function render(string $view, array $data = []): string
    {
        $base = dirname(__DIR__, 2) . '/views';
        $viewFile = $base . '/' . trim($view, '/') . '.php';
        $layoutFile = $base . '/layouts/app.php';

        if (!file_exists($viewFile)) {
            return "View não encontrada: " . htmlspecialchars($viewFile);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = (string)ob_get_clean();

        ob_start();
        require $layoutFile;
        return (string)ob_get_clean();
    }
}

