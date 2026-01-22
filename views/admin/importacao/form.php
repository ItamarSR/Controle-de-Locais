<?php
$title = 'Importar MPs';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1">Importar MPs</h1>
    <div class="text-secondary small">Lê colunas A (código) e B (nome) a partir da linha 7.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/dashboard')) ?>">Voltar</a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form method="post" action="<?= htmlspecialchars(\Core\Http::url('/admin/importacao')) ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
      <div class="mb-3">
        <label class="form-label">Arquivo (.xlsx, .xls ou .csv)</label>
        <input class="form-control" type="file" name="arquivo" required accept=".xlsx,.xls,.csv">
        <div class="invalid-feedback">Selecione um arquivo.</div>
        <div class="form-text">
          Para XLS/XLSX, o servidor precisa do Composer com o pacote <code>phpoffice/phpspreadsheet</code> (ou seja, <code>vendor/autoload.php</code> + <code>vendor/phpoffice/phpspreadsheet</code>).
          Se não tiver, envie CSV (delimitador “;” ou “,”).
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Importar</button>
    </form>
  </div>
</div>

