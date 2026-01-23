<?php
$title = 'Cadastrar OP';
$responsavel = $responsavel ?? null;
$data_auto = $data_auto ?? date('d/m/Y H:i');
$prefill = $prefill ?? [];
$op_exists = $op_exists ?? false;
$reacerto_label = $reacerto_label ?? '';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1 fw-bold">Cadastrar OP</h1>
    <div class="text-secondary small fw-bold">Preencha os campos e clique em inserir.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/conferencia')) ?>">Voltar</a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <?php if ($op_exists): ?>
      <div class="alert alert-danger fw-bold">
        ESSA OP É REACERTO?
        <div class="small fw-bold mt-1">Se SIM, o campo Reacerto será preenchido automaticamente.</div>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(\Core\Http::url('/conferencia/op')) ?>" class="needs-validation" novalidate>
      <input type="hidden" name="confirm_reacerto" id="confirm_reacerto" value="">

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">OP</label>
          <input class="form-control upper fw-bold" name="op" id="op" required maxlength="50" value="<?= htmlspecialchars((string)($prefill['op'] ?? '')) ?>">
          <div class="invalid-feedback">Informe a OP.</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">REACERTO</label>
          <input class="form-control fw-bold" id="reacerto" readonly value="<?= htmlspecialchars($reacerto_label) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">RETÉM</label>
          <select class="form-select fw-bold" name="retem" id="retem">
            <option value="0" <?= ((int)($prefill['retem'] ?? 0) === 0) ? 'selected' : '' ?>>NÃO</option>
            <option value="1" <?= ((int)($prefill['retem'] ?? 0) === 1) ? 'selected' : '' ?>>SIM</option>
          </select>
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">ENTRADA</label>
          <input class="form-control upper fw-bold" name="entrada" value="<?= htmlspecialchars((string)($prefill['entrada'] ?? '')) ?>">
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">ÓLEO</label>
          <input class="form-control upper fw-bold" name="oleo" value="<?= htmlspecialchars((string)($prefill['oleo'] ?? '')) ?>">
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">SAÍDA</label>
          <input class="form-control upper fw-bold" name="saida" value="<?= htmlspecialchars((string)($prefill['saida'] ?? '')) ?>">
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">COR</label>
          <input class="form-control upper fw-bold" name="cor" value="<?= htmlspecialchars((string)($prefill['cor'] ?? '')) ?>">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">DATA</label>
          <input class="form-control fw-bold" readonly value="<?= htmlspecialchars($data_auto) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">RESPONSÁVEL</label>
          <input class="form-control fw-bold" readonly value="<?= htmlspecialchars((string)($responsavel['nome'] ?? '')) ?>">
        </div>

        <div class="col-12">
          <label class="form-label fw-bold">OBS</label>
          <textarea class="form-control upper fw-bold" name="obs" rows="3"><?= htmlspecialchars((string)($prefill['obs'] ?? '')) ?></textarea>
        </div>
      </div>

      <div id="op-warning" class="mt-3 d-none"></div>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary fw-bold" type="submit" id="btn-inserir">INSERIR</button>
        <a class="btn btn-outline-secondary fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/conferencia/op/consulta')) ?>">CONSULTAR</a>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const op = document.getElementById('op');
    const reacerto = document.getElementById('reacerto');
    const warn = document.getElementById('op-warning');
    const btn = document.getElementById('btn-inserir');
    const confirm = document.getElementById('confirm_reacerto');
    let t = null;

    function setWarn(html, type){
      warn.className = 'mt-3 alert alert-' + type + ' fw-bold';
      warn.innerHTML = html;
      warn.classList.remove('d-none');
    }
    function clearWarn(){
      warn.classList.add('d-none');
      warn.innerHTML = '';
    }

    async function check() {
      const v = (op.value || '').trim().toUpperCase();
      op.value = v;
      reacerto.value = '';
      confirm.value = '';
      btn.disabled = false;
      clearWarn();
      if (!v) return;

      try {
        const res = await fetch('<?= htmlspecialchars(\Core\Http::url('/conferencia/api/op/')) ?>' + encodeURIComponent(v));
        const data = await res.json();
        if (!data || !data.ok) return;

        if (data.count > 0) {
          setWarn(`ESSA OP JÁ EXISTE. <span class="text-decoration-underline">ESSA OP É REACERTO?</span>
            <div class="mt-2 d-flex gap-2">
              <button type="button" class="btn btn-light fw-bold" id="btn-sim">SIM</button>
              <button type="button" class="btn btn-outline-light fw-bold" id="btn-nao">NÃO</button>
            </div>`, 'danger');

          btn.disabled = true;

          setTimeout(() => {
            const sim = document.getElementById('btn-sim');
            const nao = document.getElementById('btn-nao');
            if (sim) sim.onclick = () => {
              confirm.value = 'sim';
              reacerto.value = data.label;
              btn.disabled = false;
              setWarn(`REACERTO CONFIRMADO: ${data.label}`, 'warning');
            };
            if (nao) nao.onclick = () => {
              confirm.value = 'nao';
              btn.disabled = true;
              setWarn('ESSA OP JÁ FOI INSERIDA.', 'danger');
            };
          }, 0);
        } else {
          reacerto.value = 'ORIGINAL';
        }
      } catch (e) {}
    }

    op.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(check, 250);
    });

    // prefill
    check();
  })();
</script>

