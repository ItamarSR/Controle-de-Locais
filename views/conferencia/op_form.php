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
          <select class="form-select fw-bold" name="retem" id="retem" required>
            <option value="" <?= !isset($prefill['retem']) ? 'selected' : '' ?>>SELECIONE...</option>
            <option value="1" <?= ((int)($prefill['retem'] ?? 0) === 1) ? 'selected' : '' ?>>SIM</option>
          </select>
          <div class="invalid-feedback">Selecione SIM.</div>
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">ENTRADA</label>
          <input class="form-control fw-bold" name="entrada" id="entrada" required inputmode="decimal" autocomplete="off" value="<?= htmlspecialchars((string)($prefill['entrada'] ?? '')) ?>" placeholder="EX: 32,220">
          <div class="invalid-feedback">Informe um número.</div>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">ÓLEO</label>
          <input class="form-control fw-bold" name="oleo" id="oleo" required inputmode="decimal" autocomplete="off" value="<?= htmlspecialchars((string)($prefill['oleo'] ?? '')) ?>" placeholder="EX: 1,000">
          <div class="invalid-feedback">Informe um número.</div>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">SAÍDA</label>
          <input class="form-control fw-bold" name="saida" id="saida" required inputmode="decimal" autocomplete="off" value="<?= htmlspecialchars((string)($prefill['saida'] ?? '')) ?>" placeholder="EX: 33,000">
          <div class="invalid-feedback">Informe um número.</div>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">COR</label>
          <input class="form-control upper fw-bold" name="cor" id="cor" required value="<?= htmlspecialchars((string)($prefill['cor'] ?? '')) ?>">
          <div class="invalid-feedback">Informe a cor.</div>
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">DESPERDÍCIO</label>
          <input class="form-control fw-bold" name="desperdicio" id="desperdicio" readonly value="">
          <div class="form-text">Calculado: SAÍDA - (ENTRADA + ÓLEO).</div>
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
      <div id="desp-warning" class="mt-3 d-none"></div>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary fw-bold" type="submit" id="btn-inserir">INSERIR</button>
        <a class="btn btn-outline-secondary fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/conferencia/op/consulta')) ?>">CONSULTAR</a>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const form = document.querySelector('form');
    const op = document.getElementById('op');
    const reacerto = document.getElementById('reacerto');
    const warn = document.getElementById('op-warning');
    const warnDesp = document.getElementById('desp-warning');
    const btn = document.getElementById('btn-inserir');
    const confirm = document.getElementById('confirm_reacerto');
    const entrada = document.getElementById('entrada');
    const oleo = document.getElementById('oleo');
    const saida = document.getElementById('saida');
    const desp = document.getElementById('desperdicio');
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

    function setWarnDesp(msg, type){
      warnDesp.className = 'mt-3 alert alert-' + type + ' fw-bold';
      warnDesp.textContent = msg;
      warnDesp.classList.remove('d-none');
    }
    function clearWarnDesp(){
      warnDesp.classList.add('d-none');
      warnDesp.textContent = '';
    }

    function parseBr(v){
      v = String(v || '').trim();
      if (!v) return null;
      v = v.replace(/\./g,'').replace(',', '.');
      const n = Number(v);
      return isNaN(n) ? null : n;
    }
    function fmtBr(n){
      return n.toLocaleString('pt-BR', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
    }

    function maskNumber(el){
      let v = String(el.value || '');
      v = v.replace(/\./g, ','); // troca ponto por vírgula
      v = v.replace(/[^\d,]/g,'');
      const parts = v.split(',');
      let intPart = parts[0] || '';
      let decPart = parts[1] || '';
      if (parts.length > 2) {
        decPart = parts.slice(1).join('').slice(0,3);
      }
      decPart = decPart.slice(0,3);
      // auto vírgula com 3 casas apenas quando tiver muitos dígitos (evita 1250 virar 1,250)
      if (parts.length === 1 && intPart.length >= 5) {
        decPart = intPart.slice(-3);
        intPart = intPart.slice(0, -3);
        v = intPart + ',' + decPart;
      } else {
        v = parts.length > 1 ? (intPart + ',' + decPart) : intPart;
      }
      el.value = v;
    }

    function calcDesperdicio(){
      if (!entrada || !oleo || !saida || !desp) return;
      const e = parseBr(entrada.value);
      const o = parseBr(oleo.value);
      const s = parseBr(saida.value);
      clearWarnDesp();
      if (e === null || o === null || s === null) {
        desp.value = '';
        return;
      }
      const sum = e + o;
      const d = Math.max(0, s - sum);
      desp.value = fmtBr(d);
      if (d > 0.400) {
        setWarnDesp('ALERTA: (SAÍDA - (ENTRADA + ÓLEO)) ACIMA DE 0,400.', 'warning');
      }
    }

    // Enter pula para o próximo campo
    if (form) {
      form.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        // permite quebra de linha apenas com SHIFT+ENTER no textarea
        if (target.tagName === 'TEXTAREA' && e.shiftKey) return;
        e.preventDefault();
        const focusables = Array.from(form.querySelectorAll('input, select, textarea, button'))
          .filter(el => (el instanceof HTMLElement) && !el.hasAttribute('disabled') && el.getAttribute('type') !== 'submit');
        const idx = focusables.indexOf(target);
        if (idx >= 0 && idx < focusables.length - 1) {
          focusables[idx + 1].focus();
        }
      });
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

    // Máscara e cálculo
    [entrada, oleo, saida].forEach(el => {
      if (!el) return;
      el.addEventListener('input', () => { maskNumber(el); calcDesperdicio(); });
      el.addEventListener('blur', () => { maskNumber(el); calcDesperdicio(); });
    });

    // prefill
    check();
    calcDesperdicio();
  })();
</script>

