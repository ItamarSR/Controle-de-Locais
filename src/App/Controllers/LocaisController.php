<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Local;
use App\Models\MateriaPrima;
use App\Models\User;
use Core\Http;
use Core\View;

final class LocaisController extends BaseController
{
    private function buildLocaisSlots(): array
    {
        $slots = [];
        for ($i = 3; $i <= 46; $i++) {
            foreach (['A', 'B', 'C'] as $l) {
                $slots[] = $i . $l;
            }
        }

        $counts = (new Local())->countsByNomeLocal($slots);
        $out = [];
        foreach ($slots as $s) {
            $c = (int)($counts[$s] ?? 0);
            $nivel = $c > 8 ? 'danger' : ($c >= 5 ? 'warning' : 'success');
            $out[] = ['nome' => $s, 'count' => $c, 'nivel' => $nivel];
        }
        return $out;
    }

    public function apiByCodigo(string $codigo): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        header('Content-Type: application/json; charset=utf-8');

        $codigo = trim($codigo);
        if ($codigo === '') {
            echo json_encode(['ok' => false, 'error' => 'codigo_vazio'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $mp = (new MateriaPrima())->findByCodigo($codigo);
        if (!$mp) {
            echo json_encode(['ok' => true, 'mp' => null, 'locais' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $locais = (new Local())->listByMpId((int)$mp['id']);
        echo json_encode([
            'ok' => true,
            'mp' => ['id' => (int)$mp['id'], 'codigo_mp' => (string)$mp['codigo_mp'], 'nome_mp' => (string)$mp['nome_mp']],
            'locais' => array_map(fn($l) => ['id' => (int)$l['id'], 'nome_local' => (string)$l['nome_local']], $locais),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function index(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $q = trim((string)($_GET['q'] ?? ''));
        $locais = (new Local())->listAdmin($q !== '' ? $q : null);
        echo View::render('admin/locais/index', ['title' => 'Locais', 'locais' => $locais, 'q' => $q]);
    }

    public function createForm(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $u = (new User())->findById((int)($_SESSION['user_id'] ?? 0));
        echo View::render('admin/locais/form', [
            'title' => 'Novo local',
            'responsavel' => $u,
            'data_auto' => date('d/m/Y H:i'),
            'slots' => $this->buildLocaisSlots(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $codigo = strtoupper(trim((string)($_POST['codigo_mp'] ?? '')));
        $nomeLocal = strtoupper(trim((string)($_POST['nome_local'] ?? '')));
        $force = (int)($_POST['force'] ?? 0) === 1;

        if ($codigo === '' || $nomeLocal === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha Código e Local.'];
            Http::redirect('/admin/locais/novo');
        }

        $mp = (new MateriaPrima())->findByCodigo($codigo);
        if (!$mp) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código não encontrado na base de MPs. Faça a importação de MPs (CSV) e tente novamente.'];
            Http::redirect('/admin/locais/novo');
        }

        $localModel = new Local();
        $existentes = $localModel->listByMpId((int)$mp['id']);
        if (!$force && !empty($existentes)) {
            // Mostra aviso e permite confirmar
            $u = (new User())->findById((int)($_SESSION['user_id'] ?? 0));
            echo View::render('admin/locais/form', [
                'title' => 'Novo local',
                'codigo_mp' => $codigo,
                'descricao_mp' => (string)$mp['nome_mp'],
                'nome_local' => $nomeLocal,
                'dup_locais' => $existentes,
                'responsavel' => $u,
                'data_auto' => date('d/m/Y H:i'),
                'slots' => $this->buildLocaisSlots(),
            ]);
            return;
        }

        if (!$localModel->create($nomeLocal, (int)$mp['id'], (int)($_SESSION['user_id'] ?? 0))) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível salvar o local.'];
            Http::redirect('/admin/locais/novo');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Local cadastrado com sucesso. Você pode cadastrar outro.'];
        Http::redirect('/admin/locais/novo');
    }

    public function editForm(string $id): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $local = (new Local())->findForForm((int)$id);
        if (!$local) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => '/admin/locais/' . $id . '/editar']);
            return;
        }
        $u = (new User())->findById((int)($_SESSION['user_id'] ?? 0));
        echo View::render('admin/locais/form', [
            'title' => 'Editar local',
            'local' => $local,
            'codigo_mp' => (string)$local['codigo_mp'],
            'descricao_mp' => (string)$local['nome_mp'],
            'nome_local' => (string)$local['nome_local'],
            'responsavel' => $u,
            'data_auto' => date('d/m/Y H:i'),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $codigo = strtoupper(trim((string)($_POST['codigo_mp'] ?? '')));
        $nomeLocal = strtoupper(trim((string)($_POST['nome_local'] ?? '')));
        $mp = (new MateriaPrima())->findByCodigo($codigo);
        $mpId = (int)($mp['id'] ?? 0);

        if ($codigo === '' || $nomeLocal === '' || $mpId <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha Código e Local.'];
            Http::redirect('/admin/locais/' . $id . '/editar');
        }

        if (!(new Local())->update((int)$id, $nomeLocal, $mpId)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível atualizar o local.'];
            Http::redirect('/admin/locais/' . $id . '/editar');
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Local atualizado com sucesso.'];
        Http::redirect('/admin/locais');
    }

    public function delete(string $id): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        $ok = (new Local())->delete((int)$id);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Local excluído.']
            : ['type' => 'danger', 'message' => 'Não foi possível excluir o local.'];
        Http::redirect('/admin/locais');
    }
}

