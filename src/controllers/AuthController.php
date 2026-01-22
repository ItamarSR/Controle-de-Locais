<?php
// src/controllers/AuthController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    public function login() {
        $erros = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->buscarPorEmail($email);

            if ($usuario && password_verify($senha, $usuario['senha']) && $usuario['status'] === 1) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['nivel'] = $usuario['nivel_acesso'];
                if ($usuario['primeiro_acesso'] === 1) {
                    $_SESSION['reset_senha_id'] = $usuario['id']; // Temp for reset
                    header('Location: /reset-senha');
                } else {
                    header('Location: /admin/dashboard');
                }
                exit;
            } else {
                $erros[] = "Credenciais inválidas ou usuário inativo.";
            }
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function resetSenha() {
        if (!isset($_SESSION['reset_senha_id'])) {
            header('Location: /login');
            exit;
        }
        $erros = [];
        $sucesso = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha = $_POST['senha'] ?? '';
            $confirm = $_POST['confirm'] ?? '';

            if (strlen($senha) < 6) $erros[] = "Senha deve ter no mínimo 6 caracteres.";
            if ($senha !== $confirm) $erros[] = "Senhas não coincidem.";

            if (empty($erros)) {
                $usuarioModel = new Usuario();
                $dados = ['senha' => $senha];
                if ($usuarioModel->atualizar($_SESSION['reset_senha_id'], $dados) && $usuarioModel->atualizarPrimeiroAcesso($_SESSION['reset_senha_id'])) {
                    $sucesso = true;
                    unset($_SESSION['reset_senha_id']);
                    header('Location: /admin/dashboard');
                    exit;
                } else {
                    $erros[] = "Erro ao atualizar senha.";
                }
            }
        }
        require __DIR__ . '/../views/auth/reset-senha.php';
    }

    public function logout() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header('Location: /login');
        exit;
    }
}