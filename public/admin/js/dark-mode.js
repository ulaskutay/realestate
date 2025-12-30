/**
 * Dark Mode Toggle
 * LocalStorage ile kullanıcı tercihini kaydeder ve yükler
 */

(function() {
    'use strict';

    // LocalStorage key
    const DARK_MODE_KEY = 'admin_dark_mode';
    
    // HTML element
    const htmlElement = document.documentElement;
    
    /**
     * Dark mode'u aktif/pasif yap
     */
    function setDarkMode(enabled) {
        if (enabled) {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }
        
        // LocalStorage'a kaydet
        try {
            localStorage.setItem(DARK_MODE_KEY, enabled ? 'dark' : 'light');
        } catch (e) {
            console.warn('LocalStorage kullanılamıyor:', e);
        }
        
        // Toggle switch'i güncelle
        updateIcons(enabled);
    }
    
    /**
     * Dark mode durumunu kontrol et
     */
    function isDarkMode() {
        return htmlElement.classList.contains('dark');
    }
    
    /**
     * Icon'ları güncelle
     */
    function updateIcons(isDark) {
        const toggleButton = document.getElementById('dark-mode-toggle');
        if (toggleButton) {
            toggleButton.setAttribute('aria-checked', isDark);
        }
    }
    
    /**
     * Dark mode'u toggle et
     */
    function toggleDarkMode() {
        const toggleButton = document.getElementById('dark-mode-toggle');
        const currentMode = isDarkMode();
        
        // Toggle animasyonu için class ekle
        if (toggleButton) {
            toggleButton.classList.add('toggling');
            toggleButton.setAttribute('aria-checked', !currentMode);
            
            // Animasyon sonrası class'ı kaldır
            setTimeout(() => {
                toggleButton.classList.remove('toggling');
            }, 300);
        }
        
        setDarkMode(!currentMode);
        
        // Animasyon için transition ekle (sadece bir kez)
        if (!htmlElement.style.transition) {
            htmlElement.style.transition = 'background-color 0.3s ease, color 0.3s ease';
        }
    }
    
    /**
     * Sayfa yüklendiğinde dark mode tercihini yükle
     * Not: Dark mode head'deki inline script tarafından uygulanmış olmalı,
     * ama güvenlik için tekrar kontrol ediyoruz
     */
    function initDarkMode() {
        // LocalStorage'dan tercihi oku ve doğrula
        try {
            const savedPreference = localStorage.getItem(DARK_MODE_KEY);
            
            if (savedPreference === 'dark' || savedPreference === 'light') {
                // Kayıtlı tercih varsa, DOM'un doğru durumda olduğundan emin ol
                const shouldBeDark = savedPreference === 'dark';
                const currentlyDark = isDarkMode();
                
                // Eğer DOM durumu LocalStorage ile uyuşmuyorsa, düzelt
                if (shouldBeDark !== currentlyDark) {
                    if (shouldBeDark) {
                        htmlElement.classList.add('dark');
                    } else {
                        htmlElement.classList.remove('dark');
                    }
                }
                
                // Icon'ları güncelle
                updateIcons(shouldBeDark);
            } else {
                // Kayıtlı tercih yoksa, mevcut durumu kullan ve kaydet
                const currentMode = isDarkMode();
                updateIcons(currentMode);
                
                // Eğer tercih yoksa ama sistem tercihi varsa kaydet
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !currentMode) {
                    setDarkMode(true);
                } else {
                    // Mevcut durumu kaydet
                    localStorage.setItem(DARK_MODE_KEY, currentMode ? 'dark' : 'light');
                }
            }
        } catch (e) {
            console.warn('LocalStorage kullanılamıyor:', e);
            // LocalStorage yoksa mevcut DOM durumunu kullan
            const currentMode = isDarkMode();
            updateIcons(currentMode);
        }
        
        // Sistem tercihi değişikliklerini dinle
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Eğer localStorage'da tercih yoksa, sistem tercihini dinle
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (!savedPreference) {
                    mediaQuery.addEventListener('change', function(e) {
                        setDarkMode(e.matches);
                        updateIcons(e.matches);
                    });
                }
            } catch (e) {
                // LocalStorage yoksa sistem tercihini dinle
                mediaQuery.addEventListener('change', function(e) {
                    setDarkMode(e.matches);
                    updateIcons(e.matches);
                });
            }
        }
    }
    
    // Global fonksiyon olarak export et
    window.toggleDarkMode = toggleDarkMode;
    
    // Sayfa yüklendiğinde başlat (icon'ları güncellemek için)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarkMode);
    } else {
        initDarkMode();
    }
})();

