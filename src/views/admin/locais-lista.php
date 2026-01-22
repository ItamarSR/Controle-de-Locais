<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<h2>Gerenciamento de Locais</h2>

<a href="<?= htmlspecialchars(url('/admin/locais/criar')) ?>" class="btn btn-primary">+ Novo Local</a>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
    <div class="alert success">Local excluído com sucesso!</div>
<?php endif; ?>
<?php if (isset($_GET['erro'])): ?>
    <div class="alert danger">Erro ao excluir local.</div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome do Local</th>
            <th>MP (Código - Nome)</th>
            <th>Data Cadastro</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($locais as $local): ?>
        <tr>
            <td><?= $local['id'] ?></td>
            <td><?= htmlspecialchars($local['nome_local']) ?></td>
            <td><?= htmlspecialchars($local['codigo_mp']) ?> - <?= htmlspecialchars($local['nome_mp']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($local['data_cadastro'])) ?></td>
            <td>
                <a href="<?= htmlspecialchars(url('/admin/locais/editar/' . $local['id'])) ?>" class="btn btn-small">Editar</a>
                <form action="<?= htmlspecialchars(url('/admin/locais/excluir/' . $local['id'])) ?>" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Confirma exclusão?')">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($locais)): ?>
            <tr><td colspan="5">Nenhum local cadastrado ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>