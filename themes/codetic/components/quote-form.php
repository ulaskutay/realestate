<?php
/**
 * Codetic Theme - Quote Form Component
 * Çok adımlı teklif alma formu
 */

// Değişkenleri kontrol et
$form = $form ?? [];
$steps = $steps ?? [];
$totalSteps = $totalSteps ?? 1;
$pageTitle = $pageTitle ?? 'Teklif Al';

$formId = 'quote-form-' . $form['id'];
$currentStep = 1;
?>

<section class="relative min-h-screen py-16 md:py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0">
        <div class="absolute top-20 left-1/4 w-[600px] h-[600px] bg-primary/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(19,127,236,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(19,127,236,0.03)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 10%, black 90%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 10%, black 90%, transparent 100%);"></div>
    </div>

    <div class="container max-w-[900px] w-full px-6 md:px-10 relative z-10 mx-auto">
        <!-- Header -->
        <div class="text-center mb-12 relative">
            <!-- Watching Characters -->
            <div class="flex items-end justify-center gap-4 mb-8 relative h-32 md:h-40">
                <!-- Purple Character (Tallest) -->
                <div class="quote-character quote-character-purple w-12 h-24 md:w-16 md:h-32 bg-gradient-to-b from-purple-500 to-purple-600 rounded-2xl md:rounded-3xl flex items-start justify-center pt-3 md:pt-4 relative z-10 shadow-lg hover:shadow-purple-500/50">
                    <div class="flex gap-1.5 md:gap-2">
                        <div class="quote-eye w-2 h-2.5 md:w-3 md:h-4 bg-white rounded-full flex items-center justify-center overflow-hidden">
                            <div class="quote-pupil w-1 h-1 md:w-1.5 md:h-1.5 bg-black rounded-full"></div>
                        </div>
                        <div class="quote-eye w-2 h-2.5 md:w-3 md:h-4 bg-white rounded-full flex items-center justify-center overflow-hidden">
                            <div class="quote-pupil w-1 h-1 md:w-1.5 md:h-1.5 bg-black rounded-full"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Orange Character (Bottom Left) -->
                <div class="quote-character quote-character-orange w-16 h-12 md:w-20 md:h-16 bg-gradient-to-b from-orange-400 to-orange-500 rounded-t-2xl md:rounded-t-3xl flex items-start justify-center pt-3 md:pt-3.5 relative z-0 -ml-4 md:-ml-6 shadow-lg hover:shadow-orange-500/50">
                    <div class="flex gap-1 md:gap-1.5">
                        <div class="quote-eye w-1.5 h-1.5 md:w-2 md:h-2 bg-black rounded-full"></div>
                        <div class="quote-eye w-1.5 h-1.5 md:w-2 md:h-2 bg-black rounded-full"></div>
                    </div>
                </div>
                
                <!-- Black Character (Center Right) -->
                <div class="quote-character quote-character-black w-10 h-18 md:w-14 md:h-24 bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl md:rounded-3xl flex items-start justify-center pt-3 md:pt-4 relative z-20 -ml-2 md:-ml-3 shadow-lg hover:shadow-slate-500/50">
                    <div class="flex gap-1.5 md:gap-2">
                        <div class="quote-eye w-2 h-2.5 md:w-3 md:h-4 bg-white rounded-full flex items-center justify-center overflow-hidden">
                            <div class="quote-pupil w-1 h-1 md:w-1.5 md:h-1.5 bg-black rounded-full"></div>
                        </div>
                        <div class="quote-eye w-2 h-2.5 md:w-3 md:h-4 bg-white rounded-full flex items-center justify-center overflow-hidden">
                            <div class="quote-pupil w-1 h-1 md:w-1.5 md:h-1.5 bg-black rounded-full"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Yellow Character (Bottom Right) -->
                <div class="quote-character quote-character-yellow w-10 h-14 md:w-14 md:h-18 bg-gradient-to-b from-yellow-400 to-yellow-500 rounded-2xl md:rounded-3xl flex items-start justify-center pt-3 md:pt-3.5 relative z-0 -ml-3 md:-ml-4 shadow-lg hover:shadow-yellow-500/50">
                    <div class="flex flex-col items-center gap-1">
                        <div class="flex gap-1 md:gap-1.5">
                            <div class="quote-eye w-1.5 h-1.5 md:w-2 md:h-2 bg-black rounded-full"></div>
                            <div class="quote-eye w-1.5 h-1.5 md:w-2 md:h-2 bg-black rounded-full"></div>
                        </div>
                        <div class="w-3 h-0.5 md:w-4 md:h-1 bg-black mt-1"></div>
                    </div>
                </div>
            </div>
            
            <h1 class="text-white text-3xl md:text-5xl font-bold leading-tight mb-4">
                <span class="bg-gradient-to-r from-white via-primary/90 to-primary bg-clip-text text-transparent">
                    <?php echo esc_html($pageTitle); ?>
                </span>
            </h1>
            <?php if (!empty($form['description'])): ?>
                <p class="text-slate-400 text-base md:text-lg max-w-2xl mx-auto">
                    <?php echo esc_html($form['description']); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Progress Bar -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <?php foreach ($steps as $index => $step): ?>
                    <div class="flex items-center flex-1 <?php echo $index < count($steps) - 1 ? 'mr-2' : ''; ?>">
                        <div class="flex items-center flex-1">
                            <div class="quote-step-indicator flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300 <?php echo $index === 0 ? 'bg-primary border-primary text-white' : 'bg-transparent border-slate-600 text-slate-400'; ?>" data-step="<?php echo $step['stepNumber']; ?>">
                                <span class="step-number"><?php echo $step['stepNumber']; ?></span>
                                <svg class="step-check hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <?php if ($index < count($steps) - 1): ?>
                                <div class="flex-1 h-0.5 mx-2 bg-slate-700 quote-progress-line"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-gradient-to-br from-slate-900/95 to-slate-800/95 rounded-3xl backdrop-blur-xl border border-slate-700/50 shadow-2xl p-8 md:p-12">
            <form id="<?php echo esc_attr($formId); ?>" 
                  class="quote-multistep-form" 
                  method="POST" 
                  action="<?php echo site_url('forms/submit'); ?>"
                  data-form-id="<?php echo esc_attr($form['id']); ?>">
                
                <input type="hidden" name="_form_id" value="<?php echo esc_attr($form['id']); ?>">
                
                <?php 
                // Honeypot spam koruması
                $honeypotEnabled = get_option('honeypot_enabled', 1);
                if ($honeypotEnabled): 
                ?>
                <input type="text" 
                       name="website_url" 
                       value="" 
                       tabindex="-1" 
                       autocomplete="off" 
                       style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;"
                       aria-hidden="true">
                <?php endif; ?>

                <!-- Steps -->
                <?php foreach ($steps as $index => $step): ?>
                    <div class="quote-step-content <?php echo $index === 0 ? 'active' : 'hidden'; ?>" data-step="<?php echo $step['stepNumber']; ?>">
                        <!-- Step Header -->
                        <div class="text-center mb-8">
                            <h2 class="text-white text-2xl md:text-3xl font-bold mb-2">
                                <?php echo esc_html($step['title']); ?>
                            </h2>
                            <?php if (!empty($step['subtitle'])): ?>
                                <p class="text-slate-400 text-base md:text-lg">
                                    <?php echo esc_html($step['subtitle']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Step Fields -->
                        <div class="space-y-6">
                            <?php 
                            // Adım 1 için özel render (Proje Tipi - Büyük Kartlar)
                            if ($step['stepNumber'] === 1 && !empty($step['fields'])) {
                                $projectTypeField = null;
                                foreach ($step['fields'] as $field) {
                                    if (in_array($field['name'], ['project_type', 'client_type', 'project_for']) && $field['type'] === 'radio') {
                                        $projectTypeField = $field;
                                        break;
                                    }
                                }
                                
                                if ($projectTypeField && !empty($projectTypeField['options'])): ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <?php foreach ($projectTypeField['options'] as $i => $option): 
                                            $optionValue = $option['value'] ?? $option;
                                            $optionLabel = $option['label'] ?? $option;
                                            $isCompany = (stripos($optionLabel, 'şirket') !== false || stripos($optionLabel, 'company') !== false || stripos($optionValue, 'company') !== false || stripos($optionValue, 'şirket') !== false);
                                            $isPersonal = (stripos($optionLabel, 'kişisel') !== false || stripos($optionLabel, 'personal') !== false || stripos($optionValue, 'personal') !== false || stripos($optionValue, 'kişisel') !== false);
                                        ?>
                                            <label class="quote-project-type-card group relative flex flex-col items-center justify-center p-8 md:p-12 rounded-2xl border-2 border-slate-700 bg-gradient-to-br from-slate-800/50 to-slate-900/50 cursor-pointer transition-all duration-300 hover:border-primary hover:shadow-lg hover:shadow-primary/20 hover:scale-105 <?php echo ($projectTypeField['default_value'] ?? '') === $optionValue ? 'border-primary bg-primary/10 shadow-lg shadow-primary/20' : ''; ?>">
                                                <input type="radio" 
                                                       name="<?php echo esc_attr($projectTypeField['name']); ?>" 
                                                       value="<?php echo esc_attr($optionValue); ?>"
                                                       class="absolute opacity-0 pointer-events-none"
                                                       <?php echo ($projectTypeField['default_value'] ?? '') === $optionValue ? 'checked' : ''; ?>
                                                       <?php echo $projectTypeField['required'] ? 'required' : ''; ?>
                                                       data-project-type="<?php echo $isCompany ? 'company' : ($isPersonal ? 'personal' : ''); ?>">
                                                
                                                <!-- Icon -->
                                                <div class="mb-4 text-6xl md:text-7xl transition-transform duration-300 group-hover:scale-110">
                                                    <?php if ($isCompany): ?>
                                                        <span class="inline-block">🏢</span>
                                                    <?php elseif ($isPersonal): ?>
                                                        <span class="inline-block">👤</span>
                                                    <?php else: ?>
                                                        <span class="inline-block">📋</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Label -->
                                                <h3 class="text-white text-xl md:text-2xl font-bold mb-2 text-center">
                                                    <?php echo esc_html($optionLabel); ?>
                                                </h3>
                                                
                                                <!-- Description -->
                                                <?php if ($isCompany): ?>
                                                    <p class="text-slate-400 text-sm md:text-base text-center">Web tasarım ihtiyaçlarınız için tercih edebilirsiniz.</p>
                                                <?php elseif ($isPersonal): ?>
                                                    <p class="text-slate-400 text-sm md:text-base text-center">Ürünlerinizi çevrimiçi pazarda satmak isteyip istemediğinizi seçin.</p>
                                                <?php endif; ?>
                                                
                                                <!-- Check Icon -->
                                                <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-primary flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else:
                                    // Normal field render
                                    foreach ($step['fields'] as $field): ?>
                                        <?php render_quote_form_field($field, $step['stepNumber']); ?>
                                    <?php endforeach;
                                endif;
                            }
                            // Adım 2 için özel render (Kategorilere Göre Hizmet Seçimi)
                            elseif ($step['stepNumber'] === 2 && !empty($step['fields']) && isset($serviceCategories)) {
                                // Hizmetleri kategorilere göre göster
                                $hasServices = false;
                                foreach ($serviceCategories as $catKey => $category) {
                                    if (!empty($category['fields'])) {
                                        $hasServices = true;
                                        break;
                                    }
                                }
                                
                                if ($hasServices): ?>
                                    <div class="space-y-8">
                                        <?php foreach ($serviceCategories as $catKey => $category): 
                                            if (empty($category['fields'])) continue;
                                        ?>
                                            <div class="quote-service-category">
                                                <h3 class="text-white text-lg md:text-xl font-semibold mb-4 flex items-center gap-2">
                                                    <span class="text-2xl"><?php echo esc_html($category['icon']); ?></span>
                                                    <span><?php echo esc_html($category['name']); ?></span>
                                                </h3>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <?php foreach ($category['fields'] as $field): 
                                                        if (!empty($field['options'])): ?>
                                                            <?php foreach ($field['options'] as $i => $option): 
                                                                $fieldId = 'field-' . $field['name'] . '-' . $i;
                                                                $optionValue = $option['value'] ?? $option;
                                                                $optionLabel = $option['label'] ?? $option;
                                                            ?>
                                                                <label class="quote-service-card flex items-center p-4 rounded-xl border-2 border-slate-700 bg-slate-800/30 hover:border-primary/50 hover:bg-slate-800/50 cursor-pointer transition-all duration-200 group">
                                                                    <input type="checkbox" 
                                                                           id="<?php echo esc_attr($fieldId); ?>"
                                                                           name="<?php echo esc_attr($field['name']); ?>[]" 
                                                                           value="<?php echo esc_attr($optionValue); ?>"
                                                                           class="w-5 h-5 text-primary focus:ring-2 focus:ring-primary border-slate-600 bg-slate-800 rounded transition-all">
                                                                    <span class="ml-3 text-white flex-1 group-hover:text-primary transition-colors"><?php echo esc_html($optionLabel); ?></span>
                                                                    <div class="w-5 h-5 rounded border-2 border-slate-600 flex items-center justify-center group-has-[:checked]:bg-primary group-has-[:checked]:border-primary transition-all">
                                                                        <svg class="w-3 h-3 text-white opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                    </div>
                                                                </label>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else:
                                    // Normal field render
                                    foreach ($step['fields'] as $field): ?>
                                        <?php render_quote_form_field($field, $step['stepNumber']); ?>
                                    <?php endforeach;
                                endif;
                            }
                            // Diğer adımlar için normal render
                            else:
                                foreach ($step['fields'] as $field): ?>
                                    <?php render_quote_form_field($field, $step['stepNumber']); ?>
                                <?php endforeach;
                            endif; ?>
                        </div>

                        <!-- Step Navigation -->
                        <div class="flex items-center justify-between mt-10 pt-8 border-t border-slate-700">
                            <button type="button" 
                                    class="quote-prev-btn px-6 py-3 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-white transition-all duration-200 <?php echo $index === 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                    data-step="<?php echo $step['stepNumber']; ?>"
                                    <?php echo $index === 0 ? 'disabled' : ''; ?>>
                                <span class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Geri
                                </span>
                            </button>

                            <div class="text-slate-400 text-sm">
                                Adım <?php echo $step['stepNumber']; ?> / <?php echo $totalSteps; ?>
                            </div>

                            <?php if ($index < count($steps) - 1): ?>
                                <button type="button" 
                                        class="quote-next-btn px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-white transition-all duration-200 flex items-center gap-2 font-medium"
                                        data-step="<?php echo $step['stepNumber']; ?>"
                                        data-next-step="<?php echo $step['stepNumber'] + 1; ?>">
                                    İleri
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            <?php else: ?>
                                <button type="submit" 
                                        class="quote-submit-btn px-8 py-3 rounded-xl bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-white font-semibold transition-all duration-200 flex items-center gap-2">
                                    <span class="submit-text">Gönder</span>
                                    <span class="submit-loading hidden">
                                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>

            <!-- Success Message -->
            <div id="<?php echo esc_attr($formId); ?>-success" class="hidden text-center py-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-500/20 mb-6">
                    <svg class="w-10 h-10 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-white text-2xl font-bold mb-2">Talebiniz Alındı!</h3>
                <p class="text-slate-400 text-lg mb-6">
                    <?php echo esc_html($form['success_message'] ?? 'Size en kısa sürede geri dönüş yapacağız.'); ?>
                </p>
                <a href="/" class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-white transition-all duration-200 font-medium">
                    Anasayfaya Dön
                </a>
            </div>

            <!-- Error Message -->
            <div id="<?php echo esc_attr($formId); ?>-error" class="hidden p-4 mb-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400"></div>
        </div>
    </div>
</section>

<?php
/**
 * Quote form field render function
 */
if (!function_exists('render_quote_form_field')) {
function render_quote_form_field($field, $stepNumber = null) {
    $fieldId = 'field-' . $field['name'];
    $requiredClass = $field['required'] ? 'required' : '';
    
    // Layout elemanlarını atla (heading, paragraph, divider)
    if (in_array($field['type'], ['heading', 'paragraph', 'divider'])) {
        return;
    }
    
    // Adım 4 için dinamik alan kontrolü (şirket/kişisel)
    $fieldName = strtolower($field['name']);
    $isCompanyField = in_array($fieldName, ['company_name', 'position', 'company']);
    $isPersonalField = in_array($fieldName, ['name', 'surname', 'email', 'phone', 'city', 'district']);
    $dynamicClass = '';
    if ($isCompanyField) {
        $dynamicClass = 'quote-company-field';
    } elseif ($isPersonalField) {
        $dynamicClass = 'quote-personal-field';
    }
    
    ?>
    <div class="quote-form-field <?php echo esc_attr($requiredClass); ?> <?php echo esc_attr($dynamicClass); ?>" 
         data-field-type="<?php echo esc_attr($field['type']); ?>"
         data-field-name="<?php echo esc_attr($field['name']); ?>">
        <?php if ($field['type'] !== 'hidden'): ?>
            <label class="block text-white font-medium mb-2" for="<?php echo esc_attr($fieldId); ?>">
                <?php echo esc_html($field['label']); ?>
                <?php if ($field['required']): ?>
                    <span class="text-red-400">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>
        
        <div class="field-input-wrapper">
            <?php
            switch ($field['type']) {
                case 'text':
                case 'email':
                case 'phone':
                case 'number':
                case 'date':
                    $inputType = $field['type'];
                    if ($field['type'] === 'phone') $inputType = 'tel';
                    ?>
                    <input type="<?php echo esc_attr($inputType); ?>" 
                           id="<?php echo esc_attr($fieldId); ?>"
                           name="<?php echo esc_attr($field['name']); ?>" 
                           placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                           value="<?php echo esc_attr($field['default_value'] ?? ''); ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                           <?php echo $field['required'] ? 'required' : ''; ?>>
                    <?php
                    break;
                    
                case 'textarea':
                    ?>
                    <textarea id="<?php echo esc_attr($fieldId); ?>"
                              name="<?php echo esc_attr($field['name']); ?>" 
                              placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                              rows="4"
                              class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"
                              <?php echo $field['required'] ? 'required' : ''; ?>><?php echo esc_html($field['default_value'] ?? ''); ?></textarea>
                    <?php
                    break;
                    
                case 'select':
                    ?>
                    <select id="<?php echo esc_attr($fieldId); ?>"
                            name="<?php echo esc_attr($field['name']); ?>" 
                            class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
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
                    
                case 'radio':
                    // Adım 1 için özel render yapıldı, buraya gelmemeli ama fallback olarak
                    ?>
                    <div class="space-y-3">
                        <?php if (!empty($field['options'])): ?>
                            <?php foreach ($field['options'] as $i => $option): ?>
                                <label class="flex items-center p-4 rounded-xl bg-slate-800/30 border border-slate-700 hover:bg-slate-800/50 cursor-pointer transition-all quote-radio-option">
                                    <input type="radio" 
                                           id="<?php echo esc_attr($fieldId); ?>-<?php echo $i; ?>"
                                           name="<?php echo esc_attr($field['name']); ?>" 
                                           value="<?php echo esc_attr($option['value'] ?? $option); ?>"
                                           class="w-5 h-5 text-primary focus:ring-2 focus:ring-primary border-slate-600 bg-slate-800"
                                           <?php echo ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'checked' : ''; ?>
                                           <?php echo $field['required'] ? 'required' : ''; ?>>
                                    <span class="ml-3 text-white"><?php echo esc_html($option['label'] ?? $option); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php
                    break;
                    
                case 'checkbox':
                    // KVKK checkbox için özel tasarım
                    if (strpos($fieldName, 'kvkk') !== false || strpos($fieldName, 'consent') !== false || strpos($fieldName, 'terms') !== false) {
                        ?>
                        <label class="flex items-start p-4 rounded-xl bg-slate-800/30 border border-slate-700 hover:bg-slate-800/50 cursor-pointer transition-all">
                            <input type="checkbox" 
                                   id="<?php echo esc_attr($fieldId); ?>"
                                   name="<?php echo esc_attr($field['name']); ?>" 
                                   value="1"
                                   class="w-5 h-5 text-primary focus:ring-2 focus:ring-primary border-slate-600 bg-slate-800 rounded mt-0.5"
                                   <?php echo $field['required'] ? 'required' : ''; ?>>
                            <span class="ml-3 text-white text-sm"><?php echo esc_html($field['label']); ?></span>
                        </label>
                        <?php
                    } else {
                        // Normal checkbox
                        ?>
                        <div class="space-y-3">
                            <?php if (!empty($field['options'])): ?>
                                <?php foreach ($field['options'] as $i => $option): ?>
                                    <label class="flex items-center p-3 rounded-xl bg-slate-800/30 border border-slate-700 hover:bg-slate-800/50 cursor-pointer transition-all">
                                        <input type="checkbox" 
                                               id="<?php echo esc_attr($fieldId); ?>-<?php echo $i; ?>"
                                               name="<?php echo esc_attr($field['name']); ?>[]" 
                                               value="<?php echo esc_attr($option['value'] ?? $option); ?>"
                                               class="w-5 h-5 text-primary focus:ring-2 focus:ring-primary border-slate-600 bg-slate-800 rounded">
                                        <span class="ml-3 text-white"><?php echo esc_html($option['label'] ?? $option); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
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
            <p class="mt-2 text-sm text-slate-400"><?php echo esc_html($field['help_text']); ?></p>
        <?php endif; ?>
        
        <div class="field-error-message hidden mt-2 text-sm text-red-400"></div>
    </div>
    <?php
}
}
?>

<style>
@keyframes pulse-slow {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 0.8; }
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

/* Character Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes blink {
    0%, 90%, 100% { transform: scaleY(1); }
    95% { transform: scaleY(0.1); }
}

@keyframes look-around {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(2px); }
    75% { transform: translateX(-2px); }
}

.quote-character {
    animation: float 3s ease-in-out infinite;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.quote-character:hover {
    transform: translateY(-5px) scale(1.05);
}

.quote-character-purple {
    animation-delay: 0s;
}

.quote-character-orange {
    animation-delay: 0.5s;
}

.quote-character-black {
    animation-delay: 1s;
}

.quote-character-yellow {
    animation-delay: 1.5s;
}

.quote-character .quote-eye {
    animation: blink 3s ease-in-out infinite, look-around 5s ease-in-out infinite;
    position: relative;
}

.quote-character:hover .quote-eye {
    animation: blink 0.5s ease-in-out, look-around 2s ease-in-out infinite;
}

.quote-pupil {
    transition: transform 0.2s ease;
}

/* Mouse tracking for eyes */
@media (hover: hover) {
    .quote-character:hover .quote-pupil {
        transform: translate(var(--mouse-x, 0), var(--mouse-y, 0));
    }
}

.quote-step-content {
    animation: fadeIn 0.3s ease-in;
}

.quote-step-content.active {
    display: block;
}

.quote-step-content.hidden {
    display: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quote-step-indicator.completed {
    background: linear-gradient(135deg, #10b981, #059669);
    border-color: #10b981;
    color: white;
}

.quote-step-indicator.completed .step-number {
    display: none;
}

.quote-step-indicator.completed .step-check {
    display: block !important;
}

.quote-step-indicator.active {
    background: linear-gradient(135deg, #137fec, #0ea5e9);
    border-color: #137fec;
}

.quote-progress-line {
    transition: background-color 0.3s ease;
}

.quote-progress-line.completed {
    background: linear-gradient(90deg, #137fec, #0ea5e9);
}

.quote-radio-option:has(input:checked),
.quote-checkbox-option:has(input:checked) {
    border-color: #137fec;
    background: rgba(19, 127, 236, 0.1);
}

/* Proje Tipi Kartları */
.quote-project-type-card input:checked ~ *,
.quote-project-type-card:has(input:checked) {
    border-color: #137fec;
    background: linear-gradient(135deg, rgba(19, 127, 236, 0.15), rgba(19, 127, 236, 0.05));
    box-shadow: 0 10px 30px rgba(19, 127, 236, 0.3);
}

.quote-project-type-card:has(input:checked) .absolute {
    opacity: 1 !important;
}

/* Hizmet Kartları */
.quote-service-card:has(input:checked) {
    border-color: #137fec;
    background: rgba(19, 127, 236, 0.1);
}

.quote-service-card:has(input:checked) span {
    color: #137fec;
}

/* Dinamik Alan Gösterimi */
.quote-company-field,
.quote-personal-field {
    transition: opacity 0.3s ease, max-height 0.3s ease;
}

.quote-company-field.hidden,
.quote-personal-field.hidden {
    display: none;
    opacity: 0;
    max-height: 0;
    overflow: hidden;
}

/* Hizmet Kategorileri */
.quote-service-category {
    padding: 1.5rem;
    background: rgba(15, 23, 42, 0.5);
    border-radius: 1rem;
    border: 1px solid rgba(148, 163, 184, 0.1);
}

.quote-form-field.field-error input,
.quote-form-field.field-error textarea,
.quote-form-field.field-error select {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.quote-form-field.field-error .field-error-message {
    display: block;
}

/* Responsive Design */
@media (max-width: 768px) {
    .quote-step-indicator {
        width: 8px;
        height: 8px;
        min-width: 8px;
    }
    
    .quote-step-indicator .step-number,
    .quote-step-indicator .step-check {
        display: none;
    }
    
    .quote-project-type-card {
        padding: 1.5rem !important;
    }
    
    .quote-project-type-card .text-6xl {
        font-size: 3rem;
    }
    
    .quote-service-category {
        padding: 1rem !important;
    }
    
    .quote-service-card {
        padding: 0.75rem !important;
    }
}

/* Progress Bar İyileştirmeleri */
.quote-progress-line {
    height: 2px;
    background: rgba(148, 163, 184, 0.3);
}

.quote-progress-line.completed {
    background: linear-gradient(90deg, #137fec, #0ea5e9);
    height: 3px;
    box-shadow: 0 0 10px rgba(19, 127, 236, 0.5);
}

/* Kart Animasyonları */
@keyframes cardPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
}

.quote-project-type-card:has(input:checked) {
    animation: cardPulse 2s ease-in-out infinite;
}

/* Hizmet Kartları Hover */
.quote-service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 127, 236, 0.2);
}
</style>

<script>
(function() {
    'use strict';
    
    const form = document.getElementById('<?php echo esc_js($formId); ?>');
    if (!form) return;
    
    const formWrapper = form.closest('.bg-gradient-to-br');
    const successEl = document.getElementById('<?php echo esc_js($formId); ?>-success');
    const errorEl = document.getElementById('<?php echo esc_js($formId); ?>-error');
    const steps = form.querySelectorAll('.quote-step-content');
    const indicators = document.querySelectorAll('.quote-step-indicator');
    const progressLines = document.querySelectorAll('.quote-progress-line');
    
    let currentStep = 1;
    const totalSteps = steps.length;
    
    // Step navigation
    function showStep(stepNumber) {
        // Hide all steps
        steps.forEach(step => {
            step.classList.remove('active');
            step.classList.add('hidden');
        });
        
        // Show current step
        const currentStepEl = form.querySelector(`[data-step="${stepNumber}"]`);
        if (currentStepEl) {
            currentStepEl.classList.remove('hidden');
            currentStepEl.classList.add('active');
        }
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            const stepNum = parseInt(indicator.dataset.step);
            indicator.classList.remove('active', 'completed');
            
            if (stepNum < stepNumber) {
                indicator.classList.add('completed');
                indicator.querySelector('.step-number')?.classList.add('hidden');
                indicator.querySelector('.step-check')?.classList.remove('hidden');
            } else if (stepNum === stepNumber) {
                indicator.classList.add('active');
                indicator.querySelector('.step-number')?.classList.remove('hidden');
                indicator.querySelector('.step-check')?.classList.add('hidden');
            }
        });
        
        // Update progress lines
        progressLines.forEach((line, index) => {
            if (index + 1 < stepNumber) {
                line.classList.add('completed');
            } else {
                line.classList.remove('completed');
            }
        });
        
        currentStep = stepNumber;
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Next button
    form.querySelectorAll('.quote-next-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const stepNumber = parseInt(this.dataset.step);
            const stepContent = form.querySelector(`[data-step="${stepNumber}"]`);
            
            // Validate current step
            if (!validateStep(stepContent)) {
                return;
            }
            
            const nextStep = parseInt(this.dataset.nextStep);
            if (nextStep <= totalSteps) {
                showStep(nextStep);
            }
        });
    });
    
    // Prev button
    form.querySelectorAll('.quote-prev-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.disabled) return;
            
            const stepNumber = parseInt(this.dataset.step);
            const prevStep = stepNumber - 1;
            
            if (prevStep >= 1) {
                showStep(prevStep);
            }
        });
    });
    
    // Validate step
    function validateStep(stepContent) {
        const fields = stepContent.querySelectorAll('[required]');
        let isValid = true;
        
        // Clear previous errors
        stepContent.querySelectorAll('.field-error').forEach(el => {
            el.classList.remove('field-error');
        });
        stepContent.querySelectorAll('.field-error-message').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        
        fields.forEach(field => {
            let fieldValid = true;
            const fieldWrapper = field.closest('.quote-form-field');
            
            // Check required fields
            if (field.type === 'checkbox') {
                // For checkbox groups, check if at least one is checked
                const checkboxes = stepContent.querySelectorAll(`input[name="${field.name}"]`);
                const checked = Array.from(checkboxes).some(cb => cb.checked);
                if (!checked) {
                    fieldValid = false;
                }
            } else if (field.type === 'radio') {
                // For radio groups, check if at least one is selected
                const radios = stepContent.querySelectorAll(`input[name="${field.name}"]`);
                const checked = Array.from(radios).some(r => r.checked);
                if (!checked) {
                    fieldValid = false;
                }
            } else {
                // For other fields
                if (!field.value.trim()) {
                    fieldValid = false;
                }
            }
            
            if (!fieldValid) {
                isValid = false;
                if (fieldWrapper) {
                    fieldWrapper.classList.add('field-error');
                    const errorMsg = fieldWrapper.querySelector('.field-error-message');
                    if (errorMsg) {
                        errorMsg.textContent = 'Bu alan zorunludur';
                        errorMsg.classList.remove('hidden');
                    }
                }
            }
        });
        
        if (!isValid) {
            // Show error message
            if (errorEl) {
                errorEl.textContent = 'Lütfen tüm zorunlu alanları doldurun';
                errorEl.classList.remove('hidden');
                setTimeout(() => {
                    errorEl.classList.add('hidden');
                }, 5000);
            }
        }
        
        return isValid;
    }
    
    // Form submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate last step
        const lastStep = form.querySelector(`[data-step="${totalSteps}"]`);
        if (!validateStep(lastStep)) {
            return;
        }
        
        // Show loading
        const submitBtn = form.querySelector('.quote-submit-btn');
        const submitText = submitBtn.querySelector('.submit-text');
        const submitLoading = submitBtn.querySelector('.submit-loading');
        
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitLoading.classList.remove('hidden');
        
        // Clear errors
        errorEl.classList.add('hidden');
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Sunucudan geçersiz yanıt alındı');
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Success
                form.style.display = 'none';
                if (successEl) {
                    successEl.classList.remove('hidden');
                }
                
                // Redirect if provided
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            } else {
                // Error
                if (data.errors) {
                    // Field errors
                    Object.entries(data.errors).forEach(([fieldName, message]) => {
                        const field = form.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                        if (field) {
                            const fieldWrapper = field.closest('.quote-form-field');
                            if (fieldWrapper) {
                                fieldWrapper.classList.add('field-error');
                                const errorMsg = fieldWrapper.querySelector('.field-error-message');
                                if (errorMsg) {
                                    errorMsg.textContent = message;
                                    errorMsg.classList.remove('hidden');
                                }
                            }
                        }
                    });
                }
                
                // General error
                if (data.message) {
                    errorEl.textContent = data.message;
                    errorEl.classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            errorEl.textContent = 'Form gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
            errorEl.classList.remove('hidden');
        } finally {
            // Reset button
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
        }
    });
    
    // Proje tipi seçimi için kart güncelleme
    form.querySelectorAll('.quote-project-type-card input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Tüm kartları güncelle
            form.querySelectorAll('.quote-project-type-card').forEach(card => {
                card.classList.remove('border-primary', 'bg-primary/10', 'shadow-lg', 'shadow-primary/20');
            });
            
            // Seçili kartı vurgula
            const selectedCard = this.closest('.quote-project-type-card');
            if (selectedCard) {
                selectedCard.classList.add('border-primary', 'bg-primary/10', 'shadow-lg', 'shadow-primary/20');
            }
            
            // Dinamik alan gösterimi (Adım 4)
            updateDynamicFields(this);
        });
    });
    
    // Dinamik alan güncelleme fonksiyonu
    function updateDynamicFields(projectTypeRadio) {
        const projectType = projectTypeRadio.dataset.projectType || '';
        const step4 = form.querySelector('[data-step="4"]');
        
        if (!step4) return;
        
        const companyFields = step4.querySelectorAll('.quote-company-field');
        const personalFields = step4.querySelectorAll('.quote-personal-field');
        
        if (projectType === 'company') {
            // Şirket alanlarını göster, kişisel alanları gizle
            companyFields.forEach(field => {
                field.classList.remove('hidden');
                field.style.display = 'block';
            });
            personalFields.forEach(field => {
                field.classList.add('hidden');
                field.style.display = 'none';
                // Required'ı kaldır
                const inputs = field.querySelectorAll('[required]');
                inputs.forEach(input => input.removeAttribute('required'));
            });
        } else if (projectType === 'personal') {
            // Kişisel alanları göster, şirket alanlarını gizle
            personalFields.forEach(field => {
                field.classList.remove('hidden');
                field.style.display = 'block';
            });
            companyFields.forEach(field => {
                field.classList.add('hidden');
                field.style.display = 'none';
                // Required'ı kaldır
                const inputs = field.querySelectorAll('[required]');
                inputs.forEach(input => input.removeAttribute('required'));
            });
        } else {
            // Her ikisini de göster (varsayılan)
            companyFields.forEach(field => {
                field.classList.remove('hidden');
                field.style.display = 'block';
            });
            personalFields.forEach(field => {
                field.classList.remove('hidden');
                field.style.display = 'block';
            });
        }
    }
    
    // İlk yüklemede dinamik alanları kontrol et
    const initialProjectType = form.querySelector('.quote-project-type-card input[type="radio"]:checked');
    if (initialProjectType) {
        updateDynamicFields(initialProjectType);
    }
    
    // Initialize
    showStep(1);
    
    // Character eye tracking
    const characters = document.querySelectorAll('.quote-character');
    const characterContainer = document.querySelector('.quote-character')?.closest('.flex.items-end');
    
    if (characterContainer && characters.length > 0) {
        characterContainer.addEventListener('mousemove', (e) => {
            characters.forEach(character => {
                const rect = character.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                const deltaX = (e.clientX - centerX) / rect.width;
                const deltaY = (e.clientY - centerY) / rect.height;
                
                // Limit pupil movement
                const maxMove = 3;
                const moveX = Math.max(-maxMove, Math.min(maxMove, deltaX * maxMove));
                const moveY = Math.max(-maxMove, Math.min(maxMove, deltaY * maxMove));
                
                const eyes = character.querySelectorAll('.quote-pupil');
                eyes.forEach(pupil => {
                    pupil.style.setProperty('--mouse-x', moveX + 'px');
                    pupil.style.setProperty('--mouse-y', moveY + 'px');
                    pupil.style.transform = `translate(${moveX}px, ${moveY}px)`;
                });
            });
        });
        
        characterContainer.addEventListener('mouseleave', () => {
            characters.forEach(character => {
                const eyes = character.querySelectorAll('.quote-pupil');
                eyes.forEach(pupil => {
                    pupil.style.transform = 'translate(0, 0)';
                });
            });
        });
    }
})();
</script>
