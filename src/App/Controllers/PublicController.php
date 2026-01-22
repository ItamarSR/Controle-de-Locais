<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Local;
use Core\View;

final class PublicController
{
    public function index(): void
    {
        $locais = (new Local())->listPublic();
        echo View::render('public/home', ['title' => 'Locais', 'locais' => $locais]);
    }

    public function etiqueta(string $id): void
    {
        $local = (new Local())->findWithMp((int)$id);
        if (!$local) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => '/etiqueta/' . $id]);
            return;
        }

        // etiqueta é uma página simples de impressão (sem layout)
        require dirname(__DIR__, 3) . '/views/public/etiqueta.php';
    }
}

