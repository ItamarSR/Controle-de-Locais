<?php
$title = 'Consulta pública de locais';
?>

<div class="row g-3 align-items-stretch">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
          <div>
            <h1 class="h4 mb-1">Consulta pública de locais</h1>
            <p class="text-secondary mb-0">Digite o <b>Código</b> para localizar o estoque e imprimir etiqueta BOPP 100×60.</p>
          </div>
          <a class="btn btn-outline-primary" href="<?= htmlspecialchars(\Core\Http::url('/login')) ?>">Área administrativa</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-end">
          <div>
            <div class="text-secondary small fw-bold">DASH PRODUÇÃO</div>
            <div class="h5 fw-bold mb-0">Resumo por hora (OP e KG)</div>
          </div>
          <div class="d-flex gap-2 align-items-end">
            <div>
              <label class="form-label fw-bold mb-1">DATA</label>
              <input type="date" class="form-control fw-bold" id="dash-date" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
            </div>
            <button class="btn btn-primary fw-bold" id="dash-refresh" type="button">ATUALIZAR</button>
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-12 col-md-6">
            <div class="card border-0" style="background: rgba(37, 99, 235, .10);">
              <div class="card-body p-3">
                <div class="text-secondary fw-bold">TOTAL OP</div>
                <div class="display-6 fw-bold mb-0" id="dash-total-ops">0</div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="card border-0" style="background: rgba(79, 70, 229, .10);">
              <div class="card-body p-3">
                <div class="text-secondary fw-bold">TOTAL KG (SAÍDA)</div>
                <div class="display-6 fw-bold mb-0" id="dash-total-kg">0</div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <div id="dash-status" class="text-secondary small fw-bold"></div>
          <div class="row g-2 mt-2" id="dash-grid"></div>
        </div>
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

<script>
  (function () {
    const dateEl = document.getElementById('dash-date');
    const btn = document.getElementById('dash-refresh');
    const grid = document.getElementById('dash-grid');
    const st = document.getElementById('dash-status');
    const totOps = document.getElementById('dash-total-ops');
    const totKg = document.getElementById('dash-total-kg');
    if (!dateEl || !btn || !grid) return;

    function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function fmtKg(v){
      const n = Number(v || 0);
      return n.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
    }

    async function load() {
      const d = (dateEl.value || '').trim();
      st.textContent = 'Carregando dash...';
      grid.innerHTML = '';
      try {
        const res = await fetch('<?= htmlspecialchars(\Core\Http::url('/api/dash-producao')) ?>' + '?date=' + encodeURIComponent(d));
        const data = await res.json();
        if (!data || !data.ok) throw new Error('Falha');

        totOps.textContent = String(data.total_ops ?? 0);
        totKg.textContent = fmtKg(data.total_kg ?? 0);
        st.textContent = 'Atualizado.';

        const hours = Array.isArray(data.hours) ? data.hours : [];
        grid.innerHTML = hours.map(h => {
          const ops = Number(h.ops || 0);
          const kg = Number(h.kg || 0);
          const bg = (ops > 0 || kg > 0) ? 'rgba(16,185,129,.12)' : 'rgba(148,163,184,.14)';
          return `
            <div class="col-6 col-md-4 col-lg-3">
              <div class="card border-0" style="background:${bg}">
                <div class="card-body p-3">
                  <div class="fw-bold">${esc(h.label)}</div>
                  <div class="d-flex justify-content-between mt-2">
                    <div>
                      <div class="text-secondary small fw-bold">OP</div>
                      <div class="h5 fw-bold mb-0">${esc(ops)}</div>
                    </div>
                    <div class="text-end">
                      <div class="text-secondary small fw-bold">KG</div>
                      <div class="h5 fw-bold mb-0">${esc(fmtKg(kg))}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;
        }).join('');
      } catch (e) {
        st.textContent = 'Erro ao carregar o dash.';
        grid.innerHTML = '<div class="text-danger fw-bold">Erro ao carregar dados.</div>';
      }
    }

    btn.addEventListener('click', load);
    dateEl.addEventListener('change', load);
    load();
  })();
</script>

