const findEmptyImageItem = (field) =>
    [...field.querySelectorAll('.field-collection-item')].find((item) => {
        const cards = item.querySelectorAll('[data-ea-fileupload-card]');
        const deleteChecked = item.querySelector('input[type=checkbox].form-check-input')?.checked;

        return cards.length === 0 && !deleteChecked;
    });

const openFilePicker = (item) => {
    const fileInput = item?.querySelector('[data-ea-fileupload-input]');
    if (!fileInput) {
        return;
    }

    requestAnimationFrame(() => fileInput.click());
};

const bindFileUploadFallback = (root) => {
    root.querySelectorAll('[data-ea-fileupload-field]').forEach((container) => {
        const addButton = container.querySelector('[data-ea-fileupload-add]');
        const fileInput = container.querySelector('[data-ea-fileupload-input]');

        if (!addButton || !fileInput || addButton.dataset.vgUploadBound === 'true') {
            return;
        }

        addButton.dataset.vgUploadBound = 'true';
        addButton.addEventListener('click', () => {
            fileInput.click();
        });
    });
};

const updatePhotoLabels = (field) => {
    field.querySelectorAll('.field-collection-item').forEach((item, index) => {
        const button = item.querySelector('.accordion-button');
        if (!button) {
            return;
        }

        let label = button.querySelector('.vg-menu-image-entry-label');
        if (!label) {
            button.replaceChildren();
            label = document.createElement('span');
            label.className = 'vg-menu-image-entry-label';
            button.appendChild(label);
        }

        label.textContent = `Photo ${index + 1}`;
    });
};

const enhanceEmptyState = (field) => {
    field.querySelectorAll('.collection-empty').forEach((empty) => {
        if (empty.dataset.vgEnhanced === 'true') {
            return;
        }

        empty.dataset.vgEnhanced = 'true';
        empty.classList.add('vg-menu-images-empty');

        empty.querySelector('.badge')?.remove();

        const icon = document.createElement('div');
        icon.className = 'vg-menu-images-empty__icon';
        icon.innerHTML = '<i class="fa fa-image" aria-hidden="true"></i>';

        const title = document.createElement('p');
        title.className = 'vg-menu-images-empty__title';
        title.textContent = 'Aucune photo pour ce menu';

        const hint = document.createElement('p');
        hint.className = 'vg-menu-images-empty__hint';
        hint.textContent = 'Cliquez sur « Choisir une photo » pour sélectionner un fichier.';

        empty.prepend(icon, title, hint);

        const addButton = field.querySelector('.field-collection-add-button');
        if (addButton) {
            empty.style.cursor = 'pointer';
            empty.addEventListener('click', () => addButton.click());
        }
    });
};

const enhanceAddButton = (field) => {
    const addButton = field.querySelector('.field-collection-add-button');
    if (!addButton || addButton.dataset.vgEnhanced === 'true') {
        return;
    }

    addButton.dataset.vgEnhanced = 'true';
    addButton.classList.add('vg-menu-images-add-btn');

    [...addButton.childNodes]
        .filter((node) => node.nodeType === Node.TEXT_NODE)
        .forEach((node) => node.remove());

    let label = addButton.querySelector('.vg-menu-images-add-btn__label');
    if (!label) {
        label = document.createElement('span');
        label.className = 'vg-menu-images-add-btn__label';
        addButton.appendChild(label);
    }

    label.textContent = 'Choisir une photo';

    addButton.addEventListener(
        'click',
        (event) => {
            const emptyItem = findEmptyImageItem(field);
            if (emptyItem) {
                event.preventDefault();
                event.stopImmediatePropagation();
                openFilePicker(emptyItem);

                return;
            }

            field.dataset.vgAwaitingUpload = 'true';
        },
        true,
    );
};

const enhanceMenuImagesField = (field) => {
    enhanceEmptyState(field);
    enhanceAddButton(field);
    updatePhotoLabels(field);
    bindFileUploadFallback(field);
};

const initMenuImagesFields = () => {
    document.querySelectorAll('[data-ea-collection-field].vg-menu-images-field').forEach(enhanceMenuImagesField);
    bindFileUploadFallback(document);
};

const handleCollectionItemAdded = (event) => {
    const collection = event.detail?.collection;
    const newElement = event.detail?.newElement;

    initMenuImagesFields();

    if (!collection?.classList.contains('vg-menu-images-field')) {
        return;
    }

    if (collection.dataset.vgAwaitingUpload !== 'true') {
        return;
    }

    collection.dataset.vgAwaitingUpload = 'false';
    openFilePicker(newElement);
};

document.addEventListener('DOMContentLoaded', initMenuImagesFields);
document.addEventListener('ea.collection.item-added', handleCollectionItemAdded);
document.addEventListener('ea.collection.item-removed', initMenuImagesFields);
