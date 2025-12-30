<?php
/**
 * SEO Modül - Meta Tag Ayarları
 * Modern Design
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.seo-page {
    --accent: #ff6b6b;
    --accent-dim: rgba(255, 107, 107, 0.1);
    --accent-glow: rgba(255, 107, 107, 0.3);
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

/* Alert */
.seo-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 0.9375rem;
    background: var(--accent-dim);
    border: 1px solid var(--accent-glow);
    color: var(--accent);
}

/* Form Section */
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
    background: var(--surface-2);
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

/* Form Group */
.form-group {
    margin-bottom: 20px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text);
    font-weight: 500;
    font-size: 0.9375rem;
    margin-bottom: 8px;
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
    font-family: inherit;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}

.form-input::placeholder {
    color: var(--text-dim);
}

.form-textarea {
    width: 100%;
    min-height: 100px;
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

/* Variables Helper */
.variables-box {
    background: var(--surface-3);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px;
    margin-top: 16px;
}

.variables-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text);
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.variables-title .material-symbols-outlined {
    font-size: 16px;
    color: var(--accent);
}

.variables-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.variable-tag {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 6px 12px;
    border-radius: 6px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8125rem;
    color: var(--accent);
    cursor: pointer;
    transition: all 0.2s;
}

.variable-tag:hover {
    background: var(--accent-dim);
    border-color: var(--accent);
}

/* Preview Card */
.preview-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.preview-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #666;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.serp-preview {
    max-width: 600px;
}

.serp-title {
    font-size: 1.125rem;
    color: #1a0dab;
    font-family: Arial, sans-serif;
    margin: 0 0 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.serp-url {
    font-size: 0.875rem;
    color: #006621;
    font-family: Arial, sans-serif;
    margin: 0 0 4px;
}

.serp-description {
    font-size: 0.875rem;
    color: #545454;
    font-family: Arial, sans-serif;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Separator Input */
.separator-input {
    display: flex;
    align-items: center;
    gap: 12px;
}

.separator-input input {
    width: 80px;
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
}

.separator-preview {
    color: var(--text-dim);
    font-size: 0.875rem;
}

/* Form Actions */
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
    color: white;
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
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
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
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-input, .form-textarea {
        font-size: 16px; /* iOS zoom prevention */
        padding: 12px 14px;
    }
    
    .separator-input {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .separator-input input {
        width: 100%;
    }
    
    .separator-preview {
        font-size: 0.8125rem;
    }
    
    .variables-box {
        padding: 12px;
    }
    
    .variables-list {
        gap: 6px;
    }
    
    .variable-tag {
        font-size: 0.75rem;
        padding: 5px 10px;
    }
    
    .preview-card {
        padding: 16px;
        margin-top: 16px;
    }
    
    .serp-preview {
        max-width: 100%;
    }
    
    .serp-title {
        font-size: 1rem;
    }
    
    .form-actions {
        flex-direction: column-reverse;
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
    
    .form-label {
        font-size: 0.875rem;
    }
    
    .form-label .material-symbols-outlined {
        font-size: 16px;
    }
}
</style>

<div class="seo-page">
    <!-- Header -->
    <div class="page-header">
        <a href="<?php echo admin_url('module/seo'); ?>" class="back-btn">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="page-title"><?php echo esc_html($title); ?></h1>
            <p class="page-subtitle">Title ve description şablonlarını özelleştirin</p>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="seo-alert">
        <span class="material-symbols-outlined">check_circle</span>
        <?php 
        echo esc_html($_SESSION['flash_message']); 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    </div>
    <?php endif; ?>
    
    <form method="POST">
        <!-- Title Templates -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">title</span>
                </div>
                <h2 class="section-title">Title Şablonları</h2>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">home</span>
                        Ana Sayfa Title
                    </label>
                    <input type="text" name="meta_title_home" class="form-input"
                           value="<?php echo esc_attr($settings['meta_title_home'] ?? '{site_name}'); ?>"
                           placeholder="{site_name}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">article</span>
                        Yazı Title
                    </label>
                    <input type="text" name="meta_title_post" class="form-input"
                           value="<?php echo esc_attr($settings['meta_title_post'] ?? '{post_title} - {site_name}'); ?>"
                           placeholder="{post_title} - {site_name}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">folder</span>
                        Kategori Title
                    </label>
                    <input type="text" name="meta_title_category" class="form-input"
                           value="<?php echo esc_attr($settings['meta_title_category'] ?? '{category_name} - {site_name}'); ?>"
                           placeholder="{category_name} - {site_name}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">remove</span>
                        Ayraç
                    </label>
                    <div class="separator-input">
                        <input type="text" name="meta_title_separator" class="form-input"
                               value="<?php echo esc_attr($settings['meta_title_separator'] ?? ' - '); ?>"
                               placeholder=" - ">
                        <span class="separator-preview">Örnek: Yazı Başlığı <strong><?php echo esc_html($settings['meta_title_separator'] ?? ' - '); ?></strong> Site Adı</span>
                    </div>
                </div>
                
                <div class="variables-box">
                    <h4 class="variables-title">
                        <span class="material-symbols-outlined">code</span>
                        Kullanılabilir Değişkenler
                    </h4>
                    <div class="variables-list">
                        <span class="variable-tag">{site_name}</span>
                        <span class="variable-tag">{post_title}</span>
                        <span class="variable-tag">{category_name}</span>
                        <span class="variable-tag">{tag_name}</span>
                        <span class="variable-tag">{page_number}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Description Templates -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <h2 class="section-title">Description Şablonları</h2>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">home</span>
                        Ana Sayfa Description
                    </label>
                    <textarea name="meta_description_home" class="form-textarea"
                              placeholder="Sitenizin genel açıklaması..."><?php echo esc_html($settings['meta_description_home'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">text_snippet</span>
                        Varsayılan Description
                    </label>
                    <textarea name="meta_description_default" class="form-textarea"
                              placeholder="Özel açıklama girilmediğinde kullanılacak varsayılan metin..."><?php echo esc_html($settings['meta_description_default'] ?? ''); ?></textarea>
                </div>
                
                <!-- Google Preview -->
                <div class="preview-card">
                    <h4 class="preview-title">
                        <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                        Google Arama Önizlemesi
                    </h4>
                    <div class="serp-preview">
                        <h3 class="serp-title"><?php echo esc_html($settings['meta_title_home'] ?? 'Site Adı'); ?></h3>
                        <p class="serp-url">example.com</p>
                        <p class="serp-description"><?php echo esc_html($settings['meta_description_home'] ?? 'Sitenizin açıklaması burada görünecek. Meta description, arama sonuçlarında görünen önemli bir SEO öğesidir.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <span class="material-symbols-outlined">save</span>
                Kaydet
            </button>
        </div>
    </form>
</div>

<script>
// Variable tag click to copy
document.querySelectorAll('.variable-tag').forEach(tag => {
    tag.addEventListener('click', function() {
        navigator.clipboard.writeText(this.textContent);
        const original = this.textContent;
        this.textContent = 'Kopyalandı!';
        setTimeout(() => this.textContent = original, 1000);
    });
});
</script>
