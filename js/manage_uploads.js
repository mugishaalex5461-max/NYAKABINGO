/**
 * Upload management for gallery items.
 * Supports delete, rename, and replace actions.
 */

document.addEventListener('DOMContentLoaded', function () {
    const endpoint = window.GALLERY_MANAGE_ENDPOINT || 'manage_uploads.php';

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.classList.contains('manage-delete')) {
            event.preventDefault();
            event.stopPropagation();
            if (!window.GALLERY_IS_ADMIN) {
                promptAdminLoginRequired();
                return;
            }
            handleDelete(target);
            return;
        }

        if (target.classList.contains('manage-rename')) {
            event.preventDefault();
            event.stopPropagation();
            if (!window.GALLERY_IS_ADMIN) {
                promptAdminLoginRequired();
                return;
            }
            handleRename(target);
            return;
        }

        if (target.classList.contains('manage-replace')) {
            event.preventDefault();
            event.stopPropagation();
            if (!window.GALLERY_IS_ADMIN) {
                promptAdminLoginRequired();
                return;
            }
            const input = target.parentElement ? target.parentElement.querySelector('.manage-replace-input') : null;
            if (input) {
                input.click();
            }
        }
    });

    document.addEventListener('change', function (event) {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        if (!target.classList.contains('manage-replace-input')) {
            return;
        }

        if (!window.GALLERY_IS_ADMIN) {
            promptAdminLoginRequired();
            target.value = '';
            return;
        }

        const button = target.parentElement ? target.parentElement.querySelector('.manage-replace') : null;
        if (!button) {
            return;
        }

        handleReplace(button, target);
    });

    async function handleDelete(button) {
        const mediaType = button.dataset.mediaType || '';
        const filename = button.dataset.filename || '';
        if (!mediaType || !filename) {
            return;
        }

        const ok = window.confirm('Delete this file? This cannot be undone.');
        if (!ok) {
            return;
        }

        setButtonBusy(button, true, 'Deleting...');

        try {
            const data = await postForm(endpoint, {
                action: 'delete',
                media_type: mediaType,
                filename: filename
            });

            if (!data.success) {
                window.alert(data.message || 'Delete failed.');
                setButtonBusy(button, false);
                return;
            }

            window.location.reload();
        } catch (error) {
            window.alert('Delete failed: ' + error.message);
            setButtonBusy(button, false);
        }
    }

    async function handleRename(button) {
        const mediaType = button.dataset.mediaType || '';
        const filename = button.dataset.filename || '';
        if (!mediaType || !filename) {
            return;
        }

        const dot = filename.lastIndexOf('.');
        const currentName = dot > 0 ? filename.substring(0, dot) : filename;
        const newName = window.prompt('Enter new file name (without extension):', currentName);
        if (!newName) {
            return;
        }

        setButtonBusy(button, true, 'Saving...');

        try {
            const data = await postForm(endpoint, {
                action: 'rename',
                media_type: mediaType,
                filename: filename,
                new_name: newName
            });

            if (!data.success) {
                window.alert(data.message || 'Rename failed.');
                setButtonBusy(button, false);
                return;
            }

            window.location.reload();
        } catch (error) {
            window.alert('Rename failed: ' + error.message);
            setButtonBusy(button, false);
        }
    }

    async function handleReplace(button, input) {
        const mediaType = button.dataset.mediaType || '';
        const filename = button.dataset.filename || '';
        const file = input.files && input.files.length ? input.files[0] : null;
        if (!mediaType || !filename || !file) {
            return;
        }

        const ok = window.confirm('Replace "' + filename + '" with selected file?');
        if (!ok) {
            input.value = '';
            return;
        }

        setButtonBusy(button, true, 'Replacing...');

        const formData = new FormData();
        formData.append('action', 'replace');
        formData.append('media_type', mediaType);
        formData.append('filename', filename);
        formData.append('new_file', file);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error('Server returned invalid JSON response.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Replace failed.');
            }

            window.location.reload();
        } catch (error) {
            window.alert('Replace failed: ' + error.message);
            setButtonBusy(button, false);
            input.value = '';
        }
    }

    async function postForm(url, payload) {
        const formData = new FormData();
        Object.keys(payload).forEach(function (key) {
            formData.append(key, payload[key]);
        });

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (error) {
            throw new Error('Server returned invalid JSON response.');
        }

        if (!response.ok) {
            throw new Error(data.message || 'Request failed.');
        }

        return data;
    }

    function setButtonBusy(button, busy, busyText) {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent || '';
        }

        button.disabled = busy;
        button.textContent = busy ? (busyText || 'Please wait...') : button.dataset.originalText;
    }

    function promptAdminLoginRequired() {
        window.alert('Admin login required first. Please sign in with valid credentials.');

        const emailInput = document.getElementById('admin-email-input');
        const loginBox = document.querySelector('.auth-box');
        if (loginBox) {
            loginBox.classList.add('show');
            loginBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (emailInput) {
            emailInput.focus();
        }
    }
});
