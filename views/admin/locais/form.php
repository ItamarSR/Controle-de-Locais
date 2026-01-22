<?php
$title = $title ?? 'Local';
$local = $local ?? null;
$codigo_mp = $codigo_mp ?? ($local['codigo_mp'] ?? '');
$descricao_mp = $descricao_mp ?? ($local['nome_mp'] ?? '');
$nome_local = $nome_local ?? ($local['nome_local'] ?? '');
$dup_locais = $dup_locais ?? [];
$responsavel = $responsavel ?? null;
$data_auto = $data_auto ?? date('d/m/Y H:i');

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
      <?php if (!empty($dup_locais)): ?>
        <div class="alert alert-warning">
          <div class="fw-semibold mb-1">Já existe um Local para esse Código.</div>
          <div class="small text-secondary mb-2">Locais já cadastrados para <?= htmlspecialchars($codigo_mp) ?>:</div>
          <ul class="mb-2">
            <?php foreach ($dup_locais as $d): ?>
              <li><?= htmlspecialchars($d['nome_local']) ?></li>
            <?php endforeach; ?>
          </ul>
          <div class="small">Se quiser, você pode cadastrar um novo local mesmo assim.</div>
        </div>
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label">Código</label>
          <input class="form-control" name="codigo_mp" id="codigo_mp" required maxlength="50" value="<?= htmlspecialchars($codigo_mp) ?>" placeholder="Ex: 12345">
          <div class="invalid-feedback">Informe o código.</div>
        </div>
        <div class="col-12 col-md-8">
          <label class="form-label">Descrição</label>
          <input class="form-control" name="descricao_mp" id="descricao_mp" value="<?= htmlspecialchars($descricao_mp) ?>" readonly>
          <div class="form-text">Preenchido automaticamente pela MP (nome_mp) a partir do Código.</div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label mt-3">Local</label>
        <input class="form-control" name="nome_local" id="nome_local" required maxlength="255" value="<?= htmlspecialchars($nome_local) ?>" placeholder="Ex: Corredor B – Prateleira 08">
        <div class="invalid-feedback">Informe o nome do local.</div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Data</label>
          <input class="form-control" value="<?= htmlspecialchars($data_auto) ?>" readonly>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Responsável</label>
          <input class="form-control" value="<?= htmlspecialchars((string)($responsavel['nome'] ?? '')) ?>" readonly>
        </div>
      </div>

      <div class="d-flex gap-2">
        <?php if (!empty($dup_locais) && !$local): ?>
          <input type="hidden" name="force" value="1">
          <button class="btn btn-warning" type="submit">Cadastrar novo Local mesmo assim</button>
          <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais/novo')) ?>">Voltar e alterar</a>
        <?php else: ?>
          <button class="btn btn-primary" type="submit">Salvar</button>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const codigo = document.getElementById('codigo_mp');
    const desc = document.getElementById('descricao_mp');
    let t = null;

    async function lookup() {
      const v = (codigo.value || '').trim();
      if (!v) { desc.value = ''; return; }
      try {
        const res = await fetch('<?= htmlspecialchars(\Core\Http::url('/admin/locais/api/codigo/')) ?>' + encodeURIComponent(v), { headers: { 'Accept': 'application/json' }});
        const data = await res.json();
        if (!data || !data.ok || !data.mp) { desc.value = ''; return; }
        desc.value = data.mp.nome_mp || '';
      } catch (e) {
        // silencioso
      }
    }

    codigo.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(lookup, 250);
    });

    // prefill
    lookup();
  })();
</script>

