<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MateriaPrima;
use Core\Http;
use Core\View;

final class ImportacaoController extends BaseController
{
    private function tryLoadVendorAutoload(): bool
    {
        // Tenta carregar autoload do Composer (alguns deploys colocam o webroot na raiz ou em /public).
        $root = dirname(__DIR__, 4);
        $candidates = [
            $root . '/vendor/autoload.php',
            $root . '/public/vendor/autoload.php',
        ];

        foreach ($candidates as $file) {
            if (is_string($file) && $file !== '' && file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        return false;
    }

    public function form(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);
        echo View::render('admin/importacao/form', ['title' => 'Importar MPs']);
    }

    public function import(): void
    {
        $this->requireRole(['editor', 'editorpro', 'admin']);

        if (!isset($_FILES['arquivo']) || !is_array($_FILES['arquivo'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Envie um arquivo.'];
            Http::redirect('/admin/importacao');
        }

        $f = $_FILES['arquivo'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Falha no upload do arquivo.'];
            Http::redirect('/admin/importacao');
        }

        $name = (string)($f['name'] ?? '');
        $tmp = (string)($f['tmp_name'] ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $mpModel = new MateriaPrima();
        $importados = 0;
        $erros = 0;

        if ($ext === 'csv') {
            $h = fopen($tmp, 'r');
            if (!$h) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível abrir o CSV.'];
                Http::redirect('/admin/importacao');
            }

            // detecta delimitador (',' ou ';')
            $sample = '';
            $pos = ftell($h);
            for ($i = 0; $i < 5 && !feof($h); $i++) {
                $line = fgets($h);
                if ($line !== false) $sample .= $line;
            }
            fseek($h, $pos);
            $delimiter = (substr_count($sample, ';') > substr_count($sample, ',')) ? ';' : ',';

            $rowNum = 0;
            while (($row = fgetcsv($h, 0, $delimiter)) !== false) {
                $rowNum++;
                if ($rowNum < 7) continue; // começa na linha 7
                $codigo = trim((string)($row[0] ?? ''));
                $nomeMp = trim((string)($row[1] ?? ''));
                if ($codigo === '' && $nomeMp === '') continue;
                if ($mpModel->upsert($codigo, $nomeMp)) $importados++; else $erros++;
            }
            fclose($h);
        } elseif (in_array($ext, ['xlsx', 'xls'], true)) {
            $hasVendorAutoload = $this->tryLoadVendorAutoload();
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                $msg = 'Importação XLS/XLSX indisponível: biblioteca PhpSpreadsheet não encontrada.';
                if (!$hasVendorAutoload) {
                    $msg .= ' Não encontrei o arquivo vendor/autoload.php no servidor.';
                } else {
                    $msg .= ' O autoload foi encontrado, mas o pacote pode não estar instalado (verifique vendor/phpoffice/phpspreadsheet).';
                }
                $msg .= ' Solução: rode "composer install" no servidor (na raiz do projeto) ou envie CSV.';
                $_SESSION['flash'] = ['type' => 'danger', 'message' => $msg];
                Http::redirect('/admin/importacao');
            }

            $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp)->getActiveSheet();
            $max = (int)$sheet->getHighestRow();
            for ($r = 7; $r <= $max; $r++) {
                $codigo = trim((string)$sheet->getCell('A' . $r)->getValue());
                $nomeMp = trim((string)$sheet->getCell('B' . $r)->getValue());
                if ($codigo === '' && $nomeMp === '') continue;
                if ($mpModel->upsert($codigo, $nomeMp)) $importados++; else $erros++;
            }
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Formato inválido. Envie CSV, XLS ou XLSX.'];
            Http::redirect('/admin/importacao');
        }

        if ($importados === 0) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Nenhuma linha foi importada (verifique se os dados começam na linha 7).'];
        } else {
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Importação concluída: {$importados} registros. Erros: {$erros}."];
        }

        Http::redirect('/admin/importacao');
    }
}

