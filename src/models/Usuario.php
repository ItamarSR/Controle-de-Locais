<?php
// src/models/Usuario.php

require_once __DIR__ . '/../../config/database.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listarTodos(): array {
        $stmt = $this->pdo->query("SELECT id, nome, email, nivel_acesso, primeiro_acesso, status FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar(array $dados): bool {
        if ($dados['nivel_acesso'] === 'admin') return false; // Bloqueio de novo admin
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, primeiro_acesso, status) VALUES (:nome, :email, :senha, :nivel, 1, 1)");
        return $stmt->execute([
            ':nome' => trim($dados['nome']),
            ':email' => trim($dados['email']),
            ':senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':nivel' => $dados['nivel_acesso']
        ]);
    }

    public function atualizar(int $id, array $dados): bool {
        if (isset($dados['nivel_acesso']) && $dados['nivel_acesso'] === 'admin') return false;
        if ($id === 1 && isset($dados['nivel_acesso']) && $dados['nivel_acesso'] !== 'admin') return false;

        $sets = [];
        $params = [':id' => $id];

        if (!empty($dados['nome'])) {
            $sets[] = "nome = :nome";
            $params[':nome'] = trim($dados['nome']);
        }
        if (!empty($dados['email'])) {
            $sets[] = "email = :email";
            $params[':email'] = trim($dados['email']);
        }
        if (!empty($dados['senha'])) {
            $sets[] = "senha = :senha";
            $params[':senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }
        if (isset($dados['nivel_acesso'])) {
            $sets[] = "nivel_acesso = :nivel";
            $params[':nivel'] = $dados['nivel_acesso'];
        }
        if (isset($dados['status'])) {
            $sets[] = "status = :status";
            $params[':status'] = (int)$dados['status'];
        }

        if (empty($sets)) return false;

        $sql = "UPDATE usuarios SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluir(int $id): bool {
        if ($id === 1) return false;
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function podeGerenciarUsuarios(int $userId): bool {
        $user = $this->buscarPorId($userId);
        return $user && in_array($user['nivel_acesso'], ['editorpro', 'admin']);
    }

    public function atualizarPrimeiroAcesso(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET primeiro_acesso = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}