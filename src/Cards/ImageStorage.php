<?php

declare(strict_types=1);

namespace App\Cards;

use InvalidArgumentException;
use RuntimeException;

final class ImageStorage
{
    private const MAX_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function store(?array $file, bool $required): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new InvalidArgumentException('Selecione uma imagem para a carta.');
            }

            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Não foi possível receber a imagem enviada.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_SIZE) {
            throw new InvalidArgumentException('A imagem deve ter no máximo 5 MB.');
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($temporaryPath)) {
            throw new InvalidArgumentException('O arquivo de imagem enviado é inválido.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $extension = self::ALLOWED_TYPES[$mimeType] ?? null;

        if ($extension === null) {
            throw new InvalidArgumentException('Envie uma imagem JPG, PNG ou WebP.');
        }

        $fileName = bin2hex(random_bytes(16)) . ".{$extension}";
        $destination = self::directory() . "/{$fileName}";

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Não foi possível salvar a imagem da carta.');
        }

        return $fileName;
    }

    public static function delete(?string $fileName): void
    {
        $path = self::absolutePath($fileName);

        if ($path !== null && is_file($path)) {
            unlink($path);
        }
    }

    public static function absolutePath(?string $fileName): ?string
    {
        if ($fileName === null || $fileName === '' || basename($fileName) !== $fileName) {
            return null;
        }

        return self::directory() . "/{$fileName}";
    }

    private static function directory(): string
    {
        $directory = dirname(__DIR__, 2) . '/uploads/cards';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Não foi possível preparar o diretório de imagens.');
        }

        return $directory;
    }
}
