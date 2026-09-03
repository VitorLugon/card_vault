INSERT INTO cards (name_en, name_pt, game, edition_id, image_path, rarity)
SELECT
    'Llanowar Elves',
    'Elfos de Llanowar',
    'magic',
    'dom',
    'demo-llanowar-elves.jpg',
    'Comum'
WHERE NOT EXISTS (
    SELECT 1 FROM cards WHERE image_path = 'demo-llanowar-elves.jpg'
);

INSERT INTO cards (name_en, name_pt, game, edition_id, image_path, rarity)
SELECT
    'Pikachu',
    'Pikachu',
    'pokemon',
    'base1',
    'demo-pikachu.png',
    'Comum'
WHERE NOT EXISTS (
    SELECT 1 FROM cards WHERE image_path = 'demo-pikachu.png'
);

INSERT INTO cards (name_en, name_pt, game, edition_id, image_path, rarity)
SELECT
    'Blue-Eyes White Dragon',
    'Dragão Branco de Olhos Azuis',
    'yugioh',
    'lob',
    'demo-blue-eyes-white-dragon.jpg',
    'Ultra rara'
WHERE NOT EXISTS (
    SELECT 1 FROM cards WHERE image_path = 'demo-blue-eyes-white-dragon.jpg'
);
