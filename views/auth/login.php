<?php
$title = 'Login';
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-1">Entrar</h1>
        <p class="text-secondary mb-4">Acesse o painel administrativo.</p>

        <form method="post" action="<?= htmlspecialchars(\Core\Http::url('/login')) ?>" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
            <div class="invalid-feedback">Informe um email válido.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="senha" class="form-control" required minlength="6">
            <div class="invalid-feedback">Informe sua senha.</div>
          </div>
          <button class="btn btn-primary w-100" type="submit">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>

