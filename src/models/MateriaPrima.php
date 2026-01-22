<?php
// src/models/MateriaPrima.php

require_once __DIR__ . '/../../config/database.php';

class MateriaPrima {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    public function importar(array $dados): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO materias_primas (codigo_mp, nome_mp) 
            VALUES (:codigo, :nome)
            ON DUPLICATE KEY UPDATE nome_mp = :nome
        ");
        return $stmt->execute([
            ':codigo' => trim($dados['codigo_mp']),
            ':nome' => trim($dados['nome_mp'])
        ]);
    }
}