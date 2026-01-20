<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold dark:text-white">Toplu Çeviri</h1>
        <a href="<?php echo admin_url('module/translation'); ?>" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Geri Dön
        </a>
    </div>
    
    <!-- Çeviri Temizleme Araçları -->
    <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded p-4 mb-6">
        <h3 class="text-red-800 dark:text-red-200 font-bold mb-3">Çeviri Temizleme Araçları</h3>
        
        <div class="space-y-4">
            <!-- Bozuk Çevirileri Temizle -->
            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded">
                <div>
                    <p class="text-gray-800 dark:text-gray-200 font-medium">Bozuk Çevirileri Temizle</p>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        URL'ler, CSS class'ları, gradient değerleri gibi yanlışlıkla çevrilmiş teknik değerleri siler.
                    </p>
                </div>
                <button type="button" id="cleanup-btn" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 whitespace-nowrap">
                    Bozukları Temizle
                </button>
            </div>
            
            <!-- Tüm Çevirileri Sil -->
            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded">
                <div>
                    <p class="text-gray-800 dark:text-gray-200 font-medium">Tüm Çevirileri Sil</p>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        <strong class="text-red-600">DİKKAT:</strong> Tüm çevirileri siler. Sıfırdan başlamak için kullanın.
                    </p>
                </div>
                <button type="button" id="delete-all-btn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 whitespace-nowrap">
                    Tümünü Sil
                </button>
            </div>
        </div>
        
        <div id="cleanup-result" class="mt-3 hidden"></div>
    </div>
    
    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded p-4 mb-6">
        <p class="text-yellow-800 dark:text-yellow-200">
            <strong>Uyarı:</strong> Toplu çeviri işlemi web sitesindeki TÜM içerikleri çevirecektir:
        </p>
        <ul class="list-disc list-inside mt-2 text-sm space-y-1">
            <li>Sayfalar ve Postlar (başlık, içerik, özet)</li>
            <li>Sözleşmeler</li>
            <li>Menüler ve Menü Öğeleri</li>
            <li>Sliderlar, Slider Öğeleri ve Katmanları</li>
            <li>Formlar ve Form Alanları</li>
            <li>Kategoriler ve Etiketler</li>
        </ul>
        <p class="text-yellow-800 dark:text-yellow-200 mt-2">
            Bu işlem DeepL API kullanım limitinizi tüketebilir. Zaten çevrilmiş içerikler atlanacaktır.
        </p>
    </div>
    
    <form id="bulk-translate-form" method="POST" action="<?php echo admin_url('module/translation/bulk_translate'); ?>">
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-white">Hedef Dil</label>
                <select name="target_language" id="target_language" required class="w-full px-3 py-2 border rounded dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <option value="">Dil Seçin</option>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?php echo $lang['code']; ?>">
                            <?php echo htmlspecialchars($lang['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" id="translate-btn" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Çeviriyi Başlat
            </button>
        </div>
    </form>
    
    <div id="result" class="mt-6 hidden"></div>
    
    <!-- Progress Bar -->
    <div id="progress-container" class="mt-6 hidden">
        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-2">
            <div id="progress-bar" class="bg-blue-500 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
            <span id="progress-text">Hazırlanıyor...</span>
            <span id="progress-percent">0%</span>
        </div>
    </div>
    
    <script>
        // Bozuk çevirileri temizle
        document.getElementById('cleanup-btn').addEventListener('click', function() {
            const btn = this;
            const resultDiv = document.getElementById('cleanup-result');
            
            if (!confirm('Bozuk çevirileri temizlemek istediğinizden emin misiniz?')) {
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Temizleniyor...';
            
            const formData = new FormData();
            formData.append('action', 'delete_broken');
            
            fetch('<?php echo admin_url('module/translation/cleanup_translations'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Sunucu hatası: ' + text.substring(0, 200));
                    }
                });
            })
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Bozukları Temizle';
                resultDiv.classList.remove('hidden');
                
                if (data.success) {
                    resultDiv.className = 'mt-3 p-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded text-sm';
                    resultDiv.innerHTML = '<strong>Başarılı!</strong> ' + data.message;
                } else {
                    resultDiv.className = 'mt-3 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded text-sm';
                    let errorMsg = '<strong>Hata!</strong> ' + data.message;
                    if (data.error_details) {
                        errorMsg += '<br><small class="text-xs opacity-75">' + data.error_details + '</small>';
                    }
                    resultDiv.innerHTML = errorMsg;
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.textContent = 'Bozukları Temizle';
                resultDiv.classList.remove('hidden');
                resultDiv.className = 'mt-3 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded text-sm';
                resultDiv.innerHTML = '<strong>Hata!</strong> ' + error.message + '<br><small class="text-xs opacity-75">Lütfen tarayıcı konsolunu kontrol edin.</small>';
                console.error('Cleanup error:', error);
            });
        });
        
        // Tüm çevirileri sil
        document.getElementById('delete-all-btn').addEventListener('click', function() {
            const btn = this;
            const resultDiv = document.getElementById('cleanup-result');
            
            if (!confirm('TÜM ÇEVİRİLERİ SİLMEK İSTEDİĞİNİZDEN EMİN MİSİNİZ?\n\nBu işlem geri alınamaz!')) {
                return;
            }
            
            if (!confirm('Son kez onaylayın: Tüm çeviriler silinecek ve sıfırdan başlamanız gerekecek.')) {
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Siliniyor...';
            
            const formData = new FormData();
            formData.append('delete_all', '1');
            
            fetch('<?php echo admin_url('module/translation/cleanup_translations'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Sunucu hatası: ' + text.substring(0, 200));
                    }
                });
            })
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Tümünü Sil';
                resultDiv.classList.remove('hidden');
                
                if (data.success) {
                    resultDiv.className = 'mt-3 p-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded text-sm';
                    resultDiv.innerHTML = '<strong>Başarılı!</strong> ' + data.message;
                } else {
                    resultDiv.className = 'mt-3 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded text-sm';
                    let errorMsg = '<strong>Hata!</strong> ' + data.message;
                    if (data.error_details) {
                        errorMsg += '<br><small class="text-xs opacity-75">' + data.error_details + '</small>';
                    }
                    resultDiv.innerHTML = errorMsg;
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.textContent = 'Tümünü Sil';
                resultDiv.classList.remove('hidden');
                resultDiv.className = 'mt-3 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded text-sm';
                resultDiv.innerHTML = '<strong>Hata!</strong> ' + error.message + '<br><small class="text-xs opacity-75">Lütfen tarayıcı konsolunu kontrol edin.</small>';
                console.error('Delete all error:', error);
            });
        });
        
        document.getElementById('bulk-translate-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('translate-btn');
            const resultDiv = document.getElementById('result');
            const targetLang = document.getElementById('target_language').value;
            
            if (!targetLang) {
                alert('Lütfen bir dil seçin');
                return;
            }
            
            if (!confirm('Toplu çeviri işlemini başlatmak istediğinizden emin misiniz? Bu işlem biraz zaman alabilir.')) {
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Çeviri yapılıyor...';
            resultDiv.classList.add('hidden');
            
            // Progress bar göster
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const progressPercent = document.getElementById('progress-percent');
            
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressPercent.textContent = '0%';
            progressText.textContent = 'Çeviri işlemi başlatılıyor...';
            
            // Kategori listesi - her kategori için ayrı request yapılacak
            const categories = [
                { key: 'pages', name: 'Sayfalar' },
                { key: 'posts', name: 'Postlar' },
                { key: 'agreements', name: 'Sözleşmeler' },
                { key: 'menus', name: 'Menüler' },
                { key: 'menu_items', name: 'Menü Öğeleri' },
                { key: 'sliders', name: 'Sliderlar' },
                { key: 'slider_items', name: 'Slider Öğeleri' },
                { key: 'slider_layers', name: 'Slider Katmanları' },
                { key: 'forms', name: 'Formlar' },
                { key: 'form_fields', name: 'Form Alanları' },
                { key: 'categories', name: 'Kategoriler' },
                { key: 'tags', name: 'Etiketler' },
                { key: 'page_sections', name: 'Sayfa Section\'ları (Hero, Pricing, vb.)' },
                { key: 'theme_options', name: 'Tema Ayarları' },
                { key: 'themes', name: 'Tema JSON Ayarları' },
                { key: 'site_options', name: 'Site Ayarları' },
                { key: 'options', name: 'Sistem Ayarları (Options)' },
                // Hardcoded kategorisi kaldırıldı - extract edilen metinler artık title/content olarak kaydediliyor
            ];
            
            let totalTranslated = 0;
            let totalSkipped = 0;
            let currentCategoryIndex = 0;
            
            // Her kategori için sırayla request yap
            function processNextCategory() {
                if (currentCategoryIndex >= categories.length) {
                    // Tüm kategoriler tamamlandı
                    progressBar.style.width = '100%';
                    progressPercent.textContent = '100%';
                    progressText.textContent = 'Çeviri tamamlandı!';
                    
                    setTimeout(() => {
                        progressContainer.classList.add('hidden');
                    }, 1000);
                    
                    btn.disabled = false;
                    btn.textContent = 'Çeviriyi Başlat';
                    
                    resultDiv.classList.remove('hidden');
                    resultDiv.className = 'mt-6 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded';
                    resultDiv.innerHTML = '<strong>Başarılı!</strong> Tüm içerikler çevrildi!<br>Çevrilen: ' + totalTranslated + '<br>Atlanan: ' + totalSkipped;
                    return;
                }
                
                const category = categories[currentCategoryIndex];
                const progress = Math.round((currentCategoryIndex / categories.length) * 100);
                
                progressBar.style.width = progress + '%';
                progressPercent.textContent = progress + '%';
                progressText.textContent = category.name + ' çevriliyor... (' + (currentCategoryIndex + 1) + '/' + categories.length + ')';
                
                const formData = new FormData();
                formData.append('target_language', targetLang);
                formData.append('category', category.key);
                
                fetch('<?php echo admin_url('module/translation/bulk_translate'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Sunucu hatası: ' + text.substring(0, 200));
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        totalTranslated += data.translated || 0;
                        totalSkipped += data.skipped || 0;
                    } else {
                        console.error('Kategori hatası (' + category.name + '):', data.message);
                    }
                    
                    currentCategoryIndex++;
                    // Bir sonraki kategoriyi işle (kısa bir gecikme ile)
                    setTimeout(processNextCategory, 100);
                })
                .catch(error => {
                    console.error('Kategori hatası (' + category.name + '):', error);
                    // Hata olsa bile devam et
                    currentCategoryIndex++;
                    setTimeout(processNextCategory, 100);
                });
            }
            
            // İlk kategoriyi işle
            processNextCategory();
        });
    </script>
</div>
