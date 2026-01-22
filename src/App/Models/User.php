<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDO;

final class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $st->execute([':email' => $email]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function hasAnyAdmin(): bool
    {
        $st = $this->pdo->query("SELECT 1 FROM usuarios WHERE nivel_acesso = 'admin' LIMIT 1");
        return (bool)$st->fetchColumn();
    }

    public function createInitialAdmin(string $nome, string $email, string $senha): bool
    {
        if ($this->hasAnyAdmin()) return false;

        $st = $this->pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, primeiro_acesso, status)
            VALUES (:nome, :email, :senha, 'admin', 1, 1)
        ");

        return $st->execute([
            ':nome' => trim($nome),
            ':email' => trim($email),
            ':senha' => password_hash($senha, PASSWORD_DEFAULT),
        ]);
    }

    public function listAll(): array
    {
        $st = $this->pdo->query("SELECT id, nome, email, nivel_acesso, primeiro_acesso, status FROM usuarios ORDER BY nome ASC");
        return $st->fetchAll();
    }

    public function create(array $data): bool
    {
        $nivel = (string)($data['nivel_acesso'] ?? 'editor');
        if ($nivel === 'admin') return false; // admin só via seed

        $st = $this->pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, primeiro_acesso, status)
            VALUES (:nome, :email, :senha, :nivel, 1, 1)
        ");

        return $st->execute([
            ':nome' => trim((string)$data['nome']),
            ':email' => trim((string)$data['email']),
            ':senha' => password_hash((string)$data['senha'], PASSWORD_DEFAULT),
            ':nivel' => $nivel,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nome'])) {
            $fields[] = 'nome = :nome';
            $params[':nome'] = trim((string)$data['nome']);
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = trim((string)$data['email']);
        }
        if (!empty($data['senha'])) {
            $fields[] = 'senha = :senha';
            $params[':senha'] = password_hash((string)$data['senha'], PASSWORD_DEFAULT);
        }
        if (isset($data['nivel_acesso'])) {
            $nivel = (string)$data['nivel_acesso'];
            if ($nivel === 'admin') return false;
            $fields[] = 'nivel_acesso = :nivel';
            $params[':nivel'] = $nivel;
        }
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params[':status'] = (int)$data['status'];
        }
        if (isset($data['primeiro_acesso'])) {
            $fields[] = 'primeiro_acesso = :pa';
            $params[':pa'] = (int)$data['primeiro_acesso'];
        }

        if (!$fields) return false;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $st = $this->pdo->prepare($sql);
        return $st->execute($params);
    }

    public function delete(int $id): bool
    {
        // Protege o admin inicial (primeiro admin por menor id)
        $st = $this->pdo->query("SELECT id FROM usuarios WHERE nivel_acesso = 'admin' ORDER BY id ASC LIMIT 1");
        $adminId = (int)($st->fetchColumn() ?: 0);
        if ($id === $adminId) return false;

        $del = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        return $del->execute([':id' => $id]);
    }
}

