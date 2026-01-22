<?php

declare(strict_types=1);

namespace App\Models;

use Core\Db;
use PDO;
use PDOException;

final class Settings
{
    private PDO $pdo;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->pdo = Db::pdo();
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function tableAvailable(): bool
    {
        $this->lastError = null;
        try {
            $this->pdo->query("SELECT 1 FROM configuracoes LIMIT 1");
            return true;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * @return array<string,string>
     */
    public function all(): array
    {
        $this->lastError = null;
        try {
            $st = $this->pdo->query("SELECT `chave`, `valor` FROM configuracoes");
            $rows = $st->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[(string)$r['chave']] = (string)$r['valor'];
            }
            return $out;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $this->lastError = null;
        try {
            $st = $this->pdo->prepare("SELECT `valor` FROM configuracoes WHERE `chave` = :k LIMIT 1");
            $st->execute([':k' => $key]);
            $v = $st->fetchColumn();
            return $v === false ? $default : (string)$v;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return $default;
        }
    }

    public function set(string $key, string $value): bool
    {
        $key = trim($key);
        if ($key === '') return false;
        $this->lastError = null;
        try {
            $st = $this->pdo->prepare("
                INSERT INTO configuracoes (`chave`, `valor`)
                VALUES (:k, :v)
                ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`)
            ");
            return $st->execute([':k' => $key, ':v' => $value]);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}

