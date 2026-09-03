<?php

declare(strict_types=1);

use App\Auth\Auth;
use App\Security\Csrf;

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (!Auth::check()) {
    redirect('/login.php');
}

$user = Auth::user();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel - CardVault</title>
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/assets/css/app.css?v=4.1">
    <script src="/assets/js/cards.js?v=4.1" defer></script>
</head>
<body class="dashboard-page">
    <header class="topbar">
        <a class="brand" href="/dashboard.php">CardVault</a>

        <div class="account">
            <div>
                <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <form method="post" action="/logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="button button--secondary" type="submit">Sair</button>
            </form>
        </div>
    </header>

    <main class="dashboard dashboard--cards">
        <header class="manager-header">
            <div>
                <p class="eyebrow">Painel administrativo</p>
                <h1>Gerenciador de cartas</h1>
                <p class="dashboard__lead">Consulte o catálogo e mantenha as informações das cartas atualizadas.</p>
            </div>

            <button class="button button--primary" id="new-card-button" type="button">
                <span aria-hidden="true">+</span>
                Nova carta
            </button>
        </header>

        <section class="panel toolbar" aria-label="Busca e filtros">
            <label class="toolbar__field toolbar__field--search">
                <span>Buscar carta</span>
                <input id="card-search" type="search" placeholder="Nome em inglês ou português">
            </label>

            <label class="toolbar__field">
                <span>Card game</span>
                <select id="game-filter">
                    <option value="">Todos os jogos</option>
                    <option value="magic">Magic: The Gathering</option>
                    <option value="pokemon">Pokémon</option>
                    <option value="yugioh">Yu-Gi-Oh!</option>
                </select>
            </label>

            <p class="results-summary" id="results-summary" aria-live="polite"></p>
        </section>

        <div class="loading-state" id="cards-loading" role="status">
            <span class="spinner" aria-hidden="true"></span>
            Carregando cartas...
        </div>

        <section class="panel request-error" id="cards-error" hidden>
            <h2>Não foi possível carregar as cartas</h2>
            <p>Verifique sua conexão e tente novamente.</p>
            <button class="button button--secondary" id="retry-cards" type="button">Tentar novamente</button>
        </section>

        <section class="panel empty-state" id="empty-state" hidden>
            <span class="empty-state__symbol" aria-hidden="true">◇</span>
            <h2 id="empty-title">Nenhuma carta cadastrada</h2>
            <p id="empty-description">Cadastre a primeira carta para começar a organizar o catálogo.</p>
            <button class="button button--primary" id="empty-new-card" type="button">Cadastrar carta</button>
        </section>

        <section class="cards-grid" id="cards-grid" aria-label="Cartas cadastradas"></section>
    </main>

    <dialog class="dialog card-dialog" id="card-dialog" aria-labelledby="card-dialog-title">
        <form class="card-form" id="card-form" action="/api/cards.php" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <header class="dialog__header">
                <div>
                    <p class="eyebrow">Dados da carta</p>
                    <h2 id="card-dialog-title">Nova carta</h2>
                </div>
                <button class="icon-button" id="close-card-dialog" type="button" aria-label="Fechar formulário">×</button>
            </header>

            <p class="feedback feedback--error" id="form-feedback" role="alert" hidden></p>

            <div class="form-grid">
                <label class="field form-grid__full">
                    <span>Nome em inglês</span>
                    <input id="name-en" name="name_en" maxlength="150" required>
                    <small class="field-error" data-error-for="name_en"></small>
                </label>

                <label class="field form-grid__full">
                    <span>Nome em português <em>(opcional)</em></span>
                    <input id="name-pt" name="name_pt" maxlength="150">
                    <small class="field-error" data-error-for="name_pt"></small>
                </label>

                <label class="field">
                    <span>Card game</span>
                    <select id="game" name="game" required>
                        <option value="">Selecione</option>
                        <option value="magic">Magic: The Gathering</option>
                        <option value="pokemon">Pokémon</option>
                        <option value="yugioh">Yu-Gi-Oh!</option>
                    </select>
                    <small class="field-error" data-error-for="game"></small>
                </label>

                <label class="field">
                    <span>Edição</span>
                    <select id="edition" name="edition_id" required disabled>
                        <option value="">Selecione o card game primeiro</option>
                    </select>
                    <small class="field-error" data-error-for="edition_id"></small>
                </label>

                <label class="field form-grid__full">
                    <span>Raridade</span>
                    <input id="rarity" name="rarity" list="rarity-suggestions" maxlength="60" required>
                    <datalist id="rarity-suggestions">
                        <option value="Comum"></option>
                        <option value="Incomum"></option>
                        <option value="Rara"></option>
                        <option value="Mítica"></option>
                        <option value="Promo"></option>
                    </datalist>
                    <small class="field-error" data-error-for="rarity"></small>
                </label>

                <label class="field form-grid__full">
                    <span>Imagem da carta</span>
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <small class="field-hint" id="image-hint">JPG, PNG ou WebP de até 5 MB.</small>
                    <small class="field-error" data-error-for="image"></small>
                </label>

                <div class="image-preview form-grid__full" id="image-preview-container" hidden>
                    <img id="image-preview" alt="Prévia da imagem selecionada">
                    <span>Prévia da imagem</span>
                </div>
            </div>

            <footer class="dialog__footer">
                <button class="button button--secondary" id="cancel-card-form" type="button">Cancelar</button>
                <button class="button button--primary" id="save-card-button" type="submit">Salvar carta</button>
            </footer>
        </form>
    </dialog>

    <dialog class="dialog confirm-dialog" id="delete-dialog" aria-labelledby="delete-dialog-title">
        <div class="confirm-dialog__icon" aria-hidden="true">!</div>
        <h2 id="delete-dialog-title">Excluir carta?</h2>
        <p id="delete-dialog-description">Esta ação não poderá ser desfeita.</p>
        <div class="dialog__footer">
            <button class="button button--secondary" id="cancel-delete" type="button">Cancelar</button>
            <button class="button button--danger" id="confirm-delete" type="button">Excluir carta</button>
        </div>
    </dialog>

    <div class="toast" id="toast" role="status" aria-live="polite" hidden></div>

    <template id="card-template">
        <article class="card-item">
            <div class="card-item__image-wrap">
                <img class="card-item__image" alt="" loading="lazy">
                <span class="card-item__rarity"></span>
            </div>
            <div class="card-item__body">
                <div>
                    <p class="card-item__game"></p>
                    <h2 class="card-item__name"></h2>
                    <p class="card-item__name-pt"></p>
                </div>
                <p class="card-item__edition"></p>
                <div class="card-item__actions">
                    <button class="button button--secondary" data-action="edit" type="button">Editar</button>
                    <button class="button button--quiet-danger" data-action="delete" type="button">Excluir</button>
                </div>
            </div>
        </article>
    </template>
</body>
</html>
