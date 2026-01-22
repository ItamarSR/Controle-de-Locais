<?php
// Página de impressão (sem layout).
// Requisito: etiqueta BOPP 100x60mm.
use App\Models\Settings;

$pt = 22;
try {
  $s = new Settings();
  $pt = (int)($s->get('print_text_pt', (string)$pt) ?? $pt);
} catch (Throwable $e) {}

if ($pt < 10) $pt = 10;
if ($pt > 40) $pt = 40;

// Requisito: LOCAL (nome_local) deve sair grande; demais seguem o tamanho configurado.
$ptNome = max(28, min(44, $pt + 10));
$ptMp = $pt;
$ptSmall = max(8, $pt - 10);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Etiqueta</title>
  <style>
    @page { size: 100mm 60mm; margin: 0; }
    html, body { margin: 0; padding: 0; }
    .etq {
      width: 100mm;
      height: 60mm;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-family: Arial, sans-serif;
      text-align: center;
      box-sizing: border-box;
      padding: 6mm;
    }
    .nome { font-size: <?= (int)$ptNome ?>pt; font-weight: 700; line-height: 1.05; margin-bottom: 6mm; }
    .mp { font-size: <?= (int)$ptMp ?>pt; font-weight: 700; }
    .mp small { display:block; font-weight: 400; font-size: <?= (int)$ptSmall ?>pt; margin-top: 2mm; }
  </style>
</head>
<body onload="window.print()">
  <div class="etq">
    <div class="nome"><?= htmlspecialchars($local['nome_local']) ?></div>
    <div class="mp">
      MP: <?= htmlspecialchars($local['codigo_mp']) ?>
      <small><?= htmlspecialchars($local['nome_mp']) ?></small>
    </div>
  </div>
</body>
</html>

