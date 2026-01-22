<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<h2><?= isset($usuario) ? 'Editar Usuário' : 'Novo Usuário' ?></h2>

<?php if (!empty($erros)): ?>
    <div class="alert danger">
        <ul><?php foreach ($erros as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="alert success">Usuário salvo com sucesso!</div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Nome *</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label><?= isset($usuario) ? 'Nova Senha (deixe em branco para não alterar)' : 'Senha *' ?></label>
        <input type="password" name="senha" <?= isset($usuario) ? '' : 'required' ?> minlength="6">
    </div>

    <div class="form-group">
        <label>Nível de Acesso *</label>
        <select name="nivel_acesso" required>
            <option value="editor"   <?= (isset($usuario) && $usuario['nivel_acesso'] === 'editor')   ? 'selected' : '' ?>>Editor</option>
            <option value="editorpro"<?= (isset($usuario) && $usuario['nivel_acesso'] === 'editorpro')? 'selected' : '' ?>>EditorPro</option>
        </select>
        <small>(Não é possível criar ou alterar para Admin via interface)</small>
    </div>

    <?php if (isset($usuario)): ?>
    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="1" <?= $usuario['status'] ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= !$usuario['status'] ? 'selected' : '' ?>>Inativo</option>
        </select>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="<?= htmlspecialchars(url('/admin/usuarios')) ?>" class="btn btn-secondary">Voltar</a>
</form>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>