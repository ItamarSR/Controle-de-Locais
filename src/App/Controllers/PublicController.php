<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Local;
use App\Models\Op;
use App\Models\Settings;
use Core\Http;
use Core\View;

final class PublicController
{
    public function index(): void
    {
        echo View::render('public/home', ['title' => 'Consulta pública']);
    }

    public function powerbi(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $per = (int)($_GET['per'] ?? 200);
        if ($page < 1) $page = 1;
        if ($per < 50) $per = 50;
        if ($per > 1000) $per = 1000;
        $offset = ($page - 1) * $per;

        $opModel = new Op();
        try {
            $total = $opModel->countAll();
            $rows = $opModel->listPaged($offset, $per);
        } catch (\Throwable $e) {
            $total = 0;
            $rows = [];
        }

        echo View::render('public/powerbi', [
            'title' => 'PowerBI - OPs',
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per' => $per,
        ]);
    }

    public function apiPowerbiOps(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $page = (int)($_GET['page'] ?? 1);
        $per = (int)($_GET['per'] ?? 500);
        if ($page < 1) $page = 1;
        if ($per < 1) $per = 1;
        if ($per > 1000) $per = 1000;
        $offset = ($page - 1) * $per;

        try {
            $opModel = new Op();
            $total = $opModel->countAll();
            $rows = $opModel->listPaged($offset, $per);
            echo json_encode(['ok' => true, 'page' => $page, 'per' => $per, 'total' => $total, 'rows' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => 'db_error'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function etiqueta(string $id): void
    {
        $local = (new Local())->findWithMp((int)$id);
        if (!$local) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => '/etiqueta/' . $id]);
            return;
        }

        // etiqueta é uma página simples de impressão (sem layout)
        require dirname(__DIR__, 3) . '/views/public/etiqueta.php';
    }

    public function apiConsulta(string $codigo): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $codigo = trim($codigo);
        if ($codigo === '') {
            echo json_encode(['ok' => false, 'error' => 'codigo_vazio'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $rows = (new Local())->consultaPublicaPorCodigo($codigo);
        if (!$rows) {
            echo json_encode(['ok' => true, 'codigo' => $codigo, 'descricao' => null, 'itens' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $descricao = (string)($rows[0]['nome_mp'] ?? '');
        $itens = array_map(function (array $r): array {
            return [
                'id' => (int)$r['id'],
                'codigo' => (string)($r['codigo_mp'] ?? ''),
                'local' => (string)($r['nome_local'] ?? ''),
                'descricao' => (string)($r['nome_mp'] ?? ''),
                'data' => (string)($r['data_cadastro'] ?? ''),
                'responsavel' => (string)($r['responsavel_nome'] ?? ''),
                'etiqueta_url' => Http::url('/etiqueta/' . (int)$r['id']),
            ];
        }, $rows);

        echo json_encode([
            'ok' => true,
            'codigo' => $codigo,
            'descricao' => $descricao,
            'itens' => $itens,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function apiDashProducao(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $date = (string)($_GET['date'] ?? date('Y-m-d'));
        $date = trim($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $stats = (new Op())->statsByHour($date);

        // Metas (configuráveis) - diárias
        $metaOpDaily = 96;
        $metaKgDaily = 12000.0;
        try {
            $set = new Settings();
            $metaOpDaily = (int)($set->get('dash_meta_op_daily', (string)$metaOpDaily) ?? $metaOpDaily);
            $metaKgDaily = (float)($set->get('dash_meta_kg_daily', (string)$metaKgDaily) ?? $metaKgDaily);

            // fallback de compatibilidade (bases antigas): step * 24
            if ($metaOpDaily <= 0) {
                $step = (int)($set->get('dash_meta_op_step', '4') ?? 4);
                $metaOpDaily = max(0, $step) * 24;
            }
            if ($metaKgDaily <= 0) {
                $stepKg = (float)($set->get('dash_meta_kg_step', '500') ?? 500);
                $metaKgDaily = max(0.0, $stepKg) * 24.0;
            }
        } catch (\Throwable $e) {
        }
        if ($metaOpDaily < 0) $metaOpDaily = 0;
        if ($metaKgDaily < 0) $metaKgDaily = 0.0;

        $totalOps = 0;
        $totalKg = 0.0;
        foreach ($stats as $s) {
            $totalOps += (int)$s['ops'];
            $totalKg += (float)$s['kg'];
        }

        echo json_encode([
            'ok' => true,
            'date' => $date,
            'total_ops' => $totalOps,
            'total_kg' => round($totalKg, 3),
            'meta_op_daily' => $metaOpDaily,
            'meta_kg_daily' => round($metaKgDaily, 3),
            'hours' => array_map(function (array $h): array {
                $hh = (int)$h['hour'];
                return [
                    'hour' => $hh,
                    'inicio' => str_pad((string)$hh, 2, '0', STR_PAD_LEFT) . ':00:00',
                    'fim' => str_pad((string)(($hh + 1) % 24), 2, '0', STR_PAD_LEFT) . ':00:00',
                    'ops' => (int)$h['ops'],
                    'kg' => round((float)$h['kg'], 3),
                ];
            }, $stats),
        ], JSON_UNESCAPED_UNICODE);
    }
}

