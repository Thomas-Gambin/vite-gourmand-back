document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.vg-menu-plats-btn');
    if (buttons.length === 0) {
        return;
    }

    let modal = document.getElementById('vg-menu-plats-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'vg-menu-plats-modal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.setAttribute('aria-labelledby', 'vg-menu-plats-modal-title');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content vg-menu-plats-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="vg-menu-plats-modal-title">Plats du menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="vg-menu-plats-list list-group list-group-flush"></ul>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const modalTitle = modal.querySelector('#vg-menu-plats-modal-title');
    const modalList = modal.querySelector('.vg-menu-plats-list');
    const bsModal = bootstrap.Modal.getOrCreateInstance(modal);

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const menuTitle = button.dataset.menuTitle || 'Menu';
            let plats = [];

            try {
                plats = JSON.parse(button.dataset.plats || '[]');
            } catch {
                plats = [];
            }

            modalTitle.textContent = `Plats — ${menuTitle}`;
            modalList.replaceChildren();

            if (!Array.isArray(plats) || plats.length === 0) {
                const emptyItem = document.createElement('li');
                emptyItem.className = 'list-group-item text-muted';
                emptyItem.textContent = 'Aucun plat associé';
                modalList.appendChild(emptyItem);
            } else {
                plats.forEach((platTitle) => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item vg-menu-plats-list__item';
                    item.textContent = platTitle;
                    modalList.appendChild(item);
                });
            }

            bsModal.show();
        });
    });
});
