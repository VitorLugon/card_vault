<?php

declare(strict_types=1);

namespace App\Http;

use App\Auth\Auth;
use App\Security\Csrf;

final class ApiRequest
{
    public static function requireAuthentication(): void
    {
        if (!Auth::check()) {
            JsonResponse::send(['message' => 'Autenticação necessária.'], 401);
        }
    }

    public static function requireCsrfToken(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;

        if (!Csrf::validate(is_string($token) ? $token : null)) {
            JsonResponse::send(['message' => 'Sua sessão expirou. Atualize a página e tente novamente.'], 403);
        }
    }
}
