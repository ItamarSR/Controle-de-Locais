<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Op;
use App\Models\User;
use Core\Http;
use Core\View;

final class OpController extends BaseController
{
    public function form(): void
    {
        $this->requireRole(['conferencia']);
        $u = (new User())->findById((int)($_SESSION['user_id'] ?? 0));

        echo View::render('conferencia/op_form', [
            'title' => 'Cadastrar OP',
            'responsavel' => $u,
            'data_auto' => date('d/m/Y H:i'),
        ]);
    }

    public function apiStatus(string $op): void
    {
        $this->requireRole(['conferencia']);
        header('Content-Type: application/json; charset=utf-8');

        $op = strtoupper(trim($op));
        if ($op === '') {
            echo json_encode(['ok' => false, 'error' => 'op_vazia'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $count = (new Op())->countByOp($op);
        $next = $count > 0 ? $count : 0;
        $label = $count > 0 ? "{$next}º REACERTO" : "ORIGINAL";

        echo json_encode(['ok' => true, 'op' => $op, 'count' => $count, 'next_reacerto' => $next, 'label' => $label], JSON_UNESCAPED_UNICODE);
    }

    public function insert(): void
    {
        $this->requireRole(['conferencia']);

        $op = strtoupper(trim((string)($_POST['op'] ?? '')));
        $entrada = trim((string)($_POST['entrada'] ?? ''));
        $oleo = trim((string)($_POST['oleo'] ?? ''));
        $saida = trim((string)($_POST['saida'] ?? ''));
        $cor = trim((string)($_POST['cor'] ?? ''));
        $obs = trim((string)($_POST['obs'] ?? ''));
        $retem = (int)($_POST['retem'] ?? 0) === 1 ? 1 : 0;

        $confirm = (string)($_POST['confirm_reacerto'] ?? '');
        $confirm = $confirm === 'sim' ? 'sim' : ($confirm === 'nao' ? 'nao' : '');

        if ($op === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Informe a OP.'];
            Http::redirect('/conferencia/op');
        }

        $opModel = new Op();
        $count = $opModel->countByOp($op);

        if ($count > 0 && $confirm === '') {
            // Força decisão no formulário
            $u = (new User())->findById((int)($_SESSION['user_id'] ?? 0));
            echo View::render('conferencia/op_form', [
                'title' => 'Cadastrar OP',
                'responsavel' => $u,
                'data_auto' => date('d/m/Y H:i'),
                'prefill' => compact('op', 'entrada', 'oleo', 'saida', 'cor', 'obs', 'retem'),
                'op_exists' => true,
                'reacerto_label' => ($count) . 'º REACERTO',
                'reacerto_next' => $count,
            ]);
            return;
        }

        if ($count > 0 && $confirm === 'nao') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Essa OP já foi inserida.'];
            Http::redirect('/conferencia/op');
        }

        $reacerto = $count > 0 ? $count : 0;
        if ($count > 0 && $confirm === 'sim') {
            // ok, segue
        }

        $ok = $opModel->insert([
            'op' => $op,
            'entrada' => $entrada !== '' ? $entrada : null,
            'oleo' => $oleo !== '' ? $oleo : null,
            'saida' => $saida !== '' ? $saida : null,
            'cor' => $cor !== '' ? $cor : null,
            'reacerto' => $reacerto,
            'obs' => $obs !== '' ? $obs : null,
            'retem' => $retem,
            'responsavel_usuario_id' => (int)($_SESSION['user_id'] ?? 0),
        ]);

        if (!$ok) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível inserir a OP (verifique se a tabela ops existe no banco).'];
            Http::redirect('/conferencia/op');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'OP inserida com sucesso.'];
        Http::redirect('/conferencia/op/consulta');
    }

    public function consulta(): void
    {
        $this->requireRole(['conferencia']);
        $q = trim((string)($_GET['q'] ?? ''));
        $rows = (new Op())->search($q !== '' ? $q : null);
        echo View::render('conferencia/op_consulta', ['title' => 'Consultar OP', 'rows' => $rows, 'q' => $q]);
    }
}

