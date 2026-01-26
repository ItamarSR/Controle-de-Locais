<?php
$title = 'Dashboard';
$nivel = (string)($_SESSION['nivel'] ?? '');
// Dados de conexão (para Power BI)
$dbHost = \Core\Env::getString('DB_HOST', 'localhost') ?? 'localhost';
$dbPort = \Core\Env::getString('DB_PORT', '3306') ?? '3306';
$dbName = \Core\Env::getString('DB_NAME', 'sistemas_almox') ?? 'sistemas_almox';
$dbUser = \Core\Env::getString('DB_USER', 'sistemas_master') ?? 'sistemas_master';
$dbPass = \Core\Env::getString('DB_PASS', '3yOQZ;v2j5O8.e') ?? '';

// Sanitiza host (remove https://, path e porta de cPanel por engano)
$hostSan = $dbHost;
if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*://#', $hostSan)) {
  $u = parse_url($hostSan);
  if (is_array($u) && !empty($u['host'])) $hostSan = (string)$u['host'];
}
// remove path e porta caso ainda exista (ex.: domain:2083/cpanel)
$hostSan = preg_replace('#/.*$#', '', (string)$hostSan);
$hostSan = preg_replace('#:\d+$#', '', (string)$hostSan);

$dbServer = $hostSan . ($dbPort !== '' ? (':' . $dbPort) : '');
$pbiConnName = $dbServer . ';' . $dbName; // formato do Power BI (Gateway): servidor:porta;banco
?>

<div class="row g-3">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-1">Painel administrativo</h1>
        <p class="text-secondary mb-0">Nível: <span class="badge text-bg-light border"><?= htmlspecialchars(strtoupper($nivel)) ?></span></p>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/admin/locais')) ?>">
      <div class="card-body p-4">
        <div class="fw-semibold">Locais</div>
        <div class="text-secondary small">Cadastrar, editar e remover locais</div>
      </div>
    </a>
  </div>

  <div class="col-12 col-md-6">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/admin/importacao')) ?>">
      <div class="card-body p-4">
        <div class="fw-semibold">Importar MPs</div>
        <div class="text-secondary small">Importar via CSV (colunas A/B, a partir da linha 7)</div>
      </div>
    </a>
  </div>

  <?php if (in_array($nivel, ['editorpro', 'admin'], true)): ?>
  <div class="col-12">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/admin/usuarios')) ?>">
      <div class="card-body p-4">
        <div class="fw-semibold">Usuários</div>
        <div class="text-secondary small">Criar e gerenciar usuários (Editor/EditorPro)</div>
      </div>
    </a>
  </div>

  <div class="col-12">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/conferencia')) ?>">
      <div class="card-body p-4">
        <div class="fw-semibold">Conferência</div>
        <div class="text-secondary small">Cadastro e consulta de OP</div>
      </div>
    </a>
  </div>

  <div class="col-12">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/admin/configuracoes')) ?>">
      <div class="card-body p-4">
        <div class="fw-semibold">Configurações</div>
        <div class="text-secondary small">Impressão (tamanho do texto) e tema (Admin)</div>
      </div>
    </a>
  </div>
  <?php endif; ?>

  <?php if ($nivel === 'admin'): ?>
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
          <div class="fw-semibold">Conexão do Banco (Power BI)</div>
          <div class="text-secondary small fw-bold">Copie e cole no Power BI (MySQL).</div>
        </div>

        <div class="row g-2">
          <div class="col-12">
            <label class="form-label small fw-bold mb-1">Nome da conexão (Power BI / Gateway)</label>
            <input class="form-control form-control-sm fw-bold" readonly value="<?= htmlspecialchars($pbiConnName) ?>">
            <div class="form-text">
              Use exatamente assim no campo <b>“Nome da conexão”</b>: <b>servidor:porta;banco</b>.
              Não use <b>https://</b> e não use a porta do cPanel (ex.: <b>2083</b>).
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold mb-1">Servidor</label>
            <input class="form-control form-control-sm fw-bold" readonly value="<?= htmlspecialchars($dbServer) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold mb-1">Banco</label>
            <input class="form-control form-control-sm fw-bold" readonly value="<?= htmlspecialchars($dbName) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold mb-1">Usuário</label>
            <input class="form-control form-control-sm fw-bold" readonly value="<?= htmlspecialchars($dbUser) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold mb-1 d-flex align-items-center justify-content-between">
              <span>Senha</span>
              <button class="btn btn-outline-secondary btn-sm fw-bold py-0 px-2" type="button" id="btn-pass">Mostrar</button>
            </label>
            <input class="form-control form-control-sm fw-bold" id="db-pass" type="password" readonly value="<?= htmlspecialchars($dbPass) ?>">
          </div>
        </div>

        <div class="form-text mt-2">
          Power BI Desktop: <b>Obter Dados</b> → <b>Banco de dados MySQL</b> → Servidor = <b><?= htmlspecialchars($dbServer) ?></b>, Banco = <b><?= htmlspecialchars($dbName) ?></b>.
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php if ($nivel === 'admin'): ?>
<script>
  (function(){
    const btn = document.getElementById('btn-pass');
    const inp = document.getElementById('db-pass');
    if (!btn || !inp) return;
    btn.addEventListener('click', () => {
      const isHidden = inp.getAttribute('type') === 'password';
      inp.setAttribute('type', isHidden ? 'text' : 'password');
      btn.textContent = isHidden ? 'Ocultar' : 'Mostrar';
    });
  })();
</script>
<?php endif; ?>

