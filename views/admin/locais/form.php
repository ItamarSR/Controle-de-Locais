<?php
$title = $title ?? 'Local';
$local = $local ?? null;
$codigo_mp = $codigo_mp ?? ($local['codigo_mp'] ?? '');
$descricao_mp = $descricao_mp ?? ($local['nome_mp'] ?? '');
$nome_local = $nome_local ?? ($local['nome_local'] ?? '');
$dup_locais = $dup_locais ?? [];
$slots = $slots ?? [];
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

<div class="mx-auto" style="max-width: 1100px;">
<div class="card shadow-sm">
  <div class="card-body p-3 p-md-4">
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

      <div class="row g-2">
        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">Código</label>
          <input class="form-control form-control-sm" name="codigo_mp" id="codigo_mp" required maxlength="50" value="<?= htmlspecialchars($codigo_mp) ?>" placeholder="Ex: 12345">
          <div class="invalid-feedback">Informe o código.</div>
        </div>
        <div class="col-12 col-md-8 col-lg-6">
          <label class="form-label fw-bold small mb-1">Descrição</label>
          <input class="form-control form-control-sm" name="descricao_mp" id="descricao_mp" value="<?= htmlspecialchars($descricao_mp) ?>" readonly>
          <div class="form-text">Preenchido automaticamente pela MP (nome_mp) a partir do Código.</div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <label class="form-label fw-bold small mb-1">Local</label>
          <input class="form-control form-control-sm" name="nome_local" id="nome_local" required maxlength="255" value="<?= htmlspecialchars($nome_local) ?>" placeholder="Ex: 3A">
          <div class="invalid-feedback">Informe o nome do local.</div>
        </div>
      </div>

      <div class="row g-2 mt-2">
        <div class="col-12 col-md-6">
          <label class="form-label fw-bold small mb-1">Data</label>
          <input class="form-control form-control-sm" value="<?= htmlspecialchars($data_auto) ?>" readonly>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label fw-bold small mb-1">Responsável</label>
          <input class="form-control form-control-sm" value="<?= htmlspecialchars((string)($responsavel['nome'] ?? '')) ?>" readonly>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-3">
        <?php if (!empty($dup_locais) && !$local): ?>
          <input type="hidden" name="force" value="1">
          <button class="btn btn-warning btn-sm fw-bold" type="submit">Cadastrar novo Local mesmo assim</button>
          <a class="btn btn-outline-secondary btn-sm fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais/novo')) ?>">Voltar e alterar</a>
        <?php else: ?>
          <button class="btn btn-primary btn-sm fw-bold px-4" type="submit">Salvar</button>
        <?php endif; ?>
        <a class="btn btn-outline-secondary btn-sm fw-bold px-4" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>">Cancelar</a>
      </div>
    </form>

    <?php if (!$local && is_array($slots) && $slots): ?>
      <div class="mt-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="fw-bold">Mapa de Locais (3A–46C)</div>
          <div class="small text-secondary fw-bold">
            Verde: &lt; 5 &nbsp;|&nbsp; Laranja: 5–8 &nbsp;|&nbsp; Vermelho: &gt; 8
          </div>
        </div>
        <div class="mt-2 p-2 rounded-3 border" style="background: rgba(255,255,255,.35);">
          <div class="d-flex flex-wrap gap-2 justify-content-center">
            <?php foreach ($slots as $s): ?>
              <?php
                $nivel = (string)($s['nivel'] ?? 'success');
                $nome = (string)($s['nome'] ?? '');
                $count = (int)($s['count'] ?? 0);
                $cls = $nivel === 'danger' ? 'btn-danger' : ($nivel === 'warning' ? 'btn-warning' : 'btn-success');
              ?>
              <button
                type="button"
                class="btn btn-sm fw-bold <?= htmlspecialchars($cls) ?>"
                data-slot="<?= htmlspecialchars($nome) ?>"
                title="<?= htmlspecialchars($nome) ?> (<?= (int)$count ?>)"
              >
                <?= htmlspecialchars($nome) ?>
                <span class="badge text-bg-light ms-1"><?= (int)$count ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
</div>

<script>
  (function () {
    const codigo = document.getElementById('codigo_mp');
    const desc = document.getElementById('descricao_mp');
    const nomeLocal = document.getElementById('nome_local');
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

    // Clique no mapa para preencher o campo Local
    if (nomeLocal) {
      document.querySelectorAll('[data-slot]').forEach(btn => {
        btn.addEventListener('click', () => {
          const v = btn.getAttribute('data-slot') || '';
          nomeLocal.value = v;
          nomeLocal.focus();
        });
      });
    }
  })();
</script>

