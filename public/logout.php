<?php

declare(strict_types=1);

use App\Auth\Auth;
use App\Security\Csrf;

require_once dirname(__DIR__) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método não permitido.');
}

if (!Auth::check()) {
    redirect('/login.php');
}

$csrfToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null;

if (!Csrf::validate($csrfToken)) {
    http_response_code(403);
    exit('Sua sessão expirou. Volte ao painel e tente novamente.');
}

Auth::logout();
redirect('/login.php?logout=1');
