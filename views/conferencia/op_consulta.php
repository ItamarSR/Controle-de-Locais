<?php
$title = 'Consultar OP';
$rows = $rows ?? [];
$q = $q ?? '';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h1 class="h4 mb-1 fw-bold">Consultar / Pesquisar OP</h1>
    <div class="text-secondary small fw-bold">Mostrando os últimos lançamentos (até 500).</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(\Core\Http::url('/conferencia')) ?>">Voltar</a>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-body p-3 p-md-4">
    <form class="row g-2 align-items-end" method="get" action="<?= htmlspecialchars(\Core\Http::url('/conferencia/op/consulta')) ?>">
      <div class="col-12 col-md-6">
        <label class="form-label fw-bold">Pesquisar OP</label>
        <input class="form-control upper fw-bold" name="q" value="<?= htmlspecialchars((string)$q) ?>" placeholder="EX: 12345">
      </div>
      <div class="col-12 col-md-6">
        <button class="btn btn-primary fw-bold" type="submit">PESQUISAR</button>
        <a class="btn btn-outline-secondary fw-bold" href="<?= htmlspecialchars(\Core\Http::url('/conferencia/op')) ?>">NOVO</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="fw-bold">OP</th>
            <th class="fw-bold">ENTRADA</th>
            <th class="fw-bold">ÓLEO</th>
            <th class="fw-bold">SAÍDA</th>
            <th class="fw-bold">QTDE EMB</th>
            <th class="fw-bold">TOTAL EMB</th>
            <th class="fw-bold">DESPERDÍCIO</th>
            <th class="fw-bold">COR</th>
            <th class="fw-bold">REACERTO</th>
            <th class="fw-bold">RETÉM</th>
            <th class="fw-bold">RESPONSÁVEL</th>
            <th class="fw-bold">DATA/HORA</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="12" class="text-secondary fw-bold p-4">NENHUM REGISTRO.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td class="fw-bold"><?= htmlspecialchars((string)$r['op']) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['entrada'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['oleo'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['saida'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['qtde_emb'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['total_emb_kg'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['desperdicio'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['cor'] ?? '')) ?></td>
                <td class="fw-bold">
                  <?= ((int)($r['reacerto'] ?? 0) > 0) ? htmlspecialchars((string)$r['reacerto']) . 'º' : 'ORIGINAL' ?>
                </td>
                <td class="fw-bold"><?= ((int)($r['retem'] ?? 0) === 1) ? 'SIM' : 'NÃO' ?></td>
                <td class="fw-bold"><?= htmlspecialchars((string)($r['responsavel_nome'] ?? '')) ?></td>
                <td class="fw-bold"><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$r['criado_em']))) ?></td>
              </tr>
              <?php if (!empty($r['obs'])): ?>
                <tr>
                  <td colspan="12" class="px-3 pb-3 text-secondary fw-bold">
                    OBS: <?= htmlspecialchars((string)$r['obs']) ?>
                  </td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

