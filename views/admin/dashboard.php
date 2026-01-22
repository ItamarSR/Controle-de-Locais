<?php
$title = 'Dashboard';
$nivel = (string)($_SESSION['nivel'] ?? '');
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
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/admin/configuracoes')) ?>">
      <div class="card-body p-4">
        <div class="fw-semibold">Configurações</div>
        <div class="text-secondary small">Impressão (tamanho do texto) e tema (Admin)</div>
      </div>
    </a>
  </div>
  <?php endif; ?>
</div>

