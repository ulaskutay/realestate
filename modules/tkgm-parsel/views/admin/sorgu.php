<?php
$settings = $settings ?? [];
$baseUrl = admin_url('module/tkgm-parsel/sorgu');
?>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Parsel Sorgu</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">İl, ilçe ve mahalle seçin; ada ve parsel numaralarını girin. Sistem eşleşen parsel bilgisini getirir.</p>
    </div>
    <a href="<?php echo admin_url('module/tkgm-parsel'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
        <span class="text-sm font-medium">Dashboard</span>
    </a>
</header>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form id="parselSorguForm" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="il" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İl</label>
                <select id="il" name="il_kodu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Seçiniz</option>
                </select>
            </div>
            <div>
                <label for="ilce" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İlçe</label>
                <select id="ilce" name="ilce_kodu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" disabled>
                    <option value="">Önce il seçin</option>
                </select>
            </div>
            <div>
                <label for="mahalle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mahalle / Köy</label>
                <select id="mahalle" name="mahalle_kodu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" disabled>
                    <option value="">Önce ilçe seçin</option>
                </select>
            </div>
            <div>
                <label for="ada" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ada</label>
                <input type="text" id="ada" name="ada" placeholder="Örn: 114" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" maxlength="50">
            </div>
            <div>
                <label for="parsel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parsel</label>
                <input type="text" id="parsel" name="parsel" placeholder="Örn: 50" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" maxlength="50">
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" id="btnSorgula" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined text-xl">search</span>
                <span>Sorgula</span>
            </button>
            <button type="button" id="btnOrnek" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <span class="material-symbols-outlined text-xl">map</span>
                <span>Örnek veri ile dene</span>
            </button>
            <button type="button" id="btnTemizle" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <span class="material-symbols-outlined text-xl">clear</span>
                <span>Temizle</span>
            </button>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div id="sonucAlani" class="hidden bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Parsel Bilgisi</h2>
    <div id="parselHaritaWrap" class="hidden mb-4 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600" style="height: 400px;">
        <div id="parselHarita" style="height: 100%;"></div>
    </div>
    <div id="parsel3DBtnWrap" class="hidden mb-4">
        <button type="button" id="btn3DDron" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <span class="material-symbols-outlined text-xl">videocam</span>
            <span>3D Dron Görünümü</span>
        </button>
    </div>
    <div id="sonucIcerik"></div>
</div>

<!-- 3D Dron Modal -->
<div id="modal3DDron" class="hidden fixed inset-0 z-[9999] flex flex-col bg-black/95" style="display: none;">
    <div class="flex items-center justify-between px-4 py-2 bg-gray-900/80 border-b border-gray-700">
        <h3 class="text-white font-semibold">3D Dron Görünümü – 360° Tur</h3>
        <button type="button" id="modal3DClose" class="p-2 text-gray-400 hover:text-white rounded-lg transition-colors">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
    </div>
    <div class="flex-1 relative min-h-0 flex items-center justify-center bg-black">
        <div id="cesium3DContainer" style="width: 100%; max-width: min(90vw, 405px); aspect-ratio: 9/16; max-height: 80vh;"></div>
    </div>
    <div class="flex flex-wrap items-center gap-3 px-4 py-3 bg-gray-900/80 border-t border-gray-700">
        <button type="button" id="btn3DTurBaslat" class="flex items-center gap-2 px-3 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
            <span class="material-symbols-outlined text-lg">play_arrow</span>
            <span>360° Tur Başlat</span>
        </button>
        <button type="button" id="btn3DTurDurdur" class="hidden flex items-center gap-2 px-3 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm">
            <span class="material-symbols-outlined text-lg">stop</span>
            <span>Durdur</span>
        </button>
        <label class="flex items-center gap-2 text-sm text-gray-300">
            <span>Hız:</span>
            <select id="select3DHiz" class="px-2 py-1 rounded bg-gray-700 text-white text-sm">
                <option value="0.5">0.5x</option>
                <option value="1" selected>1x</option>
                <option value="1.5">1.5x</option>
                <option value="2">2x</option>
            </select>
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-300">
            <span>Süre (sn):</span>
            <select id="select3DSure" class="px-2 py-1 rounded bg-gray-700 text-white text-sm">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="30">30</option>
            </select>
        </label>
        <button type="button" id="btn3DVideoIndir" class="flex items-center gap-2 px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm">
            <span class="material-symbols-outlined text-lg">download</span>
            <span>Video İndir</span>
        </button>
    </div>
</div>

<div id="hataAlani" class="hidden mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
    <p id="hataMesaji" class="text-sm font-medium text-red-800 dark:text-red-200"></p>
</div>

<div id="yukleniyor" class="hidden mb-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Sorgulanıyor...</p>
</div>

<script>
(function() {
    var baseUrl = <?php echo json_encode($baseUrl); ?>;
    var sep = baseUrl.indexOf('?') !== -1 ? '&' : '?';
    var mapboxToken = <?php echo json_encode(trim($settings['mapbox_access_token'] ?? '')); ?>;
    var googleMapsApiKey = <?php echo json_encode(trim($settings['google_maps_api_key'] ?? '')); ?>;

    function ajax(url) {
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); });
    }

    function fillSelect(sel, list, valueKey, labelKey) {
        labelKey = labelKey || 'adi';
        sel.innerHTML = '';
        var first = document.createElement('option');
        first.value = '';
        first.textContent = list.length ? 'Seçiniz' : '—';
        sel.appendChild(first);
        list.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item[valueKey] || item.kodu;
            opt.textContent = item[labelKey] || item.adi || opt.value;
            sel.appendChild(opt);
        });
        sel.disabled = false;
    }

    function disableAfter(select) {
        var names = ['ilce', 'mahalle'];
        var idx = names.indexOf(select.id);
        for (var i = idx + 1; i < names.length; i++) {
            var el = document.getElementById(names[i]);
            if (el && el.tagName === 'SELECT') {
                el.innerHTML = '<option value="">—</option>';
                el.disabled = true;
            }
        }
        if (select.id === 'mahalle') {
            document.getElementById('ada').value = '';
            document.getElementById('parsel').value = '';
        }
    }

    var il = document.getElementById('il');
    var ilce = document.getElementById('ilce');
    var mahalle = document.getElementById('mahalle');
    var ada = document.getElementById('ada');
    var parsel = document.getElementById('parsel');
    var form = document.getElementById('parselSorguForm');
    var sonucAlani = document.getElementById('sonucAlani');
    var sonucIcerik = document.getElementById('sonucIcerik');
    var parselHaritaWrap = document.getElementById('parselHaritaWrap');
    var hataAlani = document.getElementById('hataAlani');
    var hataMesaji = document.getElementById('hataMesaji');
    var yukleniyor = document.getElementById('yukleniyor');
    var btnSorgula = document.getElementById('btnSorgula');

    function roundCoord(c) { return Math.round(c * 1e5) / 1e5; }
    function getCenterFromGeom(geom) {
        var ring = geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0] ? geom.coordinates[0] : [];
        if (geom.type === 'MultiPolygon' && geom.coordinates && geom.coordinates[0] && geom.coordinates[0][0]) ring = geom.coordinates[0][0];
        if (ring.length === 0) return { lng: 29.1, lat: 36.7 };
        var lng = 0, lat = 0;
        for (var i = 0; i < ring.length; i++) {
            lng += ring[i][0];
            lat += ring[i][1];
        }
        return { lng: lng / ring.length, lat: lat / ring.length };
    }
    function getBoundingSphereFromGeom(geom, Cesium) {
        var ring = [];
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) ring = geom.coordinates[0];
        else if (geom.type === 'MultiPolygon' && geom.coordinates && geom.coordinates[0] && geom.coordinates[0][0]) ring = geom.coordinates[0][0];
        if (ring.length === 0) return null;
        var center = getCenterFromGeom(geom);
        var R = 6371000;
        var toRad = Math.PI / 180;
        var maxDist = 0;
        for (var i = 0; i < ring.length; i++) {
            var dlat = (ring[i][1] - center.lat) * toRad;
            var dlon = (ring[i][0] - center.lng) * toRad * Math.cos(center.lat * toRad);
            var d = R * Math.sqrt(dlat * dlat + dlon * dlon);
            if (d > maxDist) maxDist = d;
        }
        var radius = Math.max(maxDist * 1.2, 80);
        return { sphere: new Cesium.BoundingSphere(Cesium.Cartesian3.fromDegrees(center.lng, center.lat, 0), radius), range: Math.max(radius * 2.5, 150) };
    }
    function simplifyRing(ring, maxPoints) {
        maxPoints = maxPoints || 80;
        if (ring.length <= maxPoints) return ring.map(function(p) { return [roundCoord(p[0]), roundCoord(p[1])]; });
        var step = ring.length / maxPoints;
        var out = [];
        for (var i = 0; i < ring.length && out.length < maxPoints; i += step) {
            out.push([roundCoord(ring[i][0]), roundCoord(ring[i][1])]);
        }
        if (out.length > 0 && (out[out.length - 1][0] !== ring[ring.length - 1][0] || out[out.length - 1][1] !== ring[ring.length - 1][1])) {
            out.push([roundCoord(ring[ring.length - 1][0]), roundCoord(ring[ring.length - 1][1])]);
        }
        return out;
    }
    function loadGoogleMapsScript() {
        if (window.google && window.google.maps) return Promise.resolve();
        return new Promise(function(resolve, reject) {
            var done = false;
            function finish(err) {
                if (done) return;
                done = true;
                if (err) reject(err); else resolve();
            }
            var t = setTimeout(function() { finish(new Error('Google Maps zaman aşımı. API key ve referans kısıtlamalarını kontrol edin.')); }, 15000);
            window._googleMapsResolve = function() { clearTimeout(t); finish(); };
            var s = document.createElement('script');
            s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(googleMapsApiKey) + '&callback=_googleMapsResolve';
            s.onerror = function() { clearTimeout(t); finish(new Error('Google Maps script yüklenemedi')); };
            document.head.appendChild(s);
        });
    }
    function geoJsonToGooglePaths(geom) {
        var rings = [];
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) {
            rings.push(geom.coordinates[0].map(function(p) { return { lat: p[1], lng: p[0] }; }));
        } else if (geom.type === 'MultiPolygon' && geom.coordinates) {
            geom.coordinates.forEach(function(poly) {
                if (poly[0]) rings.push(poly[0].map(function(p) { return { lat: p[1], lng: p[0] }; }));
            });
        }
        return rings;
    }
    function geoJsonToGoogleBounds(geom) {
        var minLat = 90, maxLat = -90, minLng = 180, maxLng = -180;
        function addRing(ring) {
            for (var i = 0; i < ring.length; i++) {
                var lat = ring[i][1], lng = ring[i][0];
                if (lat < minLat) minLat = lat;
                if (lat > maxLat) maxLat = lat;
                if (lng < minLng) minLng = lng;
                if (lng > maxLng) maxLng = lng;
            }
        }
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) addRing(geom.coordinates[0]);
        else if (geom.type === 'MultiPolygon' && geom.coordinates) geom.coordinates.forEach(function(p) { if (p[0]) addRing(p[0]); });
        return { ne: { lat: maxLat, lng: maxLng }, sw: { lat: minLat, lng: minLng } };
    }
    function initParselMapLeaflet(geojson) {
        var mapEl = document.getElementById('parselHarita');
        if (!mapEl || !window.L) return;
        var map = L.map('parselHarita').setView([39, 35], 6);
        if (mapboxToken) {
            L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/satellite-v9/tiles/256/{z}/{x}/{y}?access_token=' + mapboxToken, {
                attribution: '© Mapbox © OpenStreetMap',
                maxZoom: 20,
                maxNativeZoom: 19
            }).addTo(map);
        } else {
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Esri, Maxar, Earthstar Geographics',
                maxZoom: 19,
                maxNativeZoom: 19
            }).addTo(map);
        }
        var layer = L.geoJSON(geojson, { style: { color: '#00ffff', weight: 2, opacity: 1, fillColor: '#00ffff', fillOpacity: 0 } }).addTo(map);
        map.fitBounds(layer.getBounds());
        window.parselMap = map;
    }

    function loadScript(src) {
        return new Promise(function(resolve, reject) {
            var s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }
    // İlleri yükle
    ajax(baseUrl + sep + 'action=iller').then(function(res) {
        if (res.success && res.data && res.data.length) {
            fillSelect(il, res.data);
        }
    }).catch(function() {
        fillSelect(il, []);
    });

    il.addEventListener('change', function() {
        var kodu = this.value;
        disableAfter(il);
        if (!kodu) return;
        ilce.disabled = true;
        ilce.innerHTML = '<option value="">Yükleniyor...</option>';
        ajax(baseUrl + sep + 'action=ilceler&il_kodu=' + encodeURIComponent(kodu)).then(function(res) {
            fillSelect(ilce, res.success && res.data ? res.data : []);
        });
    });

    ilce.addEventListener('change', function() {
        var kodu = this.value;
        disableAfter(ilce);
        if (!kodu) return;
        mahalle.disabled = true;
        mahalle.innerHTML = '<option value="">Yükleniyor...</option>';
        ajax(baseUrl + sep + 'action=mahalleler&il_kodu=' + encodeURIComponent(il.value) + '&ilce_kodu=' + encodeURIComponent(kodu)).then(function(res) {
            fillSelect(mahalle, res.success && res.data ? res.data : []);
        });
    });

    mahalle.addEventListener('change', function() {
        disableAfter(mahalle);
        var kodu = this.value;
        if (!kodu) return;
    });

    function showDetayResult(res) {
        if (typeof window.parselMap !== 'undefined' && window.parselMap) {
            if (typeof window.parselMap.remove === 'function') window.parselMap.remove();
            window.parselMap = null;
        }
        var prevMapEl = document.getElementById('parselHarita');
        if (prevMapEl) prevMapEl.innerHTML = '';
        if (res.success && res.data) {
            var d = res.data;
            if (d.from_cbs) {
                var html = '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-300"><tbody>';
                if (d.tasinmaz_no !== undefined && d.tasinmaz_no !== '') html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Taşınmaz No</th><td class="py-2">' + escapeHtml(String(d.tasinmaz_no)) + '</td></tr>';
                html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Ada</th><td class="py-2">' + escapeHtml(String(d.ada || '')) + '</td></tr>';
                html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Parsel</th><td class="py-2">' + escapeHtml(String(d.parsel_no || '')) + '</td></tr>';
                if (d.alan_m2 !== undefined && d.alan_m2 !== null && d.alan_m2 !== '') html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Alan (m²)</th><td class="py-2">' + escapeHtml(String(d.alan_m2)) + '</td></tr>';
                if (d.nitelik) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Nitelik</th><td class="py-2">' + escapeHtml(String(d.nitelik)) + '</td></tr>';
                if (d.il_adi) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İl</th><td class="py-2">' + escapeHtml(String(d.il_adi)) + '</td></tr>';
                if (d.ilce_adi) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İlçe</th><td class="py-2">' + escapeHtml(String(d.ilce_adi)) + '</td></tr>';
                if (d.mahalle_adi) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Mahalle / Köy</th><td class="py-2">' + escapeHtml(String(d.mahalle_adi)) + '</td></tr>';
                html += '</tbody></table>';
                sonucIcerik.innerHTML = html;
                if (d.geometry || d.geojson) {
                    parselHaritaWrap.classList.remove('hidden');
                    var geojson = d.geojson || { type: 'Feature', geometry: d.geometry, properties: {} };
                    window.currentParselGeojson = geojson;
                    var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                    if (btn3DWrap) btn3DWrap.classList.remove('hidden');
                    var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
                    var mapEl = document.getElementById('parselHarita');
                    if (window.parselMap) {
                        if (typeof window.parselMap.remove === 'function') window.parselMap.remove();
                        window.parselMap = null;
                    }
                    if (mapEl) mapEl.innerHTML = '';
                    setTimeout(function() {
                        if (!mapEl) return;
                        if (googleMapsApiKey) {
                            loadGoogleMapsScript().then(function() {
                                var center = getCenterFromGeom(geom);
                                var paths = geoJsonToGooglePaths(geom);
                                if (paths.length === 0) {
                                    initParselMapLeaflet(geojson);
                                    return;
                                }
                                window.parselMap = new google.maps.Map(mapEl, {
                                    center: { lat: center.lat, lng: center.lng },
                                    zoom: 17,
                                    tilt: 45,
                                    heading: 0,
                                    mapTypeId: google.maps.MapTypeId.SATELLITE,
                                    disableDefaultUI: false,
                                    zoomControl: true,
                                    mapTypeControl: true,
                                    scaleControl: true,
                                    fullscreenControl: true,
                                    streetViewControl: false
                                });
                                var polygon = new google.maps.Polygon({
                                    paths: paths,
                                    strokeColor: '#00ffff',
                                    strokeWeight: 2,
                                    strokeOpacity: 1,
                                    fillColor: '#00ffff',
                                    fillOpacity: 0
                                });
                                polygon.setMap(window.parselMap);
                                var b = geoJsonToGoogleBounds(geom);
                                window.parselMap.fitBounds(new google.maps.LatLngBounds(
                                    new google.maps.LatLng(b.sw.lat, b.sw.lng),
                                    new google.maps.LatLng(b.ne.lat, b.ne.lng)
                                ));
                            }).catch(function() {
                                initParselMapLeaflet(geojson);
                            });
                        } else {
                            initParselMapLeaflet(geojson);
                        }
                    }, 100);
                } else {
                    parselHaritaWrap.classList.add('hidden');
                    var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                    if (btn3DWrap) btn3DWrap.classList.add('hidden');
                }
            } else {
                var q = d.query || {};
                var ilAdi = il.options[il.selectedIndex] ? il.options[il.selectedIndex].text : q.il_kodu || '';
                var ilceAdi = ilce.options[ilce.selectedIndex] ? ilce.options[ilce.selectedIndex].text : q.ilce_kodu || '';
                var mahalleAdi = mahalle.options[mahalle.selectedIndex] ? mahalle.options[mahalle.selectedIndex].text : q.mahalle_kodu || '';
                var url = d.parselsorgu_url || 'https://parselsorgu.tkgm.gov.tr';
                sonucIcerik.innerHTML =
                    '<p class="text-amber-700 dark:text-amber-300 text-sm mb-4">Bu ada/parsel için CBS servisi şu an detay döndürmedi. Girilen bilgiler aşağıdadır; resmi sorgu için TKGM Parsel Sorgu sayfasını kullanabilirsiniz.</p>' +
                    '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-300 mb-4"><tbody>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İl</th><td class="py-2">' + escapeHtml(ilAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İlçe</th><td class="py-2">' + escapeHtml(ilceAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Mahalle / Köy</th><td class="py-2">' + escapeHtml(mahalleAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Ada</th><td class="py-2">' + escapeHtml(String(q.ada || '')) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Parsel</th><td class="py-2">' + escapeHtml(String(q.parsel || '')) + '</td></tr>' +
                    '</tbody></table>' +
                    '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">' +
                    'Parsel Sorgu (TKGM) sayfasında kontrol et <span class="material-symbols-outlined text-lg">open_in_new</span></a>';
                parselHaritaWrap.classList.add('hidden');
                var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                if (btn3DWrap) btn3DWrap.classList.add('hidden');
            }
            sonucAlani.classList.remove('hidden');
        } else {
            hataMesaji.textContent = res.message || 'Parsel detayı alınamadı.';
            hataAlani.classList.remove('hidden');
        }
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        hataAlani.classList.add('hidden');
        sonucAlani.classList.add('hidden');
        var adaVal = (ada.value || '').trim();
        var parselVal = (parsel.value || '').trim();
        if (!il.value || !ilce.value || !mahalle.value || !adaVal || !parselVal) {
            hataMesaji.textContent = 'Lütfen il, ilçe, mahalle seçin ve ada ile parsel numaralarını girin.';
            hataAlani.classList.remove('hidden');
            return;
        }
        btnSorgula.disabled = true;
        yukleniyor.classList.remove('hidden');
        var q = baseUrl + sep + 'action=detay&il_kodu=' + encodeURIComponent(il.value) + '&ilce_kodu=' + encodeURIComponent(ilce.value) + '&mahalle_kodu=' + encodeURIComponent(mahalle.value) + '&ada=' + encodeURIComponent(adaVal) + '&parsel=' + encodeURIComponent(parselVal);
        ajax(q).then(function(res) {
            yukleniyor.classList.add('hidden');
            btnSorgula.disabled = false;
            showDetayResult(res);
        }).catch(function(err) {
            yukleniyor.classList.add('hidden');
            btnSorgula.disabled = false;
            hataMesaji.textContent = 'İstek sırasında bir hata oluştu.';
            hataAlani.classList.remove('hidden');
        });
    });

    document.getElementById('btnOrnek').addEventListener('click', function() {
        hataAlani.classList.add('hidden');
        sonucAlani.classList.add('hidden');
        var btnOrnek = this;
        btnOrnek.disabled = true;
        yukleniyor.classList.remove('hidden');
        ajax(baseUrl + sep + 'action=ornek').then(function(res) {
            yukleniyor.classList.add('hidden');
            btnOrnek.disabled = false;
            showDetayResult(res);
        }).catch(function() {
            yukleniyor.classList.add('hidden');
            btnOrnek.disabled = false;
            hataMesaji.textContent = 'Örnek veri yüklenemedi.';
            hataAlani.classList.remove('hidden');
        });
    });

    document.getElementById('btnTemizle').addEventListener('click', function() {
        il.value = '';
        ilce.innerHTML = '<option value="">Önce il seçin</option>'; ilce.disabled = true;
        mahalle.innerHTML = '<option value="">Önce ilçe seçin</option>'; mahalle.disabled = true;
        ada.value = '';
        parsel.value = '';
        sonucAlani.classList.add('hidden');
        parselHaritaWrap.classList.add('hidden');
        var btn3DWrap = document.getElementById('parsel3DBtnWrap');
        if (btn3DWrap) btn3DWrap.classList.add('hidden');
        if (window.parselMap) {
            if (typeof window.parselMap.remove === 'function') window.parselMap.remove();
            window.parselMap = null;
        }
        var pH = document.getElementById('parselHarita');
        if (pH) pH.innerHTML = '';
        hataAlani.classList.add('hidden');
    });

    // --- 3D Dron Görünümü (Cesium lazy-load) ---
    var cesium3DViewer = null;
    var orbitAnimationId = null;
    var cesiumLoadPromise = null;

    function loadCesium() {
        if (window.Cesium) return Promise.resolve();
        if (cesiumLoadPromise) return cesiumLoadPromise;
        cesiumLoadPromise = new Promise(function(resolve, reject) {
            window.CESIUM_BASE_URL = 'https://unpkg.com/cesium@1.105/Build/Cesium/';
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/cesium@1.105/Build/Cesium/Widgets/widgets.css';
            link.onload = function() {
                var script = document.createElement('script');
                script.src = 'https://unpkg.com/cesium@1.105/Build/Cesium/Cesium.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            };
            link.onerror = reject;
            document.head.appendChild(link);
        });
        return cesiumLoadPromise;
    }
    var orbitStartTime = 0;
    var orbitDuration = 20;
    var orbitSpeed = 1;
    var mediaRecorder = null;
    var recordedChunks = [];
    var isRecording = false;

    function initCesium3D() {
        var container = document.getElementById('cesium3DContainer');
        var geojson = window.currentParselGeojson;
        if (!container || !geojson || !window.Cesium) return;
        if (cesium3DViewer) {
            cesium3DViewer.destroy();
            cesium3DViewer = null;
        }
        var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
        var center = getCenterFromGeom(geom);

        var Cesium = window.Cesium;
        cesium3DViewer = new Cesium.Viewer('cesium3DContainer', {
            imageryProvider: new Cesium.UrlTemplateImageryProvider({
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                credit: 'Esri, Maxar, Earthstar Geographics'
            }),
            baseLayerPicker: false,
            geocoder: false,
            homeButton: true,
            sceneModePicker: false,
            navigationHelpButton: false,
            animation: false,
            timeline: false,
            fullscreenButton: true,
            vrButton: false,
            scene3DOnly: true,
            requestRenderMode: false,
            contextOptions: { webgl: { preserveDrawingBuffer: true } }
        });

        var use3DTiles = !!googleMapsApiKey;
        if (use3DTiles) {
            try {
                var tileset = cesium3DViewer.scene.primitives.add(new Cesium.Cesium3DTileset({
                    url: 'https://tile.googleapis.com/v1/3dtiles/root.json?key=' + encodeURIComponent(googleMapsApiKey),
                    showCreditsOnScreen: true,
                    maximumScreenSpaceError: 16
                }));
                tileset.readyPromise.then(function() {
                    cesium3DViewer.scene.globe.show = false;
                }).catch(function() {
                    cesium3DViewer.scene.globe.show = true;
                });
            } catch (e) {
                cesium3DViewer.scene.globe.show = true;
            }
        }

        requestAnimationFrame(function() {
            if (cesium3DViewer && cesium3DViewer.scene) cesium3DViewer.scene.requestRender();
            if (cesium3DViewer && cesium3DViewer.resize) cesium3DViewer.resize();
        });

        function addParselToScene(viewer, geom) {
            var ring = [];
            if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) ring = geom.coordinates[0];
            else if (geom.type === 'MultiPolygon' && geom.coordinates && geom.coordinates[0] && geom.coordinates[0][0]) ring = geom.coordinates[0][0];
            if (ring.length < 2) return;
            var polyPositions = ring.map(function(p) { return Cesium.Cartesian3.fromDegrees(p[0], p[1], 8); });
            polyPositions.push(polyPositions[0]);
            viewer.entities.add({
                name: 'parsel-cizgi-glow',
                polyline: {
                    positions: polyPositions,
                    width: 22,
                    material: Cesium.Color.CYAN.withAlpha(0.4),
                    clampToGround: false,
                    followSurface: false,
                    disableDepthTestDistance: Number.POSITIVE_INFINITY
                }
            });
            viewer.entities.add({
                name: 'parsel-cizgi',
                polyline: {
                    positions: polyPositions,
                    width: 14,
                    material: Cesium.Color.CYAN,
                    clampToGround: false,
                    followSurface: false,
                    disableDepthTestDistance: Number.POSITIVE_INFINITY
                }
            });
        }

        function finishInit() {
            addParselToScene(cesium3DViewer, geom);
            var bs = getBoundingSphereFromGeom(geom, Cesium);
            if (bs) {
                cesium3DViewer.camera.viewBoundingSphere(bs.sphere, new Cesium.HeadingPitchRange(0, Cesium.Math.toRadians(-45), bs.range));
                window.parselBoundingSphere = bs.sphere;
                window.parselOrbitRange = bs.range;
            }
            if (cesium3DViewer.resize) cesium3DViewer.resize();
            cesium3DViewer.scene.requestRender();
        }

        if (use3DTiles) {
            var tileset = cesium3DViewer.scene.primitives.get(cesium3DViewer.scene.primitives.length - 1);
            if (tileset && tileset.readyPromise) {
                tileset.readyPromise.then(finishInit).catch(finishInit);
            } else {
                finishInit();
            }
        } else {
            finishInit();
        }
    }

    function runOrbitAnimation(cbOnComplete) {
        if (!cesium3DViewer || !window.Cesium) return;
        var geojson = window.currentParselGeojson;
        if (!geojson) return;
        var Cesium = window.Cesium;
        var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
        var boundingSphere = window.parselBoundingSphere;
        var range = window.parselOrbitRange;
        if (!boundingSphere || range == null) {
            var bs = getBoundingSphereFromGeom(geom, Cesium);
            if (!bs) return;
            boundingSphere = bs.sphere;
            range = bs.range;
        }
        cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(0, Cesium.Math.toRadians(-45), range));

        var dur = parseInt(document.getElementById('select3DSure').value, 10) || 20;
        var spd = parseFloat(document.getElementById('select3DHiz').value) || 1;
        orbitDuration = dur;
        orbitSpeed = spd;
        orbitStartTime = performance.now();

        document.getElementById('btn3DTurBaslat').classList.add('hidden');
        document.getElementById('btn3DTurDurdur').classList.remove('hidden');
        document.getElementById('select3DHiz').disabled = true;
        document.getElementById('select3DSure').disabled = true;

        function tick() {
            var elapsed = (performance.now() - orbitStartTime) / 1000;
            var t = Math.min(elapsed / (orbitDuration / orbitSpeed), 1);
            var heading = t * Math.PI * 2;
            cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(heading, Cesium.Math.toRadians(-45), range));
            if (t >= 1) {
                orbitAnimationId = null;
                document.getElementById('btn3DTurBaslat').classList.remove('hidden');
                document.getElementById('btn3DTurDurdur').classList.add('hidden');
                document.getElementById('select3DHiz').disabled = false;
                document.getElementById('select3DSure').disabled = false;
                if (cbOnComplete) cbOnComplete();
                return;
            }
            orbitAnimationId = requestAnimationFrame(tick);
        }
        orbitAnimationId = requestAnimationFrame(tick);
    }

    function stopOrbitAnimation() {
        if (orbitAnimationId) {
            cancelAnimationFrame(orbitAnimationId);
            orbitAnimationId = null;
        }
        document.getElementById('btn3DTurBaslat').classList.remove('hidden');
        document.getElementById('btn3DTurDurdur').classList.add('hidden');
        document.getElementById('select3DHiz').disabled = false;
        document.getElementById('select3DSure').disabled = false;
    }

    function downloadVideo() {
        if (!cesium3DViewer || !cesium3DViewer.scene || !cesium3DViewer.scene.canvas) return;
        var canvas = cesium3DViewer.scene.canvas;
        var stream = canvas.captureStream(30);
        var mime = MediaRecorder.isTypeSupported('video/webm; codecs=vp9') ? 'video/webm; codecs=vp9' : 'video/webm';
        recordedChunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 5000000 });
        mediaRecorder.ondataavailable = function(e) {
            if (e.data.size > 0) recordedChunks.push(e.data);
        };
        mediaRecorder.onstop = function() {
            isRecording = false;
            var blob = new Blob(recordedChunks, { type: mime });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'parsel-360-tur.webm';
            a.click();
            URL.revokeObjectURL(url);
        };
        mediaRecorder.start(100);
        isRecording = true;
        runOrbitAnimation(function() {
            if (mediaRecorder && isRecording) mediaRecorder.stop();
        });
    }

    document.getElementById('btn3DDron').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Yükleniyor...';
        loadCesium().then(function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-xl">videocam</span> 3D Dron Görünümü';
            var modal = document.getElementById('modal3DDron');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    initCesium3D();
                });
            });
        }).catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-xl">videocam</span> 3D Dron Görünümü';
            alert('3D görünüm yüklenemedi. Lütfen sayfayı yenileyip tekrar deneyin.');
        });
    });

    document.getElementById('modal3DClose').addEventListener('click', function() {
        stopOrbitAnimation();
        if (mediaRecorder && isRecording) mediaRecorder.stop();
        var modal = document.getElementById('modal3DDron');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        if (cesium3DViewer) {
            cesium3DViewer.destroy();
            cesium3DViewer = null;
        }
    });

    document.getElementById('btn3DTurBaslat').addEventListener('click', function() {
        runOrbitAnimation();
    });

    document.getElementById('btn3DTurDurdur').addEventListener('click', function() {
        stopOrbitAnimation();
    });

    document.getElementById('btn3DVideoIndir').addEventListener('click', function() {
        downloadVideo();
    });

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
})();
</script>
