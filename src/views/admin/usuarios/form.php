<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container py-4">
    <h1><?= isset($usuario) ? 'Editar Usuário' : 'Criar Novo Usuário' ?></h1>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($erros as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label class="form-label">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
            <div class="invalid-feedback">Informe o nome completo.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
            <div class="invalid-feedback">Email válido é obrigatório.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">
                <?= isset($usuario) ? 'Nova Senha (deixe em branco para manter atual)' : 'Senha <span class="text-danger">*</span>' ?>
            </label>
            <input type="password" name="senha" class="form-control" <?= isset($usuario) ? '' : 'required' ?> minlength="6">
            <div class="invalid-feedback">Mínimo 6 caracteres.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Nível de Acesso <span class="text-danger">*</span></label>
            <select name="nivel_acesso" class="form-select" required>
                <option value="editor" <?= ($usuario['nivel_acesso'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                <option value="editorpro" <?= ($usuario['nivel_acesso'] ?? '') === 'editorpro' ? 'selected' : '' ?>>EditorPro</option>
            </select>
            <small class="form-text text-muted">Admin só via banco.</small>
        </div>

        <?php if (isset($usuario)): ?>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="1" <?= $usuario['status'] ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= !$usuario['status'] ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="<?= htmlspecialchars(url('/admin/usuarios')) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validação
(function () {
    'use strict'
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault()
                e.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>