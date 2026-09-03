<?php

declare(strict_types=1);

use App\Config\Database;

require_once dirname(__DIR__) . '/src/Config/Database.php';

$databaseStatus = 'indisponível';
$statusClass = 'status--error';
$httpStatus = 503;

try {
    Database::connection()->query('SELECT 1');
    $databaseStatus = 'conectado';
    $statusClass = 'status--success';
    $httpStatus = 200;
} catch (\Throwable) {
    // A página continua acessível para facilitar o diagnóstico do ambiente.
}

http_response_code($httpStatus);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CardVault - Configuração</title>
    <link rel="stylesheet" href="/assets/css/setup.css">
</head>
<body>
    <main class="setup-card">
        <p class="eyebrow">CardVault</p>
        <h1>Ambiente inicial configurado</h1>
        <p class="description">
            A aplicação PHP está respondendo e a base do portal administrativo está pronta
            para receber a autenticação.
        </p>

        <div class="status <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
            <span class="status__dot" aria-hidden="true"></span>
            Banco de dados: <?= htmlspecialchars($databaseStatus, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </main>
</body>
</html>
