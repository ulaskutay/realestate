/**
 * Slider Editor JavaScript
 * Drag & drop, layer yönetimi ve düzenleme özellikleri
 */

class SliderEditor {
    constructor(config) {
        this.itemId = config.itemId;
        this.adminUrl = config.adminUrl;
        this.canvas = document.getElementById('slider-canvas');
        this.layersContainer = document.getElementById('layers-container');
        this.propertiesPanel = document.getElementById('properties-panel');
        this.selectedLayerId = null;
        this.isDragging = false;
        this.dragOffset = { x: 0, y: 0 };
        this.layers = [];
        this.copiedLayer = null; // Kopyalanan layer verisi
        
        this.init();
    }
    
    init() {
        this.loadLayers();
        this.setupCanvas();
        this.setupLayerSelection();
        this.setupDragAndDrop();
        this.setupKeyboard();
    }
    
    async loadLayers() {
        // Layer'lar zaten DOM'da var, bunları parse edelim
        const layerElements = this.layersContainer.querySelectorAll('.layer-element');
        this.layers = Array.from(layerElements).map(el => ({
            id: parseInt(el.dataset.layerId),
            element: el,
            x: 0,
            y: 0,
            width: 0,
            height: 0
        }));
    }
    
    setupCanvas() {
        if (!this.canvas) return;
        
        // Canvas'a tıklama - seçimi temizle
        this.canvas.addEventListener('click', (e) => {
            if (e.target === this.canvas || e.target === this.layersContainer) {
                this.deselectLayer();
            }
        });
    }
    
    setupLayerSelection() {
        // Layer'lara tıklama - seç
        document.addEventListener('click', (e) => {
            const layerElement = e.target.closest('.layer-element');
            if (layerElement) {
                e.stopPropagation();
                this.selectLayer(parseInt(layerElement.dataset.layerId));
            }
        });
        
        // Layer listesinden seç
        document.addEventListener('click', (e) => {
            const layerItem = e.target.closest('.layer-item');
            if (layerItem && !e.target.closest('button')) {
                const layerId = parseInt(layerItem.dataset.layerId);
                this.selectLayer(layerId);
            }
        });
    }
    
    setupDragAndDrop() {
        // Canvas üzerinde layer sürükleme
        let isDraggingLayer = false;
        let currentLayer = null;
        let startX, startY, startLeft, startTop;
        
        this.layersContainer.addEventListener('mousedown', (e) => {
            const layerElement = e.target.closest('.layer-element');
            if (!layerElement) return;
            
            // Sadece layer'ın kendisine tıklanırsa
            if (e.target === layerElement || layerElement.contains(e.target)) {
                e.preventDefault();
                e.stopPropagation();
                
                isDraggingLayer = true;
                currentLayer = layerElement;
                this.selectLayer(parseInt(currentLayer.dataset.layerId));
                
                const rect = this.canvas.getBoundingClientRect();
                const layerRect = currentLayer.getBoundingClientRect();
                
                startX = e.clientX;
                startY = e.clientY;
                startLeft = layerRect.left - rect.left;
                startTop = layerRect.top - rect.top;
                
                currentLayer.classList.add('dragging');
                document.body.style.cursor = 'grabbing';
            }
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDraggingLayer || !currentLayer) return;
            
            e.preventDefault();
            
            const rect = this.canvas.getBoundingClientRect();
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            let newLeft = startLeft + deltaX;
            let newTop = startTop + deltaY;
            
            // Canvas sınırları içinde tut
            const layerRect = currentLayer.getBoundingClientRect();
            const layerWidth = layerRect.width;
            const layerHeight = layerRect.height;
            
            newLeft = Math.max(0, Math.min(newLeft, rect.width - layerWidth));
            newTop = Math.max(0, Math.min(newTop, rect.height - layerHeight));
            
            // Pozisyonu güncelle
            const percentX = (newLeft / rect.width) * 100;
            const percentY = (newTop / rect.height) * 100;
            
            currentLayer.style.left = percentX + '%';
            currentLayer.style.top = percentY + '%';
        });
        
        document.addEventListener('mouseup', async (e) => {
            if (!isDraggingLayer || !currentLayer) return;
            
            isDraggingLayer = false;
            currentLayer.classList.remove('dragging');
            document.body.style.cursor = '';
            
            // Pozisyonu kaydet
            const rect = this.canvas.getBoundingClientRect();
            const layerRect = currentLayer.getBoundingClientRect();
            
            const percentX = ((layerRect.left - rect.left) / rect.width) * 100;
            const percentY = ((layerRect.top - rect.top) / rect.height) * 100;
            
            await this.updateLayerPosition(
                parseInt(currentLayer.dataset.layerId),
                percentX + '%',
                percentY + '%'
            );
            
            currentLayer = null;
        });
        
        // Layer listesinde sıralama için drag & drop
        const layersList = document.getElementById('layers-list');
        if (layersList) {
            let draggedElement = null;
            
            layersList.addEventListener('dragstart', (e) => {
                if (e.target.classList.contains('layer-item')) {
                    draggedElement = e.target;
                    e.target.classList.add('dragging');
                }
            });
            
            layersList.addEventListener('dragover', (e) => {
                e.preventDefault();
                const afterElement = this.getDragAfterElement(layersList, e.clientY);
                if (afterElement == null) {
                    layersList.appendChild(draggedElement);
                } else {
                    layersList.insertBefore(draggedElement, afterElement);
                }
            });
            
            layersList.addEventListener('dragend', (e) => {
                if (draggedElement) {
                    draggedElement.classList.remove('dragging');
                    draggedElement = null;
                }
            });
        }
    }
    
    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.layer-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    setupKeyboard() {
        document.addEventListener('keydown', (e) => {
            if (this.selectedLayerId === null) return;
            
            // Delete tuşu - layer sil
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (confirm('Bu layer\'ı silmek istediğinizden emin misiniz?')) {
                    this.deleteLayer(this.selectedLayerId);
                }
            }
            
            // Arrow keys - layer taşı (Shift ile daha hızlı)
            const step = e.shiftKey ? 10 : 1;
            let deltaX = 0;
            let deltaY = 0;
            
            if (e.key === 'ArrowLeft') {
                deltaX = -step;
                e.preventDefault();
            } else if (e.key === 'ArrowRight') {
                deltaX = step;
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                deltaY = -step;
                e.preventDefault();
            } else if (e.key === 'ArrowDown') {
                deltaY = step;
                e.preventDefault();
            }
            
            if (deltaX !== 0 || deltaY !== 0) {
                this.moveLayerByPixels(this.selectedLayerId, deltaX, deltaY);
            }
        });
    }
    
    selectLayer(layerId) {
        // Önceki seçimi temizle
        this.deselectLayer();
        
        // Yeni layer'ı seç
        this.selectedLayerId = layerId;
        
        const layerElement = document.querySelector(`.layer-element[data-layer-id="${layerId}"]`);
        const layerItem = document.querySelector(`.layer-item[data-layer-id="${layerId}"]`);
        
        if (layerElement) {
            layerElement.classList.add('selected');
            // Resize handle'ları ekle
            this.addResizeHandles(layerElement);
        }
        
        if (layerItem) {
            layerItem.classList.add('bg-primary/10', 'border-primary/50');
            layerItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        // Özellikler panelini yükle
        this.loadLayerProperties(layerId);
    }
    
    deselectLayer() {
        if (this.selectedLayerId !== null) {
            const layerElement = document.querySelector(`.layer-element[data-layer-id="${this.selectedLayerId}"]`);
            const layerItem = document.querySelector(`.layer-item[data-layer-id="${this.selectedLayerId}"]`);
            
            if (layerElement) {
                layerElement.classList.remove('selected');
                // Resize handle'ları kaldır
                this.removeResizeHandles(layerElement);
            }
            
            if (layerItem) {
                layerItem.classList.remove('bg-primary/10', 'border-primary/50');
            }
        }
        
        this.selectedLayerId = null;
        this.showEmptyProperties();
    }
    
    async loadLayerProperties(layerId) {
        try {
            const response = await fetch(`${this.adminUrl}sliders/layer-data/${layerId}`);
            const data = await response.json();
            
            if (data.success && data.layer) {
                this.renderPropertiesPanel(data.layer);
            } else {
                // Eğer endpoint yoksa, DOM'dan veriyi al
                this.loadLayerPropertiesFromDOM(layerId);
            }
        } catch (error) {
            console.error('Layer özellikleri yüklenemedi:', error);
            // DOM'dan yükle
            this.loadLayerPropertiesFromDOM(layerId);
        }
    }
    
    loadLayerPropertiesFromDOM(layerId) {
        // Önce global layerData'dan dene (PHP'den aktarılan)
        if (window.layerDataFromPHP) {
            const layerFromPHP = window.layerDataFromPHP.find(l => l.id === layerId);
            if (layerFromPHP) {
                this.renderPropertiesPanel(layerFromPHP);
                return;
            }
        }
        
        // Fallback: DOM'dan yükle
        const layerElement = document.querySelector(`.layer-element[data-layer-id="${layerId}"]`);
        if (!layerElement) {
            this.showEmptyProperties();
            return;
        }
        
        const layerData = {
            id: layerId,
            type: layerElement.dataset.layerType || 'text',
            content: layerElement.dataset.layerContent || layerElement.textContent.trim() || '',
            position_x: layerElement.style.left || '50%',
            position_y: layerElement.style.top || '50%',
            width: layerElement.style.width || 'auto',
            height: layerElement.style.height || 'auto',
            z_index: parseInt(layerElement.style.zIndex) || 1,
            opacity: parseFloat(layerElement.style.opacity) || 1,
            color: layerElement.style.color || '#000000',
            background_color: layerElement.style.backgroundColor || '#ffffff',
            font_size: layerElement.style.fontSize || '',
            border_radius: layerElement.style.borderRadius || '0',
            text_align: layerElement.style.textAlign || 'left'
        };
        
        this.renderPropertiesPanel(layerData);
    }
    
    renderPropertiesPanel(layer) {
        const panel = this.propertiesPanel;
        
        panel.innerHTML = `
            <div class="space-y-4">
                <!-- Layer Info -->
                <div class="pb-4 border-b border-gray-200 dark:border-white/10">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-primary">
                            ${this.getLayerIcon(layer.type)}
                        </span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">${this.getLayerTypeName(layer.type)}</h3>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">İçerik</h3>
                    <textarea 
                        id="prop-content" 
                        class="w-full px-3 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white text-sm"
                        rows="3"
                        onchange="editor.updateLayerProperty(${layer.id}, 'content', this.value)"
                    >${layer.content || ''}</textarea>
                </div>
                
                <!-- Position -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Pozisyon</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">X</label>
                            <input 
                                type="text" 
                                id="prop-position-x"
                                value="${layer.position_x || '50%'}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'position_x', this.value)"
                            >
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Y</label>
                            <input 
                                type="text" 
                                id="prop-position-y"
                                value="${layer.position_y || '50%'}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'position_y', this.value)"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Size -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Boyut</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Genişlik</label>
                            <input 
                                type="text" 
                                id="prop-width"
                                value="${layer.width || 'auto'}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'width', this.value)"
                            >
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Yükseklik</label>
                            <input 
                                type="text" 
                                id="prop-height"
                                value="${layer.height || 'auto'}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'height', this.value)"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Style -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Stil</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Opacity</label>
                            <input 
                                type="range" 
                                min="0" 
                                max="1" 
                                step="0.01"
                                value="${layer.opacity || 1}"
                                class="w-full"
                                oninput="editor.updateLayerProperty(${layer.id}, 'opacity', parseFloat(this.value))"
                            >
                            <span class="text-xs text-gray-500">${Math.round((layer.opacity || 1) * 100)}%</span>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Z-Index</label>
                            <input 
                                type="number" 
                                id="prop-z-index"
                                value="${layer.z_index || 1}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'z_index', parseInt(this.value))"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Colors -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Renkler</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Metin</label>
                            <input 
                                type="color" 
                                id="prop-color"
                                value="${layer.color || '#000000'}"
                                class="w-full h-8 border border-gray-200 dark:border-white/10 rounded"
                                onchange="editor.updateLayerProperty(${layer.id}, 'color', this.value)"
                            >
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Arka Plan</label>
                            <input 
                                type="color" 
                                id="prop-bg-color"
                                value="${layer.background_color || '#ffffff'}"
                                class="w-full h-8 border border-gray-200 dark:border-white/10 rounded"
                                onchange="editor.updateLayerProperty(${layer.id}, 'background_color', this.value)"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Typography -->
                ${layer.type === 'text' ? `
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Tipografi</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Font Boyutu</label>
                            <input 
                                type="text" 
                                id="prop-font-size"
                                value="${layer.font_size || ''}"
                                placeholder="24px, 2rem, vb."
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'font_size', this.value)"
                            >
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Font Kalınlığı</label>
                            <select 
                                id="prop-font-weight"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'font_weight', this.value)"
                            >
                                <option value="" ${(!layer.font_weight || layer.font_weight === '') ? 'selected' : ''}>Normal</option>
                                <option value="300" ${layer.font_weight === '300' ? 'selected' : ''}>Light</option>
                                <option value="400" ${layer.font_weight === '400' ? 'selected' : ''}>Regular</option>
                                <option value="500" ${layer.font_weight === '500' ? 'selected' : ''}>Medium</option>
                                <option value="600" ${layer.font_weight === '600' ? 'selected' : ''}>Semi Bold</option>
                                <option value="700" ${layer.font_weight === '700' ? 'selected' : ''}>Bold</option>
                                <option value="800" ${layer.font_weight === '800' ? 'selected' : ''}>Extra Bold</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Hizalama</label>
                            <select 
                                id="prop-text-align"
                                value="${layer.text_align || 'left'}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'text_align', this.value)"
                            >
                                <option value="left">Sol</option>
                                <option value="center">Orta</option>
                                <option value="right">Sağ</option>
                                <option value="justify">Yasla</option>
                            </select>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <!-- Link (Button için) -->
                ${layer.type === 'button' ? `
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Link</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">URL</label>
                            <input 
                                type="text" 
                                id="prop-link-url"
                                value="${layer.link_url || ''}"
                                placeholder="https://example.com"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'link_url', this.value)"
                            >
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Hedef</label>
                            <select 
                                id="prop-link-target"
                                value="${layer.link_target || '_self'}"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateLayerProperty(${layer.id}, 'link_target', this.value)"
                            >
                                <option value="_self">Aynı Sekme</option>
                                <option value="_blank">Yeni Sekme</option>
                            </select>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <!-- Border Radius -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Köşe Yuvarlama</h3>
                    <input 
                        type="text" 
                        id="prop-border-radius"
                        value="${layer.border_radius || '0'}"
                        placeholder="0, 4px, 50%, vb."
                        class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                        onchange="editor.updateLayerProperty(${layer.id}, 'border_radius', this.value)"
                    >
                </div>
                
                <!-- Animasyon Ayarları -->
                <div class="properties-section">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Animasyon</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Giriş Animasyonu</label>
                            <select 
                                id="prop-animation-in"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateAnimationProperty(${layer.id}, 'animation_in', 'type', this.value)"
                            >
                                <option value="">Yok</option>
                                <option value="fadeIn">Fade In</option>
                                <option value="slideInLeft">Slide In Left</option>
                                <option value="slideInRight">Slide In Right</option>
                                <option value="slideInUp">Slide In Up</option>
                                <option value="slideInDown">Slide In Down</option>
                                <option value="zoomIn">Zoom In</option>
                                <option value="rotateIn">Rotate In</option>
                                <option value="bounceIn">Bounce In</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Çıkış Animasyonu</label>
                            <select 
                                id="prop-animation-out"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                onchange="editor.updateAnimationProperty(${layer.id}, 'animation_out', 'type', this.value)"
                            >
                                <option value="">Yok</option>
                                <option value="fadeOut">Fade Out</option>
                                <option value="slideOutLeft">Slide Out Left</option>
                                <option value="slideOutRight">Slide Out Right</option>
                                <option value="slideOutUp">Slide Out Up</option>
                                <option value="slideOutDown">Slide Out Down</option>
                                <option value="zoomOut">Zoom Out</option>
                                <option value="rotateOut">Rotate Out</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Süre (ms)</label>
                                <input 
                                    type="number" 
                                    id="prop-animation-duration"
                                    value="1000"
                                    min="0"
                                    step="100"
                                    class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                    onchange="editor.updateAnimationProperty(${layer.id}, 'animation_in', 'duration', this.value)"
                                >
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Gecikme (ms)</label>
                                <input 
                                    type="number" 
                                    id="prop-animation-delay"
                                    value="0"
                                    min="0"
                                    step="100"
                                    class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-background-dark text-gray-900 dark:text-white"
                                    onchange="editor.updateAnimationProperty(${layer.id}, 'animation_in', 'delay', this.value)"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    showEmptyProperties() {
        this.propertiesPanel.innerHTML = `
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <span class="material-symbols-outlined text-4xl mb-2">tune</span>
                <p class="text-sm">Bir layer seçin</p>
            </div>
        `;
    }
    
    async updateLayerProperty(layerId, property, value) {
        const formData = new FormData();
        formData.append(property, value);
        
        try {
            const response = await fetch(`${this.adminUrl}sliders/update-layer/${layerId}`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // UI'ı güncelle
                this.updateLayerUI(layerId, property, value);
                
                // Eğer width veya height güncellendiyse, resize handle'ları yeniden çiz
                if (property === 'width' || property === 'height') {
                    const layerElement = document.querySelector(`.layer-element[data-layer-id="${layerId}"]`);
                    if (layerElement && layerElement.classList.contains('selected')) {
                        this.addResizeHandles(layerElement);
                    }
                }
            }
        } catch (error) {
            console.error('Layer güncellenemedi:', error);
        }
    }
    
    async updateAnimationProperty(layerId, animationType, property, value) {
        // Animation property'lerini JSON olarak kaydetmek için
        // Önce mevcut animasyon verilerini al
        try {
            const response = await fetch(`${this.adminUrl}sliders/layer-data/${layerId}`);
            const data = await response.json();
            
            if (data.success && data.layer) {
                let animationData = data.layer[animationType] || {};
                if (typeof animationData === 'string') {
                    animationData = JSON.parse(animationData);
                }
                
                animationData[property] = value;
                
                // JSON olarak kaydet
                const formData = new FormData();
                formData.append(animationType, JSON.stringify(animationData));
                
                const updateResponse = await fetch(`${this.adminUrl}sliders/update-layer/${layerId}`, {
                    method: 'POST',
                    body: formData
                });
                
                const updateData = await updateResponse.json();
                if (updateData.success) {
                    console.log('Animasyon güncellendi');
                }
            }
        } catch (error) {
            console.error('Animasyon güncellenemedi:', error);
        }
    }
    
    updateLayerUI(layerId, property, value) {
        const layerElement = document.querySelector(`.layer-element[data-layer-id="${layerId}"]`);
        if (!layerElement) return;
        
        switch (property) {
            case 'position_x':
                layerElement.style.left = value;
                break;
            case 'position_y':
                layerElement.style.top = value;
                break;
            case 'width':
                layerElement.style.width = value;
                break;
            case 'height':
                layerElement.style.height = value;
                break;
            case 'opacity':
                layerElement.style.opacity = value;
                break;
            case 'color':
                layerElement.style.color = value;
                break;
            case 'background_color':
                layerElement.style.backgroundColor = value;
                break;
            case 'z_index':
                layerElement.style.zIndex = value;
                break;
            case 'content':
                if (layerElement.dataset.layerType === 'text') {
                    layerElement.innerHTML = value.replace(/\n/g, '<br>');
                }
                layerElement.dataset.layerContent = value;
                break;
            case 'font_size':
                layerElement.style.fontSize = value;
                break;
            case 'font_weight':
                layerElement.style.fontWeight = value;
                break;
            case 'text_align':
                layerElement.style.textAlign = value;
                break;
            case 'border_radius':
                layerElement.style.borderRadius = value;
                break;
            case 'link_url':
                // Button için link güncelleme - DOM'da a tag'i varsa güncelle
                const buttonLink = layerElement.querySelector('a');
                if (buttonLink) {
                    buttonLink.href = value;
                }
                break;
            case 'link_target':
                // Button için link target güncelleme
                const buttonLinkTarget = layerElement.querySelector('a');
                if (buttonLinkTarget) {
                    buttonLinkTarget.target = value;
                }
                break;
        }
    }
    
    async updateLayerPosition(layerId, x, y) {
        const formData = new FormData();
        formData.append('position_x', x);
        formData.append('position_y', y);
        
        try {
            const response = await fetch(`${this.adminUrl}sliders/update-layer/${layerId}`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Pozisyon güncellenemedi:', error);
            return false;
        }
    }
    
    async moveLayerByPixels(layerId, deltaX, deltaY) {
        const layerElement = document.querySelector(`.layer-element[data-layer-id="${layerId}"]`);
        if (!layerElement) return;
        
        const canvas = this.canvas;
        const rect = canvas.getBoundingClientRect();
        const layerRect = layerElement.getBoundingClientRect();
        
        const currentX = ((layerRect.left - rect.left) / rect.width) * 100;
        const currentY = ((layerRect.top - rect.top) / rect.height) * 100;
        
        const pixelPercentX = (deltaX / rect.width) * 100;
        const pixelPercentY = (deltaY / rect.height) * 100;
        
        const newX = Math.max(0, Math.min(100, currentX + pixelPercentX));
        const newY = Math.max(0, Math.min(100, currentY + pixelPercentY));
        
        layerElement.style.left = newX + '%';
        layerElement.style.top = newY + '%';
        
        await this.updateLayerPosition(layerId, newX + '%', newY + '%');
    }
    
    async deleteLayer(layerId) {
        try {
            const response = await fetch(`${this.adminUrl}sliders/delete-layer/${layerId}`, {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Layer silinemedi:', error);
        }
    }
    
    getLayerIcon(type) {
        const icons = {
            'text': 'text_fields',
            'image': 'image',
            'video': 'movie',
            'button': 'smart_button',
            'shape': 'shapes',
            'html': 'code'
        };
        return icons[type] || 'layers';
    }
    
    addResizeHandles(layerElement) {
        // Önce mevcut handle'ları kaldır
        this.removeResizeHandles(layerElement);
        
        // 8 köşe ve kenar handle'ı oluştur
        const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
        
        handles.forEach(direction => {
            const handle = document.createElement('div');
            handle.className = `resize-handle ${direction}`;
            handle.dataset.direction = direction;
            
            // Resize başlangıcı
            handle.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                this.startResize(layerElement, direction, e);
            });
            
            layerElement.appendChild(handle);
        });
    }
    
    removeResizeHandles(layerElement) {
        const handles = layerElement.querySelectorAll('.resize-handle');
        handles.forEach(handle => handle.remove());
    }
    
    startResize(layerElement, direction, startEvent) {
        const startX = startEvent.clientX;
        const startY = startEvent.clientY;
        const startWidth = layerElement.offsetWidth;
        const startHeight = layerElement.offsetHeight;
        const startLeft = layerElement.offsetLeft;
        const startTop = layerElement.offsetTop;
        
        const canvas = this.canvas;
        const canvasRect = canvas.getBoundingClientRect();
        
        const onMouseMove = (e) => {
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            let newWidth = startWidth;
            let newHeight = startHeight;
            let newLeft = startLeft;
            let newTop = startTop;
            
            // Yön bazlı resize
            if (direction.includes('e')) {
                newWidth = Math.max(50, startWidth + deltaX);
            }
            if (direction.includes('w')) {
                newWidth = Math.max(50, startWidth - deltaX);
                newLeft = startLeft + deltaX;
            }
            if (direction.includes('s')) {
                newHeight = Math.max(50, startHeight + deltaY);
            }
            if (direction.includes('n')) {
                newHeight = Math.max(50, startHeight - deltaY);
                newTop = startTop + deltaY;
            }
            
            // Canvas sınırları içinde tut
            const maxWidth = canvasRect.width - newLeft;
            const maxHeight = canvasRect.height - newTop;
            newWidth = Math.min(newWidth, maxWidth);
            newHeight = Math.min(newHeight, maxHeight);
            
            // UI güncelle
            layerElement.style.width = newWidth + 'px';
            layerElement.style.height = newHeight + 'px';
            layerElement.style.left = (newLeft / canvasRect.width * 100) + '%';
            layerElement.style.top = (newTop / canvasRect.height * 100) + '%';
        };
        
        const onMouseUp = async () => {
            // Final pozisyonu kaydet
            const layerId = parseInt(layerElement.dataset.layerId);
            const rect = layerElement.getBoundingClientRect();
            const canvasRect = canvas.getBoundingClientRect();
            
            const positionX = ((rect.left - canvasRect.left) / canvasRect.width * 100) + '%';
            const positionY = ((rect.top - canvasRect.top) / canvasRect.height * 100) + '%';
            const width = rect.width + 'px';
            const height = rect.height + 'px';
            
            // Backend'e kaydet
            await this.updateLayerPosition(layerId, positionX, positionY);
            
            // Width ve height'i de güncelle
            const formData = new FormData();
            formData.append('width', width);
            formData.append('height', height);
            
            try {
                await fetch(`${this.adminUrl}sliders/update-layer/${layerId}`, {
                    method: 'POST',
                    body: formData
                });
            } catch (error) {
                console.error('Boyut güncellenemedi:', error);
            }
            
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }
    
    getLayerTypeName(type) {
        const names = {
            'text': 'Metin',
            'image': 'Resim',
            'video': 'Video',
            'button': 'Buton',
            'shape': 'Şekil',
            'html': 'HTML'
        };
        return names[type] || 'Layer';
    }
    
    async copyLayer(layerId) {
        try {
            // Layer verisini al
            const response = await fetch(`${this.adminUrl}sliders/layer-data/${layerId}`);
            const data = await response.json();
            
            if (data.success && data.layer) {
                this.copiedLayer = data.layer;
                
                // Paste butonunu göster
                const pasteBtn = document.getElementById('paste-layer-btn');
                if (pasteBtn) {
                    pasteBtn.style.display = 'block';
                }
                
                // Başarı mesajı
                this.showMessage('Layer kopyalandı!', 'success');
            }
        } catch (error) {
            console.error('Layer kopyalanamadı:', error);
            this.showMessage('Layer kopyalanamadı', 'error');
        }
    }
    
    async pasteLayer() {
        if (!this.copiedLayer) {
            this.showMessage('Kopyalanacak layer yok', 'error');
            return;
        }
        
        try {
            // Yeni layer oluştur (kopyalanan veriden)
            const formData = new FormData();
            formData.append('slider_item_id', this.itemId);
            formData.append('type', this.copiedLayer.type);
            formData.append('content', this.copiedLayer.content || '');
            
            // Pozisyonu biraz kaydır (orijinalinden farklı görünsün)
            const originalX = parseFloat(this.copiedLayer.position_x) || 50;
            const originalY = parseFloat(this.copiedLayer.position_y) || 50;
            formData.append('position_x', (originalX + 5) + '%');
            formData.append('position_y', (originalY + 5) + '%');
            
            formData.append('width', this.copiedLayer.width || 'auto');
            formData.append('height', this.copiedLayer.height || 'auto');
            formData.append('z_index', this.copiedLayer.z_index || 1);
            formData.append('opacity', this.copiedLayer.opacity || 1);
            
            if (this.copiedLayer.color) formData.append('color', this.copiedLayer.color);
            if (this.copiedLayer.background_color) formData.append('background_color', this.copiedLayer.background_color);
            if (this.copiedLayer.font_size) formData.append('font_size', this.copiedLayer.font_size);
            if (this.copiedLayer.font_weight) formData.append('font_weight', this.copiedLayer.font_weight);
            if (this.copiedLayer.text_align) formData.append('text_align', this.copiedLayer.text_align);
            if (this.copiedLayer.border_radius) formData.append('border_radius', this.copiedLayer.border_radius);
            if (this.copiedLayer.link_url) formData.append('link_url', this.copiedLayer.link_url);
            if (this.copiedLayer.link_target) formData.append('link_target', this.copiedLayer.link_target);
            
            const response = await fetch(`${this.adminUrl}sliders/add-layer`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showMessage('Layer yapıştırıldı!', 'success');
                // Sayfayı yenile
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                this.showMessage('Layer yapıştırılamadı: ' + (data.message || 'Bilinmeyen hata'), 'error');
            }
        } catch (error) {
            console.error('Layer yapıştırılamadı:', error);
            this.showMessage('Layer yapıştırılamadı', 'error');
        }
    }
    
    showMessage(message, type = 'info') {
        const messageEl = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        messageEl.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2`;
        messageEl.innerHTML = `<span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span><span>${message}</span>`;
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.remove();
        }, 3000);
    }
}

// Global editor instance
let editor = null;
