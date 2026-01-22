<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container py-4">
    <h1><?= isset($local) ? 'Editar Local' : 'Cadastrar Novo Local' ?></h1>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="nome_local" class="form-label">Nome do Local <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nome_local" name="nome_local" 
                   value="<?= htmlspecialchars($local['nome_local'] ?? '') ?>" 
                   required placeholder="Ex: Corredor B – Prateleira 08" maxlength="255">
            <div class="invalid-feedback">Informe o nome do local.</div>
        </div>

        <div class="mb-3">
            <label for="mp_id" class="form-label">Matéria-Prima <span class="text-danger">*</span></label>
            <select class="form-select" id="mp_id" name="mp_id" required>
                <option value="">Selecione uma matéria-prima...</option>
                <?php foreach ($mps as $mp): ?>
                    <option value="<?= $mp['id'] ?>" 
                        <?= (isset($local) && $local['mp_id'] == $mp['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mp['codigo_mp']) ?> – <?= htmlspecialchars($mp['nome_mp']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Selecione uma matéria-prima válida.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="/admin/locais" class="btn btn-outline-secondary">Cancelar</a>
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