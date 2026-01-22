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
        Http::redirect('/admin/dashboard');
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        echo View::render('admin/dashboard', ['title' => 'Dashboard']);
    }
}

