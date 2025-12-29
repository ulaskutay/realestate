/**
 * Privacy-Friendly Analytics Tracker
 * Hafif ve GDPR uyumlu sayfa tracking sistemi
 */

(function() {
    'use strict';
    
    // Tracking API endpoint
    const TRACK_ENDPOINT = '/api/track';
    
    // Sayfa yükleme zamanı
    const pageLoadTime = Date.now();
    
    // Tracking verisi
    let trackingData = null;
    
    /**
     * Sayfa görüntülenmesini track et
     */
    function trackPageView() {
        trackingData = {
            page_url: window.location.href,
            page_title: document.title,
            referrer: document.referrer || null,
            user_agent: navigator.userAgent,
            timestamp: Date.now()
        };
        
        // API'ye gönder
        sendTrackingData(trackingData);
    }
    
    /**
     * Sayfa kalma süresini track et
     */
    function trackVisitDuration() {
        const duration = Math.floor((Date.now() - pageLoadTime) / 1000); // Saniye cinsinden
        
        if (duration < 1) return; // 1 saniyeden az kalındıysa gönderme
        
        // Duration'ı güncelle
        sendDurationUpdate(duration);
    }
    
    /**
     * Tracking verisini API'ye gönder
     */
    function sendTrackingData(data) {
        // Beacon API kullan (sayfa kapatılırken bile çalışır)
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
            navigator.sendBeacon(TRACK_ENDPOINT, blob);
        } else {
            // Fallback: fetch API
            fetch(TRACK_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                keepalive: true
            }).catch(function() {});
        }
    }
    
    /**
     * Ziyaret süresini güncelle
     */
    function sendDurationUpdate(duration) {
        if (!trackingData) return;
        
        const data = {
            page_url: trackingData.page_url,
            duration: duration
        };
        
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
            navigator.sendBeacon(TRACK_ENDPOINT + '/duration', blob);
        } else {
            fetch(TRACK_ENDPOINT + '/duration', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                keepalive: true
            }).catch(function() {});
        }
    }
    
    // ==================== EVENT LISTENERS ====================
    
    // Sayfa yüklendiğinde track et
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackPageView);
    } else {
        trackPageView();
    }
    
    // Sayfa kapatılmadan önce duration gönder
    window.addEventListener('beforeunload', trackVisitDuration);
    
    // Visibility API - Sayfa background'a geçince duration gönder
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            trackVisitDuration();
        }
    });
    
    // Periyodik duration güncellemesi (her 30 saniyede)
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            trackVisitDuration();
        }
    }, 30000);
    
})();

