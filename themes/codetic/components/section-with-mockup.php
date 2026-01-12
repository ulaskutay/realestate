<?php
/**
 * Codetic Theme - Section With Mockup Component
 * Modern teknolojik mockup gösterimi - temaya uygun
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

// Section ayarları
$sectionTitle = $section['title'] ?? 'Sektöre göre geliştirilebilir yapı';
$sectionDescription = $section['description'] ?? 'Her sektöre uygun olacak şekilde şekillendirilebilir, yapay zeka destekli, ölçeklenebilir web tasarım projeleri';
$reverseLayout = isset($settings['reverse_layout']) ? (bool)$settings['reverse_layout'] : false;

$sectionId = 'section-mockup-' . uniqid();
?>

<section class="relative py-24 md:py-48 bg-[#0a0a0f] overflow-hidden overflow-x-hidden" id="<?php echo esc_attr($sectionId); ?>">
    <!-- Top Mask - Dashboard Showcase'tan geçiş -->
    <div class="absolute top-0 left-0 right-0 h-32 md:h-48 pointer-events-none z-[5]" style="background: linear-gradient(180deg, rgba(10,10,15,0.6) 0%, rgba(10,10,15,0.35) 50%, rgba(10,10,15,0.15) 80%, transparent 100%);"></div>
    
    <!-- Animated Background -->
    <div class="absolute inset-0">
        <!-- Gradient Orbs -->
        <div class="absolute top-20 left-1/4 w-[600px] h-[600px] bg-violet-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-purple-600/5 rounded-full blur-[150px]"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.02)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%);"></div>
    </div>

    <div class="container max-w-[1220px] w-full px-6 md:px-10 relative z-10 mx-auto">
        <div class="grid grid-cols-1 gap-16 md:gap-12 w-full items-center <?php echo $reverseLayout ? 'md:grid-cols-2 md:grid-flow-col-dense' : 'md:grid-cols-2'; ?>">
            <!-- Text Content -->
            <div class="flex flex-col items-start gap-6 mt-10 md:mt-0 max-w-[546px] mx-auto md:mx-0 <?php echo $reverseLayout ? 'md:col-start-2' : ''; ?> section-mockup-text">
                <div class="space-y-4">
                    <h2 class="text-white text-3xl md:text-[40px] font-bold leading-tight md:leading-[53px]">
                        <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">
                            <?php 
                            // Allow only <br> tags, escape everything else
                            $titleHtml = strip_tags($sectionTitle, '<br><br/>');
                            // Escape the text content but preserve <br> tags
                            $titleParts = preg_split('/(<br\s*\/?>)/i', $titleHtml, -1, PREG_SPLIT_DELIM_CAPTURE);
                            $titleHtml = '';
                            foreach ($titleParts as $part) {
                                if (preg_match('/^<br\s*\/?>$/i', $part)) {
                                    $titleHtml .= $part;
                                } else {
                                    $titleHtml .= esc_html($part);
                                }
                            }
                            // Convert newlines to <br />
                            $titleHtml = nl2br($titleHtml);
                            echo $titleHtml;
                            ?>
                        </span>
                    </h2>
                </div>

                <p class="text-slate-400 text-base md:text-lg leading-relaxed">
                    <?php 
                    // Allow only <br> tags, escape everything else
                    $descHtml = strip_tags($sectionDescription, '<br><br/>');
                    // Escape the text content but preserve <br> tags
                    $descParts = preg_split('/(<br\s*\/?>)/i', $descHtml, -1, PREG_SPLIT_DELIM_CAPTURE);
                    $descHtml = '';
                    foreach ($descParts as $part) {
                        if (preg_match('/^<br\s*\/?>$/i', $part)) {
                            $descHtml .= $part;
                        } else {
                            $descHtml .= esc_html($part);
                        }
                    }
                    // Convert newlines to <br />
                    $descHtml = nl2br($descHtml);
                    echo $descHtml;
                    ?>
                </p>

                <!-- Feature Points -->
                <div class="flex flex-col gap-3 mt-4">
                    <div class="flex items-center gap-3 text-slate-300">
                        <div class="w-1.5 h-1.5 rounded-full bg-gradient-to-r from-blue-400 to-cyan-400"></div>
                        <span class="text-sm md:text-base">Özelleştirilebilir yapı</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-300">
                        <div class="w-1.5 h-1.5 rounded-full bg-gradient-to-r from-violet-400 to-purple-400"></div>
                        <span class="text-sm md:text-base">Yapay zeka destekli</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-300">
                        <div class="w-1.5 h-1.5 rounded-full bg-gradient-to-r from-emerald-400 to-teal-400"></div>
                        <span class="text-sm md:text-base">Ölçeklenebilir mimari</span>
                    </div>
                </div>
            </div>

            <!-- Code/UI Mockup Content -->
            <div class="relative mt-10 md:mt-0 mx-auto <?php echo $reverseLayout ? 'md:col-start-1' : ''; ?> w-full max-w-[500px] md:max-w-[600px] section-mockup-images">
                <!-- Decorative Background Glow -->
                <div class="absolute w-full h-[400px] md:h-[500px] bg-gradient-to-br from-blue-600/20 via-violet-600/20 to-purple-600/20 rounded-[32px] z-0 section-mockup-bg <?php echo $reverseLayout ? 'bottom-[10%] right-[-15%]' : 'top-[10%] left-[-15%]'; ?> blur-2xl"></div>

                <!-- Main Mockup Card -->
                <div class="relative w-full h-[450px] md:h-[550px] bg-gradient-to-br from-slate-900/90 to-slate-800/90 rounded-[32px] backdrop-blur-xl border border-slate-700/50 z-10 overflow-hidden section-mockup-main shadow-2xl">
                    <!-- Card Header -->
                    <div class="absolute top-0 left-0 right-0 h-12 bg-slate-800/50 backdrop-blur-sm border-b border-slate-700/50 flex items-center gap-2 px-4 z-20">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                        </div>
                        <div class="flex-1 text-center">
                            <span class="text-xs text-slate-400 font-mono">code.tsx</span>
                        </div>
                    </div>

                    <!-- Code Content -->
                    <div class="absolute inset-0 pt-12 p-6 overflow-hidden overflow-x-hidden">
                        <div class="h-full overflow-y-auto overflow-x-hidden custom-scrollbar">
                            <pre class="text-xs md:text-sm font-mono text-slate-300 leading-relaxed whitespace-pre-wrap break-words overflow-wrap-anywhere"><code><span class="text-purple-400">export</span> <span class="text-blue-400">const</span> <span class="text-cyan-400">CustomizableSystem</span> = <span class="text-purple-400">()</span> => {
  <span class="text-purple-400">const</span> <span class="text-slate-200">[sector, setSector]</span> = <span class="text-cyan-400">useState</span>(<span class="text-green-400">'auto'</span>);
  
  <span class="text-purple-400">return</span> (
    <span class="text-slate-400">&lt;</span><span class="text-cyan-400">div</span> <span class="text-yellow-400">className</span>=<span class="text-green-400">"flexible-structure"</span><span class="text-slate-400">&gt;</span>
      <span class="text-slate-400">&lt;</span><span class="text-cyan-400">AIEngine</span> 
        <span class="text-yellow-400">sector</span>={<span class="text-slate-200">{sector}</span>}
        <span class="text-yellow-400">scalable</span>={<span class="text-blue-400">true</span>}
      <span class="text-slate-400">/&gt;</span>
      <span class="text-slate-400">&lt;</span><span class="text-cyan-400">WebDesign</span> 
        <span class="text-yellow-400">customizable</span>={<span class="text-blue-400">true</span>}
        <span class="text-yellow-400">responsive</span>={<span class="text-blue-400">true</span>}
      <span class="text-slate-400">/&gt;</span>
    <span class="text-slate-400">&lt;/</span><span class="text-cyan-400">div</span><span class="text-slate-400">&gt;</span>
  );
};</code></pre>
                        </div>
                    </div>

                    <!-- Glowing Accent -->
                    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-blue-600/20 via-transparent to-transparent pointer-events-none"></div>
                </div>

                <!-- Floating UI Elements -->
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl backdrop-blur-sm border border-blue-400/30 z-20 animate-pulse-slow">
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-blue-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Decorative bottom gradient -->
    <div class="absolute w-full h-px bottom-0 left-0 z-0" style="background: radial-gradient(50% 50% at 50% 50%, rgba(59,130,246,0.3) 0%, rgba(59,130,246,0) 100%);"></div>
</section>

<style>
/* Section With Mockup Styles */
#<?php echo esc_attr($sectionId); ?> .section-mockup-text {
    opacity: 0;
    transform: translateY(50px);
    transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-text.visible {
    opacity: 1;
    transform: translateY(0);
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-images {
    opacity: 0;
    transform: translateY(50px);
    transition: opacity 0.7s ease-out 0.2s, transform 0.7s ease-out 0.2s;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-images.visible {
    opacity: 1;
    transform: translateY(0);
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-bg {
    transition: transform 1.2s ease-out, opacity 1.2s ease-out;
    opacity: 0;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-bg.visible {
    transform: <?php echo $reverseLayout ? 'translateY(-20px) translateX(10px)' : 'translateY(-30px) translateX(-10px)'; ?>;
    opacity: 1;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-main {
    transition: transform 1.2s ease-out 0.1s, box-shadow 1.2s ease-out 0.1s;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-main.visible {
    transform: <?php echo $reverseLayout ? 'translateY(20px)' : 'translateY(30px)'; ?>;
    box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
}

/* Custom Scrollbar */
#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 3px;
}

#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 3px;
}

#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.7);
}

/* Prevent horizontal scroll */
#<?php echo esc_attr($sectionId); ?> {
    overflow-x: hidden;
    max-width: 100vw;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-main {
    overflow-x: hidden;
    max-width: 100%;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-main > div {
    overflow-x: hidden;
    max-width: 100%;
}

#<?php echo esc_attr($sectionId); ?> .section-mockup-main pre,
#<?php echo esc_attr($sectionId); ?> .section-mockup-main code {
    overflow-x: hidden;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    max-width: 100%;
    width: 100%;
}

/* Pulse Animation */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 0.4;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #<?php echo esc_attr($sectionId); ?> {
        padding-top: 4rem;
        padding-bottom: 4rem;
    }
    
    #<?php echo esc_attr($sectionId); ?> .section-mockup-bg,
    #<?php echo esc_attr($sectionId); ?> .section-mockup-main {
        transition: none;
    }
    
    #<?php echo esc_attr($sectionId); ?> .section-mockup-bg.visible,
    #<?php echo esc_attr($sectionId); ?> .section-mockup-main.visible {
        transform: none;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    const section = document.getElementById('<?php echo esc_js($sectionId); ?>');
    if (!section) return;
    
    const textElement = section.querySelector('.section-mockup-text');
    const imagesElement = section.querySelector('.section-mockup-images');
    const bgElement = section.querySelector('.section-mockup-bg');
    const mainElement = section.querySelector('.section-mockup-main');
    
    // Intersection Observer for scroll animations - Optimized
    const observerOptions = {
        threshold: 0.1, // Reduced threshold for earlier trigger
        rootMargin: '50px' // Start animation before element enters viewport
    };
    
    // Use requestAnimationFrame to batch DOM updates
    let pendingUpdates = [];
    let rafScheduled = false;
    
    function processPendingUpdates() {
        pendingUpdates.forEach(({ element, className }) => {
            if (element) {
                element.classList.add(className);
            }
        });
        pendingUpdates = [];
        rafScheduled = false;
    }
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Batch all DOM updates using requestAnimationFrame
                if (textElement) {
                    pendingUpdates.push({ element: textElement, className: 'visible' });
                }
                
                // Images animation with delay - use requestAnimationFrame instead of setTimeout
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (imagesElement) {
                            imagesElement.classList.add('visible');
                        }
                        if (bgElement) {
                            bgElement.classList.add('visible');
                        }
                        if (mainElement) {
                            mainElement.classList.add('visible');
                        }
                    });
                });
                
                if (!rafScheduled) {
                    rafScheduled = true;
                    requestAnimationFrame(processPendingUpdates);
                }
                
                // Unobserve after animation
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    observer.observe(section);
    
    // Cleanup
    window.addEventListener('beforeunload', () => {
        observer.disconnect();
    });
})();
</script>
