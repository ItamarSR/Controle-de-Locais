<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<h2><?= isset($local) ? 'Editar Local' : 'Novo Local' ?></h2>

<?php if (!empty($erros)): ?>
    <div class="alert danger">
        <ul>
            <?php foreach ($erros as $erro): ?>
                <li><?= $erro ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="alert success">Local salvo com sucesso!</div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Nome do Local *</label>
        <input type="text" name="nome_local" required
               value="<?= htmlspecialchars($local['nome_local'] ?? '') ?>"
               placeholder="Ex: Corredor A - Prateleira 05">
    </div>

    <div class="form-group">
        <label>Matéria-Prima *</label>
        <select name="mp_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($mps as $mp): ?>
                <option value="<?= $mp['id'] ?>"
                    <?= (isset($local) && $local['mp_id'] == $mp['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mp['codigo_mp']) ?> - <?= htmlspecialchars($mp['nome_mp']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="<?= htmlspecialchars(url('/admin/locais')) ?>" class="btn btn-secondary">Cancelar</a>
</form>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>