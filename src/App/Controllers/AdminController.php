<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Http;
use Core\View;

final class AdminController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        if ((string)($_SESSION['nivel'] ?? '') === 'conferencia') {
            Http::redirect('/conferencia');
        }
        Http::redirect('/admin/dashboard');
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        if ((string)($_SESSION['nivel'] ?? '') === 'conferencia') {
            Http::redirect('/conferencia');
        }
        echo View::render('admin/dashboard', ['title' => 'Dashboard']);
    }
}

