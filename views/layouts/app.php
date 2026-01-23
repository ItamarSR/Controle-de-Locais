<?php
use Core\Http;
use App\Models\Settings;

$title = $title ?? 'Almoxarifado';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Tema (Admin)
$themePage = '#f6f7fb';
$themeHeader = '#ffffff';
$themeFooter = '#ffffff';
try {
  $s = new Settings();
  $themePage = (string)($s->get('theme_page', $themePage) ?? $themePage);
  $themeHeader = (string)($s->get('theme_header', $themeHeader) ?? $themeHeader);
  $themeFooter = (string)($s->get('theme_footer', $themeFooter) ?? $themeFooter);
} catch (Throwable $e) {
  // sem DB/config: mantém defaults
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= htmlspecialchars(Http::url('/assets/app.css')) ?>" rel="stylesheet">
</head>
<body class="bg-body-tertiary" style="--theme-page: <?= htmlspecialchars($themePage) ?>; --theme-header: <?= htmlspecialchars($themeHeader) ?>; --theme-footer: <?= htmlspecialchars($themeFooter) ?>;">
  <nav class="navbar navbar-expand-lg border-bottom sticky-top" style="background: var(--theme-header);">
    <div class="container">
      <a class="navbar-brand fw-bold" href="<?= htmlspecialchars(Http::url('/')) ?>">Almoxarifado</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto gap-2">
          <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::url('/')) ?>">Público</a></li>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <?php $nivel = (string)($_SESSION['nivel'] ?? ''); ?>
            <?php if (in_array($nivel, ['conferencia', 'admin', 'editorpro'], true)): ?>
              <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::url('/conferencia')) ?>">Conferência</a></li>
            <?php endif; ?>
            <?php if ($nivel !== 'conferencia'): ?>
              <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::url('/admin/dashboard')) ?>">Painel</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link text-danger" href="<?= htmlspecialchars(Http::url('/logout')) ?>">Sair</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="btn btn-primary btn-sm px-3" href="<?= htmlspecialchars(Http::url('/login')) ?>">Entrar</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </main>

  <footer class="border-top" style="background: var(--theme-footer);">
    <div class="container py-3 small text-secondary fw-bold">
      Controle de Locais e Inventário
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= htmlspecialchars(Http::url('/assets/app.js')) ?>"></script>
</body>
</html>

