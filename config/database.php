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
    // Não derrube o sistema no require/boot (permite renderizar uma página de erro amigável).
    // Detalhes devem ser registrados em produção; aqui mantemos detalhe disponível para CLI/debug.
    $pdo = null;
    $__db_error = 'Não foi possível conectar ao banco de dados. Verifique DB_HOST/DB_NAME/DB_USER/DB_PASS e se o MySQL está acessível.';
    $__db_error_detail = $e->getMessage();
}

/**
 * Retorna a instância PDO do projeto
 */
function getConnection(): PDO
{
    global $pdo;
    global $__db_error;

    if (!$pdo instanceof PDO) {
        throw new RuntimeException($__db_error ?: 'Banco de dados indisponível.');
    }
    return $pdo;
}

/**
 * Detalhe do erro de conexão (uso em CLI/debug).
 */
function getDbConnectionErrorDetail(): ?string
{
    global $__db_error_detail;
    return isset($__db_error_detail) ? (string)$__db_error_detail : null;
}
