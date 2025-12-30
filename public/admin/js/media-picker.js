/**
 * Media Picker Component
 * İçerik Kütüphanesi'nden dosya seçme bileşeni
 */

class MediaPicker {
    constructor(options = {}) {
        this.options = {
            onSelect: options.onSelect || null,
            multiple: options.multiple || false,
            type: options.type || 'all', // all, image, video, audio, document
            targetInput: options.targetInput || null,
            targetPreview: options.targetPreview || null,
            ...options
        };
        
        this.selectedItems = [];
        this.currentPage = 1;
        this.modal = null;
        
        this.init();
    }
    
    init() {
        this.createModal();
        this.bindEvents();
    }
    
    createModal() {
        // Modal zaten varsa oluşturma
        if (document.getElementById('media-picker-modal')) {
            this.modal = document.getElementById('media-picker-modal');
            return;
        }
        
        const modalHTML = `
            <div id="media-picker-modal" class="fixed inset-0 z-[99999] hidden">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="window.mediaPicker?.close()"></div>
                <div class="absolute inset-4 md:inset-8 lg:inset-12 bg-white dark:bg-background-dark rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="max-height: calc(100vh - 2rem);">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-2xl">perm_media</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">İçerik Kütüphanesi</h3>
                        </div>
                        <button onclick="window.mediaPicker?.close()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                            <span class="material-symbols-outlined text-gray-500">close</span>
                        </button>
                    </div>
                    
                    <!-- Toolbar -->
                    <div class="flex flex-col sm:flex-row gap-4 px-6 py-4 border-b border-gray-200 dark:border-white/10">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                            <input 
                                type="text" 
                                id="media-picker-search"
                                placeholder="Dosya ara..." 
                                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                            >
                        </div>
                        
                        <!-- Type Filter -->
                        <div class="flex gap-2">
                            <button type="button" data-filter="all" class="media-filter-btn active px-3 py-2 rounded-lg text-sm font-medium transition-colors">Tümü</button>
                            <button type="button" data-filter="image" class="media-filter-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors">Resimler</button>
                            <button type="button" data-filter="video" class="media-filter-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors">Videolar</button>
                        </div>
                        
                        <!-- Upload Button -->
                        <button type="button" onclick="window.mediaPicker?.showUpload()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined text-xl">upload</span>
                            <span class="hidden sm:inline">Yükle</span>
                        </button>
                    </div>
                    
                    <!-- Upload Area (Hidden by default) -->
                    <div id="media-picker-upload-area" class="hidden px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                        <div id="media-picker-dropzone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center transition-colors hover:border-primary hover:bg-primary/5">
                            <input type="file" id="media-picker-file-input" class="hidden" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx">
                            <span class="material-symbols-outlined text-gray-400 text-4xl mb-2">cloud_upload</span>
                            <p class="text-gray-600 dark:text-gray-400 mb-2">Dosyaları sürükleyip bırakın veya</p>
                            <button type="button" onclick="document.getElementById('media-picker-file-input').click()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                Dosya Seç
                            </button>
                        </div>
                        <div id="media-picker-upload-progress" class="hidden mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Yükleniyor...</span>
                                <span id="media-picker-upload-percent" class="text-sm text-gray-500">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                <div id="media-picker-upload-bar" class="bg-primary h-2 rounded-full transition-all" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Grid -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <div id="media-picker-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <!-- Items will be loaded here -->
                        </div>
                        
                        <!-- Loading -->
                        <div id="media-picker-loading" class="hidden text-center py-12">
                            <div class="inline-block w-8 h-8 border-4 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                            <p class="text-gray-500 dark:text-gray-400 mt-4">Yükleniyor...</p>
                        </div>
                        
                        <!-- Empty State -->
                        <div id="media-picker-empty" class="hidden text-center py-12">
                            <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">perm_media</span>
                            <p class="text-gray-500 dark:text-gray-400">Henüz dosya yüklenmemiş</p>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                        <div id="media-picker-selection-info" class="text-sm text-gray-500 dark:text-gray-400">
                            <span id="media-picker-selected-count">0</span> dosya seçildi
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="window.mediaPicker?.close()" class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                İptal
                            </button>
                            <button type="button" id="media-picker-select-btn" onclick="window.mediaPicker?.confirmSelection()" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Seç
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('media-picker-modal');
        
        // Add styles
        this.addStyles();
    }
    
    addStyles() {
        if (document.getElementById('media-picker-styles')) return;
        
        const styles = `
            <style id="media-picker-styles">
                .media-filter-btn {
                    background: transparent;
                    color: #6b7280;
                }
                .media-filter-btn:hover {
                    background: rgba(0, 0, 0, 0.05);
                }
                .dark .media-filter-btn:hover {
                    background: rgba(255, 255, 255, 0.05);
                }
                .media-filter-btn.active {
                    background: #137fec;
                    color: white;
                }
                .media-picker-item {
                    position: relative;
                    aspect-ratio: 1;
                    border-radius: 0.5rem;
                    overflow: hidden;
                    cursor: pointer;
                    border: 2px solid transparent;
                    transition: all 0.2s;
                }
                .media-picker-item:hover {
                    border-color: #137fec;
                    transform: translateY(-2px);
                }
                .media-picker-item.selected {
                    border-color: #137fec;
                    box-shadow: 0 0 0 3px rgba(19, 127, 236, 0.2);
                }
                .media-picker-item.selected .media-picker-check {
                    display: flex;
                }
                .media-picker-check {
                    display: none;
                    position: absolute;
                    top: 0.5rem;
                    right: 0.5rem;
                    width: 1.5rem;
                    height: 1.5rem;
                    background: #137fec;
                    border-radius: 50%;
                    align-items: center;
                    justify-content: center;
                    color: white;
                }
                #media-picker-dropzone.drag-over {
                    border-color: #137fec;
                    background: rgba(19, 127, 236, 0.1);
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styles);
    }
    
    bindEvents() {
        // Search
        const searchInput = document.getElementById('media-picker-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.loadMedia(), 300);
            });
        }
        
        // Filter buttons
        document.querySelectorAll('.media-filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.media-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.options.type = btn.dataset.filter;
                this.loadMedia();
            });
        });
        
        // File input
        const fileInput = document.getElementById('media-picker-file-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleUpload(e.target.files));
        }
        
        // Drag & Drop
        const dropzone = document.getElementById('media-picker-dropzone');
        if (dropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
                dropzone.addEventListener(event, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            
            ['dragenter', 'dragover'].forEach(event => {
                dropzone.addEventListener(event, () => dropzone.classList.add('drag-over'));
            });
            
            ['dragleave', 'drop'].forEach(event => {
                dropzone.addEventListener(event, () => dropzone.classList.remove('drag-over'));
            });
            
            dropzone.addEventListener('drop', (e) => {
                this.handleUpload(e.dataTransfer.files);
            });
        }
    }
    
    open(options = {}) {
        this.options = { ...this.options, ...options };
        this.selectedItems = [];
        this.currentPage = 1;
        
        // Reset filters
        document.querySelectorAll('.media-filter-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === 'all');
        });
        this.options.type = 'all';
        
        // Reset search
        const searchInput = document.getElementById('media-picker-search');
        if (searchInput) searchInput.value = '';
        
        // Show modal
        this.modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Load media
        this.loadMedia();
        
        // Update selection UI
        this.updateSelectionUI();
    }
    
    close() {
        this.modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Hide upload area
        document.getElementById('media-picker-upload-area')?.classList.add('hidden');
    }
    
    showUpload() {
        const uploadArea = document.getElementById('media-picker-upload-area');
        uploadArea.classList.toggle('hidden');
    }
    
    async loadMedia() {
        const grid = document.getElementById('media-picker-grid');
        const loading = document.getElementById('media-picker-loading');
        const empty = document.getElementById('media-picker-empty');
        
        grid.innerHTML = '';
        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        
        const search = document.getElementById('media-picker-search')?.value || '';
        const type = this.options.type || 'all';
        
        try {
            const url = this.getAdminUrl('media/list') + '&type=' + encodeURIComponent(type) + '&search=' + encodeURIComponent(search) + '&p=' + this.currentPage;
            const response = await fetch(url);
            
            // Önce text olarak al, sonra JSON parse et
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', text);
                loading.classList.add('hidden');
                grid.innerHTML = '<div class="col-span-full text-center text-red-500 py-8">Sunucu yanıtı geçersiz. Konsolu kontrol edin.</div>';
                return;
            }
            
            loading.classList.add('hidden');
            
            if (data.success && data.media && data.media.length > 0) {
                data.media.forEach(item => {
                    grid.appendChild(this.createMediaItem(item));
                });
            } else {
                empty.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Media load error:', error);
            loading.classList.add('hidden');
            grid.innerHTML = '<div class="col-span-full text-center text-red-500 py-8">Dosyalar yüklenirken bir hata oluştu: ' + error.message + '</div>';
        }
    }
    
    createMediaItem(item) {
        const div = document.createElement('div');
        div.className = 'media-picker-item bg-gray-100 dark:bg-white/5';
        div.dataset.id = item.id;
        div.dataset.url = item.file_url;
        div.dataset.name = item.original_name;
        div.dataset.type = item.file_type;
        
        let preview = '';
        if (item.file_type === 'image') {
            preview = `<img src="${item.file_url}" alt="${item.original_name}" class="w-full h-full object-cover">`;
        } else if (item.file_type === 'video') {
            preview = `<div class="w-full h-full flex items-center justify-center bg-gray-800"><span class="material-symbols-outlined text-white text-3xl">play_circle</span></div>`;
        } else if (item.file_type === 'audio') {
            preview = `<div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500"><span class="material-symbols-outlined text-white text-3xl">audiotrack</span></div>`;
        } else {
            preview = `<div class="w-full h-full flex items-center justify-center"><span class="material-symbols-outlined text-gray-400 text-3xl">description</span></div>`;
        }
        
        div.innerHTML = `
            ${preview}
            <div class="media-picker-check">
                <span class="material-symbols-outlined text-sm">check</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/60 to-transparent">
                <p class="text-white text-xs truncate">${item.original_name}</p>
            </div>
        `;
        
        div.addEventListener('click', () => this.toggleSelection(div, item));
        
        return div;
    }
    
    toggleSelection(element, item) {
        if (this.options.multiple) {
            const index = this.selectedItems.findIndex(i => i.id === item.id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
                element.classList.remove('selected');
            } else {
                this.selectedItems.push(item);
                element.classList.add('selected');
            }
        } else {
            // Single selection
            document.querySelectorAll('.media-picker-item.selected').forEach(el => el.classList.remove('selected'));
            this.selectedItems = [item];
            element.classList.add('selected');
        }
        
        this.updateSelectionUI();
    }
    
    updateSelectionUI() {
        const count = this.selectedItems.length;
        document.getElementById('media-picker-selected-count').textContent = count;
        document.getElementById('media-picker-select-btn').disabled = count === 0;
    }
    
    confirmSelection() {
        if (this.selectedItems.length === 0) return;
        
        const selected = this.options.multiple ? this.selectedItems : this.selectedItems[0];
        
        // Update target input if provided
        if (this.options.targetInput) {
            const input = document.getElementById(this.options.targetInput) || document.querySelector(this.options.targetInput);
            if (input) {
                input.value = this.options.multiple 
                    ? this.selectedItems.map(i => i.file_url).join(',')
                    : selected.file_url;
            }
        }
        
        // Update preview if provided
        if (this.options.targetPreview) {
            const preview = document.getElementById(this.options.targetPreview) || document.querySelector(this.options.targetPreview);
            if (preview) {
                if (selected.file_type === 'image') {
                    preview.innerHTML = `<img src="${selected.file_url}" alt="${selected.original_name}" class="max-w-full max-h-full object-contain">`;
                } else {
                    preview.innerHTML = `<span class="material-symbols-outlined text-4xl">${selected.file_type === 'video' ? 'videocam' : 'description'}</span><p class="text-sm mt-2">${selected.original_name}</p>`;
                }
            }
        }
        
        // Call onSelect callback
        if (this.options.onSelect) {
            this.options.onSelect(selected);
        }
        
        this.close();
    }
    
    async handleUpload(files) {
        if (!files || files.length === 0) return;
        
        const progressArea = document.getElementById('media-picker-upload-progress');
        const progressBar = document.getElementById('media-picker-upload-bar');
        const progressPercent = document.getElementById('media-picker-upload-percent');
        
        progressArea.classList.remove('hidden');
        
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        
        try {
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressPercent.textContent = percent + '%';
                }
            });
            
            xhr.addEventListener('load', () => {
                progressArea.classList.add('hidden');
                progressBar.style.width = '0%';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        this.loadMedia();
                        document.getElementById('media-picker-upload-area').classList.add('hidden');
                    }
                } catch (e) {
                    console.error('Upload response error:', e);
                }
            });
            
            xhr.open('POST', this.getAdminUrl('media/upload'));
            xhr.send(formData);
            
        } catch (error) {
            console.error('Upload error:', error);
            progressArea.classList.add('hidden');
        }
    }
    
    getAdminUrl(page) {
        const currentUrl = window.location.href;
        const adminMatch = currentUrl.match(/(.*\/admin\.php)/);
        if (adminMatch) {
            return adminMatch[1] + '?page=' + page;
        }
        return '/public/admin.php?page=' + page;
    }
}

// Global instance
window.mediaPicker = null;

// Helper function to open media picker
function openMediaPicker(options = {}) {
    if (!window.mediaPicker) {
        window.mediaPicker = new MediaPicker();
    }
    window.mediaPicker.open(options);
}

