<?php

declare(strict_types=1);

require_once __DIR__ . '/Config/Database.php';
require_once __DIR__ . '/Auth/Auth.php';
require_once __DIR__ . '/Security/Csrf.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_name('cardvault_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function redirect(string $path): never
{
    header("Location: {$path}");
    exit;
}
