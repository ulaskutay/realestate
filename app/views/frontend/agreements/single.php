<?php
/**
 * Tekil Sözleşme Sayfası
 */
?>

<style>
/* Agreement Page - Dark Theme Compatible */
.agreement-page {
    background: #0a0a0f;
    min-height: 100vh;
    padding-top: 120px;
}

.agreement-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 24px 80px;
}

.agreement-header {
    margin-bottom: 48px;
    padding-bottom: 32px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.agreement-header h1 {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 2.5rem;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
    line-height: 1.3;
    letter-spacing: -0.02em;
}

.agreement-content {
    color: rgba(255, 255, 255, 0.8);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 16px;
    line-height: 1.8;
}

.agreement-content h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #ffffff;
    margin: 48px 0 20px 0;
    line-height: 1.4;
}

.agreement-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #ffffff;
    margin: 40px 0 16px 0;
    line-height: 1.4;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.agreement-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
    margin: 32px 0 12px 0;
    line-height: 1.4;
}

.agreement-content p {
    margin: 0 0 20px 0;
    color: rgba(255, 255, 255, 0.75);
}

.agreement-content ul,
.agreement-content ol {
    margin: 20px 0;
    padding-left: 24px;
}

.agreement-content li {
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.75);
}

.agreement-content a {
    color: #60a5fa;
    text-decoration: none;
    transition: color 0.2s;
}

.agreement-content a:hover {
    color: #93c5fd;
    text-decoration: underline;
}

.agreement-content strong {
    font-weight: 600;
    color: #ffffff;
}

.agreement-content blockquote {
    border-left: 3px solid rgba(139, 92, 246, 0.5);
    padding-left: 20px;
    margin: 24px 0;
    color: rgba(255, 255, 255, 0.6);
    font-style: italic;
}

.agreement-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 24px 0;
}

.agreement-content th,
.agreement-content td {
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 12px 16px;
    text-align: left;
}

.agreement-content th {
    background: rgba(255, 255, 255, 0.05);
    font-weight: 600;
    color: #ffffff;
}

.agreement-footer {
    margin-top: 48px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: flex-end;
}

.agreement-print-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.agreement-print-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.25);
}

@media (max-width: 768px) {
    .agreement-page {
        padding-top: 100px;
    }
    
    .agreement-container {
        padding: 0 20px 60px;
    }
    
    .agreement-header h1 {
        font-size: 1.75rem;
    }
    
    .agreement-content {
        font-size: 15px;
    }
    
    .agreement-content h1 {
        font-size: 1.5rem;
        margin: 36px 0 16px 0;
    }
    
    .agreement-content h2 {
        font-size: 1.25rem;
        margin: 32px 0 14px 0;
    }
    
    .agreement-content h3 {
        font-size: 1.125rem;
        margin: 28px 0 12px 0;
    }
}

@media print {
    .agreement-page {
        background: #fff !important;
        padding-top: 0 !important;
    }
    
    .agreement-header h1 {
        color: #000 !important;
    }
    
    .agreement-content,
    .agreement-content p,
    .agreement-content li {
        color: #333 !important;
    }
    
    .agreement-content h1,
    .agreement-content h2,
    .agreement-content h3,
    .agreement-content strong {
        color: #000 !important;
    }
    
    .agreement-content h2 {
        border-bottom-color: #ddd !important;
    }
    
    .agreement-header {
        border-bottom-color: #ddd !important;
    }
    
    .agreement-footer {
        display: none !important;
    }
}
</style>

<div class="agreement-page">
    <div class="agreement-container">
        <header class="agreement-header">
            <h1><?php echo esc_html($agreement['title']); ?></h1>
        </header>
        
        <article class="agreement-content">
            <?php echo process_agreement_content($agreement['content']); ?>
        </article>
        
        <footer class="agreement-footer">
            <button onclick="window.print()" class="agreement-print-btn">
                <span class="material-symbols-outlined" style="font-size: 18px;">print</span>
                Yazdır
            </button>
        </footer>
    </div>
</div>
