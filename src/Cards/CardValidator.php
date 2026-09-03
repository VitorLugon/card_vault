<?php

declare(strict_types=1);

namespace App\Cards;

final class CardValidator
{
    public static function validate(array $input): array
    {
        $data = [
            'name_en' => trim((string) ($input['name_en'] ?? '')),
            'name_pt' => trim((string) ($input['name_pt'] ?? '')),
            'game' => trim((string) ($input['game'] ?? '')),
            'edition_id' => trim((string) ($input['edition_id'] ?? '')),
            'rarity' => trim((string) ($input['rarity'] ?? '')),
        ];
        $errors = [];

        if ($data['name_en'] === '') {
            $errors['name_en'] = 'Informe o nome da carta em inglês.';
        } elseif (strlen($data['name_en']) > 150) {
            $errors['name_en'] = 'O nome em inglês deve ter no máximo 150 caracteres.';
        }

        if ($data['name_pt'] === '') {
            $data['name_pt'] = null;
        } elseif (strlen($data['name_pt']) > 150) {
            $errors['name_pt'] = 'O nome em português deve ter no máximo 150 caracteres.';
        }

        if (EditionCatalog::editionsFor($data['game']) === null) {
            $errors['game'] = 'Selecione um card game válido.';
        } elseif (!EditionCatalog::contains($data['game'], $data['edition_id'])) {
            $errors['edition_id'] = 'Selecione uma edição válida para o card game escolhido.';
        }

        if ($data['rarity'] === '') {
            $errors['rarity'] = 'Informe a raridade da carta.';
        } elseif (strlen($data['rarity']) > 60) {
            $errors['rarity'] = 'A raridade deve ter no máximo 60 caracteres.';
        }

        return ['data' => $data, 'errors' => $errors];
    }
}
