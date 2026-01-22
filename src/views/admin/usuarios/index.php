<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerenciamento de Usuários</h1>
        <a href="<?= htmlspecialchars(url('/admin/usuarios/criar')) ?>" class="btn btn-success">+ Novo Usuário</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Usuário excluído com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_GET['erro'] === 'exclusao_negada' ? 'Não é permitido excluir o administrador inicial ou ocorreu erro.' : 'Erro na operação.' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível</th>
                    <th>Primeiro Acesso?</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><strong><?= strtoupper($u['nivel_acesso']) ?></strong></td>
                    <td><?= $u['primeiro_acesso'] ? '<span class="badge bg-warning">Sim</span>' : '<span class="badge bg-success">Não</span>' ?></td>
                    <td><?= $u['status'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>' ?></td>
                    <td>
                        <a href="<?= htmlspecialchars(url('/admin/usuarios/editar/' . $u['id'])) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <?php if ($u['id'] !== 1): ?>
                        <form action="<?= htmlspecialchars(url('/admin/usuarios/excluir/' . $u['id'])) ?>" method="POST" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir este usuário permanentemente?')">Excluir</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="7" class="text-center">Nenhum usuário cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>