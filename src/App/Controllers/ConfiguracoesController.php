<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Settings;
use Core\Http;
use Core\View;

final class ConfiguracoesController extends BaseController
{
    private function uploadLogoIfAny(Settings $s): void
    {
        if (!isset($_FILES['logo_file']) || !is_array($_FILES['logo_file'])) return;
        $f = $_FILES['logo_file'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return;
        if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Falha no upload da logo.'];
            Http::redirect('/admin/configuracoes');
        }

        $tmp = (string)($f['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Upload inválido da logo.'];
            Http::redirect('/admin/configuracoes');
        }

        $mime = (string)@mime_content_type($tmp);
        $ext = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => '',
        };
        if ($ext === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Formato de logo inválido. Envie PNG, JPG/JPEG ou WEBP.'];
            Http::redirect('/admin/configuracoes');
        }

        $name = 'logo.' . $ext;
        $rel = '/uploads/' . $name;

        $targets = [
            dirname(__DIR__, 3) . '/public' . $rel, // /public/uploads
            dirname(__DIR__, 3) . $rel,            // /uploads (webroot alternativo)
        ];

        foreach ($targets as $dest) {
            $dir = dirname($dest);
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
        }

        // move uma vez e copia para o segundo destino
        $first = $targets[0];
        if (!@move_uploaded_file($tmp, $first)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível salvar a logo no servidor.'];
            Http::redirect('/admin/configuracoes');
        }
        @copy($first, $targets[1]);

        $s->set('logo_path', $rel);
    }

    public function form(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $nivel = (string)($_SESSION['nivel'] ?? '');
        $s = new Settings();

        echo View::render('admin/configuracoes', [
            'title' => 'Configurações',
            'nivel' => $nivel,
            'settings' => $s->all(),
            'settings_ok' => $s->tableAvailable(),
            'settings_error' => $s->lastError(),
        ]);
    }

    public function save(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $nivel = (string)($_SESSION['nivel'] ?? '');
        $s = new Settings();

        if (!$s->tableAvailable()) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Tabela de configurações não encontrada no banco. Atualize o banco usando o sql/schema.sql.',
            ];
            Http::redirect('/admin/configuracoes');
        }

        // Impressão (EditorPro/Admin)
        $printPt = (int)($_POST['print_text_pt'] ?? 22);
        if ($printPt < 10) $printPt = 10;
        if ($printPt > 40) $printPt = 40;
        if (!$s->set('print_text_pt', (string)$printPt)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível salvar as configurações de impressão.'];
            Http::redirect('/admin/configuracoes');
        }

        $profile = (string)($_POST['printer_profile'] ?? 'bematech');
        if (!in_array($profile, ['bematech', 'elgin'], true)) $profile = 'bematech';
        $s->set('printer_profile', $profile);

        $ox = (float)($_POST['print_offset_x_mm'] ?? 0);
        $oy = (float)($_POST['print_offset_y_mm'] ?? 0);
        if ($ox < -10) $ox = -10;
        if ($ox > 10) $ox = 10;
        if ($oy < -10) $oy = -10;
        if ($oy > 10) $oy = 10;
        $s->set('print_offset_x_mm', (string)$ox);
        $s->set('print_offset_y_mm', (string)$oy);

        $scale = (float)($_POST['print_scale'] ?? 1);
        if ($scale < 0.80) $scale = 0.80;
        if ($scale > 1.20) $scale = 1.20;
        $s->set('print_scale', (string)$scale);

        // Dash Produção (metas)
        $metaOp = (int)($_POST['dash_meta_op_step'] ?? 4);
        if ($metaOp < 0) $metaOp = 0;
        if ($metaOp > 9999) $metaOp = 9999;
        $s->set('dash_meta_op_step', (string)$metaOp);

        $metaKg = (float)($_POST['dash_meta_kg_step'] ?? 500);
        if ($metaKg < 0) $metaKg = 0;
        if ($metaKg > 999999) $metaKg = 999999;
        $s->set('dash_meta_kg_step', (string)$metaKg);

        // Tema (somente Admin)
        if ($nivel === 'admin') {
            $page = trim((string)($_POST['theme_page'] ?? '#f6f7fb'));
            $header = trim((string)($_POST['theme_header'] ?? '#ffffff'));
            $footer = trim((string)($_POST['theme_footer'] ?? '#ffffff'));
            $form = trim((string)($_POST['theme_form'] ?? '#ffffff'));
            $text = trim((string)($_POST['theme_text'] ?? '#0f172a'));

            // valida hex simples
            foreach (['theme_page' => $page, 'theme_header' => $header, 'theme_footer' => $footer, 'theme_form' => $form, 'theme_text' => $text] as $k => $v) {
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Cores inválidas. Use o seletor de cor.'];
                    Http::redirect('/admin/configuracoes');
                }
            }

            if (
                !$s->set('theme_page', $page) ||
                !$s->set('theme_header', $header) ||
                !$s->set('theme_footer', $footer) ||
                !$s->set('theme_form', $form) ||
                !$s->set('theme_text', $text)
            ) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível salvar as cores do tema.'];
                Http::redirect('/admin/configuracoes');
            }

            // Upload da logo (opcional)
            $this->uploadLogoIfAny($s);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Configurações salvas.'];
        Http::redirect('/admin/configuracoes');
    }
}

