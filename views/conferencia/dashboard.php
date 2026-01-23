<?php
$title = 'Conferência';
?>

<div class="row g-3">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-1 fw-bold">Painel de Conferência</h1>
        <p class="text-secondary fw-bold mb-0">Cadastro e consulta de OP (Ordem de Produção).</p>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/conferencia/op')) ?>">
      <div class="card-body p-4">
        <div class="fw-bold">Cadastrar OP</div>
        <div class="text-secondary small fw-bold">Inserir OP com controle de Reacerto</div>
      </div>
    </a>
  </div>

  <div class="col-12 col-md-6">
    <a class="card shadow-sm text-decoration-none" href="<?= htmlspecialchars(\Core\Http::url('/conferencia/op/consulta')) ?>">
      <div class="card-body p-4">
        <div class="fw-bold">Consultar OP</div>
        <div class="text-secondary small fw-bold">Pesquisar e visualizar lançamentos</div>
      </div>
    </a>
  </div>
</div>

