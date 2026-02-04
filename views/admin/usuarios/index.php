<?php
$title = 'Usuários';
$usuarios = $usuarios ?? [];
?>

<div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
  <div>
    <h1 class="h4 mb-1">Usuários</h1>
    <div class="text-secondary small">Criar e gerenciar usuários (Editor/EditorPro). Admin é protegido.</div>
  </div>
  <a class="btn btn-primary" href="<?= htmlspecialchars(\Core\Http::url('/admin/usuarios/novo')) ?>">+ Novo usuário</a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if (!$usuarios): ?>
      <div class="p-4">Nenhum usuário cadastrado.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Email</th>
              <th>Nível</th>
              <th>Status</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge text-bg-light border"><?= htmlspecialchars(strtoupper($u['nivel_acesso'])) ?></span></td>
                <td><?= ((int)$u['status'] === 1) ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(\Core\Http::url('/admin/usuarios/' . $u['id'] . '/editar')) ?>">Editar</a>
                  <form class="d-inline" method="post" action="<?= htmlspecialchars(\Core\Http::url('/admin/usuarios/' . $u['id'] . '/excluir')) ?>" onsubmit="return confirm('Excluir este usuário?');">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

