<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "health=ok\n";
echo "php_version=" . PHP_VERSION . "\n";
echo "php_version_id=" . PHP_VERSION_ID . "\n";
echo "pdo_mysql=" . (extension_loaded('pdo_mysql') ? 'yes' : 'no') . "\n";
echo "gd=" . (extension_loaded('gd') ? 'yes' : 'no') . "\n";
echo "zip=" . (extension_loaded('zip') ? 'yes' : 'no') . "\n";
echo "mbstring=" . (extension_loaded('mbstring') ? 'yes' : 'no') . "\n";
echo "intl=" . (extension_loaded('intl') ? 'yes' : 'no') . "\n";

$vendor = __DIR__ . '/../vendor/autoload.php';
echo "vendor_autoload_path=" . $vendor . "\n";
echo "vendor_autoload_exists=" . (file_exists($vendor) ? 'yes' : 'no') . "\n";

if (PHP_VERSION_ID >= 80100 && file_exists($vendor)) {
    require_once $vendor;
    echo "vendor_autoload_loaded=yes\n";
    echo "phpspreadsheet_iofactory=" . (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class) ? 'yes' : 'no') . "\n";
} else {
    echo "vendor_autoload_loaded=no\n";
    echo "phpspreadsheet_iofactory=unknown\n";
}

