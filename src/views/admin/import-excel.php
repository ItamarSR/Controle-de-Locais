<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container py-4">
    <h1>Importar Dados de Excel (Matérias-Primas)</h1>

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
        <div class="alert alert-success">Importação realizada com sucesso!</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Arquivo para importação (.csv, .xls, .xlsx)</label>
            <input type="file" name="excel" class="form-control" required accept=".csv,.xls,.xlsx">
            <small class="form-text text-muted">Preferência: CSV (fallback sem dependências). Importa colunas A (código) e B (nome) a partir da linha 7. Para XLS/XLSX o servidor precisa da biblioteca PhpSpreadsheet.</small>
        </div>
        <button type="submit" class="btn btn-primary">Importar</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>