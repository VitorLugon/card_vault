<?php

declare(strict_types=1);

use App\Cards\ImageStorage;
use App\Config\Database;

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    exit("Este script só pode ser executado pelo terminal.\n");
}

$action = $argv[1] ?? '';
$imageSources = [
    'demo-llanowar-elves.jpg' => 'https://cards.scryfall.io/normal/front/5/8/581b7327-3215-4a4f-b4ae-d9d4002ba882.jpg',
    'demo-pikachu.png' => 'https://images.pokemontcg.io/base1/58_hires.png',
    'demo-blue-eyes-white-dragon.jpg' => 'https://images.ygoprodeck.com/images/cards/89631139.jpg',
];
$imageFiles = array_keys($imageSources);
$legacyImageFiles = [
    'demo-magic.svg',
    'demo-pokemon.svg',
    'demo-yugioh.svg',
];
$managedImageFiles = [...$imageFiles, ...$legacyImageFiles];

if (!in_array($action, ['install', 'remove'], true)) {
    exit("Use: php database/demo.php install|remove\n");
}

$connection = Database::connection();

if ($action === 'remove') {
    $placeholders = implode(', ', array_fill(0, count($managedImageFiles), '?'));
    $statement = $connection->prepare("DELETE FROM cards WHERE image_path IN ({$placeholders})");
    $statement->execute($managedImageFiles);

    foreach ($managedImageFiles as $imageFile) {
        ImageStorage::delete($imageFile);
    }

    echo "Cartas de demonstração removidas.\n";
    exit(0);
}

$newFiles = [];

try {
    foreach ($imageSources as $imageFile => $sourceUrl) {
        $destination = ImageStorage::absolutePath($imageFile);

        if ($destination === null) {
            throw new RuntimeException("Nome de imagem inválido: {$imageFile}");
        }

        if (is_file($destination)) {
            continue;
        }

        $image = downloadImage($sourceUrl);
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->buffer($image);
        $expectedType = str_ends_with($imageFile, '.png') ? 'image/png' : 'image/jpeg';

        if ($mimeType !== $expectedType) {
            throw new RuntimeException("Formato inesperado para a imagem: {$imageFile}");
        }

        if (file_put_contents($destination, $image, LOCK_EX) === false) {
            throw new RuntimeException("Não foi possível salvar a imagem: {$imageFile}");
        }

        $newFiles[] = $imageFile;
    }

    $sql = file_get_contents(__DIR__ . '/demo.sql');

    if ($sql === false) {
        throw new RuntimeException('Não foi possível ler database/demo.sql.');
    }

    $statements = preg_split('/;\s*(?:\R|$)/', trim($sql)) ?: [];
    $connection->beginTransaction();

    $legacyPlaceholders = implode(', ', array_fill(0, count($legacyImageFiles), '?'));
    $removeLegacy = $connection->prepare("DELETE FROM cards WHERE image_path IN ({$legacyPlaceholders})");
    $removeLegacy->execute($legacyImageFiles);

    foreach ($statements as $statement) {
        if (trim($statement) !== '') {
            $connection->exec($statement);
        }
    }

    $connection->commit();

    foreach ($legacyImageFiles as $legacyImageFile) {
        ImageStorage::delete($legacyImageFile);
    }
} catch (Throwable $exception) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }

    foreach ($newFiles as $imageFile) {
        ImageStorage::delete($imageFile);
    }

    fwrite(STDERR, "Não foi possível cadastrar as cartas de demonstração: {$exception->getMessage()}\n");
    exit(1);
}

echo "Cartas de demonstração cadastradas.\n";

function downloadImage(string $url): string
{
    $context = stream_context_create([
        'http' => [
            'follow_location' => 1,
            'header' => implode("\r\n", [
                'User-Agent: CardVaultDemo/1.0',
                'Accept: image/avif,image/webp,image/png,image/jpeg,*/*;q=0.8',
            ]),
            'timeout' => 20,
        ],
    ]);

    $image = @file_get_contents($url, false, $context);

    if ($image === false || $image === '') {
        throw new RuntimeException("Falha ao baixar {$url}");
    }

    if (strlen($image) > 5 * 1024 * 1024) {
        throw new RuntimeException("A imagem baixada ultrapassa 5 MB: {$url}");
    }

    return $image;
}
