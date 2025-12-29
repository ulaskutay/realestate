<?php
/**
 * SEO Modül - Schema.org Ayarları
 * Modern Design
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.seo-page {
    --accent: #6bcb77;
    --accent-dim: rgba(107, 203, 119, 0.1);
    --accent-glow: rgba(107, 203, 119, 0.3);
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
    justify-content: space-between;
    background: var(--surface-2);
}

.section-header-left {
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

/* Toggle Switch */
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

.form-hint {
    color: var(--text-dim);
    font-size: 0.8125rem;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Social Grid */
.social-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

@media (max-width: 768px) {
    .social-grid { grid-template-columns: 1fr; }
}

.social-input {
    position: relative;
}

.social-input .form-input {
    padding-left: 48px;
}

.social-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
}

/* JSON Preview */
.json-preview {
    background: var(--surface-3);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-top: 20px;
    overflow: hidden;
}

.json-header {
    padding: 12px 16px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
}

.json-header .material-symbols-outlined {
    color: var(--accent);
    font-size: 18px;
}

.json-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.json-body {
    padding: 16px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8125rem;
    line-height: 1.6;
    color: var(--text-dim);
    max-height: 200px;
    overflow-y: auto;
}

.json-body .key { color: #9b5de5; }
.json-body .string { color: var(--accent); }
.json-body .bracket { color: var(--text-dim); }

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
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .section-header {
        padding: 16px 20px;
        flex-wrap: wrap;
        gap: 12px;
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
    
    .form-input {
        font-size: 16px; /* iOS zoom prevention */
        padding: 12px 14px;
    }
    
    .social-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .social-input .form-input {
        padding-left: 44px;
    }
    
    .social-icon {
        left: 12px;
        width: 20px;
        height: 20px;
    }
    
    .json-preview {
        margin-top: 16px;
    }
    
    .json-header {
        padding: 10px 14px;
    }
    
    .json-body {
        padding: 12px;
        font-size: 0.75rem;
        max-height: 150px;
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
    
    .form-label {
        font-size: 0.875rem;
    }
    
    .form-label .material-symbols-outlined {
        font-size: 16px;
    }
    
    .form-hint {
        font-size: 0.75rem;
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
    
    .json-body {
        font-size: 0.6875rem;
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
            <p class="page-subtitle">Yapılandırılmış veri (JSON-LD) ayarları</p>
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
        <!-- Organization -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-header-left">
                    <div class="section-icon">
                        <span class="material-symbols-outlined">apartment</span>
                    </div>
                    <h2 class="section-title">Organization Schema</h2>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="schema_enabled" value="1" <?php echo ($settings['schema_enabled'] ?? true) ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <div class="section-body">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">business</span>
                        Kuruluş Adı
                    </label>
                    <input type="text" name="schema_organization_name" class="form-input"
                           value="<?php echo esc_attr($settings['schema_organization_name'] ?? ''); ?>"
                           placeholder="Şirket veya marka adınız">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">link</span>
                        Web Sitesi URL
                    </label>
                    <input type="url" name="schema_organization_url" class="form-input"
                           value="<?php echo esc_attr($settings['schema_organization_url'] ?? ''); ?>"
                           placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-outlined">image</span>
                        Logo URL
                    </label>
                    <input type="url" name="schema_organization_logo" class="form-input"
                           value="<?php echo esc_attr($settings['schema_organization_logo'] ?? ''); ?>"
                           placeholder="https://example.com/logo.png">
                    <div class="form-hint">
                        <span class="material-symbols-outlined" style="font-size: 16px;">info</span>
                        Önerilen boyut: En az 112x112 piksel, kare veya dikdörtgen
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Social Profiles -->
        <div class="form-section">
            <div class="section-header">
                <div class="section-header-left">
                    <div class="section-icon">
                        <span class="material-symbols-outlined">share</span>
                    </div>
                    <h2 class="section-title">Sosyal Medya Profilleri</h2>
                </div>
            </div>
            <div class="section-body">
                <div class="social-grid">
                    <div class="form-group">
                        <label class="form-label">Facebook</label>
                        <div class="social-input">
                            <span class="social-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </span>
                            <input type="url" name="schema_social_facebook" class="form-input"
                                   value="<?php echo esc_attr($settings['schema_social_facebook'] ?? ''); ?>"
                                   placeholder="https://facebook.com/sayfaniz">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Twitter / X</label>
                        <div class="social-input">
                            <span class="social-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </span>
                            <input type="url" name="schema_social_twitter" class="form-input"
                                   value="<?php echo esc_attr($settings['schema_social_twitter'] ?? ''); ?>"
                                   placeholder="https://twitter.com/hesabiniz">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Instagram</label>
                        <div class="social-input">
                            <span class="social-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </span>
                            <input type="url" name="schema_social_instagram" class="form-input"
                                   value="<?php echo esc_attr($settings['schema_social_instagram'] ?? ''); ?>"
                                   placeholder="https://instagram.com/hesabiniz">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">LinkedIn</label>
                        <div class="social-input">
                            <span class="social-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </span>
                            <input type="url" name="schema_social_linkedin" class="form-input"
                                   value="<?php echo esc_attr($settings['schema_social_linkedin'] ?? ''); ?>"
                                   placeholder="https://linkedin.com/company/sirketiniz">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">YouTube</label>
                        <div class="social-input">
                            <span class="social-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </span>
                            <input type="url" name="schema_social_youtube" class="form-input"
                                   value="<?php echo esc_attr($settings['schema_social_youtube'] ?? ''); ?>"
                                   placeholder="https://youtube.com/@kanaliniz">
                        </div>
                    </div>
                </div>
                
                <!-- JSON Preview -->
                <div class="json-preview">
                    <div class="json-header">
                        <span class="material-symbols-outlined">code</span>
                        <h4 class="json-title">JSON-LD Önizleme</h4>
                    </div>
                    <div class="json-body">
<pre><span class="bracket">{</span>
  <span class="key">"@context"</span>: <span class="string">"https://schema.org"</span>,
  <span class="key">"@type"</span>: <span class="string">"Organization"</span>,
  <span class="key">"name"</span>: <span class="string">"<?php echo esc_html($settings['schema_organization_name'] ?? 'Kuruluş Adı'); ?>"</span>,
  <span class="key">"url"</span>: <span class="string">"<?php echo esc_html($settings['schema_organization_url'] ?? $siteUrl); ?>"</span>,
  <span class="key">"logo"</span>: <span class="string">"<?php echo esc_html($settings['schema_organization_logo'] ?? ''); ?>"</span>,
  <span class="key">"sameAs"</span>: <span class="bracket">[</span>
    <span class="string">"https://facebook.com/..."</span>,
    <span class="string">"https://twitter.com/..."</span>
  <span class="bracket">]</span>
<span class="bracket">}</span></pre>
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
