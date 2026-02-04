<?php
$title = 'Consulta pública de locais';
$logoPath = null;
try {
  $s = new \App\Models\Settings();
  $logoPath = (string)($s->get('logo_path', '') ?? '');
  if (trim($logoPath) === '') $logoPath = null;
} catch (Throwable $e) {
  $logoPath = null;
}
?>

<div class="row g-3 align-items-stretch">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex justify-content-center">
          <?php if (is_string($logoPath) && $logoPath !== ''): ?>
            <img
              src="<?= htmlspecialchars(\Core\Http::url($logoPath)) ?>"
              alt="Logo"
              width="300"
              height="73"
              style="object-fit:contain;border:1px solid rgba(15,23,42,.18);border-radius:16px;background:rgba(255,255,255,.65);padding:10px;"
            >
          <?php else: ?>
            <div class="text-secondary fw-bold">LOGO NÃO CONFIGURADA (ADMIN &gt; CONFIGURAÇÕES)</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-3 p-md-4">
        <button
          class="btn btn-primary fw-bold w-100 d-flex align-items-center justify-content-between"
          type="button"
          data-bs-toggle="modal"
          data-bs-target="#dashModal"
        >
          <span>DASH PRODUÇÃO</span>
          <span class="small">ABRIR</span>
        </button>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-3 p-md-4">
        <div class="row g-3">
          <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label fw-bold">CÓDIGO</label>
            <input class="form-control" id="public-codigo" placeholder="EX: 12345" maxlength="50" autocomplete="off" style="text-transform:uppercase;font-weight:800;">
          </div>
          <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label fw-bold">LOCAL</label>
            <input class="form-control" id="public-local" placeholder="EX: 3A" maxlength="50" autocomplete="off" style="text-transform:uppercase;font-weight:800;">
          </div>
          <div class="col-12">
            <div id="public-status" class="text-secondary small fw-bold"></div>
          </div>
        </div>

        <div class="table-responsive">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <button class="btn btn-primary btn-sm fw-bold" type="button" id="public-print-selected" disabled>IMPRIMIR SELECIONADOS (0)</button>
              <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" id="public-clear-selected" disabled>LIMPAR SELEÇÃO</button>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" id="public-select-all">
                <label class="form-check-label fw-bold small" for="public-select-all">Selecionar todos (lista)</label>
              </div>
            </div>
          </div>
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:44px;"></th>
                <th class="fw-bold">CÓDIGO</th>
                <th class="fw-bold">LOCAL</th>
                <th class="fw-bold">DESCRIÇÃO</th>
                <th class="fw-bold">DATA</th>
                <th class="fw-bold">RESPONSÁVEL</th>
                <th class="text-end">Etiqueta</th>
              </tr>
            </thead>
            <tbody id="public-result">
              <tr>
                <td colspan="7" class="text-secondary fw-bold">CARREGANDO...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Impressão (mesma tela) -->
<div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <div class="fw-bold">Imprimir etiquetas</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="printFrame" title="Pré-visualização" style="width:100%;height:78vh;border:0;background:#fff;"></iframe>
      </div>
      <div class="modal-footer">
        <div class="me-auto small fw-bold text-secondary" id="printModalInfo"></div>
        <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-primary fw-bold" id="printNow">Imprimir</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const inputCodigo = document.getElementById('public-codigo');
    const inputLocal = document.getElementById('public-local');
    const tbody = document.getElementById('public-result');
    const status = document.getElementById('public-status');
    if (!inputCodigo || !inputLocal || !tbody || !status) return;

    const btnPrintSelected = document.getElementById('public-print-selected');
    const btnClearSelected = document.getElementById('public-clear-selected');
    const chkAll = document.getElementById('public-select-all');
    const printModalEl = document.getElementById('printModal');
    const printFrame = document.getElementById('printFrame');
    const printNow = document.getElementById('printNow');
    const printInfo = document.getElementById('printModalInfo');

    let t = null;
    let currentItems = [];
    const selected = new Set();

    function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function fmtDate(s){
      if (!s) return '';
      // s vem como timestamp do MySQL. Mostra dd/mm/aaaa hh:mm
      const d = new Date(String(s).replace(' ', 'T'));
      if (isNaN(d.getTime())) return String(s);
      const pad = n => String(n).padStart(2,'0');
      return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function updateSelectionUi() {
      const n = selected.size;
      if (btnPrintSelected) {
        btnPrintSelected.disabled = n < 1;
        btnPrintSelected.textContent = `IMPRIMIR SELECIONADOS (${n})`;
      }
      if (btnClearSelected) btnClearSelected.disabled = n < 1;

      // Atualiza "selecionar todos" com base na lista atual
      if (chkAll) {
        const visibleIds = currentItems.map(i => String(i.id));
        const allVisibleSelected = visibleIds.length > 0 && visibleIds.every(id => selected.has(id));
        const someVisibleSelected = visibleIds.some(id => selected.has(id));
        chkAll.indeterminate = !allVisibleSelected && someVisibleSelected;
        chkAll.checked = allVisibleSelected;
      }
    }

    function openPrint(ids) {
      const list = Array.isArray(ids) ? ids.map(v => String(v)).filter(v => /^\d+$/.test(v) && Number(v) > 0) : [];
      if (list.length < 1) return;
      const url = '<?= htmlspecialchars(\Core\Http::url('/etiquetas')) ?>' + '?ids=' + encodeURIComponent(list.join(',')) + '&t=' + Date.now();
      if (printFrame) printFrame.src = url;
      if (printInfo) printInfo.textContent = `${list.length} etiqueta(s) selecionada(s).`;
      if (printModalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const m = window.bootstrap.Modal.getOrCreateInstance(printModalEl);
        m.show();
      } else if (printModalEl) {
        // fallback: abre na mesma aba
        window.location.href = url;
      }
    }

    async function run() {
      const codigo = (inputCodigo.value || '').trim();
      const local = (inputLocal.value || '').trim();
      status.textContent = 'Carregando...';
      tbody.innerHTML = '<tr><td colspan="7" class="text-secondary fw-bold">CARREGANDO...</td></tr>';

      try {
        const qs = new URLSearchParams();
        if (codigo) qs.set('codigo', codigo);
        if (local) qs.set('local', local);
        // limite alto para "exibir todos" (com proteção no servidor)
        qs.set('limit', '5000');

        const res = await fetch('<?= htmlspecialchars(\Core\Http::url('/api/locais')) ?>' + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data || !data.ok) throw new Error('Falha');

        const itens = Array.isArray(data.itens) ? data.itens : [];
        currentItems = itens;

        if (itens.length === 0) {
          status.textContent = 'Nenhum item encontrado.';
          tbody.innerHTML = '<tr><td colspan="7" class="text-secondary fw-bold">NENHUM ITEM ENCONTRADO.</td></tr>';
          updateSelectionUi();
          return;
        }

        const hasFilter = !!codigo || !!local;
        status.textContent = hasFilter
          ? `${itens.length} item(ns) encontrado(s) para o filtro.`
          : `${itens.length} item(ns) cadastrados (exibindo lista).`;

        tbody.innerHTML = itens.map(i => `
          <tr>
            <td>
              <input class="form-check-input public-check" type="checkbox" data-id="${esc(i.id)}" ${selected.has(String(i.id)) ? 'checked' : ''}>
            </td>
            <td class="fw-bold">${esc(i.codigo)}</td>
            <td class="fw-bold">${esc(i.local)}</td>
            <td class="fw-bold">${esc(i.descricao)}</td>
            <td class="fw-bold">${esc(fmtDate(i.data))}</td>
            <td class="fw-bold">${esc(i.responsavel || '')}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary public-print-one" type="button" data-id="${esc(i.id)}">Imprimir</button>
            </td>
          </tr>
        `).join('');

        updateSelectionUi();
      } catch (e) {
        status.textContent = 'Erro ao consultar. Tente novamente.';
        tbody.innerHTML = '<tr><td colspan="7" class="text-danger">Erro ao consultar.</td></tr>';
      }
    }

    function queue() {
      clearTimeout(t);
      t = setTimeout(run, 250);
    }

    inputCodigo.addEventListener('input', queue);
    inputLocal.addEventListener('input', queue);

    // seleção via tabela (delegação)
    tbody.addEventListener('change', (ev) => {
      const el = ev.target;
      if (!(el instanceof HTMLElement)) return;
      if (!el.classList.contains('public-check')) return;
      const id = String(el.getAttribute('data-id') || '');
      if (!/^\d+$/.test(id)) return;
      if (el.checked) selected.add(id); else selected.delete(id);
      updateSelectionUi();
    });

    tbody.addEventListener('click', (ev) => {
      const el = ev.target;
      if (!(el instanceof HTMLElement)) return;
      if (!el.classList.contains('public-print-one')) return;
      const id = String(el.getAttribute('data-id') || '');
      if (!/^\d+$/.test(id)) return;
      openPrint([id]);
    });

    if (chkAll) {
      chkAll.addEventListener('change', () => {
        const check = !!chkAll.checked;
        for (const i of currentItems) {
          const id = String(i.id);
          if (!/^\d+$/.test(id)) continue;
          if (check) selected.add(id); else selected.delete(id);
        }
        // Atualiza checkboxes visíveis sem precisar recarregar
        tbody.querySelectorAll('input.public-check[data-id]').forEach((n) => {
          if (!(n instanceof HTMLInputElement)) return;
          const id = String(n.getAttribute('data-id') || '');
          n.checked = selected.has(id);
        });
        updateSelectionUi();
      });
    }

    if (btnClearSelected) {
      btnClearSelected.addEventListener('click', () => {
        selected.clear();
        tbody.querySelectorAll('input.public-check[data-id]').forEach((n) => {
          if (n instanceof HTMLInputElement) n.checked = false;
        });
        updateSelectionUi();
      });
    }

    if (btnPrintSelected) {
      btnPrintSelected.addEventListener('click', () => {
        openPrint(Array.from(selected));
      });
    }

    if (printNow && printFrame) {
      printNow.addEventListener('click', () => {
        try {
          if (printFrame.contentWindow) printFrame.contentWindow.focus();
          if (printFrame.contentWindow) printFrame.contentWindow.print();
        } catch (e) {
          // fallback: abre a página do iframe na mesma aba
          if (printFrame.src) window.location.href = printFrame.src;
        }
      });
    }

    // Carrega a lista completa ao abrir a página
    run();
  })();
</script>

<!-- Modal Dash Produção -->
<div class="modal fade modal-dash" id="dashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <div class="fw-bold">Dash Produção</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-3 p-md-4">
        <div class="dash-wrap dash-compact">
          <div class="dash-fixed">
            <div class="dash-fixed-inner">
              <div class="dash-top mb-2">
                <div class="dash-kpi dash-kpi-ops">
                  <div class="dash-kpi-title">TOTAL OPS</div>
                  <div class="dash-kpi-value" id="dash-total-ops">0</div>
                </div>
                <div class="dash-kpi dash-kpi-kg">
                  <div class="dash-kpi-title">TOTAL KG</div>
                  <div class="dash-kpi-value" id="dash-total-kg">0</div>
                </div>
                <div class="dash-date">
                  <div class="dash-date-title">DATA</div>
                  <div class="dash-date-controls">
                    <button class="btn btn-outline-secondary btn-sm fw-bold" id="dash-prev" type="button">-</button>
                    <input type="date" class="form-control form-control-sm fw-bold" id="dash-date" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
                    <button class="btn btn-outline-secondary btn-sm fw-bold" id="dash-next" type="button">+</button>
                    <button class="btn btn-primary btn-sm fw-bold" id="dash-refresh" type="button">OK</button>
                  </div>
                </div>
                <div class="dash-meta">
                  <div class="dash-meta-title d-flex align-items-center justify-content-between">
                    <span>META OP</span>
                    <span id="dash-meta-op-ico" class="fw-bold"></span>
                  </div>
                  <input class="form-control form-control-sm fw-bold" id="dash-meta-op" type="number" readonly>
                </div>
                <div class="dash-meta">
                  <div class="dash-meta-title d-flex align-items-center justify-content-between">
                    <span>META KG</span>
                    <span id="dash-meta-kg-ico" class="fw-bold"></span>
                  </div>
                  <input class="form-control form-control-sm fw-bold" id="dash-meta-kg" type="number" readonly>
                </div>
              </div>

              <div id="dash-status" class="dash-status text-secondary small fw-bold"></div>
            </div>
          </div>

          <div class="dash-scroll mt-2">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0 dash-table">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold">INÍCIO</th>
                    <th class="fw-bold">FIM</th>
                    <th class="fw-bold text-center">OPS</th>
                    <th class="fw-bold text-center">META OP</th>
                    <th class="fw-bold text-center">KG</th>
                    <th class="fw-bold text-center">META KG</th>
                  </tr>
                </thead>
                <tbody id="dash-rows">
                  <tr><td colspan="6" class="text-secondary fw-bold">CARREGANDO...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const dateEl = document.getElementById('dash-date');
    const btn = document.getElementById('dash-refresh');
    const rowsEl = document.getElementById('dash-rows');
    const st = document.getElementById('dash-status');
    const totOps = document.getElementById('dash-total-ops');
    const totKg = document.getElementById('dash-total-kg');
    const metaOpEl = document.getElementById('dash-meta-op');
    const metaKgEl = document.getElementById('dash-meta-kg');
    const metaOpIco = document.getElementById('dash-meta-op-ico');
    const metaKgIco = document.getElementById('dash-meta-kg-ico');
    const prev = document.getElementById('dash-prev');
    const next = document.getElementById('dash-next');
    if (!dateEl || !btn || !rowsEl || !metaOpEl || !metaKgEl) return;

    function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function fmtKg(v){
      const n = Number(v || 0);
      return n.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
    }

    function addDays(iso, delta){
      const d = new Date(iso + 'T00:00:00');
      if (isNaN(d.getTime())) return iso;
      d.setDate(d.getDate() + delta);
      const pad = n => String(n).padStart(2,'0');
      return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    }

    async function load() {
      const d = (dateEl.value || '').trim();
      st.textContent = 'Carregando dash...';
      rowsEl.innerHTML = '<tr><td colspan="6" class="text-secondary fw-bold">CARREGANDO...</td></tr>';
      try {
        const res = await fetch('<?= htmlspecialchars(\Core\Http::url('/api/dash-producao')) ?>' + '?date=' + encodeURIComponent(d));
        const data = await res.json();
        if (!data || !data.ok) throw new Error('Falha');

        const totalOps = Number(data.total_ops ?? 0);
        const totalKg = Number(data.total_kg ?? 0);
        totOps.textContent = String(totalOps);
        totKg.textContent = fmtKg(totalKg);
        st.textContent = 'Atualizado.';

        // Metas diárias vindas do servidor (EditorPro/Admin)
        const metaOpDaily = Number(data.meta_op_daily ?? 0);
        const metaKgDaily = Number(data.meta_kg_daily ?? 0);
        metaOpEl.value = metaOpDaily > 0 ? String(metaOpDaily) : '';
        metaKgEl.value = metaKgDaily > 0 ? String(metaKgDaily) : '';

        function iconFor(total, meta){
          if (!(meta > 0)) return '';
          if (total < meta) return '☹';
          if (total === meta) return '🙂';
          return '👍';
        }
        if (metaOpIco) metaOpIco.textContent = iconFor(totalOps, metaOpDaily);
        if (metaKgIco) metaKgIco.textContent = iconFor(totalKg, metaKgDaily);

        // Meta por hora: distribui a meta diária em 24h (cumulativa)
        const metaOpStep = metaOpDaily > 0 ? (metaOpDaily / 24) : 0;
        const metaKgStep = metaKgDaily > 0 ? (metaKgDaily / 24) : 0;

        const hours = Array.isArray(data.hours) ? data.hours : [];
        rowsEl.innerHTML = hours.map(h => {
          const hour = Number(h.hour || 0);
          const ops = Number(h.ops || 0);
          const kg = Number(h.kg || 0);
          const metaOp = metaOpStep > 0 ? Math.round(metaOpStep * (hour + 1)) : 0;
          const metaKg = metaKgStep > 0 ? (metaKgStep * (hour + 1)) : 0;

          const okOps = metaOp > 0 ? (ops >= metaOp) : null;
          const okKg = metaKg > 0 ? (kg >= metaKg) : null;
          const cls = (okOps === true || okKg === true) ? 'dash-row-ok' : ((ops > 0 || kg > 0) ? 'dash-row-has' : '');

          return `
            <tr class="${cls}">
              <td class="fw-bold">${esc(h.inicio)}</td>
              <td class="fw-bold">${esc(h.fim)}</td>
              <td class="text-center fw-bold">${esc(ops)}</td>
              <td class="text-center fw-bold">${esc(metaOp)}</td>
              <td class="text-center fw-bold">${esc(fmtKg(kg))}</td>
              <td class="text-center fw-bold">${esc(fmtKg(metaKg))}</td>
            </tr>
          `;
        }).join('');
      } catch (e) {
        st.textContent = 'Erro ao carregar o dash.';
        rowsEl.innerHTML = '<tr><td colspan="6" class="text-danger fw-bold">ERRO AO CARREGAR DADOS.</td></tr>';
      }
    }

    btn.addEventListener('click', load);
    dateEl.addEventListener('change', load);
    if (prev) prev.addEventListener('click', () => { dateEl.value = addDays(dateEl.value, -1); load(); });
    if (next) next.addEventListener('click', () => { dateEl.value = addDays(dateEl.value, +1); load(); });

    // carrega ao abrir o modal (primeira vez)
    const modalEl = document.getElementById('dashModal');
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', () => load(), { once: true });
    }
  })();
</script>

