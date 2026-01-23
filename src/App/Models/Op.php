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
                INSERT INTO ops (op, entrada, oleo, saida, desperdicio, cor, reacerto, obs, retem, responsavel_usuario_id)
                VALUES (:op, :entrada, :oleo, :saida, :desperdicio, :cor, :reacerto, :obs, :retem, :resp)
            ");
            return $st->execute([
                ':op' => (string)$data['op'],
                ':entrada' => $data['entrada'] ?? null,
                ':oleo' => $data['oleo'] ?? null,
                ':saida' => $data['saida'] ?? null,
                ':desperdicio' => $data['desperdicio'] ?? null,
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
            SELECT o.id, o.op, o.entrada, o.oleo, o.saida, o.desperdicio, o.cor, o.reacerto, o.obs, o.retem, o.criado_em,
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

    /**
     * Retorna estatísticas por hora para uma data (YYYY-MM-DD).
     * Saída: array[0..23] com ['hour'=>int,'ops'=>int,'kg'=>float]
     */
    public function statsByHour(string $date): array
    {
        $date = trim($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return array_map(fn($h) => ['hour' => $h, 'ops' => 0, 'kg' => 0.0], range(0, 23));
        }

        // SUM: tenta converter "saida" para número (aceita vírgula como decimal). Valores inválidos viram 0.
        $sql = "
            SELECT HOUR(criado_em) AS hr,
                   COUNT(*) AS ops,
                   COALESCE(SUM(CAST(NULLIF(REPLACE(saida, ',', '.'), '') AS DECIMAL(12,3))), 0) AS kg
            FROM ops
            WHERE DATE(criado_em) = :d
            GROUP BY hr
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([':d' => $date]);
        $rows = $st->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            $h = (int)$r['hr'];
            $map[$h] = ['hour' => $h, 'ops' => (int)$r['ops'], 'kg' => (float)$r['kg']];
        }

        $out = [];
        for ($h = 0; $h <= 23; $h++) {
            $out[] = $map[$h] ?? ['hour' => $h, 'ops' => 0, 'kg' => 0.0];
        }
        return $out;
    }
}

