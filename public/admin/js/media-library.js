/**
 * Media Library JavaScript
 * İçerik kütüphanesi için JavaScript fonksiyonları
 */

// Selected items array
let selectedItems = [];

// DOM Elements
const uploadModal = document.getElementById('uploadModal');
const mediaDetailModal = document.getElementById('mediaDetailModal');
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const uploadProgress = document.getElementById('uploadProgress');
const uploadResults = document.getElementById('uploadResults');
const uploadResultsList = document.getElementById('uploadResultsList');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initDropZone();
    initFileInput();
    initMediaEditForm();
});

/**
 * Initialize Drop Zone
 */
function initDropZone() {
    if (!dropZone) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
    });
    
    dropZone.addEventListener('drop', handleDrop, false);
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

/**
 * Initialize File Input
 */
function initFileInput() {
    if (!fileInput) return;
    
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
}

/**
 * Handle Files Upload
 */
function handleFiles(files) {
    if (files.length === 0) return;
    
    uploadProgress.classList.remove('hidden');
    uploadResults.classList.add('hidden');
    uploadResultsList.innerHTML = '';
    
    const formData = new FormData();
    
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            document.getElementById('uploadBar').style.width = percent + '%';
            document.getElementById('uploadPercent').textContent = percent + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        uploadProgress.classList.add('hidden');
        uploadResults.classList.remove('hidden');
        
        try {
            const response = JSON.parse(xhr.responseText);
            
            if (response.results) {
                response.results.forEach(result => {
                    const item = document.createElement('div');
                    item.className = 'upload-result-item ' + (result.success ? 'success' : 'error');
                    item.innerHTML = `
                        <div class="icon">
                            <span class="material-symbols-outlined">${result.success ? 'check_circle' : 'error'}</span>
                        </div>
                        <span class="filename">${result.media?.original_name || result.filename || 'Dosya'}</span>
                        <span class="text-sm ${result.success ? 'text-green-600' : 'text-red-600'}">${result.message}</span>
                    `;
                    uploadResultsList.appendChild(item);
                });
                
                if (response.success) {
                    showToast('Dosyalar başarıyla yüklendi', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } else if (response.success) {
                const item = document.createElement('div');
                item.className = 'upload-result-item success';
                item.innerHTML = `
                    <div class="icon">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <span class="filename">${response.media?.original_name || 'Dosya'}</span>
                    <span class="text-sm text-green-600">Başarıyla yüklendi</span>
                `;
                uploadResultsList.appendChild(item);
                
                showToast('Dosya başarıyla yüklendi', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Bir hata oluştu', 'error');
            }
        } catch (e) {
            showToast('Beklenmeyen bir hata oluştu', 'error');
            console.error('Parse error:', e);
        }
    });
    
    xhr.addEventListener('error', function() {
        uploadProgress.classList.add('hidden');
        showToast('Yükleme sırasında bir hata oluştu', 'error');
    });
    
    xhr.open('POST', getAdminUrl('media/upload'));
    xhr.send(formData);
}

/**
 * Open Upload Modal
 */
function openUploadModal() {
    uploadModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Reset state
    uploadProgress.classList.add('hidden');
    uploadResults.classList.add('hidden');
    uploadResultsList.innerHTML = '';
    document.getElementById('uploadBar').style.width = '0%';
    document.getElementById('uploadPercent').textContent = '0%';
    fileInput.value = '';
}

/**
 * Close Upload Modal
 */
function closeUploadModal() {
    uploadModal.classList.add('hidden');
    document.body.style.overflow = '';
}

/**
 * Open Media Detail Modal
 */
function openMediaDetail(id) {
    const mediaPreview = document.getElementById('mediaPreview');
    
    // Show loading
    mediaPreview.innerHTML = '<div class="loading-spinner mx-auto"></div>';
    mediaDetailModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Fetch media details
    fetch(getAdminUrl('media/get/' + id))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const media = data.media;
                
                // Set form values
                document.getElementById('mediaId').value = media.id;
                document.getElementById('mediaFileName').textContent = media.original_name;
                document.getElementById('mediaFileUrl').value = media.file_url;
                document.getElementById('mediaMimeType').textContent = media.mime_type;
                document.getElementById('mediaFileSize').textContent = media.formatted_size;
                document.getElementById('mediaAltText').value = media.alt_text || '';
                document.getElementById('mediaDescription').value = media.description || '';
                
                // Set preview
                if (media.file_type === 'image') {
                    mediaPreview.innerHTML = `<img src="${media.file_url}" alt="${media.alt_text || media.original_name}" class="max-h-80">`;
                } else if (media.file_type === 'video') {
                    mediaPreview.innerHTML = `<video controls class="max-h-80"><source src="${media.file_url}" type="${media.mime_type}"></video>`;
                } else if (media.file_type === 'audio') {
                    mediaPreview.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full p-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
                            <span class="material-symbols-outlined text-white text-6xl mb-4">audiotrack</span>
                            <audio controls class="w-full"><source src="${media.file_url}" type="${media.mime_type}"></audio>
                        </div>
                    `;
                } else {
                    mediaPreview.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full p-8">
                            <span class="material-symbols-outlined text-gray-400 text-6xl mb-2">description</span>
                            <p class="text-gray-500 dark:text-gray-400">${media.original_name}</p>
                            <a href="${media.file_url}" target="_blank" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                Dosyayı İndir
                            </a>
                        </div>
                    `;
                }
            } else {
                showToast(data.message || 'Medya yüklenemedi', 'error');
                closeMediaDetail();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Medya yüklenirken bir hata oluştu', 'error');
            closeMediaDetail();
        });
}

/**
 * Close Media Detail Modal
 */
function closeMediaDetail() {
    mediaDetailModal.classList.add('hidden');
    document.body.style.overflow = '';
}

/**
 * Initialize Media Edit Form
 */
function initMediaEditForm() {
    const form = document.getElementById('mediaEditForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('mediaId').value;
        const formData = new FormData(form);
        
        fetch(getAdminUrl('media/update/' + id), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Medya başarıyla güncellendi', 'success');
            } else {
                showToast(data.message || 'Güncelleme başarısız', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Güncelleme sırasında bir hata oluştu', 'error');
        });
    });
}

/**
 * Delete Media
 */
function deleteMedia(id) {
    if (!confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
        return;
    }
    
    fetch(getAdminUrl('media/delete/' + id), {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Dosya başarıyla silindi', 'success');
            closeMediaDetail();
            
            // Remove from grid
            const item = document.querySelector(`.media-item[data-id="${id}"]`);
            if (item) {
                item.remove();
            }
            
            // Reload if no items left
            const grid = document.getElementById('mediaGrid');
            if (grid && grid.children.length === 0) {
                location.reload();
            }
        } else {
            showToast(data.message || 'Silme başarısız', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Silme sırasında bir hata oluştu', 'error');
    });
}

/**
 * Toggle Selection
 */
function toggleSelection(element) {
    const id = element.dataset.id;
    
    if (element.classList.contains('selected')) {
        element.classList.remove('selected');
        selectedItems = selectedItems.filter(item => item !== id);
    } else {
        element.classList.add('selected');
        selectedItems.push(id);
    }
    
    updateSelectionUI();
}

/**
 * Update Selection UI
 */
function updateSelectionUI() {
    const selectionActions = document.getElementById('selectionActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedItems.length > 0) {
        selectionActions.classList.remove('hidden');
        selectedCount.textContent = selectedItems.length;
        document.body.classList.add('selection-mode');
    } else {
        selectionActions.classList.add('hidden');
        document.body.classList.remove('selection-mode');
    }
}

/**
 * Clear Selection
 */
function clearSelection() {
    selectedItems = [];
    document.querySelectorAll('.media-item.selected').forEach(item => {
        item.classList.remove('selected');
    });
    updateSelectionUI();
}

/**
 * Delete Selected
 */
function deleteSelected() {
    if (selectedItems.length === 0) return;
    
    if (!confirm(`${selectedItems.length} dosyayı silmek istediğinizden emin misiniz?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('ids', JSON.stringify(selectedItems));
    
    fetch(getAdminUrl('media/delete-multiple'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Remove from grid
            selectedItems.forEach(id => {
                const item = document.querySelector(`.media-item[data-id="${id}"]`);
                if (item) {
                    item.remove();
                }
            });
            
            clearSelection();
            
            // Reload if no items left
            const grid = document.getElementById('mediaGrid');
            if (grid && grid.children.length === 0) {
                location.reload();
            }
        } else {
            showToast(data.message || 'Silme başarısız', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Silme sırasında bir hata oluştu', 'error');
    });
}

/**
 * Copy URL to Clipboard
 */
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('URL panoya kopyalandı', 'success');
    }).catch(err => {
        console.error('Copy error:', err);
        showToast('URL kopyalanamadı', 'error');
    });
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');
    
    const icons = {
        success: 'check_circle',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };
    
    toastIcon.textContent = icons[type] || icons.info;
    toastMessage.textContent = message;
    
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

/**
 * Get Admin URL
 */
function getAdminUrl(page) {
    // Get current URL and extract base
    const currentUrl = window.location.href;
    const adminMatch = currentUrl.match(/(.*\/admin\.php)/);
    
    if (adminMatch) {
        return adminMatch[1] + '?page=' + page;
    }
    
    return '/public/admin.php?page=' + page;
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape to close modals
    if (e.key === 'Escape') {
        if (!uploadModal.classList.contains('hidden')) {
            closeUploadModal();
        }
        if (!mediaDetailModal.classList.contains('hidden')) {
            closeMediaDetail();
        }
        if (selectedItems.length > 0) {
            clearSelection();
        }
    }
    
    // Delete selected with Delete key
    if (e.key === 'Delete' && selectedItems.length > 0) {
        deleteSelected();
    }
    
    // Select all with Ctrl+A
    if (e.key === 'a' && e.ctrlKey) {
        e.preventDefault();
        document.querySelectorAll('.media-item').forEach(item => {
            if (!item.classList.contains('selected')) {
                item.classList.add('selected');
                selectedItems.push(item.dataset.id);
            }
        });
        updateSelectionUI();
    }
});

