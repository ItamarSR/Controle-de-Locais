<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MateriaPrima;
use Core\Http;
use Core\View;

final class ImportacaoController extends BaseController
{
    private function ensureVendorAutoload(): void
    {
        // Runtime não depende do Composer, mas para XLS/XLSX podemos carregar se existir.
        $vendor = dirname(__DIR__, 3) . '/vendor/autoload.php';
        if (file_exists($vendor)) {
            require_once $vendor;
        }
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
            $this->ensureVendorAutoload();
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Importação XLS/XLSX indisponível neste servidor. Envie um arquivo CSV, ou instale o vendor (composer install).'];
                Http::redirect('/admin/importacao');
            }

            try {
                $reader = $ext === 'xls'
                    ? new \PhpOffice\PhpSpreadsheet\Reader\Xls()
                    : new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($tmp);
                $sheet = $spreadsheet->getActiveSheet();

                $rowNum = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNum = $row->getRowIndex();
                    if ($rowNum < 7) continue; // começa na linha 7

                    $codigo = trim((string)$sheet->getCell('A' . $rowNum)->getValue());
                    $nomeMp = trim((string)$sheet->getCell('B' . $rowNum)->getValue());
                    if ($codigo === '' && $nomeMp === '') continue;
                    if ($mpModel->upsert($codigo, $nomeMp)) $importados++; else $erros++;
                }
            } catch (\Throwable $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Falha ao ler o Excel. Tente salvar como CSV e importar novamente.'];
                Http::redirect('/admin/importacao');
            }
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Formato inválido. Envie CSV, XLS ou XLSX.' ];
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

