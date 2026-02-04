<?php
// scripts/smoke_tests.php — testes "smoke" rápidos que não exigem Composer/DB
// Execução: php scripts/smoke_tests.php
// Objetivo: validar rota em subdiretório, pontos de entrada e fallback CSV (sanity checks)

declare(strict_types=1);

$results = [];

function ok(string $name, bool $cond, string $msg = ''): void {
    global $results;
    $results[] = ['name' => $name, 'ok' => $cond, 'msg' => $msg];
    $status = $cond ? "OK" : "FAIL";
    echo "[$status] $name" . ($msg ? " — $msg" : "") . PHP_EOL;
}

// --- helper: router logic (copied from index.php/public/index.php)
function resolve_path(string $scriptName, string $requestUri): string {
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $requestPath = parse_url($requestUri, PHP_URL_PATH);
    if ($basePath !== '' && strpos($requestPath, $basePath) === 0) {
        $requestPath = substr($requestPath, strlen($basePath));
    }
    $path = trim($requestPath, '/');
    if ($path === 'index.php' || $path === 'index.php/') {
        $path = '';
    }
    return $path;
}

// Test 1: subdirectory routing
$path = resolve_path('/almoxarifado/index.php', '/almoxarifado/');
ok('routing: subdir root maps to empty path', $path === '', "got '".$path."'");
$path2 = resolve_path('/almoxarifado/index.php', '/almoxarifado/admin/dashboard');
ok('routing: subdir admin/dashboard -> admin/dashboard', $path2 === 'admin/dashboard', $path2);

// Test 2: entrypoints reference current front controller/autoloader
$rootIndex = file_get_contents(__DIR__ . '/../index.php');
$publicIndex = file_get_contents(__DIR__ . '/../public/index.php');

$rootForwardsToPublic = (strpos($rootIndex, "public/index.php") !== false);
$publicHasAutoloadFallback = (strpos($publicIndex, "src/autoload_fallback.php") !== false);
ok('entrypoint: root forwards to public/index.php', $rootForwardsToPublic, 'expected require of public/index.php');
ok('entrypoint: public uses src/autoload_fallback.php', $publicHasAutoloadFallback, 'expected require of src/autoload_fallback.php');

// Test 3: import view accepts CSV and documents fallback
$importView = file_get_contents(__DIR__ . '/../views/admin/importacao/form.php');
$hasAcceptCsv = stripos($importView, 'accept=".csv') !== false || stripos($importView, "accept='.csv") !== false;
$hasAcceptXls = stripos($importView, '.xls') !== false;
$hasAcceptXlsx = stripos($importView, '.xlsx') !== false;
ok('view: import-excel accepts .csv', $hasAcceptCsv);
ok('view: import-excel accepts .xls/.xlsx', $hasAcceptXls && $hasAcceptXlsx);
$hasCsvNote = stripos($importView, 'CSV') !== false;
ok('view: import-excel documents CSV', $hasCsvNote);

// Test 4: ImportacaoController contains CSV parser (fgetcsv)
$importController = file_get_contents(__DIR__ . '/../src/App/Controllers/ImportacaoController.php');
$hasFgetcsv = strpos($importController, 'fgetcsv') !== false;
$hasCsvBranch = (strpos($importController, "\$ext === 'csv'") !== false) || (strpos($importController, "\$ext === \"csv\"") !== false);
ok('controller: ImportController implements CSV parsing', $hasFgetcsv && $hasCsvBranch);

// Summary
$failed = array_filter($results, fn($r) => !$r['ok']);
if (count($failed) === 0) {
    echo PHP_EOL . "All smoke tests passed ✅" . PHP_EOL;
    exit(0);
} else {
    echo PHP_EOL . count($failed) . " smoke test(s) failed ⚠️" . PHP_EOL;
    foreach ($failed as $f) {
        echo " - {$f['name']}" . ($f['msg'] ? " ({$f['msg']})" : "") . PHP_EOL;
    }
    exit(2);
}
