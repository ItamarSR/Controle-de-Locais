<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Locais Cadastrados</h1>
        <a href="/admin/locais/criar" class="btn btn-success">+ Novo Local</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Local excluído com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Erro ao excluir o local.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($locais)): ?>
        <div class="alert alert-info text-center">
            Nenhum local cadastrado ainda.
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome do Local</th>
                    <th>Matéria-Prima</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($locais as $local): ?>
                <tr>
                    <td><?= htmlspecialchars($local['id']) ?></td>
                    <td><?= htmlspecialchars($local['nome_local']) ?></td>
                    <td>
                        <small class="text-muted"><?= htmlspecialchars($local['codigo_mp']) ?></small><br>
                        <?= htmlspecialchars($local['nome_mp']) ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($local['data_cadastro'])) ?></td>
                    <td>
                        <a href="/admin/locais/editar/<?= $local['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="/admin/locais/excluir/<?= $local['id'] ?>" method="POST" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Confirma exclusão permanente?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>