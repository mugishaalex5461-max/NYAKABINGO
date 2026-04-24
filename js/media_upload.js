/**
 * Media Upload System for gallery.php
 * Handles videos, songs, and short videos with drag-drop upload forms.
 */

document.addEventListener('DOMContentLoaded', function () {
    const uploadEndpoint = window.GALLERY_MEDIA_UPLOAD_ENDPOINT || 'upload_media.php';
    const autoUploadOnSelect = true;
    const forms = document.querySelectorAll('.media-upload-form');
    if (!forms.length) {
        return;
    }

    forms.forEach(function (form) {
        const mediaType = form.dataset.mediaType;
        const maxSize = parseInt(form.dataset.maxSize || '0', 10);
        const input = form.querySelector('.media-file-input');
        const dropZone = form.querySelector('.media-drop-zone');
        const status = form.querySelector('.media-upload-status');
        const selectedFile = form.querySelector('.media-selected-file');
        const button = form.querySelector('.media-upload-btn');
        let pendingFile = null;

        if (!mediaType || !input || !dropZone || !status || !selectedFile || !button) {
            return;
        }

        dropZone.addEventListener('click', function () {
            input.click();
        });

        dropZone.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropZone.style.backgroundColor = '#dbeafe';
            dropZone.style.borderColor = '#1d4ed8';
        });

        dropZone.addEventListener('dragleave', function (event) {
            event.preventDefault();
            dropZone.style.backgroundColor = '#f8fafc';
            dropZone.style.borderColor = '#3b82f6';
        });

        dropZone.addEventListener('drop', function (event) {
            event.preventDefault();
            dropZone.style.backgroundColor = '#f8fafc';
            dropZone.style.borderColor = '#3b82f6';

            if (!event.dataTransfer || !event.dataTransfer.files || !event.dataTransfer.files.length) {
                return;
            }

            setSelectedFile(event.dataTransfer.files[0]);
        });

        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) {
                return;
            }
            setSelectedFile(input.files[0]);
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            uploadMedia();
        });

        function setSelectedFile(file) {
            if (!isValidType(file, mediaType)) {
                showStatus('Please choose the correct file type for this section.', 'error');
                input.value = '';
                selectedFile.textContent = '';
                pendingFile = null;
                return;
            }

            if (maxSize > 0 && file.size > maxSize) {
                showStatus(getLimitMessage(mediaType), 'error');
                input.value = '';
                selectedFile.textContent = '';
                pendingFile = null;
                return;
            }

            pendingFile = file;
            selectedFile.textContent = 'Selected: ' + file.name;
            showStatus(autoUploadOnSelect ? 'File selected. Uploading now...' : 'File ready to upload.', 'info');

            // Update input with selected drop file if needed
            if (input.files !== undefined && input.files.length === 0) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            }

            if (autoUploadOnSelect) {
                uploadMedia();
            }
        }

        function uploadMedia() {
            if (!window.GALLERY_IS_ADMIN) {
                showStatus('Admin login required before upload. Please sign in first, then click Upload again.', 'error');
                focusAdminLogin();
                return;
            }

            const file = (input.files && input.files.length) ? input.files[0] : pendingFile;

            if (!file) {
                showStatus('Please select a file first.', 'error');
                return;
            }

            if (!isValidType(file, mediaType)) {
                showStatus('Invalid file type for this section.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('media', file);
            formData.append('media_type', mediaType);

            button.disabled = true;
            button.textContent = 'Uploading...';
            showStatus('Uploading media...', 'info');

            fetch(uploadEndpoint, {
                method: 'POST',
                body: formData
            })
                .then(async function (response) {
                    const text = await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        throw new Error('Server returned invalid response.');
                    }

                    if (!response.ok) {
                        throw new Error(data.message || 'Upload failed.');
                    }

                    return data;
                })
                .then(function (data) {
                    button.disabled = false;
                    button.textContent = getButtonText(mediaType);

                    if (!data.success) {
                        showStatus(data.message || 'Upload failed.', 'error');
                        return;
                    }

                    showStatus('Upload successful! Refreshing list...', 'success');
                    input.value = '';
                    selectedFile.textContent = '';
                    pendingFile = null;

                    setTimeout(function () {
                        window.location.href = window.location.pathname + '?media_refresh=' + Date.now();
                    }, 900);
                })
                .catch(function (error) {
                    button.disabled = false;
                    button.textContent = getButtonText(mediaType);
                    showStatus('Upload failed: ' + error.message, 'error');
                    window.console.error('Media upload failed for ' + mediaType + ':', error);
                });
        }

        function showStatus(message, type) {
            status.textContent = message;
            status.style.display = 'block';

            if (type === 'success') {
                status.style.backgroundColor = '#dcfce7';
                status.style.color = '#166534';
                status.style.borderColor = '#4ade80';
            } else if (type === 'error') {
                status.style.backgroundColor = '#fee2e2';
                status.style.color = '#991b1b';
                status.style.borderColor = '#f87171';
            } else {
                status.style.backgroundColor = '#dbeafe';
                status.style.color = '#1e3a8a';
                status.style.borderColor = '#93c5fd';
            }
        }
    });

    function isValidType(file, mediaType) {
        const type = file.type || '';
        const name = (file.name || '').toLowerCase();

        const audioByExt = /\.(mp3|mpga|wav|ogg|m4a|aac|flac|opus|amr|wma)$/i.test(name);
        const videoByExt = /\.(mp4|webm|ogg|mov|m4v|avi|mkv|mpg|mpeg|3gp)$/i.test(name);

        if (mediaType === 'songs') {
            return type.startsWith('audio/') || audioByExt;
        }

        return type.startsWith('video/') || videoByExt;
    }

    function getButtonText(mediaType) {
        if (mediaType === 'songs') {
            return 'Upload Song';
        }
        if (mediaType === 'short_videos') {
            return 'Upload Short Video';
        }
        return 'Upload Video';
    }

    function getLimitMessage(mediaType) {
        if (mediaType === 'songs') {
            return 'Song is too large. Maximum allowed is 15MB.';
        }
        if (mediaType === 'short_videos') {
            return 'Short video is too large. Maximum allowed is 20MB.';
        }
        return 'Video is too large. Maximum allowed is 40MB.';
    }

    function focusAdminLogin() {
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

    document.querySelectorAll('.media-empty-action').forEach(function (button) {
        button.addEventListener('click', function () {
            const mediaType = button.getAttribute('data-media-type') || '';
            const parentCard = button.closest('.media-card');
            const scopedSelector = '.media-upload-form[data-media-type="' + mediaType + '"]';
            const form = (parentCard ? parentCard.querySelector(scopedSelector) : null) || document.querySelector(scopedSelector);
            const dropZone = form ? form.querySelector('.media-drop-zone') : null;

            if (!form || !dropZone) {
                return;
            }

            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            dropZone.click();
        });
    });
});
