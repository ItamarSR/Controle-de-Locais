<?php
declare(strict_types=1);

// Front controller na raiz (quando o webroot NÃO é /public).
// Encaminha para o novo sistema (router em public/index.php).
require __DIR__ . '/public/index.php';
