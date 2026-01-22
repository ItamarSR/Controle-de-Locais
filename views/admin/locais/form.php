<?php
$title = $title ?? 'Local';
$local = $local ?? null;
$mps = $mps ?? [];

$action = $local
  ? \Core\Http::url('/admin/locais/' . $local['id'] . '/editar')
  : \Core\Http::url('/admin/locais/novo');
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1"><?= htmlspecialchars($local ? 'Editar local' : 'Novo local') ?></h1>
    <div class="text-secondary small">Selecione uma MP já importada.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>">Voltar</a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="needs-validation" novalidate>
      <div class="mb-3">
        <label class="form-label">Nome do local</label>
        <input class="form-control" name="nome_local" required maxlength="255" value="<?= htmlspecialchars($local['nome_local'] ?? '') ?>">
        <div class="invalid-feedback">Informe o nome do local.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Matéria-prima (MP)</label>
        <select class="form-select" name="mp_id" required>
          <option value="">Selecione...</option>
          <?php foreach ($mps as $mp): ?>
            <option value="<?= (int)$mp['id'] ?>" <?= ((int)($local['mp_id'] ?? 0) === (int)$mp['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($mp['codigo_mp']) ?> — <?= htmlspecialchars($mp['nome_mp']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">Selecione uma MP.</div>
        <?php if (!$mps): ?>
          <div class="form-text text-danger">Nenhuma MP encontrada. Faça a importação em “Importar MPs”.</div>
        <?php endif; ?>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>">Cancelar</a>
      </div>
    </form>
  </div>
</div>

