<?php
// sql/seed_admin.php
// Use: php sql/seed_admin.php [--email=admin@example.com] [--password=trocar123] [--name="Administrador Inicial"] [--force]

$opts = getopt('', ['email::', 'password::', 'name::', 'force']);
$email = $opts['email'] ?? 'admin@example.com';
$password = $opts['password'] ?? 'trocar123';
$name = $opts['name'] ?? 'Administrador Inicial';
$force = isset($opts['force']);

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getConnection();
} catch (Throwable $e) {
    fwrite(STDERR, "Erro ao conectar ao banco: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

// Verifica se já existe um admin
$stmt = $pdo->prepare("SELECT id, email FROM usuarios WHERE nivel_acesso = 'admin' LIMIT 1");
$stmt->execute();
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existing && !$force) {
    echo "Já existe um usuário admin (id={$existing['id']}, email={$existing['email']}). Use --force para forçar.\n";
    exit(0);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($existing && $force) {
    $upd = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, primeiro_acesso = 1, status = 1 WHERE id = :id");
    $ok = $upd->execute([':nome' => $name, ':email' => $email, ':senha' => $hash, ':id' => $existing['id']]);
    if ($ok) {
        echo "Admin existente atualizado (id={$existing['id']}). Senha temporária: {$password}\n";
        echo "SQL (hash): INSERT INTO usuarios (nome,email,senha,nivel_acesso,primeiro_acesso,status) VALUES ('{$name}','{$email}','{$hash}','admin',1,1);\n";
        exit(0);
    }
    fwrite(STDERR, "Falha ao atualizar admin existente.\n");
    exit(1);
}

$ins = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, primeiro_acesso, status) VALUES (:nome, :email, :senha, 'admin', 1, 1)");
$ok = $ins->execute([':nome' => $name, ':email' => $email, ':senha' => $hash]);
if ($ok) {
    echo "Admin criado com sucesso. Email: {$email} / Senha temporária: {$password}\n";
    echo "Por segurança, faça login e troque a senha imediatamente.\n";
    echo "SQL (hash): INSERT INTO usuarios (nome,email,senha,nivel_acesso,primeiro_acesso,status) VALUES ('{$name}','{$email}','{$hash}','admin',1,1);\n";
    exit(0);
}

fwrite(STDERR, "Falha ao inserir admin.\n");
exit(1);
