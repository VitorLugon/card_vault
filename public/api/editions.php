<?php

declare(strict_types=1);

use App\Cards\EditionCatalog;
use App\Http\ApiRequest;
use App\Http\JsonResponse;

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

ApiRequest::requireAuthentication();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    JsonResponse::send(['message' => 'Método não permitido.'], 405);
}

$game = trim((string) ($_GET['game'] ?? ''));
$editions = EditionCatalog::editionsFor($game);

if ($editions === null) {
    JsonResponse::send(['message' => 'Card game inválido.'], 422);
}

JsonResponse::send(['data' => $editions]);
