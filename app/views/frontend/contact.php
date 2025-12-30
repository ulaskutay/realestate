<?php
/**
 * İletişim Sayfası
 * Şirket bilgileri ve sosyal medya linkleri panelden otomatik çekilir
 */

// Site ve şirket bilgileri
$siteName = get_option('site_name', 'Site Adı');
$companyName = get_option('company_name', $siteName);
$companyEmail = get_option('company_email', get_option('contact_email', ''));
$companyPhone = get_option('company_phone', get_option('contact_phone', ''));
$companyAddress = get_option('company_address', get_option('contact_address', ''));
$companyCity = get_option('company_city', '');

// Sosyal medya linkleri
$socialLinks = [
    'facebook' => ['url' => get_option('social_facebook', ''), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook', 'color' => '#1877f2'],
    'instagram' => ['url' => get_option('social_instagram', ''), 'icon' => 'fab fa-instagram', 'label' => 'Instagram', 'color' => '#e4405f'],
    'twitter' => ['url' => get_option('social_twitter', ''), 'icon' => 'fab fa-x-twitter', 'label' => 'X (Twitter)', 'color' => '#000000'],
    'linkedin' => ['url' => get_option('social_linkedin', ''), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn', 'color' => '#0a66c2'],
    'youtube' => ['url' => get_option('social_youtube', ''), 'icon' => 'fab fa-youtube', 'label' => 'YouTube', 'color' => '#ff0000'],
    'tiktok' => ['url' => get_option('social_tiktok', ''), 'icon' => 'fab fa-tiktok', 'label' => 'TikTok', 'color' => '#000000'],
    'pinterest' => ['url' => get_option('social_pinterest', ''), 'icon' => 'fab fa-pinterest-p', 'label' => 'Pinterest', 'color' => '#bd081c'],
];

// Aktif sosyal medya linklerini filtrele
$activeSocials = array_filter($socialLinks, fn($s) => !empty($s['url']));

// Google Maps embed URL (opsiyonel)
$mapEmbed = get_option('google_maps_embed', '');
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="contact-page">
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="hero-overlay"></div>
        <div class="hero-animation">
            <div class="animated-gradient"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge" data-aos="fade-down">
                    <div class="badge-glow"></div>
                    <i class="fas fa-paper-plane"></i>
                    <span>İletişim</span>
                </div>
                <h1 data-aos="fade-up" data-aos-delay="100">Bizimle İletişime Geçin</h1>
                <p data-aos="fade-up" data-aos-delay="200">Sorularınız, önerileriniz veya işbirliği teklifleriniz için bize ulaşabilirsiniz. En kısa sürede size dönüş yapacağız.</p>
                
                <!-- Quick Stats -->
                <div class="hero-stats" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <span>24 Saat İçinde Yanıt</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <i class="fas fa-headset"></i>
                        <span>7/24 Destek</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <i class="fas fa-shield-check"></i>
                        <span>Güvenli İletişim</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
            <div class="decoration-dots"></div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="contact-main">
        <div class="container">
            <div class="contact-grid">
                
                <!-- İletişim Formu -->
                <div class="contact-form-wrapper" data-aos="fade-right">
                    <div class="form-card">
                        <div class="form-header">
                            <div class="form-icon">
                                <i class="fas fa-message"></i>
                            </div>
                            <h2>Mesaj Gönderin</h2>
                            <p>Formu doldurun, 24 saat içinde yanıt verelim.</p>
                        </div>
                        
                        <?php the_form('iletisim'); ?>
                    </div>
                </div>

                <!-- İletişim Bilgileri -->
                <div class="contact-info-wrapper" data-aos="fade-left">
                    
                    <!-- İletişim Kartları -->
                    <div class="info-cards">
                        
                        <?php if ($companyEmail): ?>
                        <a href="mailto:<?php echo esc_attr($companyEmail); ?>" class="info-card info-card-hover" data-aos="fade-up">
                            <div class="card-icon email-icon">
                                <i class="fas fa-envelope"></i>
                                <div class="icon-glow"></div>
                            </div>
                            <div class="card-content">
                                <span class="card-label">E-posta Adresimiz</span>
                                <span class="card-value"><?php echo esc_html($companyEmail); ?></span>
                                <span class="card-hint">Bize e-posta gönderin</span>
                            </div>
                            <div class="card-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>
                        <?php endif; ?>

                        <?php if ($companyPhone): ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" class="info-card info-card-hover" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-icon phone-icon">
                                <i class="fas fa-phone"></i>
                                <div class="icon-glow"></div>
                            </div>
                            <div class="card-content">
                                <span class="card-label">Telefon Numaramız</span>
                                <span class="card-value"><?php echo esc_html($companyPhone); ?></span>
                                <span class="card-hint">Bizi hemen arayın</span>
                            </div>
                            <div class="card-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>
                        <?php endif; ?>

                        <?php if ($companyAddress): ?>
                        <div class="info-card address-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-icon address-icon">
                                <i class="fas fa-location-dot"></i>
                                <div class="icon-glow"></div>
                            </div>
                            <div class="card-content">
                                <span class="card-label">Ofis Adresimiz</span>
                                <span class="card-value"><?php echo esc_html($companyAddress); ?></span>
                                <?php if ($companyCity): ?>
                                <span class="card-city"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($companyCity); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Sosyal Medya -->
                    <?php if (!empty($activeSocials)): ?>
                    <div class="social-section" data-aos="fade-up">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-share-nodes"></i>
                            </div>
                            <div>
                                <h3>Sosyal Medya</h3>
                                <p>Bizi sosyal medyada takip edin</p>
                            </div>
                        </div>
                        <div class="social-links">
                            <?php foreach ($activeSocials as $key => $social): ?>
                            <a href="<?php echo esc_url($social['url']); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="social-link social-<?php echo $key; ?>"
                               title="<?php echo esc_attr($social['label']); ?>"
                               data-tooltip="<?php echo esc_attr($social['label']); ?>"
                               style="--social-color: <?php echo $social['color']; ?>">
                                <i class="<?php echo $social['icon']; ?>"></i>
                                <span class="social-tooltip"><?php echo esc_html($social['label']); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Çalışma Saatleri -->
                    <div class="hours-section" data-aos="fade-up">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h3>Çalışma Saatleri</h3>
                                <p>Müsaitlik durumumuz</p>
                            </div>
                        </div>
                        <div class="hours-list">
                            <div class="hours-item">
                                <div class="day-info">
                                    <i class="fas fa-briefcase"></i>
                                    <span class="day">Pazartesi - Cuma</span>
                                </div>
                                <span class="time active">09:00 - 18:00</span>
                            </div>
                            <div class="hours-item">
                                <div class="day-info">
                                    <i class="fas fa-calendar-day"></i>
                                    <span class="day">Cumartesi</span>
                                </div>
                                <span class="time active">10:00 - 14:00</span>
                            </div>
                            <div class="hours-item weekend">
                                <div class="day-info">
                                    <i class="fas fa-calendar-xmark"></i>
                                    <span class="day">Pazar</span>
                                </div>
                                <span class="time closed">Kapalı</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Harita Section (Opsiyonel) -->
    <?php if ($mapEmbed): ?>
    <section class="map-section">
        <div class="map-wrapper">
            <?php echo $mapEmbed; ?>
        </div>
    </section>
    <?php elseif ($companyAddress): ?>
    <section class="map-section">
        <div class="map-wrapper">
            <iframe 
                src="https://maps.google.com/maps?q=<?php echo urlencode($companyAddress . ' ' . $companyCity); ?>&output=embed"
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>
    <?php endif; ?>

</div>

<style>
/* ================================
   Premium Contact Page Styles
   ================================ */

/* AOS Library for Animations */
@import url('https://unpkg.com/aos@2.3.1/dist/aos.css');

:root {
    --contact-primary: #6366f1;
    --contact-primary-dark: #4f46e5;
    --contact-secondary: #10b981;
    --contact-accent: #f59e0b;
    --contact-danger: #ef4444;
    --contact-dark: #0f172a;
    --contact-gray: #64748b;
    --contact-light: #f1f5f9;
    --contact-white: #ffffff;
    --contact-gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --contact-gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --contact-gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --contact-gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.contact-page {
    background: var(--contact-light);
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ========== HERO SECTION ========== */
.contact-hero {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    padding: 6rem 0 10rem;
    overflow: hidden;
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-animation {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.animated-gradient {
    position: absolute;
    inset: -50%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    animation: gradientShift 15s ease-in-out infinite;
}

@keyframes gradientShift {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(10%, 10%) scale(1.1); }
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.hero-badge {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    padding: 0.75rem 1.5rem;
    border-radius: 100px;
    font-size: 0.875rem;
    font-weight: 600;
    color: white;
    margin-bottom: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.hero-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.badge-glow {
    position: absolute;
    inset: -2px;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    border-radius: 100px;
    opacity: 0;
    animation: badgeGlow 3s ease-in-out infinite;
}

@keyframes badgeGlow {
    0%, 100% { opacity: 0; transform: translateX(-100%); }
    50% { opacity: 1; transform: translateX(100%); }
}

.contact-hero h1 {
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 800;
    color: white;
    margin: 0 0 1.5rem;
    line-height: 1.1;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.contact-hero p {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.95);
    line-height: 1.7;
    margin: 0 0 3rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.hero-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: white;
    font-size: 0.95rem;
    font-weight: 500;
}

.stat-item i {
    font-size: 1.25rem;
    opacity: 0.9;
}

.stat-divider {
    width: 1px;
    height: 24px;
    background: rgba(255, 255, 255, 0.3);
}

.hero-decoration {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.decoration-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    animation: float 20s ease-in-out infinite;
}

.circle-1 {
    width: 500px;
    height: 500px;
    top: -250px;
    right: -100px;
    animation-delay: 0s;
}

.circle-2 {
    width: 350px;
    height: 350px;
    bottom: -175px;
    left: -80px;
    animation-delay: 2s;
}

.circle-3 {
    width: 200px;
    height: 200px;
    top: 50%;
    left: 15%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -30px) rotate(120deg); }
    66% { transform: translate(-20px, 20px) rotate(240deg); }
}

.decoration-dots {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 30px 30px;
    animation: dotsMove 30s linear infinite;
}

@keyframes dotsMove {
    0% { background-position: 0 0; }
    100% { background-position: 30px 30px; }
}

/* ========== MAIN CONTENT ========== */
.contact-main {
    position: relative;
    margin-top: -5rem;
    padding-bottom: 6rem;
    z-index: 10;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}

/* ========== FORM CARD ========== */
.form-card {
    background: var(--contact-white);
    border-radius: 2rem;
    padding: 3rem;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.08),
        0 0 0 1px rgba(0, 0, 0, 0.02);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-card:hover {
    box-shadow: 
        0 30px 80px rgba(0, 0, 0, 0.12),
        0 0 0 1px rgba(102, 126, 234, 0.1);
    transform: translateY(-4px);
}

.form-header {
    margin-bottom: 2.5rem;
    text-align: center;
}

.form-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 1.5rem;
    background: var(--contact-gradient-primary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.3);
    animation: iconPulse 3s ease-in-out infinite;
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); box-shadow: 0 16px 40px rgba(102, 126, 234, 0.4); }
}

.form-header h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--contact-dark);
    margin: 0 0 0.75rem;
}

.form-header p {
    color: var(--contact-gray);
    font-size: 1rem;
    margin: 0;
}

/* Form Styling */
.form-card .cms-form .form-group {
    margin-bottom: 1.5rem;
}

.form-card .cms-form label {
    display: block;
    font-weight: 600;
    color: var(--contact-dark);
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    letter-spacing: 0.02em;
}

.form-card .cms-form input,
.form-card .cms-form textarea,
.form-card .cms-form select {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--contact-white);
    color: var(--contact-dark);
    font-family: inherit;
}

.form-card .cms-form input::placeholder,
.form-card .cms-form textarea::placeholder {
    color: #94a3b8;
}

.form-card .cms-form input:focus,
.form-card .cms-form textarea:focus,
.form-card .cms-form select:focus {
    border-color: var(--contact-primary);
    box-shadow: 
        0 0 0 4px rgba(99, 102, 241, 0.1),
        0 4px 12px rgba(99, 102, 241, 0.15);
    outline: none;
    transform: translateY(-2px);
}

.form-card .cms-form textarea {
    min-height: 140px;
    resize: vertical;
}

.form-card .cms-form button[type="submit"] {
    width: 100%;
    padding: 1.125rem 2rem;
    background: var(--contact-gradient-primary);
    color: white;
    border: none;
    border-radius: 1rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.form-card .cms-form button[type="submit"]::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.form-card .cms-form button[type="submit"]:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(102, 126, 234, 0.4);
}

.form-card .cms-form button[type="submit"]:hover::before {
    opacity: 1;
}

.form-card .cms-form button[type="submit"]:active {
    transform: translateY(-1px);
}

/* ========== INFO CARDS ========== */
.info-cards {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.info-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: var(--contact-white);
    padding: 1.5rem 1.75rem;
    border-radius: 1.5rem;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 8px 24px rgba(0, 0, 0, 0.06),
        0 0 0 1px rgba(0, 0, 0, 0.02);
    overflow: hidden;
}

.info-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--contact-gradient-primary);
    transform: scaleY(0);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.info-card-hover:hover {
    transform: translateX(8px);
    box-shadow: 
        0 16px 40px rgba(0, 0, 0, 0.1),
        0 0 0 1px rgba(102, 126, 234, 0.1);
}

.info-card-hover:hover::before {
    transform: scaleY(1);
}

.card-icon {
    position: relative;
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.375rem;
    flex-shrink: 0;
    transition: all 0.4s ease;
}

.icon-glow {
    position: absolute;
    inset: -3px;
    border-radius: 18px;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.info-card-hover:hover .card-icon {
    transform: scale(1.1) rotate(5deg);
}

.info-card-hover:hover .icon-glow {
    opacity: 1;
    animation: iconGlowAnim 2s ease-in-out infinite;
}

@keyframes iconGlowAnim {
    0%, 100% { box-shadow: 0 0 20px currentColor; }
    50% { box-shadow: 0 0 30px currentColor, 0 0 40px currentColor; }
}

.email-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.phone-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.address-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.card-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--contact-gray);
}

.card-value {
    font-size: 1.0625rem;
    font-weight: 600;
    color: var(--contact-dark);
    line-height: 1.4;
}

.card-hint {
    font-size: 0.8125rem;
    color: #94a3b8;
    font-weight: 400;
}

.card-city {
    font-size: 0.875rem;
    color: var(--contact-gray);
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.card-city i {
    font-size: 0.75rem;
}

.card-arrow {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--contact-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--contact-gray);
    transition: all 0.4s ease;
    flex-shrink: 0;
}

.info-card-hover:hover .card-arrow {
    background: var(--contact-gradient-primary);
    color: white;
    transform: translateX(4px);
}

/* ========== SOCIAL SECTION ========== */
.social-section {
    background: var(--contact-white);
    padding: 2rem;
    border-radius: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 
        0 8px 24px rgba(0, 0, 0, 0.06),
        0 0 0 1px rgba(0, 0, 0, 0.02);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.section-icon {
    width: 48px;
    height: 48px;
    background: var(--contact-gradient-primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.section-header h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--contact-dark);
    margin: 0 0 0.25rem;
}

.section-header p {
    font-size: 0.875rem;
    color: var(--contact-gray);
    margin: 0;
}

.social-links {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.social-link {
    position: relative;
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--contact-gray);
    background: var(--contact-light);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
}

.social-link:hover {
    color: white;
    background: var(--social-color);
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
}

.social-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-8px);
    background: var(--contact-dark);
    color: white;
    padding: 0.5rem 0.875rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s ease;
}

.social-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: var(--contact-dark);
}

.social-link:hover .social-tooltip {
    opacity: 1;
    transform: translateX(-50%) translateY(-12px);
}

/* ========== HOURS SECTION ========== */
.hours-section {
    background: var(--contact-white);
    padding: 2rem;
    border-radius: 1.5rem;
    box-shadow: 
        0 8px 24px rgba(0, 0, 0, 0.06),
        0 0 0 1px rgba(0, 0, 0, 0.02);
}

.hours-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: var(--contact-light);
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.hours-item:hover {
    background: #e2e8f0;
    transform: translateX(4px);
}

.day-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.day-info i {
    font-size: 1rem;
    color: var(--contact-primary);
    width: 20px;
    text-align: center;
}

.hours-item .day {
    font-weight: 600;
    color: var(--contact-dark);
    font-size: 0.9375rem;
}

.hours-item .time {
    font-weight: 600;
    font-size: 0.9375rem;
    padding: 0.375rem 0.875rem;
    border-radius: 8px;
    background: rgba(16, 185, 129, 0.1);
    color: var(--contact-secondary);
}

.hours-item .time.closed {
    background: rgba(239, 68, 68, 0.1);
    color: var(--contact-danger);
}

.hours-item.weekend {
    opacity: 0.8;
}

.hours-item.weekend .day-info i {
    color: var(--contact-danger);
}

/* ========== MAP SECTION ========== */
.map-section {
    margin-top: 3rem;
    background: var(--contact-dark);
}

.map-wrapper {
    height: 500px;
    width: 100%;
    border-radius: 2rem;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.map-wrapper iframe {
    display: block;
    width: 100%;
    height: 100%;
    filter: grayscale(30%) contrast(1.1);
    transition: filter 0.3s ease;
}

.map-wrapper:hover iframe {
    filter: grayscale(0%) contrast(1);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1024px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-info-wrapper {
        order: -1;
    }
}

@media (max-width: 768px) {
    .contact-hero {
        padding: 4rem 0 8rem;
    }
    
    .hero-stats {
        gap: 1rem;
    }
    
    .stat-divider {
        display: none;
    }
    
    .form-card {
        padding: 2rem 1.5rem;
    }
    
    .form-icon {
        width: 60px;
        height: 60px;
        font-size: 1.75rem;
    }
    
    .info-card {
        padding: 1.25rem;
    }
    
    .card-icon {
        width: 48px;
        height: 48px;
        font-size: 1.125rem;
    }
    
    .card-arrow {
        width: 32px;
        height: 32px;
    }
    
    .map-wrapper {
        height: 350px;
        border-radius: 1.5rem;
    }
}

@media (max-width: 480px) {
    .contact-hero {
        padding: 3rem 0 6rem;
    }
    
    .contact-hero h1 {
        font-size: 2rem;
    }
    
    .contact-hero p {
        font-size: 1rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .form-card {
        padding: 1.5rem;
    }
    
    .social-links {
        justify-content: center;
    }
}

/* ========== AOS Animation Overrides ========== */
[data-aos] {
    pointer-events: auto;
}

[data-aos="fade-up"] {
    transform: translateY(30px);
    opacity: 0;
    transition: transform 0.6s ease, opacity 0.6s ease;
}

[data-aos="fade-up"].aos-animate {
    transform: translateY(0);
    opacity: 1;
}

[data-aos="fade-right"] {
    transform: translateX(-30px);
    opacity: 0;
    transition: transform 0.6s ease, opacity 0.6s ease;
}

[data-aos="fade-right"].aos-animate {
    transform: translateX(0);
    opacity: 1;
}

[data-aos="fade-left"] {
    transform: translateX(30px);
    opacity: 0;
    transition: transform 0.6s ease, opacity 0.6s ease;
}

[data-aos="fade-left"].aos-animate {
    transform: translateX(0);
    opacity: 1;
}

[data-aos="fade-down"] {
    transform: translateY(-30px);
    opacity: 0;
    transition: transform 0.6s ease, opacity 0.6s ease;
}

[data-aos="fade-down"].aos-animate {
    transform: translateY(0);
    opacity: 1;
}
</style>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 100
        });
    }
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Form validation enhancement
    const form = document.querySelector('.cms-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gönderiliyor...';
                submitBtn.disabled = true;
            }
        });
    }
});
</script>
