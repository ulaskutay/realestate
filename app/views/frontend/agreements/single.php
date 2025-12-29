<?php
/**
 * Tekil Sözleşme Sayfası
 * Gizlilik Politikası, KVKK, Kullanım Şartları vb.
 */

// Sözleşme türü renkleri ve ikonları
$typeStyles = [
    'privacy' => ['gradient' => 'from-purple-600 to-indigo-600', 'icon' => 'shield'],
    'kvkk' => ['gradient' => 'from-blue-600 to-cyan-600', 'icon' => 'verified_user'],
    'terms' => ['gradient' => 'from-orange-500 to-amber-500', 'icon' => 'description'],
    'cookies' => ['gradient' => 'from-teal-500 to-green-500', 'icon' => 'cookie'],
    'other' => ['gradient' => 'from-gray-600 to-gray-700', 'icon' => 'gavel']
];

$agreementType = $agreement['type'] ?? 'other';
$style = $typeStyles[$agreementType] ?? $typeStyles['other'];
$bgGradient = $style['gradient'];
$typeIcon = $style['icon'];
?>

<!-- Sözleşme Header -->
<section class="bg-gradient-to-r <?php echo $bgGradient; ?> py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <!-- İkon -->
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-6">
                <span class="material-symbols-outlined text-3xl text-white"><?php echo $typeIcon; ?></span>
            </div>
            
            <!-- Başlık -->
            <h1 class="text-3xl md:text-5xl font-bold text-white leading-tight">
                <?php echo esc_html($agreement['title']); ?>
            </h1>
        </div>
    </div>
</section>

<!-- İçerik -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- İçerik Kartı -->
            <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- İçerik -->
                <div class="p-8 md:p-12">
                    <div class="prose prose-lg max-w-none 
                                prose-headings:text-gray-800 prose-headings:font-bold
                                prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4 prose-h2:pb-2 prose-h2:border-b prose-h2:border-gray-200
                                prose-h3:text-xl prose-h3:mt-6 prose-h3:mb-3
                                prose-p:text-gray-600 prose-p:leading-relaxed
                                prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline
                                prose-ul:my-4 prose-li:text-gray-600
                                prose-strong:text-gray-800">
                        <?php 
                        // Yer tutucuları site ayarlarıyla değiştir
                        echo process_agreement_content($agreement['content']); 
                        ?>
                    </div>
                </div>
                
                <!-- Alt Bilgi -->
                <div class="bg-gray-50 px-8 md:px-12 py-6 border-t border-gray-100">
                    <div class="flex items-center justify-end">
                        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm font-medium">
                            <span class="material-symbols-outlined text-base">print</span>
                            Yazdır
                        </button>
                    </div>
                </div>
            </article>
            
            <?php
            // Diğer yayındaki sözleşmeleri getir
            $otherAgreements = [];
            try {
                require_once __DIR__ . '/../../../models/Agreement.php';
                $agreementModel = new Agreement();
                $allPublished = $agreementModel->getPublished();
                // Mevcut sözleşmeyi listeden çıkar
                $otherAgreements = array_filter($allPublished, function($a) use ($agreement) {
                    return $a['id'] != $agreement['id'];
                });
            } catch (Exception $e) {
                $otherAgreements = [];
            }
            
            // Tür renkleri ve ikonları
            $linkStyles = [
                'privacy' => ['bg' => 'bg-purple-50 hover:bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'shield'],
                'kvkk' => ['bg' => 'bg-blue-50 hover:bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'verified_user'],
                'terms' => ['bg' => 'bg-orange-50 hover:bg-orange-100', 'text' => 'text-orange-700', 'icon' => 'description'],
                'cookies' => ['bg' => 'bg-teal-50 hover:bg-teal-100', 'text' => 'text-teal-700', 'icon' => 'cookie'],
                'other' => ['bg' => 'bg-gray-50 hover:bg-gray-100', 'text' => 'text-gray-700', 'icon' => 'gavel']
            ];
            
            if (!empty($otherAgreements)):
            ?>
            <!-- Hızlı Linkler - Sadece yayındaki sözleşmeler -->
            <div class="mt-8 bg-white rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Diğer Yasal Metinler</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($otherAgreements as $other): 
                        $style = $linkStyles[$other['type']] ?? $linkStyles['other'];
                    ?>
                    <a href="/sozlesmeler/<?php echo esc_attr($other['slug']); ?>" class="flex items-center gap-2 p-3 <?php echo $style['bg']; ?> <?php echo $style['text']; ?> rounded-lg transition-colors text-sm font-medium">
                        <span class="material-symbols-outlined"><?php echo $style['icon']; ?></span>
                        <?php echo esc_html($other['title']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</section>

<style>
@media print {
    header, footer, nav, aside, .no-print {
        display: none !important;
    }
    section:first-of-type {
        background: #f3f4f6 !important;
        color: #1f2937 !important;
        padding: 1rem !important;
    }
    section:first-of-type h1 {
        color: #1f2937 !important;
    }
    article {
        box-shadow: none !important;
    }
}
</style>

