/**
 * Image Upload System for Gallery
 */

document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('image-upload-form');
    const imageInput = document.getElementById('image-input');
    const uploadBtn = document.getElementById('upload-btn');
    const uploadStatus = document.getElementById('upload-status');
    const dragDropZone = document.getElementById('drag-drop-zone');
    const quickUploadButtons = document.querySelectorAll('.quick-upload-btn');
    let selectedTargetFilename = '';
    
    if (!uploadForm) return;
    
    // Drag and drop functionality
    if (dragDropZone) {
        dragDropZone.addEventListener('dragover', handleDragOver);
        dragDropZone.addEventListener('dragleave', handleDragLeave);
        dragDropZone.addEventListener('drop', handleDrop);
    }
    
    // File input change
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });
    }

    if (quickUploadButtons.length > 0 && imageInput) {
        quickUploadButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                selectedTargetFilename = button.dataset.targetFilename || '';
                imageInput.click();
            });
        });
    }
    
    // Form submit
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            uploadImage();
        });
    }
    
    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        dragDropZone.style.backgroundColor = '#dbeafe';
        dragDropZone.style.borderColor = '#3b82f6';
    }
    
    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        dragDropZone.style.backgroundColor = '#f3f4f6';
        dragDropZone.style.borderColor = '#e5e7eb';
    }
    
    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        dragDropZone.style.backgroundColor = '#f3f4f6';
        dragDropZone.style.borderColor = '#e5e7eb';
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    }
    
    function handleFiles(files) {
        if (files.length === 0) return;
        
        const file = files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showStatus('Please select an image file!', 'error');
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showStatus('File size must be less than 5MB!', 'error');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            if (preview) {
                preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 5px;">`;
            }
        };
        reader.readAsDataURL(file);
        
        imageInput.files = files;

        if (selectedTargetFilename !== '') {
            uploadBtn.textContent = 'Upload to ' + selectedTargetFilename;
            showStatus('Selected target: ' + selectedTargetFilename + '. Click "Upload Image" to save.', 'info');
        } else {
            uploadBtn.textContent = 'Upload Image';
        }
    }
    
    function uploadImage() {
        if (!window.GALLERY_IS_ADMIN) {
            showStatus('Admin login required before uploading. Please sign in first.', 'error');
            focusAdminLogin();
            return;
        }

        if (!imageInput.files || imageInput.files.length === 0) {
            showStatus('Please select an image!', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', imageInput.files[0]);
        if (selectedTargetFilename !== '') {
            formData.append('target_filename', selectedTargetFilename);
        }
        
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';
        showStatus('Uploading image...', 'info');
        
        fetch('/NYAKABINGO_PRIMARY/pages/upload_images.php', {
            method: 'POST',
            body: formData
        })
        .then(async (response) => {
            const text = await response.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error('Server returned invalid response. Please check PHP upload errors.');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Upload request failed.');
            }

            return data;
        })
        .then(data => {
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Upload Image';
            
            if (data.success) {
                showStatus('Image uploaded successfully! ✓', 'success');
                imageInput.value = '';
                selectedTargetFilename = '';
                const preview = document.getElementById('image-preview');
                if (preview) preview.innerHTML = '';
                uploadBtn.textContent = 'Upload Image';
                
                // Reload gallery
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showStatus('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Upload Image';
            showStatus('Upload failed: ' + error.message, 'error');
            console.error('Error:', error);
        });
    }
    
    function showStatus(message, type) {
        if (!uploadStatus) return;
        
        uploadStatus.textContent = message;
        uploadStatus.style.display = 'block';
        
        if (type === 'success') {
            uploadStatus.style.backgroundColor = '#d1fae5';
            uploadStatus.style.color = '#065f46';
            uploadStatus.style.borderColor = '#6ee7b7';
        } else if (type === 'error') {
            uploadStatus.style.backgroundColor = '#fee2e2';
            uploadStatus.style.color = '#991b1b';
            uploadStatus.style.borderColor = '#fca5a5';
        } else {
            uploadStatus.style.backgroundColor = '#dbeafe';
            uploadStatus.style.color = '#1e40af';
            uploadStatus.style.borderColor = '#93c5fd';
        }
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
});
