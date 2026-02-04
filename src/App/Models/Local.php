<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDOException;
use PDO;

final class Local
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    /**
     * Lista pública com filtros opcionais (código e/ou local).
     * Importante: usa LIMIT para evitar respostas gigantes em bases grandes.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listPublicFiltered(?string $codigo = null, ?string $local = null, int $limit = 2000): array
    {
        $codigo = $codigo !== null ? trim($codigo) : null;
        $local = $local !== null ? trim($local) : null;
        if ($codigo === '') $codigo = null;
        if ($local === '') $local = null;

        if ($limit < 1) $limit = 1;
        if ($limit > 5000) $limit = 5000;

        $where = [];
        $binds = [];
        if ($codigo !== null) {
            $where[] = 'mp.codigo_mp LIKE :codigo';
            $binds[':codigo'] = '%' . $codigo . '%';
        }
        if ($local !== null) {
            $where[] = 'l.nome_local LIKE :local';
            $binds[':local'] = '%' . $local . '%';
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        try {
            $sql = "
                SELECT l.id,
                       l.nome_local,
                       l.data_cadastro,
                       mp.codigo_mp,
                       mp.nome_mp,
                       u.nome AS responsavel_nome
                FROM locais l
                JOIN materias_primas mp ON mp.id = l.mp_id
                LEFT JOIN usuarios u ON u.id = l.responsavel_usuario_id
                $whereSql
                ORDER BY l.nome_local ASC, l.data_cadastro DESC, l.id DESC
                LIMIT :lim
            ";
            $st = $this->pdo->prepare($sql);
            foreach ($binds as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll();
        } catch (PDOException $e) {
            // Compatibilidade: base antiga sem coluna responsavel_usuario_id
            $sql = "
                SELECT l.id,
                       l.nome_local,
                       l.data_cadastro,
                       mp.codigo_mp,
                       mp.nome_mp
                FROM locais l
                JOIN materias_primas mp ON mp.id = l.mp_id
                $whereSql
                ORDER BY l.nome_local ASC, l.data_cadastro DESC, l.id DESC
                LIMIT :lim
            ";
            $st = $this->pdo->prepare($sql);
            foreach ($binds as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll();
        }
    }

    public function listAdmin(?string $q = null): array
    {
        $q = $q !== null ? trim($q) : null;
        $hasFilter = $q !== null && $q !== '';
        try {
            if ($hasFilter) {
                $st = $this->pdo->prepare("
                    SELECT l.id,
                           l.nome_local,
                           l.mp_id,
                           l.data_cadastro,
                           mp.codigo_mp,
                           mp.nome_mp,
                           u.nome AS responsavel_nome
                    FROM locais l
                    JOIN materias_primas mp ON mp.id = l.mp_id
                    LEFT JOIN usuarios u ON u.id = l.responsavel_usuario_id
                    WHERE (mp.codigo_mp LIKE :q1 OR mp.nome_mp LIKE :q2)
                    ORDER BY l.nome_local ASC
                ");
                $like = '%' . $q . '%';
                $st->execute([':q1' => $like, ':q2' => $like]);
                return $st->fetchAll();
            }

            $st = $this->pdo->query("
                SELECT l.id,
                       l.nome_local,
                       l.mp_id,
                       l.data_cadastro,
                       mp.codigo_mp,
                       mp.nome_mp,
                       u.nome AS responsavel_nome
                FROM locais l
                JOIN materias_primas mp ON mp.id = l.mp_id
                LEFT JOIN usuarios u ON u.id = l.responsavel_usuario_id
                ORDER BY l.nome_local ASC
            ");
            return $st->fetchAll();
        } catch (PDOException $e) {
            // Compatibilidade: base antiga sem coluna responsavel_usuario_id
            if ($hasFilter) {
                $st = $this->pdo->prepare("
                    SELECT l.id, l.nome_local, l.mp_id, l.data_cadastro, mp.codigo_mp, mp.nome_mp
                    FROM locais l
                    JOIN materias_primas mp ON mp.id = l.mp_id
                    WHERE (mp.codigo_mp LIKE :q1 OR mp.nome_mp LIKE :q2)
                    ORDER BY l.nome_local ASC
                ");
                $like = '%' . $q . '%';
                $st->execute([':q1' => $like, ':q2' => $like]);
                return $st->fetchAll();
            }

            $st = $this->pdo->query("
                    SELECT l.id, l.nome_local, l.mp_id, l.data_cadastro, mp.codigo_mp, mp.nome_mp
                    FROM locais l
                    JOIN materias_primas mp ON mp.id = l.mp_id
                    ORDER BY l.nome_local ASC
                ");
            return $st->fetchAll();
        }
    }

    /**
     * Retorna contagens por nome_local para uma lista de locais.
     * Saída: ['3A' => 2, '3B' => 0, ...]
     */
    public function countsByNomeLocal(array $nomes): array
    {
        $nomes = array_values(array_filter(array_map('strval', $nomes), fn($s) => trim($s) !== ''));
        if (!$nomes) return [];

        $placeholders = implode(',', array_fill(0, count($nomes), '?'));
        $st = $this->pdo->prepare("
            SELECT nome_local, COUNT(*) AS c
            FROM locais
            WHERE nome_local IN ($placeholders)
            GROUP BY nome_local
        ");
        $st->execute($nomes);
        $rows = $st->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[(string)$r['nome_local']] = (int)$r['c'];
        }
        return $out;
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

    public function consultaPublicaPorCodigo(string $codigo): array
    {
        $codigo = trim($codigo);
        if ($codigo === '') return [];

        try {
            $st = $this->pdo->prepare("
                SELECT l.id,
                       l.nome_local,
                       l.data_cadastro,
                       mp.codigo_mp,
                       mp.nome_mp,
                       u.nome AS responsavel_nome
                FROM locais l
                JOIN materias_primas mp ON mp.id = l.mp_id
                LEFT JOIN usuarios u ON u.id = l.responsavel_usuario_id
                WHERE mp.codigo_mp = :c
                ORDER BY l.data_cadastro DESC
            ");
            $st->execute([':c' => $codigo]);
            return $st->fetchAll();
        } catch (PDOException $e) {
            // Compatibilidade: base antiga sem coluna responsavel_usuario_id
            $st = $this->pdo->prepare("
                SELECT l.id,
                       l.nome_local,
                       l.data_cadastro,
                       mp.codigo_mp,
                       mp.nome_mp
                FROM locais l
                JOIN materias_primas mp ON mp.id = l.mp_id
                WHERE mp.codigo_mp = :c
                ORDER BY l.data_cadastro DESC
            ");
            $st->execute([':c' => $codigo]);
            return $st->fetchAll();
        }
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM locais WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function findForForm(int $id): ?array
    {
        $st = $this->pdo->prepare("
            SELECT l.id, l.nome_local, l.mp_id, l.data_cadastro, mp.codigo_mp, mp.nome_mp
            FROM locais l
            JOIN materias_primas mp ON mp.id = l.mp_id
            WHERE l.id = :id
            LIMIT 1
        ");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function listByMpId(int $mpId): array
    {
        $st = $this->pdo->prepare("
            SELECT id, nome_local, data_cadastro
            FROM locais
            WHERE mp_id = :mp
            ORDER BY data_cadastro DESC
        ");
        $st->execute([':mp' => $mpId]);
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

    public function create(string $nomeLocal, int $mpId, ?int $responsavelUsuarioId = null): bool
    {
        $nomeLocal = trim($nomeLocal);
        if ($nomeLocal === '' || $mpId <= 0) return false;

        try {
            $st = $this->pdo->prepare("INSERT INTO locais (nome_local, mp_id, responsavel_usuario_id) VALUES (:nome, :mp, :resp)");
            return $st->execute([':nome' => $nomeLocal, ':mp' => $mpId, ':resp' => $responsavelUsuarioId]);
        } catch (PDOException $e) {
            // Compatibilidade: base antiga sem coluna responsavel_usuario_id
            $st = $this->pdo->prepare("INSERT INTO locais (nome_local, mp_id) VALUES (:nome, :mp)");
            return $st->execute([':nome' => $nomeLocal, ':mp' => $mpId]);
        }
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

