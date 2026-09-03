(() => {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const cardsGrid = document.querySelector('#cards-grid');
    const cardsLoading = document.querySelector('#cards-loading');
    const cardsError = document.querySelector('#cards-error');
    const emptyState = document.querySelector('#empty-state');
    const emptyTitle = document.querySelector('#empty-title');
    const emptyDescription = document.querySelector('#empty-description');
    const emptyNewCard = document.querySelector('#empty-new-card');
    const resultsSummary = document.querySelector('#results-summary');
    const searchInput = document.querySelector('#card-search');
    const gameFilter = document.querySelector('#game-filter');
    const cardTemplate = document.querySelector('#card-template');
    const cardDialog = document.querySelector('#card-dialog');
    const cardDialogTitle = document.querySelector('#card-dialog-title');
    const cardForm = document.querySelector('#card-form');
    const formFeedback = document.querySelector('#form-feedback');
    const gameInput = document.querySelector('#game');
    const editionInput = document.querySelector('#edition');
    const rarityInput = document.querySelector('#rarity');
    const imageInput = document.querySelector('#image');
    const imageHint = document.querySelector('#image-hint');
    const imagePreviewContainer = document.querySelector('#image-preview-container');
    const imagePreview = document.querySelector('#image-preview');
    const saveButton = document.querySelector('#save-card-button');
    const deleteDialog = document.querySelector('#delete-dialog');
    const deleteDescription = document.querySelector('#delete-dialog-description');
    const confirmDeleteButton = document.querySelector('#confirm-delete');
    const toast = document.querySelector('#toast');

    const raritiesByGame = {
        magic: ['Comum', 'Incomum', 'Rara', 'Mítica'],
        pokemon: ['Comum', 'Incomum', 'Rara', 'Rara holográfica', 'Ultra rara', 'Promo'],
        yugioh: ['Comum', 'Rara', 'Super rara', 'Ultra rara', 'Secreta'],
    };

    const state = {
        cards: [],
        editingId: null,
        deletingId: null,
        editionRequestId: 0,
        previewObjectUrl: null,
        toastTimer: null,
    };

    document.querySelector('#new-card-button').addEventListener('click', openCreateDialog);
    emptyNewCard.addEventListener('click', openCreateDialog);
    document.querySelector('#retry-cards').addEventListener('click', loadCards);
    document.querySelector('#close-card-dialog').addEventListener('click', () => cardDialog.close());
    document.querySelector('#cancel-card-form').addEventListener('click', () => cardDialog.close());
    document.querySelector('#cancel-delete').addEventListener('click', () => deleteDialog.close());
    confirmDeleteButton.addEventListener('click', deleteCard);
    searchInput.addEventListener('input', applyFilters);
    gameFilter.addEventListener('change', applyFilters);
    gameInput.addEventListener('change', () => {
        loadEditions(gameInput.value);
        loadRarities(gameInput.value);
    });
    imageInput.addEventListener('change', updateImagePreview);
    cardForm.addEventListener('submit', saveCard);
    cardsGrid.addEventListener('click', handleCardAction);
    cardDialog.addEventListener('close', clearPreviewObjectUrl);
    cardDialog.addEventListener('click', closeOnBackdrop);
    deleteDialog.addEventListener('click', closeOnBackdrop);

    loadCards();

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...options.headers,
            },
        });

        const payload = await response.json().catch(() => ({
            message: 'O servidor retornou uma resposta inválida.',
        }));

        if (response.status === 401) {
            window.location.href = '/login.php';
            throw new Error('Sessão encerrada.');
        }

        if (!response.ok) {
            const error = new Error(payload.message || 'Não foi possível concluir a operação.');
            error.payload = payload;
            throw error;
        }

        return payload;
    }

    async function loadCards() {
        cardsLoading.hidden = false;
        cardsError.hidden = true;
        emptyState.hidden = true;
        cardsGrid.hidden = true;
        resultsSummary.textContent = '';

        try {
            const response = await request('/api/cards.php');
            state.cards = response.data;
            applyFilters();
        } catch (error) {
            cardsError.hidden = false;
            resultsSummary.textContent = 'Não foi possível atualizar a listagem.';
        } finally {
            cardsLoading.hidden = true;
        }
    }

    function applyFilters() {
        const term = normalize(searchInput.value);
        const selectedGame = gameFilter.value;
        const filteredCards = state.cards.filter((card) => {
            const matchesGame = selectedGame === '' || card.game === selectedGame;
            const names = normalize(`${card.name_en} ${card.name_pt || ''}`);
            return matchesGame && names.includes(term);
        });

        renderCards(filteredCards);

        const total = filteredCards.length;
        resultsSummary.textContent = total === 1 ? '1 carta encontrada' : `${total} cartas encontradas`;
    }

    function renderCards(cards) {
        cardsGrid.replaceChildren();
        cardsGrid.hidden = cards.length === 0;
        emptyState.hidden = cards.length > 0;

        if (cards.length === 0) {
            const hasFilters = searchInput.value.trim() !== '' || gameFilter.value !== '';
            emptyTitle.textContent = hasFilters ? 'Nenhuma carta encontrada' : 'Nenhuma carta cadastrada';
            emptyDescription.textContent = hasFilters
                ? 'Tente alterar o texto da busca ou o filtro de card game.'
                : 'Cadastre a primeira carta para começar a organizar o catálogo.';
            emptyNewCard.hidden = hasFilters;
            return;
        }

        const fragment = document.createDocumentFragment();

        cards.forEach((card) => {
            const element = cardTemplate.content.firstElementChild.cloneNode(true);
            element.dataset.cardId = String(card.id);

            const image = element.querySelector('.card-item__image');
            image.src = card.image_url;
            image.alt = `Imagem da carta ${card.name_en}`;

            element.querySelector('.card-item__rarity').textContent = card.rarity;
            element.querySelector('.card-item__game').textContent = card.game_name;
            element.querySelector('.card-item__name').textContent = card.name_en;
            element.querySelector('.card-item__edition').textContent = card.edition_name || card.edition_id;

            const portugueseName = element.querySelector('.card-item__name-pt');
            portugueseName.textContent = card.name_pt || 'Sem nome em português';
            portugueseName.classList.toggle('muted', !card.name_pt);

            fragment.append(element);
        });

        cardsGrid.append(fragment);
    }

    function handleCardAction(event) {
        const button = event.target.closest('button[data-action]');
        if (button === null) {
            return;
        }

        const cardElement = button.closest('[data-card-id]');
        const card = state.cards.find((item) => item.id === Number(cardElement.dataset.cardId));

        if (card === undefined) {
            return;
        }

        if (button.dataset.action === 'edit') {
            openEditDialog(card);
        } else if (button.dataset.action === 'delete') {
            openDeleteDialog(card);
        }
    }

    function openCreateDialog() {
        state.editingId = null;
        cardForm.reset();
        clearFormErrors();
        resetEditionInput();
        resetRarityInput();
        clearImagePreview();
        cardDialogTitle.textContent = 'Nova carta';
        saveButton.textContent = 'Salvar carta';
        imageInput.required = true;
        imageHint.textContent = 'JPG, PNG ou WebP de até 5 MB.';
        cardDialog.showModal();
        document.querySelector('#name-en').focus();
    }

    function openEditDialog(card) {
        state.editingId = card.id;
        cardForm.reset();
        clearFormErrors();
        clearImagePreview();

        document.querySelector('#name-en').value = card.name_en;
        document.querySelector('#name-pt').value = card.name_pt || '';
        gameInput.value = card.game;
        loadRarities(card.game, card.rarity);
        imageInput.required = false;
        imageHint.textContent = 'Envie outra imagem apenas se quiser substituir a atual.';
        cardDialogTitle.textContent = 'Editar carta';
        saveButton.textContent = 'Salvar alterações';
        showImagePreview(card.image_url);
        cardDialog.showModal();
        loadEditions(card.game, card.edition_id);
    }

    async function loadEditions(game, selectedEdition = '') {
        const requestId = ++state.editionRequestId;
        editionInput.disabled = true;
        editionInput.replaceChildren(createOption('', game ? 'Carregando edições...' : 'Selecione o card game primeiro'));
        clearFieldError('edition_id');

        if (game === '') {
            return;
        }

        try {
            const response = await request(`/api/editions.php?game=${encodeURIComponent(game)}`);
            if (requestId !== state.editionRequestId) {
                return;
            }

            const options = document.createDocumentFragment();
            options.append(createOption('', 'Selecione uma edição'));
            response.data.forEach((edition) => options.append(createOption(edition.id, edition.name)));
            editionInput.replaceChildren(options);
            editionInput.disabled = false;
            editionInput.value = selectedEdition;
        } catch (error) {
            if (requestId !== state.editionRequestId) {
                return;
            }

            editionInput.replaceChildren(createOption('', 'Não foi possível carregar as edições'));
            showFieldError('edition_id', 'Tente selecionar o card game novamente.');
        }
    }

    function createOption(value, label) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        return option;
    }

    function resetEditionInput() {
        state.editionRequestId += 1;
        editionInput.disabled = true;
        editionInput.replaceChildren(createOption('', 'Selecione o card game primeiro'));
    }

    function loadRarities(game, selectedRarity = '') {
        const rarities = raritiesByGame[game] || [];
        rarityInput.replaceChildren(createOption('', game ? 'Selecione uma raridade' : 'Selecione o card game primeiro'));
        rarityInput.disabled = rarities.length === 0;

        rarities.forEach((rarity) => rarityInput.append(createOption(rarity, rarity)));

        if (selectedRarity !== '' && !rarities.includes(selectedRarity)) {
            rarityInput.append(createOption(selectedRarity, selectedRarity));
        }

        rarityInput.value = selectedRarity;
        clearFieldError('rarity');
    }

    function resetRarityInput() {
        rarityInput.disabled = true;
        rarityInput.replaceChildren(createOption('', 'Selecione o card game primeiro'));
    }

    async function saveCard(event) {
        event.preventDefault();
        clearFormErrors();

        if (!cardForm.reportValidity()) {
            return;
        }

        const selectedImage = imageInput.files[0];
        if (selectedImage && selectedImage.size > 5 * 1024 * 1024) {
            showFieldError('image', 'A imagem deve ter no máximo 5 MB.');
            return;
        }

        const formData = new FormData(cardForm);
        let url = '/api/cards.php';

        if (state.editingId !== null) {
            formData.set('_method', 'PUT');
            url += `?id=${state.editingId}`;
        }

        setButtonLoading(saveButton, true, 'Salvando...');

        try {
            const response = await request(url, {
                method: 'POST',
                headers: {'X-CSRF-Token': csrfToken},
                body: formData,
            });
            cardDialog.close();
            showToast(response.message);
            await loadCards();
        } catch (error) {
            const errors = error.payload?.errors || {};
            Object.entries(errors).forEach(([field, message]) => showFieldError(field, message));
            formFeedback.textContent = error.message;
            formFeedback.hidden = false;
        } finally {
            setButtonLoading(saveButton, false);
        }
    }

    function openDeleteDialog(card) {
        state.deletingId = card.id;
        deleteDescription.textContent = `A carta “${card.name_en}” e sua imagem serão removidas. Esta ação não poderá ser desfeita.`;
        deleteDialog.showModal();
    }

    async function deleteCard() {
        if (state.deletingId === null) {
            return;
        }

        const formData = new FormData();
        formData.set('_method', 'DELETE');
        formData.set('csrf_token', csrfToken);
        setButtonLoading(confirmDeleteButton, true, 'Excluindo...');

        try {
            const response = await request(`/api/cards.php?id=${state.deletingId}`, {
                method: 'POST',
                headers: {'X-CSRF-Token': csrfToken},
                body: formData,
            });
            deleteDialog.close();
            showToast(response.message);
            state.deletingId = null;
            await loadCards();
        } catch (error) {
            deleteDialog.close();
            showToast(error.message, true);
        } finally {
            setButtonLoading(confirmDeleteButton, false);
        }
    }

    function updateImagePreview() {
        const file = imageInput.files[0];
        clearPreviewObjectUrl();

        if (!file) {
            const currentCard = state.cards.find((card) => card.id === state.editingId);
            currentCard ? showImagePreview(currentCard.image_url) : clearImagePreview();
            return;
        }

        state.previewObjectUrl = URL.createObjectURL(file);
        showImagePreview(state.previewObjectUrl);
        clearFieldError('image');
    }

    function showImagePreview(source) {
        imagePreview.src = source;
        imagePreviewContainer.hidden = false;
    }

    function clearImagePreview() {
        clearPreviewObjectUrl();
        imagePreview.removeAttribute('src');
        imagePreviewContainer.hidden = true;
    }

    function clearPreviewObjectUrl() {
        if (state.previewObjectUrl !== null) {
            URL.revokeObjectURL(state.previewObjectUrl);
            state.previewObjectUrl = null;
        }
    }

    function clearFormErrors() {
        formFeedback.hidden = true;
        formFeedback.textContent = '';
        document.querySelectorAll('[data-error-for]').forEach((element) => {
            element.textContent = '';
        });
        cardForm.querySelectorAll('[aria-invalid="true"]').forEach((element) => {
            element.removeAttribute('aria-invalid');
        });
    }

    function clearFieldError(field) {
        showFieldError(field, '');
    }

    function showFieldError(field, message) {
        const errorElement = document.querySelector(`[data-error-for="${field}"]`);
        const input = cardForm.elements.namedItem(field === 'edition_id' ? 'edition_id' : field);

        if (errorElement !== null) {
            errorElement.textContent = message;
        }

        if (input instanceof HTMLElement) {
            input.toggleAttribute('aria-invalid', message !== '');
        }
    }

    function setButtonLoading(button, loading, loadingText = '') {
        if (loading) {
            button.dataset.originalText = button.textContent;
            button.textContent = loadingText;
            button.disabled = true;
            return;
        }

        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        delete button.dataset.originalText;
    }

    function showToast(message, isError = false) {
        window.clearTimeout(state.toastTimer);
        toast.textContent = message;
        toast.classList.toggle('toast--error', isError);
        toast.hidden = false;
        state.toastTimer = window.setTimeout(() => {
            toast.hidden = true;
        }, 4000);
    }

    function closeOnBackdrop(event) {
        if (event.target === event.currentTarget) {
            event.currentTarget.close();
        }
    }

    function normalize(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('pt-BR');
    }
})();
