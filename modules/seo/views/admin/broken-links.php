<?php
/**
 * SEO Modül - Kırık Bağlantılar (404 Kontrolü)
 */
$allResults = $allResults ?? [];
$brokenOnly = $brokenOnly ?? [];
$siteUrl = $siteUrl ?? '';
?>

<style>
.bl-page {
    --accent: #f59e0b;
    --accent-dim: rgba(245, 158, 11, 0.1);
    --danger: #ef4444;
    --success: #22c55e;
    --surface: #ffffff;
    --surface-2: #f9fafb;
    --border: #e5e7eb;
    --text: #111827;
    --text-dim: #6b7280;
    font-family: 'Outfit', sans-serif;
}
html.dark .bl-page, .dark .bl-page {
    --surface: #0d1117; --surface-2: #161b22; --border: #30363d; --text: #e6edf3; --text-dim: #7d8590;
}
.bl-page .page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
.bl-page .page-header-left { display: flex; align-items: center; gap: 16px; }
.bl-page .back-btn {
    width: 44px; height: 44px; background: var(--surface); border: 1px solid var(--border);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    color: var(--text-dim); text-decoration: none;
}
.bl-page .back-btn:hover { border-color: var(--accent); color: var(--accent); }
.bl-page .page-title { font-size: 1.75rem; font-weight: 700; color: var(--text); margin: 0; }
.bl-page .page-subtitle { color: var(--text-dim); font-size: 0.9375rem; margin: 4px 0 0; }
.bl-page .btn-scan {
    display: inline-flex; align-items: center; gap: 8px; padding: 14px 24px; background: var(--accent);
    border: none; border-radius: 12px; color: #fff; font-weight: 600; font-size: 0.9375rem;
    cursor: pointer; text-decoration: none; font-family: inherit;
}
.bl-page .btn-scan:hover { opacity: 0.9; }
.bl-page .seo-alert {
    display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 12px;
    margin-bottom: 24px; font-size: 0.9375rem; background: var(--accent-dim);
    border: 1px solid rgba(245, 158, 11, 0.3); color: var(--accent);
}
.bl-page .bl-table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; margin-bottom: 24px; }
.bl-page .bl-table { width: 100%; border-collapse: collapse; }
.bl-page .bl-table th {
    text-align: left; padding: 14px 16px; background: var(--surface-2); border-bottom: 1px solid var(--border);
    font-weight: 600; font-size: 0.875rem; color: var(--text);
}
.bl-page .bl-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
.bl-page .bl-table tr:last-child td { border-bottom: none; }
.bl-page .bl-table .code-url { font-size: 0.8125rem; word-break: break-all; color: var(--text); }
.bl-page .bl-table .http-badge {
    display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.8125rem; font-weight: 600;
}
.bl-page .bl-table .http-badge.ok { background: rgba(34, 197, 94, 0.15); color: var(--success); }
.bl-page .bl-table .http-badge.error { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
.bl-page .bl-table .http-badge.unknown { background: var(--surface-2); color: var(--text-dim); }
.bl-page .bl-table .link-add {
    display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px;
    background: var(--accent-dim); color: var(--accent); text-decoration: none; font-size: 0.8125rem; font-weight: 500;
}
.bl-page .bl-table .link-add:hover { background: rgba(245, 158, 11, 0.2); }
.bl-page .empty-state { text-align: center; padding: 48px 24px; color: var(--text-dim); }
.bl-page .empty-state .material-symbols-outlined { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
</style>

<div class="bl-page">
    <div class="page-header">
        <div class="page-header-left">
            <a href="<?php echo admin_url('module/seo'); ?>" class="back-btn">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="page-title"><?php echo esc_html($title); ?></h1>
                <p class="page-subtitle">Sitemap ve menüdeki linkleri tarayıp 404 veya hatalı URL'leri tespit edin</p>
            </div>
        </div>
        <form method="POST" action="<?php echo admin_url('module/seo/broken_links_scan'); ?>" style="display:inline;">
            <button type="submit" class="btn-scan">
                <span class="material-symbols-outlined">search</span>
                Taramayı Başlat
            </button>
        </form>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="seo-alert">
        <span class="material-symbols-outlined">info</span>
        <?php
        echo esc_html($_SESSION['flash_message']);
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    </div>
    <?php endif; ?>

    <div class="bl-table-wrap">
        <?php if (empty($allResults)): ?>
        <div class="empty-state">
            <span class="material-symbols-outlined">link_off</span>
            <p>Henüz tarama yapılmadı. &quot;Taramayı Başlat&quot; ile sitemap ve menü linklerini kontrol edin.</p>
        </div>
        <?php else: ?>
        <table class="bl-table">
            <thead>
                <tr>
                    <th>URL</th>
                    <th style="width:100px">Kaynak</th>
                    <th style="width:100px">HTTP</th>
                    <th style="width:180px">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allResults as $r): 
                    $code = $r['http_code'] ?? null;
                    $isError = $code === null || $code >= 400;
                    $badgeClass = $code !== null && $code >= 200 && $code < 300 ? 'ok' : ($isError ? 'error' : 'unknown');
                    $redirectUrl = admin_url('module/seo/redirect_create') . '?source_url=' . rawurlencode(parse_url($r['url'], PHP_URL_PATH) ?: $r['url']);
                ?>
                <tr>
                    <td>
                        <span class="code-url" title="<?php echo esc_attr($r['url']); ?>"><?php echo esc_html($r['url']); ?></span>
                        <?php if (!empty($r['link_text'])): ?>
                        <br><small style="color:var(--text-dim)"><?php echo esc_html($r['link_text']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($r['source'] ?? '-'); ?></td>
                    <td>
                        <span class="http-badge <?php echo $badgeClass; ?>">
                            <?php echo $code !== null ? (int)$code : '—'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isError): ?>
                        <a href="<?php echo esc_url($redirectUrl); ?>" class="link-add">
                            <span class="material-symbols-outlined" style="font-size:14px">redo</span>
                            Yönlendirme ekle
                        </a>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
