(function() {
    'use strict';

    var pxPerSec = 60;
    var state = {
        timeline: {},
        tracks: [],
        clips: [],
        selectedClipId: null,
        playheadTime: 0
    };

    var app = document.getElementById('vt-app');
    if (!app) return;

    var timelineId = parseInt(app.getAttribute('data-timeline-id'), 10);
    var saveUrl = app.getAttribute('data-save-url') || '';
    var mediaListUrl = app.getAttribute('data-media-list-url') || '';

    function init() {
        var d = window.VT_DATA;
        if (d && d.timeline) state.timeline = d.timeline;
        if (d && d.tracks) state.tracks = d.tracks;
        if (d && d.clipsByTrack) {
            state.clips = [];
            Object.keys(d.clipsByTrack).forEach(function(trackId) {
                (d.clipsByTrack[trackId] || []).forEach(function(c) {
                    c.track_id = parseInt(trackId, 10);
                    state.clips.push(c);
                });
            });
        }
        state.timeline.id = state.timeline.id || timelineId;
        bindToolbar();
        renderRuler();
        renderTracks();
        bindSave();
        bindAddTrack();
        bindAddClipPanel();
        bindMediaModal();
        var addPanel = document.getElementById('vt-add-clip-panel');
        var ph = document.getElementById('vt-props-placeholder');
        if (ph) ph.style.display = 'none';
        if (addPanel) addPanel.style.display = 'block';
    }

    function getDuration() {
        var v = parseFloat(document.getElementById('vt-duration') && document.getElementById('vt-duration').value);
        return isNaN(v) || v <= 0 ? 10 : v;
    }

    function getTimelineWidth() {
        return getDuration() * pxPerSec;
    }

    function bindToolbar() {
        var nameEl = document.getElementById('vt-name');
        var widthEl = document.getElementById('vt-width');
        var heightEl = document.getElementById('vt-height');
        var durationEl = document.getElementById('vt-duration');
        if (nameEl) nameEl.addEventListener('change', function() { state.timeline.name = this.value; });
        if (widthEl) widthEl.addEventListener('change', function() { state.timeline.width = parseInt(this.value, 10) || 1920; });
        if (heightEl) heightEl.addEventListener('change', function() { state.timeline.height = parseInt(this.value, 10) || 1080; });
        if (durationEl) durationEl.addEventListener('change', function() {
            state.timeline.duration_sec = parseFloat(this.value) || 10;
            renderRuler();
            renderTracks();
        });
    }

    function renderRuler() {
        var ruler = document.getElementById('vt-ruler');
        if (!ruler) return;
        var duration = getDuration();
        ruler.innerHTML = '';
        ruler.style.width = getTimelineWidth() + 'px';
        var step = duration <= 5 ? 0.5 : duration <= 30 ? 1 : 5;
        for (var t = 0; t <= duration; t += step) {
            var tick = document.createElement('div');
            tick.className = 'vt-ruler-tick';
            tick.style.left = (t * pxPerSec) + 'px';
            var span = document.createElement('span');
            span.textContent = t;
            tick.appendChild(span);
            ruler.appendChild(tick);
        }
    }

    function renderTracks() {
        var container = document.getElementById('vt-tracks');
        if (!container) return;
        var duration = getDuration();
        var w = getTimelineWidth();
        container.innerHTML = '';
        container.style.minWidth = w + 'px';

        state.tracks.forEach(function(track, trackIndex) {
            var row = document.createElement('div');
            row.className = 'vt-track-row';
            row.setAttribute('data-track-index', trackIndex);

            var label = document.createElement('div');
            label.className = 'vt-track-label';
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.gap = '8px';
            label.innerHTML = '<span>' + (track.name || ('Track ' + (trackIndex + 1))) + '</span>';
            var addClipBtn = document.createElement('button');
            addClipBtn.type = 'button';
            addClipBtn.className = 'vt-btn vt-btn-sm';
            addClipBtn.style.padding = '2px 6px';
            addClipBtn.textContent = '+ Clip ▼';
            addClipBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var menu = document.createElement('div');
                menu.className = 'vt-dropdown';
                menu.style.cssText = 'position:absolute;z-index:100;min-width:120px;';
                ['text', 'shape', 'image', 'video', 'audio'].forEach(function(type) {
                    var a = document.createElement('button');
                    a.type = 'button';
                    a.className = 'vt-dropdown-item';
                    a.textContent = type;
                    a.addEventListener('click', function() { addClipToTrack(trackIndex, type); document.body.removeChild(menu); });
                    menu.appendChild(a);
                });
                document.body.appendChild(menu);
                var rect = addClipBtn.getBoundingClientRect();
                menu.style.left = rect.left + 'px';
                menu.style.top = (rect.bottom + 4) + 'px';
                var close = function() { if (menu.parentNode) document.body.removeChild(menu); document.removeEventListener('click', close); };
                setTimeout(function() { document.addEventListener('click', close); }, 0);
            });
            label.appendChild(addClipBtn);
            row.appendChild(label);

            var lane = document.createElement('div');
            lane.className = 'vt-track-lane';
            lane.style.width = w + 'px';

            var clipsInTrack = state.clips.filter(function(c) {
                if (track.id != null) return c.track_id === track.id;
                return c.track_id === trackIndex || c.track_id === track.id;
            });

            clipsInTrack.forEach(function(clip) {
                var left = (parseFloat(clip.start_time) || 0) * pxPerSec;
                var width = Math.max(24, (parseFloat(clip.duration) || 1) * pxPerSec);
                var div = document.createElement('div');
                div.className = 'vt-clip vt-type-' + (clip.type || 'text');
                div.setAttribute('data-clip-id', clip.id || clip._cid || '');
                div.setAttribute('data-clip-cid', clip._cid || ('c' + Math.random().toString(36).slice(2)));
                if (!clip._cid) clip._cid = div.getAttribute('data-clip-cid');
                div.style.left = left + 'px';
                div.style.width = width + 'px';
                div.textContent = (clip.type || 'text') + ' ' + (clip.start_time != null ? clip.start_time.toFixed(1) : '0');
                if (state.selectedClipId && (clip.id === state.selectedClipId || clip._cid === state.selectedClipId)) {
                    div.classList.add('selected');
                }
                div.addEventListener('click', function(e) {
                    e.stopPropagation();
                    selectClip(clip);
                });
                var resize = document.createElement('div');
                resize.className = 'vt-clip-resize';
                resize.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    resizeClip(clip, e);
                });
                div.appendChild(resize);
                dragClip(div, clip);
                lane.appendChild(div);
            });

            row.appendChild(lane);
            container.appendChild(row);
        });
    }

    function selectClip(clip) {
        state.selectedClipId = clip.id || clip._cid;
        renderTracks();
        showProps(clip);
    }

    function showProps(clip) {
        if (!clip) return;
        var placeholder = document.getElementById('vt-props-placeholder');
        var addPanel = document.getElementById('vt-add-clip-panel');
        var form = document.getElementById('vt-props-form');
        if (!form) return;
        if (placeholder) placeholder.style.display = 'none';
        if (addPanel) addPanel.style.display = 'none';
        form.style.display = 'block';
        form.innerHTML = '';

        addField(form, 'Tip', null, null, true);
        var typeSelect = document.createElement('select');
        typeSelect.id = 'vt-prop-type';
        ['text', 'shape', 'image', 'video', 'audio'].forEach(function(t) {
            var o = document.createElement('option');
            o.value = t;
            o.textContent = t;
            if (t === (clip.type || 'text')) o.selected = true;
            typeSelect.appendChild(o);
        });
        form.appendChild(typeSelect);
        typeSelect.addEventListener('change', function() { clip.type = this.value; });

        addField(form, 'Başlangıç (sn)', 'vt-prop-start', clip.start_time, false, function(v) { clip.start_time = parseFloat(v) || 0; });
        addField(form, 'Süre (sn)', 'vt-prop-duration', clip.duration, false, function(v) { clip.duration = parseFloat(v) || 1; });

        if (!clip.content) clip.content = {};
        var c = clip.content;

        if (clip.type === 'text') {
            addField(form, 'Metin', 'vt-prop-text', c.text, false, function(v) { c.text = v; });
            addField(form, 'Font boyutu', 'vt-prop-font-size', c.font_size || 48, false, function(v) { c.font_size = parseInt(v, 10) || 48; });
            addField(form, 'Renk', 'vt-prop-color', c.color || '#ffffff', false, function(v) { c.color = v; });
            addField(form, 'X', 'vt-prop-x', c.x, false, function(v) { c.x = parseFloat(v) || 0; });
            addField(form, 'Y', 'vt-prop-y', c.y, false, function(v) { c.y = parseFloat(v) || 0; });
        }
        if (clip.type === 'shape') {
            var shapeSelect = document.createElement('select');
            shapeSelect.innerHTML = '<option value="rect">rect</option><option value="ellipse">ellipse</option><option value="line">line</option>';
            if (c.shape) shapeSelect.value = c.shape;
            form.appendChild(document.createElement('label')).textContent = 'Şekil';
            form.appendChild(shapeSelect);
            shapeSelect.addEventListener('change', function() { c.shape = this.value; });
            addField(form, 'Fill', 'vt-prop-fill', c.fill || '#ff0000', false, function(v) { c.fill = v; });
            addField(form, 'Opacity', 'vt-prop-opacity', c.opacity != null ? c.opacity : 0.8, false, function(v) { c.opacity = parseFloat(v) || 0.8; });
            addField(form, 'X', 'vt-prop-x', c.x, false, function(v) { c.x = parseFloat(v) || 0; });
            addField(form, 'Y', 'vt-prop-y', c.y, false, function(v) { c.y = parseFloat(v) || 0; });
            addField(form, 'Genişlik', 'vt-prop-width', c.width, false, function(v) { c.width = parseFloat(v) || 100; });
            addField(form, 'Yükseklik', 'vt-prop-height', c.height, false, function(v) { c.height = parseFloat(v) || 100; });
        }
        if (clip.type === 'video' || clip.type === 'image') {
            var mediaLabel = document.createElement('label');
            mediaLabel.textContent = clip.type === 'video' ? 'Video kaynağı' : 'Görsel kaynağı';
            form.appendChild(mediaLabel);
            var mediaWrap = document.createElement('div');
            mediaWrap.style.display = 'flex';
            mediaWrap.style.gap = '8px';
            mediaWrap.style.marginBottom = '8px';
            var urlInput = document.createElement('input');
            urlInput.type = 'text';
            urlInput.placeholder = 'URL veya medya kütüphanesinden seçin';
            urlInput.style.flex = '1';
            urlInput.value = clip.source || (c.source_url || '');
            urlInput.addEventListener('change', function() { clip.source = this.value; if (!c.source_url) c.source_url = this.value; });
            mediaWrap.appendChild(urlInput);
            var btnMedia = document.createElement('button');
            btnMedia.type = 'button';
            btnMedia.className = 'vt-btn-media';
            btnMedia.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px">folder_open</span> Medya seç';
            btnMedia.addEventListener('click', function() { openMediaModal(clip, clip.type); });
            mediaWrap.appendChild(btnMedia);
            form.appendChild(mediaWrap);
            if (clip.source) {
                var prev = document.createElement('div');
                prev.style.marginTop = '6px';
                prev.style.fontSize = '11px';
                prev.style.color = 'var(--text-muted,#6b7280)';
                prev.textContent = 'Seçili: ' + (clip.source.length > 50 ? clip.source.slice(0, 50) + '…' : clip.source);
                form.appendChild(prev);
            }
        }

        addField(form, 'Playhead (sn)', 'vt-playhead', state.playheadTime, false, function(v) { state.playheadTime = parseFloat(v) || 0; });

        if (!c.keyframes) c.keyframes = {};
        var kf = c.keyframes;
        var props = ['opacity', 'x', 'y', 'scaleX', 'scaleY', 'rotation'];
        props.forEach(function(prop) {
            var arr = kf[prop] || [];
            var h3 = document.createElement('h4');
            h3.style.marginTop = '12px';
            h3.style.fontSize = '12px';
            h3.textContent = 'Keyframes: ' + prop;
            form.appendChild(h3);
            var table = document.createElement('table');
            table.className = 'vt-keyframes-table';
            table.innerHTML = '<thead><tr><th>t (sn)</th><th>değer</th><th>easing</th></tr></thead><tbody></tbody>';
            var tbody = table.querySelector('tbody');
            arr.forEach(function(k) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><input type="number" step="0.1" value="' + (k.t != null ? k.t : 0) + '"></td><td><input type="text" value="' + (k.v != null ? k.v : '') + '"></td><td><input type="text" value="' + (k.easing || '') + '" placeholder="linear"></td>';
                tr.querySelector('td input').addEventListener('change', function() { k.t = parseFloat(this.value) || 0; });
                tr.querySelectorAll('td input')[1].addEventListener('change', function() { k.v = this.value; });
                tr.querySelectorAll('td input')[2].addEventListener('change', function() { k.easing = this.value; });
                tbody.appendChild(tr);
            });
            form.appendChild(table);
            var addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'vt-btn vt-btn-sm vt-keyframe-add';
            addBtn.textContent = 'Keyframe ekle (playhead)';
            addBtn.addEventListener('click', function() {
                var playheadEl = document.getElementById('vt-playhead');
                var t = playheadEl ? (parseFloat(playheadEl.value) || 0) : state.playheadTime;
                state.playheadTime = t;
                var v = arr.length ? arr[arr.length - 1].v : (prop === 'opacity' ? 1 : prop === 'scaleX' || prop === 'scaleY' ? 1 : 0);
                arr.push({ t: t, v: v });
                arr.sort(function(a, b) { return (a.t || 0) - (b.t || 0); });
                showProps(clip);
            });
            form.appendChild(addBtn);
        });

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'vt-btn vt-btn-sm';
        delBtn.style.marginTop = '12px';
        delBtn.style.background = '#dc2626';
        delBtn.style.color = '#fff';
        delBtn.textContent = 'Clip sil';
        delBtn.addEventListener('click', function() {
            state.clips = state.clips.filter(function(x) { return (x.id !== clip.id && x._cid !== clip._cid); });
            state.selectedClipId = null;
            var ph = document.getElementById('vt-props-placeholder');
            var addPanel = document.getElementById('vt-add-clip-panel');
            if (ph) ph.style.display = 'block';
            if (addPanel) addPanel.style.display = 'block';
            form.style.display = 'none';
            renderTracks();
        });
        form.appendChild(delBtn);
    }

    var currentMediaClip = null;

    function openMediaModal(clip, filterType) {
        currentMediaClip = clip;
        var modal = document.getElementById('vt-media-modal');
        var title = document.getElementById('vt-media-modal-title');
        var list = document.getElementById('vt-media-list');
        var empty = document.getElementById('vt-media-empty');
        if (!modal || !list) return;
        title.textContent = filterType === 'video' ? 'Video seç' : 'Görsel seç';
        list.innerHTML = '<p class="vt-modal-loading">Yükleniyor…</p>';
        empty.style.display = 'none';
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        var typeParam = filterType === 'video' ? 'video' : (filterType === 'image' ? 'image' : 'all');
        var url = mediaListUrl + (mediaListUrl.indexOf('?') !== -1 ? '&' : '?') + 'type=' + typeParam + '&p=1';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function(r) {
                var ct = (r.headers.get('Content-Type') || '').toLowerCase();
                if (!r.ok) {
                    throw new Error(r.status === 403 ? 'Yetki gerekli.' : r.status === 404 ? 'Medya listesi bulunamadı.' : 'İstek başarısız: ' + r.status);
                }
                if (ct.indexOf('application/json') === -1) {
                    throw new Error('Sunucu JSON döndürmedi. Giriş yapılmış mı kontrol edin.');
                }
                return r.json();
            })
            .then(function(data) {
                list.innerHTML = '';
                var items = (data && data.media) ? data.media : (Array.isArray(data) ? data : []);
                if (items.length === 0) {
                    empty.style.display = 'block';
                    return;
                }
                items.forEach(function(item) {
                    var url = item.file_url || item.url || '';
                    var div = document.createElement('div');
                    div.className = 'vt-media-item';
                    div.setAttribute('data-url', url);
                    div.setAttribute('data-id', item.id || '');
                    if (item.thumbnail_path) {
                        var img = document.createElement('img');
                        img.src = item.thumbnail_path;
                        img.alt = item.original_name || '';
                        div.appendChild(img);
                    } else if (item.mime_type && item.mime_type.indexOf('image') === 0 && url) {
                        var img = document.createElement('img');
                        img.src = url;
                        img.alt = item.original_name || '';
                        div.appendChild(img);
                    } else {
                        var pl = document.createElement('div');
                        pl.className = 'vt-media-placeholder';
                        pl.innerHTML = '<span class="material-symbols-outlined">' + (filterType === 'video' ? 'movie' : 'image') + '</span>';
                        div.appendChild(pl);
                    }
                    div.addEventListener('click', function() {
                        var clipToUpdate = currentMediaClip;
                        if (!clipToUpdate) return;
                        clipToUpdate.source = url;
                        if (!clipToUpdate.content) clipToUpdate.content = {};
                        clipToUpdate.content.source_url = url;
                        closeMediaModal();
                        showProps(clipToUpdate);
                        renderTracks();
                    });
                    list.appendChild(div);
                });
            })
            .catch(function(err) {
                list.innerHTML = '<p class="vt-modal-error">' + (err && err.message ? err.message : 'Liste yüklenemedi.') + '</p>';
            });
    }

    function closeMediaModal() {
        var modal = document.getElementById('vt-media-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        currentMediaClip = null;
    }

    function bindMediaModal() {
        var modal = document.getElementById('vt-media-modal');
        if (!modal) return;
        modal.querySelector('.vt-modal-backdrop').addEventListener('click', closeMediaModal);
        var closeBtn = modal.querySelector('.vt-modal-close');
        if (closeBtn) closeBtn.addEventListener('click', closeMediaModal);
    }

    function bindAddClipPanel() {
        var panel = document.getElementById('vt-add-clip-panel');
        if (!panel) return;
        panel.querySelectorAll('.vt-add-clip-card').forEach(function(card) {
            card.addEventListener('click', function() {
                var type = this.getAttribute('data-type');
                var trackIndex = state.tracks.length ? 0 : 0;
                if (state.tracks.length === 0) {
                    state.tracks.push({ id: null, name: 'Track 1', sort_order: 0, is_locked: 0, is_muted: 0 });
                    renderRuler();
                    renderTracks();
                }
                addClipToTrack(trackIndex, type);
                if (type === 'video' || type === 'image') {
                    setTimeout(function() { openMediaModal(state.clips[state.clips.length - 1], type); }, 150);
                }
            });
        });
    }

    function addField(form, labelText, id, value, readonly, onChange) {
        var label = document.createElement('label');
        label.textContent = labelText;
        form.appendChild(label);
        var input = document.createElement('input');
        input.type = typeof value === 'number' ? 'number' : 'text';
        if (id) input.id = id;
        input.value = value != null ? value : '';
        if (readonly) input.readOnly = true;
        if (onChange) input.addEventListener('change', function() { onChange(this.value); });
        form.appendChild(input);
    }

    function dragClip(div, clip) {
        var startX, startStart;
        div.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('vt-clip-resize')) return;
            startX = e.clientX;
            startStart = parseFloat(clip.start_time) || 0;
            var onMove = function(ev) {
                var dx = (ev.clientX - startX) / pxPerSec;
                var next = Math.max(0, startStart + dx);
                var dur = parseFloat(clip.duration) || 1;
                if (next + dur > getDuration()) next = getDuration() - dur;
                clip.start_time = next;
                div.style.left = (next * pxPerSec) + 'px';
            };
            var onUp = function() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    }

    function resizeClip(clip, e) {
        var startX = e.clientX;
        var startDur = parseFloat(clip.duration) || 1;
        var onMove = function(ev) {
            var dx = (ev.clientX - startX) / pxPerSec;
            var next = Math.max(0.1, startDur + dx);
            var maxDur = getDuration() - (parseFloat(clip.start_time) || 0);
            if (next > maxDur) next = maxDur;
            clip.duration = next;
            var div = app.querySelector('[data-clip-cid="' + (clip._cid || '') + '"]');
            if (div) div.style.width = (next * pxPerSec) + 'px';
        };
        var onUp = function() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }

    function bindAddTrack() {
        var btn = document.getElementById('vt-add-track');
        if (!btn) return;
        btn.addEventListener('click', function() {
            var newTrack = {
                id: null,
                name: 'Track ' + (state.tracks.length + 1),
                sort_order: state.tracks.length,
                is_locked: 0,
                is_muted: 0
            };
            state.tracks.push(newTrack);
            renderRuler();
            renderTracks();
        });
    }

    function addClipToTrack(trackIndex, type) {
        var track = state.tracks[trackIndex];
        if (!track) return;
        var start = 0;
        state.clips.forEach(function(c) {
            var tid = c.track_id;
            if (tid === track.id || state.tracks.indexOf(track) === (typeof tid === 'number' && tid < state.tracks.length ? tid : -1)) {
                var end = (parseFloat(c.start_time) || 0) + (parseFloat(c.duration) || 1);
                if (end > start) start = end;
            }
        });
        var content = { keyframes: {} };
        if (type === 'text') {
            content.text = 'Metin';
            content.font_size = 48;
            content.color = '#ffffff';
            content.x = 100;
            content.y = 200;
        }
        if (type === 'shape') {
            content.shape = 'rect';
            content.fill = '#ff0000';
            content.opacity = 0.8;
            content.x = 0;
            content.y = 0;
            content.width = 200;
            content.height = 100;
        }
        var clip = {
                id: null,
                track_id: track.id != null ? track.id : trackIndex,
                type: type || 'text',
                start_time: start,
                duration: 2,
                sort_order: state.clips.length,
                source: null,
                content: content
            };
        state.clips.push(clip);
        renderTracks();
        selectClip(clip);
    }

    function bindSave() {
        var btn = document.getElementById('vt-save');
        if (!btn) return;
        btn.addEventListener('click', function() {
            var nameEl = document.getElementById('vt-name');
            var widthEl = document.getElementById('vt-width');
            var heightEl = document.getElementById('vt-height');
            var durationEl = document.getElementById('vt-duration');
            state.timeline.name = nameEl ? nameEl.value : state.timeline.name;
            state.timeline.width = widthEl ? parseInt(widthEl.value, 10) : state.timeline.width;
            state.timeline.height = heightEl ? parseInt(heightEl.value, 10) : state.timeline.height;
            state.timeline.duration_sec = durationEl ? parseFloat(durationEl.value) : state.timeline.duration_sec;

            var payload = {
                timeline: {
                    id: state.timeline.id,
                    name: state.timeline.name,
                    width: state.timeline.width,
                    height: state.timeline.height,
                    fps: state.timeline.fps || 25,
                    duration_sec: state.timeline.duration_sec,
                    background_color: state.timeline.background_color || '#000000',
                    settings: state.timeline.settings || null
                },
                tracks: state.tracks.map(function(t, i) {
                    return {
                        id: t.id,
                        name: t.name || ('Track ' + (i + 1)),
                        sort_order: i,
                        is_locked: t.is_locked ? 1 : 0,
                        is_muted: t.is_muted ? 1 : 0
                    };
                }),
                clips: state.clips.map(function(c, i) {
                    var trackId = c.track_id;
                    if (typeof trackId === 'number' && trackId >= 0 && state.tracks[trackId] != null) {
                        trackId = state.tracks[trackId].id != null ? state.tracks[trackId].id : trackId;
                    }
                    return {
                        id: c.id || null,
                        track_id: trackId,
                        type: c.type || 'text',
                        start_time: parseFloat(c.start_time) || 0,
                        duration: parseFloat(c.duration) || 1,
                        sort_order: i,
                        source: c.source || null,
                        content: c.content || {}
                    };
                })
            };

            btn.disabled = true;
            btn.textContent = 'Kaydediliyor...';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', saveUrl);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined">save</span> Kaydet';
                var res = {};
                try { res = JSON.parse(xhr.responseText); } catch (e) {}
                if (res.success) {
                    alert('Kaydedildi.');
                    window.location.reload();
                } else {
                    alert('Hata: ' + (res.message || xhr.statusText));
                }
            };
            xhr.onerror = function() {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined">save</span> Kaydet';
                alert('Kayıt sırasında hata oluştu.');
            };
            xhr.send(JSON.stringify(payload));
        });
    }

    init();
})();
