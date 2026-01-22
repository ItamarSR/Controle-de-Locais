<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDO;

final class Local
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    public function listAdmin(): array
    {
        $st = $this->pdo->query("
            SELECT l.id, l.nome_local, l.mp_id, l.data_cadastro, mp.codigo_mp, mp.nome_mp
            FROM locais l
            JOIN materias_primas mp ON mp.id = l.mp_id
            ORDER BY l.nome_local ASC
        ");
        return $st->fetchAll();
    }

    public function listPublic(): array
    {
        $st = $this->pdo->query("
            SELECT l.id, l.nome_local, mp.codigo_mp, mp.nome_mp
            FROM locais l
            JOIN materias_primas mp ON mp.id = l.mp_id
            ORDER BY l.nome_local ASC
        ");
        return $st->fetchAll();
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM locais WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function findWithMp(int $id): ?array
    {
        $st = $this->pdo->prepare("
            SELECT l.id, l.nome_local, mp.codigo_mp, mp.nome_mp
            FROM locais l
            JOIN materias_primas mp ON mp.id = l.mp_id
            WHERE l.id = :id
            LIMIT 1
        ");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function create(string $nomeLocal, int $mpId): bool
    {
        $nomeLocal = trim($nomeLocal);
        if ($nomeLocal === '' || $mpId <= 0) return false;

        $st = $this->pdo->prepare("INSERT INTO locais (nome_local, mp_id) VALUES (:nome, :mp)");
        return $st->execute([':nome' => $nomeLocal, ':mp' => $mpId]);
    }

    public function update(int $id, string $nomeLocal, int $mpId): bool
    {
        $nomeLocal = trim($nomeLocal);
        if ($id <= 0 || $nomeLocal === '' || $mpId <= 0) return false;

        $st = $this->pdo->prepare("UPDATE locais SET nome_local = :nome, mp_id = :mp WHERE id = :id");
        return $st->execute([':id' => $id, ':nome' => $nomeLocal, ':mp' => $mpId]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM locais WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}

