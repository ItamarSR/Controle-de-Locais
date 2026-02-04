<?php
$title = $title ?? 'Usuário';
$user = $user ?? null;

$action = $user
  ? \Core\Http::url('/admin/usuarios/' . $user['id'] . '/editar')
  : \Core\Http::url('/admin/usuarios/novo');
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1"><?= htmlspecialchars($user ? 'Editar usuário' : 'Novo usuário') ?></h1>
    <div class="text-secondary small">Admin só pode ser criado via banco (admin inicial).</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/usuarios')) ?>">Voltar</a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="needs-validation" novalidate>
      <div class="mb-3">
        <label class="form-label">Nome</label>
        <input class="form-control" name="nome" required value="<?= htmlspecialchars($user['nome'] ?? '') ?>">
        <div class="invalid-feedback">Informe o nome.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        <div class="invalid-feedback">Informe um email válido.</div>
      </div>

      <div class="mb-3">
        <label class="form-label"><?= $user ? 'Nova senha (opcional)' : 'Senha' ?></label>
        <input class="form-control" type="password" name="senha" <?= $user ? '' : 'required' ?> minlength="6">
        <div class="invalid-feedback">Senha deve ter no mínimo 6 caracteres.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Nível de acesso</label>
        <select class="form-select" name="nivel_acesso" required>
          <option value="editor" <?= (($user['nivel_acesso'] ?? '') === 'editor') ? 'selected' : '' ?>>Editor</option>
          <option value="editorpro" <?= (($user['nivel_acesso'] ?? '') === 'editorpro') ? 'selected' : '' ?>>EditorPro</option>
          <option value="conferencia" <?= (($user['nivel_acesso'] ?? '') === 'conferencia') ? 'selected' : '' ?>>Conferência</option>
        </select>
      </div>

      <?php if ($user): ?>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="1" <?= ((int)($user['status'] ?? 1) === 1) ? 'selected' : '' ?>>Ativo</option>
          <option value="0" <?= ((int)($user['status'] ?? 1) === 0) ? 'selected' : '' ?>>Inativo</option>
        </select>
      </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/admin/usuarios')) ?>">Cancelar</a>
      </div>
    </form>
  </div>
</div>

