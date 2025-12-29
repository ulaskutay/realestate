<?php
/**
 * Admin Header Snippet
 * Tüm admin sayfalarında kullanılacak header (head bölümü)
 */
$pageTitle = $title ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo esc_html($pageTitle); ?></title>
    
    <!-- Local Fonts -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Dark Mode - Sayfa yüklenmeden önce çalışmalı (FOUC önleme) -->
    <script>
        (function() {
            'use strict';
            const DARK_MODE_KEY = 'admin_dark_mode';
            const htmlElement = document.documentElement;
            
            // LocalStorage'dan tercihi oku
            let darkModePreference = null;
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (savedPreference === 'dark' || savedPreference === 'light') {
                    darkModePreference = savedPreference === 'dark';
                }
            } catch (e) {
                // LocalStorage yoksa devam et
                console.warn('Dark mode: LocalStorage erişim hatası', e);
            }
            
            // Eğer localStorage'da tercih yoksa, sistem tercihini kullan
            if (darkModePreference === null) {
                try {
                    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        darkModePreference = true;
                    } else {
                        darkModePreference = false;
                    }
                } catch (e) {
                    // matchMedia desteklenmiyorsa varsayılan light mode
                    darkModePreference = false;
                }
            }
            
            // Dark mode'u hemen uygula (sayfa render edilmeden önce)
            // Force application - önce temizle, sonra ekle
            if (darkModePreference) {
                htmlElement.classList.remove('light');
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
                htmlElement.classList.add('light');
            }
            
            // Tercihi localStorage'a kaydet (eğer yoksa)
            try {
                const currentSaved = localStorage.getItem(DARK_MODE_KEY);
                if (!currentSaved) {
                    localStorage.setItem(DARK_MODE_KEY, darkModePreference ? 'dark' : 'light');
                }
            } catch (e) {
                // Kaydetme başarısız oldu, devam et
            }
        })();
    </script>
    
    <!-- İkonların hemen görünmesi için inline CSS -->
    <style>
        /* Material Icons için temel stil - Font yüklenmeden önce de çalışır */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            /* Font yüklenene kadar görünür tut */
            visibility: visible !important;
            opacity: 1 !important;
            /* Geçiş animasyonu */
            transition: opacity 0.2s ease-in-out;
        }
        
        /* Font yüklendiğinde smooth geçiş için - fonts.css'den yükleniyor */
        
        /* Font yüklendiğinde opacity artışı */
        body.fonts-loaded .material-symbols-outlined {
            opacity: 1;
        }
    </style>
    
    <!-- Font yükleme kontrolü -->
    <script>
        // Material Icons font yükleme kontrolü ve callback
        (function() {
            if ('fonts' in document) {
                document.fonts.ready.then(function() {
                    document.body.classList.add('fonts-loaded');
                });
            } else {
                // Fallback - eski tarayıcılar için
                setTimeout(function() {
                    document.body.classList.add('fonts-loaded');
                }, 100);
            }
        })();
    </script>
    
    <!-- Custom CSS -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">

