<?php
// src/models/Local.php

require_once __DIR__ . '/../../config/database.php';

class Local {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    public function listarTodos(): array {
        $stmt = $this->pdo->prepare("
            SELECT l.id, l.nome_local, l.mp_id, l.data_cadastro, mp.codigo_mp, mp.nome_mp
            FROM locais l
            INNER JOIN materias_primas mp ON l.mp_id = mp.id
            ORDER BY l.nome_local ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM locais WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(array $dados): bool {
        $stmt = $this->pdo->prepare("INSERT INTO locais (nome_local, mp_id, data_cadastro) VALUES (:nome_local, :mp_id, NOW())");
        return $stmt->execute([
            ':nome_local' => trim($dados['nome_local']),
            ':mp_id' => (int)$dados['mp_id']
        ]);
    }

    public function atualizar(int $id, array $dados): bool {
        $stmt = $this->pdo->prepare("UPDATE locais SET nome_local = :nome_local, mp_id = :mp_id WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':nome_local' => trim($dados['nome_local']),
            ':mp_id' => (int)$dados['mp_id']
        ]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM locais WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function listarMateriasPrimas(): array {
        $stmt = $this->pdo->prepare("SELECT id, codigo_mp, nome_mp FROM materias_primas ORDER BY nome_mp ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}