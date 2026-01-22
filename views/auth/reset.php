<?php
$title = 'Trocar senha';
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-1">Trocar senha</h1>
        <p class="text-secondary mb-4">Primeiro acesso: defina uma nova senha.</p>

        <form method="post" action="<?= htmlspecialchars(\Core\Http::url('/reset-senha')) ?>" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label">Nova senha</label>
            <input type="password" name="senha" class="form-control" required minlength="6">
            <div class="invalid-feedback">Mínimo 6 caracteres.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar senha</label>
            <input type="password" name="confirm" class="form-control" required minlength="6">
            <div class="invalid-feedback">Confirme a senha.</div>
          </div>
          <button class="btn btn-primary w-100" type="submit">Salvar</button>
        </form>
      </div>
    </div>
  </div>
</div>

