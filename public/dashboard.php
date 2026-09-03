<?php

declare(strict_types=1);

use App\Auth\Auth;
use App\Security\Csrf;

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (!Auth::check()) {
    redirect('/login.php');
}

$user = Auth::user();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel - CardVault</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="dashboard-page">
    <header class="topbar">
        <a class="brand" href="/dashboard.php">CardVault</a>

        <div class="account">
            <div>
                <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <form method="post" action="/logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="button button--secondary" type="submit">Sair</button>
            </form>
        </div>
    </header>

    <main class="dashboard">
        <p class="eyebrow">Painel administrativo</p>
        <h1>Olá, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>.</h1>
        <p class="dashboard__lead">Sua sessão está ativa e esta área só pode ser acessada após o login.</p>

        <section class="panel next-step">
            <span class="next-step__number">03</span>
            <div>
                <h2>Gerenciamento de cartas</h2>
                <p>A listagem e o cadastro serão adicionados na próxima etapa.</p>
            </div>
        </section>
    </main>
</body>
</html>
