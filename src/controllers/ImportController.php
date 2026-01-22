<?php
// src/controllers/ImportController.php

session_start();
require_once __DIR__ . '/../models/MateriaPrima.php';
require_once __DIR__ . '/../models/Usuario.php';

// Composer/vendor removido — usamos o autoloader local em `src/autoload.php`.
// A biblioteca PhpSpreadsheet é opcional; quando ausente a funcionalidade de importação degrada gentilmente.

use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?erro=nao_autenticado');
            exit;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorId($_SESSION['user_id']);
        if (!$usuario || !in_array($usuario['nivel_acesso'], ['editor', 'editorpro', 'admin'])) {
            header('Location: /login?erro=acesso_negado');
            exit;
        }
    }

    public function index() {
        $erros = [];
        $sucesso = false;

        

        require __DIR__ . '/../views/admin/import-excel.php';
    }
}