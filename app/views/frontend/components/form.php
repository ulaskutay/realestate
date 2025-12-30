<?php
/**
 * Frontend Form Component
 * Formları frontend'de render eder
 * 
 * Kullanım: render_form('form-slug') veya render_form_by_id(1)
 */

/**
 * Form'u slug'a göre render eder
 */
if (!function_exists('render_form')) {
function render_form($slug) {
    require_once __DIR__ . '/../../../models/Form.php';
    require_once __DIR__ . '/../../../models/FormField.php';
    
    $formModel = new Form();
    $form = $formModel->findBySlugWithFields($slug);
    
    if (!$form || $form['status'] !== 'active') {
        return '<p class="form-error">Form bulunamadı veya aktif değil.</p>';
    }
    
    return render_form_html($form);
}
}

/**
 * Form'u ID'ye göre render eder
 */
if (!function_exists('render_form_by_id')) {
function render_form_by_id($id) {
    require_once __DIR__ . '/../../../models/Form.php';
    require_once __DIR__ . '/../../../models/FormField.php';
    
    $formModel = new Form();
    $form = $formModel->findWithFields($id);
    
    if (!$form || $form['status'] !== 'active') {
        return '<p class="form-error">Form bulunamadı veya aktif değil.</p>';
    }
    
    return render_form_html($form);
}
}

/**
 * Form HTML'ini oluşturur
 */
if (!function_exists('render_form_html')) {
function render_form_html($form) {
    $styleClass = 'form-style-' . ($form['form_style'] ?? 'default');
    $layoutClass = 'form-layout-' . ($form['layout'] ?? 'vertical');
    $formId = 'cms-form-' . $form['id'];
    
    ob_start();
    ?>
    <div class="cms-form-wrapper" id="<?php echo esc_attr($formId); ?>-wrapper">
        <!-- Form açıklaması contact.php'de gösteriliyor, burada göstermiyoruz -->
        
        <form id="<?php echo esc_attr($formId); ?>" 
              class="cms-form <?php echo esc_attr($styleClass); ?> <?php echo esc_attr($layoutClass); ?>" 
              method="POST" 
              action="/forms/submit"
              data-form-id="<?php echo esc_attr($form['id']); ?>">
            
            <input type="hidden" name="_form_id" value="<?php echo esc_attr($form['id']); ?>">
            
            <?php 
            // Honeypot spam koruması
            $honeypotEnabled = get_option('honeypot_enabled', 1); // Varsayılan aktif
            if ($honeypotEnabled): 
            ?>
            <!-- Honeypot alanı - Botları yakalamak için görünmez -->
            <input type="text" 
                   name="website_url" 
                   value="" 
                   tabindex="-1" 
                   autocomplete="off" 
                   style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;"
                   aria-hidden="true">
            <?php endif; ?>
            
            <div class="form-fields">
                <?php if (!empty($form['fields'])): ?>
                    <?php foreach ($form['fields'] as $field): ?>
                        <?php 
                        // Status kontrolü - hem status hem is_active kontrol et
                        $isActive = false;
                        if (isset($field['status'])) {
                            $isActive = ($field['status'] === 'active');
                        } elseif (isset($field['is_active'])) {
                            $isActive = ($field['is_active'] == 1 || $field['is_active'] === true);
                        } else {
                            // Varsayılan olarak aktif kabul et
                            $isActive = true;
                        }
                        if (!$isActive) continue; 
                        ?>
                        <?php render_form_field($field); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($form['fields'])): ?>
                <div class="form-submit">
                    <button type="submit" class="submit-button" style="background-color: <?php echo esc_attr($form['submit_button_color'] ?? '#137fec'); ?>">
                        <span class="button-text"><?php echo esc_html($form['submit_button_text'] ?? 'Gönder'); ?></span>
                        <span class="button-loading" style="display: none;">
                            <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" opacity="0.25"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            <?php endif; ?>
        </form>
        
        <div id="<?php echo esc_attr($formId); ?>-success" class="form-success" style="display: none;">
            <div class="success-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <p class="success-message"><?php echo esc_html($form['success_message'] ?? 'Formunuz başarıyla gönderildi!'); ?></p>
        </div>
        
        <div id="<?php echo esc_attr($formId); ?>-error" class="form-error-message" style="display: none;">
            <p></p>
        </div>
    </div>
    
    <script>
    (function() {
        const form = document.getElementById('<?php echo esc_attr($formId); ?>');
        const wrapper = document.getElementById('<?php echo esc_attr($formId); ?>-wrapper');
        const successEl = document.getElementById('<?php echo esc_attr($formId); ?>-success');
        const errorEl = document.getElementById('<?php echo esc_attr($formId); ?>-error');
        
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Loading state
                const submitBtn = form.querySelector('.submit-button');
                const btnText = submitBtn.querySelector('.button-text');
                const btnLoading = submitBtn.querySelector('.button-loading');
                
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
                
                // Önceki hataları temizle
                form.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
                form.querySelectorAll('.field-error-message').forEach(el => el.remove());
                errorEl.style.display = 'none';
                
                try {
                    const formData = new FormData(form);
                    
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    // Content-Type kontrolü
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('Non-JSON response:', text);
                        throw new Error('Sunucudan geçersiz yanıt alındı. Lütfen sayfayı yenileyip tekrar deneyin.');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Başarılı
                        form.style.display = 'none';
                        successEl.style.display = 'block';
                        
                        // Yönlendirme varsa
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        }
                    } else {
                        // Hata
                        if (data.errors) {
                            // Alan bazlı hatalar
                            Object.entries(data.errors).forEach(([fieldName, message]) => {
                                const field = form.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                                if (field) {
                                    const fieldWrapper = field.closest('.form-field');
                                    if (fieldWrapper) {
                                        fieldWrapper.classList.add('field-error');
                                        const errorMsg = document.createElement('div');
                                        errorMsg.className = 'field-error-message';
                                        errorMsg.textContent = message;
                                        fieldWrapper.appendChild(errorMsg);
                                    }
                                }
                            });
                        }
                        
                        // Genel hata mesajı
                        if (data.message) {
                            errorEl.querySelector('p').textContent = data.message;
                            errorEl.style.display = 'block';
                        }
                    }
                } catch (error) {
                    console.error('Form submission error:', error);
                    errorEl.querySelector('p').textContent = 'Form gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
                    errorEl.style.display = 'block';
                } finally {
                    // Loading state'i kaldır
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
}

/**
 * Tek bir form alanını render eder
 */
if (!function_exists('render_form_field')) {
function render_form_field($field) {
    $widthClass = 'field-width-' . ($field['width'] ?? 'full');
    $requiredClass = $field['required'] ? 'field-required' : '';
    $customClass = $field['css_class'] ?? '';
    
    // Layout elemanları
    if (in_array($field['type'], ['heading', 'paragraph', 'divider'])) {
        render_form_layout_element($field);
        return;
    }
    
    ?>
    <div class="form-field <?php echo esc_attr($widthClass); ?> <?php echo esc_attr($requiredClass); ?> <?php echo esc_attr($customClass); ?>" data-field-type="<?php echo esc_attr($field['type']); ?>">
        <?php if ($field['type'] !== 'hidden'): ?>
            <label class="field-label" for="field-<?php echo esc_attr($field['name']); ?>">
                <?php echo esc_html($field['label']); ?>
                <?php if ($field['required']): ?>
                    <span class="required-mark">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>
        
        <div class="field-input">
            <?php
            switch ($field['type']) {
                case 'text':
                case 'email':
                case 'phone':
                case 'number':
                case 'date':
                case 'time':
                case 'datetime':
                    $inputType = $field['type'];
                    if ($field['type'] === 'phone') $inputType = 'tel';
                    if ($field['type'] === 'datetime') $inputType = 'datetime-local';
                    ?>
                    <input type="<?php echo esc_attr($inputType); ?>" 
                           id="field-<?php echo esc_attr($field['name']); ?>"
                           name="<?php echo esc_attr($field['name']); ?>" 
                           placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                           value="<?php echo esc_attr($field['default_value'] ?? ''); ?>"
                           <?php echo $field['required'] ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'textarea':
                    ?>
                    <textarea id="field-<?php echo esc_attr($field['name']); ?>"
                              name="<?php echo esc_attr($field['name']); ?>" 
                              placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                              rows="4"
                              <?php echo $field['required'] ? 'required' : ''; ?>><?php echo esc_html($field['default_value'] ?? ''); ?></textarea>
                    <?php
                    break;
                    
                case 'select':
                    ?>
                    <select id="field-<?php echo esc_attr($field['name']); ?>"
                            name="<?php echo esc_attr($field['name']); ?>" 
                            <?php echo $field['required'] ? 'required' : ''; ?>>
                        <option value=""><?php echo esc_html($field['placeholder'] ?? 'Seçiniz...'); ?></option>
                        <?php if (!empty($field['options'])): ?>
                            <?php foreach ($field['options'] as $option): ?>
                                <option value="<?php echo esc_attr($option['value'] ?? $option); ?>" 
                                        <?php echo ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($option['label'] ?? $option); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php
                    break;
                    
                case 'checkbox':
                    ?>
                    <div class="checkbox-group">
                        <?php if (!empty($field['options'])): ?>
                            <?php foreach ($field['options'] as $i => $option): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           id="field-<?php echo esc_attr($field['name']); ?>-<?php echo $i; ?>"
                                           name="<?php echo esc_attr($field['name']); ?>[]" 
                                           value="<?php echo esc_attr($option['value'] ?? $option); ?>">
                                    <span><?php echo esc_html($option['label'] ?? $option); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;
                    
                case 'radio':
                    ?>
                    <div class="radio-group">
                        <?php if (!empty($field['options'])): ?>
                            <?php foreach ($field['options'] as $i => $option): ?>
                                <label class="radio-label">
                                    <input type="radio" 
                                           id="field-<?php echo esc_attr($field['name']); ?>-<?php echo $i; ?>"
                                           name="<?php echo esc_attr($field['name']); ?>" 
                                           value="<?php echo esc_attr($option['value'] ?? $option); ?>"
                                           <?php echo ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'checked' : ''; ?>>
                                    <span><?php echo esc_html($option['label'] ?? $option); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;
                    
                case 'file':
                    ?>
                    <input type="file" 
                           id="field-<?php echo esc_attr($field['name']); ?>"
                           name="<?php echo esc_attr($field['name']); ?>" 
                           <?php echo $field['required'] ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'hidden':
                    ?>
                    <input type="hidden" 
                           name="<?php echo esc_attr($field['name']); ?>" 
                           value="<?php echo esc_attr($field['default_value'] ?? ''); ?>">
                    <?php
                    break;
            }
            ?>
        </div>
        
        <?php if (!empty($field['help_text'])): ?>
            <div class="field-help"><?php echo esc_html($field['help_text']); ?></div>
        <?php endif; ?>
    </div>
    <?php
}
}

/**
 * Layout elemanlarını render eder
 */
if (!function_exists('render_form_layout_element')) {
function render_form_layout_element($field) {
    switch ($field['type']) {
        case 'heading':
            ?>
            <div class="form-heading">
                <h3><?php echo esc_html($field['label']); ?></h3>
            </div>
            <?php
            break;
            
        case 'paragraph':
            ?>
            <div class="form-paragraph">
                <p><?php echo nl2br(esc_html($field['default_value'] ?? $field['label'])); ?></p>
            </div>
            <?php
            break;
            
        case 'divider':
            ?>
            <div class="form-divider">
                <hr>
            </div>
            <?php
            break;
    }
}
}

/**
 * Shortcode benzeri kullanım için helper
 * [form slug="contact-form"]
 */
if (!function_exists('process_form_shortcode')) {
function process_form_shortcode($content) {
    return preg_replace_callback('/\[form\s+slug="([^"]+)"\]/i', function($matches) {
        return render_form($matches[1]);
    }, $content);
}
}
?>

