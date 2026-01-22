<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Local;
use App\Models\MateriaPrima;
use Core\Http;
use Core\View;

final class LocaisController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $locais = (new Local())->listAdmin();
        echo View::render('admin/locais/index', ['title' => 'Locais', 'locais' => $locais]);
    }

    public function createForm(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $mps = (new MateriaPrima())->listAll();
        echo View::render('admin/locais/form', ['title' => 'Novo local', 'mps' => $mps]);
    }

    public function create(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $nome = trim((string)($_POST['nome_local'] ?? ''));
        $mpId = (int)($_POST['mp_id'] ?? 0);

        if (!(new Local())->create($nome, $mpId)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível salvar o local. Verifique os campos.'];
            Http::redirect('/admin/locais/novo');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Local cadastrado com sucesso.'];
        Http::redirect('/admin/locais');
    }

    public function editForm(string $id): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $local = (new Local())->find((int)$id);
        if (!$local) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => '/admin/locais/' . $id . '/editar']);
            return;
        }
        $mps = (new MateriaPrima())->listAll();
        echo View::render('admin/locais/form', ['title' => 'Editar local', 'local' => $local, 'mps' => $mps]);
    }

    public function edit(string $id): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $nome = trim((string)($_POST['nome_local'] ?? ''));
        $mpId = (int)($_POST['mp_id'] ?? 0);

        if (!(new Local())->update((int)$id, $nome, $mpId)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar o local.'];
            Http::redirect('/admin/locais/' . $id . '/editar');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Local atualizado com sucesso.'];
        Http::redirect('/admin/locais');
    }

    public function delete(string $id): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $ok = (new Local())->delete((int)$id);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Local excluído.']
            : ['type' => 'danger', 'message' => 'Não foi possível excluir o local.'];
        Http::redirect('/admin/locais');
    }
}

