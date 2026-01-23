<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Core\Http;
use Core\View;

final class UsuariosController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $usuarios = (new User())->listAll();
        echo View::render('admin/usuarios/index', ['title' => 'Usuários', 'usuarios' => $usuarios]);
    }

    public function createForm(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        echo View::render('admin/usuarios/form', ['title' => 'Novo usuário']);
    }

    public function create(): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $data = [
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'senha' => (string)($_POST['senha'] ?? ''),
            'nivel_acesso' => (string)($_POST['nivel_acesso'] ?? 'editor'),
        ];

        if ($data['nome'] === '' || $data['email'] === '' || strlen($data['senha']) < 6) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha nome, email e senha (mínimo 6).'];
            Http::redirect('/admin/usuarios/novo');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email inválido.'];
            Http::redirect('/admin/usuarios/novo');
        }
        if (!in_array($data['nivel_acesso'], ['editor', 'editorpro', 'conferencia'], true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nível de acesso inválido.'];
            Http::redirect('/admin/usuarios/novo');
        }

        $userModel = new User();
        if ($userModel->findByEmail($data['email'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Este email já está cadastrado.'];
            Http::redirect('/admin/usuarios/novo');
        }

        if (!$userModel->create($data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível criar o usuário.'];
            Http::redirect('/admin/usuarios/novo');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Usuário criado. Ele deverá trocar a senha no primeiro acesso.'];
        Http::redirect('/admin/usuarios');
    }

    public function editForm(string $id): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $user = (new User())->findById((int)$id);
        if (!$user) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => '/admin/usuarios/' . $id . '/editar']);
            return;
        }
        echo View::render('admin/usuarios/form', ['title' => 'Editar usuário', 'user' => $user]);
    }

    public function edit(string $id): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $data = [
            'nome' => trim((string)($_POST['nome'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'senha' => (string)($_POST['senha'] ?? ''),
            'nivel_acesso' => (string)($_POST['nivel_acesso'] ?? 'editor'),
            'status' => (int)($_POST['status'] ?? 1),
        ];

        if ($data['nome'] === '' || $data['email'] === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nome e email são obrigatórios.'];
            Http::redirect('/admin/usuarios/' . $id . '/editar');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email inválido.'];
            Http::redirect('/admin/usuarios/' . $id . '/editar');
        }
        if ($data['senha'] !== '' && strlen($data['senha']) < 6) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nova senha deve ter no mínimo 6 caracteres.'];
            Http::redirect('/admin/usuarios/' . $id . '/editar');
        }
        if (!in_array($data['nivel_acesso'], ['editor', 'editorpro', 'conferencia'], true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nível de acesso inválido.'];
            Http::redirect('/admin/usuarios/' . $id . '/editar');
        }

        $userModel = new User();
        $existing = $userModel->findById((int)$id);
        if (!$existing) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Usuário não encontrado.'];
            Http::redirect('/admin/usuarios');
        }
        if ($data['email'] !== $existing['email'] && $userModel->findByEmail($data['email'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email já em uso.'];
            Http::redirect('/admin/usuarios/' . $id . '/editar');
        }

        if (!$userModel->update((int)$id, $data)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar o usuário.'];
            Http::redirect('/admin/usuarios/' . $id . '/editar');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Usuário atualizado.'];
        Http::redirect('/admin/usuarios');
    }

    public function delete(string $id): void
    {
        $this->requireRole(['editorpro', 'admin']);
        $ok = (new User())->delete((int)$id);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Usuário excluído.']
            : ['type' => 'danger', 'message' => 'Não foi possível excluir (admin inicial protegido?).'];
        Http::redirect('/admin/usuarios');
    }
}

