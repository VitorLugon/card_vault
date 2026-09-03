<?php

declare(strict_types=1);

namespace App\Cards;

use JsonException;
use RuntimeException;

final class EditionCatalog
{
    private const GAME_NAMES = [
        'magic' => 'Magic: The Gathering',
        'pokemon' => 'Pokémon',
        'yugioh' => 'Yu-Gi-Oh!',
    ];

    private static ?array $editions = null;

    public static function games(): array
    {
        return self::GAME_NAMES;
    }

    public static function editionsFor(string $game): ?array
    {
        if (!array_key_exists($game, self::GAME_NAMES)) {
            return null;
        }

        return self::load()[$game] ?? [];
    }

    public static function contains(string $game, string $editionId): bool
    {
        $editions = self::editionsFor($game);

        if ($editions === null) {
            return false;
        }

        foreach ($editions as $edition) {
            if ($edition['id'] === $editionId) {
                return true;
            }
        }

        return false;
    }

    public static function editionName(string $game, string $editionId): ?string
    {
        foreach (self::editionsFor($game) ?? [] as $edition) {
            if ($edition['id'] === $editionId) {
                return $edition['name'];
            }
        }

        return null;
    }

    private static function load(): array
    {
        if (self::$editions !== null) {
            return self::$editions;
        }

        $path = dirname(__DIR__, 2) . '/data/editions.json';
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Não foi possível carregar o catálogo de edições.');
        }

        try {
            self::$editions = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('O catálogo de edições possui um JSON inválido.', 0, $exception);
        }

        return self::$editions;
    }
}
