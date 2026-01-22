<?php
// src/views/admin/dashboard.php

require __DIR__ . '/../partials/header.php';

$usuarioModel = new \Usuario();
$usuario = $usuarioModel->buscarPorId($_SESSION['user_id']);
?>

<div class="container py-4">
    <h1>Bem-vindo, <?= htmlspecialchars($usuario['nome']) ?>!</h1>
    <p>Nível de acesso: <?= strtoupper($usuario['nivel_acesso']) ?></p>
    <div class="list-group">
        <a href="<?= htmlspecialchars(url('/admin/locais')) ?>" class="list-group-item list-group-item-action">Gerenciar Locais</a>
        <?php if (in_array($usuario['nivel_acesso'], ['editorpro', 'admin'])): ?>
            <a href="<?= htmlspecialchars(url('/admin/usuarios')) ?>" class="list-group-item list-group-item-action">Gerenciar Usuários</a>
        <?php endif; ?>
        <a href="<?= htmlspecialchars(url('/admin/import-excel')) ?>" class="list-group-item list-group-item-action">Importar Excel (MPs)</a>
        <a href="<?= htmlspecialchars(url('/logout')) ?>" class="list-group-item list-group-item-action text-danger">Logout</a>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>