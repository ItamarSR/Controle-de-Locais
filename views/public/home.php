<?php
$title = 'Locais cadastrados';
$locais = $locais ?? [];
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

  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-3 p-md-4">
        <?php if (!$locais): ?>
          <div class="alert alert-info mb-0">Nenhum local cadastrado.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Local</th>
                  <th>MP</th>
                  <th class="text-end">Etiqueta</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($locais as $l): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($l['nome_local']) ?></td>
                  <td>
                    <span class="badge text-bg-light border"><?= htmlspecialchars($l['codigo_mp']) ?></span>
                    <div class="text-secondary small"><?= htmlspecialchars($l['nome_mp']) ?></div>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-primary" target="_blank" href="<?= htmlspecialchars(\Core\Http::url('/etiqueta/' . $l['id'])) ?>">
                      Imprimir etiqueta
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

