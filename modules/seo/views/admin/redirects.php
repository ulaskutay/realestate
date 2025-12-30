<?php
/**
 * SEO Modül - Yönlendirmeler
 * Modern Design
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.seo-page {
    --accent: #00f5d4;
    --accent-dim: rgba(0, 245, 212, 0.1);
    --accent-glow: rgba(0, 245, 212, 0.3);
    --warning: #ffd93d;
    --danger: #f72585;
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

.btn-add {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--accent);
    border: none;
    border-radius: 12px;
    color: var(--surface);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px var(--accent-glow);
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

.seo-alert.error {
    background: rgba(247, 37, 133, 0.1);
    border: 1px solid rgba(247, 37, 133, 0.3);
    color: var(--danger);
}

/* Stats */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}

.stat-mini {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.stat-mini-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text);
    line-height: 1;
}

.stat-mini-number.active { color: var(--accent); }
.stat-mini-number.inactive { color: var(--text-dim); }
.stat-mini-number.hits { color: var(--warning); }

.stat-mini-label {
    color: var(--text-dim);
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 6px;
}

/* Table Container */
.table-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 64px 32px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: var(--surface-3);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: var(--text-dim);
}

.empty-icon .material-symbols-outlined {
    font-size: 2.5rem;
}

.empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text);
    margin: 0 0 8px;
}

.empty-desc {
    color: var(--text-dim);
    margin: 0 0 24px;
}

.empty-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
}

.empty-action:hover {
    text-decoration: underline;
}

/* Table */
.redirect-table {
    width: 100%;
    border-collapse: collapse;
}

.redirect-table th {
    padding: 16px 20px;
    background: var(--surface-2);
    color: var(--text-dim);
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

.redirect-table th:last-child {
    text-align: right;
}

.redirect-table td {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.redirect-table tr:last-child td {
    border-bottom: none;
}

.redirect-table tr:hover {
    background: var(--surface-2);
}

/* URL Display */
.url-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.url-code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.875rem;
    color: var(--text);
    background: var(--surface-3);
    padding: 6px 12px;
    border-radius: 6px;
    display: inline-block;
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.url-note {
    font-size: 0.75rem;
    color: var(--text-dim);
}

.url-target {
    display: flex;
    align-items: center;
    gap: 8px;
}

.url-target .material-symbols-outlined {
    color: var(--accent);
    font-size: 1rem;
}

/* Badges */
.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-301 {
    background: rgba(59, 130, 246, 0.15);
    color: #60a5fa;
}

.badge-302 {
    background: rgba(255, 217, 61, 0.15);
    color: var(--warning);
}

.badge-active {
    background: var(--accent-dim);
    color: var(--accent);
}

.badge-inactive {
    background: var(--surface-3);
    color: var(--text-dim);
}

.hits-count {
    font-variant-numeric: tabular-nums;
    color: var(--text-dim);
    font-size: 0.9375rem;
}

/* Actions */
.actions-cell {
    display: flex;
    justify-content: flex-end;
    gap: 4px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.action-btn:hover {
    background: var(--surface-3);
    color: var(--text);
}

.action-btn.delete:hover {
    background: rgba(247, 37, 133, 0.1);
    color: var(--danger);
}

/* Info Box */
.info-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.info-box-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.info-box-header .material-symbols-outlined {
    color: var(--accent);
}

.info-box-title {
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.info-box-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-box-list li {
    color: var(--text-dim);
    font-size: 0.875rem;
    padding: 6px 0;
    display: flex;
    gap: 8px;
}

.info-box-list li strong {
    color: var(--text);
    font-weight: 500;
}

/* Responsive */
@media (max-width: 1024px) {
    .redirect-table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .redirect-table thead {
        display: none;
    }
    
    .redirect-table tbody {
        display: block;
    }
    
    .redirect-table tr {
        display: block;
        margin-bottom: 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
    }
    
    .redirect-table td {
        display: block;
        padding: 8px 0;
        border-bottom: none;
        text-align: left !important;
    }
    
    .redirect-table td:before {
        content: attr(data-label);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-dim);
        display: block;
        margin-bottom: 4px;
    }
    
    .actions-cell {
        justify-content: flex-start;
        margin-top: 8px;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
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
    
    .btn-add {
        width: 100%;
        justify-content: center;
        min-height: 44px;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .stat-mini {
        padding: 16px;
    }
    
    .stat-mini-number {
        font-size: 1.75rem;
    }
    
    .stat-mini-label {
        font-size: 0.75rem;
    }
    
    .table-container {
        border-radius: 12px;
    }
    
    .url-code {
        max-width: 100%;
        font-size: 0.8125rem;
        padding: 8px 10px;
    }
    
    .action-btn {
        width: 40px;
        height: 40px;
        min-width: 40px;
        min-height: 40px;
    }
    
    .empty-state {
        padding: 48px 24px;
    }
    
    .empty-icon {
        width: 64px;
        height: 64px;
    }
    
    .empty-icon .material-symbols-outlined {
        font-size: 2rem;
    }
    
    .empty-title {
        font-size: 1.125rem;
    }
    
    .info-box {
        padding: 16px;
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
    
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .stat-mini-number {
        font-size: 1.5rem;
    }
    
    .url-cell {
        gap: 6px;
    }
    
    .url-target {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .badge {
        font-size: 0.6875rem;
        padding: 3px 10px;
    }
    
    .hits-count {
        font-size: 0.875rem;
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
                <p class="page-subtitle">301/302 URL yönlendirmelerini yönetin</p>
            </div>
        </div>
        <a href="<?php echo admin_url('module/seo/redirect_create'); ?>" class="btn-add">
            <span class="material-symbols-outlined">add</span>
            Yeni Yönlendirme
        </a>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="seo-alert <?php echo $_SESSION['flash_type'] === 'success' ? 'success' : 'error'; ?>">
        <span class="material-symbols-outlined">
            <?php echo $_SESSION['flash_type'] === 'success' ? 'check_circle' : 'error'; ?>
        </span>
        <?php 
        echo esc_html($_SESSION['flash_message']); 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    </div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-mini">
            <div class="stat-mini-number"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-mini-label">Toplam</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-number active"><?php echo number_format($stats['active']); ?></div>
            <div class="stat-mini-label">Aktif</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-number inactive"><?php echo number_format($stats['inactive']); ?></div>
            <div class="stat-mini-label">Pasif</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-number hits"><?php echo number_format($stats['total_hits']); ?></div>
            <div class="stat-mini-label">Toplam Hit</div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="table-container">
        <?php if (empty($redirects)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <span class="material-symbols-outlined">redo</span>
            </div>
            <h3 class="empty-title">Henüz yönlendirme yok</h3>
            <p class="empty-desc">URL yönlendirmesi ekleyerek eski bağlantıları yeni sayfalara yönlendirin.</p>
            <a href="<?php echo admin_url('module/seo/redirect_create'); ?>" class="empty-action">
                <span class="material-symbols-outlined">add</span>
                İlk yönlendirmeyi ekle
            </a>
        </div>
        <?php else: ?>
        <table class="redirect-table">
            <thead>
                <tr>
                    <th>Kaynak URL</th>
                    <th>Hedef URL</th>
                    <th style="text-align: center;">Tip</th>
                    <th style="text-align: center;">Hit</th>
                    <th style="text-align: center;">Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($redirects as $redirect): ?>
                <tr>
                    <td data-label="Kaynak URL">
                        <div class="url-cell">
                            <code class="url-code"><?php echo esc_html($redirect['source_url']); ?></code>
                            <?php if (!empty($redirect['note'])): ?>
                            <span class="url-note"><?php echo esc_html($redirect['note']); ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td data-label="Hedef URL">
                        <div class="url-target">
                            <span class="material-symbols-outlined">arrow_forward</span>
                            <code class="url-code"><?php echo esc_html($redirect['target_url']); ?></code>
                        </div>
                    </td>
                    <td data-label="Tip" style="text-align: center;">
                        <span class="badge badge-<?php echo $redirect['type']; ?>">
                            <?php echo $redirect['type']; ?>
                        </span>
                    </td>
                    <td data-label="Hit" style="text-align: center;">
                        <span class="hits-count"><?php echo number_format($redirect['hits']); ?></span>
                    </td>
                    <td data-label="Durum" style="text-align: center;">
                        <span class="badge badge-<?php echo $redirect['status'] === 'active' ? 'active' : 'inactive'; ?>">
                            <?php echo $redirect['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                        </span>
                    </td>
                    <td data-label="İşlemler">
                        <div class="actions-cell">
                            <a href="<?php echo admin_url('module/seo/redirect_edit/' . $redirect['id']); ?>" 
                               class="action-btn" title="Düzenle">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <a href="<?php echo admin_url('module/seo/redirect_delete/' . $redirect['id']); ?>" 
                               class="action-btn delete"
                               onclick="return confirm('Bu yönlendirmeyi silmek istediğinize emin misiniz?')"
                               title="Sil">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <!-- Info Box -->
    <div class="info-box">
        <div class="info-box-header">
            <span class="material-symbols-outlined">help</span>
            <h4 class="info-box-title">Yönlendirme Tipleri</h4>
        </div>
        <ul class="info-box-list">
            <li>
                <strong>301 (Kalıcı):</strong>
                Sayfa kalıcı olarak taşındı. SEO değerini yeni URL'ye aktarır.
            </li>
            <li>
                <strong>302 (Geçici):</strong>
                Sayfa geçici olarak başka yere yönlendirildi. SEO değerini korur.
            </li>
        </ul>
    </div>
</div>
