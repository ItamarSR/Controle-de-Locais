<?php
// src/controllers/ImportController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/MateriaPrima.php';
require_once __DIR__ . '/../models/Usuario.php';

// Composer/vendor removido — usamos o autoloader local em `src/autoload.php`.
// A biblioteca PhpSpreadsheet é opcional; quando ausente a funcionalidade de importação degrada gentilmente.

use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            redirect('/login?erro=nao_autenticado');
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorId($_SESSION['user_id']);
        if (!$usuario || !in_array($usuario['nivel_acesso'], ['editor', 'editorpro', 'admin'])) {
            redirect('/login?erro=acesso_negado');
        }
    }

    public function index() {
        $erros = [];
        $sucesso = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_FILES['excel']) || !is_array($_FILES['excel'])) {
                $erros[] = "Arquivo não enviado.";
            } else {
                $file = $_FILES['excel'];
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $erros[] = "Falha no upload do arquivo (código " . (int)($file['error'] ?? -1) . ").";
                } else {
                    $tmpPath = $file['tmp_name'] ?? '';
                    $originalName = $file['name'] ?? '';
                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    $mpModel = new MateriaPrima();
                    $importados = 0;

                    if ($extension === 'csv') {
                        $handle = fopen($tmpPath, 'r');
                        if (!$handle) {
                            $erros[] = "Não foi possível abrir o CSV enviado.";
                        } else {
                            // Detecta delimitador simples (',' ou ';') olhando as primeiras linhas.
                            $sample = '';
                            $pos = ftell($handle);
                            for ($i = 0; $i < 5 && !feof($handle); $i++) {
                                $line = fgets($handle);
                                if ($line !== false) {
                                    $sample .= $line;
                                }
                            }
                            fseek($handle, $pos);

                            $comma = substr_count($sample, ',');
                            $semi = substr_count($sample, ';');
                            $delimiter = ($semi > $comma) ? ';' : ',';

                            $rowNum = 0;
                            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                                $rowNum++;
                                // Ignora linhas 1..6 (a importação começa na linha 7).
                                if ($rowNum < 7) continue;

                                $codigo = trim((string)($row[0] ?? ''));
                                $nome = trim((string)($row[1] ?? ''));
                                if ($codigo === '' && $nome === '') continue;
                                if ($codigo === '' || $nome === '') {
                                    $erros[] = "Linha {$rowNum}: Código e Nome são obrigatórios.";
                                    continue;
                                }

                                if ($mpModel->importar(['codigo_mp' => $codigo, 'nome_mp' => $nome])) {
                                    $importados++;
                                } else {
                                    $erros[] = "Linha {$rowNum}: Falha ao importar '{$codigo} — {$nome}'.";
                                }
                            }
                            fclose($handle);
                        }
                    } elseif (in_array($extension, ['xls', 'xlsx'], true)) {
                        // XLS/XLSX requer PhpSpreadsheet (opcional).
                        if (!class_exists(IOFactory::class)) {
                            $erros[] = "Importação de XLS/XLSX indisponível no servidor. Envie um CSV ou instale PhpSpreadsheet via Composer.";
                        } else {
                            try {
                                $spreadsheet = IOFactory::load($tmpPath);
                                $sheet = $spreadsheet->getActiveSheet();
                                $highestRow = (int)$sheet->getHighestRow();

                                for ($row = 7; $row <= $highestRow; $row++) {
                                    $codigo = trim((string)$sheet->getCell('A' . $row)->getValue());
                                    $nome = trim((string)$sheet->getCell('B' . $row)->getValue());
                                    if ($codigo === '' && $nome === '') continue;
                                    if ($codigo === '' || $nome === '') {
                                        $erros[] = "Linha {$row}: Código e Nome são obrigatórios.";
                                        continue;
                                    }
                                    if ($mpModel->importar(['codigo_mp' => $codigo, 'nome_mp' => $nome])) {
                                        $importados++;
                                    } else {
                                        $erros[] = "Linha {$row}: Falha ao importar '{$codigo} — {$nome}'.";
                                    }
                                }
                            } catch (Throwable $e) {
                                $erros[] = "Falha ao ler planilha: " . $e->getMessage();
                            }
                        }
                    } else {
                        $erros[] = "Formato não suportado. Envie .csv, .xls ou .xlsx.";
                    }

                    if (empty($erros) && $importados > 0) {
                        $sucesso = true;
                    } elseif (empty($erros) && $importados === 0) {
                        $erros[] = "Nenhuma linha válida foi importada (verifique se os dados começam na linha 7).";
                    }
                }
            }
        }

        require __DIR__ . '/../views/admin/import-excel.php';
    }
}