<?php
// src/views/public/index.php
// Página pública (dashboard) — compatível com rota em public/index.php

require_once __DIR__ . '/../partials/header.php';

// Carrega locais (consulta simples)
$pdo = getConnection();
$stmt = $pdo->query("SELECT l.id, l.nome_local, mp.codigo_mp, mp.nome_mp
    FROM locais l
    JOIN materias_primas mp ON l.mp_id = mp.id
    ORDER BY l.nome_local");
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<style>
    .hero-locais {
        background: linear-gradient(90deg, #e3f0ff 0%, #f8fbff 100%);
        border-radius: 1.5rem;
        padding: 2.5rem 1.5rem 2rem 1.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 2px 16px 0 rgba(80,120,180,0.07);
        text-align: center;
    }
    .hero-locais h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #2a4d7a;
        margin-bottom: 0.5rem;
    }
    .hero-locais p {
        color: #4a6a8a;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    .hero-locais .print-info {
        color: #3570c7;
        font-size: 0.98rem;
        margin-top: 0.7rem;
        background: #eaf3ff;
        border-radius: 0.7rem;
        padding: 0.5rem 1rem;
        display: inline-block;
    }
    .locais-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    .local-card {
        background: #fff;
        border-radius: 1.1rem;
        box-shadow: 0 2px 12px 0 rgba(80,120,180,0.08);
        padding: 1.5rem 1.2rem 1.2rem 1.2rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: box-shadow 0.2s;
        border: 1px solid #e6eaf2;
    }
    .local-card:hover {
        box-shadow: 0 4px 24px 0 rgba(80,120,180,0.13);
        border-color: #b3c6e6;
    }
    .local-nome {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2a4d7a;
        margin-bottom: 0.2rem;
    }
    .local-mp {
        color: #3570c7;
        font-size: 1.18rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 0.7rem;
        background: #eaf3ff;
        border-radius: 0.5rem;
        padding: 0.2rem 0.7rem;
        display: inline-block;
    }
    .btn-etiqueta {
        background: linear-gradient(90deg, #4f8cff 0%, #6ad1ff 100%);
        color: #fff;
        border: none;
        border-radius: 2rem;
        font-weight: 500;
        font-size: 1rem;
        padding: 0.5rem 1.3rem;
        box-shadow: 0 1px 4px 0 rgba(80,120,180,0.10);
        transition: background 0.2s, box-shadow 0.2s;
        margin-right: 0.5rem;
    }
    .btn-etiqueta:hover {
        background: linear-gradient(90deg, #3570c7 0%, #3db6e6 100%);
        color: #fff;
        box-shadow: 0 2px 8px 0 rgba(80,120,180,0.16);
    }
    .btn-pdf {
        background: linear-gradient(90deg, #ffb84f 0%, #ffd36a 100%);
        color: #2a4d7a;
        border: none;
        border-radius: 2rem;
        font-weight: 500;
        font-size: 1rem;
        padding: 0.5rem 1.3rem;
        box-shadow: 0 1px 4px 0 rgba(80,120,180,0.10);
        transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-pdf:hover {
        background: linear-gradient(90deg, #e6a13d 0%, #ffe6b3 100%);
        color: #2a4d7a;
        box-shadow: 0 2px 8px 0 rgba(80,120,180,0.16);
    }
    @media (max-width: 600px) {
        .hero-locais { padding: 1.2rem 0.5rem 1.2rem 0.5rem; }
        .local-card { padding: 1rem 0.7rem 1rem 0.7rem; }
    }
    /* Etiqueta impressa */
    @media print {
        body { background: #fff !important; }
        .etiqueta {
            width: 100mm;
            height: 60mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            font-size: 20pt;
            text-align: center;
            border: 2px dashed #3570c7;
            margin: 0 auto;
            background: #f8fbff;
            color: #2a4d7a;
        }
        .etiqueta .nome {
            font-size: 2.1rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
            color: #2a4d7a;
        }
        .etiqueta .mp {
            font-size: 1.3rem;
            font-weight: 600;
            color: #3570c7;
            background: #eaf3ff;
            border-radius: 0.5rem;
            padding: 0.2rem 1.2rem;
            display: inline-block;
        }
    }
</style>

<div class="container py-4">

    <div class="hero-locais">
        <h1>Locais Cadastrados</h1>
        <p>Consulte os locais disponíveis e imprima etiquetas personalizadas.<br>Visual moderno, responsivo e fácil de usar.</p>
        <div class="print-info">
            <strong>Impressão de Etiquetas:</strong> Formato BOPP 100×60mm, compatível com Bematech/Elgin.<br>
            <span style="color:#2a4d7a">Use o botão <b>Imprimir Etiqueta</b> para gerar a etiqueta pronta para impressão.<br>Para PDF, ative o recurso Dompdf.</span>
        </div>
    </div>

    <?php if (empty($locais)): ?>
        <div class="alert alert-info text-center">Nenhum local cadastrado.</div>
    <?php endif; ?>

    <div class="locais-grid">
        <?php foreach ($locais as $local): ?>
            <div class="local-card">
                <div>
                    <div class="local-nome">🏷️ <?= htmlspecialchars($local['nome_local']) ?></div>
                    <div class="local-mp">MP: <?= htmlspecialchars($local['codigo_mp']) ?> — <?= htmlspecialchars($local['nome_mp']) ?></div>
                </div>
                <div class="d-flex">
                    <button class="btn btn-etiqueta mt-2" onclick="imprimirEtiqueta(<?= $local['id'] ?>)">Imprimir Etiqueta</button>
                    <button class="btn btn-pdf mt-2" onclick="alert('Recurso PDF disponível ao ativar Dompdf no servidor.')" title="Exportar PDF" type="button">PDF</button>
                </div>
            </div>

            <div id="etq-<?= $local['id'] ?>" class="etiqueta d-none">
                <strong class="nome"><?= htmlspecialchars($local['nome_local']) ?></strong>
                <div class="mp">MP: <?= htmlspecialchars($local['codigo_mp']) ?> — <?= htmlspecialchars($local['nome_mp']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function imprimirEtiqueta(id) {
    const div = document.getElementById('etq-' + id);
    const win = window.open('', '_blank');
    win.document.write(`
        <html>
        <head>
            <title>Etiqueta</title>
            <style>@media print {@page { size: 100mm 60mm; margin: 0; } body { margin: 0; } .etiqueta { width: 100mm; height: 60mm; display:flex; flex-direction:column; justify-content:center; align-items:center; font-family: Arial, sans-serif; font-size:18pt; text-align:center; }}</style>
        </head>
        <body class="etiqueta">${div.innerHTML}</body>
        </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>