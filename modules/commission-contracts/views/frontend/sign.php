<?php
/**
 * Karşı taraf imza sayfası - STANDALONE (Tema header/footer KULLANILMAZ)
 * Token ile erişilir; sözleşme PDF formatında gösterilir, canvas ile imza alınır.
 */
$contract = $contract ?? null;
$token = $token ?? '';
$contractHtml = $contractHtml ?? '';
$error = $error ?? null;
$alreadySigned = $alreadySigned ?? false;
$success = $success ?? false;

$pageTitle = 'Sözleşme İmzası';
$clientName = '';
if ($contract && !empty($contract['client_name'])) {
    $clientName = $contract['client_name'];
    $pageTitle = 'Sözleşme İmzası – ' . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8');
}

$downloadUrl = '';
if ($token && function_exists('site_url')) {
    $downloadUrl = site_url('sozlesme-imza/' . $token . '/pdf');
}

$siteLogo = '';
$siteName = 'Sözleşme Sistemi';
if (function_exists('get_option')) {
    $siteLogo = get_option('site_logo', '');
    $siteSettings = get_option('site_settings', []);
    $siteName = $siteSettings['company_name'] ?? ($siteSettings['site_name'] ?? 'Sözleşme Sistemi');
    if ($siteLogo && strpos($siteLogo, 'http') !== 0 && function_exists('site_url')) {
        $siteLogo = site_url(ltrim($siteLogo, '/'));
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { font-size: 16px; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #1f2937;
            line-height: 1.6;
        }
        .sign-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .sign-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .sign-header-logo {
            max-height: 56px;
            max-width: 200px;
            margin-bottom: 0.5rem;
        }
        .sign-header-title {
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255,255,255,0.95);
            margin: 0;
        }
        .sign-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .sign-card-header {
            background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%);
            color: #fff;
            padding: 1.25rem 1.5rem;
        }
        .sign-card-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }
        .sign-card-header p {
            font-size: 0.875rem;
            margin: 0;
            opacity: 0.85;
        }
        .sign-card-body {
            padding: 1.5rem;
        }
        .success-banner {
            background: linear-gradient(90deg, #059669 0%, #10b981 100%);
        }
        .contract-section {
            margin-bottom: 1.5rem;
        }
        .contract-section-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .contract-section-label svg {
            width: 16px;
            height: 16px;
        }
        .contract-preview {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding: 0;
        }
        .contract-preview-inner {
            padding: 1.5rem;
            font-size: 12px;
            line-height: 1.5;
            color: #374151;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .contract-preview-inner img {
            max-width: 140px !important;
            max-height: 40px !important;
        }
        .contract-preview-inner table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.5rem 0;
        }
        .contract-preview-inner th,
        .contract-preview-inner td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }
        .contract-preview-inner th {
            background: #f3f4f6;
            font-weight: 600;
        }
        .signature-section {
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 12px;
            padding: 1.25rem;
            margin-top: 1.5rem;
        }
        .signature-section-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #166534;
            margin: 0 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .signature-section-title svg {
            width: 20px;
            height: 20px;
        }
        .signature-canvas-container {
            background: #fff;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            overflow: hidden;
            max-width: 500px;
        }
        .signature-canvas-container canvas {
            display: block;
            width: 100%;
            height: 140px;
            cursor: crosshair;
            touch-action: none;
        }
        .signature-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn svg {
            width: 18px;
            height: 18px;
        }
        .btn-primary {
            background: linear-gradient(90deg, #059669 0%, #10b981 100%);
            color: #fff;
            box-shadow: 0 4px 14px 0 rgba(16,185,129,0.39);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16,185,129,0.5);
        }
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .btn-secondary {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .btn-secondary:hover {
            background: #f9fafb;
        }
        .btn-download {
            background: linear-gradient(90deg, #1e40af 0%, #3b82f6 100%);
            color: #fff;
            box-shadow: 0 4px 14px 0 rgba(59,130,246,0.39);
        }
        .btn-download:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59,130,246,0.5);
        }
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 0.875rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .error-alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .success-message {
            text-align: center;
            padding: 1rem 0;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .success-icon svg {
            width: 32px;
            height: 32px;
            color: #fff;
        }
        .success-message h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #166534;
            margin: 0 0 0.5rem;
        }
        .success-message p {
            color: #6b7280;
            margin: 0;
        }
        .download-section {
            background: #eff6ff;
            border-radius: 12px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            text-align: center;
        }
        .download-section p {
            margin: 0 0 1rem;
            color: #1e40af;
            font-weight: 500;
        }
        @media (max-width: 640px) {
            .sign-wrapper { padding: 1rem 0.75rem; }
            .sign-card-body { padding: 1rem; }
            .contract-preview { max-height: 350px; }
            .signature-canvas-container { max-width: 100%; }
            .signature-section { padding: 1rem; }
            .signature-actions .btn { flex: 1 1 100%; min-width: 0; }
            .btn { padding: 0.625rem 1rem; font-size: 0.875rem; }
        }
    </style>
</head>
<body>
<div class="sign-wrapper">
    <header class="sign-header">
        <?php if ($siteLogo): ?>
            <img src="<?php echo htmlspecialchars($siteLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" class="sign-header-logo">
        <?php endif; ?>
        <p class="sign-header-title"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></p>
    </header>

    <div class="sign-card">
        <?php if ($success): ?>
            <div class="sign-card-header success-banner">
                <h1>İmzanız Başarıyla Alındı</h1>
                <p>Sözleşme imzalama işlemi tamamlandı.</p>
            </div>
            <div class="sign-card-body">
                <div class="success-message">
                    <div class="success-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2>Teşekkür Ederiz</h2>
                    <p>Sözleşmeyi başarıyla imzaladınız. Aşağıdan imzalı kopyanızı indirebilirsiniz.</p>
                </div>
                <?php if ($contractHtml !== ''): ?>
                    <div class="contract-section">
                        <div class="contract-section-label">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            İmzalı Sözleşmeniz
                        </div>
                        <div class="contract-preview">
                            <div class="contract-preview-inner"><?php echo $contractHtml; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($downloadUrl): ?>
                    <div class="download-section">
                        <p>Sözleşmenizi bilgisayarınıza kaydedin</p>
                        <a href="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-download" download>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            PDF Olarak İndir
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($alreadySigned): ?>
            <div class="sign-card-header success-banner">
                <h1>Sözleşme Zaten İmzalanmış</h1>
                <p>Bu sözleşme tarafınızca daha önce imzalanmıştır.</p>
            </div>
            <div class="sign-card-body">
                <div class="success-message">
                    <div class="success-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2>İmza Tamamlandı</h2>
                    <p>Aşağıda imzalı sözleşmenizi görüntüleyebilir ve PDF olarak indirebilirsiniz.</p>
                </div>
                <?php if ($contractHtml !== ''): ?>
                    <div class="contract-section">
                        <div class="contract-section-label">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            İmzalı Sözleşme
                        </div>
                        <div class="contract-preview">
                            <div class="contract-preview-inner"><?php echo $contractHtml; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($downloadUrl): ?>
                    <div class="download-section">
                        <p>Sözleşmenizi bilgisayarınıza kaydedin</p>
                        <a href="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-download" download>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            PDF Olarak İndir
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($contract): ?>
            <div class="sign-card-header">
                <h1>Sözleşme İmzalama</h1>
                <p><?php echo $clientName ? 'Sayın ' . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . ', lütfen aşağıdaki sözleşmeyi inceleyip imzalayınız.' : 'Lütfen aşağıdaki sözleşmeyi inceleyip imzalayınız.'; ?></p>
            </div>
            <div class="sign-card-body">
                <?php if ($error): ?>
                    <div class="error-alert">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($contractHtml !== ''): ?>
                    <div class="contract-section">
                        <div class="contract-section-label">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Sözleşme Metni — İmzalamadan Önce Lütfen Okuyunuz
                        </div>
                        <div class="contract-preview">
                            <div class="contract-preview-inner"><?php echo $contractHtml; ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo function_exists('site_url') ? htmlspecialchars(site_url('sozlesme-imza/' . $token), ENT_QUOTES, 'UTF-8') : ''; ?>" method="POST" id="sign-form">
                    <input type="hidden" name="signature_data" id="signature-data-input" value="">
                    
                    <div class="signature-section">
                        <h3 class="signature-section-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            İmzanızı Çizin
                        </h3>
                        <div class="signature-canvas-container">
                            <canvas id="signature-canvas" width="500" height="140"></canvas>
                        </div>
                        <div class="signature-actions">
                            <button type="button" id="signature-clear" class="btn btn-secondary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Temizle
                            </button>
                            <button type="submit" id="sign-submit" class="btn btn-primary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                İmzala ve Gönder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="sign-card-header">
                <h1>Geçersiz Link</h1>
                <p>Bu imza linki geçersiz veya süresi dolmuş.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if ($contract && !$alreadySigned && !$success): ?>
<script>
(function() {
    var canvas = document.getElementById('signature-canvas');
    var signInput = document.getElementById('signature-data-input');
    var signForm = document.getElementById('sign-form');
    var signSubmit = document.getElementById('sign-submit');
    var signClear = document.getElementById('signature-clear');
    
    if (!canvas || !signInput || !signForm) return;
    
    var ctx = canvas.getContext('2d');
    var drawing = false;
    var lastX = 0, lastY = 0;
    var hasDrawn = false;
    var container = canvas.closest('.signature-canvas-container') || canvas.parentElement;
    
    function resizeCanvas() {
        if (!container) return;
        var w = container.clientWidth;
        var h = Math.max(120, Math.round((140 / 500) * w));
        if (canvas.width !== w || canvas.height !== h) {
            canvas.width = w;
            canvas.height = h;
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, w, h);
        }
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }
    
    function start(e) {
        e.preventDefault();
        drawing = true;
        hasDrawn = true;
        var p = getPos(e);
        lastX = p.x;
        lastY = p.y;
    }
    
    function draw(e) {
        e.preventDefault();
        if (!drawing) return;
        var p = getPos(e);
        ctx.strokeStyle = '#1e3a8a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        lastX = p.x;
        lastY = p.y;
    }
    
    function end() {
        drawing = false;
    }
    
    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseout', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', end);
    
    if (signClear) {
        signClear.addEventListener('click', function() {
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        });
    }
    
    signForm.addEventListener('submit', function(e) {
        if (!hasDrawn) {
            e.preventDefault();
            alert('Lütfen imza alanına imzanızı çizin.');
            return false;
        }
        signInput.value = canvas.toDataURL('image/png');
        if (!signInput.value || signInput.value.length < 500) {
            e.preventDefault();
            alert('Lütfen imza alanına imzanızı çizin.');
            return false;
        }
        if (signSubmit) {
            signSubmit.disabled = true;
            signSubmit.innerHTML = '<svg class="animate-spin" fill="none" viewBox="0 0 24 24" style="width:18px;height:18px;animation:spin 1s linear infinite"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity:0.75"></path></svg> Gönderiliyor...';
        }
    });
})();
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
<?php endif; ?>
</body>
</html>
