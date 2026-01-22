<?php
// src/views/auth/reset-senha.php

$erros = $erros ?? [];
$sucesso = $sucesso ?? false;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4" style="width: 400px;">
        <h3 class="text-center mb-4">Trocar Senha (Primeiro Acesso)</h3>
        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <?= implode('<br>', $erros) ?>
            </div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success">Senha atualizada! Redirecionando...</div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirme Senha</label>
                <input type="password" name="confirm" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Nova Senha</button>
        </form>
    </div>
</body>
</html>