<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Local;
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
}

