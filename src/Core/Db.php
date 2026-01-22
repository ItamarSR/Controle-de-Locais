<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = Env::getString('DB_HOST', 'localhost');
        $name = Env::getString('DB_NAME', 'sistemas_almox');
        // Mantém compatibilidade com credenciais legadas (se não houver env vars).
        $user = Env::getString('DB_USER', 'sistemas_master');
        $pass = Env::getString('DB_PASS', '3yOQZ;v2j5O8.e');
        $charset = Env::getString('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$pdo = new PDO($dsn, $user, $pass, $opts);
            return self::$pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Falha ao conectar ao banco. Verifique DB_HOST/DB_NAME/DB_USER/DB_PASS.');
        }
    }
}

