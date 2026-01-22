<?php
// src/controllers/UsuarioController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $model;

    public function __construct() {
        $this->model = new Usuario();

        if (!isset($_SESSION['user_id']) || !$this->model->podeGerenciarUsuarios($_SESSION['user_id'])) {
            redirect('/login?erro=acesso_negado');
        }
    }

    public function index() {
        $usuarios = $this->model->listarTodos();
        require __DIR__ . '/../views/admin/usuarios/index.php';
    }

    public function criar() {
        $erros = [];
        $sucesso = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'senha' => $_POST['senha'] ?? '',
                'nivel_acesso' => $_POST['nivel_acesso'] ?? ''
            ];

            if (empty($dados['nome'])) $erros[] = "Nome é obrigatório.";
            if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = "Email inválido.";
            if (empty($dados['senha']) || strlen($dados['senha']) < 6) $erros[] = "Senha deve ter no mínimo 6 caracteres.";
            if (!in_array($dados['nivel_acesso'], ['editor', 'editorpro'])) $erros[] = "Nível de acesso inválido.";
            if ($this->model->buscarPorEmail($dados['email'])) $erros[] = "Este email já está cadastrado.";

            if (empty($erros)) {
                if ($this->model->criar($dados)) {
                    $sucesso = "Usuário criado com sucesso! (Primeiro acesso: deve trocar a senha)";
                } else {
                    $erros[] = "Erro ao cadastrar usuário.";
                }
            }
        }

        require __DIR__ . '/../views/admin/usuarios/form.php';
    }

    public function editar(int $id) {
        $usuario = $this->model->buscarPorId($id);
        if (!$usuario) die("Usuário não encontrado.");

        $erros = [];
        $sucesso = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'senha' => $_POST['senha'] ?? '',
                'nivel_acesso' => $_POST['nivel_acesso'] ?? $usuario['nivel_acesso'],
                'status' => (int)($_POST['status'] ?? $usuario['status'])
            ];

            if (empty($dados['nome'])) $erros[] = "Nome é obrigatório.";
            if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = "Email inválido.";
            if ($dados['senha'] && strlen($dados['senha']) < 6) $erros[] = "Nova senha deve ter no mínimo 6 caracteres.";
            if ($dados['email'] !== $usuario['email'] && $this->model->buscarPorEmail($dados['email'])) $erros[] = "Email já em uso.";

            if (empty($erros)) {
                if ($this->model->atualizar($id, $dados)) {
                    $sucesso = "Usuário atualizado com sucesso!";
                } else {
                    $erros[] = "Erro ao atualizar (nível admin protegido?).";
                }
            }
        }

        require __DIR__ . '/../views/admin/usuarios/form.php';
    }

    public function excluir(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/usuarios');
        }

        if ($this->model->excluir($id)) {
            redirect('/admin/usuarios?msg=excluido');
        } else {
            redirect('/admin/usuarios?erro=exclusao_negada');
        }
    }
}