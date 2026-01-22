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
}

