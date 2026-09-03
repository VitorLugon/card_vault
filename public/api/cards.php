<?php

declare(strict_types=1);

use App\Cards\CardPresenter;
use App\Cards\CardRepository;
use App\Cards\CardValidator;
use App\Cards\ImageStorage;
use App\Http\ApiRequest;
use App\Http\JsonResponse;

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

ApiRequest::requireAuthentication();

$repository = new CardRepository();
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    if ($requestMethod === 'GET') {
        if (!isset($_GET['id'])) {
            JsonResponse::send(['data' => CardPresenter::many($repository->all())]);
        }

        $id = requestedCardId();
        if ($id === null) {
            JsonResponse::send(['message' => 'Identificador da carta inválido.'], 422);
        }

        $card = $repository->find($id);
        if ($card === null) {
            JsonResponse::send(['message' => 'Carta não encontrada.'], 404);
        }

        JsonResponse::send(['data' => CardPresenter::one($card)]);
    }

    if ($requestMethod !== 'POST') {
        header('Allow: GET, POST');
        JsonResponse::send(['message' => 'Método não permitido.'], 405);
    }

    ApiRequest::requireCsrfToken();
    $action = strtoupper((string) ($_POST['_method'] ?? 'POST'));

    if ($action === 'POST') {
        createCard($repository);
    }

    if ($action === 'PUT') {
        updateCard($repository);
    }

    if ($action === 'DELETE') {
        deleteCard($repository);
    }

    JsonResponse::send(['message' => 'Operação inválida.'], 405);
} catch (\Throwable) {
    JsonResponse::send(['message' => 'Não foi possível concluir a operação.'], 500);
}

function createCard(CardRepository $repository): never
{
    $validation = CardValidator::validate($_POST);

    if ($validation['errors'] !== []) {
        JsonResponse::send([
            'message' => 'Revise os campos informados.',
            'errors' => $validation['errors'],
        ], 422);
    }

    try {
        $imagePath = ImageStorage::store($_FILES['image'] ?? null, true);
    } catch (\InvalidArgumentException $exception) {
        JsonResponse::send([
            'message' => 'Revise os campos informados.',
            'errors' => ['image' => $exception->getMessage()],
        ], 422);
    }

    $data = $validation['data'];
    $data['image_path'] = $imagePath;

    try {
        $id = $repository->create($data);
    } catch (\Throwable $exception) {
        ImageStorage::delete($imagePath);
        throw $exception;
    }

    JsonResponse::send([
        'message' => 'Carta cadastrada com sucesso.',
        'data' => CardPresenter::one($repository->find($id)),
    ], 201);
}

function updateCard(CardRepository $repository): never
{
    $id = requestedCardId();
    if ($id === null) {
        JsonResponse::send(['message' => 'Identificador da carta inválido.'], 422);
    }

    $currentCard = $repository->find($id);
    if ($currentCard === null) {
        JsonResponse::send(['message' => 'Carta não encontrada.'], 404);
    }

    $validation = CardValidator::validate($_POST);
    if ($validation['errors'] !== []) {
        JsonResponse::send([
            'message' => 'Revise os campos informados.',
            'errors' => $validation['errors'],
        ], 422);
    }

    try {
        $newImagePath = ImageStorage::store($_FILES['image'] ?? null, false);
    } catch (\InvalidArgumentException $exception) {
        JsonResponse::send([
            'message' => 'Revise os campos informados.',
            'errors' => ['image' => $exception->getMessage()],
        ], 422);
    }

    $data = $validation['data'];
    $data['image_path'] = $newImagePath ?? $currentCard['image_path'];

    try {
        $repository->update($id, $data);
    } catch (\Throwable $exception) {
        ImageStorage::delete($newImagePath);
        throw $exception;
    }

    if ($newImagePath !== null) {
        ImageStorage::delete($currentCard['image_path']);
    }

    JsonResponse::send([
        'message' => 'Carta atualizada com sucesso.',
        'data' => CardPresenter::one($repository->find($id)),
    ]);
}

function deleteCard(CardRepository $repository): never
{
    $id = requestedCardId();
    if ($id === null) {
        JsonResponse::send(['message' => 'Identificador da carta inválido.'], 422);
    }

    $card = $repository->find($id);
    if ($card === null) {
        JsonResponse::send(['message' => 'Carta não encontrada.'], 404);
    }

    $repository->delete($id);
    ImageStorage::delete($card['image_path']);

    JsonResponse::send(['message' => 'Carta excluída com sucesso.']);
}

function requestedCardId(): ?int
{
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $id === false || $id === null ? null : $id;
}
