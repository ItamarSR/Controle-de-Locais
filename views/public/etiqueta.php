<?php
// Página de impressão (sem layout).
// Requisito: etiqueta BOPP 100x60mm.
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
    .nome { font-size: 22pt; font-weight: 700; line-height: 1.05; margin-bottom: 6mm; }
    .mp { font-size: 16pt; font-weight: 700; }
    .mp small { display:block; font-weight: 400; font-size: 10pt; margin-top: 2mm; }
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

