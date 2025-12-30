<?php
$title = $section['title'] ?? 'Projenizi Hayata Geçirelim';
$subtitle = $section['subtitle'] ?? 'Hemen iletişime geçin ve size özel çözümlerimizi keşfedin.';
$settings = $section['settings'] ?? [];
$buttonText = $settings['button_text'] ?? 'Bize Ulaşın';
$buttonLink = $settings['button_link'] ?? '/contact';
$formId = $settings['form_id'] ?? null;
$showForm = $settings['show_form'] ?? false;

// Form varsa getir
$form = null;
if ($formId && function_exists('get_form_by_id')) {
    $form = get_form_by_id($formId);
}
?>

<section class="py-24 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 gradient-primary"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
    
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
            <?php echo htmlspecialchars($title); ?>
        </h2>
        <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
            <?php echo htmlspecialchars($subtitle); ?>
        </p>
        
        <?php if ($showForm && $form): ?>
            <!-- Form Gösterimi -->
            <div class="cta-form-wrapper bg-white/95 backdrop-blur-sm rounded-2xl p-8 lg:p-10 max-w-xl mx-auto shadow-xl">
                <?php 
                if (function_exists('cms_form_by_id')) {
                    echo cms_form_by_id($formId);
                } elseif (function_exists('the_form_by_id')) {
                    the_form_by_id($formId);
                }
                ?>
            </div>
        <?php else: ?>
            <!-- Buton Gösterimi -->
            <a href="<?php echo htmlspecialchars($buttonLink); ?>" class="inline-flex items-center gap-2 bg-white text-primary px-10 py-4 rounded-xl font-semibold text-lg hover:bg-gray-100 transition-all hover:scale-105 shadow-xl">
                <?php echo htmlspecialchars($buttonText); ?>
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        <?php endif; ?>
    </div>
</section>

<style>
/* CTA Form Styles - Minimal & Clean */
.cta-form-wrapper .cms-form {
    color: #374151;
}

.cta-form-wrapper .cms-form .form-group {
    margin-bottom: 1.25rem;
}

.cta-form-wrapper .cms-form label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-align: center;
}

.cta-form-wrapper .cms-form input,
.cta-form-wrapper .cms-form textarea,
.cta-form-wrapper .cms-form select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.9375rem;
    transition: all 0.2s;
    background: #ffffff;
    color: #111827;
}

.cta-form-wrapper .cms-form input:focus,
.cta-form-wrapper .cms-form textarea:focus,
.cta-form-wrapper .cms-form select:focus {
    border-color: var(--color-primary, #137fec);
    outline: none;
    box-shadow: 0 0 0 3px rgba(19, 127, 236, 0.1);
}

.cta-form-wrapper .cms-form input::placeholder,
.cta-form-wrapper .cms-form textarea::placeholder {
    color: #9ca3af;
}

.cta-form-wrapper .cms-form textarea {
    min-height: 100px;
    resize: vertical;
}

.cta-form-wrapper .cms-form button[type="submit"] {
    width: 100%;
    padding: 0.875rem 1.5rem;
    background: var(--color-primary, #137fec);
    color: #ffffff;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.5rem;
}

.cta-form-wrapper .cms-form button[type="submit"]:hover {
    background: var(--color-primary, #0d6efd);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(19, 127, 236, 0.3);
}

.cta-form-wrapper .cms-form button[type="submit"]:active {
    transform: translateY(0);
}

.cta-form-wrapper .cms-form .form-description {
    text-align: center;
    color: #6b7280;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.cta-form-wrapper .cms-form .required-field::after {
    content: ' *';
    color: #ef4444;
}

/* Form başarı/hata mesajları */
.cta-form-wrapper .cms-form .form-success,
.cta-form-wrapper .cms-form .form-error {
    padding: 0.875rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    text-align: center;
    font-size: 0.875rem;
}

.cta-form-wrapper .cms-form .form-success {
    background: #d1fae5;
    color: #065f46;
}

.cta-form-wrapper .cms-form .form-error {
    background: #fee2e2;
    color: #991b1b;
}
</style>
