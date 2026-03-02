<?php
/**
 * SEO Modül - Sayfa Meta Yönetimi
 * Tüm sayfalar için meta title, description override
 */
$pageKeys = $pageKeys ?? [];
$saved = $saved ?? [];
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.seo-page-meta {
    --accent: #7b61ff;
    --accent-dim: rgba(123, 97, 255, 0.1);
    --accent-glow: rgba(123, 97, 255, 0.3);
    --surface: #ffffff;
    --surface-2: #f9fafb;
    --surface-3: #f3f4f6;
    --border: #e5e7eb;
    --text: #111827;
    --text-dim: #6b7280;
    font-family: 'Outfit', sans-serif;
}

html.dark .seo-page-meta,
.dark .seo-page-meta {
    --surface: #0d1117;
    --surface-2: #161b22;
    --surface-3: #21262d;
    --border: #30363d;
    --text: #e6edf3;
    --text-dim: #7d8590;
}

.seo-page-meta .page-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.seo-page-meta .back-btn {
    width: 44px; height: 44px; background: var(--surface); border: 1px solid var(--border);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    color: var(--text-dim); text-decoration: none; transition: all 0.2s;
}
.seo-page-meta .back-btn:hover { border-color: var(--accent); color: var(--accent); }
.seo-page-meta .page-title { font-size: 1.75rem; font-weight: 700; color: var(--text); margin: 0; }
.seo-page-meta .page-subtitle { color: var(--text-dim); font-size: 0.9375rem; margin: 4px 0 0; }
.seo-page-meta .seo-alert {
    display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 12px;
    margin-bottom: 24px; font-size: 0.9375rem; background: var(--accent-dim);
    border: 1px solid var(--accent-glow); color: var(--accent);
}

.seo-page-meta .meta-table-wrap {
    background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; margin-bottom: 24px;
}
.seo-page-meta .meta-table { width: 100%; border-collapse: collapse; }
.seo-page-meta .meta-table th {
    text-align: left; padding: 14px 16px; background: var(--surface-2); border-bottom: 1px solid var(--border);
    font-weight: 600; font-size: 0.875rem; color: var(--text);
}
.seo-page-meta .meta-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: top; }
.seo-page-meta .meta-table tr:last-child td { border-bottom: none; }
.seo-page-meta .meta-table .page-name { font-weight: 500; color: var(--text); white-space: nowrap; }
.seo-page-meta .meta-table input[type="text"],
.seo-page-meta .meta-table textarea {
    width: 100%; padding: 10px 12px; background: var(--surface-2); border: 1px solid var(--border);
    border-radius: 8px; color: var(--text); font-size: 0.875rem; font-family: inherit;
}
.seo-page-meta .meta-table input:focus,
.seo-page-meta .meta-table textarea:focus {
    outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px var(--accent-dim);
}
.seo-page-meta .meta-table textarea { min-height: 60px; resize: vertical; }
.seo-page-meta .meta-table .char-count { font-size: 0.75rem; color: var(--text-dim); margin-top: 4px; }
.seo-page-meta .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px; }
.seo-page-meta .btn-submit {
    display: flex; align-items: center; gap: 8px; padding: 14px 28px; background: var(--accent); border: none;
    border-radius: 12px; color: white; font-weight: 600; font-size: 0.9375rem; cursor: pointer;
    transition: all 0.2s; font-family: inherit;
}
.seo-page-meta .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px var(--accent-glow); }

@media (max-width: 768px) {
    .seo-page-meta .meta-table-wrap { overflow-x: auto; }
    .seo-page-meta .meta-table { min-width: 600px; }
}
</style>

<div class="seo-page-meta">
    <div class="page-header">
        <a href="<?php echo admin_url('module/seo'); ?>" class="back-btn">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="page-title"><?php echo esc_html($title); ?></h1>
            <p class="page-subtitle">Modül sayfaları (İlanlar, Danışmanlar, Harita, İlan Kategorisi vb.) dahil tüm sayfa tipleri için meta title, description ve robots değerlerini buradan düzenleyebilirsiniz.</p>
            <p style="margin-top: 8px; font-size: 0.875rem; color: var(--text-dim);"><strong>Şablon kullanımı:</strong> Meta Title veya Meta Description alanını boş bırakırsanız, <a href="<?php echo admin_url('module/seo/meta'); ?>" style="color: var(--accent);">Meta Tag Ayarları</a>ndaki <em>Diğer Tüm Sayfalar</em> title şablonu ve <em>Diğer Tüm Sayfalar / Varsayılan Description</em> kullanılır. İstediğiniz sayfa için özel değer girerek şablonu geçersiz kılabilirsiniz.</p>
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
        <div class="meta-table-wrap">
            <table class="meta-table">
                <thead>
                    <tr>
                        <th style="width:180px">Sayfa</th>
                        <th>Meta Title</th>
                        <th>Meta Description</th>
                        <th style="width:120px">Meta Robots</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pageKeys as $key => $label): 
                        $row = $saved[$key] ?? [];
                        $metaTitle = $row['meta_title'] ?? '';
                        $metaDesc = $row['meta_description'] ?? '';
                        $metaRobots = $row['meta_robots'] ?? '';
                    ?>
                    <tr>
                        <td class="page-name"><?php echo esc_html($label); ?></td>
                        <td>
                            <input type="text" name="meta_title[<?php echo esc_attr($key); ?>]" 
                                   value="<?php echo esc_attr($metaTitle); ?>" 
                                   placeholder="Boş = varsayılan" maxlength="255">
                            <div class="char-count"><span class="cnt-title-<?php echo esc_attr($key); ?>"><?php echo mb_strlen($metaTitle); ?></span>/60</div>
                        </td>
                        <td>
                            <textarea name="meta_description[<?php echo esc_attr($key); ?>]" 
                                      placeholder="Boş = varsayılan" maxlength="500" rows="2"><?php echo esc_html($metaDesc); ?></textarea>
                            <div class="char-count"><span class="cnt-desc-<?php echo esc_attr($key); ?>"><?php echo mb_strlen($metaDesc); ?></span>/160</div>
                        </td>
                        <td>
                            <input type="text" name="meta_robots[<?php echo esc_attr($key); ?>]" 
                                   value="<?php echo esc_attr($metaRobots); ?>" 
                                   placeholder="index, follow" maxlength="100">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
document.querySelectorAll('.meta-table input[name^="meta_title"], .meta-table textarea[name^="meta_description"]').forEach(function(el) {
    var key = el.name.match(/\[([^\]]+)\]/)[1];
    var isTitle = el.name.indexOf('meta_title') !== -1;
    var cntEl = document.querySelector(isTitle ? '.cnt-title-' + key : '.cnt-desc-' + key);
    var max = isTitle ? 60 : 160;
    el.addEventListener('input', function() {
        if (cntEl) cntEl.textContent = (el.value.length > max ? max : el.value.length);
    });
});
</script>
