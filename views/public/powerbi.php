<?php
$title = $title ?? 'PowerBI';
$rows = $rows ?? [];
$total = (int)($total ?? 0);
$page = (int)($page ?? 1);
$per = (int)($per ?? 200);
if ($page < 1) $page = 1;
if ($per < 50) $per = 50;
if ($per > 1000) $per = 1000;
$pages = $per > 0 ? (int)max(1, (int)ceil($total / $per)) : 1;
$prev = max(1, $page - 1);
$next = min($pages, $page + 1);
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
  <div>
    <h1 class="h4 mb-1 fw-bold">PowerBI - OPs</h1>
    <div class="text-secondary small fw-bold">Lista pública das OPs lançadas no banco (paginação).</div>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <a class="btn btn-outline-secondary btn-sm fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/powerbi')) ?>">Atualizar</a>
    <a class="btn btn-outline-secondary btn-sm fw-bold" target="_blank" href="<?= htmlspecialchars(\Core\Http::url('/api/powerbi/ops?per=1000&page=1')) ?>">API JSON</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div class="small fw-bold text-secondary">
        Total: <span class="badge text-bg-light border"><?= (int)$total ?></span>
        | Página <?= (int)$page ?> / <?= (int)$pages ?>
      </div>
      <form method="get" action="<?= htmlspecialchars(\Core\Http::url('/powerbi')) ?>" class="d-flex gap-2 align-items-center">
        <label class="small fw-bold text-secondary">Por página</label>
        <select class="form-select form-select-sm fw-bold" name="per" onchange="this.form.submit()">
          <?php foreach ([200, 500, 1000] as $opt): ?>
            <option value="<?= (int)$opt ?>" <?= $per === $opt ? 'selected' : '' ?>><?= (int)$opt ?></option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="page" value="1">
      </form>
    </div>

    <?php if (!$rows): ?>
      <div class="text-secondary fw-bold">Nenhuma OP encontrada.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="fw-bold">DATA/HORA</th>
              <th class="fw-bold">OP</th>
              <th class="fw-bold text-end">ENTRADA</th>
              <th class="fw-bold text-end">ÓLEO</th>
              <th class="fw-bold text-end">SAÍDA</th>
              <th class="fw-bold text-end">QTDE EMB</th>
              <th class="fw-bold text-end">TOTAL EMB</th>
              <th class="fw-bold text-end">DESPERDÍCIO</th>
              <th class="fw-bold">COR</th>
              <th class="fw-bold">REACERTO</th>
              <th class="fw-bold">RETÉM</th>
              <th class="fw-bold">RESPONSÁVEL</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td class="text-secondary small fw-bold"><?= htmlspecialchars(isset($r['criado_em']) ? date('d/m/Y H:i', strtotime((string)$r['criado_em'])) : '') ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['op'] ?? '')) ?></td>
                <td class="text-end fw-bold"><?= htmlspecialchars((string)($r['entrada'] ?? '')) ?></td>
                <td class="text-end fw-bold"><?= htmlspecialchars((string)($r['oleo'] ?? '')) ?></td>
                <td class="text-end fw-bold"><?= htmlspecialchars((string)($r['saida'] ?? '')) ?></td>
                <td class="text-end fw-bold"><?= htmlspecialchars((string)($r['qtde_emb'] ?? '')) ?></td>
                <td class="text-end fw-bold"><?= htmlspecialchars((string)($r['total_emb_kg'] ?? '')) ?></td>
                <td class="text-end fw-bold"><?= htmlspecialchars((string)($r['desperdicio'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['cor'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['reacerto'] ?? '')) ?></td>
                <td class="fw-bold"><?= ((int)($r['retem'] ?? 0) === 1) ? 'SIM' : '' ?></td>
                <td class="text-secondary small fw-bold"><?= htmlspecialchars((string)($r['responsavel_nome'] ?? '')) ?></td>
              </tr>
              <?php if (!empty($r['obs'])): ?>
                <tr>
                  <td colspan="12" class="small text-secondary"><b>OBS:</b> <?= htmlspecialchars((string)$r['obs']) ?></td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="d-flex align-items-center justify-content-between mt-3">
        <a class="btn btn-outline-secondary btn-sm fw-bold <?= $page <= 1 ? 'disabled' : '' ?>"
           href="<?= htmlspecialchars(\Core\Http::url('/powerbi?page=' . $prev . '&per=' . $per)) ?>">Anterior</a>
        <div class="small text-secondary fw-bold">Página <?= (int)$page ?> / <?= (int)$pages ?></div>
        <a class="btn btn-outline-secondary btn-sm fw-bold <?= $page >= $pages ? 'disabled' : '' ?>"
           href="<?= htmlspecialchars(\Core\Http::url('/powerbi?page=' . $next . '&per=' . $per)) ?>">Próxima</a>
      </div>
    <?php endif; ?>
  </div>
</div>

