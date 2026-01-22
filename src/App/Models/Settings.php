<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDO;
use PDOException;

final class Settings
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    /**
     * @return array<string,string>
     */
    public function all(): array
    {
        try {
            $st = $this->pdo->query("SELECT `chave`, `valor` FROM configuracoes");
            $rows = $st->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[(string)$r['chave']] = (string)$r['valor'];
            }
            return $out;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        try {
            $st = $this->pdo->prepare("SELECT `valor` FROM configuracoes WHERE `chave` = :k LIMIT 1");
            $st->execute([':k' => $key]);
            $v = $st->fetchColumn();
            return $v === false ? $default : (string)$v;
        } catch (PDOException $e) {
            return $default;
        }
    }

    public function set(string $key, string $value): bool
    {
        $key = trim($key);
        if ($key === '') return false;
        try {
            $st = $this->pdo->prepare("
                INSERT INTO configuracoes (`chave`, `valor`)
                VALUES (:k, :v)
                ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`)
            ");
            return $st->execute([':k' => $key, ':v' => $value]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

