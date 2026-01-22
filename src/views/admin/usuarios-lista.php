<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<h2>Gerenciamento de Usuários</h2>

<a href="/admin/usuarios/criar" class="btn btn-primary">+ Novo Usuário</a>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
    <div class="alert success">Usuário excluído com sucesso!</div>
<?php endif; ?>
<?php if (isset($_GET['erro'])): ?>
    <div class="alert danger">
        <?= $_GET['erro'] === 'admin_protegido' ? 'Não é permitido excluir o administrador inicial.' : 'Erro ao excluir usuário.' ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Nível</th>
            <th>Primeiro Acesso</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nome']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= strtoupper($u['nivel_acesso']) ?></td>
            <td><?= $u['primeiro_acesso'] ? 'Sim' : 'Não' ?></td>
            <td><?= $u['status'] ? 'Ativo' : 'Inativo' ?></td>
            <td>
                <a href="/admin/usuarios/editar/<?= $u['id'] ?>" class="btn btn-small">Editar</a>
                <?php if ($u['id'] != 1): ?>
                <form action="/admin/usuarios/excluir/<?= $u['id'] ?>" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Confirma exclusão permanente?')">Excluir</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($usuarios)): ?>
            <tr><td colspan="7">Nenhum usuário cadastrado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>