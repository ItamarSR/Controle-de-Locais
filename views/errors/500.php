<?php
$title = $title ?? 'Erro';
?>
<div class="card shadow-sm border-danger-subtle">
  <div class="card-body p-4">
    <h1 class="h4 mb-2">O sistema encontrou um erro</h1>
    <p class="text-secondary mb-0"><?= htmlspecialchars($message ?? 'Erro inesperado') ?></p>
  </div>
</div>

