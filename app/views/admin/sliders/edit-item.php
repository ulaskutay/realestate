<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Slide Düzenle</title>
    
    <!-- Dark Mode - Sayfa yüklenmeden önce çalışmalı (FOUC önleme) -->
    <script>
        (function() {
            'use strict';
            const DARK_MODE_KEY = 'admin_dark_mode';
            const htmlElement = document.documentElement;
            let darkModePreference = null;
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (savedPreference === 'dark' || savedPreference === 'light') {
                    darkModePreference = savedPreference === 'dark';
                }
            } catch (e) {}
            if (darkModePreference === null) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    darkModePreference = true;
                } else {
                    darkModePreference = false;
                }
            }
            if (darkModePreference) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        })();
    </script>
    
    
    
    
    <!-- Material Icons Font Preload -->
    
    
    <!-- Custom CSS (Font-face içeriyor) -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Google Fonts - Inter -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
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
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <!-- SideNavBar -->
            <?php 
            $currentPage = 'sliders';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-4xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Slide Düzenle</p>
                            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal"><?php echo esc_html($item['title'] ?? 'Başlıksız'); ?></p>
                        </div>
                        <a href="<?php echo admin_url('sliders/edit/' . $slider['id']); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            <span class="material-symbols-outlined text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Slide Settings Form -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                        <h2 class="text-gray-900 dark:text-white text-xl font-semibold mb-6">Slide Ayarları</h2>
                        <form id="edit-item-form" method="POST" action="<?php echo admin_url('sliders/update-item/' . $item['id']); ?>" class="space-y-6">
                            
                            <!-- Slide Tipi -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slide Tipi</label>
                                <select name="type" id="item-type-select" required class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="image" <?php echo ($item['type'] ?? 'image') === 'image' ? 'selected' : ''; ?>>Resim</option>
                                    <option value="video" <?php echo ($item['type'] ?? '') === 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="html" <?php echo ($item['type'] ?? '') === 'html' ? 'selected' : ''; ?>>HTML</option>
                                </select>
                            </div>
                            
                            <!-- Media URL -->
                            <div id="media-url-section">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Medya URL</label>
                                <div class="flex gap-2">
                                    <input 
                                        type="text" 
                                        name="media_url" 
                                        id="item-media-url"
                                        value="<?php echo esc_attr($item['media_url'] ?? ''); ?>"
                                        placeholder="https://example.com/image.jpg veya /uploads/image.jpg"
                                        class="flex-1 px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                    />
                                    <button 
                                        type="button"
                                        onclick="openMediaPickerForSlider()"
                                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2"
                                    >
                                        <span class="material-symbols-outlined text-xl">perm_media</span>
                                        <span>Kütüphaneden Seç</span>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Resim veya video dosyasının URL'sini girin veya dosya yöneticisinden seçin</p>
                            </div>
                            
                            <!-- Başlık -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başlık</label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    value="<?php echo esc_attr($item['title'] ?? ''); ?>"
                                    placeholder="Slide başlığı"
                                    class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                />
                            </div>
                            
                            <!-- Alt Başlık -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alt Başlık</label>
                                <input 
                                    type="text" 
                                    name="subtitle" 
                                    value="<?php echo esc_attr($item['subtitle'] ?? ''); ?>"
                                    placeholder="Slide alt başlığı"
                                    class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                />
                            </div>
                            
                            <!-- Açıklama -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Açıklama</label>
                                <textarea 
                                    name="description" 
                                    rows="3"
                                    placeholder="Slide açıklaması"
                                    class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                ><?php echo esc_html($item['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <!-- Buton Ayarları -->
                            <div class="border-t border-gray-200 dark:border-white/10 pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Buton Ayarları</h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Metni</label>
                                        <input 
                                            type="text" 
                                            name="button_text" 
                                            value="<?php echo esc_attr($item['button_text'] ?? ''); ?>"
                                            placeholder="Detaylar"
                                            class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                        />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Linki</label>
                                        <input 
                                            type="text" 
                                            name="button_link" 
                                            value="<?php echo esc_attr($item['button_link'] ?? ''); ?>"
                                            placeholder="https://example.com veya /sayfa"
                                            class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                        />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link Hedefi</label>
                                        <select name="button_target" class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                            <option value="_self" <?php echo ($item['button_target'] ?? '_self') === '_self' ? 'selected' : ''; ?>>Aynı Sekmede</option>
                                            <option value="_blank" <?php echo ($item['button_target'] ?? '') === '_blank' ? 'selected' : ''; ?>>Yeni Sekmede</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Overlay ve Pozisyon -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Overlay Opaklığı</label>
                                    <input 
                                        type="range" 
                                        name="overlay_opacity" 
                                        min="0" 
                                        max="1" 
                                        step="0.1"
                                        value="<?php echo esc_attr($item['overlay_opacity'] ?? 0); ?>"
                                        class="w-full"
                                        oninput="document.getElementById('overlay-value').textContent = Math.round(this.value * 100) + '%'"
                                    />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Opaklık: <span id="overlay-value"><?php echo round(($item['overlay_opacity'] ?? 0) * 100); ?>%</span></p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Metin Pozisyonu</label>
                                    <select name="text_position" class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                        <option value="center" <?php echo ($item['text_position'] ?? 'center') === 'center' ? 'selected' : ''; ?>>Ortada</option>
                                        <option value="left" <?php echo ($item['text_position'] ?? '') === 'left' ? 'selected' : ''; ?>>Solda</option>
                                        <option value="right" <?php echo ($item['text_position'] ?? '') === 'right' ? 'selected' : ''; ?>>Sağda</option>
                                        <option value="top" <?php echo ($item['text_position'] ?? '') === 'top' ? 'selected' : ''; ?>>Üstte</option>
                                        <option value="bottom" <?php echo ($item['text_position'] ?? '') === 'bottom' ? 'selected' : ''; ?>>Altta</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Durum -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="item-status" 
                                    name="status" 
                                    value="active"
                                    <?php echo ($item['status'] ?? 'active') === 'active' ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="item-status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
                            </div>
                            
                            <!-- Butonlar -->
                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                                <a href="<?php echo admin_url('sliders/edit/' . $slider['id']); ?>" class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium">
                                    İptal
                                </a>
                                <button 
                                    type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                >
                                    Değişiklikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- File Manager Modal -->
    <div id="file-manager-modal" class="hidden fixed inset-0 bg-black/70 z-[99999] flex items-center justify-center p-4" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
        <div class="bg-white dark:bg-background-dark rounded-xl p-6 max-w-4xl w-full max-h-[90vh] flex flex-col relative z-[99999]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Dosya Yöneticisi</h3>
                <button onclick="closeFileManager()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Upload Section -->
            <div class="mb-6 p-4 border border-gray-200 dark:border-white/10 rounded-lg bg-gray-50 dark:bg-background-dark/50">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Yeni Dosya Yükle</label>
                <div class="flex gap-2">
                    <input 
                        type="file" 
                        id="file-upload-input"
                        accept="image/*,video/*"
                        class="flex-1 px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                    />
                    <button 
                        type="button"
                        onclick="uploadFile()"
                        id="upload-btn"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium"
                    >
                        Yükle
                    </button>
                </div>
                <div id="upload-status" class="hidden mt-2 p-2 rounded-lg text-sm"></div>
            </div>
            
            <!-- Files Grid -->
            <div class="flex-1 overflow-y-auto">
                <div id="files-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                        <span class="material-symbols-outlined text-4xl mb-2 block">hourglass_empty</span>
                        <p>Dosyalar yükleniyor...</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-white/10">
                <button 
                    type="button" 
                    onclick="closeFileManager()" 
                    class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium"
                >
                    İptal
                </button>
            </div>
        </div>
    </div>

    <script>
        const adminUrl = '<?php echo admin_url(''); ?>?page=';

        // File Manager Functions
        function openFileManager() {
            const fileManagerModal = document.getElementById('file-manager-modal');
            fileManagerModal.classList.remove('hidden');
            loadFiles();
        }

        function closeFileManager() {
            document.getElementById('file-manager-modal').classList.add('hidden');
        }

        async function loadFiles() {
            const grid = document.getElementById('files-grid');
            grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-4xl mb-2 block">hourglass_empty</span><p>Dosyalar yükleniyor...</p></div>';
            
            try {
                const response = await fetch(adminUrl + 'sliders/file-manager-list');
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse hatası:', parseError);
                    grid.innerHTML = `<div class="col-span-full text-center py-8 text-red-500"><span class="material-symbols-outlined text-4xl mb-2 block">error</span><p>Dosyalar yüklenirken bir hata oluştu</p></div>`;
                    return;
                }
                
                if (data.success && data.files.length > 0) {
                    grid.innerHTML = data.files.map(file => `
                        <div class="relative group cursor-pointer border-2 border-gray-200 dark:border-white/10 rounded-lg overflow-hidden hover:border-primary transition-colors" onclick="selectFile('${file.url}')">
                            ${file.type === 'image' 
                                ? `<img src="${file.url}" alt="${file.name}" class="w-full h-32 object-cover">`
                                : `<div class="w-full h-32 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-400">play_circle</span>
                                   </div>`
                            }
                            <div class="p-2 bg-white dark:bg-background-dark">
                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate" title="${file.name}">${file.name}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">${formatFileSize(file.size)}</p>
                            </div>
                        </div>
                    `).join('');
                } else {
                    grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-4xl mb-2 block">folder_open</span><p>Henüz dosya yüklenmemiş</p></div>';
                }
            } catch (error) {
                grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500"><p>Dosyalar yüklenirken bir hata oluştu: ' + error.message + '</p></div>';
            }
        }

        function selectFile(url) {
            document.getElementById('item-media-url').value = url;
            closeFileManager();
        }

        async function uploadFile() {
            const input = document.getElementById('file-upload-input');
            const statusDiv = document.getElementById('upload-status');
            const uploadBtn = document.getElementById('upload-btn');
            
            if (!input.files || !input.files[0]) {
                alert('Lütfen bir dosya seçin');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', input.files[0]);
            
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Yükleniyor...';
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200';
            statusDiv.textContent = 'Dosya yükleniyor...';
            
            try {
                const response = await fetch(adminUrl + 'sliders/file-manager-upload', {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                    statusDiv.textContent = 'Sunucu yanıtı geçersiz.';
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Yükle';
                    return;
                }
                
                if (data.success) {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200';
                    statusDiv.textContent = data.message;
                    input.value = '';
                    
                    setTimeout(() => {
                        loadFiles();
                    }, 500);
                } else {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                    statusDiv.textContent = data.message || 'Dosya yüklenirken bir hata oluştu';
                }
            } catch (error) {
                statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                statusDiv.textContent = 'Bir hata oluştu: ' + error.message;
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Yükle';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Form Submit
        document.getElementById('edit-item-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitButton = e.target.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.textContent = 'Kaydediliyor...';
            
            try {
                const response = await fetch(adminUrl + 'sliders/update-item/<?php echo $item['id']; ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Başarı mesajı göster
                    const message = document.createElement('div');
                    message.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    message.innerHTML = '<span class="material-symbols-outlined">check_circle</span><span>Slide başarıyla güncellendi!</span>';
                    document.body.appendChild(message);
                    
                    setTimeout(() => {
                        message.remove();
                        window.location.href = '<?php echo admin_url('sliders/edit/' . $slider['id']); ?>';
                    }, 1500);
                } else {
                    alert('Hata: ' + (data.message || 'Slide güncellenirken bir hata oluştu'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            } catch (error) {
                alert('Bir hata oluştu: ' + error.message);
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    </script>
    
    <!-- Media Picker JS -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/media-picker.js'; ?>"></script>
    <script>
        // Media Picker for Slider
        function openMediaPickerForSlider() {
            openMediaPicker({
                type: 'all',
                targetInput: 'item-media-url',
                onSelect: function(media) {
                    console.log('Selected media:', media);
                }
            });
        }

        // Legacy support
        function openFileManager() {
            openMediaPickerForSlider();
        }
    </script>
</body>
</html>
