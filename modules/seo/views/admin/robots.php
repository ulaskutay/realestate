<?php
/**
 * SEO Modül - Robots.txt Düzenleyici
 * Modern Design
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

.seo-page {
    --accent: #7b61ff;
    --accent-dim: rgba(123, 97, 255, 0.1);
    --accent-glow: rgba(123, 97, 255, 0.3);
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
    background: rgba(123, 97, 255, 0.1);
    border: 1px solid rgba(123, 97, 255, 0.3);
    color: var(--accent);
}

/* Editor Container */
.editor-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
}

.editor-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
}

.editor-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text);
    font-weight: 600;
    font-size: 1rem;
}

.editor-title .material-symbols-outlined {
    color: var(--accent);
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

.editor-body {
    padding: 0;
}

.code-editor {
    width: 100%;
    min-height: 400px;
    padding: 24px;
    background: var(--surface);
    border: none;
    color: var(--text);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9375rem;
    line-height: 1.7;
    resize: vertical;
}

.code-editor:focus {
    outline: none;
}

.code-editor::placeholder {
    color: var(--text-dim);
}

/* Line numbers simulation */
.editor-wrapper {
    position: relative;
    display: flex;
}

.line-numbers {
    padding: 24px 16px;
    background: var(--surface-2);
    color: var(--text-dim);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9375rem;
    line-height: 1.7;
    text-align: right;
    user-select: none;
    border-right: 1px solid var(--border);
    min-width: 50px;
}

/* URL Preview */
.url-preview {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: var(--surface-2);
    border-top: 1px solid var(--border);
}

.url-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.url-label {
    color: var(--text-dim);
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.url-link {
    color: var(--accent);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9375rem;
    text-decoration: none;
}

.url-link:hover {
    text-decoration: underline;
}

/* Template Snippets */
.snippets-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    margin-top: 24px;
    overflow: hidden;
}

.snippets-header {
    padding: 16px 20px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.snippets-header .material-symbols-outlined {
    color: var(--accent);
}

.snippets-title {
    color: var(--text);
    font-weight: 600;
    font-size: 0.9375rem;
    margin: 0;
}

.snippets-body {
    padding: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.snippet-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--surface-3);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-dim);
    font-size: 0.8125rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.snippet-btn:hover {
    background: var(--accent-dim);
    border-color: var(--accent);
    color: var(--accent);
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
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
    
    .editor-header {
        padding: 16px 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .editor-title {
        font-size: 0.9375rem;
    }
    
    .code-editor {
        padding: 20px;
        font-size: 0.875rem;
        min-height: 300px;
    }
    
    .line-numbers {
        padding: 20px 12px;
        font-size: 0.875rem;
        min-width: 40px;
    }
    
    .url-preview {
        padding: 12px 16px;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .url-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .url-link {
        font-size: 0.875rem;
        word-break: break-all;
    }
    
    .snippets-section {
        margin-top: 20px;
    }
    
    .snippets-header {
        padding: 14px 16px;
    }
    
    .snippets-body {
        padding: 12px;
        gap: 8px;
    }
    
    .snippet-btn {
        font-size: 0.75rem;
        padding: 6px 12px;
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
    
    .editor-header {
        padding: 14px 16px;
    }
    
    .code-editor {
        padding: 16px;
        font-size: 0.8125rem;
        min-height: 250px;
    }
    
    .line-numbers {
        padding: 16px 10px;
        font-size: 0.8125rem;
        min-width: 35px;
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
                <p class="page-subtitle">Arama motoru tarayıcıları için erişim kuralları</p>
            </div>
        </div>
        <a href="/robots.txt" target="_blank" class="preview-btn">
            <span class="material-symbols-outlined">open_in_new</span>
            robots.txt Görüntüle
        </a>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="seo-alert success">
        <span class="material-symbols-outlined">check_circle</span>
        <?php 
        echo esc_html($_SESSION['flash_message']); 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="editor-container">
            <div class="editor-header">
                <div class="editor-title">
                    <span class="material-symbols-outlined">smart_toy</span>
                    robots.txt Editörü
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="robots_enabled" value="1" <?php echo ($settings['robots_enabled'] ?? true) ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="editor-body">
                <textarea name="robots_content" class="code-editor" placeholder="User-agent: *
Allow: /

Sitemap: <?php echo $siteUrl; ?>/sitemap.xml"><?php echo esc_html($settings['robots_content'] ?? ''); ?></textarea>
            </div>
            
            <div class="url-preview">
                <div class="url-info">
                    <span class="url-label">URL:</span>
                    <a href="<?php echo $siteUrl; ?>/robots.txt" target="_blank" class="url-link">
                        <?php echo $siteUrl; ?>/robots.txt
                    </a>
                </div>
                <span class="material-symbols-outlined" style="color: var(--accent);">link</span>
            </div>
        </div>
        
        <!-- Quick Snippets -->
        <div class="snippets-section">
            <div class="snippets-header">
                <span class="material-symbols-outlined">bolt</span>
                <h3 class="snippets-title">Hızlı Ekle</h3>
            </div>
            <div class="snippets-body">
                <button type="button" class="snippet-btn" onclick="insertSnippet('User-agent: *\nAllow: /')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                    Tümüne İzin Ver
                </button>
                <button type="button" class="snippet-btn" onclick="insertSnippet('User-agent: *\nDisallow: /')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">block</span>
                    Tümünü Engelle
                </button>
                <button type="button" class="snippet-btn" onclick="insertSnippet('Disallow: /admin/')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">admin_panel_settings</span>
                    Admin Engelle
                </button>
                <button type="button" class="snippet-btn" onclick="insertSnippet('Disallow: /api/')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">api</span>
                    API Engelle
                </button>
                <button type="button" class="snippet-btn" onclick="insertSnippet('Sitemap: <?php echo $siteUrl; ?>/sitemap.xml')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">account_tree</span>
                    Sitemap Ekle
                </button>
                <button type="button" class="snippet-btn" onclick="insertSnippet('Crawl-delay: 10')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">schedule</span>
                    Crawl Delay
                </button>
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
function insertSnippet(text) {
    const editor = document.querySelector('.code-editor');
    const cursorPos = editor.selectionStart;
    const before = editor.value.substring(0, cursorPos);
    const after = editor.value.substring(editor.selectionEnd);
    
    editor.value = before + (before && !before.endsWith('\n') ? '\n' : '') + text + '\n' + after;
    editor.focus();
    editor.selectionStart = editor.selectionEnd = cursorPos + text.length + 1;
}
</script>
