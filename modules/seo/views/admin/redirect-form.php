<?php
/**
 * SEO Modül - Yönlendirme Form
 * Modern Design
 */
$isEdit = !empty($redirect);
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

.seo-page {
    --accent: #ffd93d;
    --accent-dim: rgba(255, 217, 61, 0.1);
    --accent-glow: rgba(255, 217, 61, 0.3);
    --success: #00f5d4;
    /* Light mode colors */
    --surface: #ffffff;
    --surface-2: #f9fafb;
    --surface-3: #f3f4f6;
    --border: #e5e7eb;
    --text: #111827;
    --text-dim: #6b7280;
    font-family: 'Outfit', sans-serif;
    max-width: 700px;
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
    gap: 16px;
    margin-bottom: 32px;
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

/* Form Card */
.form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
}

.form-card-header {
    padding: 24px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 16px;
}

.form-icon {
    width: 52px;
    height: 52px;
    background: var(--accent-dim);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: 1.5rem;
}

.form-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.form-card-desc {
    color: var(--text-dim);
    font-size: 0.875rem;
    margin: 4px 0 0;
}

.form-card-body {
    padding: 28px;
}

/* URL Flow Visual */
.url-flow {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    padding: 20px;
    background: var(--surface-2);
    border-radius: 12px;
}

.url-flow-box {
    flex: 1;
    text-align: center;
}

.url-flow-label {
    font-size: 0.75rem;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}

.url-flow-value {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.875rem;
    color: var(--text);
    background: var(--surface);
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid var(--border);
    word-break: break-all;
}

.url-flow-arrow {
    color: var(--accent);
    flex-shrink: 0;
}

/* Form Group */
.form-group {
    margin-bottom: 24px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text);
    font-weight: 500;
    font-size: 0.9375rem;
    margin-bottom: 10px;
}

.form-label .material-symbols-outlined {
    font-size: 18px;
    color: var(--accent);
}

.form-input {
    width: 100%;
    padding: 14px 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-size: 0.9375rem;
    font-family: 'JetBrains Mono', monospace;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}

.form-input::placeholder {
    color: var(--text-dim);
    font-family: 'Outfit', sans-serif;
}

.form-hint {
    color: var(--text-dim);
    font-size: 0.8125rem;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Select & Radio */
.form-select {
    width: 100%;
    padding: 14px 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-size: 0.9375rem;
    font-family: inherit;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%237d8590' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 18px;
    padding-right: 48px;
    transition: all 0.2s;
}

.form-select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}

.form-select option {
    background: var(--surface-2);
    color: var(--text);
}

/* Type Selector */
.type-selector {
    display: flex;
    gap: 12px;
}

.type-option {
    flex: 1;
    position: relative;
}

.type-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.type-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px;
    background: var(--surface-2);
    border: 2px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.type-option input:checked + label {
    border-color: var(--accent);
    background: var(--accent-dim);
}

.type-badge {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
}

.type-name {
    font-weight: 600;
    color: var(--text);
    font-size: 0.9375rem;
}

.type-desc {
    font-size: 0.75rem;
    color: var(--text-dim);
    text-align: center;
}

/* Textarea */
.form-textarea {
    width: 100%;
    min-height: 80px;
    padding: 14px 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-size: 0.9375rem;
    font-family: inherit;
    resize: vertical;
    transition: all 0.2s;
}

.form-textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 8px;
}

.btn-cancel {
    padding: 14px 24px;
    background: var(--surface-3);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text-dim);
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-cancel:hover {
    border-color: var(--text-dim);
    color: var(--text);
}

.btn-submit {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: var(--accent);
    border: none;
    border-radius: 10px;
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
    .seo-page {
        max-width: 100%;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .form-card-header {
        padding: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .form-icon {
        width: 44px;
        height: 44px;
        font-size: 1.25rem;
    }
    
    .form-card-title {
        font-size: 1.125rem;
    }
    
    .form-card-body {
        padding: 20px;
    }
    
    .url-flow {
        flex-direction: column;
        gap: 12px;
        padding: 16px;
    }
    
    .url-flow-arrow {
        transform: rotate(90deg);
    }
    
    .url-flow-value {
        font-size: 0.8125rem;
        padding: 8px 12px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-input, .form-select, .form-textarea {
        font-size: 16px; /* iOS zoom prevention */
        padding: 12px 14px;
    }
    
    .type-selector {
        flex-direction: column;
        gap: 10px;
    }
    
    .type-option label {
        padding: 16px;
    }
    
    .type-badge {
        font-size: 1.25rem;
    }
    
    .form-actions {
        flex-direction: column-reverse;
        gap: 10px;
    }
    
    .btn-cancel, .btn-submit {
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
    
    .form-card-header {
        padding: 16px;
    }
    
    .form-card-body {
        padding: 16px;
    }
    
    .url-flow {
        padding: 12px;
    }
    
    .form-label {
        font-size: 0.875rem;
    }
    
    .form-label .material-symbols-outlined {
        font-size: 16px;
    }
    
    .form-hint {
        font-size: 0.75rem;
    }
    
    .type-name {
        font-size: 0.875rem;
    }
    
    .type-desc {
        font-size: 0.6875rem;
    }
}
</style>

<div class="seo-page">
    <!-- Header -->
    <div class="page-header">
        <a href="<?php echo admin_url('module/seo/redirects'); ?>" class="back-btn">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="page-title"><?php echo esc_html($title); ?></h1>
            <p class="page-subtitle"><?php echo $isEdit ? 'Mevcut yönlendirmeyi düzenleyin' : 'Yeni URL yönlendirmesi oluşturun'; ?></p>
        </div>
    </div>
    
    <form method="POST" action="<?php echo admin_url('module/seo/' . $action); ?>">
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-icon">
                    <span class="material-symbols-outlined"><?php echo $isEdit ? 'edit' : 'add_link'; ?></span>
                </div>
                <div>
                    <h2 class="form-card-title"><?php echo $isEdit ? 'Yönlendirme Düzenle' : 'Yeni Yönlendirme'; ?></h2>
                    <p class="form-card-desc">Eski URL'leri yeni sayfalara yönlendirin</p>
                </div>
            </div>
            
            <div class="form-card-body">
                <!-- URL Flow Visual -->
                <div class="url-flow">
                    <div class="url-flow-box">
                        <div class="url-flow-label">Kaynak URL</div>
                        <div class="url-flow-value" id="source-preview"><?php echo esc_html($redirect['source_url'] ?? '/eski-sayfa'); ?></div>
                    </div>
                    <div class="url-flow-arrow">
                        <span class="material-symbols-outlined" style="font-size: 28px;">arrow_forward</span>
                    </div>
                    <div class="url-flow-box">
                        <div class="url-flow-label">Hedef URL</div>
                        <div class="url-flow-value" id="target-preview"><?php echo esc_html($redirect['target_url'] ?? '/yeni-sayfa'); ?></div>
                    </div>
                </div>
                
                <!-- Source URL -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">logout</span>
                        Kaynak URL
                    </label>
                    <input type="text" name="source_url" class="form-input" required
                           value="<?php echo esc_attr($redirect['source_url'] ?? ''); ?>"
                           placeholder="/eski-sayfa-yolu"
                           oninput="document.getElementById('source-preview').textContent = this.value || '/eski-sayfa'">
                    <div class="form-hint">
                        <span class="material-symbols-outlined" style="font-size: 16px;">info</span>
                        Ziyaretçilerin erişmeye çalıştığı eski URL
                    </div>
                </div>
                
                <!-- Target URL -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">login</span>
                        Hedef URL
                    </label>
                    <input type="text" name="target_url" class="form-input" required
                           value="<?php echo esc_attr($redirect['target_url'] ?? ''); ?>"
                           placeholder="/yeni-sayfa-yolu veya https://example.com/sayfa"
                           oninput="document.getElementById('target-preview').textContent = this.value || '/yeni-sayfa'">
                    <div class="form-hint">
                        <span class="material-symbols-outlined" style="font-size: 16px;">info</span>
                        Ziyaretçilerin yönlendirileceği yeni URL
                    </div>
                </div>
                
                <!-- Redirect Type -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">compare_arrows</span>
                        Yönlendirme Tipi
                    </label>
                    <div class="type-selector">
                        <div class="type-option">
                            <input type="radio" name="type" id="type-301" value="301" 
                                   <?php echo ($redirect['type'] ?? '301') === '301' ? 'checked' : ''; ?>>
                            <label for="type-301">
                                <span class="type-badge">301</span>
                                <span class="type-name">Kalıcı</span>
                                <span class="type-desc">SEO değerini yeni URL'ye aktarır</span>
                            </label>
                        </div>
                        <div class="type-option">
                            <input type="radio" name="type" id="type-302" value="302"
                                   <?php echo ($redirect['type'] ?? '') === '302' ? 'checked' : ''; ?>>
                            <label for="type-302">
                                <span class="type-badge">302</span>
                                <span class="type-name">Geçici</span>
                                <span class="type-desc">SEO değerini orijinal URL'de tutar</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">toggle_on</span>
                        Durum
                    </label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($redirect['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo ($redirect['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Devre Dışı</option>
                    </select>
                </div>
                
                <!-- Note -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">sticky_note_2</span>
                        Not (Opsiyonel)
                    </label>
                    <textarea name="note" class="form-textarea" placeholder="Bu yönlendirme hakkında bir not..."><?php echo esc_html($redirect['note'] ?? ''); ?></textarea>
                </div>
                
                <!-- Actions -->
                <div class="form-actions">
                    <a href="<?php echo admin_url('module/seo/redirects'); ?>" class="btn-cancel">İptal</a>
                    <button type="submit" class="btn-submit">
                        <span class="material-symbols-outlined"><?php echo $isEdit ? 'save' : 'add'; ?></span>
                        <?php echo $isEdit ? 'Güncelle' : 'Oluştur'; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
