<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($form['name']); ?> - Form Önizleme</title>
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/form-builder.css'; ?>" rel="stylesheet"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .preview-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 600px;
            width: 100%;
            padding: 2rem;
        }
        
        .preview-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .preview-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .preview-description {
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        /* Form Styles */
        .cms-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        
        .form-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .form-field {
            flex-basis: 100%;
        }
        
        .form-field.field-width-half { flex-basis: calc(50% - 0.5rem); }
        .form-field.field-width-third { flex-basis: calc(33.333% - 0.67rem); }
        .form-field.field-width-quarter { flex-basis: calc(25% - 0.75rem); }
        
        @media (max-width: 640px) {
            .form-field.field-width-half,
            .form-field.field-width-third,
            .form-field.field-width-quarter {
                flex-basis: 100%;
            }
        }
        
        .field-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .required-mark {
            color: #ef4444;
            margin-left: 0.25rem;
        }
        
        .field-input input[type="text"],
        .field-input input[type="email"],
        .field-input input[type="tel"],
        .field-input input[type="number"],
        .field-input input[type="date"],
        .field-input input[type="time"],
        .field-input input[type="datetime-local"],
        .field-input textarea,
        .field-input select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            color: #111827;
            background-color: white;
            transition: all 0.2s;
        }
        
        .field-input input:focus,
        .field-input textarea:focus,
        .field-input select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .field-input textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .field-help {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.375rem;
        }
        
        /* Checkbox & Radio */
        .checkbox-group,
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .checkbox-label,
        .radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: #374151;
        }
        
        .checkbox-label input,
        .radio-label input {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: #3b82f6;
        }
        
        /* Layout Elements */
        .form-heading {
            flex-basis: 100%;
            padding: 0.5rem 0;
        }
        
        .form-heading h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
        }
        
        .form-paragraph {
            flex-basis: 100%;
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .form-divider {
            flex-basis: 100%;
            padding: 0.5rem 0;
        }
        
        .form-divider hr {
            border: none;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Submit Button */
        .form-submit {
            padding-top: 0.5rem;
        }
        
        .submit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .submit-button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .submit-button:active {
            transform: translateY(0);
        }
        
        /* No Fields Message */
        .no-fields-message {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }
        
        /* Success Message */
        .form-success {
            text-align: center;
            padding: 2rem;
            background: #ecfdf5;
            border-radius: 0.5rem;
            display: none;
        }
        
        .form-success.show {
            display: block;
        }
        
        .form-success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-success-icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }
        
        .form-success-message {
            color: #065f46;
            font-size: 1.125rem;
            font-weight: 500;
        }
        
        /* Preview Notice */
        .preview-notice {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        
        .preview-notice a {
            color: #60a5fa;
            text-decoration: none;
        }
        
        .preview-notice a:hover {
            text-decoration: underline;
        }
        
        /* Form Styles */
        .form-style-modern .field-input input,
        .form-style-modern .field-input textarea,
        .form-style-modern .field-input select {
            border-radius: 0.75rem;
            border: 2px solid #e5e7eb;
        }
        
        .form-style-modern .field-input input:focus,
        .form-style-modern .field-input textarea:focus,
        .form-style-modern .field-input select:focus {
            border-color: <?php echo esc_attr($form['submit_button_color'] ?? '#3b82f6'); ?>;
        }
        
        .form-style-minimal .field-input input,
        .form-style-minimal .field-input textarea,
        .form-style-minimal .field-input select {
            border: none;
            border-bottom: 2px solid #e5e7eb;
            border-radius: 0;
            padding-left: 0;
            padding-right: 0;
        }
        
        .form-style-minimal .field-input input:focus,
        .form-style-minimal .field-input textarea:focus,
        .form-style-minimal .field-input select:focus {
            border-color: <?php echo esc_attr($form['submit_button_color'] ?? '#3b82f6'); ?>;
            box-shadow: none;
        }
        
        .form-style-bordered .cms-form {
            border: 2px solid #e5e7eb;
            padding: 1.5rem;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Preview Notice -->
    <div class="preview-notice">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
        </svg>
        Form Önizleme Modu
        <span style="margin: 0 0.5rem; opacity: 0.5;">|</span>
        <a href="<?php echo admin_url('forms/edit/' . $form['id']); ?>">Düzenlemeye Dön</a>
    </div>
    
    <div class="preview-container">
        <div class="preview-header">
            <h1 class="preview-title"><?php echo esc_html($form['name']); ?></h1>
            <?php if (!empty($form['description'])): ?>
                <p class="preview-description"><?php echo esc_html($form['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        $styleClass = 'form-style-' . ($form['form_style'] ?? 'default');
        $layoutClass = 'form-layout-' . ($form['layout'] ?? 'vertical');
        ?>
        
        <form id="preview-form" class="cms-form <?php echo esc_attr($styleClass); ?> <?php echo esc_attr($layoutClass); ?>" onsubmit="return handleSubmit(event);">
            <div class="form-fields">
                <?php if (!empty($form['fields'])): ?>
                    <?php foreach ($form['fields'] as $field): ?>
                        <?php if ($field['status'] !== 'active') continue; ?>
                        <?php renderPreviewField($field); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-fields-message">
                        <p>Bu formda henüz alan bulunmuyor.</p>
                        <p style="margin-top: 0.5rem; font-size: 0.875rem;">Alanları eklemek için düzenleme sayfasını kullanın.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($form['fields'])): ?>
                <div class="form-submit">
                    <button type="submit" class="submit-button" style="background-color: <?php echo esc_attr($form['submit_button_color'] ?? '#3b82f6'); ?>">
                        <?php echo esc_html($form['submit_button_text'] ?? 'Gönder'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
        
        <div id="form-success" class="form-success">
            <div class="form-success-icon">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <p class="form-success-message"><?php echo esc_html($form['success_message'] ?? 'Formunuz başarıyla gönderildi!'); ?></p>
        </div>
    </div>
    
    <script>
        function handleSubmit(e) {
            e.preventDefault();
            
            // Önizleme modunda gerçek gönderim yapılmaz
            document.getElementById('preview-form').style.display = 'none';
            document.getElementById('form-success').classList.add('show');
            
            // 3 saniye sonra formu tekrar göster
            setTimeout(() => {
                document.getElementById('preview-form').style.display = '';
                document.getElementById('form-success').classList.remove('show');
            }, 3000);
            
            return false;
        }
    </script>
</body>
</html>

<?php
function renderPreviewField($field) {
    $widthClass = 'field-width-' . ($field['width'] ?? 'full');
    $requiredClass = $field['required'] ? 'field-required' : '';
    $customClass = $field['css_class'] ?? '';
    
    // Layout elemanları
    if (in_array($field['type'], ['heading', 'paragraph', 'divider'])) {
        renderPreviewLayoutElement($field);
        return;
    }
    
    ?>
    <div class="form-field <?php echo esc_attr($widthClass); ?> <?php echo esc_attr($requiredClass); ?> <?php echo esc_attr($customClass); ?>">
        <?php if ($field['type'] !== 'hidden'): ?>
            <label class="field-label">
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
                case 'tel':
                case 'number':
                case 'date':
                case 'time':
                case 'datetime':
                    $inputType = $field['type'];
                    if ($field['type'] === 'phone' || $field['type'] === 'tel') $inputType = 'tel';
                    if ($field['type'] === 'datetime') $inputType = 'datetime-local';
                    ?>
                    <input type="<?php echo esc_attr($inputType); ?>" 
                           name="<?php echo esc_attr($field['name']); ?>" 
                           placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                           value="<?php echo esc_attr($field['default_value'] ?? ''); ?>"
                           <?php echo $field['required'] ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'textarea':
                    ?>
                    <textarea name="<?php echo esc_attr($field['name']); ?>" 
                              placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                              rows="4"
                              <?php echo $field['required'] ? 'required' : ''; ?>><?php echo esc_html($field['default_value'] ?? ''); ?></textarea>
                    <?php
                    break;
                    
                case 'select':
                    ?>
                    <select name="<?php echo esc_attr($field['name']); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                        <option value=""><?php echo esc_html($field['placeholder'] ?? 'Seçiniz...'); ?></option>
                        <?php if (!empty($field['options'])): ?>
                            <?php foreach ($field['options'] as $option): ?>
                                <option value="<?php echo esc_attr($option['value'] ?? $option); ?>">
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
                            <?php foreach ($field['options'] as $option): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" 
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
                            <?php foreach ($field['options'] as $option): ?>
                                <label class="radio-label">
                                    <input type="radio" 
                                           name="<?php echo esc_attr($field['name']); ?>" 
                                           value="<?php echo esc_attr($option['value'] ?? $option); ?>">
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

function renderPreviewLayoutElement($field) {
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
?>

