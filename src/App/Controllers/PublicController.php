<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Local;
use App\Models\Op;
use Core\Http;
use Core\View;

final class PublicController
{
    public function index(): void
    {
        echo View::render('public/home', ['title' => 'Consulta pública']);
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
            'hours' => array_map(function (array $h): array {
                $hh = (int)$h['hour'];
                return [
                    'hour' => $hh,
                    'label' => str_pad((string)$hh, 2, '0', STR_PAD_LEFT) . ':00–' . str_pad((string)$hh, 2, '0', STR_PAD_LEFT) . ':59',
                    'ops' => (int)$h['ops'],
                    'kg' => round((float)$h['kg'], 3),
                ];
            }, $stats),
        ], JSON_UNESCAPED_UNICODE);
    }
}

