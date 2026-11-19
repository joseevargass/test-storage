document.addEventListener('DOMContentLoaded', () => {
    const openModal = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        const anyOpen = document.querySelector('[role="dialog"].flex');
        if (!anyOpen) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Dismissible and auto-dismissing flash alerts
    const dismissAlert = (alert) => {
        if (!alert || alert.dataset.dismissed === 'true') {
            return;
        }
        alert.dataset.dismissed = 'true';
        alert.classList.add('opacity-0', '-translate-y-1');
        window.setTimeout(() => {
            alert.remove();
        }, 280);
    };

    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => {
            dismissAlert(button.closest('[data-alert]'));
        });
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
        const delay = Number.parseInt(alert.dataset.autoDismiss || '5500', 10);
        if (!Number.isNaN(delay) && delay > 0) {
            window.setTimeout(() => dismissAlert(alert), delay);
        }
    });

    // Live image preview on file inputs
    document.querySelectorAll('[data-image-preview-target]').forEach((input) => {
        input.addEventListener('change', () => {
            const targetId = input.dataset.imagePreviewTarget;
            const preview = targetId ? document.getElementById(targetId) : null;
            const file = input.files && input.files[0];

            if (!preview || !file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                if (typeof event.target?.result === 'string') {
                    preview.src = event.target.result;
                    preview.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        });
    });

    // In-app photo lightbox viewer
    const lightboxModal = document.getElementById('photo-lightbox-modal');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxTitle = document.getElementById('lightbox-title');
    const lightboxOriginalLink = document.getElementById('lightbox-original-link');

    document.querySelectorAll('[data-open-lightbox]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const src = trigger.dataset.src || '';
            const title = trigger.dataset.title || '';

            if (lightboxImage) {
                lightboxImage.src = src;
                lightboxImage.alt = title;
            }
            if (lightboxTitle) {
                lightboxTitle.textContent = title;
            }
            if (lightboxOriginalLink) {
                lightboxOriginalLink.href = src;
            }
            openModal(lightboxModal);
        });
    });

    // Edit / Replace Photo modal
    const photoEditModal = document.getElementById('photo-edit-modal');
    const photoEditForm = document.getElementById('photo-edit-form');
    const photoEditId = document.getElementById('photo-edit-id');
    const photoEditTitle = document.getElementById('photo-edit-title');
    const photoEditPreview = document.getElementById('photo-edit-preview');
    const photoEditFile = document.getElementById('photo-edit-file');

    document.querySelectorAll('[data-open-edit-photo]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (photoEditForm) {
                photoEditForm.action = trigger.dataset.action || '#';
            }
            if (photoEditId) {
                photoEditId.value = trigger.dataset.id || '';
            }
            if (photoEditTitle) {
                photoEditTitle.value = trigger.dataset.title || '';
            }
            if (photoEditPreview) {
                photoEditPreview.src = trigger.dataset.src || '';
            }
            if (photoEditFile) {
                photoEditFile.value = '';
            }
            openModal(photoEditModal);
            photoEditTitle?.focus();
        });
    });

    // Edit / Replace Doc modal
    const docEditModal = document.getElementById('doc-edit-modal');
    const docEditForm = document.getElementById('doc-edit-form');
    const docEditId = document.getElementById('doc-edit-id');
    const docEditTitle = document.getElementById('doc-edit-title');
    const docEditFile = document.getElementById('doc-edit-file');

    document.querySelectorAll('[data-open-edit-doc]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (docEditForm) {
                docEditForm.action = trigger.dataset.action || '#';
            }
            if (docEditId) {
                docEditId.value = trigger.dataset.id || '';
            }
            if (docEditTitle) {
                docEditTitle.value = trigger.dataset.title || '';
            }
            if (docEditFile) {
                docEditFile.value = '';
            }
            openModal(docEditModal);
            docEditTitle?.focus();
        });
    });

    // Delete confirmation modal
    const deleteModal = document.getElementById('delete-confirm-modal');
    const deleteMessage = document.getElementById('delete-modal-message');
    const deleteConfirmBtn = document.getElementById('delete-modal-confirm-btn');
    let pendingDeleteForm = null;

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }
            event.preventDefault();
            pendingDeleteForm = form;

            const title = form.dataset.itemTitle || 'este archivo';
            const type = form.dataset.itemType || 'archivo';

            if (deleteMessage) {
                const isImage = type === 'imagen';
                const itemLabel = isImage ? `la imagen «${title}»` : `el documento PDF «${title}»`;
                const pronoun = isImage ? 'la' : 'lo';
                deleteMessage.textContent = `¿Estás seguro de que deseas eliminar ${itemLabel}? Esta acción también ${pronoun} borrará de forma permanente del almacenamiento.`;
            }

            openModal(deleteModal);
        });
    });

    deleteConfirmBtn?.addEventListener('click', () => {
        if (!pendingDeleteForm) {
            return;
        }
        const formToSubmit = pendingDeleteForm;
        pendingDeleteForm = null;
        formToSubmit.dataset.confirmed = 'true';
        closeModal(deleteModal);
        formToSubmit.submit();
    });

    // Close modals via close buttons, backdrop click, or Escape key
    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.closeModal;
            if (modalId === 'delete-confirm-modal') {
                pendingDeleteForm = null;
            }
            closeModal(modalId ? document.getElementById(modalId) : null);
        });
    });

    document.querySelectorAll('[role="dialog"]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                if (modal.id === 'delete-confirm-modal') {
                    pendingDeleteForm = null;
                }
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        document.querySelectorAll('[role="dialog"].flex').forEach((modal) => {
            if (modal.id === 'delete-confirm-modal') {
                pendingDeleteForm = null;
            }
            closeModal(modal);
        });
    });
});
