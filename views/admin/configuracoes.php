<?php
$title = 'Configurações';
$nivel = $nivel ?? (string)($_SESSION['nivel'] ?? '');
$settings = $settings ?? [];
$settings_ok = $settings_ok ?? true;
$settings_error = $settings_error ?? null;

$printPt = (int)($settings['print_text_pt'] ?? 22);
$printerProfile = (string)($settings['printer_profile'] ?? 'bematech');
$printOffsetX = (float)($settings['print_offset_x_mm'] ?? 0);
$printOffsetY = (float)($settings['print_offset_y_mm'] ?? 0);
$printScale = (float)($settings['print_scale'] ?? 1);
$dashMetaOpStep = (int)($settings['dash_meta_op_step'] ?? 4);
$dashMetaKgStep = (float)($settings['dash_meta_kg_step'] ?? 500);
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
            Dica: comece com <b>22</b>. Para corrigir centralização (ex.: Elgin), ajuste X/Y em mm.
          </div>
        </div>
      </div>

      <hr class="my-4">

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Perfil da impressora</label>
          <select class="form-select" name="printer_profile">
            <option value="bematech" <?= $printerProfile === 'bematech' ? 'selected' : '' ?>>Bematech</option>
            <option value="elgin" <?= $printerProfile === 'elgin' ? 'selected' : '' ?>>Elgin</option>
          </select>
          <div class="form-text">Use “Elgin” para aplicar ajustes de centralização.</div>
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">Deslocamento X (mm)</label>
          <input type="number" step="0.1" class="form-control" name="print_offset_x_mm" value="<?= htmlspecialchars((string)$printOffsetX) ?>">
          <div class="form-text">Ex.: 2.0 para direita; -2.0 para esquerda.</div>
        </div>

        <div class="col-6 col-md-4">
          <label class="form-label fw-bold">Deslocamento Y (mm)</label>
          <input type="number" step="0.1" class="form-control" name="print_offset_y_mm" value="<?= htmlspecialchars((string)$printOffsetY) ?>">
          <div class="form-text">Ex.: 2.0 para baixo; -2.0 para cima.</div>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Escala</label>
          <input type="number" step="0.01" class="form-control" name="print_scale" value="<?= htmlspecialchars((string)$printScale) ?>">
          <div class="form-text">Normal: 1.00. Ajuste fino: 0.95–1.05.</div>
        </div>
      </div>

      <hr class="my-4">

      <h2 class="h6 fw-bold mb-3">Dash Produção (metas)</h2>
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Meta OP por hora</label>
          <input type="number" class="form-control" name="dash_meta_op_step" min="0" max="9999" value="<?= (int)$dashMetaOpStep ?>">
          <div class="form-text">Usada para calcular a coluna META OP (cumulativa por hora).</div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Meta KG por hora</label>
          <input type="number" step="0.1" class="form-control" name="dash_meta_kg_step" min="0" max="999999" value="<?= htmlspecialchars((string)$dashMetaKgStep) ?>">
          <div class="form-text">Usada para calcular a coluna META KG (cumulativa por hora).</div>
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

