<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDO;
use PDOException;

final class Op
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    public function countByOp(string $op): int
    {
        $op = trim($op);
        if ($op === '') return 0;
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM ops WHERE op = :op");
        $st->execute([':op' => $op]);
        return (int)$st->fetchColumn();
    }

    public function insert(array $data): bool
    {
        try {
            $st = $this->pdo->prepare("
                INSERT INTO ops (op, entrada, oleo, saida, cor, reacerto, obs, retem, responsavel_usuario_id)
                VALUES (:op, :entrada, :oleo, :saida, :cor, :reacerto, :obs, :retem, :resp)
            ");
            return $st->execute([
                ':op' => (string)$data['op'],
                ':entrada' => $data['entrada'] ?? null,
                ':oleo' => $data['oleo'] ?? null,
                ':saida' => $data['saida'] ?? null,
                ':cor' => $data['cor'] ?? null,
                ':reacerto' => (int)($data['reacerto'] ?? 0),
                ':obs' => $data['obs'] ?? null,
                ':retem' => (int)($data['retem'] ?? 0),
                ':resp' => $data['responsavel_usuario_id'] ?? null,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function search(?string $op = null): array
    {
        $op = $op !== null ? trim($op) : null;
        $params = [];
        $where = [];

        if ($op !== null && $op !== '') {
            $where[] = "o.op LIKE :op";
            $params[':op'] = '%' . $op . '%';
        }

        $sql = "
            SELECT o.id, o.op, o.entrada, o.oleo, o.saida, o.cor, o.reacerto, o.obs, o.retem, o.criado_em,
                   u.nome AS responsavel_nome
            FROM ops o
            LEFT JOIN usuarios u ON u.id = o.responsavel_usuario_id
        ";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY o.criado_em DESC, o.id DESC LIMIT 500";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}

