/**
 * Harita Üzerinde İlanlar – Google Maps frontend
 * Uses window.listingsMapConfig: { apiKey, defaultLat, defaultLng, defaultZoom, listings, mapId? }
 * Set mapId (e.g. DEMO_MAP_ID or your Cloud Console map ID) to use AdvancedMarkerElement and avoid deprecation warnings.
 */
(function () {
    var config = window.listingsMapConfig;
    if (!config || !config.apiKey) return;

    var mapEl = document.getElementById('listings-map-canvas');
    if (!mapEl) return;

    function loadScript(src, isAsync) {
        return new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = src;
            s.async = !!isAsync;
            s.onload = function () { resolve(); };
            s.onerror = function () { reject(new Error('Script load failed: ' + src)); };
            document.head.appendChild(s);
        });
    }

    function loadGoogleMaps() {
        if (window.google && window.google.maps) return Promise.resolve();
        return new Promise(function (resolve, reject) {
            var done = false;
            function finish(err) {
                if (done) return;
                done = true;
                if (err) reject(err); else resolve();
            }
            window._listingsMapGmapsResolve = function () { finish(); };
            var t = setTimeout(function () { finish(new Error('Google Maps timeout')); }, 15000);
            var s = document.createElement('script');
            var url = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(config.apiKey) + '&loading=async&callback=_listingsMapGmapsResolve';
            if (config.mapId) url += '&libraries=marker';
            s.src = url;
            s.async = true;
            s.defer = true;
            s.onerror = function () { clearTimeout(t); finish(new Error('Google Maps script failed')); };
            document.head.appendChild(s);
        });
    }

    function pinSvgUrl(color) {
        var hex = color.replace('#', '');
        return 'data:image/svg+xml;utf-8,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">' +
            '<path fill="' + hex + '" stroke="#fff" stroke-width="2" d="M16 0C7.2 0 0 7.2 0 16c0 12 16 24 16 24s16-12 16-24C32 7.2 24.8 0 16 0z"/>' +
            '<circle cx="16" cy="14" r="6" fill="#fff"/>' +
            '</svg>'
        );
    }

    function initMap() {
        var center = { lat: config.defaultLat, lng: config.defaultLng };
        var mapTypeId = (config.mapType === 'satellite') ? google.maps.MapTypeId.SATELLITE : google.maps.MapTypeId.ROADMAP;
        var mapOptions = {
            center: center,
            zoom: config.defaultZoom,
            mapTypeId: mapTypeId,
            mapTypeControl: true,
            mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
            fullscreenControl: true,
            zoomControl: true,
            scaleControl: false,
            streetViewControl: false,
        };
        if (config.mapId) mapOptions.mapId = config.mapId;
        var map = new google.maps.Map(mapEl, mapOptions);

        var listings = config.listings || [];
        var markers = [];
        var infoWindow = new google.maps.InfoWindow();
        var colors = config.colors || {};
        var primary = (colors.primary && colors.primary.replace) ? colors.primary.replace('#', '') : 'bc1a1a';
        var accent = (colors.accent && colors.accent.replace) ? colors.accent.replace('#', '') : '9a1615';
        var pinColor = function (status) { return (status === 'rent') ? ('#' + accent) : ('#' + primary); };
        var useAdvancedMarkers = config.mapId && (google.maps.marker && google.maps.marker.AdvancedMarkerElement);

        listings.forEach(function (item) {
            var iconUrl = pinSvgUrl(pinColor(item.listing_status || 'sale'));
            var position = { lat: item.lat, lng: item.lng };
            var marker;
            if (useAdvancedMarkers) {
                var pinEl = document.createElement('img');
                pinEl.src = iconUrl;
                pinEl.style.width = '32px';
                pinEl.style.height = '40px';
                pinEl.style.display = 'block';
                marker = new google.maps.marker.AdvancedMarkerElement({
                    position: position,
                    map: map,
                    title: item.title || (item.label + ' – ' + item.price),
                    content: pinEl,
                });
            } else {
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: item.title || (item.label + ' – ' + item.price),
                    icon: { url: iconUrl, scaledSize: new google.maps.Size(32, 40), anchor: new google.maps.Point(16, 40) },
                });
            }
            marker.listing = item;
            var clickEvent = useAdvancedMarkers ? 'gmp-click' : 'click';
            marker.addListener(clickEvent, function () {
                var textMuted = (colors.text_muted || '#6b7280').replace('#', '');
                var primaryHex = '#' + primary;
                var content = '<div class="listings-map-infowindow" style="min-width:220px;max-width:280px;padding:8px 0;font-family:system-ui,sans-serif;">';
                content += '<div style="font-weight:700;font-size:14px;margin-bottom:6px;line-height:1.3;">' + escapeHtml(item.title || item.label || 'İlan') + '</div>';
                if (item.label) content += '<div style="font-size:12px;color:#' + textMuted + ';margin-bottom:4px;">' + escapeHtml(item.label) + '</div>';
                if (item.price) content += '<div style="font-size:15px;font-weight:600;color:' + primaryHex + ';margin-bottom:8px;">' + escapeHtml(item.price) + '</div>';
                if (item.detail_url) content += '<a href="' + escapeHtml(item.detail_url) + '" target="_top" style="display:inline-block;padding:6px 12px;background:' + primaryHex + ';color:#fff;text-decoration:none;border-radius:6px;font-size:13px;font-weight:500;">İlanı görüntüle</a>';
                content += '</div>';
                infoWindow.setContent(content);
                infoWindow.open(map, marker);
            });
            markers.push(marker);
        });

        if (window.markerClusterer && typeof window.markerClusterer.MarkerClusterer === 'function' && !useAdvancedMarkers) {
            new window.markerClusterer.MarkerClusterer({ map: map, markers: markers });
        }
        if (listings.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            var getPos = function (m) { return useAdvancedMarkers ? m.position : m.getPosition(); };
            markers.forEach(function (m) { bounds.extend(getPos(m)); });
            if (listings.length === 1) {
                map.setCenter(getPos(markers[0]));
                map.setZoom(15);
            } else {
                try {
                    map.fitBounds(bounds, 50);
                } catch (e) {
                    map.fitBounds(bounds);
                }
            }
        }

        var searchInput = document.getElementById('listings-map-address');
        var searchBtn = document.getElementById('listings-map-search-btn');
        if (searchInput && searchBtn) {
            searchBtn.addEventListener('click', function () {
                var query = (searchInput.value || '').trim();
                if (!query) return;
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: query }, function (results, status) {
                    if (status === 'OK' && results[0]) {
                        map.setCenter(results[0].geometry.location);
                        map.setZoom(14);
                    }
                });
            });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); searchBtn.click(); }
            });
        }

        window.listingsMapInstance = map;
    }

    function escapeHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    loadScript('https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js', false)
        .then(function () {
            if (window.markerClusterer && !window.markerClusterer.MarkerClusterer && window.markerClusterer.default) {
                window.markerClusterer = { MarkerClusterer: window.markerClusterer.default };
            }
            return loadGoogleMaps();
        })
        .catch(function () {
            return loadGoogleMaps();
        })
        .then(function () {
            initMap();
        })
        .catch(function (err) {
            if (mapEl) mapEl.innerHTML = '<div style="padding:2rem;text-align:center;color:#c00;">Harita yüklenemedi. API key ve internet bağlantınızı kontrol edin.</div>';
            console.error('Listings map:', err);
        });
})();
