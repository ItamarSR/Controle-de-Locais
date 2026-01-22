<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDO;

final class MateriaPrima
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    public function listAll(): array
    {
        $st = $this->pdo->query("SELECT id, codigo_mp, nome_mp FROM materias_primas ORDER BY nome_mp ASC");
        return $st->fetchAll();
    }

    public function findByCodigo(string $codigo): ?array
    {
        $codigo = trim($codigo);
        if ($codigo === '') return null;
        $st = $this->pdo->prepare("SELECT id, codigo_mp, nome_mp FROM materias_primas WHERE codigo_mp = :c LIMIT 1");
        $st->execute([':c' => $codigo]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function upsert(string $codigo, string $nome): bool
    {
        $codigo = trim($codigo);
        $nome = trim($nome);
        if ($codigo === '' || $nome === '') return false;

        $st = $this->pdo->prepare("
            INSERT INTO materias_primas (codigo_mp, nome_mp)
            VALUES (:codigo, :nome)
            ON DUPLICATE KEY UPDATE nome_mp = VALUES(nome_mp)
        ");
        return $st->execute([':codigo' => $codigo, ':nome' => $nome]);
    }
}

