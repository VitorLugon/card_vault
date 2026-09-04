<?php

declare(strict_types=1);

use App\Auth\Auth;
use App\Security\Csrf;

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (Auth::check()) {
    redirect('/dashboard.php');
}

$email = '';
$error = null;
$loggedOut = isset($_GET['logout']) && $_GET['logout'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $csrfToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null;

    if (!Csrf::validate($csrfToken)) {
        http_response_code(403);
        $error = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Informe um e-mail válido e a senha.';
    } elseif (!Auth::attempt($email, $password)) {
        $error = 'E-mail ou senha incorretos.';
    } else {
        redirect('/dashboard.php');
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - CardVault</title>
    <link rel="stylesheet" href="/assets/css/app.css?v=4.1">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-intro" aria-labelledby="page-title">
            <p class="eyebrow">CardVault</p>
            <h1 id="page-title">Organize sua coleção em um só lugar.</h1>
            <p>Entre para cadastrar, consultar e manter as cartas do catálogo.</p>
        </section>

        <section class="panel auth-panel" aria-label="Acesso administrativo">
            <div>
                <p class="eyebrow">Área administrativa</p>
                <h2>Boas-vindas</h2>
                <p class="muted">Use suas credenciais para continuar.</p>
            </div>

            <?php if ($loggedOut): ?>
                <p class="feedback feedback--success" role="status">Você saiu da sua conta.</p>
            <?php endif; ?>

            <?php if ($error !== null): ?>
                <p class="feedback feedback--error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="post" action="/login.php" class="form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <label class="field">
                    <span>E-mail</span>
                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        placeholder="voce@exemplo.com"
                        required
                        autofocus
                    >
                </label>

                <label class="field">
                    <span>Senha</span>
                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Digite sua senha"
                        required
                    >
                </label>

                <button type="submit" class="button button--primary">Entrar</button>
            </form>
        </section>
    </main>
</body>
</html>
