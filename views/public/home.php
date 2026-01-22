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

