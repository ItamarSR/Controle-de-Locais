<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Core\Http;
use Core\View;

final class AuthController extends BaseController
{
    public function showLogin(): void
    {
        echo View::render('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Informe email e senha.'];
            Http::redirect('/login');
        }

        $userModel = new User();
        $u = $userModel->findByEmail($email);

        if (!$u || (int)$u['status'] !== 1 || !password_verify($senha, (string)$u['senha'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Credenciais inválidas ou usuário inativo.'];
            Http::redirect('/login');
        }

        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['nivel'] = (string)$u['nivel_acesso'];

        if ((int)$u['primeiro_acesso'] === 1) {
            Http::redirect('/reset-senha');
        }

        Http::redirect('/admin/dashboard');
    }

    public function showResetSenha(): void
    {
        $this->requireAuth();
        echo View::render('auth/reset', ['title' => 'Trocar senha']);
    }

    public function resetSenha(): void
    {
        $this->requireAuth();

        $senha = (string)($_POST['senha'] ?? '');
        $confirm = (string)($_POST['confirm'] ?? '');

        if (strlen($senha) < 6) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'A senha deve ter no mínimo 6 caracteres.'];
            Http::redirect('/reset-senha');
        }
        if ($senha !== $confirm) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'As senhas não coincidem.'];
            Http::redirect('/reset-senha');
        }

        $userModel = new User();
        $ok = $userModel->update((int)$_SESSION['user_id'], ['senha' => $senha, 'primeiro_acesso' => 0]);
        if (!$ok) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar a senha.'];
            Http::redirect('/reset-senha');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Senha atualizada com sucesso.'];
        Http::redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        Http::redirect('/login');
    }
}

