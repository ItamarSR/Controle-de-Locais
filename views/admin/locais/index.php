<?php
$title = 'Locais';
$locais = $locais ?? [];
$q = (string)($q ?? '');
?>

<div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mb-3">
  <div>
    <h1 class="h4 mb-1">Locais</h1>
    <div class="text-secondary small">Cadastre, edite e remova locais.</div>
  </div>
  <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
    <form method="get" action="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>" class="d-flex gap-2">
      <input class="form-control form-control-sm upper" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Filtro: código ou descrição">
      <button class="btn btn-outline-secondary btn-sm fw-bold" type="submit">Filtrar</button>
      <?php if ($q !== ''): ?>
        <a class="btn btn-outline-secondary btn-sm fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>">Limpar</a>
      <?php endif; ?>
    </form>
    <a class="btn btn-primary btn-sm fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais/novo')) ?>">+ Novo local</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if (!$locais): ?>
      <div class="p-4">Nenhum local cadastrado.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Código</th>
              <th>Local</th>
              <th>Descrição</th>
              <th>Data</th>
              <th>Responsável</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($locais as $l): ?>
              <tr>
                <td><span class="badge text-bg-light border"><?= htmlspecialchars($l['codigo_mp']) ?></span></td>
                <td class="fw-semibold"><?= htmlspecialchars($l['nome_local']) ?></td>
                <td class="text-secondary"><?= htmlspecialchars($l['nome_mp']) ?></td>
                <td class="text-secondary small"><?= htmlspecialchars(isset($l['data_cadastro']) ? date('d/m/Y H:i', strtotime((string)$l['data_cadastro'])) : '') ?></td>
                <td class="text-secondary small"><?= htmlspecialchars((string)($l['responsavel_nome'] ?? '')) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais/' . $l['id'] . '/editar')) ?>">Editar</a>
                  <form class="d-inline" method="post" action="<?= htmlspecialchars(\Core\Http::url('/admin/locais/' . $l['id'] . '/excluir')) ?>" onsubmit="return confirm('Excluir este local?');">
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

