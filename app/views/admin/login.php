<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? esc_html($title) : 'Admin Giriş'; ?></title>
    
    <!-- Favicon -->
    <?php 
    $favicon = get_site_favicon();
    if (!empty($favicon)): ?>
    <link rel="icon" type="image/png" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #22d3ee;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --input-bg: rgba(255, 255, 255, 0.06);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus: rgba(99, 102, 241, 0.5);
            --error-bg: rgba(239, 68, 68, 0.15);
            --error-border: rgba(239, 68, 68, 0.5);
            --error-text: #fca5a5;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            background: #0a0a0f;
        }
        
        /* Video Arkaplan */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(10, 10, 15, 0.7) 0%, rgba(30, 20, 50, 0.6) 100%);
            z-index: 1;
        }
        
        /* Animasyonlu Parçacıklar */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(99, 102, 241, 0.6);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 20s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 18s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 22s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 1s; animation-duration: 16s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 3s; animation-duration: 19s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; animation-duration: 21s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 2.5s; animation-duration: 17s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 4.5s; animation-duration: 23s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 1.5s; animation-duration: 20s; }
        .particle:nth-child(10) { left: 15%; animation-delay: 3.5s; animation-duration: 18s; }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
                transform: translateY(80vh) scale(1);
            }
            90% {
                opacity: 1;
                transform: translateY(10vh) scale(1);
            }
            100% {
                transform: translateY(-10vh) scale(0);
                opacity: 0;
            }
        }
        
        /* Giriş Kartı */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
        }
        
        .login-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Logo / Başlık */
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-logo {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.3);
            padding: 8px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .login-logo svg {
            width: 32px;
            height: 32px;
            fill: white;
        }
        
        .login-header h1 {
            color: var(--text-primary);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: var(--text-secondary);
            font-size: 15px;
            font-weight: 400;
        }
        
        /* Form */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            position: relative;
        }
        
        .form-group label {
            display: block;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper .icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            fill: var(--text-secondary);
            transition: fill 0.3s ease;
            pointer-events: none;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        /* Input'a değer girildiğinde beyaz arka plan */
        .form-group input:not(:placeholder-shown),
        .form-group input:focus,
        .form-group input.has-value {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.95);
            color: #1f2937;
            box-shadow: 0 0 0 4px var(--input-focus);
        }
        
        /* Input'a değer girildiğinde placeholder rengi */
        .form-group input:not(:placeholder-shown)::placeholder,
        .form-group input.has-value::placeholder {
            color: rgba(31, 41, 55, 0.5);
        }
        
        .form-group input:focus ~ .icon,
        .input-wrapper:focus-within .icon,
        .form-group input:not(:placeholder-shown) ~ .icon,
        .form-group input.has-value ~ .icon {
            fill: var(--primary);
        }
        
        /* Şifre Göster/Gizle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: var(--text-secondary);
            transition: fill 0.3s ease;
        }
        
        .password-toggle:hover svg {
            fill: var(--text-primary);
        }
        
        /* Buton */
        .btn-login {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.7);
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-login .btn-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .btn-login.loading .btn-text { display: none; }
        .btn-login.loading .spinner { display: block; }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Hata Mesajı */
        .error-message {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease-in-out;
        }
        
        .error-message svg {
            width: 20px;
            height: 20px;
            fill: var(--error-text);
            flex-shrink: 0;
        }
        
        .error-message span {
            color: var(--error-text);
            font-size: 14px;
            font-weight: 500;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        /* Alt Link */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 32px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
            transition: transform 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--text-primary);
        }
        
        .back-link:hover svg {
            transform: translateX(-4px);
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 36px 24px;
                border-radius: 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .form-group input {
                padding: 14px 14px 14px 44px;
            }
            
            .btn-login {
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>
    <video id="background-video" class="video-background" autoplay loop muted playsinline preload="auto">
        <source src="/public/uploads/videos/background-video.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    
    <!-- Animasyonlu Parçacıklar -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="login-logo">
                    <?php 
                    $logoUrl = get_site_favicon();
                    if (empty($logoUrl)) {
                        // Varsayılan logo yolu
                        $logoUrl = '/public/uploads/Logo/codetic-favicon.png';
                    }
                    ?>
                    <img src="<?php echo esc_url($logoUrl); ?>" alt="Codetic Logo">
                </div>
                <h1>Hoş Geldiniz</h1>
                <p>Yönetim paneline erişmek için giriş yapın</p>
            </div>
            
            <?php if (isset($error) && $error): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    <span><?php echo esc_html($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı veya E-posta</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" placeholder="admin@example.com" required autofocus>
                        <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                        </svg>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg id="eye-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="submitBtn">
                    <span class="btn-content">
                        <span class="btn-text">Giriş Yap</span>
                        <span class="spinner"></span>
                    </span>
                </button>
            </form>
            
            <a href="<?php echo site_url(); ?>" class="back-link">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Ana Sayfaya Dön
            </a>
        </div>
    </div>
    
    <script>
        // Şifre göster/gizle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }
        
        // Form gönderildiğinde loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
        
        // Video oynatma
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('background-video');
            if (!video) return;
            
            video.addEventListener('loadeddata', function() {
                video.play().catch(function() {
                    const playOnInteraction = function() {
                        video.play();
                        document.removeEventListener('click', playOnInteraction);
                        document.removeEventListener('touchstart', playOnInteraction);
                    };
                    document.addEventListener('click', playOnInteraction, { once: true });
                    document.addEventListener('touchstart', playOnInteraction, { once: true });
                });
            });
            
            video.play().catch(function() {});
            
            video.addEventListener('ended', function() {
                video.currentTime = 0;
                video.play();
            });
        });
        
        // Input animasyonları ve beyaz arka plan kontrolü
        document.querySelectorAll('.form-group input').forEach(input => {
            // Input değer değişikliklerini dinle
            function updateInputStyle() {
                if (input.value && input.value.trim() !== '') {
                    input.classList.add('has-value');
                } else {
                    input.classList.remove('has-value');
                }
            }
            
            input.addEventListener('input', updateInputStyle);
            input.addEventListener('change', updateInputStyle);
            
            // Sayfa yüklendiğinde kontrol et
            updateInputStyle();
            
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
