<?php
$title = 'Cadastrar OP';
$responsavel = $responsavel ?? null;
$data_auto = $data_auto ?? date('d/m/Y H:i');
$prefill = $prefill ?? [];
$op_exists = $op_exists ?? false;
$reacerto_label = $reacerto_label ?? '';
$prefill_qtde_emb = (string)($prefill['qtde_emb'] ?? '');
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1 fw-bold">Cadastrar OP</h1>
    <div class="text-secondary small fw-bold">Preencha os campos e clique em inserir.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/conferencia')) ?>">Voltar</a>
</div>

<div class="mx-auto" style="max-width: 980px;">
<div class="card shadow-sm op-card">
  <div class="card-body p-3 p-md-4">
    <div id="op-warning-top" class="<?= $op_exists ? '' : 'd-none' ?>">
      <?php if ($op_exists): ?>
        <div class="alert alert-danger fw-bold">
          ESSA OP JÁ EXISTE. <span class="text-decoration-underline">ESSA OP É REACERTO?</span>
          <div class="small fw-bold mt-1">Se SIM, o campo Reacerto será preenchido automaticamente.</div>
        </div>
      <?php endif; ?>
    </div>

    <form method="post" action="<?= htmlspecialchars(\Core\Http::url('/conferencia/op')) ?>" class="needs-validation" novalidate>
      <input type="hidden" name="confirm_reacerto" id="confirm_reacerto" value="">

      <div class="row g-2">
        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">OP</label>
          <input class="form-control form-control-sm upper fw-bold" name="op" id="op" required maxlength="50" value="<?= htmlspecialchars((string)($prefill['op'] ?? '')) ?>">
          <div class="invalid-feedback">Informe a OP.</div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">REACERTO</label>
          <input class="form-control form-control-sm fw-bold" id="reacerto" readonly value="<?= htmlspecialchars($reacerto_label) ?>">
        </div>
        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">RETÉM</label>
          <select class="form-select form-select-sm fw-bold" name="retem" id="retem" required>
            <option value="" <?= !isset($prefill['retem']) ? 'selected' : '' ?>>SELECIONE...</option>
            <option value="1" <?= ((int)($prefill['retem'] ?? 0) === 1) ? 'selected' : '' ?>>SIM</option>
          </select>
          <div class="invalid-feedback">Selecione SIM.</div>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">ENTRADA</label>
          <input class="form-control form-control-sm fw-bold" name="entrada" id="entrada" required inputmode="decimal" autocomplete="off" value="<?= htmlspecialchars((string)($prefill['entrada'] ?? '')) ?>" placeholder="EX: 32,220">
          <div class="invalid-feedback">Informe um número.</div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">ÓLEO</label>
          <input class="form-control form-control-sm fw-bold" name="oleo" id="oleo" required inputmode="decimal" autocomplete="off" value="<?= htmlspecialchars((string)($prefill['oleo'] ?? '')) ?>" placeholder="EX: 1,000">
          <div class="invalid-feedback">Informe um número.</div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1 d-flex align-items-center justify-content-between gap-2">
            <span>SAÍDA</span>
            <span class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" id="saida_pmode">
              <label class="form-check-label small fw-bold" for="saida_pmode">P1–P12</label>
            </span>
          </label>
          <input class="form-control form-control-sm fw-bold" name="saida" id="saida" required inputmode="decimal" autocomplete="off" value="<?= htmlspecialchars((string)($prefill['saida'] ?? '')) ?>" placeholder="EX: 33,000">
          <div class="invalid-feedback">Informe um número.</div>
          <div class="form-text" id="saida-liquida-txt">SAÍDA LÍQUIDA = SAÍDA - TOTAL EMB</div>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">COR</label>
          <input class="form-control form-control-sm upper fw-bold" name="cor" id="cor" required value="<?= htmlspecialchars((string)($prefill['cor'] ?? '')) ?>">
          <div class="invalid-feedback">Informe a cor.</div>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">DESPERDÍCIO</label>
          <input class="form-control form-control-sm fw-bold" name="desperdicio" id="desperdicio" readonly value="">
          <div class="form-text">Calculado: SAÍDA - (ENTRADA + ÓLEO).</div>
        </div>
        <div class="col-12 d-none" id="p-wrap">
          <div class="border rounded-3 p-2" style="background: rgba(255,255,255,.35);">
            <div class="small fw-bold mb-2">PESAGENS (P1–P12) — SAÍDA = SOMA</div>
            <div class="row g-2">
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <div class="col-6 col-md-4 col-lg-2">
                  <input class="form-control form-control-sm fw-bold" data-p="1" inputmode="decimal" autocomplete="off" placeholder="P<?= $i ?> (EX: 0,000)">
                </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">DATA</label>
          <input class="form-control form-control-sm fw-bold" readonly value="<?= htmlspecialchars($data_auto) ?>">
        </div>
        <div class="col-12 col-md-4 col-lg-3">
          <label class="form-label fw-bold small mb-1">RESPONSÁVEL</label>
          <input class="form-control form-control-sm fw-bold" readonly value="<?= htmlspecialchars((string)($responsavel['nome'] ?? '')) ?>">
        </div>

        <div class="col-12">
          <label class="form-label fw-bold small mb-1">OBS</label>
          <textarea class="form-control form-control-sm upper fw-bold" name="obs" id="obs" rows="2"><?= htmlspecialchars((string)($prefill['obs'] ?? '')) ?></textarea>
        </div>

        <div class="col-12 d-none" id="emb-wrap">
          <div class="border rounded-3 p-2" style="background: rgba(255,255,255,.35);">
            <div class="row g-2 align-items-start">
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold small mb-1">QTDE EMB</label>
                <input class="form-control form-control-sm fw-bold" name="qtde_emb" id="qtde_emb" inputmode="numeric" autocomplete="off" value="<?= htmlspecialchars($prefill_qtde_emb) ?>" placeholder="EX: 10">
                <div class="invalid-feedback">Qtde Emb é obrigatória quando SAÍDA &gt; 100,000.</div>
                <div class="form-text">Cálculo: QTDE × 0,060kg</div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold small mb-1">TOTAL EMB (KG)</label>
                <input class="form-control form-control-sm fw-bold" id="total_emb_kg" readonly value="">
                <div class="form-text">&nbsp;</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="op-warning" class="d-none"></div>
      <div id="desp-warning" class="mt-3 d-none"></div>

      <div class="d-flex flex-wrap gap-2 mt-3">
        <button class="btn btn-primary btn-sm fw-bold px-4" type="submit" id="btn-inserir">INSERIR</button>
        <a class="btn btn-outline-secondary btn-sm fw-bold px-4" href="<?= htmlspecialchars(\Core\Http::url('/conferencia/op/consulta')) ?>">CONSULTAR</a>
      </div>
    </form>
  </div>
</div>
</div>

<script>
  (function () {
    const form = document.querySelector('form');
    const op = document.getElementById('op');
    const reacerto = document.getElementById('reacerto');
    const warn = document.getElementById('op-warning');
    const warnTop = document.getElementById('op-warning-top');
    const warnDesp = document.getElementById('desp-warning');
    const btn = document.getElementById('btn-inserir');
    const confirm = document.getElementById('confirm_reacerto');
    const entrada = document.getElementById('entrada');
    const oleo = document.getElementById('oleo');
    const saida = document.getElementById('saida');
    const qtdeEmb = document.getElementById('qtde_emb');
    const totalEmb = document.getElementById('total_emb_kg');
    const desp = document.getElementById('desperdicio');
    const obs = document.getElementById('obs');
    const embWrap = document.getElementById('emb-wrap');
    const pMode = document.getElementById('saida_pmode');
    const pWrap = document.getElementById('p-wrap');
    const pInputs = Array.from(document.querySelectorAll('[data-p]'));
    let t = null;

    const EMB_KG = 0.060;
    const EMB_SHOW_ABOVE = 100.000;

    function setWarn(html, type){
      if (warnTop) {
        warnTop.innerHTML = '<div class="alert alert-' + type + ' fw-bold">' + html + '</div>';
        warnTop.classList.remove('d-none');
      }
    }
    function clearWarn(){
      if (warnTop) {
        warnTop.classList.add('d-none');
        warnTop.innerHTML = '';
      }
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
    function fmt3(n){
      if (!Number.isFinite(n)) return '';
      // sem separador de milhar (ex.: 1250,000)
      return n.toFixed(3).replace('.', ',');
    }

    // Máscara de peso (3 casas decimais) digitando da direita para esquerda:
    // 1250 -> 1,250 | 12230 -> 12,230 | 1250000 -> 1250,000
    function maskWeight(el){
      const digits = String(el.value || '').replace(/\D/g,'');
      if (!digits) { el.value = ''; return; }
      const dec = digits.slice(-3).padStart(3, '0');
      const intPart = digits.length > 3 ? digits.slice(0, -3) : '0';
      el.value = intPart + ',' + dec;
    }

    function calcEmbTotal(){
      if (!qtdeEmb || !totalEmb) return;
      const v = String(qtdeEmb.value || '').replace(/[^\d]/g,'');
      qtdeEmb.value = v;
      if (!v) { totalEmb.value = ''; return; }
      const q = Number(v);
      if (!Number.isFinite(q)) { totalEmb.value = ''; return; }
      const total = q * EMB_KG; // kg
      totalEmb.value = fmt3(total);
    }

    function updateEmbVisibility(){
      if (!saida || !qtdeEmb || !embWrap) return;
      const s = parseBr(saida.value);
      const show = (s !== null && s > EMB_SHOW_ABOVE);
      embWrap.classList.toggle('d-none', !show);
      qtdeEmb.required = !!show;
      if (!show) {
        qtdeEmb.value = '';
        if (totalEmb) totalEmb.value = '';
      }
    }

    function getTotalEmbKg(){
      const q = qtdeEmb && qtdeEmb.value ? Number(String(qtdeEmb.value).replace(/\D/g,'')) : 0;
      return (Number.isFinite(q) ? (q * EMB_KG) : 0);
    }

    function calcSaidaFromPesagens(){
      if (!saida) return;
      let sum = 0;
      let hasAny = false;
      pInputs.forEach(inp => {
        if (!(inp instanceof HTMLInputElement)) return;
        const n = parseBr(inp.value);
        if (n !== null) { sum += n; hasAny = true; }
      });
      saida.value = hasAny ? fmt3(sum) : '';
    }

    function calcDesperdicio(){
      if (!entrada || !oleo || !saida || !desp) return;
      const e = parseBr(entrada.value);
      const o = parseBr(oleo.value);
      const s = parseBr(saida.value);
      clearWarnDesp();
      desp.classList.remove('border-danger','border-success');
      desp.classList.remove('text-danger','text-success');
      if (obs) obs.required = false;
      if (e === null || o === null || s === null) {
        desp.value = '';
        return;
      }
      // Saída líquida descontando embalagens (se informado)
      const totalEmbKg = getTotalEmbKg();
      const saidaLiquida = s - totalEmbKg;

      const sum = e + o;
      const d = (saidaLiquida - sum);
      desp.value = fmt3(d);
      if (d < -0.400) {
        desp.classList.add('border-danger','text-danger');
        if (obs) obs.required = true;
        setWarnDesp('ALERTA: DESPERDÍCIO MENOR QUE -0,400. OBS É OBRIGATÓRIO.', 'danger');
      } else if (d > 0.400) {
        desp.classList.add('border-success','text-success');
        if (obs) obs.required = true;
        setWarnDesp('ALERTA: DESPERDÍCIO MAIOR QUE 0,400. OBS É OBRIGATÓRIO.', 'success');
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
      el.addEventListener('input', () => { maskWeight(el); updateEmbVisibility(); calcDesperdicio(); });
      el.addEventListener('blur', () => { maskWeight(el); updateEmbVisibility(); calcDesperdicio(); });
    });
    if (qtdeEmb) {
      qtdeEmb.addEventListener('input', () => { calcEmbTotal(); calcDesperdicio(); });
      qtdeEmb.addEventListener('blur', () => { calcEmbTotal(); calcDesperdicio(); });
    }

    // Modo pesagens P1..P10
    if (pMode && pWrap && saida) {
      function syncMode(){
        const on = !!pMode.checked;
        saida.readOnly = on;
        pWrap.classList.toggle('d-none', !on);
        if (on) {
          calcSaidaFromPesagens();
          updateEmbVisibility();
          calcDesperdicio();
        }
      }
      pMode.addEventListener('change', syncMode);
      pInputs.forEach(inp => {
        if (!(inp instanceof HTMLInputElement)) return;
        inp.addEventListener('input', () => { maskWeight(inp); calcSaidaFromPesagens(); updateEmbVisibility(); calcDesperdicio(); });
        inp.addEventListener('blur', () => { maskWeight(inp); calcSaidaFromPesagens(); updateEmbVisibility(); calcDesperdicio(); });
      });
      syncMode();
    }

    // prefill
    check();
    updateEmbVisibility();
    calcDesperdicio();
    calcEmbTotal();
  })();
</script>

