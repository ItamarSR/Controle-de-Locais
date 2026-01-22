<?php
// config/database.php
// Conexão PDO — prefere variáveis de ambiente, mantém fallback para compatibilidade local.

$__DB_FALLBACKS = [
    'host' => 'localhost',
    'name' => 'sistemas_almox',            // nome do banco padrão (corrigido)
    'user' => 'sistemas_master',    // fallback (preservado)
    'pass' => '3yOQZ;v2j5O8.e',     // fallback (preservado)
    'charset' => 'utf8mb4',
];

$host    = getenv('DB_HOST') ?: $__DB_FALLBACKS['host'];
$dbname  = getenv('DB_NAME') ?: $__DB_FALLBACKS['name'];
$user    = getenv('DB_USER') ?: $__DB_FALLBACKS['user'];
$pass    = getenv('DB_PASS') ?: $__DB_FALLBACKS['pass'];
$charset = $__DB_FALLBACKS['charset'];

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Em ambiente de produção registre o erro e retorne uma mensagem genérica.
    throw new RuntimeException('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

/**
 * Retorna a instância PDO do projeto
 */
function getConnection(): PDO
{
    global $pdo;
    return $pdo;
}
