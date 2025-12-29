/**
 * Enhanced Slider Editor JavaScript
 * Gelişmiş özellikler için ek JavaScript
 */

// Layer verilerini PHP'den almak için global değişken
if (typeof window.layerData === 'undefined') {
    window.layerData = {};
}

// Edit-item sayfasında layer verilerini hazırla
document.addEventListener('DOMContentLoaded', () => {
    // Layer verilerini DOM'dan topla ve global değişkene kaydet
    const layerElements = document.querySelectorAll('.layer-element');
    layerElements.forEach(element => {
        const layerId = parseInt(element.dataset.layerId);
        const layerItem = document.querySelector(`.layer-item[data-layer-id="${layerId}"]`);
        
        if (layerItem) {
            window.layerData[layerId] = {
                id: layerId,
                type: element.dataset.layerType || 'text',
                content: element.dataset.layerContent || element.textContent.trim(),
                position_x: element.style.left || '50%',
                position_y: element.style.top || '50%',
                width: element.style.width || 'auto',
                height: element.style.height || 'auto',
                z_index: parseInt(element.style.zIndex) || 1,
                opacity: parseFloat(element.style.opacity) || 1,
                color: element.style.color || '',
                background_color: element.style.backgroundColor || '',
                font_size: element.style.fontSize || '',
                border_radius: element.style.borderRadius || '0',
                text_align: element.style.textAlign || 'left'
            };
        }
    });
});

// SliderEditor sınıfını genişlet
if (typeof SliderEditor !== 'undefined') {
    // Mevcut loadLayerPropertiesFromDOM metodunu güncelle
    const originalLoadFromDOM = SliderEditor.prototype.loadLayerPropertiesFromDOM;
    
    SliderEditor.prototype.loadLayerPropertiesFromDOM = function(layerId) {
        // Önce global layerData'dan dene
        if (window.layerData && window.layerData[layerId]) {
            this.renderPropertiesPanel(window.layerData[layerId]);
            return;
        }
        
        // Fallback: DOM'dan yükle
        if (originalLoadFromDOM) {
            originalLoadFromDOM.call(this, layerId);
        }
    };
}
