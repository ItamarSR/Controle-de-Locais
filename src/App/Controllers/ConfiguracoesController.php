<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Settings;
use Core\Http;
use Core\View;

final class ConfiguracoesController extends BaseController
{
    public function form(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $nivel = (string)($_SESSION['nivel'] ?? '');
        $s = new Settings();

        echo View::render('admin/configuracoes', [
            'title' => 'Configurações',
            'nivel' => $nivel,
            'settings' => $s->all(),
        ]);
    }

    public function save(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $nivel = (string)($_SESSION['nivel'] ?? '');
        $s = new Settings();

        // Impressão (EditorPro/Admin)
        $printPt = (int)($_POST['print_text_pt'] ?? 22);
        if ($printPt < 10) $printPt = 10;
        if ($printPt > 40) $printPt = 40;
        $s->set('print_text_pt', (string)$printPt);

        // Tema (somente Admin)
        if ($nivel === 'admin') {
            $page = trim((string)($_POST['theme_page'] ?? '#f6f7fb'));
            $header = trim((string)($_POST['theme_header'] ?? '#ffffff'));
            $footer = trim((string)($_POST['theme_footer'] ?? '#ffffff'));

            // valida hex simples
            foreach (['theme_page' => $page, 'theme_header' => $header, 'theme_footer' => $footer] as $k => $v) {
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Cores inválidas. Use o seletor de cor.'];
                    Http::redirect('/admin/configuracoes');
                }
            }

            $s->set('theme_page', $page);
            $s->set('theme_header', $header);
            $s->set('theme_footer', $footer);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Configurações salvas.'];
        Http::redirect('/admin/configuracoes');
    }
}

