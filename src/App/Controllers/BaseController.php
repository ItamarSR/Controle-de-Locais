<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Http;

abstract class BaseController
{
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Faça login para continuar.'];
            Http::redirect('/login');
        }
    }

    /** @param array<int,string> $roles */
    protected function requireRole(array $roles): void
    {
        $this->requireAuth();
        $nivel = (string)($_SESSION['nivel'] ?? '');
        if (!in_array($nivel, $roles, true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Acesso negado.'];
            Http::redirect('/login');
        }
    }
}

