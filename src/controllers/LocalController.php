<?php
// src/controllers/LocalController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/Local.php';
require_once __DIR__ . '/../models/Usuario.php';

class LocalController {
    private $model;

    public function __construct() {
        $this->model = new Local();

        if (!isset($_SESSION['user_id'])) {
            redirect('/login?erro=nao_autenticado');
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorId($_SESSION['user_id']);
        if (!$usuario || !in_array($usuario['nivel_acesso'], ['editor', 'editorpro', 'admin'])) {
            redirect('/login?erro=acesso_negado');
        }
    }

    public function index() {
        $locais = $this->model->listarTodos();
        require __DIR__ . '/../views/admin/locais/index.php';
    }

    public function criar() {
        $mps = $this->model->listarMateriasPrimas();
        $erros = [];
        $sucesso = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome_local = trim($_POST['nome_local'] ?? '');
            $mp_id = (int)($_POST['mp_id'] ?? 0);

            if (empty($nome_local)) {
                $erros[] = "O nome do local é obrigatório.";
            }
            if ($mp_id <= 0) {
                $erros[] = "Selecione uma matéria-prima válida.";
            }

            if (empty($erros)) {
                if ($this->model->criar(['nome_local' => $nome_local, 'mp_id' => $mp_id])) {
                    $sucesso = "Local cadastrado com sucesso!";
                } else {
                    $erros[] = "Falha ao salvar no banco de dados.";
                }
            }
        }

        require __DIR__ . '/../views/admin/locais/form.php';
    }

    public function editar(int $id) {
        $local = $this->model->buscarPorId($id);
        if (!$local) {
            die("Local não encontrado.");
        }

        $mps = $this->model->listarMateriasPrimas();
        $erros = [];
        $sucesso = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome_local = trim($_POST['nome_local'] ?? '');
            $mp_id = (int)($_POST['mp_id'] ?? 0);

            if (empty($nome_local)) $erros[] = "Nome do local obrigatório.";
            if ($mp_id <= 0) $erros[] = "Matéria-prima obrigatória.";

            if (empty($erros)) {
                if ($this->model->atualizar($id, ['nome_local' => $nome_local, 'mp_id' => $mp_id])) {
                    $sucesso = "Local atualizado com sucesso!";
                } else {
                    $erros[] = "Erro ao atualizar o registro.";
                }
            }
        }

        require __DIR__ . '/../views/admin/locais/form.php';
    }

    public function excluir(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/locais');
        }

        if ($this->model->excluir($id)) {
            redirect('/admin/locais?msg=excluido');
        } else {
            redirect('/admin/locais?erro=exclusao_falhou');
        }
    }
}