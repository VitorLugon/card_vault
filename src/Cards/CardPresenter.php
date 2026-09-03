<?php

declare(strict_types=1);

namespace App\Cards;

final class CardPresenter
{
    public static function one(array $card): array
    {
        return [
            'id' => (int) $card['id'],
            'name_en' => $card['name_en'],
            'name_pt' => $card['name_pt'],
            'game' => $card['game'],
            'game_name' => EditionCatalog::games()[$card['game']] ?? $card['game'],
            'edition_id' => $card['edition_id'],
            'edition_name' => EditionCatalog::editionName($card['game'], $card['edition_id']),
            'rarity' => $card['rarity'],
            'image_url' => '/api/card-image.php?id=' . (int) $card['id'],
            'created_at' => $card['created_at'],
            'updated_at' => $card['updated_at'],
        ];
    }

    public static function many(array $cards): array
    {
        return array_map(self::one(...), $cards);
    }
}
