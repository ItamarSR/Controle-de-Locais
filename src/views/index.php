<?php
// src/views/public/index.php

require_once __DIR__ . '/../../../config/database.php';

$pdo = getConnection();
$stmt = $pdo->query("
    SELECT l.id, l.nome_local, mp.codigo_mp, mp.nome_mp
    FROM locais l
    JOIN materias_primas mp ON l.mp_id = mp.id
    ORDER BY l.nome_local ASC
");
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Pública de Locais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page { size: 100mm 60mm; margin: 0; }
            body { margin: 0; padding: 0; }
            .etiqueta {
                width: 100mm;
                height: 60mm;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                font-family: Arial, sans-serif;
                font-size: 18pt;
                text-align: center;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="container py-4">
    <h1 class="mb-4">Locais Cadastrados</h1>
    <div class="list-group">
        <?php if (empty($locais)): ?>
            <p class="text-muted">Nenhum local cadastrado.</p>
        <?php else: ?>
            <?php foreach ($locais as $local): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($local['nome_local']) ?></strong><br>
                        <small><?= htmlspecialchars($local['codigo_mp']) ?> - <?= htmlspecialchars($local['nome_mp']) ?></small>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="imprimirEtiqueta(<?= $local['id'] ?>)">Imprimir Etiqueta</button>
                </div>
                <div id="etq-<?= $local['id'] ?>" class="etiqueta no-print d-none">
                    <strong><?= htmlspecialchars($local['nome_local']) ?></strong>
                    <p><?= htmlspecialchars($local['codigo_mp']) ?> - <?= htmlspecialchars($local['nome_mp']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function imprimirEtiqueta(id) {
        const etiqueta = document.getElementById('etq-' + id).cloneNode(true);
        etiqueta.classList.remove('d-none', 'no-print');
        const win = window.open('', '', 'width=400,height=200');
        win.document.write('<html><head><title>Etiqueta</title><style>@media print {@page { size: 100mm 60mm; margin: 0; } body { margin: 0; } .etiqueta { width: 100mm; height: 60mm; display: flex; flex-direction: column; justify-content: center; align-items: center; font-family: Arial; font-size: 18pt; text-align: center; }}</style></head><body class="etiqueta">' + etiqueta.innerHTML + '</body></html>');
        win.document.close();
        win.print();
        win.close();
    }
    </script>
</body>
</html>