<?php
/**
 * SEO Modül - Sitemap Ayarları
 * Modern Design
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.seo-page {
    --accent: #00f5d4;
    --accent-dim: rgba(0, 245, 212, 0.1);
    --accent-glow: rgba(0, 245, 212, 0.3);
    /* Light mode colors */
    --surface: #ffffff;
    --surface-2: #f9fafb;
    --surface-3: #f3f4f6;
    --border: #e5e7eb;
    --text: #111827;
    --text-dim: #6b7280;
    font-family: 'Outfit', sans-serif;
}

/* Dark mode colors - html.dark class'ını takip eder */
html.dark .seo-page,
.dark .seo-page {
    --surface: #0d1117;
    --surface-2: #161b22;
    --surface-3: #21262d;
    --border: #30363d;
    --text: #e6edf3;
    --text-dim: #7d8590;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 16px;
}

.page-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.back-btn {
    width: 44px;
    height: 44px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
    text-decoration: none;
    transition: all 0.2s;
}

.back-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.page-subtitle {
    color: var(--text-dim);
    font-size: 0.9375rem;
    margin: 4px 0 0;
}

.preview-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--surface-3);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.preview-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
}

/* Alert */
.seo-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 0.9375rem;
}

.seo-alert.success {
    background: rgba(0, 245, 212, 0.1);
    border: 1px solid rgba(0, 245, 212, 0.3);
    color: var(--accent);
}

/* Form Sections */
.form-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    margin-bottom: 24px;
    overflow: hidden;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-icon {
    width: 40px;
    height: 40px;
    background: var(--accent-dim);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.section-body {
    padding: 24px;
}

/* URL Preview Box */
.url-preview {
    background: linear-gradient(135deg, var(--accent-dim), transparent);
    border: 1px solid rgba(0, 245, 212, 0.2);
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
}

.url-preview-label {
    color: var(--text-dim);
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.url-preview-link {
    color: var(--accent);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9375rem;
    text-decoration: none;
}

.url-preview-link:hover {
    text-decoration: underline;
}

/* Toggle Switch */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid var(--border);
}

.toggle-row:last-child {
    border-bottom: none;
}

.toggle-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toggle-icon {
    width: 36px;
    height: 36px;
    background: var(--surface-3);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
    font-size: 1.25rem;
}

.toggle-label {
    color: var(--text);
    font-weight: 500;
}

.toggle-desc {
    color: var(--text-dim);
    font-size: 0.8125rem;
    margin-top: 2px;
}

.toggle-switch {
    position: relative;
    width: 52px;
    height: 28px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: var(--surface-3);
    border-radius: 28px;
    transition: 0.3s;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background: var(--text-dim);
    border-radius: 50%;
    transition: 0.3s;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--accent);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
    background: var(--surface);
}

/* Grid Inputs */
.input-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    color: var(--text);
    font-weight: 500;
    font-size: 0.9375rem;
}

.form-input, .form-select {
    padding: 12px 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-size: 0.9375rem;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}

.form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%237d8590' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
    padding-right: 44px;
}

.form-select option {
    background: var(--surface-2);
    color: var(--text);
}

.form-textarea {
    padding: 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.875rem;
    resize: vertical;
    min-height: 150px;
    transition: all 0.2s;
}

.form-textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}

.form-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-dim);
    font-size: 0.8125rem;
    margin-top: 8px;
}

/* Submit Button */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-submit {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: var(--accent);
    border: none;
    border-radius: 12px;
    color: var(--surface);
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px var(--accent-glow);
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .page-header-left {
        width: 100%;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .preview-btn {
        width: 100%;
        justify-content: center;
        min-height: 44px;
    }
    
    .section-header {
        padding: 16px 20px;
    }
    
    .section-body {
        padding: 20px;
    }
    
    .section-title {
        font-size: 1rem;
    }
    
    .toggle-row {
        padding: 14px 0;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .toggle-info {
        flex: 1;
        min-width: 0;
    }
    
    .toggle-icon {
        width: 32px;
        height: 32px;
        font-size: 1.125rem;
    }
    
    .toggle-label {
        font-size: 0.9375rem;
    }
    
    .toggle-desc {
        font-size: 0.75rem;
    }
    
    .input-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .form-input, .form-select, .form-textarea {
        font-size: 16px; /* iOS zoom prevention */
        padding: 12px 14px;
    }
    
    .form-textarea {
        min-height: 120px;
    }
    
    .url-preview {
        padding: 14px 16px;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .url-preview-link {
        font-size: 0.875rem;
        word-break: break-all;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-submit {
        width: 100%;
        justify-content: center;
        min-height: 44px;
    }
}

@media (max-width: 640px) {
    .page-title {
        font-size: 1.25rem;
    }
    
    .back-btn {
        width: 40px;
        height: 40px;
    }
    
    .section-header {
        padding: 14px 16px;
    }
    
    .section-body {
        padding: 16px;
    }
    
    .section-icon {
        width: 36px;
        height: 36px;
    }
    
    .toggle-row {
        padding: 12px 0;
    }
    
    .toggle-icon {
        width: 28px;
        height: 28px;
        font-size: 1rem;
    }
    
    .toggle-switch {
        width: 48px;
        height: 26px;
    }
    
    .toggle-slider:before {
        height: 20px;
        width: 20px;
    }
    
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(22px);
    }
    
    .form-label {
        font-size: 0.875rem;
    }
    
    .form-hint {
        font-size: 0.75rem;
    }
}
</style>

<div class="seo-page">
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <a href="<?php echo admin_url('module/seo'); ?>" class="back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="page-title"><?php echo esc_html($title); ?></h1>
                <p class="page-subtitle">XML sitemap yapılandırmasını yönetin</p>
            </div>
        </div>
        <a href="/sitemap.xml" target="_blank" class="preview-btn">
            <span class="material-symbols-outlined">open_in_new</span>
            Sitemap'i Görüntüle
        </a>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="seo-alert <?php echo $_SESSION['flash_type'] === 'success' ? 'success' : 'error'; ?>">
        <span class="material-symbols-outlined">check_circle</span>
        <?php 
        echo esc_html($_SESSION['flash_message']); 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    </div>
    <?php endif; ?>
    
    <form method="POST">
        <!-- General Settings -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">tune</span>
                </div>
                <h2 class="section-title">Genel Ayarlar</h2>
            </div>
            <div class="section-body">
                <div class="toggle-row" style="border-bottom: none; padding-top: 0;">
                    <div class="toggle-info">
                        <div class="toggle-icon">
                            <span class="material-symbols-outlined">account_tree</span>
                        </div>
                        <div>
                            <div class="toggle-label">Sitemap Aktif</div>
                            <div class="toggle-desc">XML sitemap dosyasını oluştur ve sun</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sitemap_enabled" value="1" <?php echo ($settings['sitemap_enabled'] ?? true) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="url-preview">
                    <div>
                        <div class="url-preview-label">Sitemap URL</div>
                        <a href="<?php echo $siteUrl; ?>/sitemap.xml" target="_blank" class="url-preview-link">
                            <?php echo $siteUrl; ?>/sitemap.xml
                        </a>
                    </div>
                    <span class="material-symbols-outlined" style="color: var(--accent);">link</span>
                </div>
            </div>
        </div>
        
        <!-- Content Types -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">checklist</span>
                </div>
                <h2 class="section-title">Dahil Edilecek İçerikler</h2>
            </div>
            <div class="section-body">
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-icon">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <div>
                            <div class="toggle-label">Sayfalar</div>
                            <div class="toggle-desc">Tüm yayınlanmış sayfalar (doğrudan URL)</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sitemap_pages" value="1" <?php echo ($settings['sitemap_pages'] ?? true) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-icon">
                            <span class="material-symbols-outlined">article</span>
                        </div>
                        <div>
                            <div class="toggle-label">Blog Yazıları</div>
                            <div class="toggle-desc">Yayınlanmış tüm blog yazıları (/blog/ uzantılı)</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sitemap_posts" value="1" <?php echo ($settings['sitemap_posts'] ?? true) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-icon">
                            <span class="material-symbols-outlined">folder</span>
                        </div>
                        <div>
                            <div class="toggle-label">Kategoriler</div>
                            <div class="toggle-desc">Aktif kategori sayfaları</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sitemap_categories" value="1" <?php echo ($settings['sitemap_categories'] ?? true) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-icon">
                            <span class="material-symbols-outlined">label</span>
                        </div>
                        <div>
                            <div class="toggle-label">Etiketler</div>
                            <div class="toggle-desc">Kullanılan etiket sayfaları</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sitemap_tags" value="1" <?php echo ($settings['sitemap_tags'] ?? true) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Change Frequency -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">schedule</span>
                </div>
                <h2 class="section-title">Güncelleme Sıklığı</h2>
            </div>
            <div class="section-body">
                <div class="input-grid">
                    <div class="form-group">
                        <label class="form-label">Sayfalar</label>
                        <select name="sitemap_changefreq_pages" class="form-select">
                            <option value="always" <?php echo ($settings['sitemap_changefreq_pages'] ?? '') === 'always' ? 'selected' : ''; ?>>Her zaman</option>
                            <option value="hourly" <?php echo ($settings['sitemap_changefreq_pages'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Saatlik</option>
                            <option value="daily" <?php echo ($settings['sitemap_changefreq_pages'] ?? '') === 'daily' ? 'selected' : ''; ?>>Günlük</option>
                            <option value="weekly" <?php echo ($settings['sitemap_changefreq_pages'] ?? 'weekly') === 'weekly' ? 'selected' : ''; ?>>Haftalık</option>
                            <option value="monthly" <?php echo ($settings['sitemap_changefreq_pages'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Aylık</option>
                            <option value="yearly" <?php echo ($settings['sitemap_changefreq_pages'] ?? '') === 'yearly' ? 'selected' : ''; ?>>Yıllık</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Blog Yazıları</label>
                        <select name="sitemap_changefreq_posts" class="form-select">
                            <option value="always" <?php echo ($settings['sitemap_changefreq_posts'] ?? '') === 'always' ? 'selected' : ''; ?>>Her zaman</option>
                            <option value="hourly" <?php echo ($settings['sitemap_changefreq_posts'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Saatlik</option>
                            <option value="daily" <?php echo ($settings['sitemap_changefreq_posts'] ?? '') === 'daily' ? 'selected' : ''; ?>>Günlük</option>
                            <option value="weekly" <?php echo ($settings['sitemap_changefreq_posts'] ?? 'weekly') === 'weekly' ? 'selected' : ''; ?>>Haftalık</option>
                            <option value="monthly" <?php echo ($settings['sitemap_changefreq_posts'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Aylık</option>
                            <option value="yearly" <?php echo ($settings['sitemap_changefreq_posts'] ?? '') === 'yearly' ? 'selected' : ''; ?>>Yıllık</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategoriler</label>
                        <select name="sitemap_changefreq_categories" class="form-select">
                            <option value="always" <?php echo ($settings['sitemap_changefreq_categories'] ?? '') === 'always' ? 'selected' : ''; ?>>Her zaman</option>
                            <option value="hourly" <?php echo ($settings['sitemap_changefreq_categories'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Saatlik</option>
                            <option value="daily" <?php echo ($settings['sitemap_changefreq_categories'] ?? '') === 'daily' ? 'selected' : ''; ?>>Günlük</option>
                            <option value="weekly" <?php echo ($settings['sitemap_changefreq_categories'] ?? 'weekly') === 'weekly' ? 'selected' : ''; ?>>Haftalık</option>
                            <option value="monthly" <?php echo ($settings['sitemap_changefreq_categories'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Aylık</option>
                            <option value="yearly" <?php echo ($settings['sitemap_changefreq_categories'] ?? '') === 'yearly' ? 'selected' : ''; ?>>Yıllık</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Etiketler</label>
                        <select name="sitemap_changefreq_tags" class="form-select">
                            <option value="always" <?php echo ($settings['sitemap_changefreq_tags'] ?? '') === 'always' ? 'selected' : ''; ?>>Her zaman</option>
                            <option value="hourly" <?php echo ($settings['sitemap_changefreq_tags'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Saatlik</option>
                            <option value="daily" <?php echo ($settings['sitemap_changefreq_tags'] ?? '') === 'daily' ? 'selected' : ''; ?>>Günlük</option>
                            <option value="weekly" <?php echo ($settings['sitemap_changefreq_tags'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Haftalık</option>
                            <option value="monthly" <?php echo ($settings['sitemap_changefreq_tags'] ?? 'monthly') === 'monthly' ? 'selected' : ''; ?>>Aylık</option>
                            <option value="yearly" <?php echo ($settings['sitemap_changefreq_tags'] ?? '') === 'yearly' ? 'selected' : ''; ?>>Yıllık</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Priority -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">priority_high</span>
                </div>
                <h2 class="section-title">Öncelik Değerleri</h2>
            </div>
            <div class="section-body">
                <div class="form-hint" style="margin-top: 0; margin-bottom: 20px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">info</span>
                    0.0 ile 1.0 arasında değer girin. Yüksek değer = yüksek öncelik
                </div>
                
                <div class="input-grid">
                    <div class="form-group">
                        <label class="form-label">Ana Sayfa</label>
                        <input type="text" name="sitemap_priority_home" 
                               value="<?php echo esc_attr($settings['sitemap_priority_home'] ?? '1.0'); ?>"
                               class="form-input" placeholder="1.0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sayfalar</label>
                        <input type="text" name="sitemap_priority_pages" 
                               value="<?php echo esc_attr($settings['sitemap_priority_pages'] ?? '0.8'); ?>"
                               class="form-input" placeholder="0.8">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Blog Yazıları</label>
                        <input type="text" name="sitemap_priority_posts" 
                               value="<?php echo esc_attr($settings['sitemap_priority_posts'] ?? '0.8'); ?>"
                               class="form-input" placeholder="0.8">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategoriler</label>
                        <input type="text" name="sitemap_priority_categories" 
                               value="<?php echo esc_attr($settings['sitemap_priority_categories'] ?? '0.6'); ?>"
                               class="form-input" placeholder="0.6">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Etiketler</label>
                        <input type="text" name="sitemap_priority_tags" 
                               value="<?php echo esc_attr($settings['sitemap_priority_tags'] ?? '0.4'); ?>"
                               class="form-input" placeholder="0.4">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Custom URLs -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">add_link</span>
                </div>
                <h2 class="section-title">Manuel URL'ler</h2>
            </div>
            <div class="section-body">
                <textarea name="sitemap_custom_urls" class="form-textarea" 
                          placeholder="/hakkimizda&#10;/iletisim&#10;https://example.com/ozel-sayfa"><?php echo esc_html($settings['sitemap_custom_urls'] ?? ''); ?></textarea>
                <div class="form-hint">
                    <span class="material-symbols-outlined" style="font-size: 18px;">info</span>
                    Her satıra bir URL. Göreceli (/) veya tam URL (https://) kullanabilirsiniz.
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <span class="material-symbols-outlined">save</span>
                Kaydet
            </button>
        </div>
    </form>
</div>
