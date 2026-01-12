<?php
/**
 * Codetic Theme - Rezervasyon Sayfası
 * 3 aşamalı rezervasyon formu: Uçak bileti -> Otel -> Araç kiralama
 */

// ThemeLoader yükle
if (!class_exists('ThemeLoader')) {
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(dirname(__DIR__)));
    $themeLoaderPath = $docRoot . '/core/ThemeLoader.php';
    if (!file_exists($themeLoaderPath)) {
        $themeLoaderPath = __DIR__ . '/../../../core/ThemeLoader.php';
    }
    require_once $themeLoaderPath;
}
$themeLoader = ThemeLoader::getInstance();

// Page title ve meta
$pageTitle = $title ?? 'Rezervasyon';
$pageDescription = $meta_description ?? 'Uçak bileti, otel ve araç kiralama rezervasyonu yapın';

// Layout değişkenleri
if (!isset($meta_description)) {
    $meta_description = $pageDescription;
}
if (!isset($meta_keywords)) {
    $meta_keywords = 'rezervasyon, uçak bileti, otel, araç kiralama';
}
$current_page = 'reservation';

// Layout'u kullan
$layoutPath = __DIR__ . '/layouts/default.php';

// Content'i yakala
ob_start();
?>

<!-- Rezervasyon Formu -->
<section class="relative min-h-screen py-16 md:py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-1/4 w-[600px] h-[600px] bg-primary/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(19,127,236,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(19,127,236,0.03)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 10%, black 90%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 10%, black 90%, transparent 100%);"></div>
    </div>

    <div class="container max-w-[900px] w-full px-6 md:px-10 relative z-10 mx-auto">
        <!-- Header -->
        <div class="text-center mb-12 relative">
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 bg-gradient-to-br from-primary/20 to-primary/10 rounded-3xl mb-6">
                    <svg class="w-10 h-10 md:w-12 md:h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-white text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                <?php echo esc_html($pageTitle); ?>
            </h1>
            <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto">
                <?php echo esc_html($pageDescription); ?>
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center flex-1 mr-2">
                    <div class="flex items-center flex-1">
                        <div class="reservation-step-indicator flex items-center justify-center w-10 h-10 rounded-full border-2 border-primary bg-primary text-white transition-all duration-300 active" data-step="1">
                            <span class="step-number">1</span>
                            <svg class="step-check hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 bg-slate-700 reservation-progress-line" data-line="1"></div>
                    </div>
                </div>
                <div class="flex items-center flex-1 mr-2">
                    <div class="flex items-center flex-1">
                        <div class="reservation-step-indicator flex items-center justify-center w-10 h-10 rounded-full border-2 border-slate-600 text-slate-400 transition-all duration-300" data-step="2">
                            <span class="step-number">2</span>
                            <svg class="step-check hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 bg-slate-700 reservation-progress-line" data-line="2"></div>
                    </div>
                </div>
                <div class="flex items-center flex-1">
                    <div class="flex items-center flex-1">
                        <div class="reservation-step-indicator flex items-center justify-center w-10 h-10 rounded-full border-2 border-slate-600 text-slate-400 transition-all duration-300" data-step="3">
                            <span class="step-number">3</span>
                            <svg class="step-check hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-gradient-to-br from-slate-900/95 to-slate-800/95 rounded-3xl backdrop-blur-xl border border-slate-700/50 shadow-2xl p-8 md:p-12">
            <form id="reservation-form" class="reservation-multistep-form" method="POST" action="<?php echo site_url('forms/submit'); ?>">
                
                <input type="hidden" name="form_type" value="reservation">
                
                <!-- Honeypot -->
                <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off" style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">

                <!-- ADIM 1: Uçak Bileti -->
                <div class="reservation-step-content" data-step="1">
                    <div class="text-center mb-10">
                        <h2 class="text-white text-2xl md:text-3xl font-bold mb-3">Uçak Bileti Rezervasyonu</h2>
                        <p class="text-slate-400 text-base md:text-lg">Seyahat detaylarınızı girin</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Nereden -->
                        <div>
                            <label class="block text-white font-medium mb-2">
                                Nereden <span class="text-red-400">*</span>
                            </label>
                            <input type="text" 
                                   name="flight_from" 
                                   placeholder="Kalkış şehri"
                                   class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                   required>
                        </div>

                        <!-- Nereye -->
                        <div>
                            <label class="block text-white font-medium mb-2">
                                Nereye <span class="text-red-400">*</span>
                            </label>
                            <input type="text" 
                                   name="flight_to" 
                                   placeholder="Varış şehri"
                                   class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                   required>
                        </div>

                        <!-- Gidiş Tarihi -->
                        <div>
                            <label class="block text-white font-medium mb-2">
                                Gidiş Tarihi <span class="text-red-400">*</span>
                            </label>
                            <input type="date" 
                                   name="flight_departure_date" 
                                   class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                   required>
                        </div>

                        <!-- Dönüş Tarihi -->
                        <div>
                            <label class="block text-white font-medium mb-2">
                                Dönüş Tarihi
                            </label>
                            <input type="date" 
                                   name="flight_return_date" 
                                   class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                        </div>

                        <!-- Yolcu Sayısı -->
                        <div>
                            <label class="block text-white font-medium mb-2">
                                Yolcu Sayısı <span class="text-red-400">*</span>
                            </label>
                            <select name="flight_passengers" 
                                    class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                    required>
                                <option value="">Seçiniz...</option>
                                <option value="1">1 Yolcu</option>
                                <option value="2">2 Yolcu</option>
                                <option value="3">3 Yolcu</option>
                                <option value="4">4 Yolcu</option>
                                <option value="5">5+ Yolcu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Adım Göstergesi ve Buton -->
                    <div class="flex items-center justify-between mt-10 pt-6 border-t border-slate-700/50">
                        <div class="text-slate-500 text-sm">Adım <span class="text-primary font-bold">1</span> / 3</div>
                        <button type="button" 
                                class="reservation-next-btn px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30">
                            Devam Et →
                        </button>
                    </div>
                </div>

                <!-- ADIM 2: Otel Rezervasyonu -->
                <div class="reservation-step-content hidden" data-step="2">
                    <div class="text-center mb-10">
                        <h2 class="text-white text-2xl md:text-3xl font-bold mb-3">Otel Rezervasyonu</h2>
                        <p class="text-slate-400 text-base md:text-lg">Otel rezervasyonu yapmak ister misiniz?</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Otel Lazım mı? -->
                        <div>
                            <label class="block text-white font-medium mb-4 text-center">
                                Otel rezervasyonu yapmak istiyor musunuz?
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="reservation-hotel-card group relative flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-slate-700 bg-gradient-to-br from-slate-800/50 to-slate-900/50 cursor-pointer transition-all duration-300 hover:border-primary hover:shadow-xl hover:shadow-primary/20">
                                    <input type="radio" 
                                           name="hotel_needed" 
                                           value="yes"
                                           class="absolute opacity-0 pointer-events-none reservation-hotel-input"
                                           data-hotel-needed="yes">
                                    <div class="mb-4 text-5xl">🏨</div>
                                    <h3 class="text-white text-lg font-bold mb-2">Evet</h3>
                                    <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-primary flex items-center justify-center opacity-0 transition-all duration-300 check-icon">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </label>
                                <label class="reservation-hotel-card group relative flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-slate-700 bg-gradient-to-br from-slate-800/50 to-slate-900/50 cursor-pointer transition-all duration-300 hover:border-primary hover:shadow-xl hover:shadow-primary/20">
                                    <input type="radio" 
                                           name="hotel_needed" 
                                           value="no"
                                           class="absolute opacity-0 pointer-events-none reservation-hotel-input"
                                           data-hotel-needed="no"
                                           checked>
                                    <div class="mb-4 text-5xl">❌</div>
                                    <h3 class="text-white text-lg font-bold mb-2">Hayır</h3>
                                    <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-primary flex items-center justify-center opacity-0 transition-all duration-300 check-icon">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Otel Detayları (Evet seçilirse gösterilecek) -->
                        <div id="hotel-details" class="hidden space-y-6">
                            <!-- Şehir -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Otel Şehri <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       name="hotel_city" 
                                       placeholder="Otel şehri"
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            </div>

                            <!-- Giriş Tarihi -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Giriş Tarihi <span class="text-red-400">*</span>
                                </label>
                                <input type="date" 
                                       name="hotel_checkin" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            </div>

                            <!-- Çıkış Tarihi -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Çıkış Tarihi <span class="text-red-400">*</span>
                                </label>
                                <input type="date" 
                                       name="hotel_checkout" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            </div>

                            <!-- Oda Sayısı -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Oda Sayısı <span class="text-red-400">*</span>
                                </label>
                                <select name="hotel_rooms" 
                                        class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    <option value="">Seçiniz...</option>
                                    <option value="1">1 Oda</option>
                                    <option value="2">2 Oda</option>
                                    <option value="3">3 Oda</option>
                                    <option value="4">4+ Oda</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Adım Göstergesi ve Butonlar -->
                    <div class="flex items-center justify-between mt-10 pt-6 border-t border-slate-700/50">
                        <button type="button" 
                                class="reservation-prev-btn px-8 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all duration-300">
                            ← Geri
                        </button>
                        <div class="text-slate-500 text-sm">Adım <span class="text-primary font-bold">2</span> / 3</div>
                        <button type="button" 
                                class="reservation-next-btn px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30">
                            Devam Et →
                        </button>
                    </div>
                </div>

                <!-- ADIM 3: Araç Kiralama -->
                <div class="reservation-step-content hidden" data-step="3">
                    <div class="text-center mb-10">
                        <h2 class="text-white text-2xl md:text-3xl font-bold mb-3">Araç Kiralama</h2>
                        <p class="text-slate-400 text-base md:text-lg">Araç kiralama yapmak ister misiniz?</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Araç Lazım mı? -->
                        <div>
                            <label class="block text-white font-medium mb-4 text-center">
                                Araç kiralama yapmak istiyor musunuz?
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="reservation-car-card group relative flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-slate-700 bg-gradient-to-br from-slate-800/50 to-slate-900/50 cursor-pointer transition-all duration-300 hover:border-primary hover:shadow-xl hover:shadow-primary/20">
                                    <input type="radio" 
                                           name="car_needed" 
                                           value="yes"
                                           class="absolute opacity-0 pointer-events-none reservation-car-input"
                                           data-car-needed="yes">
                                    <div class="mb-4 text-5xl">🚗</div>
                                    <h3 class="text-white text-lg font-bold mb-2">Evet</h3>
                                    <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-primary flex items-center justify-center opacity-0 transition-all duration-300 check-icon">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </label>
                                <label class="reservation-car-card group relative flex flex-col items-center justify-center p-8 rounded-2xl border-2 border-slate-700 bg-gradient-to-br from-slate-800/50 to-slate-900/50 cursor-pointer transition-all duration-300 hover:border-primary hover:shadow-xl hover:shadow-primary/20">
                                    <input type="radio" 
                                           name="car_needed" 
                                           value="no"
                                           class="absolute opacity-0 pointer-events-none reservation-car-input"
                                           data-car-needed="no"
                                           checked>
                                    <div class="mb-4 text-5xl">❌</div>
                                    <h3 class="text-white text-lg font-bold mb-2">Hayır</h3>
                                    <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-primary flex items-center justify-center opacity-0 transition-all duration-300 check-icon">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Araç Detayları (Evet seçilirse gösterilecek) -->
                        <div id="car-details" class="hidden space-y-6">
                            <!-- Şehir -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Araç Alış Şehri <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       name="car_pickup_city" 
                                       placeholder="Araç alış şehri"
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            </div>

                            <!-- Alış Tarihi -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Alış Tarihi <span class="text-red-400">*</span>
                                </label>
                                <input type="date" 
                                       name="car_pickup_date" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            </div>

                            <!-- Teslim Tarihi -->
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Teslim Tarihi <span class="text-red-400">*</span>
                                </label>
                                <input type="date" 
                                       name="car_return_date" 
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            </div>
                        </div>

                        <!-- İletişim Bilgileri -->
                        <div class="pt-6 border-t border-slate-700/50 space-y-6">
                            <h3 class="text-white text-xl font-bold mb-4">İletişim Bilgileri</h3>
                            
                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Ad Soyad <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       name="contact_name" 
                                       placeholder="Adınız ve soyadınız"
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                       required>
                            </div>

                            <div>
                                <label class="block text-white font-medium mb-2">
                                    E-posta <span class="text-red-400">*</span>
                                </label>
                                <input type="email" 
                                       name="contact_email" 
                                       placeholder="E-posta adresiniz"
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                       required>
                            </div>

                            <div>
                                <label class="block text-white font-medium mb-2">
                                    Telefon <span class="text-red-400">*</span>
                                </label>
                                <input type="tel" 
                                       name="contact_phone" 
                                       placeholder="Telefon numaranız"
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Adım Göstergesi ve Butonlar -->
                    <div class="flex items-center justify-between mt-10 pt-6 border-t border-slate-700/50">
                        <button type="button" 
                                class="reservation-prev-btn px-8 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all duration-300">
                            ← Geri
                        </button>
                        <div class="text-slate-500 text-sm">Adım <span class="text-primary font-bold">3</span> / 3</div>
                        <button type="submit" 
                                class="px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30">
                            Rezervasyonu Tamamla ✓
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
/* Rezervasyon Form Stilleri */
.reservation-step-indicator.active {
    border-color: rgb(59 130 246);
    background-color: rgb(59 130 246);
    color: white;
}

.reservation-step-indicator.completed {
    border-color: rgb(34 197 94);
    background-color: rgb(34 197 94);
    color: white;
}

.reservation-step-indicator.completed .step-number {
    display: none;
}

.reservation-step-indicator.completed .step-check {
    display: block;
}

.reservation-progress-line.completed {
    background-color: rgb(34 197 94);
}

.reservation-hotel-card input:checked ~ .check-icon,
.reservation-car-card input:checked ~ .check-icon {
    opacity: 1;
}

.reservation-hotel-card input:checked,
.reservation-car-card input:checked {
    border-color: rgb(59 130 246);
    background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
}

.reservation-hotel-card:has(input:checked),
.reservation-car-card:has(input:checked) {
    border-color: rgb(59 130 246);
    box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservation-form');
    let currentStep = 1;
    const totalSteps = 3;

    // Adım göstergelerini güncelle
    function updateStepIndicators(step) {
        document.querySelectorAll('.reservation-step-indicator').forEach((indicator, index) => {
            const stepNum = index + 1;
            if (stepNum < step) {
                indicator.classList.add('completed');
                indicator.classList.remove('active');
                indicator.querySelector('.step-number').style.display = 'none';
                indicator.querySelector('.step-check').classList.remove('hidden');
            } else if (stepNum === step) {
                indicator.classList.add('active');
                indicator.classList.remove('completed');
                indicator.querySelector('.step-number').style.display = 'block';
                indicator.querySelector('.step-check').classList.add('hidden');
            } else {
                indicator.classList.remove('active', 'completed');
                indicator.querySelector('.step-number').style.display = 'block';
                indicator.querySelector('.step-check').classList.add('hidden');
            }
        });

        // Progress line'ları güncelle
        document.querySelectorAll('.reservation-progress-line').forEach((line, index) => {
            if (index + 1 < step) {
                line.classList.add('completed');
            } else {
                line.classList.remove('completed');
            }
        });
    }

    // Adım göster/gizle
    function showStep(step) {
        document.querySelectorAll('.reservation-step-content').forEach((content, index) => {
            if (index + 1 === step) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
        updateStepIndicators(step);
    }

    // İleri butonu
    document.querySelectorAll('.reservation-next-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    });

    // Geri butonu
    document.querySelectorAll('.reservation-prev-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Adım validasyonu
    function validateStep(step) {
        const stepContent = document.querySelector(`.reservation-step-content[data-step="${step}"]`);
        const requiredFields = stepContent.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('border-red-500');
                setTimeout(() => field.classList.remove('border-red-500'), 2000);
            } else {
                field.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            alert('Lütfen tüm zorunlu alanları doldurun.');
        }

        return isValid;
    }

    // Otel kartı seçimi
    document.querySelectorAll('.reservation-hotel-input').forEach(input => {
        input.addEventListener('change', function() {
            const hotelDetails = document.getElementById('hotel-details');
            if (this.value === 'yes') {
                hotelDetails.classList.remove('hidden');
                hotelDetails.querySelectorAll('[required]').forEach(field => {
                    field.setAttribute('required', 'required');
                });
            } else {
                hotelDetails.classList.add('hidden');
                hotelDetails.querySelectorAll('[required]').forEach(field => {
                    field.removeAttribute('required');
                });
            }
        });
    });

    // Araç kartı seçimi
    document.querySelectorAll('.reservation-car-input').forEach(input => {
        input.addEventListener('change', function() {
            const carDetails = document.getElementById('car-details');
            if (this.value === 'yes') {
                carDetails.classList.remove('hidden');
                carDetails.querySelectorAll('[required]').forEach(field => {
                    field.setAttribute('required', 'required');
                });
            } else {
                carDetails.classList.add('hidden');
                carDetails.querySelectorAll('[required]').forEach(field => {
                    field.removeAttribute('required');
                });
            }
        });
    });

    // Form gönderimi
    form.addEventListener('submit', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return false;
        }
    });

    // İlk adımı göster
    showStep(1);
});
</script>

<?php
$content = ob_get_clean();

// Layout'u include et
include $layoutPath;
?>
