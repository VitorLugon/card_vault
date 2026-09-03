<?php

declare(strict_types=1);

namespace App\Cards;

use App\Config\Database;

final class CardRepository
{
    public function all(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name_en, name_pt, game, edition_id, image_path, rarity, created_at, updated_at
             FROM cards
             ORDER BY updated_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, name_en, name_pt, game, edition_id, image_path, rarity, created_at, updated_at
             FROM cards
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $card = $statement->fetch();

        return $card === false ? null : $card;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO cards (name_en, name_pt, game, edition_id, image_path, rarity)
             VALUES (:name_en, :name_pt, :game, :edition_id, :image_path, :rarity)'
        );
        $statement->execute($data);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = Database::connection()->prepare(
            'UPDATE cards
             SET name_en = :name_en,
                 name_pt = :name_pt,
                 game = :game,
                 edition_id = :edition_id,
                 image_path = :image_path,
                 rarity = :rarity
             WHERE id = :id'
        );
        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM cards WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
