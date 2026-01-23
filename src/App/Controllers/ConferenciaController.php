<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\View;

final class ConferenciaController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireRole(['conferencia']);
        echo View::render('conferencia/dashboard', ['title' => 'Conferência']);
    }
}

