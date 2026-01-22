<?php
$title = 'Locais cadastrados';
?>

<div class="row g-3 align-items-stretch">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
          <div>
            <h1 class="h4 mb-1">Consulta pública de locais</h1>
            <p class="text-secondary mb-0">Liste locais e imprima etiqueta BOPP 100×60.</p>
          </div>
          <a class="btn btn-outline-primary" href="<?= htmlspecialchars(\Core\Http::url('/login')) ?>">Área administrativa</a>
        </div>
      </div>
    </div>
  </div>
</div>

