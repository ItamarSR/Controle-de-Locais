<?php
$title = 'Configurações';
$nivel = $nivel ?? (string)($_SESSION['nivel'] ?? '');
$settings = $settings ?? [];
$settings_ok = $settings_ok ?? true;
$settings_error = $settings_error ?? null;

$printPt = (int)($settings['print_text_pt'] ?? 22);
$themePage = (string)($settings['theme_page'] ?? '#f6f7fb');
$themeHeader = (string)($settings['theme_header'] ?? '#ffffff');
$themeFooter = (string)($settings['theme_footer'] ?? '#ffffff');
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1 fw-bold">Configurações</h1>
    <div class="text-secondary small fw-bold">Ajustes de impressão e aparência.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/dashboard')) ?>">Voltar</a>
</div>

<?php if (!$settings_ok): ?>
  <div class="alert alert-warning">
    <div class="fw-bold">Configurações ainda não estão ativas no banco.</div>
    <div class="small">Atualize o banco executando o arquivo <code>sql/schema.sql</code> (ele cria a tabela <code>configuracoes</code>).</div>
    <?php if (is_string($settings_error) && $settings_error !== ''): ?>
      <div class="small text-secondary mt-2">Detalhe: <code><?= htmlspecialchars($settings_error) ?></code></div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars(\Core\Http::url('/admin/configuracoes')) ?>" class="needs-validation" novalidate>
  <div class="card shadow-sm mb-3">
    <div class="card-body p-4">
      <h2 class="h6 fw-bold mb-3">Impressão (Admin / EditorPro)</h2>
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Tamanho do texto (pt)</label>
          <input type="number" class="form-control" name="print_text_pt" min="10" max="40" required value="<?= (int)$printPt ?>">
          <div class="form-text">Afeta o tamanho do texto da etiqueta na impressora.</div>
        </div>
        <div class="col-12 col-md-8">
          <div class="alert alert-info mb-0">
            Dica: comece com <b>22</b> e ajuste conforme a etiqueta BOPP 100×60.
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($nivel === 'admin'): ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body p-4">
      <h2 class="h6 fw-bold mb-3">Tema (somente Admin)</h2>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Cor da página</label>
          <input type="color" class="form-control form-control-color w-100" name="theme_page" value="<?= htmlspecialchars($themePage) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Cor do cabeçalho</label>
          <input type="color" class="form-control form-control-color w-100" name="theme_header" value="<?= htmlspecialchars($themeHeader) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Cor do rodapé</label>
          <input type="color" class="form-control form-control-color w-100" name="theme_footer" value="<?= htmlspecialchars($themeFooter) ?>">
        </div>
      </div>
      <div class="form-text mt-2">As cores serão aplicadas no sistema inteiro.</div>
    </div>
  </div>
  <?php endif; ?>

  <button class="btn btn-primary fw-bold" type="submit">Salvar configurações</button>
</form>

