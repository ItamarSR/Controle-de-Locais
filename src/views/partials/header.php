<?php
// src/views/partials/header.php

if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <style>
        body { font-family: Arial, sans-serif; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">Controle de Estoque</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/admin/locais">Locais</a></li>
                    <?php if (isset($_SESSION['nivel']) && in_array($_SESSION['nivel'], ['editorpro', 'admin'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/usuarios">Usuários</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/admin/import-excel">Importar Excel</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="/logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>