<?php
/**
 * SEO Modül - Admin Dashboard
 * Modern & Bold Design
 */
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.seo-dashboard {
    --accent: #00f5d4;
    --accent-dim: rgba(0, 245, 212, 0.1);
    --accent-glow: rgba(0, 245, 212, 0.4);
    --warning: #fee440;
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
html.dark .seo-dashboard,
.dark .seo-dashboard {
    --surface: #0d1117;
    --surface-2: #161b22;
    --surface-3: #21262d;
    --border: #30363d;
    --text: #e6edf3;
    --text-dim: #7d8590;
}

.seo-dashboard * {
    box-sizing: border-box;
}

/* Hero Section */
.seo-hero {
    position: relative;
    background: var(--surface);
    border-radius: 16px;
    padding: 32px 24px;
    margin-bottom: 24px;
    overflow: hidden;
    border: 1px solid var(--border);
}

@media (min-width: 768px) {
    .seo-hero {
        border-radius: 24px;
        padding: 48px;
        margin-bottom: 32px;
    }
}

.seo-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
    opacity: 0.3;
    animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.1); }
}

.seo-hero-content {
    position: relative;
    z-index: 1;
}

.seo-hero h1 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text);
    margin: 0 0 8px;
    letter-spacing: -0.03em;
    line-height: 1.2;
}

@media (min-width: 768px) {
    .seo-hero h1 {
        font-size: 3rem;
    }
}

.seo-hero h1 span {
    background: linear-gradient(135deg, var(--accent), #9b5de5);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.seo-hero p {
    color: var(--text-dim);
    font-size: 0.9375rem;
    margin: 0;
    line-height: 1.5;
}

@media (min-width: 768px) {
    .seo-hero p {
        font-size: 1.125rem;
    }
}

/* Stats Grid */
.seo-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

@media (max-width: 1200px) {
    .seo-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .seo-stats { grid-template-columns: 1fr; }
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (min-width: 768px) {
    .stat-card {
        border-radius: 16px;
        padding: 24px;
    }
}

.stat-card:hover {
    transform: translateY(-4px);
    border-color: var(--accent);
    box-shadow: 0 20px 40px -20px var(--accent-glow);
}

.stat-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), transparent);
    opacity: 0;
    transition: opacity 0.3s;
}

.stat-card:hover::after {
    opacity: 1;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text);
    line-height: 1;
    margin-bottom: 4px;
    font-variant-numeric: tabular-nums;
}

@media (min-width: 768px) {
    .stat-number {
        font-size: 2.5rem;
    }
}

.stat-label {
    color: var(--text-dim);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
}

.stat-sub {
    color: var(--accent);
    font-size: 0.8125rem;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-icon {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    background: var(--surface-3);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: 1.25rem;
}

@media (min-width: 768px) {
    .stat-icon {
        top: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        font-size: 1.5rem;
    }
}

/* Quick Actions */
.seo-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

@media (max-width: 1024px) {
    .seo-actions { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
    .seo-actions { grid-template-columns: 1fr; }
}

.action-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-card:hover {
    border-color: transparent;
    transform: translateY(-6px) scale(1.02);
}

.action-card.sitemap { --card-accent: #00f5d4; }
.action-card.robots { --card-accent: #7b61ff; }
.action-card.meta { --card-accent: #ff6b6b; }
.action-card.redirects { --card-accent: #ffd93d; }
.action-card.schema { --card-accent: #6bcb77; }
.action-card.view { --card-accent: #4cc9f0; }

.action-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--card-accent), transparent 60%);
    opacity: 0;
    transition: opacity 0.4s;
}

.action-card:hover::before {
    opacity: 0.08;
}

.action-card::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, var(--card-accent), transparent 50%);
    border-radius: 22px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s;
}

.action-card:hover::after {
    opacity: 1;
}

.action-icon {
    width: 56px;
    height: 56px;
    background: var(--surface-3);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: var(--card-accent);
    transition: all 0.3s;
    position: relative;
    z-index: 1;
}

.action-card:hover .action-icon {
    background: var(--card-accent);
    color: var(--surface);
    transform: rotate(-8deg) scale(1.1);
}

.action-content {
    position: relative;
    z-index: 1;
}

.action-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 4px;
}

.action-desc {
    color: var(--text-dim);
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.5;
}

.action-arrow {
    position: absolute;
    bottom: 24px;
    right: 24px;
    width: 36px;
    height: 36px;
    background: var(--surface-3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
    transition: all 0.3s;
}

.action-card:hover .action-arrow {
    background: var(--card-accent);
    color: var(--surface);
    transform: translateX(4px);
}

/* Status Panel */
.seo-status {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
}

.status-header {
    padding: 24px 28px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.status-header h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-header h2 .material-symbols-outlined {
    color: var(--accent);
}

.status-badge {
    background: var(--accent-dim);
    color: var(--accent);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8125rem;
    font-weight: 600;
}

.status-list {
    padding: 8px;
}

.status-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-radius: 12px;
    transition: background 0.2s;
}

.status-item:hover {
    background: var(--surface-2);
}

.status-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    position: relative;
}

.status-indicator.active {
    background: var(--accent);
    box-shadow: 0 0 12px var(--accent);
}

.status-indicator.active::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid var(--accent);
    opacity: 0.3;
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.status-indicator.inactive {
    background: var(--danger);
}

.status-name {
    color: var(--text);
    font-weight: 500;
    font-size: 0.9375rem;
}

.status-value {
    color: var(--text-dim);
    font-size: 0.875rem;
    background: var(--surface-3);
    padding: 6px 12px;
    border-radius: 8px;
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

/* Responsive */
@media (max-width: 640px) {
    .seo-hero {
        padding: 24px 16px;
        margin-bottom: 20px;
        border-radius: 12px;
    }
    
    .seo-hero h1 {
        font-size: 1.5rem;
        margin-bottom: 6px;
    }
    
    .seo-hero p {
        font-size: 0.875rem;
    }
    
    .stat-card {
        padding: 14px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .stat-icon {
        width: 32px;
        height: 32px;
        top: 10px;
        right: 10px;
        font-size: 1.125rem;
        border-radius: 6px;
    }
    
    .stat-sub {
        font-size: 0.75rem;
        margin-top: 6px;
    }
    
    .action-card {
        padding: 16px;
    }
    
    .action-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .action-title {
        font-size: 1rem;
    }
    
    .action-desc {
        font-size: 0.8125rem;
    }
    
    .action-arrow {
        width: 32px;
        height: 32px;
        bottom: 16px;
        right: 16px;
    }
    
    .status-header {
        padding: 16px;
        flex-wrap: wrap;
    }
    
    .status-header h2 {
        font-size: 1.125rem;
    }
    
    .status-item {
        padding: 12px 16px;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .status-value {
        align-self: flex-end;
    }
}
</style>

<div class="seo-dashboard">
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

    <!-- Hero -->
    <div class="seo-hero">
        <div class="seo-hero-content">
            <h1>SEO <span>Kontrol Merkezi</span></h1>
            <p>Sitenizin arama motoru görünürlüğünü optimize edin</p>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="seo-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">article</span>
            </div>
            <div class="stat-number"><?php echo number_format($stats['posts_count']); ?></div>
            <div class="stat-label">Sitemap Yazısı</div>
            <div class="stat-sub">
                <span class="material-symbols-outlined" style="font-size: 16px;">trending_up</span>
                İndeksleniyor
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">folder</span>
            </div>
            <div class="stat-number"><?php echo number_format($stats['categories_count']); ?></div>
            <div class="stat-label">Kategori</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">label</span>
            </div>
            <div class="stat-number"><?php echo number_format($stats['tags_count']); ?></div>
            <div class="stat-label">Etiket</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">redo</span>
            </div>
            <div class="stat-number"><?php echo number_format($redirectStats['total']); ?></div>
            <div class="stat-label">Yönlendirme</div>
            <div class="stat-sub">
                <?php echo number_format($redirectStats['total_hits']); ?> toplam hit
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="seo-actions">
        <a href="<?php echo admin_url('module/seo/sitemap'); ?>" class="action-card sitemap">
            <div class="action-icon">
                <span class="material-symbols-outlined">account_tree</span>
            </div>
            <div class="action-content">
                <h3 class="action-title">Sitemap</h3>
                <p class="action-desc">XML sitemap yapılandırması ve içerik ayarları</p>
            </div>
            <div class="action-arrow">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/robots'); ?>" class="action-card robots">
            <div class="action-icon">
                <span class="material-symbols-outlined">smart_toy</span>
            </div>
            <div class="action-content">
                <h3 class="action-title">Robots.txt</h3>
                <p class="action-desc">Arama motoru botları için erişim kuralları</p>
            </div>
            <div class="action-arrow">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/meta'); ?>" class="action-card meta">
            <div class="action-icon">
                <span class="material-symbols-outlined">code</span>
            </div>
            <div class="action-content">
                <h3 class="action-title">Meta Taglar</h3>
                <p class="action-desc">Title ve description şablonları</p>
            </div>
            <div class="action-arrow">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/redirects'); ?>" class="action-card redirects">
            <div class="action-icon">
                <span class="material-symbols-outlined">redo</span>
            </div>
            <div class="action-content">
                <h3 class="action-title">Yönlendirmeler</h3>
                <p class="action-desc">301 ve 302 URL yönlendirmeleri</p>
            </div>
            <div class="action-arrow">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/schema'); ?>" class="action-card schema">
            <div class="action-icon">
                <span class="material-symbols-outlined">data_object</span>
            </div>
            <div class="action-content">
                <h3 class="action-title">Schema.org</h3>
                <p class="action-desc">Yapılandırılmış veri ve JSON-LD</p>
            </div>
            <div class="action-arrow">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
        
        <a href="/sitemap.xml" target="_blank" class="action-card view">
            <div class="action-icon">
                <span class="material-symbols-outlined">open_in_new</span>
            </div>
            <div class="action-content">
                <h3 class="action-title">Sitemap Önizle</h3>
                <p class="action-desc">/sitemap.xml dosyasını görüntüle</p>
            </div>
            <div class="action-arrow">
                <span class="material-symbols-outlined">arrow_forward</span>
            </div>
        </a>
    </div>
    
    <!-- Status Panel -->
    <div class="seo-status">
        <div class="status-header">
            <h2>
                <span class="material-symbols-outlined">monitoring</span>
                Modül Durumu
            </h2>
            <span class="status-badge">Aktif</span>
        </div>
        <div class="status-list">
            <div class="status-item">
                <div class="status-left">
                    <div class="status-indicator <?php echo ($settings['sitemap_enabled'] ?? true) ? 'active' : 'inactive'; ?>"></div>
                    <span class="status-name">Sitemap XML</span>
                </div>
                <span class="status-value"><?php echo ($settings['sitemap_enabled'] ?? true) ? 'Etkin' : 'Devre Dışı'; ?></span>
            </div>
            
            <div class="status-item">
                <div class="status-left">
                    <div class="status-indicator <?php echo ($settings['robots_enabled'] ?? true) ? 'active' : 'inactive'; ?>"></div>
                    <span class="status-name">Robots.txt</span>
                </div>
                <span class="status-value"><?php echo ($settings['robots_enabled'] ?? true) ? 'Etkin' : 'Devre Dışı'; ?></span>
            </div>
            
            <div class="status-item">
                <div class="status-left">
                    <div class="status-indicator <?php echo ($settings['schema_enabled'] ?? true) ? 'active' : 'inactive'; ?>"></div>
                    <span class="status-name">Schema.org (JSON-LD)</span>
                </div>
                <span class="status-value"><?php echo ($settings['schema_enabled'] ?? true) ? 'Etkin' : 'Devre Dışı'; ?></span>
            </div>
            
            <div class="status-item">
                <div class="status-left">
                    <div class="status-indicator <?php echo ($settings['redirects_enabled'] ?? true) ? 'active' : 'inactive'; ?>"></div>
                    <span class="status-name">URL Yönlendirmeleri</span>
                </div>
                <span class="status-value">
                    <?php echo ($settings['redirects_enabled'] ?? true) ? $redirectStats['active'] . ' aktif kural' : 'Devre Dışı'; ?>
                </span>
            </div>
        </div>
    </div>
</div>
