<?php
/**
 * Real Estate Theme - Agent Profile Section
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$sectionTitle = !empty($section['title']) ? $section['title'] : __('Meet Our Expert Agent');
$sectionSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Dedicated to helping you find your perfect property');

$agentName = !empty($settings['agent_name']) ? $settings['agent_name'] : __('John Smith');
$agentTitle = !empty($settings['agent_title']) ? $settings['agent_title'] : __('Senior Real Estate Agent');
$agentBio = !empty($settings['agent_bio']) ? $settings['agent_bio'] : __('With over 10 years of experience in the real estate industry, I specialize in helping clients find their dream homes. My commitment to excellence and personalized service has helped hundreds of families find their perfect property.');
$agentImage = !empty($settings['agent_image']) ? $settings['agent_image'] : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400';
$agentPhone = !empty($settings['agent_phone']) ? $settings['agent_phone'] : '+1 (555) 123-4567';
$agentEmail = !empty($settings['agent_email']) ? $settings['agent_email'] : 'agent@example.com';
$agentExperience = !empty($settings['agent_experience']) ? $settings['agent_experience'] : '10+';
$agentProperties = !empty($settings['agent_properties']) ? $settings['agent_properties'] : '500+';
$agentClients = !empty($settings['agent_clients']) ? $settings['agent_clients'] : '300+';

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = '#1e40af';
$primaryButtonTextColor = '#ffffff';

// ThemeLoader'dan tema renklerini al
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryButtonBg = $themeLoaderInstance->getColor('primary', '#1e40af');
    $primaryButtonTextColor = '#ffffff';
}
?>

<section class="py-16 lg:py-24 bg-white">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-secondary mb-4"><?php echo esc_html($sectionTitle); ?></h2>
            <?php if (!empty($sectionSubtitle)): ?>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($sectionSubtitle); ?></p>
            <?php endif; ?>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8 lg:p-12">
                    <!-- Agent Image -->
                    <div class="relative">
                        <img src="<?php echo esc_url($agentImage); ?>" 
                             alt="<?php echo esc_attr($agentName); ?>" 
                             class="w-full h-full object-cover rounded-lg">
                    </div>

                    <!-- Agent Info -->
                    <div class="flex flex-col justify-center">
                        <h3 class="text-2xl lg:text-3xl font-bold text-secondary mb-2"><?php echo esc_html($agentName); ?></h3>
                        <p class="text-primary font-semibold mb-6"><?php echo esc_html($agentTitle); ?></p>
                        <p class="text-gray-600 mb-8 leading-relaxed"><?php echo esc_html($agentBio); ?></p>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-8">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-primary mb-1"><?php echo esc_html($agentExperience); ?></div>
                                <div class="text-sm text-gray-600"><?php echo esc_html(__('Years Experience')); ?></div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-primary mb-1"><?php echo esc_html($agentProperties); ?></div>
                                <div class="text-sm text-gray-600"><?php echo esc_html(__('Properties Sold')); ?></div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-primary mb-1"><?php echo esc_html($agentClients); ?></div>
                                <div class="text-sm text-gray-600"><?php echo esc_html(__('Happy Clients')); ?></div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="space-y-3 mb-8">
                            <?php if ($agentPhone): ?>
                                <a href="tel:<?php echo esc_attr($agentPhone); ?>" 
                                   class="flex items-center text-gray-700 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <?php echo esc_html($agentPhone); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($agentEmail): ?>
                                <a href="mailto:<?php echo esc_attr($agentEmail); ?>" 
                                   class="flex items-center text-gray-700 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <?php echo esc_html($agentEmail); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- CTA Button -->
                        <a href="<?php echo function_exists('localized_url') ? localized_url('/contact') : site_url('/contact'); ?>" 
                           style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;"
                           class="inline-block px-8 py-3 rounded-lg font-semibold hover:opacity-90 transition-all text-center">
                            <?php echo esc_html(__('Contact Agent')); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
