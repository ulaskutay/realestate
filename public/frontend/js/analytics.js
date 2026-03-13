/**
 * Privacy-Friendly Analytics Tracker
 * Layout head'da tek script olarak yüklenir; tüm sayfalarda çalışır.
 * Sayfa URL ve <title> otomatik gönderilir.
 */

(function() {
    'use strict';
    
    // Header'da PHP ile set edilir (site_url + /api/track) – alt klasör / farklı domain için doğru URL
    var TRACK_ENDPOINT = window.CODETIC_ANALYTICS_TRACK_URL || (window.location.origin + '/api/track');
    
    var pageLoadTime = Date.now();
    var trackingData = null;
    
    /**
     * Sayfa görüntülenmesini track et – bulunulan sayfanın URL ve title'ı gönderilir
     */
    function trackPageView() {
        trackingData = {
            page_url: window.location.href,
            page_title: document.title || '',
            referrer: document.referrer || null,
            user_agent: navigator.userAgent,
            timestamp: Date.now()
        };
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
    
    // Periyodik duration güncellemesi - requestIdleCallback kullan (her 30 saniyede)
    let lastUpdateTime = Date.now();
    const UPDATE_INTERVAL = 30000; // 30 saniye
    
    function scheduleNextUpdate() {
        if (document.visibilityState !== 'visible') {
            // Sayfa görünür değilse, visibilitychange'de tekrar dene
            return;
        }
        
        const timeSinceLastUpdate = Date.now() - lastUpdateTime;
        const delay = Math.max(0, UPDATE_INTERVAL - timeSinceLastUpdate);
        
        if (window.requestIdleCallback) {
            // requestIdleCallback kullan - browser boş olduğunda çalışır
            window.requestIdleCallback(function() {
                setTimeout(function() {
                    trackVisitDuration();
                    lastUpdateTime = Date.now();
                    scheduleNextUpdate();
                }, delay);
            }, { timeout: delay + 1000 });
        } else {
            // Fallback: setTimeout
            setTimeout(function() {
                if (document.visibilityState === 'visible') {
                    trackVisitDuration();
                    lastUpdateTime = Date.now();
                }
                scheduleNextUpdate();
            }, delay);
        }
    }
    
    // Visibility değişikliğinde güncelleme zamanla
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            scheduleNextUpdate();
        }
    });
    
    // İlk güncellemeyi zamanla
    scheduleNextUpdate();
    
})();

