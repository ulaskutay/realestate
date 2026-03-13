(function() {
    'use strict';

    var PX_PER_SEC_MIN = 6;
    var PX_PER_SEC_MAX = 240;
    var PX_PER_SEC_DEFAULT = 60;
    var HISTORY_MAX = 80;
    var SNAP_GRID_SEC = 0.5;
    var TRACK_LABEL_WIDTH = 140;

    var state = {
        timeline: {},
        tracks: [],
        clips: [],
        selectedClipIds: [],
        playheadTime: 0,
        pxPerSec: PX_PER_SEC_DEFAULT,
        snapEnabled: true,
        isPlaying: false,
        playAnimationId: null,
        previewVideoEl: null,
        previewVideoClip: null,
        history: [],
        redoStack: [],
        clipboard: [],
        trackHeight: 'normal',
        contentFilter: 'all',
        previewZoom: 1,
        previewBaseWidth: 0,
        previewBaseHeight: 0,
        selectedTrackIndex: 0
    };

    var PREVIEW_ZOOM_MIN = 0.25;
    var PREVIEW_ZOOM_MAX = 3;
    var PREVIEW_ZOOM_STEP = 0.25;

    var app = document.getElementById('vt-app');
    if (!app) return;

    var timelineId = parseInt(app.getAttribute('data-timeline-id'), 10);
    var saveUrl = app.getAttribute('data-save-url') || '';
    var mediaListUrl = app.getAttribute('data-media-list-url') || '';

    function getFps() {
        return (state.timeline.fps && state.timeline.fps > 0) ? state.timeline.fps : 25;
    }

    function formatTime(sec) {
        if (sec == null || isNaN(sec)) sec = 0;
        var m = Math.floor(sec / 60);
        var s = sec - m * 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s.toFixed(1);
    }

    function getDuration() {
        var v = parseFloat(document.getElementById('vt-duration') && document.getElementById('vt-duration').value);
        return isNaN(v) || v <= 0 ? 10 : v;
    }

    function getTimelineWidth() {
        return getDuration() * state.pxPerSec;
    }

    function snapToGrid(t) {
        if (!state.snapEnabled) return t;
        var frame = 1 / getFps();
        var grid = Math.min(SNAP_GRID_SEC, frame);
        return Math.round(t / grid) * grid;
    }

    function pushSnapshot() {
        var snap = {
            timeline: JSON.parse(JSON.stringify(state.timeline)),
            tracks: JSON.parse(JSON.stringify(state.tracks)),
            clips: JSON.parse(JSON.stringify(state.clips))
        };
        state.history.push(snap);
        if (state.history.length > HISTORY_MAX) state.history.shift();
        state.redoStack = [];
        updateUndoRedoButtons();
    }

    function updateUndoRedoButtons() {
        var undoBtn = document.getElementById('vt-undo');
        var redoBtn = document.getElementById('vt-redo');
        if (undoBtn) undoBtn.disabled = state.history.length === 0;
        if (redoBtn) redoBtn.disabled = state.redoStack.length === 0;
    }

    function undo() {
        if (state.history.length === 0) return;
        state.redoStack.push({
            timeline: JSON.parse(JSON.stringify(state.timeline)),
            tracks: JSON.parse(JSON.stringify(state.tracks)),
            clips: JSON.parse(JSON.stringify(state.clips))
        });
        var snap = state.history.pop();
        state.timeline = snap.timeline;
        state.tracks = snap.tracks;
        state.clips = snap.clips;
        state.selectedClipIds = [];
        updateUndoRedoButtons();
        renderRuler();
        renderTracks();
        updateTimeDisplay();
        renderPlayhead();
        updatePreview();
        showPropsPlaceholder();
    }

    function redo() {
        if (state.redoStack.length === 0) return;
        state.history.push({
            timeline: JSON.parse(JSON.stringify(state.timeline)),
            tracks: JSON.parse(JSON.stringify(state.tracks)),
            clips: JSON.parse(JSON.stringify(state.clips))
        });
        var snap = state.redoStack.pop();
        state.timeline = snap.timeline;
        state.tracks = snap.tracks;
        state.clips = snap.clips;
        state.selectedClipIds = [];
        updateUndoRedoButtons();
        renderRuler();
        renderTracks();
        updateTimeDisplay();
        renderPlayhead();
        updatePreview();
        showPropsPlaceholder();
    }

    function showPropsPlaceholder() {
        var placeholder = document.getElementById('vt-props-placeholder');
        var addPanel = document.getElementById('vt-add-clip-panel');
        var form = document.getElementById('vt-props-form');
        if (placeholder) placeholder.style.display = 'block';
        if (addPanel) addPanel.style.display = 'block';
        if (form) { form.style.display = 'none'; form.innerHTML = ''; }
    }

    function updateTimeDisplay() {
        var t = state.playheadTime;
        var el = document.getElementById('vt-time-display');
        var headerEl = document.getElementById('vt-header-time');
        var s = formatTime(t);
        if (el) el.textContent = s;
        if (headerEl) headerEl.textContent = s;
    }

    function renderPlayhead() {
        var x = state.playheadTime * state.pxPerSec;
        var line = document.getElementById('vt-playhead-line');
        var trackLine = document.getElementById('vt-playhead-track');
        if (line) {
            line.style.left = x + 'px';
            line.style.display = 'block';
        }
        if (trackLine) {
            trackLine.style.left = (TRACK_LABEL_WIDTH + x) + 'px';
            trackLine.style.width = '3px';
            trackLine.style.display = 'block';
        }
    }

    function bindRulerClick() {
        var ruler = document.getElementById('vt-ruler');
        var body = document.getElementById('vt-timeline-body');
        if (ruler) {
            ruler.addEventListener('click', function(e) {
                var rect = ruler.getBoundingClientRect();
                var lane = document.querySelector('.vt-ruler');
                if (!lane) return;
                var left = ruler.scrollLeft || 0;
                var clickX = e.clientX - rect.left + left;
                var t = clickX / state.pxPerSec;
                t = Math.max(0, Math.min(getDuration(), t));
                state.playheadTime = snapToGrid(t);
                updateTimeDisplay();
                renderPlayhead();
                updatePreview();
            });
        }
        if (body) {
            body.addEventListener('click', function(e) {
                if (e.target.closest('.vt-track-lane') && !e.target.closest('.vt-clip')) {
                    var lane = e.target.closest('.vt-track-lane');
                    if (!lane) return;
                    var rect = lane.getBoundingClientRect();
                    var scrollLeft = lane.scrollLeft || 0;
                    var clickX = e.clientX - rect.left + scrollLeft;
                    var t = clickX / state.pxPerSec;
                    t = Math.max(0, Math.min(getDuration(), t));
                    state.playheadTime = snapToGrid(t);
                    updateTimeDisplay();
                    renderPlayhead();
                    updatePreview();
                }
            });
        }
    }

    function bindPlayheadDrag() {
        var trackLine = document.getElementById('vt-playhead-track');
        var ruler = document.getElementById('vt-ruler');

        function startDrag(e) {
            e.preventDefault();
            e.stopPropagation();
            var startX = e.clientX;
            var startT = state.playheadTime;
            function onMove(ev) {
                var dx = ev.clientX - startX;
                var dt = dx / state.pxPerSec;
                var next = Math.max(0, Math.min(getDuration(), startT + dt));
                state.playheadTime = state.snapEnabled ? snapToGrid(next) : next;
                updateTimeDisplay();
                renderPlayhead();
                updatePreview();
            }
            function onUp() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        }

        if (trackLine) trackLine.addEventListener('mousedown', startDrag);
        if (ruler) ruler.addEventListener('mousedown', startDrag);
    }

    function interpolateKeyframe(keyframes, prop, t) {
        var arr = keyframes && keyframes[prop] ? keyframes[prop] : [];
        if (arr.length === 0) return null;
        arr = arr.slice().sort(function(a, b) { return (a.t || 0) - (b.t || 0); });
        var before = null, after = null;
        for (var i = 0; i < arr.length; i++) {
            if ((arr[i].t || 0) <= t) before = arr[i];
            if ((arr[i].t || 0) >= t && !after) after = arr[i];
        }
        if (before && !after) return before.v;
        if (!before && after) return after.v;
        if (!before && !after) return null;
        var t0 = before.t, t1 = after.t;
        if (t0 === t1) return after.v;
        var v0 = parseFloat(before.v), v1 = parseFloat(after.v);
        if (isNaN(v0) || isNaN(v1)) return after.v;
        var frac = (t - t0) / (t1 - t0);
        return v0 + (v1 - v0) * frac;
    }

    function getClipValueAtTime(clip, prop, t) {
        var c = clip.content || {};
        var kf = c.keyframes || {};
        var base = c[prop];
        if (prop === 'opacity' && base == null) base = 1;
        if (prop === 'x' || prop === 'y') base = base != null ? base : 0;
        if (prop === 'scaleX' || prop === 'scaleY') base = base != null ? base : 1;
        if (prop === 'rotation') base = base != null ? base : 0;
        var k = interpolateKeyframe(kf, prop, t - (parseFloat(clip.start_time) || 0));
        return k != null ? k : base;
    }

    function stopPreviewVideo() {
        if (state.previewVideoEl) {
            try {
                state.previewVideoEl.pause();
                if (state.previewVideoEl.parentNode) state.previewVideoEl.parentNode.removeChild(state.previewVideoEl);
            } catch (e) {}
            state.previewVideoEl = null;
            state.previewVideoClip = null;
        }
    }

    function wrapVideoWithCrop(vid, content, canvasWidth, canvasHeight) {
        var c = content || {};
        var cropLeft = Math.max(0, Math.min(1, parseFloat(c.cropLeft) || 0));
        var cropTop = Math.max(0, Math.min(1, parseFloat(c.cropTop) || 0));
        var cropWidth = Math.max(0.01, Math.min(1, parseFloat(c.cropWidth) || 1));
        var cropHeight = Math.max(0.01, Math.min(1, parseFloat(c.cropHeight) || 1));
        if (cropWidth >= 0.999 && cropHeight >= 0.999 && cropLeft <= 0.001 && cropTop <= 0.001) {
            vid.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;object-fit:contain;display:block;z-index:1;';
            return null;
        }
        var wrap = document.createElement('div');
        wrap.className = 'vt-video-crop-wrap';
        wrap.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;overflow:hidden;z-index:1;';
        var wPct = 100 / cropWidth;
        var hPct = 100 / cropHeight;
        var leftPct = -(cropLeft / cropWidth) * 100;
        var topPct = -(cropTop / cropHeight) * 100;
        vid.style.cssText = 'position:absolute;left:' + leftPct + '%;top:' + topPct + '%;width:' + wPct + '%;height:' + hPct + '%;object-fit:fill;display:block;';
        wrap.appendChild(vid);
        return wrap;
    }

    function wrapLayerTransform(clip, t, preview, designWidth, designHeight, contentW, contentH) {
        var c = clip.content || {};
        var scaleX = preview.offsetWidth / (designWidth || 1920);
        var scaleY = preview.offsetHeight / (designHeight || 1080);
        var xVal = (getClipValueAtTime(clip, 'x', t) || 0) * scaleX;
        var yVal = (getClipValueAtTime(clip, 'y', t) || 0) * scaleY;
        var sxVal = getClipValueAtTime(clip, 'scaleX', t) != null ? getClipValueAtTime(clip, 'scaleX', t) : 1;
        var syVal = getClipValueAtTime(clip, 'scaleY', t) != null ? getClipValueAtTime(clip, 'scaleY', t) : 1;
        var wrap = document.createElement('div');
        wrap.className = 'vt-layer-transform-wrap';
        if (contentW != null && contentH != null && contentW > 0 && contentH > 0) {
            var wPx = contentW * scaleX * sxVal;
            var hPx = contentH * scaleY * syVal;
            wrap.style.cssText = 'position:absolute;left:' + xVal + 'px;top:' + yVal + 'px;width:' + wPx + 'px;height:' + hPx + 'px;transform-origin:0 0;pointer-events:none;overflow:hidden;';
        } else {
            wrap.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;transform-origin:0 0;pointer-events:none;';
            wrap.style.transform = 'translate(' + xVal + 'px,' + yVal + 'px) scale(' + sxVal + ',' + syVal + ')';
        }
        return wrap;
    }

    function updatePreview(isPlaying) {
        isPlaying = arguments.length > 0 ? !!isPlaying : state.isPlaying;
        var preview = document.getElementById('vt-preview');
        if (!preview) return;
        var t = state.playheadTime;
        var bg = (state.timeline.background_color || '#000000');
        preview.style.backgroundColor = bg;
        var width = parseInt(preview.getAttribute('data-width'), 10) || 1920;
        var height = parseInt(preview.getAttribute('data-height'), 10) || 1080;
        var activeClips = state.clips.filter(function(c) {
            var start = parseFloat(c.start_time) || 0;
            var dur = parseFloat(c.duration) || 1;
            return t >= start && t < start + dur;
        });
        var typeOrder = { video: 0, image: 1, shape: 2, text: 3 };
        activeClips.sort(function(a, b) {
            var orderA = typeOrder[a.type] != null ? typeOrder[a.type] : 0;
            var orderB = typeOrder[b.type] != null ? typeOrder[b.type] : 0;
            if (orderA !== orderB) return orderA - orderB;
            var trackA = state.tracks.findIndex(function(tr) { return (tr.id != null && Number(tr.id) === Number(a.track_id)) || state.tracks[a.track_id] === tr; });
            var trackB = state.tracks.findIndex(function(tr) { return (tr.id != null && Number(tr.id) === Number(b.track_id)) || state.tracks[b.track_id] === tr; });
            return trackB - trackA;
        });
        var emptyEl = preview.querySelector('.vt-preview-empty');
        var layerContainer = preview.querySelector('.vt-preview-layers');
        if (!layerContainer) {
            layerContainer = document.createElement('div');
            layerContainer.className = 'vt-preview-layers';
            layerContainer.style.cssText = 'position:absolute;inset:0;pointer-events:none;';
            preview.appendChild(layerContainer);
        }
        if (!preview.querySelector('.vt-preview-transform-overlay')) {
            var ov = document.createElement('div');
            ov.className = 'vt-preview-transform-overlay';
            ov.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:10;';
            preview.appendChild(ov);
        }
        var firstVideoClip = null;
        for (var i = 0; i < activeClips.length; i++) {
            var vc = activeClips[i];
            if (vc.type === 'video' && (vc.source || (vc.content && vc.content.source_url))) {
                firstVideoClip = vc;
                break;
            }
        }
        var useSingleVideoPlayback = isPlaying && firstVideoClip;
        var firstVideoSourceUrl = firstVideoClip ? (firstVideoClip.source || (firstVideoClip.content && firstVideoClip.content.source_url) || '') : '';
        var reusedVideoWrap = null;
        if (firstVideoClip && !useSingleVideoPlayback && state.previewVideoEl && state.previewVideoClip === firstVideoClip && state.previewVideoEl.src === firstVideoSourceUrl) {
            var existingWrap = state.previewVideoEl.closest('.vt-layer-transform-wrap');
            if (existingWrap && existingWrap.parentNode) {
                existingWrap.parentNode.removeChild(existingWrap);
                reusedVideoWrap = existingWrap;
            }
        }
        layerContainer.innerHTML = '';
        if (useSingleVideoPlayback) {
            var clip = firstVideoClip;
            var sourceUrl = clip.source || (clip.content && clip.content.source_url) || '';
            var clipStart = parseFloat(clip.start_time) || 0;
            var clipEnd = clipStart + (parseFloat(clip.duration) || 1);
            if (!state.previewVideoEl || state.previewVideoClip !== clip || state.previewVideoEl.src !== sourceUrl) {
                stopPreviewVideo();
                var vid = document.createElement('video');
                vid.className = 'vt-preview-vid vt-layer-video';
                vid.muted = getVideoMuted(clip);
                vid.playsInline = true;
                vid.preload = 'auto';
                vid.src = sourceUrl;
                var startRel = t - clipStart;
                vid.currentTime = Math.max(0, startRel);
                vid.addEventListener('loadeddata', function() {
                    if (state.isPlaying && state.previewVideoEl === vid) {
                        vid.currentTime = Math.max(0, startRel);
                        vid.play().catch(function() {});
                    }
                });
                vid.addEventListener('canplay', function() {
                    if (state.isPlaying && state.previewVideoEl === vid && vid.paused) {
                        var rel = Math.max(0, state.playheadTime - clipStart);
                        vid.currentTime = Math.min(rel, vid.duration && !isNaN(vid.duration) ? vid.duration : rel);
                        vid.play().catch(function() {});
                    }
                });
                vid.addEventListener('ended', function() {
                    if (state.isPlaying) state.playheadTime = clipEnd;
                });
                var cropWrap = wrapVideoWithCrop(vid, clip.content, width, height);
                var videoWrap = wrapLayerTransform(clip, t, preview, width, height);
                videoWrap.appendChild(cropWrap || vid);
                layerContainer.appendChild(videoWrap);
                state.previewVideoEl = vid;
                state.previewVideoClip = clip;
                vid.play().catch(function() {});
            } else {
                var targetRel = Math.max(0, t - clipStart);
                var diff = Math.abs((state.previewVideoEl.currentTime || 0) - targetRel);
                if (state.previewVideoEl.paused || diff > 0.2) {
                    state.previewVideoEl.currentTime = targetRel;
                }
                if (state.previewVideoEl.paused) state.previewVideoEl.play().catch(function() {});
                state.previewVideoEl.muted = getVideoMuted(clip);
                var cropWrap = wrapVideoWithCrop(state.previewVideoEl, clip.content, width, height);
                var videoWrap = wrapLayerTransform(clip, t, preview, width, height);
                videoWrap.appendChild(cropWrap || state.previewVideoEl);
                layerContainer.appendChild(videoWrap);
            }
        } else if (firstVideoClip && reusedVideoWrap) {
            var clip = firstVideoClip;
            var clipStart = parseFloat(clip.start_time) || 0;
            state.previewVideoEl.muted = getVideoMuted(clip);
            reusedVideoWrap.style.transform = 'translate(' + ((getClipValueAtTime(clip, 'x', t) || 0) * (preview.offsetWidth / width)) + 'px,' + ((getClipValueAtTime(clip, 'y', t) || 0) * (preview.offsetHeight / height)) + 'px) scale(' + (getClipValueAtTime(clip, 'scaleX', t) != null ? getClipValueAtTime(clip, 'scaleX', t) : 1) + ',' + (getClipValueAtTime(clip, 'scaleY', t) != null ? getClipValueAtTime(clip, 'scaleY', t) : 1) + ')';
            state.previewVideoEl.currentTime = Math.max(0, t - clipStart);
            layerContainer.appendChild(reusedVideoWrap);
        } else if (firstVideoClip) {
            var clip = firstVideoClip;
            var sourceUrl = clip.source || (clip.content && clip.content.source_url) || '';
            var clipStart = parseFloat(clip.start_time) || 0;
            stopPreviewVideo();
            var vid = document.createElement('video');
            vid.className = 'vt-preview-vid vt-layer-video';
            vid.muted = getVideoMuted(clip);
            vid.playsInline = true;
            vid.preload = 'auto';
            vid.src = sourceUrl;
            vid.currentTime = Math.max(0, t - clipStart);
            var cropWrap = wrapVideoWithCrop(vid, clip.content, width, height);
            var videoWrap = wrapLayerTransform(clip, t, preview, width, height);
            videoWrap.appendChild(cropWrap || vid);
            layerContainer.appendChild(videoWrap);
            state.previewVideoEl = vid;
            state.previewVideoClip = clip;
        } else {
            stopPreviewVideo();
        }
        var overlayZIndex = { image: 2, shape: 3, text: 4 };
        activeClips.forEach(function(clip) {
            if (clip === firstVideoClip) return;
            var relT = t - (parseFloat(clip.start_time) || 0);
            var c = clip.content || {};
            var sourceUrl = clip.source || (c && c.source_url) || '';
            if (clip.type === 'text') {
                var el = document.createElement('div');
                el.className = 'vt-preview-text-layer vt-layer-text';
                el.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:' + (overlayZIndex.text || 4) + ';';
                var x = (getClipValueAtTime(clip, 'x', t) || 0) * (preview.offsetWidth / width);
                var y = (getClipValueAtTime(clip, 'y', t) || 0) * (preview.offsetHeight / height);
                var scaleX = getClipValueAtTime(clip, 'scaleX', t);
                var scaleY = getClipValueAtTime(clip, 'scaleY', t);
                var rot = getClipValueAtTime(clip, 'rotation', t);
                var op = getClipValueAtTime(clip, 'opacity', t);
                var fontSize = Math.max(12, (c.font_size || 48) * (preview.offsetHeight / height));
                var fontFamily = (c.font_family || 'system-ui').replace(/"/g, '');
                var color = c.color || '#ffffff';
                var fontWeight = c.font_weight || 'normal';
                var textAlign = c.text_align || 'left';
                var textContent = (c.text || '').trim();
                if (!textContent) textContent = ' ';
                el.innerHTML = '<span style="position:absolute;left:' + x + 'px;top:' + y + 'px;max-width:85%;font-size:' + fontSize + 'px;font-family:' + fontFamily + ';font-weight:' + fontWeight + ';color:' + color + ';text-align:' + textAlign + ';transform:scale(' + (scaleX != null ? scaleX : 1) + ',' + (scaleY != null ? scaleY : 1) + ') rotate(' + (rot || 0) + 'deg);transform-origin:left top;opacity:' + (op != null ? op : 1) + ';white-space:pre-wrap;word-break:break-word;text-shadow:0 1px 2px rgba(0,0,0,0.8);z-index:2;">' + textContent.replace(/</g, '&lt;').replace(/\n/g, '<br>') + '</span>';
                layerContainer.appendChild(el);
            }
            if (clip.type === 'shape') {
                var shapeEl = document.createElement('div');
                shapeEl.className = 'vt-layer-shape';
                shapeEl.style.zIndex = String(overlayZIndex.shape || 3);
                var x = (getClipValueAtTime(clip, 'x', t) || 0) * (preview.offsetWidth / width);
                var y = (getClipValueAtTime(clip, 'y', t) || 0) * (preview.offsetHeight / height);
                var w = (c.width || 100) * (preview.offsetWidth / width);
                var h = (c.height || 100) * (preview.offsetHeight / height);
                var op = getClipValueAtTime(clip, 'opacity', t);
                shapeEl.style.cssText = 'position:absolute;left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h + 'px;background:' + (c.fill || '#f00') + ';opacity:' + (op != null ? op : 0.8) + ';border-radius:' + (c.shape === 'ellipse' ? '50%' : '0') + ';';
                layerContainer.appendChild(shapeEl);
            }
            if (clip.type === 'image' && sourceUrl) {
                var img = document.createElement('img');
                img.className = 'vt-preview-img vt-layer-image';
                img.alt = '';
                img.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;object-fit:contain;display:block;';
                img.src = sourceUrl;
                var cw = c.width, ch = c.height;
                img.onload = function() {
                    if ((!cw || !ch) && img.naturalWidth && img.naturalHeight) {
                        c.width = img.naturalWidth;
                        c.height = img.naturalHeight;
                        updateTransformOverlay();
                        updatePreview();
                    }
                };
                if (!cw || !ch) {
                    cw = cw || 400;
                    ch = ch || 300;
                }
                var imgCropWrap = wrapVideoWithCrop(img, c, width, height);
                var imgWrap = wrapLayerTransform(clip, t, preview, width, height, cw, ch);
                imgWrap.style.zIndex = String(overlayZIndex.image || 2);
                imgWrap.appendChild(imgCropWrap || img);
                layerContainer.appendChild(imgWrap);
            }
            if (clip.type === 'video' && sourceUrl) {
                var vid = document.createElement('video');
                vid.className = 'vt-preview-vid vt-layer-video';
                vid.muted = getVideoMuted(clip);
                vid.playsInline = true;
                vid.preload = 'auto';
                vid.src = sourceUrl;
                var targetTime = relT;
                function seekToTime() {
                    if (vid.duration && !isNaN(vid.duration) && targetTime <= vid.duration) {
                        vid.currentTime = Math.max(0, targetTime);
                    } else {
                        vid.currentTime = targetTime;
                    }
                }
                vid.addEventListener('loadeddata', seekToTime);
                vid.addEventListener('loadedmetadata', seekToTime);
                seekToTime();
                var cropWrap = wrapVideoWithCrop(vid, c, width, height);
                var vidWrap = wrapLayerTransform(clip, t, preview, width, height);
                vidWrap.style.zIndex = String(overlayZIndex.image || 2);
                vidWrap.appendChild(cropWrap || vid);
                layerContainer.appendChild(vidWrap);
            }
        });
        if (emptyEl) {
            emptyEl.style.display = (activeClips.length === 0) ? 'block' : 'none';
        }
        updateTransformOverlay();
    }

    function updateTransformOverlay() {
        var preview = document.getElementById('vt-preview');
        var overlay = preview ? preview.querySelector('.vt-preview-transform-overlay') : null;
        if (!overlay) return;
        var sel = getSelectedClips();
        if (sel.length !== 1) {
            overlay.style.display = 'none';
            overlay.style.pointerEvents = 'none';
            overlay.innerHTML = '';
            return;
        }
        var clip = sel[0];
        if (clip.type !== 'text' && clip.type !== 'shape' && clip.type !== 'video' && clip.type !== 'image') {
            overlay.style.display = 'none';
            overlay.style.pointerEvents = 'none';
            overlay.innerHTML = '';
            return;
        }
        var t = state.playheadTime;
        var start = parseFloat(clip.start_time) || 0;
        var dur = parseFloat(clip.duration) || 1;
        if (t < start || t >= start + dur) {
            overlay.style.display = 'none';
            overlay.style.pointerEvents = 'none';
            overlay.innerHTML = '';
            return;
        }
        var width = parseInt(preview.getAttribute('data-width'), 10) || 1920;
        var height = parseInt(preview.getAttribute('data-height'), 10) || 1080;
        var scaleX = preview.offsetWidth / width;
        var scaleY = preview.offsetHeight / height;
        var c = clip.content || {};
        if (!clip.content) clip.content = c;

        var boxLeft, boxTop, boxW, boxH;
        if (clip.type === 'shape') {
            boxLeft = (c.x || 0) * scaleX;
            boxTop = (c.y || 0) * scaleY;
            boxW = (c.width || 100) * scaleX;
            boxH = (c.height || 100) * scaleY;
        } else if (clip.type === 'video' || clip.type === 'image') {
            var sx = getClipValueAtTime(clip, 'scaleX', t) != null ? getClipValueAtTime(clip, 'scaleX', t) : 1;
            var sy = getClipValueAtTime(clip, 'scaleY', t) != null ? getClipValueAtTime(clip, 'scaleY', t) : 1;
            boxLeft = (c.x || 0) * scaleX;
            boxTop = (c.y || 0) * scaleY;
            if (clip.type === 'image' && (c.width > 0 && c.height > 0)) {
                boxW = (c.width || 400) * scaleX * (sx || 1);
                boxH = (c.height || 300) * scaleY * (sy || 1);
            } else {
                boxW = preview.offsetWidth * (sx || 1);
                boxH = preview.offsetHeight * (sy || 1);
            }
        } else {
            var sx = getClipValueAtTime(clip, 'scaleX', t) != null ? getClipValueAtTime(clip, 'scaleX', t) : 1;
            var sy = getClipValueAtTime(clip, 'scaleY', t) != null ? getClipValueAtTime(clip, 'scaleY', t) : 1;
            boxLeft = (c.x || 0) * scaleX;
            boxTop = (c.y || 0) * scaleY;
            var textStr = (c.text || '').trim() || ' ';
            var fontSize = c.font_size || 48;
            var lines = textStr.split(/\n/);
            var maxLineLen = Math.max(1, lines.length ? Math.max.apply(null, lines.map(function(l) { return l.length; })) : 1);
            var approxTextW = Math.min(1200, Math.max(20, maxLineLen * fontSize * 0.55));
            var approxTextH = Math.min(800, Math.max(16, lines.length * fontSize * 1.25));
            var useW = (c.textBoxWidth != null && c.textBoxWidth > 0) ? c.textBoxWidth : approxTextW;
            var useH = (c.textBoxHeight != null && c.textBoxHeight > 0) ? c.textBoxHeight : approxTextH;
            boxW = useW * sx * scaleX;
            boxH = useH * sy * scaleY;
        }

        overlay.style.display = 'block';
        overlay.style.pointerEvents = 'auto';
        overlay.innerHTML = '';
        var box = document.createElement('div');
        box.className = 'vt-transform-box';
        box.style.cssText = 'position:absolute;left:' + boxLeft + 'px;top:' + boxTop + 'px;width:' + boxW + 'px;height:' + Math.max(boxH, 20) + 'px;border:2px solid var(--vt-primary, #2563eb);box-sizing:border-box;cursor:move;';
        overlay.appendChild(box);

        var handle = document.createElement('div');
        handle.className = 'vt-transform-handle vt-transform-handle-se';
        handle.style.cssText = 'position:absolute;width:12px;height:12px;background:var(--vt-primary, #2563eb);border-radius:2px;cursor:nwse-resize;';
        handle.style.left = (boxLeft + boxW - 6) + 'px';
        handle.style.top = (boxTop + Math.max(boxH, 20) - 6) + 'px';
        overlay.appendChild(handle);

        box.addEventListener('mousedown', function(e) {
            e.preventDefault();
            if (e.target !== box && !e.target.classList.contains('vt-transform-box')) return;
            pushSnapshot();
            var startMX = e.clientX, startMY = e.clientY;
            var startCX = (c.x != null ? c.x : 0), startCY = (c.y != null ? c.y : 0);
            function onMove(ev) {
                var dx = (ev.clientX - startMX) / scaleX;
                var dy = (ev.clientY - startMY) / scaleY;
                c.x = startCX + dx;
                c.y = startCY + dy;
                updateTransformOverlay();
                updatePreview();
            }
            function onUp() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        handle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            pushSnapshot();
            var startMX = e.clientX, startMY = e.clientY;
            if (clip.type === 'shape') {
                var startW = (c.width != null ? c.width : 100), startH = (c.height != null ? c.height : 100);
                function onMove(ev) {
                    var dw = (ev.clientX - startMX) / scaleX;
                    var dh = (ev.clientY - startMY) / scaleY;
                    c.width = Math.max(10, startW + dw);
                    c.height = Math.max(10, startH + dh);
                    updateTransformOverlay();
                    updatePreview();
                }
                function onUp() {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                }
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            } else if (clip.type === 'video' || clip.type === 'image') {
                var startSx = getClipValueAtTime(clip, 'scaleX', t) != null ? getClipValueAtTime(clip, 'scaleX', t) : 1;
                var startSy = getClipValueAtTime(clip, 'scaleY', t) != null ? getClipValueAtTime(clip, 'scaleY', t) : 1;
                var baseW = width, baseH = height;
                function onMove(ev) {
                    var dw = (ev.clientX - startMX) / scaleX;
                    var dh = (ev.clientY - startMY) / scaleY;
                    var delta = (dw / baseW + dh / baseH) / 2;
                    var s = Math.max(0.1, Math.min(5, startSx + delta));
                    c.scaleX = s;
                    c.scaleY = s;
                    updateTransformOverlay();
                    updatePreview();
                }
                function onUp() {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                }
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            } else {
                var startSx = getClipValueAtTime(clip, 'scaleX', t) != null ? getClipValueAtTime(clip, 'scaleX', t) : 1;
                var startSy = getClipValueAtTime(clip, 'scaleY', t) != null ? getClipValueAtTime(clip, 'scaleY', t) : 1;
                var baseW = c.textBoxWidth || 100, baseH = c.textBoxHeight || 50;
                function onMove(ev) {
                    var dw = (ev.clientX - startMX) / scaleX;
                    var dh = (ev.clientY - startMY) / scaleY;
                    var delta = (dw / baseW + dh / baseH) / 2;
                    var s = Math.max(0.1, Math.min(5, startSx + delta));
                    c.scaleX = s;
                    c.scaleY = s;
                    updateTransformOverlay();
                    updatePreview();
                }
                function onUp() {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                }
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            }
        });
    }

    function startPlayback() {
        if (state.isPlaying) return;
        state.isPlaying = true;
        var playBtn = document.getElementById('vt-play');
        var pauseBtn = document.getElementById('vt-pause');
        if (playBtn) playBtn.style.display = 'none';
        if (pauseBtn) pauseBtn.style.display = 'inline-flex';
        var startTime = performance.now();
        var startPlayhead = state.playheadTime;
        function tick(now) {
            if (!state.isPlaying) return;
            var next;
            if (state.previewVideoEl && state.previewVideoClip) {
                state.previewVideoEl.muted = getVideoMuted(state.previewVideoClip);
                if (state.previewVideoEl.paused) state.previewVideoEl.play().catch(function() {});
                var clipStart = parseFloat(state.previewVideoClip.start_time) || 0;
                var clipDur = parseFloat(state.previewVideoClip.duration) || 1;
                var clipEnd = clipStart + clipDur;
                var vidTime = state.previewVideoEl.currentTime;
                if (typeof vidTime !== 'number' || isNaN(vidTime)) vidTime = 0;
                next = clipStart + vidTime;
                if (state.previewVideoEl.ended || next >= clipEnd) {
                    next = clipEnd;
                    state.playheadTime = next;
                    if (next >= getDuration()) {
                        state.isPlaying = false;
                        if (playBtn) playBtn.style.display = 'inline-flex';
                        if (pauseBtn) pauseBtn.style.display = 'none';
                        stopPreviewVideo();
                        updateTimeDisplay();
                        renderPlayhead();
                        updatePreview();
                        return;
                    }
                    stopPreviewVideo();
                    startPlayhead = next;
                    startTime = now;
                    updatePreview(true);
                } else {
                    state.playheadTime = next;
                }
                updateTimeDisplay();
                renderPlayhead();
                updatePreview(true);
            } else {
                var elapsed = (now - startTime) / 1000;
                next = startPlayhead + elapsed;
                state.playheadTime = next;
                updateTimeDisplay();
                renderPlayhead();
                updatePreview(true);
            }
            if (next >= getDuration()) {
                state.playheadTime = getDuration();
                state.isPlaying = false;
                if (playBtn) playBtn.style.display = 'inline-flex';
                if (pauseBtn) pauseBtn.style.display = 'none';
                updateTimeDisplay();
                renderPlayhead();
                updatePreview();
                stopPreviewVideo();
                return;
            }
            if (!(state.previewVideoEl && state.previewVideoClip)) {
                updatePreview(true);
            }
            state.playAnimationId = requestAnimationFrame(tick);
        }
        state.playAnimationId = requestAnimationFrame(tick);
    }

    function stopPlayback() {
        state.isPlaying = false;
        if (state.playAnimationId != null) {
            cancelAnimationFrame(state.playAnimationId);
            state.playAnimationId = null;
        }
        stopPreviewVideo();
        updateTimeDisplay();
        renderPlayhead();
        updatePreview();
        var playBtn = document.getElementById('vt-play');
        var pauseBtn = document.getElementById('vt-pause');
        if (playBtn) playBtn.style.display = 'inline-flex';
        if (pauseBtn) pauseBtn.style.display = 'none';
    }

    function bindPlayback() {
        var playBtn = document.getElementById('vt-play');
        var pauseBtn = document.getElementById('vt-pause');
        var stopBtn = document.getElementById('vt-stop');
        if (playBtn) playBtn.addEventListener('click', function() {
            if (state.playheadTime >= getDuration()) state.playheadTime = 0;
            startPlayback();
        });
        if (pauseBtn) pauseBtn.addEventListener('click', function() {
            state.isPlaying = false;
            if (state.playAnimationId != null) cancelAnimationFrame(state.playAnimationId);
            stopPreviewVideo();
            updatePreview();
            if (playBtn) playBtn.style.display = 'inline-flex';
            if (pauseBtn) pauseBtn.style.display = 'none';
        });
        if (stopBtn) stopBtn.addEventListener('click', function() {
            state.isPlaying = false;
            if (state.playAnimationId != null) cancelAnimationFrame(state.playAnimationId);
            stopPreviewVideo();
            state.playheadTime = 0;
            updateTimeDisplay();
            renderPlayhead();
            updatePreview();
            if (playBtn) playBtn.style.display = 'inline-flex';
            if (pauseBtn) pauseBtn.style.display = 'none';
        });
    }

    function setZoom(pxPerSec) {
        state.pxPerSec = Math.max(PX_PER_SEC_MIN, Math.min(PX_PER_SEC_MAX, pxPerSec));
        var label = document.getElementById('vt-zoom-label');
        if (label) label.textContent = Math.round((state.pxPerSec / PX_PER_SEC_DEFAULT) * 100) + '%';
        renderRuler();
        renderTracks();
        renderPlayhead();
    }

    function bindZoom() {
        var inBtn = document.getElementById('vt-zoom-in');
        var outBtn = document.getElementById('vt-zoom-out');
        if (inBtn) inBtn.addEventListener('click', function() { setZoom(state.pxPerSec + 20); });
        if (outBtn) outBtn.addEventListener('click', function() { setZoom(state.pxPerSec - 20); });
        var body = document.getElementById('vt-timeline-body');
        if (body) {
            body.addEventListener('wheel', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    setZoom(state.pxPerSec + (e.deltaY > 0 ? -15 : 15));
                }
            }, { passive: false });
        }
    }

    function bindSnapToggle() {
        var btn = document.getElementById('vt-snap-toggle');
        if (!btn) return;
        btn.addEventListener('click', function() {
            state.snapEnabled = !state.snapEnabled;
            btn.classList.toggle('vt-active', state.snapEnabled);
            btn.title = state.snapEnabled ? 'Snap açık' : 'Snap kapalı';
        });
        btn.classList.toggle('vt-active', state.snapEnabled);
    }

    function bindUndoRedo() {
        var undoBtn = document.getElementById('vt-undo');
        var redoBtn = document.getElementById('vt-redo');
        if (undoBtn) undoBtn.addEventListener('click', undo);
        if (redoBtn) redoBtn.addEventListener('click', redo);
        updateUndoRedoButtons();
    }

    function isTrackLocked(track) {
        if (!track) return false;
        return track.is_locked === 1 || track.is_locked === '1' || track.is_locked === true;
    }

    function isTrackMuted(track) {
        if (!track) return false;
        return track.is_muted === 1 || track.is_muted === '1' || track.is_muted === true;
    }

    function isClipTrackMuted(clip) {
        if (!clip) return false;
        var trackIndex = state.tracks.findIndex(function(t) {
            return (t.id != null && Number(t.id) === Number(clip.track_id)) || state.tracks.indexOf(t) === (typeof clip.track_id === 'number' ? clip.track_id : -1);
        });
        if (trackIndex < 0 && typeof clip.track_id === 'number' && clip.track_id < state.tracks.length) trackIndex = clip.track_id;
        if (trackIndex < 0) return false;
        return isTrackMuted(state.tracks[trackIndex]);
    }

    function getVideoMuted(clip) {
        if (!clip) return true;
        return isClipTrackMuted(clip) || !(clip.content && clip.content.audioMuted === false);
    }

    function init() {
        var d = window.VT_DATA;
        if (d && d.timeline) state.timeline = d.timeline;
        if (d && d.tracks) {
            state.tracks = d.tracks;
            state.tracks.forEach(function(t) {
                if (t.id != null) t.id = parseInt(t.id, 10);
                t.is_locked = isTrackLocked(t) ? 1 : 0;
            });
        }
        if (d && d.clipsByTrack) {
            state.clips = [];
            Object.keys(d.clipsByTrack).forEach(function(trackId) {
                (d.clipsByTrack[trackId] || []).forEach(function(c) {
                    c.track_id = parseInt(trackId, 10);
                    if (c.id != null) c.id = parseInt(c.id, 10);
                    state.clips.push(c);
                });
            });
        }
        state.timeline.id = state.timeline.id || timelineId;
        state.timeline.background_color = state.timeline.background_color || '#000000';
        bindToolbar();
        bindPreviewZoom();
        bindPlayback();
        bindZoom();
        bindSnapToggle();
        bindUndoRedo();
        bindRulerClick();
        bindPlayheadDrag();
        bindTrackHeight();
        bindBgColor();
        bindShortcutsModal();
        bindKeyboard();
        bindCopyPasteButtons();
        renderRuler();
        renderTracks();
        renderPlayhead();
        updateTimeDisplay();
        applySizeToPreview();
        updatePreview();
        bindDownloadProject();
        bindSave();
        bindTimelineClicks();
        bindAddTrack();
        bindTimelineTabs();
        bindTrackReorder();
        bindAddClipPanel();
        bindMediaModal();
        var addPanel = document.getElementById('vt-add-clip-panel');
        var ph = document.getElementById('vt-props-placeholder');
        if (ph) ph.style.display = 'none';
        if (addPanel) addPanel.style.display = 'block';
        setZoom(state.pxPerSec);
    }

    var SIZE_PRESETS = {
        '1920x1080': [1920, 1080],
        '1080x1920': [1080, 1920],
        '1280x720': [1280, 720],
        '720x1280': [720, 1280],
        '3840x2160': [3840, 2160]
    };

    function getPresetForSize(w, h) {
        w = parseInt(w, 10);
        h = parseInt(h, 10);
        for (var key in SIZE_PRESETS) {
            var p = SIZE_PRESETS[key];
            if (p[0] === w && p[1] === h) return key;
        }
        return 'custom';
    }

    function applySizeToPreview() {
        var widthEl = document.getElementById('vt-width');
        var heightEl = document.getElementById('vt-height');
        var preview = document.getElementById('vt-preview');
        var badge = document.getElementById('vt-preview-badge');
        var zoomWrap = document.getElementById('vt-preview-zoom-wrap');
        if (!preview) return;
        var w = (widthEl && widthEl.value) ? parseInt(widthEl.value, 10) : (parseInt(state.timeline.width, 10) || parseInt(preview.getAttribute('data-width'), 10) || 1920);
        var h = (heightEl && heightEl.value) ? parseInt(heightEl.value, 10) : (parseInt(state.timeline.height, 10) || parseInt(preview.getAttribute('data-height'), 10) || 1080);
        w = isNaN(w) || w < 1 ? 1920 : w;
        h = isNaN(h) || h < 1 ? 1080 : h;
        preview.setAttribute('data-width', w);
        preview.setAttribute('data-height', h);
        if (state.timeline) { state.timeline.width = w; state.timeline.height = h; }
        if (badge) badge.textContent = 'Canvas ' + w + ' × ' + h;

        var maxW = zoomWrap ? zoomWrap.clientWidth - 24 : 800;
        var maxH = (typeof window !== 'undefined' && window.innerHeight) ? Math.min(0.6 * window.innerHeight, 520) : 520;
        var aspect = w / h;
        var boxW = maxW;
        var boxH = boxW / aspect;
        if (boxH > maxH) {
            boxH = maxH;
            boxW = boxH * aspect;
        }
        boxW = Math.max(160, Math.round(boxW));
        boxH = Math.max(160, Math.round(boxH));
        preview.style.width = boxW + 'px';
        preview.style.height = boxH + 'px';
        preview.style.aspectRatio = w + ' / ' + h;
        state.previewBaseWidth = boxW;
        state.previewBaseHeight = boxH;

        updatePreview();
        scheduleApplyPreviewZoom();
    }

    function scheduleApplyPreviewZoom() {
        if (state.previewZoomApplyScheduled) return;
        state.previewZoomApplyScheduled = true;
        requestAnimationFrame(function() {
            state.previewZoomApplyScheduled = false;
            applyPreviewZoom();
        });
    }

    function applyPreviewZoom() {
        var preview = document.getElementById('vt-preview');
        if (!preview) return;
        var z = state.previewZoom;
        var baseW = state.previewBaseWidth || preview.offsetWidth || 400;
        var baseH = state.previewBaseHeight || preview.offsetHeight || 400;
        if (baseW && baseH) {
            state.previewBaseWidth = baseW;
            state.previewBaseHeight = baseH;
        }
        preview.style.width = Math.round(baseW * z) + 'px';
        preview.style.height = Math.round(baseH * z) + 'px';
        var label = document.getElementById('vt-preview-zoom-label');
        if (label) label.textContent = Math.round(z * 100) + '%';
    }

    function setPreviewZoom(delta) {
        var z = state.previewZoom + delta;
        z = Math.max(PREVIEW_ZOOM_MIN, Math.min(PREVIEW_ZOOM_MAX, z));
        z = Math.round(z / PREVIEW_ZOOM_STEP) * PREVIEW_ZOOM_STEP;
        state.previewZoom = z;
        scheduleApplyPreviewZoom();
    }

    function bindPreviewZoom() {
        var zoomOut = document.getElementById('vt-preview-zoom-out');
        var zoomIn = document.getElementById('vt-preview-zoom-in');
        if (zoomOut) zoomOut.addEventListener('click', function() { setPreviewZoom(-PREVIEW_ZOOM_STEP); });
        if (zoomIn) zoomIn.addEventListener('click', function() { setPreviewZoom(PREVIEW_ZOOM_STEP); });
        window.addEventListener('resize', function() {
            applySizeToPreview();
        });
    }

    function bindToolbar() {
        var nameEl = document.getElementById('vt-name');
        var widthEl = document.getElementById('vt-width');
        var heightEl = document.getElementById('vt-height');
        var durationEl = document.getElementById('vt-duration');
        var presetEl = document.getElementById('vt-size-preset');
        if (nameEl) nameEl.addEventListener('change', function() { state.timeline.name = this.value; });
        if (presetEl) {
            presetEl.addEventListener('change', function() {
                var val = this.value;
                if (val !== 'custom' && SIZE_PRESETS[val]) {
                    var p = SIZE_PRESETS[val];
                    state.timeline.width = p[0];
                    state.timeline.height = p[1];
                    if (widthEl) { widthEl.value = p[0]; }
                    if (heightEl) { heightEl.value = p[1]; }
                    applySizeToPreview();
                }
            });
        }
        if (widthEl) {
            widthEl.addEventListener('change', function() {
                var w = parseInt(this.value, 10) || 1920;
                state.timeline.width = w;
                if (presetEl) presetEl.value = getPresetForSize(w, heightEl ? heightEl.value : 1080);
                applySizeToPreview();
            });
        }
        if (heightEl) {
            heightEl.addEventListener('change', function() {
                var h = parseInt(this.value, 10) || 1080;
                state.timeline.height = h;
                if (presetEl) presetEl.value = getPresetForSize(widthEl ? widthEl.value : 1920, h);
                applySizeToPreview();
            });
        }
        if (durationEl) durationEl.addEventListener('change', function() {
            state.timeline.duration_sec = parseFloat(this.value) || 10;
            renderRuler();
            renderTracks();
            renderPlayhead();
            updatePreview();
        });
    }

    function bindBgColor() {
        var el = document.getElementById('vt-bg-color');
        if (!el) return;
        el.addEventListener('change', function() {
            state.timeline.background_color = this.value;
            updatePreview();
        });
    }

    function bindTrackHeight() {
        var el = document.getElementById('vt-track-height');
        if (!el) return;
        el.addEventListener('change', function() {
            state.trackHeight = this.value;
            app.classList.toggle('vt-track-compact', state.trackHeight === 'compact');
        });
        if (state.trackHeight === 'compact') app.classList.add('vt-track-compact');
    }

    function bindShortcutsModal() {
        var btn = document.getElementById('vt-shortcuts-btn');
        var modal = document.getElementById('vt-shortcuts-modal');
        if (!btn || !modal) return;
        btn.addEventListener('click', function() {
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        });
        modal.querySelector('.vt-modal-backdrop').addEventListener('click', function() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        });
        modal.querySelector('.vt-modal-close').addEventListener('click', function() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        });
    }

    function bindKeyboard() {
        document.addEventListener('keydown', function(e) {
            if (e.target.closest('input') || e.target.closest('textarea') || e.target.closest('select')) return;
            if (e.key === ' ') {
                e.preventDefault();
                if (state.isPlaying) stopPlayback(); else startPlayback();
                return;
            }
            if (e.key === '?' && !e.ctrlKey && !e.metaKey) {
                var modal = document.getElementById('vt-shortcuts-modal');
                if (modal) { modal.style.display = 'flex'; modal.setAttribute('aria-hidden', 'false'); }
                e.preventDefault();
                return;
            }
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'z') {
                    e.preventDefault();
                    if (e.shiftKey) redo(); else undo();
                    return;
                }
                if (e.key === 'y' && e.ctrlKey) {
                    e.preventDefault();
                    redo();
                    return;
                }
                if (e.key === 'd') {
                    e.preventDefault();
                    duplicateSelectedClips();
                    return;
                }
                if (e.key === 'c') {
                    e.preventDefault();
                    copySelectedClips();
                    return;
                }
                if (e.key === 'v') {
                    e.preventDefault();
                    pasteClips();
                    return;
                }
            }
            if (e.key === 'Delete' || e.key === 'Backspace') {
                e.preventDefault();
                deleteSelectedClips();
            }
        });
    }

    function bindCopyPasteButtons() {
        var copyBtn = document.getElementById('vt-copy-clips');
        var pasteBtn = document.getElementById('vt-paste-clips');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                var sel = getSelectedClips();
                if (sel.length === 0) {
                    showToast('Kopyalamak için önce bir öğe seçin.', 'info');
                    return;
                }
                copySelectedClips();
            });
        }
        if (pasteBtn) {
            pasteBtn.addEventListener('click', function() {
                pasteClips();
            });
        }
    }

    function isClipSelected(clip) {
        var id = clip.id != null ? String(clip.id) : (clip._cid || '');
        return state.selectedClipIds.some(function(sid) { return sid != null && String(sid) === id; });
    }

    function selectClip(clip, addToSelection) {
        if (addToSelection) {
            var id = clip.id || clip._cid;
            var idStr = id != null ? String(id) : '';
            var idx = state.selectedClipIds.findIndex(function(sid) { return sid != null && String(sid) === idStr; });
            if (idx === -1) state.selectedClipIds.push(id);
            else state.selectedClipIds.splice(idx, 1);
        } else {
            state.selectedClipIds = [clip.id || clip._cid];
        }
        var trackIndex = state.tracks.findIndex(function(t) { return (t.id != null && Number(t.id) === Number(clip.track_id)) || state.tracks.indexOf(t) === (typeof clip.track_id === 'number' ? clip.track_id : -1); });
        if (trackIndex < 0 && typeof clip.track_id === 'number' && clip.track_id < state.tracks.length) trackIndex = clip.track_id;
        if (trackIndex >= 0) state.selectedTrackIndex = trackIndex;
        renderTracks();
        if (state.selectedClipIds.length === 1) {
            var selId = state.selectedClipIds[0];
            var c = state.clips.find(function(x) { var xid = x.id != null ? String(x.id) : (x._cid || ''); return selId != null && String(selId) === xid; });
            if (c) showProps(c);
        } else {
            showPropsPlaceholder();
        }
        updatePreview();
    }

    function getSelectedClips() {
        return state.clips.filter(function(c) { return isClipSelected(c); });
    }

    function duplicateSelectedClips() {
        var sel = getSelectedClips();
        if (sel.length === 0) return;
        pushSnapshot();
        sel.forEach(function(clip) {
            var trackId = clip.track_id;
            var trackIndex = typeof trackId === 'number' && trackId < state.tracks.length ? trackId : state.tracks.findIndex(function(t) { return t.id === trackId; });
            if (trackIndex < 0) trackIndex = 0;
            var start = (parseFloat(clip.start_time) || 0) + (parseFloat(clip.duration) || 1);
            var newClip = {
                id: null,
                _cid: 'c' + Math.random().toString(36).slice(2),
                track_id: state.tracks[trackIndex].id != null ? state.tracks[trackIndex].id : trackIndex,
                type: clip.type || 'text',
                start_time: start,
                duration: parseFloat(clip.duration) || 1,
                sort_order: state.clips.length,
                source: clip.source || null,
                content: clip.content ? JSON.parse(JSON.stringify(clip.content)) : {}
            };
            state.clips.push(newClip);
        });
        renderTracks();
    }

    function copySelectedClips() {
        var sel = getSelectedClips();
        state.clipboard = sel.map(function(c) {
            return { type: c.type, duration: parseFloat(c.duration) || 1, source: c.source, content: c.content ? JSON.parse(JSON.stringify(c.content)) : {} };
        });
        if (sel.length > 0) showToast(sel.length === 1 ? 'Öğe kopyalandı.' : sel.length + ' öğe kopyalandı.', 'success');
    }

    function pasteClips() {
        if (state.clipboard.length === 0) {
            showToast('Kopyalanmış öğe yok. Önce bir öğe seçip kopyalayın.', 'info');
            return;
        }
        pushSnapshot();
        var trackIndex = 0;
        if (state.selectedClipIds.length === 1) {
            var c = state.clips.find(function(x) { var xid = x.id != null ? String(x.id) : (x._cid || ''); return state.selectedClipIds[0] != null && String(state.selectedClipIds[0]) === xid; });
            if (c) trackIndex = typeof c.track_id === 'number' && c.track_id < state.tracks.length ? c.track_id : state.tracks.findIndex(function(t) { return t.id != null && Number(t.id) === Number(c.track_id); });
            if (trackIndex < 0) trackIndex = 0;
        }
        var start = state.playheadTime;
        var pasted = [];
        state.clipboard.forEach(function(data) {
            var clip = {
                id: null,
                _cid: 'c' + Math.random().toString(36).slice(2),
                track_id: state.tracks[trackIndex].id != null ? state.tracks[trackIndex].id : trackIndex,
                type: data.type || 'text',
                start_time: start,
                duration: data.duration || 1,
                sort_order: state.clips.length,
                source: data.source || null,
                content: data.content ? JSON.parse(JSON.stringify(data.content)) : {}
            };
            state.clips.push(clip);
            pasted.push(clip);
            start += data.duration || 1;
        });
        state.selectedClipIds = pasted.map(function(c) { return c._cid; });
        if (pasted.length === 1) {
            state.selectedTrackIndex = typeof pasted[0].track_id === 'number' && pasted[0].track_id < state.tracks.length ? pasted[0].track_id : state.tracks.findIndex(function(t) { return t.id != null && Number(t.id) === Number(pasted[0].track_id); });
            if (state.selectedTrackIndex < 0) state.selectedTrackIndex = 0;
            showProps(pasted[0]);
        } else {
            showPropsPlaceholder();
        }
        renderTracks();
        updatePreview();
        showToast(pasted.length === 1 ? 'Yapıştırıldı.' : pasted.length + ' öğe yapıştırıldı.', 'success');
    }

    function deleteSelectedClips() {
        var sel = getSelectedClips();
        if (sel.length === 0) return;
        pushSnapshot();
        state.clips = state.clips.filter(function(x) { return sel.indexOf(x) === -1; });
        state.selectedClipIds = [];
        renderTracks();
        showPropsPlaceholder();
        updatePreview();
    }

    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('vt-toast-container');
        if (!container) return;
        var el = document.createElement('div');
        el.className = 'vt-toast vt-toast-' + type;
        el.textContent = message;
        container.appendChild(el);
        setTimeout(function() {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 3000);
    }

    function renderRuler() {
        var ruler = document.getElementById('vt-ruler');
        if (!ruler) return;
        var duration = getDuration();
        ruler.innerHTML = '';
        var playheadLine = document.createElement('div');
        playheadLine.id = 'vt-playhead-line';
        playheadLine.className = 'vt-playhead-line';
        ruler.appendChild(playheadLine);
        ruler.style.width = getTimelineWidth() + 'px';
        var step = duration <= 5 ? 0.5 : duration <= 30 ? 1 : 5;
        for (var t = 0; t <= duration; t += step) {
            var tick = document.createElement('div');
            tick.className = 'vt-ruler-tick';
            tick.style.left = (t * state.pxPerSec) + 'px';
            var span = document.createElement('span');
            span.textContent = t;
            tick.appendChild(span);
            ruler.appendChild(tick);
        }
        renderPlayhead();
    }

    function buildFilmstrip(clip, sourceUrl, onComplete) {
        if (clip._filmstripBuilding) return;
        clip._filmstripBuilding = true;
        var video = document.createElement('video');
        video.muted = true;
        video.preload = 'auto';
        video.playsInline = true;
        video.style.cssText = 'position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;';
        document.body.appendChild(video);
        function done() {
            clip._filmstripBuilding = false;
            try { if (video.parentNode) video.parentNode.removeChild(video); } catch (e) {}
            video.src = '';
            if (onComplete) onComplete();
        }
        video.addEventListener('error', function() { done(); });
        video.addEventListener('loadedmetadata', function() {
            var duration = video.duration;
            if (!duration || duration <= 0) { done(); return; }
            var clipW = (parseFloat(clip.duration) || 1) * state.pxPerSec;
            var numFrames = Math.min(12, Math.max(4, Math.floor(clipW / 40)));
            var urls = [];
            var currentIndex = 0;
            var times = [];
            for (var j = 0; j < numFrames; j++) times.push((j / Math.max(1, numFrames - 1)) * duration);
            var canvas = document.createElement('canvas');
            canvas.width = 80;
            canvas.height = 45;
            var ctx = canvas.getContext('2d');
            function doCapture() {
                if (currentIndex >= numFrames) {
                    clip._filmstripFrames = urls;
                    clip._filmstripSourceUrl = sourceUrl;
                    done();
                    return;
                }
                video.currentTime = times[currentIndex];
            }
            video.addEventListener('seeked', function onSeeked() {
                try {
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    urls.push(canvas.toDataURL('image/jpeg', 0.75));
                } catch (err) {}
                currentIndex++;
                doCapture();
            });
            doCapture();
        });
        video.src = sourceUrl;
    }

    function renderTracks() {
        var container = document.getElementById('vt-tracks');
        if (!container) return;
        var duration = getDuration();
        var w = getTimelineWidth();
        container.innerHTML = '';
        container.style.minWidth = w + 'px';

        state.tracks.forEach(function(track, trackIndex) {
            var clipsInTrackCheck = state.clips.filter(function(c) {
                var tid = track.id != null ? Number(track.id) : null;
                var onTrack = (tid != null && Number(c.track_id) === tid) || (tid == null && (c.track_id === trackIndex || c.track_id == track.id));
                if (!onTrack) return false;
                if (state.contentFilter !== 'all' && c.type !== state.contentFilter) return false;
                return true;
            });
            if (clipsInTrackCheck.length === 0) return;

            var row = document.createElement('div');
            row.className = 'vt-track-row' + (state.selectedTrackIndex === trackIndex ? ' vt-track-selected' : '');
            row.setAttribute('data-track-index', trackIndex);
            var label = document.createElement('div');
            label.className = 'vt-track-label';
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.gap = '8px';
            var dragHandle = document.createElement('span');
            dragHandle.className = 'vt-track-drag material-symbols-outlined';
            dragHandle.textContent = 'drag_indicator';
            dragHandle.title = 'Sıralamak için sürükle';
            dragHandle.setAttribute('data-track-index', trackIndex);
            label.appendChild(dragHandle);
            var labelText = document.createElement('span');
            labelText.className = 'vt-track-name';
            labelText.textContent = track.name || ('Layer ' + (trackIndex + 1));
            label.appendChild(labelText);
            var lockBtn = document.createElement('button');
            lockBtn.type = 'button';
            lockBtn.className = 'vt-track-btn vt-track-btn-lock ' + (isTrackLocked(track) ? 'vt-active' : '');
            lockBtn.title = isTrackLocked(track) ? 'Kilidi aç' : 'Kilitle';
            lockBtn.setAttribute('data-action', 'lock');
            lockBtn.setAttribute('data-track-index', String(trackIndex));
            lockBtn.innerHTML = '<span class="material-symbols-outlined">' + (isTrackLocked(track) ? 'lock' : 'lock_open') + '</span>';
            label.appendChild(lockBtn);
            var hasVideoInTrack = clipsInTrackCheck.some(function(c) { return c.type === 'video'; });
            if (hasVideoInTrack) {
                var isMuted = track.is_muted === 1 || track.is_muted === '1' || track.is_muted === true;
                var muteBtn = document.createElement('button');
                muteBtn.type = 'button';
                muteBtn.className = 'vt-track-btn vt-track-btn-mute ' + (isMuted ? 'vt-active' : '');
                muteBtn.title = isMuted ? 'Sesi aç' : 'Sessiz';
                muteBtn.setAttribute('data-action', 'mute');
                muteBtn.setAttribute('data-track-index', String(trackIndex));
                muteBtn.innerHTML = '<span class="material-symbols-outlined">' + (isMuted ? 'volume_off' : 'volume_up') + '</span>';
                label.appendChild(muteBtn);
            }
            label.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    state.selectedTrackIndex = trackIndex;
                    renderTracks();
                }
            });
            label.style.cursor = 'pointer';
            label.title = 'Bu layer\'a ekleme yapmak için seçin';
            row.appendChild(label);

            var lane = document.createElement('div');
            lane.className = 'vt-track-lane';
            lane.addEventListener('click', function(e) {
                if (!e.target.closest('.vt-clip')) {
                    state.selectedTrackIndex = trackIndex;
                    renderTracks();
                }
            });
            lane.style.cursor = 'pointer';
            lane.style.width = w + 'px';
            var isLocked = isTrackLocked(track);

            var clipsInTrack = state.clips.filter(function(c) {
                var tid = track.id != null ? Number(track.id) : null;
                var onTrack = (tid != null && Number(c.track_id) === tid) || (tid == null && (c.track_id === trackIndex || c.track_id == track.id));
                if (!onTrack) return false;
                if (state.contentFilter !== 'all' && c.type !== state.contentFilter) return false;
                return true;
            });

            clipsInTrack.forEach(function(clip) {
                var startT = parseFloat(clip.start_time) || 0;
                var dur = parseFloat(clip.duration) || 1;
                var left = startT * state.pxPerSec;
                var widthByDuration = dur * state.pxPerSec;
                var width = Math.max(12, Math.min(widthByDuration, w - left));
                var div = document.createElement('div');
                var type = clip.type || 'text';
                div.className = 'vt-clip vt-type-' + type;
                var sourceUrl = clip.source || (clip.content && clip.content.source_url) || '';
                if ((type === 'video' || type === 'image') && !sourceUrl) div.classList.add('vt-clip-empty');
                div.setAttribute('data-clip-id', clip.id || clip._cid || '');
                div.setAttribute('data-clip-cid', clip._cid || ('c' + Math.random().toString(36).slice(2)));
                if (!clip._cid) clip._cid = div.getAttribute('data-clip-cid');
                div.style.left = left + 'px';
                div.style.width = width + 'px';

                var preview = document.createElement('div');
                preview.className = 'vt-clip-preview';
                if (type === 'video' && sourceUrl) {
                    preview.classList.add('vt-clip-filmstrip');
                    if (clip._filmstripSourceUrl !== sourceUrl) {
                        clip._filmstripFrames = null;
                        clip._filmstripSourceUrl = sourceUrl;
                    }
                    var frames = clip._filmstripFrames;
                    if (frames && frames.length > 0) {
                        frames.forEach(function(dataUrl) {
                            var fr = document.createElement('div');
                            fr.className = 'vt-clip-filmframe';
                            fr.style.backgroundImage = 'url(' + dataUrl + ')';
                            preview.appendChild(fr);
                        });
                    } else {
                        var place = document.createElement('div');
                        place.className = 'vt-clip-filmstrip-placeholder';
                        place.textContent = '…';
                        preview.appendChild(place);
                        buildFilmstrip(clip, sourceUrl, function() { renderTracks(); });
                    }
                } else if (type === 'image' && sourceUrl) {
                    var img = document.createElement('img');
                    img.src = sourceUrl;
                    img.alt = '';
                    img.className = 'vt-clip-thumb-img';
                    preview.appendChild(img);
                } else if (type === 'video' || type === 'image') {
                    var emptyIcon = document.createElement('span');
                    emptyIcon.className = 'material-symbols-outlined vt-clip-empty-icon';
                    emptyIcon.textContent = type === 'video' ? 'movie' : 'image';
                    preview.appendChild(emptyIcon);
                    var emptyText = document.createElement('span');
                    emptyText.className = 'vt-clip-empty-text';
                    emptyText.textContent = type === 'video' ? 'Video ekle' : 'Görsel ekle';
                    preview.appendChild(emptyText);
                } else if (type === 'text') {
                    var c = clip.content || {};
                    var txt = (c.text || '').trim() || 'Metin';
                    var snip = txt.length > 12 ? txt.slice(0, 11) + '…' : txt;
                    var textPrev = document.createElement('span');
                    textPrev.className = 'vt-clip-text-preview';
                    textPrev.textContent = snip;
                    preview.appendChild(textPrev);
                } else if (type === 'shape') {
                    var c = clip.content || {};
                    var shapeBox = document.createElement('div');
                    shapeBox.className = 'vt-clip-shape-preview';
                    shapeBox.style.background = c.fill || '#8b5cf6';
                    shapeBox.style.borderRadius = (c.shape === 'ellipse' ? '50%' : '2px');
                    preview.appendChild(shapeBox);
                }
                div.appendChild(preview);

                var labelSpan = document.createElement('span');
                labelSpan.className = 'vt-clip-label';
                labelSpan.textContent = startT.toFixed(1) + ' – ' + (startT + dur).toFixed(1) + 's';
                div.appendChild(labelSpan);

                if (isClipSelected(clip)) div.classList.add('selected');
                div.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var el = e.currentTarget;
                    if (e.target.closest('.vt-clip-resize') || e.target.closest('.vt-clip-resize-left')) return;
                    var targetClip = findClipByEl(el);
                    if (targetClip) selectClip(targetClip, e.shiftKey);
                });
                if (!isLocked) {
                    var resizeLeft = document.createElement('div');
                    resizeLeft.className = 'vt-clip-resize-left';
                    resizeLeft.title = 'Başlangıç ve süreyi ayarla (sürükle)';
                    resizeLeft.addEventListener('mousedown', function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        resizeClipLeft(clip, ev);
                    });
                    div.appendChild(resizeLeft);
                    var resize = document.createElement('div');
                    resize.className = 'vt-clip-resize';
                    resize.title = 'Süreyi ayarla (sürükle)';
                    resize.addEventListener('mousedown', function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        resizeClip(clip, ev);
                    });
                    div.appendChild(resize);
                    dragClip(div, clip);
                }
                lane.appendChild(div);
            });

            row.appendChild(lane);
            container.appendChild(row);
        });

        var playheadTrack = document.getElementById('vt-playhead-track');
        if (playheadTrack) {
            playheadTrack.style.left = (TRACK_LABEL_WIDTH + state.playheadTime * state.pxPerSec) + 'px';
        }
    }

    function resizeClipLeft(clip, e) {
        pushSnapshot();
        var startX = e.clientX;
        var startStart = parseFloat(clip.start_time) || 0;
        var startDur = parseFloat(clip.duration) || 1;
        function onMove(ev) {
            var dx = (ev.clientX - startX) / state.pxPerSec;
            var newStart = startStart + dx;
            var newDur = startDur - dx;
            if (newDur < 0.1) {
                newDur = 0.1;
                newStart = startStart + startDur - 0.1;
            }
            if (newStart < 0) {
                newStart = 0;
                newDur = startStart + startDur;
            }
            clip.start_time = state.snapEnabled ? snapToGrid(newStart) : newStart;
            clip.duration = state.snapEnabled ? snapToGrid(newDur) : newDur;
            var div = app.querySelector('[data-clip-cid="' + (clip._cid || '') + '"]');
            if (div) {
                div.style.left = (clip.start_time * state.pxPerSec) + 'px';
                div.style.width = (clip.duration * state.pxPerSec) + 'px';
                var lbl = div.querySelector('.vt-clip-label');
                if (lbl) lbl.textContent = clip.start_time.toFixed(1) + ' – ' + (clip.start_time + clip.duration).toFixed(1) + 's';
            }
        }
        function onUp() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            updatePreview();
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }

    function showProps(clip) {
        if (!clip) return;
        var placeholder = document.getElementById('vt-props-placeholder');
        var addPanel = document.getElementById('vt-add-clip-panel');
        var form = document.getElementById('vt-props-form');
        if (!form) return;
        if (placeholder) placeholder.style.display = 'none';
        if (addPanel) addPanel.style.display = 'block';
        form.style.display = 'block';
        form.innerHTML = '';

        if (!clip.content) clip.content = {};
        var c = clip.content;

        function addCropFields(body, content, clipRef) {
            addField(body, 'Sol (%)', 'vt-crop-left', Math.round((content.cropLeft != null ? content.cropLeft : 0) * 100), false, function(v) {
                content.cropLeft = Math.max(0, Math.min(1, (parseFloat(v) || 0) / 100));
                updatePreview();
            });
            addField(body, 'Üst (%)', 'vt-crop-top', Math.round((content.cropTop != null ? content.cropTop : 0) * 100), false, function(v) {
                content.cropTop = Math.max(0, Math.min(1, (parseFloat(v) || 0) / 100));
                updatePreview();
            });
            addField(body, 'Genişlik (%)', 'vt-crop-width', Math.round((content.cropWidth != null ? content.cropWidth : 1) * 100), false, function(v) {
                content.cropWidth = Math.max(0.01, Math.min(1, (parseFloat(v) || 100) / 100));
                updatePreview();
            });
            addField(body, 'Yükseklik (%)', 'vt-crop-height', Math.round((content.cropHeight != null ? content.cropHeight : 1) * 100), false, function(v) {
                content.cropHeight = Math.max(0.01, Math.min(1, (parseFloat(v) || 100) / 100));
                updatePreview();
            });
            var resetCrop = document.createElement('button');
            resetCrop.type = 'button';
            resetCrop.className = 'vt-btn vt-btn-ghost vt-btn-sm';
            resetCrop.textContent = 'Crop sıfırla';
            resetCrop.addEventListener('click', function() {
                content.cropLeft = 0;
                content.cropTop = 0;
                content.cropWidth = 1;
                content.cropHeight = 1;
                showProps(clipRef);
                updatePreview();
            });
            body.appendChild(resetCrop);
        }

        function accordion(title, contentFn) {
            var wrap = document.createElement('div');
            wrap.className = 'vt-accordion';
            var head = document.createElement('button');
            head.type = 'button';
            head.className = 'vt-accordion-head';
            head.textContent = title;
            var body = document.createElement('div');
            body.className = 'vt-accordion-body';
            contentFn(body);
            head.addEventListener('click', function() { body.classList.toggle('vt-open'); });
            wrap.appendChild(head);
            wrap.appendChild(body);
            form.appendChild(wrap);
        }

        if (clip.type === 'text') {
            accordion('İçerik', function(body) {
                var textLabel = document.createElement('label');
                textLabel.textContent = 'Metin';
                textLabel.className = 'vt-prop-label';
                body.appendChild(textLabel);
                var textarea = document.createElement('textarea');
                textarea.id = 'vt-prop-text';
                textarea.rows = 3;
                textarea.placeholder = 'Görüntülenecek metni yazın…';
                textarea.value = c.text || '';
                textarea.className = 'vt-props-form textarea';
                function applyText() {
                    c.text = textarea.value;
                    updatePreview();
                }
                textarea.addEventListener('input', applyText);
                textarea.addEventListener('change', applyText);
                body.appendChild(textarea);
            });
            accordion('Yazı tipi', function(body) {
                var fontLabel = document.createElement('label');
                fontLabel.textContent = 'Yazı tipi';
                fontLabel.className = 'vt-prop-label';
                body.appendChild(fontLabel);
                var fontSelect = document.createElement('select');
                fontSelect.id = 'vt-prop-font';
                fontSelect.className = 'vt-prop-select';
                [
                    { value: 'system-ui', label: 'Sistem varsayılanı' },
                    { value: 'Inter', label: 'Inter' },
                    { value: 'Arial', label: 'Arial' },
                    { value: 'Georgia', label: 'Georgia' },
                    { value: 'Times New Roman', label: 'Times New Roman' },
                    { value: 'Courier New', label: 'Courier New' },
                    { value: 'Verdana', label: 'Verdana' },
                    { value: 'Segoe UI', label: 'Segoe UI' },
                    { value: 'Open Sans', label: 'Open Sans' },
                    { value: 'Roboto', label: 'Roboto' },
                    { value: 'Lato', label: 'Lato' },
                    { value: 'Montserrat', label: 'Montserrat' }
                ].forEach(function(f) {
                    var o = document.createElement('option');
                    o.value = f.value;
                    o.textContent = f.label;
                    if ((c.font_family || 'system-ui') === f.value) o.selected = true;
                    fontSelect.appendChild(o);
                });
                body.appendChild(fontSelect);
                fontSelect.addEventListener('change', function() { c.font_family = this.value; updatePreview(); });
                addFieldWithSlider(body, 'Boyut (px)', 'vt-prop-font-size', c.font_size || 48, 8, 200, 1, function(v) {
                    c.font_size = parseInt(v, 10) || 48;
                    updatePreview();
                });
                var weightLabel = document.createElement('label');
                weightLabel.textContent = 'Kalınlık';
                weightLabel.className = 'vt-prop-label';
                body.appendChild(weightLabel);
                var weightSelect = document.createElement('select');
                weightSelect.className = 'vt-prop-select';
                weightSelect.innerHTML = '<option value="normal">Normal</option><option value="bold">Kalın</option>';
                weightSelect.value = c.font_weight || 'normal';
                weightSelect.addEventListener('change', function() { c.font_weight = this.value; updatePreview(); });
                body.appendChild(weightSelect);
                addField(body, 'Renk', 'vt-prop-color', c.color || '#ffffff', false, function(v) { c.color = v; updatePreview(); });
                var alignLabel = document.createElement('label');
                alignLabel.textContent = 'Hizalama';
                alignLabel.className = 'vt-prop-label';
                body.appendChild(alignLabel);
                var alignSelect = document.createElement('select');
                alignSelect.className = 'vt-prop-select';
                alignSelect.innerHTML = '<option value="left">Sol</option><option value="center">Orta</option><option value="right">Sağ</option>';
                alignSelect.value = c.text_align || 'left';
                alignSelect.addEventListener('change', function() { c.text_align = this.value; updatePreview(); });
                body.appendChild(alignSelect);
            });
            accordion('Konum', function(body) {
                addField(body, 'X (px)', 'vt-prop-x', c.x != null ? c.x : 100, false, function(v) { c.x = parseFloat(v) || 0; updatePreview(); });
                addField(body, 'Y (px)', 'vt-prop-y', c.y != null ? c.y : 200, false, function(v) { c.y = parseFloat(v) || 0; updatePreview(); });
            });
        }
        if (clip.type === 'shape') {
            accordion('Görünüm', function(body) {
                var shapeSelect = document.createElement('select');
                shapeSelect.innerHTML = '<option value="rect">rect</option><option value="ellipse">ellipse</option><option value="line">line</option>';
                if (c.shape) shapeSelect.value = c.shape;
                body.appendChild(document.createElement('label')).textContent = 'Şekil';
                body.appendChild(shapeSelect);
                shapeSelect.addEventListener('change', function() { c.shape = this.value; });
                addField(body, 'Fill', 'vt-prop-fill', c.fill || '#ff0000', false, function(v) { c.fill = v; });
                addField(body, 'Opacity', 'vt-prop-opacity', c.opacity != null ? c.opacity : 0.8, false, function(v) { c.opacity = parseFloat(v) || 0.8; });
                addField(body, 'X', 'vt-prop-x', c.x, false, function(v) { c.x = parseFloat(v) || 0; });
                addField(body, 'Y', 'vt-prop-y', c.y, false, function(v) { c.y = parseFloat(v) || 0; });
                addField(body, 'Genişlik', 'vt-prop-width', c.width, false, function(v) { c.width = parseFloat(v) || 100; });
                addField(body, 'Yükseklik', 'vt-prop-height', c.height, false, function(v) { c.height = parseFloat(v) || 100; });
            });
        }
        if (clip.type === 'video' || clip.type === 'image') {
            accordion('Medya', function(body) {
                var mediaLabel = document.createElement('label');
                mediaLabel.textContent = clip.type === 'video' ? 'Video kaynağı' : 'Görsel kaynağı';
                body.appendChild(mediaLabel);
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
                body.appendChild(mediaWrap);
                if (clip.type === 'video') {
                    var audioRow = document.createElement('div');
                    audioRow.className = 'vt-video-audio-row';
                    audioRow.style.display = 'none';
                    audioRow.style.alignItems = 'center';
                    audioRow.style.gap = '8px';
                    audioRow.style.marginBottom = '8px';
                    var sourceUrl = clip.source || c.source_url || '';
                    if (sourceUrl) {
                        var probe = document.createElement('video');
                        probe.muted = true;
                        probe.preload = 'metadata';
                        probe.style.display = 'none';
                        probe.addEventListener('loadedmetadata', function() {
                            var hasAudio = (probe.audioTracks && probe.audioTracks.length > 0) || (typeof probe.mozHasAudio !== 'undefined' && probe.mozHasAudio);
                            if (hasAudio) {
                                audioRow.style.display = 'flex';
                                var audioId = 'vt-video-audio-' + (clip._cid || clip.id || 'x') + '-' + Date.now();
                                var audioCheck = document.createElement('input');
                                audioCheck.type = 'checkbox';
                                audioCheck.id = audioId;
                                audioCheck.checked = c.audioMuted === false;
                                audioCheck.addEventListener('change', function() {
                                    c.audioMuted = !this.checked;
                                    updatePreview();
                                });
                                var audioLabel = document.createElement('label');
                                audioLabel.htmlFor = audioId;
                                audioLabel.textContent = 'Videonun sesi açık';
                                audioLabel.style.cursor = 'pointer';
                                audioRow.appendChild(audioCheck);
                                audioRow.appendChild(audioLabel);
                            }
                            probe.remove();
                        });
                        probe.addEventListener('error', function() { probe.remove(); });
                        probe.src = sourceUrl;
                    }
                    body.appendChild(audioRow);
                }
            });
            if (clip.type === 'video') {
                accordion('Çerçeve / Crop', function(body) {
                    var cropDesc = document.createElement('p');
                    cropDesc.className = 'vt-sidebar-desc';
                    cropDesc.style.marginBottom = '12px';
                    cropDesc.textContent = 'Video, canvas içinde sınırlıdır. Crop ile videonun hangi bölümünün görüneceğini belirleyin (0–100%).';
                    body.appendChild(cropDesc);
                    addCropFields(body, c, clip);
                });
            }
            if (clip.type === 'image') {
                accordion('Çerçeve / Crop', function(body) {
                    var cropDesc = document.createElement('p');
                    cropDesc.className = 'vt-sidebar-desc';
                    cropDesc.style.marginBottom = '12px';
                    cropDesc.textContent = 'Görsel, canvas içinde sınırlıdır. Crop ile görselin hangi bölümünün görüneceğini belirleyin (0–100%).';
                    body.appendChild(cropDesc);
                    addCropFields(body, c, clip);
                });
            }
        }

        accordion('Zamanlama', function(body) {
            addField(body, 'Tip', null, null, true);
            var typeSelect = document.createElement('select');
            typeSelect.id = 'vt-prop-type';
            ['text', 'shape', 'image', 'video', 'audio'].forEach(function(t) {
                var o = document.createElement('option');
                o.value = t;
                o.textContent = t;
                if (t === (clip.type || 'text')) o.selected = true;
                typeSelect.appendChild(o);
            });
            body.appendChild(typeSelect);
            typeSelect.addEventListener('change', function() { clip.type = this.value; renderTracks(); showProps(clip); updatePreview(); });
            var dur = getDuration();
            var startVal = parseFloat(clip.start_time) || 0;
            var durVal = parseFloat(clip.duration) || 1;
            var maxStart = Math.max(0, dur - durVal);
            var maxDur = Math.max(0.1, dur - startVal);
            addFieldWithSlider(body, 'Başlangıç (sn)', 'vt-prop-start', startVal, 0, Math.max(0.1, maxStart), 0.1, function(v) {
                clip.start_time = parseFloat(v) || 0;
                renderTracks();
                renderPlayhead();
                updatePreview();
            });
            addFieldWithSlider(body, 'Süre (sn)', 'vt-prop-duration', durVal, 0.1, Math.max(0.1, maxDur), 0.1, function(v) {
                clip.duration = parseFloat(v) || 1;
                renderTracks();
                updatePreview();
            });
        });

        addField(form, 'Playhead (sn)', 'vt-playhead', state.playheadTime, false, function(v) {
            state.playheadTime = parseFloat(v) || 0;
            updateTimeDisplay();
            renderPlayhead();
            updatePreview();
        });

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'vt-btn vt-btn-sm';
        delBtn.style.marginTop = '12px';
        delBtn.style.background = '#dc2626';
        delBtn.style.color = '#fff';
        delBtn.textContent = 'Clip sil';
        delBtn.addEventListener('click', function() {
            pushSnapshot();
            state.clips = state.clips.filter(function(x) { return (x.id !== clip.id && x._cid !== clip._cid); });
            var clipRef = clip.id != null ? String(clip.id) : (clip._cid || '');
            state.selectedClipIds = state.selectedClipIds.filter(function(id) { return id == null || String(id) !== clipRef; });
            showPropsPlaceholder();
            form.style.display = 'none';
            renderTracks();
            updatePreview();
        });
        form.appendChild(delBtn);
    }

    var currentMediaClip = null;

    function setVideoClipDurationFromUrl(clip, url, done) {
        var vid = document.createElement('video');
        vid.preload = 'metadata';
        vid.muted = true;
        vid.playsInline = true;
        function applyDuration() {
            var d = vid.duration;
            if (typeof d === 'number' && !isNaN(d) && d > 0) {
                clip.duration = Math.max(0.1, state.snapEnabled ? snapToGrid(d) : d);
                var clipEnd = (parseFloat(clip.start_time) || 0) + clip.duration;
                var timelineDur = getDuration();
                if (clipEnd > timelineDur) {
                    var durationEl = document.getElementById('vt-duration');
                    if (durationEl) {
                        durationEl.value = Math.ceil(clipEnd * 10) / 10;
                        state.timeline.duration_sec = parseFloat(durationEl.value);
                    }
                    renderRuler();
                }
            }
            vid.src = '';
            vid.load();
            if (done) done();
        }
        vid.addEventListener('loadedmetadata', function() { applyDuration(); });
        vid.addEventListener('error', function() { if (done) done(); });
        vid.src = url;
    }

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
                    var thumbUrl = item.thumbnail_url || null;
                    var isImage = (item.file_type === 'image') || (item.mime_type && item.mime_type.indexOf('image') === 0);
                    var isVideo = item.file_type === 'video';
                    var previewUrl = thumbUrl || url;
                    if (isVideo && previewUrl) {
                        var vid = document.createElement('video');
                        vid.src = previewUrl;
                        vid.muted = true;
                        vid.preload = 'metadata';
                        vid.playsInline = true;
                        vid.setAttribute('playsinline', '');
                        vid.className = 'vt-media-video-preview';
                        vid.style.objectFit = 'cover';
                        vid.style.width = '100%';
                        vid.style.height = '100%';
                        vid.onerror = function() {
                            vid.style.display = 'none';
                            var pl = document.createElement('div');
                            pl.className = 'vt-media-placeholder';
                            pl.innerHTML = '<span class="material-symbols-outlined">movie</span>';
                            div.appendChild(pl);
                        };
                        div.appendChild(vid);
                    } else if (thumbUrl) {
                        var img = document.createElement('img');
                        img.src = thumbUrl;
                        img.alt = item.original_name || '';
                        img.loading = 'lazy';
                        img.onerror = function() {
                            if (isImage && url) {
                                img.src = url;
                            } else {
                                this.style.display = 'none';
                                var pl = document.createElement('div');
                                pl.className = 'vt-media-placeholder';
                                pl.innerHTML = '<span class="material-symbols-outlined">' + (filterType === 'video' ? 'movie' : 'image') + '</span>';
                                div.appendChild(pl);
                            }
                        };
                        div.appendChild(img);
                    } else if (isImage && url) {
                        var img = document.createElement('img');
                        img.src = url;
                        img.alt = item.original_name || '';
                        img.loading = 'lazy';
                        img.onerror = function() {
                            this.style.display = 'none';
                            var pl = document.createElement('div');
                            pl.className = 'vt-media-placeholder';
                            pl.innerHTML = '<span class="material-symbols-outlined">image</span>';
                            div.appendChild(pl);
                        };
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
                        if (clipToUpdate.type === 'video' && url) {
                            setVideoClipDurationFromUrl(clipToUpdate, url, function() {
                                closeMediaModal();
                                showProps(clipToUpdate);
                                renderTracks();
                                updatePreview();
                            });
                        } else {
                            closeMediaModal();
                            showProps(clipToUpdate);
                            renderTracks();
                            updatePreview();
                        }
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
                if (state.tracks.length === 0) {
                    pushSnapshot();
                    state.tracks.push({ id: null, name: 'Layer 1', sort_order: 0, is_locked: 0, is_muted: 0 });
                    state.selectedTrackIndex = 0;
                    renderRuler();
                    renderTracks();
                }
                var trackIndex;
                if (type === 'text' || type === 'shape') {
                    pushSnapshot();
                    var newTrack = { id: null, name: type === 'text' ? 'Metin' : 'Şekil', sort_order: state.tracks.length, is_locked: 0, is_muted: 0 };
                    state.tracks.push(newTrack);
                    trackIndex = state.tracks.length - 1;
                    state.selectedTrackIndex = trackIndex;
                    renderRuler();
                    renderTracks();
                } else {
                    trackIndex = Math.max(0, Math.min(state.selectedTrackIndex, state.tracks.length - 1));
                    state.selectedTrackIndex = trackIndex;
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
        if (labelText === 'Renk') input.type = 'color';
        if (id) input.id = id;
        input.value = value != null ? value : '';
        if (readonly) input.readOnly = true;
        if (onChange) input.addEventListener('change', function() { onChange(this.value); });
        form.appendChild(input);
    }

    function addFieldWithSlider(form, labelText, id, value, minVal, maxVal, step, onChange) {
        var label = document.createElement('label');
        label.textContent = labelText;
        form.appendChild(label);
        var wrap = document.createElement('div');
        wrap.className = 'vt-field-with-slider';
        var input = document.createElement('input');
        input.type = 'number';
        if (id) input.id = id;
        input.min = minVal;
        input.max = maxVal;
        input.step = step || 0.1;
        input.value = value != null ? value : minVal;
        var slider = document.createElement('input');
        slider.type = 'range';
        slider.min = minVal;
        slider.max = maxVal;
        slider.step = step || 0.1;
        slider.value = value != null ? value : minVal;
        slider.className = 'vt-duration-slider';
        function apply(val) {
            var n = parseFloat(val);
            if (isNaN(n)) return;
            n = Math.max(minVal, Math.min(maxVal, n));
            input.value = n;
            slider.value = n;
            if (onChange) onChange(String(n));
        }
        input.addEventListener('input', function() { apply(this.value); });
        input.addEventListener('change', function() { apply(this.value); });
        slider.addEventListener('input', function() { apply(this.value); });
        wrap.appendChild(input);
        wrap.appendChild(slider);
        form.appendChild(wrap);
    }

    function getTrackIndexAtClientY(clientY) {
        var tracksEl = document.getElementById('vt-tracks');
        if (!tracksEl) return -1;
        var rows = tracksEl.querySelectorAll('.vt-track-row');
        for (var i = 0; i < rows.length; i++) {
            var rect = rows[i].getBoundingClientRect();
            if (clientY >= rect.top && clientY <= rect.bottom) return i;
        }
        return -1;
    }

    function dragClip(div, clip) {
        div.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('vt-clip-resize') || e.target.classList.contains('vt-clip-resize-left')) return;
            var startX = e.clientX;
            var row = div.closest('.vt-track-row');
            var startTrackIndex = row ? parseInt(row.getAttribute('data-track-index'), 10) : 0;
            if (isNaN(startTrackIndex)) startTrackIndex = 0;
            var sel = getSelectedClips();
            if (sel.length === 0) sel = [clip];
            var startStarts = sel.map(function(c) { return parseFloat(c.start_time) || 0; });
            var minStart = Math.min.apply(null, startStarts);
            var maxEnd = Math.max.apply(null, sel.map(function(c) {
                return (parseFloat(c.start_time) || 0) + (parseFloat(c.duration) || 1);
            }));
            var totalLen = maxEnd - minStart;
            var trackChanged = false;
            function onMove(ev) {
                var dx = (ev.clientX - startX) / state.pxPerSec;
                var delta = dx;
                var newMin = minStart + delta;
                var newMax = newMin + totalLen;
                if (newMin < 0) delta -= newMin;
                if (newMax > getDuration()) delta -= (newMax - getDuration());
                sel.forEach(function(c, i) {
                    var next = startStarts[i] + delta;
                    c.start_time = state.snapEnabled ? snapToGrid(next) : next;
                    var d = app.querySelector('[data-clip-cid="' + (c._cid || '') + '"]');
                    if (d) d.style.left = (c.start_time * state.pxPerSec) + 'px';
                });
            }
            function onUp(ev) {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                var targetTrackIndex = getTrackIndexAtClientY(ev.clientY);
                if (targetTrackIndex >= 0 && targetTrackIndex !== startTrackIndex) {
                    var targetTrack = state.tracks[targetTrackIndex];
                    var newTrackId = (targetTrack && targetTrack.id != null) ? targetTrack.id : targetTrackIndex;
                    pushSnapshot();
                    sel.forEach(function(c) {
                        c.track_id = newTrackId;
                    });
                    renderTracks();
                    renderPlayhead();
                    updatePreview();
                }
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    }

    function resizeClip(clip, e) {
        pushSnapshot();
        var startX = e.clientX;
        var startDur = parseFloat(clip.duration) || 1;
        var startStart = parseFloat(clip.start_time) || 0;
        function onMove(ev) {
            var dx = (ev.clientX - startX) / state.pxPerSec;
            var next = Math.max(0.1, startDur + dx);
            var maxDur = getDuration() - startStart;
            if (next > maxDur) next = maxDur;
            clip.duration = state.snapEnabled ? snapToGrid(next) : next;
            var div = app.querySelector('[data-clip-cid="' + (clip._cid || '') + '"]');
            if (div) {
                div.style.width = (clip.duration * state.pxPerSec) + 'px';
                var lbl = div.querySelector('.vt-clip-label');
                if (lbl) lbl.textContent = startStart.toFixed(1) + ' – ' + (startStart + clip.duration).toFixed(1) + 's';
            }
        }
        function onUp() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            updatePreview();
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }

    function bindTimelineTabs() {
        var tabs = document.getElementById('vt-timeline-tabs');
        if (!tabs) return;
        tabs.querySelectorAll('.vt-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var filter = btn.getAttribute('data-filter') || 'all';
                state.contentFilter = filter;
                tabs.querySelectorAll('.vt-tab').forEach(function(b) {
                    b.classList.toggle('active', b.getAttribute('data-filter') === filter);
                    b.setAttribute('aria-selected', b.getAttribute('data-filter') === filter ? 'true' : 'false');
                });
                renderTracks();
            });
        });
    }

    function bindTrackReorder() {
        app.addEventListener('mousedown', function(e) {
            var handle = e.target.closest('.vt-track-drag');
            if (!handle) return;
            e.preventDefault();
            var fromIndex = parseInt(handle.getAttribute('data-track-index'), 10);
            if (isNaN(fromIndex)) return;
            var row = handle.closest('.vt-track-row');
            if (!row) return;
            row.classList.add('vt-track-dragging');
            var lastTargetIndex = fromIndex;
            function onMove(ev) {
                var container = document.getElementById('vt-tracks');
                if (!container) return;
                var rows = container.querySelectorAll('.vt-track-row:not(.vt-track-dragging)');
                for (var i = 0; i < rows.length; i++) {
                    var rect = rows[i].getBoundingClientRect();
                    if (ev.clientY < rect.top + rect.height / 2) {
                        lastTargetIndex = i;
                        return;
                    }
                    lastTargetIndex = i + 1;
                }
            }
            function onUp() {
                row.classList.remove('vt-track-dragging');
                if (lastTargetIndex !== fromIndex && lastTargetIndex >= 0 && lastTargetIndex <= state.tracks.length) {
                    pushSnapshot();
                    var tr = state.tracks.splice(fromIndex, 1)[0];
                    var insertAt = lastTargetIndex > fromIndex ? lastTargetIndex - 1 : lastTargetIndex;
                    state.tracks.splice(insertAt, 0, tr);
                    renderRuler();
                    renderTracks();
                    renderPlayhead();
                }
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    }

    function bindAddTrack() {
        var btn = document.getElementById('vt-add-track');
        if (!btn) return;
        btn.addEventListener('click', function() {
            pushSnapshot();
            var newTrack = {
                id: null,
                name: 'Layer ' + (state.tracks.length + 1),
                sort_order: state.tracks.length,
                is_locked: 0,
                is_muted: 0
            };
            state.tracks.push(newTrack);
            state.selectedTrackIndex = state.tracks.length - 1;
            renderRuler();
            renderTracks();
        });
    }

    function addClipToTrack(trackIndex, type) {
        var track = state.tracks[trackIndex];
        if (!track) return;
        pushSnapshot();
        var start = 0;
        state.clips.forEach(function(c) {
            var tid = c.track_id;
            if ((track.id != null && Number(tid) === Number(track.id)) || state.tracks.indexOf(track) === (typeof tid === 'number' && tid < state.tracks.length ? tid : -1)) {
                var end = (parseFloat(c.start_time) || 0) + (parseFloat(c.duration) || 1);
                if (end > start) start = end;
            }
        });
        start = state.snapEnabled ? snapToGrid(start) : start;
        var content = { keyframes: {} };
        if (type === 'text') {
            content.text = 'Metin';
            content.font_size = 48;
            content.font_family = 'system-ui';
            content.color = '#ffffff';
            content.x = 100;
            content.y = 200;
            var fs = 48;
            content.textBoxWidth = Math.min(1200, Math.max(20, 5 * fs * 0.55));
            content.textBoxHeight = Math.min(800, Math.max(16, fs * 1.25));
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
        clip._cid = 'c' + Math.random().toString(36).slice(2);
        state.clips.push(clip);
        renderTracks();
        selectClip(clip, false);
        updatePreview();
    }

    function findClipByEl(clipEl) {
        if (!clipEl) return null;
        var cid = clipEl.getAttribute('data-clip-cid');
        var clipId = clipEl.getAttribute('data-clip-id');
        return state.clips.find(function(c) {
            if (cid && (c._cid === cid)) return true;
            if (clipId != null && clipId !== '' && (String(c.id) === clipId || c._cid === clipId)) return true;
            return false;
        }) || null;
    }

    function bindTimelineClicks() {
        var body = document.getElementById('vt-timeline-body');
        if (!body) return;
        function handleTimelinePointer(e) {
            var target = e.target;
            if (!body.contains(target)) return;
            var trackBtn = target.closest('.vt-track-btn');
            var action = trackBtn ? trackBtn.getAttribute('data-action') : null;
            var trackIdxStr = trackBtn ? trackBtn.getAttribute('data-track-index') : null;
            if (trackBtn && (action === 'lock' || action === 'mute') && trackIdxStr !== null && trackIdxStr !== '') {
                e.preventDefault();
                e.stopPropagation();
                var idx = parseInt(trackIdxStr, 10);
                if (isNaN(idx) || idx < 0 || idx >= state.tracks.length) return;
                var tr = state.tracks[idx];
                if (!tr) return;
                if (action === 'lock') {
                    var currentlyLocked = tr.is_locked === 1 || tr.is_locked === '1' || tr.is_locked === true;
                    tr.is_locked = currentlyLocked ? 0 : 1;
                    pushSnapshot();
                    renderTracks();
                } else if (action === 'mute') {
                    var currentlyMuted = tr.is_muted === 1 || tr.is_muted === '1' || tr.is_muted === true;
                    tr.is_muted = currentlyMuted ? 0 : 1;
                    pushSnapshot();
                    renderTracks();
                }
                return;
            }
            var clipEl = target.closest('.vt-clip');
            if (clipEl && !target.closest('.vt-clip-resize') && !target.closest('.vt-clip-resize-left')) {
                var targetClip = findClipByEl(clipEl);
                if (targetClip) {
                    e.preventDefault();
                    e.stopPropagation();
                    selectClip(targetClip, e.shiftKey);
                }
            }
        }
        document.addEventListener('click', handleTimelinePointer, true);
    }

    function getProjectPayload() {
        var nameEl = document.getElementById('vt-name');
        var widthEl = document.getElementById('vt-width');
        var heightEl = document.getElementById('vt-height');
        var durationEl = document.getElementById('vt-duration');
        var bgEl = document.getElementById('vt-bg-color');
        state.timeline.name = nameEl ? nameEl.value : state.timeline.name;
        state.timeline.width = widthEl ? parseInt(widthEl.value, 10) : state.timeline.width;
        state.timeline.height = heightEl ? parseInt(heightEl.value, 10) : state.timeline.height;
        state.timeline.duration_sec = durationEl ? parseFloat(durationEl.value) : state.timeline.duration_sec;
        if (bgEl) state.timeline.background_color = bgEl.value;
        return {
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
    }

    function bindDownloadProject() {
        var btn = document.getElementById('vt-download-project');
        if (!btn) return;
        btn.addEventListener('click', function() {
            var payload = getProjectPayload();
            payload.exported_at = new Date().toISOString();
            payload.version = 1;
            var json = JSON.stringify(payload, null, 2);
            var name = (payload.timeline.name || 'proje').replace(/[^\w\u00C0-\u024F\-]/gi, '-').replace(/-+/g, '-') || 'proje';
            var date = new Date().toISOString().slice(0, 10);
            var filename = 'timeline-' + name + '-' + date + '.json';
            var blob = new Blob([json], { type: 'application/json' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('Proje indirildi: ' + filename, 'success');
        });
    }

    function bindSave() {
        var btn = document.getElementById('vt-save');
        if (!btn) return;
        btn.addEventListener('click', function() {
            var payload = getProjectPayload();
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined">hourglass_empty</span> Kaydediliyor...';
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
                    showToast('Kaydedildi.', 'success');
                } else {
                    showToast('Hata: ' + (res.message || xhr.statusText), 'error');
                }
            };
            xhr.onerror = function() {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined">save</span> Kaydet';
                showToast('Kayıt sırasında hata oluştu.', 'error');
            };
            xhr.send(JSON.stringify(payload));
        });
    }

    init();
})();
