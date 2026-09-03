<?php

declare(strict_types=1);

use App\Cards\CardRepository;
use App\Cards\ImageStorage;
use App\Http\ApiRequest;
use App\Http\JsonResponse;

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

ApiRequest::requireAuthentication();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    JsonResponse::send(['message' => 'Método não permitido.'], 405);
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($id === false || $id === null) {
    JsonResponse::send(['message' => 'Identificador da carta inválido.'], 422);
}

$card = (new CardRepository())->find((int) $id);
$path = ImageStorage::absolutePath($card['image_path'] ?? null);

if ($card === null || $path === null || !is_file($path)) {
    JsonResponse::send(['message' => 'Imagem não encontrada.'], 404);
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($path);

header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
readfile($path);
