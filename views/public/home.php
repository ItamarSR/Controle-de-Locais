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
          <div class="col-12">
            <div id="public-status" class="text-secondary small fw-bold"></div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="fw-bold">LOCAL</th>
                <th class="fw-bold">DESCRIÇÃO</th>
                <th class="fw-bold">DATA</th>
                <th class="fw-bold">RESPONSÁVEL</th>
                <th class="text-end">Etiqueta</th>
              </tr>
            </thead>
            <tbody id="public-result">
              <tr>
                <td colspan="5" class="text-secondary fw-bold">DIGITE UM CÓDIGO PARA CONSULTAR.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const input = document.getElementById('public-codigo');
    const tbody = document.getElementById('public-result');
    const status = document.getElementById('public-status');
    if (!input || !tbody) return;

    let t = null;
    function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function fmtDate(s){
      if (!s) return '';
      // s vem como timestamp do MySQL. Mostra dd/mm/aaaa hh:mm
      const d = new Date(String(s).replace(' ', 'T'));
      if (isNaN(d.getTime())) return String(s);
      const pad = n => String(n).padStart(2,'0');
      return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    async function run() {
      const codigo = (input.value || '').trim();
      status.textContent = '';

      if (!codigo) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-secondary fw-bold">DIGITE UM CÓDIGO PARA CONSULTAR.</td></tr>';
        return;
      }

      status.textContent = 'Consultando...';
      tbody.innerHTML = '<tr><td colspan="5" class="text-secondary fw-bold">CARREGANDO...</td></tr>';

      try {
        const res = await fetch('<?= htmlspecialchars(\Core\Http::url('/api/consulta/')) ?>' + encodeURIComponent(codigo), { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data || !data.ok) throw new Error('Falha');

        const itens = Array.isArray(data.itens) ? data.itens : [];

        if (itens.length === 0) {
          status.textContent = 'Nenhum local encontrado para este código.';
          tbody.innerHTML = '<tr><td colspan="5" class="text-secondary fw-bold">NENHUM LOCAL ENCONTRADO.</td></tr>';
          return;
        }

        status.textContent = `${itens.length} local(is) encontrado(s).`;
        tbody.innerHTML = itens.map(i => `
          <tr>
            <td class="fw-bold">${esc(i.local)}</td>
            <td class="fw-bold">${esc(i.descricao)}</td>
            <td class="fw-bold">${esc(fmtDate(i.data))}</td>
            <td class="fw-bold">${esc(i.responsavel || '')}</td>
            <td class="text-end">
              <a class="btn btn-sm btn-primary" target="_blank" href="${esc(i.etiqueta_url)}">Imprimir etiqueta</a>
            </td>
          </tr>
        `).join('');
      } catch (e) {
        status.textContent = 'Erro ao consultar. Tente novamente.';
        tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Erro ao consultar.</td></tr>';
      }
    }

    input.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(run, 250);
    });
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
          <div class="dash-top mb-2">
            <div class="dash-kpi">
              <div class="dash-kpi-title">TOTAL OPS</div>
              <div class="dash-kpi-value" id="dash-total-ops">0</div>
            </div>
            <div class="dash-kpi">
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
              <div class="dash-meta-title">META OP</div>
              <input class="form-control form-control-sm fw-bold" id="dash-meta-op" type="number" min="0" value="4">
            </div>
            <div class="dash-meta">
              <div class="dash-meta-title">META KG</div>
              <input class="form-control form-control-sm fw-bold" id="dash-meta-kg" type="number" min="0" step="0.1" value="500">
            </div>
          </div>

          <div id="dash-status" class="dash-status text-secondary small fw-bold mb-2"></div>

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

        totOps.textContent = String(data.total_ops ?? 0);
        totKg.textContent = fmtKg(data.total_kg ?? 0);
        st.textContent = 'Atualizado.';

        // Defaults metas vindos do servidor (configurações)
        if (metaOpEl.value === '' || metaOpEl.dataset.inited !== '1') {
          metaOpEl.value = String(data.meta_op_step ?? 4);
          metaOpEl.dataset.inited = '1';
        }
        if (metaKgEl.value === '' || metaKgEl.dataset.inited !== '1') {
          metaKgEl.value = String(data.meta_kg_step ?? 500);
          metaKgEl.dataset.inited = '1';
        }

        const metaOpStep = Number(metaOpEl.value || 0);
        const metaKgStep = Number(metaKgEl.value || 0);

        const hours = Array.isArray(data.hours) ? data.hours : [];
        rowsEl.innerHTML = hours.map(h => {
          const hour = Number(h.hour || 0);
          const ops = Number(h.ops || 0);
          const kg = Number(h.kg || 0);
          const metaOp = Math.max(0, metaOpStep) * (hour + 1);
          const metaKg = Math.max(0, metaKgStep) * (hour + 1);

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
    metaOpEl.addEventListener('change', load);
    metaKgEl.addEventListener('change', load);

    // carrega ao abrir o modal (primeira vez)
    const modalEl = document.getElementById('dashModal');
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', () => load(), { once: true });
    }
  })();
</script>

