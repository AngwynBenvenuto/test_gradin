@extends('layouts.front-full')
@section('title', $title)

@section('content')
<div class="tippler-page">
    <div class="tippler-topbar">
        <div>
            <h4 class="mb-0">Tippler Dashboard</h4>
            <small class="text-muted">&nbsp;</small>
        </div>
        <div class="topbar-right">
            <div id="clock" class="clock"></div>
            <small class="text-muted">Auto refresh aktif</small>
            {{-- Disembunyikan dulu: behavior push cloud->LAN belum jelas (base URL/auth/endpoint
                 penerima di sisi LAN belum ada), lihat catatan di memory sync-ke-lan-push-pending. --}}
            <div style="display:none;">
                <button type="button" class="btn-sync-lan" id="btn-sync-lan" onclick="syncToLan()">
                    <span id="sync-lan-icon">&#8635;</span> Sync ke LAN
                </button>
            </div>
        </div>
    </div>

    <div class="tippler-grid">
        @foreach ($cameras as $camera)
            <div class="tippler-card" id="card-{{ $camera->id }}">
                <div class="card-main">
                    <div class="card-left">
                        <div class="card-title">{{ $camera->name }}</div>
                        <div class="card-subtitle">{{ $camera->location ?? '-' }}</div>
                    </div>

                    <div class="status-banners">
                        <span class="mini-badge cctv idle" id="cctv-badge-{{ $camera->id }}">CCTV IDLE</span>
                        <span class="mini-badge port idle" id="port-badge-{{ $camera->id }}">
                            {{ $camera->arduinoPort?->com_port ?? '-' }} IDLE
                        </span>
                        <button
                            type="button"
                            class="btn-refresh"
                            id="refresh-btn-{{ $camera->id }}"
                            onclick="refreshCard({{ $camera->id }})"
                            title="Cek koneksi sekarang"
                        >&#8635;</button>
                    </div>
                </div>

                <div class="compact-line">
                    <span id="compact-port-{{ $camera->id }}">Port: {{ $camera->arduinoPort?->com_port ?? '-' }}</span>
                    <span>•</span>
                    <span id="compact-last-{{ $camera->id }}">Last: -</span>
                </div>

                <div class="active-panel" id="panel-{{ $camera->id }}">
                    <div class="event-line">
                        <div>
                            <span class="event-label">Event</span>
                            <strong id="event-text-{{ $camera->id }}">-</strong>
                        </div>
                        <div>
                            <span class="event-label">Waktu</span>
                            <strong id="event-time-{{ $camera->id }}">-</strong>
                        </div>
                    </div>

                    <div class="input-row">
                        <div class="form-block ticket-block">
                            <label>No Tiket</label>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                id="ticket-{{ $camera->id }}"
                                placeholder="otomatis dari barcode / input manual"
                                autocomplete="off"
                                onblur="checkGandeng({{ $camera->id }}, this.value.trim())"
                            >
                        </div>
                        <div class="form-block trash-block">
                            <label>Trash</label>
                            <select class="form-control form-control-sm" id="trash-{{ $camera->id }}">
                                <option value="">Pilih</option>
                                <option value="0">0%</option>
                                <option value="3">3%</option>
                                <option value="6">6%</option>
                                <option value="9">9%</option>
                                <option value="12">12%</option>
                                <option value="15">15%</option>
                                <option value="20">&gt;20%</option>
                            </select>
                        </div>
                        <div class="form-block trash-block" id="trash2-block-{{ $camera->id }}" style="display:none;">
                            <label>Trash Gandeng</label>
                            <select class="form-control form-control-sm" id="trash2-{{ $camera->id }}">
                                <option value="">Pilih</option>
                                <option value="0">0%</option>
                                <option value="3">3%</option>
                                <option value="6">6%</option>
                                <option value="9">9%</option>
                                <option value="12">12%</option>
                                <option value="15">15%</option>
                                <option value="20">&gt;20%</option>
                            </select>
                        </div>
                        <div class="form-block bakar-block">
                            <label>&nbsp;</label>
                            <div class="bakar-wrap">
                                <input type="checkbox" id="bakar-{{ $camera->id }}" disabled>
                                <label for="bakar-{{ $camera->id }}">Tebu Bakar</label>
                            </div>
                        </div>
                    </div>

                    <div class="capture-row">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="capture-btn-{{ $camera->id }}" onclick="captureCard({{ $camera->id }})">
                            &#128247; Capture
                        </button>
                        <small class="capture-info" id="capture-info-{{ $camera->id }}"></small>
                    </div>

                    <div class="thumb-row" id="thumbs-{{ $camera->id }}">
                        <div class="thumb empty">1</div>
                        <div class="thumb empty">2</div>
                        <div class="thumb empty">3</div>
                    </div>

                    <div class="action-row">
                        <button type="button" class="btn btn-sm btn-primary" id="save-btn-{{ $camera->id }}" onclick="saveCard({{ $camera->id }})" disabled>
                            Simpan
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearCardInput({{ $camera->id }})">
                            Clear
                        </button>
                        <small class="save-info" id="save-info-{{ $camera->id }}"></small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="tippler-toast" id="tippler-toast"></div>

<style>
    html, body { overflow: hidden; }

    .tippler-page {
        min-height: 100vh;
        background: #f3f5f9;
        padding: 12px;
    }

    .tippler-topbar {
        height: 48px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .tippler-topbar h4 { font-weight: 800; }
    .topbar-right { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
    .clock { font-weight: 800; font-size: 14px; }

    .btn-sync-lan {
        border: 1px solid #b6d4fe;
        background: #e7f1ff;
        color: #0d6efd;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .btn-sync-lan:hover { background: #d7e8ff; }
    .btn-sync-lan:disabled { opacity: .5; cursor: default; }

    #sync-lan-icon.spinning {
        display: inline-block;
        animation: btn-refresh-spin .8s linear infinite;
    }

    .tippler-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        height: calc(100vh - 70px);
        align-content: start;
    }

    .tippler-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 14px;
        padding: 10px;
        min-height: 112px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .06);
        transition: .16s ease;
        overflow: hidden;
    }

    .tippler-card.is-active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, .10), 0 4px 14px rgba(13, 110, 253, .14);
    }

    .tippler-card.motion-only {
        border-color: #20c997;
    }

    .tippler-card.failed {
        border-color: #dc3545;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, .10), 0 4px 14px rgba(220, 53, 69, .14);
    }

    .card-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }

    .card-left { min-width: 0; }
    .card-title { font-size: 16px; font-weight: 900; line-height: 1.1; }
    .card-subtitle { font-size: 11px; color: #6c757d; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .status-banners {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex: 0 0 auto;
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 86px;
        padding: 3px 7px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
        line-height: 1.2;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .mini-badge.idle { background: #f1f3f5; color: #495057; border-color: #dee2e6; }
    .mini-badge.online { background: #e6fcf5; color: #087f5b; border-color: #c3fae8; }
    .mini-badge.active { background: #e7f1ff; color: #0d6efd; border-color: #b6d4fe; }
    .mini-badge.motion { background: #fff3bf; color: #997404; border-color: #ffe066; }
    .mini-badge.failed { background: #fff5f5; color: #dc3545; border-color: #ffc9c9; }
    .mini-badge.offline { background: #fff5f5; color: #c92a2a; border-color: #ffc9c9; }

    .btn-refresh {
        border: 1px solid #dee2e6;
        background: #fff;
        color: #495057;
        border-radius: 999px;
        width: 22px;
        height: 22px;
        font-size: 13px;
        line-height: 1;
        cursor: pointer;
        align-self: flex-end;
    }

    .btn-refresh:hover { background: #f1f3f5; }
    .btn-refresh:disabled { opacity: .5; cursor: default; }

    .btn-refresh.spinning {
        animation: btn-refresh-spin .8s linear infinite;
    }

    @keyframes btn-refresh-spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .compact-line {
        margin-top: 8px;
        display: flex;
        gap: 6px;
        align-items: center;
        color: #6c757d;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .compact-line span:last-child {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .active-panel {
        display: none;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #d0d7de;
    }

    /* 'panel-open': panel terbuka (baik status siaga-semua-siap maupun fokus event).
       'is-active': fokus event sungguhan pada kamera ini -> border/highlight biru. */
    .tippler-card.panel-open .active-panel { display: block; }

    .event-line {
        display: grid;
        grid-template-columns: 1.5fr .9fr;
        gap: 8px;
        background: #f8fafc;
        border-radius: 10px;
        padding: 7px;
        margin-bottom: 8px;
    }

    .event-label {
        display: block;
        font-size: 10px;
        color: #6c757d;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .event-line strong {
        display: block;
        font-size: 12px;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .input-row {
        display: grid;
        grid-template-columns: 1.4fr .72fr;
        gap: 8px;
        margin-bottom: 8px;
    }

    .form-block label {
        display: block;
        font-size: 11px;
        color: #495057;
        font-weight: 800;
        margin-bottom: 3px;
    }

    .form-control-sm {
        height: 32px;
        font-size: 13px;
        font-weight: 800;
    }

    .trash-wrap {
        display: grid;
        grid-template-columns: 1fr 20px;
        align-items: center;
        gap: 5px;
    }

    .trash-wrap span { font-weight: 900; color: #495057; }

    .bakar-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 31px;
    }

    .bakar-wrap label { margin: 0; font-weight: 700; color: #495057; font-size: 12px; }

    .capture-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .capture-info {
        color: #6c757d;
        font-size: 11px;
    }

    .thumb-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 7px;
        margin-bottom: 8px;
    }

    .thumb {
        height: 58px;
        border-radius: 10px;
        background: #111827;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }

    .thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .thumb.empty { background: #eef1f5; color: #94a3b8; cursor: default; }

    .action-row {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .save-info {
        margin-left: auto;
        color: #6c757d;
        font-size: 11px;
        text-align: right;
    }

    .tippler-toast {
        position: fixed;
        right: 18px;
        bottom: 18px;
        min-width: 260px;
        max-width: 360px;
        padding: 12px 14px;
        border-radius: 12px;
        color: #fff;
        font-weight: 800;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .18);
        opacity: 0;
        transform: translateY(12px);
        pointer-events: none;
        transition: .18s ease;
        z-index: 9999;
    }

    .tippler-toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .tippler-toast.success { background: #198754; }
    .tippler-toast.error { background: #dc3545; }

    @media (max-width: 1200px) {
        html, body { overflow: auto; }
        .tippler-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); height: auto; }
    }

    @media (max-width: 768px) {
        .tippler-topbar { height: auto; align-items: flex-start; }
        .tippler-grid { grid-template-columns: 1fr; }
        .input-row { grid-template-columns: 1fr; }
    }
</style>

<script>
    const cameraIds = @json($cameras->pluck('id')->values());
    const apiBase = @json(url('/api/tippler'));
    const knownEventKeys = {};
    let toastTimer = null;

    function updateClock() {
        const el = document.getElementById('clock');
        if (el) el.innerText = new Date().toLocaleString('id-ID');
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('tippler-toast');
        if (!toast) return;

        toast.innerText = message;
        toast.className = `tippler-toast ${type} show`;

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.className = `tippler-toast ${type}`;
        }, 2600);
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.innerText = value ?? '-';
    }

    function setBadgeClass(el, state) {
        if (!el) return;
        el.classList.remove('idle', 'online', 'active', 'motion', 'failed', 'offline');
        el.classList.add(state);
    }

    function setCardState(cameraId, item) {
        const card = document.getElementById(`card-${cameraId}`);
        const cctvBadge = document.getElementById(`cctv-badge-${cameraId}`);
        const portBadge = document.getElementById(`port-badge-${cameraId}`);

        if (!card || !cctvBadge || !portBadge) return;

        // 'panel-open'/'is-active' (visibility & focus) dikelola terpusat oleh
        // applyCardVisibility() setelah semua item status diketahui, bukan di sini.
        card.classList.remove('motion-only', 'failed');

        if (!item.has_pending_event && item.motion_status === 'active') {
            card.classList.add('motion-only');
        }

        if (item.has_failed_capture) {
            card.classList.add('failed');
        }

        if (item.camera_connection_status === 'active') {
            cctvBadge.innerText = 'CCTV ACTIVE';
            setBadgeClass(cctvBadge, 'active');
        } else if (item.camera_connection_status === 'offline') {
            cctvBadge.innerText = 'CCTV OFFLINE';
            setBadgeClass(cctvBadge, 'offline');
        } else {
            cctvBadge.innerText = 'CCTV IDLE';
            setBadgeClass(cctvBadge, 'idle');
        }

        const portName = item.com_port || '-';
        if (item.arduino_status === 'active') {
            portBadge.innerText = `${portName} ACTIVE`;
            setBadgeClass(portBadge, 'active');
        } else if (item.arduino_status === 'offline') {
            portBadge.innerText = `${portName} OFFLINE`;
            setBadgeClass(portBadge, 'offline');
        } else {
            portBadge.innerText = `${portName} IDLE`;
            setBadgeClass(portBadge, 'idle');
        }
    }

    function setThumbnails(cameraId, photos) {
        const wrap = document.getElementById(`thumbs-${cameraId}`);
        if (!wrap) return;

        wrap.innerHTML = '';
        for (let i = 0; i < 3; i++) {
            const photo = photos && photos[i] ? photos[i] : null;
            const div = document.createElement('div');
            div.className = photo ? 'thumb' : 'thumb empty';

            if (photo) {
                div.innerHTML = `<img src="${photo.url}" alt="capture ${i + 1}">`;
                div.onclick = () => window.open(photo.url, '_blank');
            } else {
                div.innerText = i + 1;
            }

            wrap.appendChild(div);
        }
    }

    function isInputDirty(cameraId) {
        const ticket = document.getElementById(`ticket-${cameraId}`)?.value.trim();
        const trash = document.getElementById(`trash-${cameraId}`)?.value.trim();
        return !!ticket || !!trash;
    }

    function fillForNewEvent(cameraId, item) {
        const currentKey = item.event_key || '';

        if (!item.has_pending_event) {
            knownEventKeys[cameraId] = currentKey;
            return;
        }

        const isNewEvent = knownEventKeys[cameraId] !== currentKey;

        if (isNewEvent) {
            clearCardInput(cameraId, false);
            knownEventKeys[cameraId] = currentKey;
        }

        const ticketInput = document.getElementById(`ticket-${cameraId}`);
        if (ticketInput && item.suggested_ticket_no && !ticketInput.value.trim()) {
            ticketInput.value = item.suggested_ticket_no;
            checkGandeng(cameraId, item.suggested_ticket_no);
        }
    }

    // Cek jenis truk (gandeng = 2 bak, butuh 2 bacaan trash) dari
    // cam_timbangan_tiket lewat no_tiket, toggle input trash kedua.
    async function checkGandeng(cameraId, ticket) {
        const block = document.getElementById(`trash2-block-${cameraId}`);
        const trash2 = document.getElementById(`trash2-${cameraId}`);
        const bakar = document.getElementById(`bakar-${cameraId}`);
        if (!block) return;

        if (!ticket) {
            block.style.display = 'none';
            if (trash2) trash2.value = '';
            if (bakar) bakar.checked = false;
            return;
        }

        try {
            const response = await fetch(`${apiBase}/ticketInfo?no_ticket=${encodeURIComponent(ticket)}`);
            const result = await response.json();
            const isGandeng = !!(result.success && result.data && result.data.is_gandeng);
            block.style.display = isGandeng ? '' : 'none';
            if (!isGandeng && trash2) trash2.value = '';
            if (bakar) bakar.checked = !!(result.success && result.data && result.data.is_bakar);
        } catch (error) {
            block.style.display = 'none';
            if (bakar) bakar.checked = false;
        }
    }

    function clearCardInput(cameraId, clearInfo = true) {
        const ticket = document.getElementById(`ticket-${cameraId}`);
        const trash = document.getElementById(`trash-${cameraId}`);
        const trash2 = document.getElementById(`trash2-${cameraId}`);
        const trash2Block = document.getElementById(`trash2-block-${cameraId}`);
        const bakar = document.getElementById(`bakar-${cameraId}`);
        const info = document.getElementById(`save-info-${cameraId}`);

        if (ticket) ticket.value = '';
        if (trash) trash.value = '';
        if (trash2) trash2.value = '';
        if (trash2Block) trash2Block.style.display = 'none';
        if (bakar) bakar.checked = false;
        if (clearInfo && info) info.innerText = '';
    }

    function collapseCard(cameraId) {
        const card = document.getElementById(`card-${cameraId}`);
        if (!card) return;
        card.classList.remove('is-active', 'panel-open');
    }

    // Trigger utama buka/tutup collapsible adalah status com port Arduino,
    // BUKAN koneksi kamera CCTV. Kamera cuma dipakai untuk ambil foto 3x
    // (dilakukan oleh proses listener terpisah, camera:listen-events) --
    // itu tetap butuh kamera aktif, tapi tidak menentukan buka/tutupnya panel.
    function isArduinoActive(item) {
        return item.arduino_status === 'active';
    }

    // Aturan tampil/fokus card, dihitung sekali per polling dari seluruh item:
    // - Kalau ada kamera dengan event pending -> hanya card itu yang panelnya
    //   terbuka & fokus (tombol Simpan aktif), card lain otomatis tertutup.
    // - Kalau tidak ada event tapi com port Arduino semua card sudah active ->
    //   semua panel terbuka supaya siap diisi, tapi tombol Simpan semua tetap disabled.
    // - Selain itu (belum semua active, belum ada event) -> semua panel tertutup.
    function applyCardVisibility(items) {
        const eventItems = items.filter(item => item.has_pending_event);
        const hasFocusedEvent = eventItems.length > 0;
        const allReady = items.length > 0 && items.every(isArduinoActive);

        items.forEach(item => {
            const cameraId = item.camera_id;
            const card = document.getElementById(`card-${cameraId}`);
            const saveBtn = document.getElementById(`save-btn-${cameraId}`);
            if (!card) return;

            let showPanel = false;
            let enableSave = false;

            const hasPhoto = !!(item.thumbnails && item.thumbnails.length > 0);

            if (hasFocusedEvent) {
                showPanel = !!item.has_pending_event;
                enableSave = !!item.has_pending_event && hasPhoto;
            } else if (allReady) {
                showPanel = true;
            }

            card.classList.toggle('panel-open', showPanel);
            card.classList.toggle('is-active', !!item.has_pending_event);
            if (saveBtn) saveBtn.disabled = !enableSave;
        });
    }

    async function loadStatus() {
        try {
            const response = await fetch(`${apiBase}/status?t=` + Date.now());
            const result = await response.json();
            if (!result.success) return;

            result.data.forEach(item => {
                const cameraId = item.camera_id;

                setCardState(cameraId, item);
                setText(`compact-port-${cameraId}`, `Port: ${item.com_port || '-'}`);
                setText(`compact-last-${cameraId}`, `Last: ${item.last_input_text || '-'}`);
                setText(`event-text-${cameraId}`, item.last_input_text || '-');
                setText(`event-time-${cameraId}`, item.arduino_last_read_at || '-');
                setThumbnails(cameraId, item.thumbnails || []);

                if (item.has_pending_event || !isInputDirty(cameraId)) {
                    fillForNewEvent(cameraId, item);
                }
            });

            applyCardVisibility(result.data);
        } catch (error) {
            console.error(error);
        }
    }

    function jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
    }

    async function refreshCard(cameraId) {
        const btn = document.getElementById(`refresh-btn-${cameraId}`);
        if (btn) {
            btn.disabled = true;
            btn.classList.add('spinning');
        }

        try {
            const response = await fetch(`${apiBase}/probe?camera_id=${cameraId}`, {
                method: 'POST',
                headers: jsonHeaders()
            });
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal cek koneksi.');
            }

            await loadStatus();
        } catch (error) {
            showToast(error.message || 'Gagal cek koneksi.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('spinning');
            }
        }
    }

    async function captureCard(cameraId) {
        const btn = document.getElementById(`capture-btn-${cameraId}`);
        const info = document.getElementById(`capture-info-${cameraId}`);
        if (btn) btn.disabled = true;
        if (info) info.innerText = 'Mengambil foto...';

        try {
            const response = await fetch(`${apiBase}/capture`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({ camera_id: cameraId })
            });
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal mengambil foto.');
            }

            showToast(result.message || 'Foto berhasil diambil.', 'success');
            await loadStatus();
        } catch (error) {
            showToast(error.message || 'Gagal mengambil foto.', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (info) info.innerText = '';
        }
    }

    async function syncToLan() {
        const btn = document.getElementById('btn-sync-lan');
        const icon = document.getElementById('sync-lan-icon');
        if (btn) btn.disabled = true;
        if (icon) icon.classList.add('spinning');

        try {
            const response = await fetch(`${apiBase}/probe`, {
                method: 'POST',
                headers: jsonHeaders()
            });
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal sync ke LAN.');
            }

            await loadStatus();
            showToast('Sync ke LAN berhasil.', 'success');
        } catch (error) {
            showToast(error.message || 'Gagal sync ke LAN.', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (icon) icon.classList.remove('spinning');
        }
    }

    async function saveCard(cameraId) {
        const ticket = document.getElementById(`ticket-${cameraId}`)?.value.trim();
        const trash = document.getElementById(`trash-${cameraId}`)?.value.trim();
        const trash2Block = document.getElementById(`trash2-block-${cameraId}`);
        const isGandeng = !!(trash2Block && trash2Block.style.display !== 'none');
        const trash2 = document.getElementById(`trash2-${cameraId}`)?.value.trim();
        const info = document.getElementById(`save-info-${cameraId}`);

        const thumbsWrap = document.getElementById(`thumbs-${cameraId}`);
        const hasPhoto = !!(thumbsWrap && thumbsWrap.querySelector('.thumb:not(.empty)'));

        if (!hasPhoto) {
            showToast('Foto belum ada, silakan capture dulu.', 'error');
            return;
        }

        if (!ticket) {
            showToast('No tiket wajib diisi.', 'error');
            return;
        }

        if (trash === '' || Number(trash) < 0 || Number(trash) > 100) {
            showToast('Trash wajib diisi 0 - 100%.', 'error');
            return;
        }

        if (isGandeng && (trash2 === '' || Number(trash2) < 0 || Number(trash2) > 100)) {
            showToast('Truk gandeng, trash bak kedua wajib diisi 0 - 100%.', 'error');
            return;
        }

        if (info) info.innerText = 'Menyimpan...';

        try {
            const response = await fetch(`${apiBase}/save`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    camera_id: cameraId,
                    no_ticket: ticket,
                    trash_percentage: trash,
                    trash_percentage_2: isGandeng ? trash2 : null
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menyimpan data.');
            }

            if (info) info.innerText = 'Tersimpan ' + (result.data.saved_at || '');

            showToast(result.message || 'Data berhasil disimpan.', 'success');
            clearCardInput(cameraId, false);
            collapseCard(cameraId);
            await loadStatus();
        } catch (error) {
            if (info) info.innerText = 'Gagal simpan';
            showToast(error.message || 'Gagal menyimpan data.', 'error');
        }
    }

    updateClock();
    loadStatus();

    setInterval(updateClock, 1000);
    setInterval(loadStatus, 500);
</script>
@endsection
