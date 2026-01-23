<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Op;
use App\Models\User;
use Core\Http;
use Core\View;

final class OpController extends BaseController
{
    private const EMB_PESO_KG = 0.050; // 50g por embalagem (0,050 kg)

    private function parseBrNumber(string $s): ?float
    {
        $s = trim($s);
        if ($s === '') return null;
        // remove separador de milhar (.) e troca decimal (,) por (.)
        $s = str_replace([' ', "\u{00A0}"], '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        if (!is_numeric($s)) return null;
        return (float)$s;
    }

    private function formatBrNumber(float $v): string
    {
        // 3 casas para suportar 32,220
        return number_format($v, 3, ',', '');
    }

    public function form(): void
    {
        $this->requireRole(['conferencia', 'admin', 'editorpro']);
        $u = (new User())->findById((int)($_SESSION['user_id'] ?? 0));

        echo View::render('conferencia/op_form', [
            'title' => 'Cadastrar OP',
            'responsavel' => $u,
            'data_auto' => date('d/m/Y H:i'),
        ]);
    }

    public function apiStatus(string $op): void
    {
        $this->requireRole(['conferencia', 'admin', 'editorpro']);
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
        $this->requireRole(['conferencia', 'admin', 'editorpro']);

        $op = strtoupper(trim((string)($_POST['op'] ?? '')));
        $entrada = trim((string)($_POST['entrada'] ?? ''));
        $oleo = trim((string)($_POST['oleo'] ?? ''));
        $saida = trim((string)($_POST['saida'] ?? ''));
        $qtdeEmb = trim((string)($_POST['qtde_emb'] ?? ''));
        $cor = trim((string)($_POST['cor'] ?? ''));
        $obs = trim((string)($_POST['obs'] ?? ''));
        $retem = (int)($_POST['retem'] ?? 0) === 1 ? 1 : 0;

        $confirm = (string)($_POST['confirm_reacerto'] ?? '');
        $confirm = $confirm === 'sim' ? 'sim' : ($confirm === 'nao' ? 'nao' : '');

        if ($op === '' || $entrada === '' || $oleo === '' || $saida === '' || $cor === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha OP, Entrada, Óleo, Saída e Cor.'];
            Http::redirect('/conferencia/op');
        }
        if ($retem !== 1) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'O campo RETÉM é obrigatório (selecione SIM).'];
            Http::redirect('/conferencia/op');
        }

        $vEntrada = $this->parseBrNumber($entrada);
        $vOleo = $this->parseBrNumber($oleo);
        $vSaida = $this->parseBrNumber($saida);
        if ($vEntrada === null || $vOleo === null || $vSaida === null) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Entrada, Óleo e Saída devem ser números válidos.'];
            Http::redirect('/conferencia/op');
        }

        // Qtde Emb: obrigatório apenas quando ENTRADA > 100kg
        $qtdeEmbInt = null;
        if ($qtdeEmb !== '') {
            if (!ctype_digit($qtdeEmb)) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Qtde Emb deve ser um número inteiro.'];
                Http::redirect('/conferencia/op');
            }
            $qtdeEmbInt = (int)$qtdeEmb;
            if ($qtdeEmbInt < 0 || $qtdeEmbInt > 999999) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Qtde Emb inválida.'];
                Http::redirect('/conferencia/op');
            }
        } elseif ($vEntrada > 100) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Qtde Emb é obrigatório quando ENTRADA é maior que 100kg.'];
            Http::redirect('/conferencia/op');
        }

        $totalEmbKgNum = ($qtdeEmbInt !== null ? ($qtdeEmbInt * self::EMB_PESO_KG) : 0.0);
        $totalEmbKg = $qtdeEmbInt !== null ? $this->formatBrNumber($totalEmbKgNum) : null;

        // Regra: SAÍDA deve descontar o TOTAL EMB (KG)
        $vSaidaLiquida = $vSaida - $totalEmbKgNum;

        $soma = $vEntrada + $vOleo;
        // Desperdício: SAÍDA - (ENTRADA + ÓLEO) (pode ser negativo)
        $desperdicio = $vSaidaLiquida - $soma;
        $despStr = $this->formatBrNumber($desperdicio);

        // Regra: se |desperdício| > 0,400, OBS obrigatório
        if (abs($desperdicio) > 0.400 && $obs === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'OBS é obrigatório quando o desperdício é maior que 0,400 (positivo ou negativo).'];
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
                'prefill' => [
                    'op' => $op,
                    'entrada' => $entrada,
                    'oleo' => $oleo,
                    'saida' => $saida,
                    'qtde_emb' => $qtdeEmb,
                    'cor' => $cor,
                    'obs' => $obs,
                    'retem' => $retem,
                ],
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
            'entrada' => $this->formatBrNumber($vEntrada),
            'oleo' => $this->formatBrNumber($vOleo),
            'saida' => $this->formatBrNumber($vSaidaLiquida),
            'qtde_emb' => $qtdeEmbInt,
            'total_emb_kg' => $totalEmbKg,
            'desperdicio' => $despStr,
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
        $this->requireRole(['conferencia', 'admin', 'editorpro']);
        $q = trim((string)($_GET['q'] ?? ''));
        $rows = (new Op())->search($q !== '' ? $q : null);
        echo View::render('conferencia/op_consulta', ['title' => 'Consultar OP', 'rows' => $rows, 'q' => $q]);
    }
}

