@extends('layouts.tracking')
@section('title', $title)

@section('extend_script')
    <link href="{{ asset('media/plugins/leafletjs/leaflet.css') }}" rel='stylesheet' media='screen' />

    {{-- ===== PWA: hanya aktif di halaman ini ===== --}}
    <link rel="manifest" href="{{ asset('media/manifest.json') }}">
    <meta name="theme-color" content="#189e31">
    <link rel="apple-touch-icon" href="{{ asset('media/img/pwa/icon-192x192.png') }}">
    <script type="text/javascript">
        (function () {
            if (!('serviceWorker' in navigator)) return;

            // Skrip SW berada di root aplikasi, jadi scope maksimalnya adalah root.
            // Kita persempit ke /tracking supaya halaman lain (/admin, /login, ...)
            // tidak pernah dikontrol service worker. Karena scope lebih SEMPIT dari
            // lokasi skrip, header Service-Worker-Allowed tidak diperlukan.
            var swUrl   = @json(url('/service-worker.js'));
            var swScope = @json(parse_url(url('/tracking'), PHP_URL_PATH));

            window.addEventListener('load', function () {
                navigator.serviceWorker.register(swUrl, { scope: swScope }).then(
                    function (reg) { console.log('ServiceWorker aktif, scope:', reg.scope); },
                    function (err) { console.warn('ServiceWorker gagal didaftarkan:', err); }
                );
            });
        })();
    </script>

    <style>
    @verbatim
        /* ====== Banner "Pasang aplikasi" (PWA) ====== */
        #pwaInstall {
            display: none;
            position: absolute; left: 50%; bottom: 18px; transform: translateX(-50%);
            z-index: 900; background: #fff; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.28);
            padding: 10px 12px; gap: 10px; align-items: center;
            max-width: calc(100% - 24px);
        }
        #pwaInstall.show { display: flex; }
        #pwaInstall .pwa-icon { width: 38px; height: 38px; border-radius: 8px; flex: 0 0 38px; }
        #pwaInstall .pwa-txt { line-height: 1.3; min-width: 0; }
        #pwaInstall .pwa-title { font-size: 13px; font-weight: 700; color: #111827; }
        #pwaInstall .pwa-sub   { font-size: 11.5px; color: #6b7280; }
        #pwaInstall .pwa-act   { display: flex; gap: 6px; margin-left: 6px; }
        #pwaInstall .pwa-act .btn { font-size: 12px; padding: 4px 12px; white-space: nowrap; }
        @media (max-width: 480px) {
            #pwaInstall { left: 12px; right: 12px; transform: none; max-width: none; }
            #pwaInstall .pwa-icon { display: none; }
        }
    @endverbatim
    </style>

    <style>
    @verbatim
        /* ====== Shell ====== */
        .tracking-wrapper {
            position: relative;
            margin: -15px;
            display: flex;
            flex-direction: column;
            background: #f1f3f5;
        }
        .tracking-wrapper:-webkit-full-screen { width: 100vw; height: 100vh; margin: 0; }
        .tracking-wrapper:-moz-full-screen    { width: 100vw; height: 100vh; margin: 0; }
        .tracking-wrapper:fullscreen          { width: 100vw; height: 100vh; margin: 0; }

        /* ====== Top bar ====== */
        .track-topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 8px 14px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }
        .tb-title { font-size: 16px; font-weight: 700; color: #1f2937; white-space: nowrap; display: flex; align-items: baseline; gap: 6px; }
        .tb-title i { color: #1a73e8; }
        .tb-title small { font-size: 11px; font-weight: 500; color: #9ca3af; }
        .tb-stats { display: flex; gap: 8px; flex-wrap: wrap; }
        .tb-stats .stat {
            font-size: 12px; background: #f3f4f6; border-radius: 20px;
            padding: 3px 10px; white-space: nowrap; color: #374151;
        }
        .tb-stats .stat b { font-size: 13px; margin-left: 3px; }
        .tb-refresh {
            margin-left: auto; display: flex; align-items: center; gap: 8px;
            font-size: 12px; color: #374151; white-space: nowrap;
        }
        .tb-refresh .refresh-prog { width: 80px; height: 6px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
        .tb-refresh .refresh-prog > div { height: 100%; background: #1a73e8; width: 100%; transition: width 1s linear; }
        #spinIcon { color: #1a73e8; }
        #spinIcon.spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #sidebarToggle { display: inline-flex; }

        /* ====== Main (sidebar + map) ====== */
        .track-main { display: flex; position: relative; height: calc(100vh - 210px); min-height: 360px; }
        /* Tinggi presisi diatur via JS fitHeight(). Selector fullscreen ditulis terpisah:
           jika digabung koma & salah satu prefix tak dikenal browser, seluruh rule gugur. */
        .tracking-wrapper:fullscreen          .track-main { flex: 1; }
        .tracking-wrapper:-webkit-full-screen .track-main { flex: 1; }
        .tracking-wrapper:-moz-full-screen    .track-main { flex: 1; }
        .tracking-wrapper.sidebar-collapsed .track-sidebar { display: none; }

        /* ====== Sidebar ====== */
        .track-sidebar { flex: 0 0 300px; width: 300px; background: #fff; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; }
        .sb-head { padding: 10px; border-bottom: 1px solid #eef0f2; }
        .sb-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
        .sb-actions { display: flex; gap: 6px; margin-top: 6px; }
        .sb-actions .btn { flex: 1; font-size: 12px; }
        .sb-list { flex: 1; overflow-y: auto; padding: 6px; }

        /* ====== Vehicle card ====== */
        .veh-card {
            display: flex; gap: 6px; align-items: flex-start;
            padding: 6px 8px; border-radius: 7px;
            border: 1px solid #eef0f2; border-left: 4px solid #9ca3af;
            margin-bottom: 5px; cursor: pointer; transition: background .15s;
        }
        .veh-card:hover { background: #f8fafc; }
        .veh-card.veh-active { background: #eaf1fe; border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8 inset; }
        .veh-card.status-moving  { border-left-color: #16a34a; }
        .veh-card.status-idle    { border-left-color: #f59e0b; }
        .veh-card.status-offline { border-left-color: #dc2626; }
        .veh-cb { margin-top: 2px; cursor: pointer; }
        .veh-body { flex: 1; min-width: 0; line-height: 1.25; }
        .veh-plate  { font-weight: 700; font-size: 12.5px; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .veh-driver { font-size: 11.5px; color: #4b5563; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .veh-meta   { font-size: 11px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ====== Quick filter chips ====== */
        .sb-filters { display: flex; gap: 4px; margin-top: 8px; }
        .sb-filters .qf {
            flex: 1; font-size: 11px; padding: 3px 4px; border: 1px solid #d1d5db;
            background: #fff; border-radius: 6px; cursor: pointer; color: #374151; white-space: nowrap;
        }
        .sb-filters .qf:hover { background: #f3f4f6; }
        .sb-filters .qf.active { background: #1a73e8; border-color: #1a73e8; color: #fff; }

        /* ====== Map ====== */
        .track-mapwrap { flex: 1; position: relative; min-width: 0; }
        #map { width: 100%; height: 100%; min-height: 300px; z-index: 1; }
        .pick-active { cursor: crosshair !important; }

        /* ====== Legend ====== */
        #mapLegend {
            position: absolute; right: 10px; bottom: 26px; z-index: 500;
            background: rgba(255,255,255,.95); border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0,0,0,.25); padding: 8px 10px;
            font-size: 11px; line-height: 1.7; max-width: 180px;
        }
        #mapLegend .lg-title { font-weight: 700; margin-bottom: 3px; color: #374151; }
        #mapLegend .lg-sep { border-top: 1px solid #eee; margin: 5px 0; }

        /* ====== Setting panel (floating) ====== */
        #settingPanel { position: absolute; top: 10px; right: 10px; z-index: 600; width: 240px; background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.25); overflow: hidden; }
        .fp-header { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #0a9d54; color: #fff; cursor: pointer; user-select: none; font-size: 14px; font-weight: 600; }
        .fp-header i { font-size: 14px; }
        #settingPanel .fp-body { max-height: 60vh; overflow-y: auto; padding: 10px; }
        #settingPanel .sp-label { font-size: 12px; font-weight: 600; margin-bottom: 3px; display: block; }
        #settingPanel .form-control-sm { font-size: 12px; }
        #settingPanel .sp-hint { font-size: 11px; min-height: 14px; }

        /* ====== Replay bar ====== */
        .track-replaybar {
            display: none; align-items: center; gap: 12px; padding: 8px 14px;
            background: #fff; border-top: 1px solid #e5e7eb; flex-wrap: wrap;
            position: absolute; left: 0; right: 0; bottom: 0; z-index: 700;
            box-shadow: 0 -2px 10px rgba(0,0,0,.12);
        }
        .track-replaybar.show { display: flex; }
        .rb-controls { display: flex; align-items: center; gap: 6px; }
        .rb-mid { flex: 1; display: flex; align-items: center; gap: 8px; min-width: 220px; }
        .rb-mid input[type=range] { flex: 1; cursor: pointer; }
        .rb-time { font-size: 12px; color: #374151; font-variant-numeric: tabular-nums; }
        .replay-speed.active { background: #1a73e8; color: #fff; border-color: #1a73e8; }
        .rb-scope { font-size: 12px; color: #6b7280; white-space: nowrap; }

        /* ====== Marker ====== */
        .truck-marker { display: flex; flex-direction: column; align-items: center; }
        .truck-marker .icon { font-size: 26px; line-height: 1; }
        .truck-marker .plate {
            font-size: 9px; font-weight: 700; background: rgba(255,255,255,.88);
            border: 1px solid #ccc; border-radius: 3px; padding: 0 3px; margin-top: 1px;
            white-space: nowrap; max-width: 80px; overflow: hidden; text-overflow: ellipsis;
        }

        /* ====== Popup ====== */
        .truck-popup table { width: 100%; font-size: 13px; border-collapse: collapse; }
        .truck-popup td { padding: 3px 6px 3px 0; vertical-align: top; }
        .truck-popup td:first-child { color: #666; white-space: nowrap; }
        .truck-popup td:last-child { font-weight: 600; }
        .truck-popup .title { font-size: 14px; font-weight: 700; margin: 0 0 8px; padding-bottom: 6px; border-bottom: 1px solid #eee; }
        .truck-popup .pp-actions { display: flex; gap: 6px; margin-top: 8px; }
        .truck-popup .pp-actions .btn { font-size: 11px; padding: 2px 8px; flex: 1; }
        .speed-ok  { color: #28a745; }
        .speed-mid { color: #fd7e14; }
        .speed-hi  { color: #dc3545; }

        /* ====== Vehicle card action buttons ====== */
        .veh-act { display: flex; gap: 5px; margin-top: 5px; }
        .veh-act .btn {
            flex: 1; font-size: 10.5px; line-height: 1.4; padding: 2px 4px;
            border-radius: 5px; white-space: nowrap;
        }

        /* ====== Custom status marker ====== */
        .truck-marker .icon {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4);
            border: 2px solid #fff;
        }
        .truck-marker.m-moving  .icon { background: #16a34a; }
        .truck-marker.m-idle    .icon { background: #f59e0b; }
        .truck-marker.m-offline .icon { background: #dc2626; }

        /* ====== Vehicle detail panel ====== */
        #vehDetail {
            position: absolute; left: 10px; bottom: 26px; z-index: 550;
            width: 240px; background: #fff; border-radius: 10px;
            box-shadow: 0 2px 14px rgba(0,0,0,.28); overflow: hidden;
            font-size: 12px;
        }
        #vehDetail .vd-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 12px; background: #1a73e8; color: #fff;
        }
        #vehDetail .vd-title { font-weight: 700; font-size: 13px; }
        #vehDetail .vd-close { background: none; border: 0; color: #fff; font-size: 18px; line-height: 1; cursor: pointer; padding: 0 2px; }
        #vehDetail .vd-body { padding: 8px 12px; }
        #vehDetail .vd-body table { width: 100%; }
        #vehDetail .vd-body td { padding: 2px 4px 2px 0; vertical-align: top; }
        #vehDetail .vd-body td:first-child { color: #6b7280; white-space: nowrap; }
        #vehDetail .vd-body td:last-child { font-weight: 600; text-align: right; }
        #vehDetail .vd-actions { display: flex; gap: 5px; padding: 0 12px 10px; flex-wrap: wrap; }
        #vehDetail .vd-actions .btn { flex: 1 1 45%; font-size: 10.5px; padding: 3px 4px; }

        /* ====== Toast (status hint) ====== */
        #trackToast {
            position: absolute; left: 50%; top: 14px; transform: translateX(-50%);
            z-index: 800; background: rgba(31,41,55,.94); color: #fff;
            padding: 7px 14px; border-radius: 20px; font-size: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.3); opacity: 0;
            transition: opacity .25s; pointer-events: none; white-space: nowrap;
        }
        #trackToast.show { opacity: 1; }

        /* ====== Mode badge (LIVE / REPLAY) ====== */
        .tb-mode { font-size: 11px; font-weight: 700; border-radius: 20px; padding: 3px 10px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; }
        .tb-mode.mode-live   { background: #dcfce7; color: #16a34a; }
        .tb-mode.mode-live .dot { width: 7px; height: 7px; border-radius: 50%; background: #16a34a; animation: livepulse 1.5s infinite; }
        .tb-mode.mode-replay { background: #fef3c7; color: #b45309; }
        @keyframes livepulse { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }

        /* ====== Tombol menu (Panduan/Privasi) -- warna eksplisit, jangan
           andalkan .btn-outline-primary (variabel primary tema admin ini
           gelap/hitam, bukan biru Bootstrap standar) ====== */
        #tbMenuToggle,
        #tbMenuToggle:hover,
        #tbMenuToggle:focus,
        #tbMenuToggle:active,
        #tbMenuToggle.show {
            background: #fff !important;
            border-color: #1a73e8 !important;
            color: #1a73e8 !important;
            box-shadow: none !important;
        }
        #tbMenuList { padding: 6px; min-width: 190px; }
        #tbMenuList .dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: 6px; font-size: 13px; color: #374151;
        }
        #tbMenuList .dropdown-item i { width: 16px; flex: 0 0 16px; text-align: center; color: #1a73e8; }
        #tbMenuList .dropdown-item:hover,
        #tbMenuList .dropdown-item:focus {
            background: #f3f4f6 !important; color: #374151 !important;
        }

        /* ====== Alert widget ====== */
        #alertBox { position: absolute; right: 10px; top: 10px; z-index: 560; display: none; flex-direction: column; align-items: flex-end; gap: 6px; }
        #alertBox.show { display: flex; }
        .alert-btn { border: 0; border-radius: 20px; padding: 5px 12px; font-size: 12px; font-weight: 700; color: #fff; background: #dc2626; box-shadow: 0 2px 8px rgba(0,0,0,.25); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .alert-btn.warn { background: #f59e0b; }
        .alert-list { background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.28); padding: 6px; font-size: 11.5px; width: 230px; max-height: 220px; overflow-y: auto; }
        .alert-list .al-item { padding: 4px 6px; border-radius: 5px; display: flex; gap: 6px; align-items: flex-start; cursor: pointer; }
        .alert-list .al-item:hover { background: #f3f4f6; }
        .alert-list .al-item.off  i { color: #dc2626; }
        .alert-list .al-item.idle i { color: #f59e0b; }
        .alert-list .al-plate { font-weight: 700; }

        /* ====== Legend collapsible ====== */
        #mapLegend .lg-title { cursor: pointer; display: flex; align-items: center; gap: 5px; }
        #mapLegend .lg-title i { color: #1a73e8; }
        #mapLegend.collapsed { padding: 6px 10px; }
        #mapLegend.collapsed .lg-body { display: none; }

        /* ====== Responsive ====== */
        @media (max-width: 768px) {
            .track-sidebar { flex-basis: 210px; width: 210px; }
            .tb-title { font-size: 14px; }
            .tb-stats { gap: 6px; }
            .tb-stats .stat { padding: 2px 7px; }
            #settingPanel { width: 200px; }
        }
    @endverbatim
    </style>
@endsection

@section('content')
<div class="tracking-wrapper">

    {{-- ===== Top bar: judul + statistik + refresh ===== --}}
    <div class="track-topbar">
        <button class="btn btn-sm btn-light" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <div class="tb-title"><i class="fas fa-route"></i> Tiket Monitoring <small>Pelacakan Realtime</small></div>
        <span class="tb-mode mode-live" id="modeBadge"><span class="dot"></span> LIVE</span>
        <div class="tb-stats">
            <span class="stat">🎫 Tiket <b id="statTotal">0</b></span>
            <span class="stat">🟢 Bergerak <b id="statMoving">0</b></span>
            <span class="stat">🟡 Idle <b id="statIdle">0</b></span>
            <span class="stat">🔴 Offline <b id="statOffline">0</b></span>
        </div>
        <div class="tb-refresh">
            <span id="lastUpdated" title="Waktu data terakhir dimuat">Terakhir: —</span>
            <i class="fas fa-sync-alt" id="spinIcon"></i>
            <div class="refresh-prog" title="Refresh berikutnya"><div id="refreshProgBar"></div></div>
            <span id="countdown" title="Refresh berikutnya">00:30</span>
            <button class="btn btn-sm btn-link p-0" id="btnRefreshNow" title="Refresh sekarang" style="text-decoration:none;">
                <i class="fas fa-redo"></i>
            </button>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" id="tbMenuToggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="tbMenuList">
                <li><a class="dropdown-item" href="{{ route('panduan-tracking') }}" target="_blank"><i class="fas fa-book"></i>&nbsp;<span>Panduan Tracking</span></a></li>
                <li><a class="dropdown-item" href="{{ route('privacy') }}" target="_blank"><i class="fas fa-shield-alt"></i>&nbsp;<span>Kebijakan Privasi</span></a></li>
            </ul>
        </div>
    </div>

    {{-- ===== Main: sidebar kendaraan + peta ===== --}}
    <div class="track-main">
        {{-- Sidebar --}}
        <div class="track-sidebar" id="trackSidebar">
            <div class="sb-head">
                <div class="sb-title">Tiket Berjalan</div>
                <input type="text" class="form-control form-control-sm" id="searchTruck" placeholder="🔍 Cari no tiket / polisi / driver">
                <div class="sb-filters" id="sbFilters">
                    <button class="qf active" data-f="all">Semua</button>
                    <button class="qf" data-f="moving">🟢 Gerak</button>
                    <button class="qf" data-f="idle">🟡 Idle</button>
                    <button class="qf" data-f="offline">🔴 Off</button>
                </div>
                <div class="sb-actions">
                    <button class="btn btn-sm btn-outline-primary" id="btnSelectAll">Tampil Semua</button>
                    <button class="btn btn-sm btn-outline-secondary" id="btnClearAll">Sembunyikan</button>
                </div>
                <div class="sb-actions">
                    <button class="btn btn-sm btn-outline-secondary" id="btnFitVisible" title="Zoom peta ke semua kendaraan yang tampil"><i class="fas fa-expand"></i> Lihat Semua di Peta</button>
                </div>
            </div>
            <div class="sb-list" id="truckList">
                <div class="text-center text-muted py-3" id="truckLoading">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data...
                </div>
            </div>
        </div>

        {{-- Map --}}
        <div class="track-mapwrap">
            <div id="map"></div>

            {{-- Legend (bisa collapse) --}}
            <div id="mapLegend" class="collapsed">
                <div class="lg-title" id="legendToggle"><i class="fas fa-info-circle"></i> Keterangan</div>
                <div class="lg-body">
                    🟢 Bergerak<br>
                    🟡 Idle<br>
                    🔴 Offline
                    <div class="lg-sep"></div>
                    🔵 History GPS<br>
                    🟢 Rute Drive<br>
                    🏁 Tujuan &nbsp; 📍 Start
                </div>
            </div>

            {{-- Alert kondisi penting (offline / idle terlalu lama) --}}
            <div id="alertBox">
                <button type="button" class="alert-btn" id="alertToggle">
                    <i class="fas fa-exclamation-triangle"></i> <span id="alertCount">0</span> Peringatan
                </button>
                <div class="alert-list" id="alertList" style="display:none;"></div>
            </div>

            {{-- Detail panel kendaraan (muncul saat kendaraan dipilih) --}}
            <div id="vehDetail" style="display:none;">
                <div class="vd-head">
                    <span class="vd-title" id="vdTitle">—</span>
                    <button type="button" class="vd-close" id="vdClose" title="Tutup">&times;</button>
                </div>
                <div class="vd-body" id="vdBody"></div>
                <div class="vd-actions">
                    <button type="button" class="btn btn-primary" id="vdFocus"><i class="fas fa-crosshairs"></i> Focus</button>
                    <button type="button" class="btn btn-secondary" id="vdHist"><i class="fas fa-history"></i> Riwayat</button>
                    <button type="button" class="btn btn-info" id="vdReplay"><i class="fas fa-play"></i> Replay</button>
                    <a class="btn btn-success" id="vdGmaps" target="_blank" rel="noopener"><i class="fas fa-map-marked-alt"></i> Maps</a>
                </div>
            </div>

            {{-- Toast status (pengganti spHint yang sudah dipindah) --}}
            <div id="trackToast"></div>

            {{-- Banner ajakan install PWA. Disembunyikan sampai browser mengirim
                 event beforeinstallprompt (Chrome/Edge/Android), atau sampai
                 terdeteksi iOS Safari yang tidak punya event itu. --}}
            <div id="pwaInstall">
                <img class="pwa-icon" src="{{ asset('media/img/pwa/icon-192x192.png') }}" alt="">
                <div class="pwa-txt">
                    <div class="pwa-title">Pasang WGI Tracking</div>
                    <div class="pwa-sub" id="pwaSub">Buka langsung dari layar utama, tanpa browser.</div>
                </div>
                <div class="pwa-act">
                    <button type="button" class="btn btn-sm btn-primary" id="pwaInstallBtn">Pasang</button>
                    <button type="button" class="btn btn-sm btn-light" id="pwaDismissBtn">Nanti</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Replay bar ===== --}}
    <div class="track-replaybar">
        <div class="rb-controls">
            <button class="btn btn-sm btn-success" id="rbPlay" title="Play"><i class="fas fa-play"></i></button>
            <button class="btn btn-sm btn-secondary" id="rbPause" title="Pause" disabled><i class="fas fa-pause"></i></button>
            <button class="btn btn-sm btn-danger" id="rbStop" title="Stop" disabled><i class="fas fa-stop"></i></button>
            <div class="btn-group btn-group-sm ml-1" role="group">
                <button class="btn btn-outline-secondary replay-speed active" data-mul="1">1x</button>
                <button class="btn btn-outline-secondary replay-speed" data-mul="2">2x</button>
                <button class="btn btn-outline-secondary replay-speed" data-mul="5">5x</button>
            </div>
        </div>
        <div class="rb-mid">
            <span class="rb-time" id="replayElapsed">00:00</span>
            <input type="range" id="replayProgress" min="0" max="1000" value="0">
            <span class="rb-time" id="replayTotal">00:00</span>
        </div>
        <div class="rb-scope" id="rbScope">Replay semua truk tampil</div>
    </div>

    {{-- Modal: pilih arsip perjalanan dulu sebelum replay --}}
    <div class="modal fade" id="replayPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="fas fa-history mr-1"></i> Replay Perjalanan</h6>
                    <button type="button" class="btn-close rp-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="small mb-1">Kendaraan</label>
                        <input type="text" class="form-control form-control-sm" id="replayVehicle" readonly>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small mb-1">Tanggal</label>
                        <input type="date" class="form-control form-control-sm" id="replayDate">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small mb-1">Periode</label>
                        <select class="form-control form-control-sm" id="replayPeriod">
                            <option value="all">Semua hari</option>
                            <option value="morning">Pagi (05:00–12:00)</option>
                            <option value="afternoon">Siang (12:00–18:00)</option>
                        </select>
                    </div>
                    <div class="small text-muted mt-2" id="replayPickerHint"></div>
                </div>
                <div class="modal-footer py-2">
                    <button class="btn btn-sm btn-secondary rp-close" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-sm btn-primary" id="btnStartReplayByDate"><i class="fas fa-play"></i> Mulai Replay</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer_js')
<script src="{{ asset('media/plugins/leafletjs/leaflet.js') }}"></script>
<script>
(function () {
    'use strict';

    var REFRESH_SEC   = 10;  // auto-refresh live tracking (detik) — biar terasa "live" ala Gojek
    var API_URL       = AppHelper.baseUrl + '/tracking/trucks';   // posisi terakhir per tiket yang sedang berjalan
    var ROUTE_URL     = AppHelper.baseUrl + '/tracking/route';    // riwayat lokasi per tiket (no_tiket) + tanggal
    var MAPBOX_TOKEN  = 'pk.eyJ1IjoiaXJmYW5yb3NqaWRpIiwiYSI6ImNrNjc0bjU2bTE2YjYzcnA5YnpwOTM5dnEifQ.ZzyiAbCzUk_MbkmUi-ICRQ';
    var DIR_URL       = 'https://api.mapbox.com/directions/v5/mapbox/driving/';   // routing ikut jalan
    var MATCH_URL     = 'https://api.mapbox.com/matching/v5/mapbox/driving/';     // snap jejak GPS ke jalan

    // Config dari server (tabel setting)
    var CFG = @json($config);
    var CENTER       = [parseFloat(CFG.center.lat), parseFloat(CFG.center.lng)];
    // Fallback END (titik tujuan) bila data GPS truk kosong
    var destination  = (CFG.dest && CFG.dest.lat !== null && CFG.dest.lng !== null)
        ? { lat: parseFloat(CFG.dest.lat), lng: parseFloat(CFG.dest.lng), label: CFG.dest.label || 'Tujuan' }
        : null;
    // Fallback START bila data GPS truk kosong
    var settingStart = (CFG.start_setting && CFG.start_setting.lat !== null && CFG.start_setting.lng !== null)
        ? { lat: parseFloat(CFG.start_setting.lat), lng: parseFloat(CFG.start_setting.lng), label: CFG.start_setting.label || 'Start' }
        : null;
    var ZOOM_DEFAULT  = 13;

    // --- Map init ---
    var map = L.map('map', { zoomControl: true }).setView(CENTER, ZOOM_DEFAULT);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // --- Fullscreen control (native browser API) ---
    L.Control.FullscreenBtn = L.Control.extend({
        onAdd: function (m) {
            var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            var a = L.DomUtil.create('a', '', container);
            a.href  = '#';
            a.title = 'Fullscreen';
            a.style.cssText = 'font-size:16px;line-height:26px;display:block;width:26px;text-align:center;text-decoration:none;color:#333;';
            a.innerHTML = '⛶';

            L.DomEvent.on(a, 'click', function (e) {
                L.DomEvent.preventDefault(e);
                var el = document.querySelector('.tracking-wrapper');
                if (!document.fullscreenElement) {
                    (el.requestFullscreen || el.webkitRequestFullscreen).call(el);
                } else {
                    (document.exitFullscreen || document.webkitExitFullscreen).call(document);
                }
            });

            document.addEventListener('fullscreenchange',       function () { syncIcon(a, m); });
            document.addEventListener('webkitfullscreenchange', function () { syncIcon(a, m); });
            return container;
        }
    });

    function syncIcon(a, m) {
        a.innerHTML = document.fullscreenElement ? '✕' : '⛶';
        setTimeout(function () { m.invalidateSize(); }, 200);
    }

    new L.Control.FullscreenBtn({ position: 'topleft' }).addTo(map);

    // --- Marker titik WGI (base) ---
    var wgiIcon = L.divIcon({
        html: '<div style="display:flex;flex-direction:column;align-items:center;">' +
                  '<div style="font-size:30px;line-height:1;">📍</div>' +
                  '<div style="font-size:10px;font-weight:700;background:#1a73e8;color:#fff;padding:1px 6px;border-radius:3px;white-space:nowrap;margin-top:1px;">WGI</div>' +
              '</div>',
        iconSize:    [60, 48],
        iconAnchor:  [30, 48],
        popupAnchor: [0, -48],
        className: ''
    });
    L.marker(CENTER, { icon: wgiIcon, zIndexOffset: 1000 })
        .bindPopup('<b>PT. WGI</b><br>Pemalang, Jawa Tengah')
        .addTo(map);

    // state — key = no_tiket (satu marker = satu tiket yang sedang berjalan, bukan per truk)
    var markers     = {};   // no_tiket → Leaflet marker (posisi terakhir)
    var trucksData  = {};   // no_tiket → truck/ticket object
    var visible     = {};   // no_tiket → boolean
    var routeCache  = {};   // no_tiket → [[lat,lng],...] riwayat (cache utk replay)
    var hiddenForReplay = {}; // no_tiket → true, marker yg disembunyikan sementara selama replay (dipulihkan setelah selesai)
    var selectedPol = null; // no_tiket yang sedang ditampilkan rutenya
    var searchQuery = '';   // filter pencarian sidebar
    var statusFilter = 'all'; // all | moving | idle | offline (chip filter)
    var routeLayer  = null; // L.LayerGroup berisi polyline + titik rute (riwayat, biru)
    var destRoute   = null; // polyline rute drive start→end (hijau)
    var startMarker = null; // marker titik start rute drive
    var endMarker   = null; // marker titik end/tujuan rute drive
    var countdown   = REFRESH_SEC;
    var countdownTimer;

    // ====== Status & ambang ======
    var MOVING_KMH = 5;                // > ini = bergerak
    var OFFLINE_MS = 5 * 60 * 1000;   // tak ada update > 5 menit = offline
    var IDLE_ALERT_MS = 5 * 60 * 1000; // idle terus-menerus > 5 menit = peringatan
    var idleSince  = {};                // no_tiket → timestamp (ms) sejak mulai idle (sesi ini)

    function parseUpdate(s) {
        if (!s) return null;
        var d = new Date(String(s).replace(' ', 'T'));
        return isNaN(d.getTime()) ? null : d;
    }
    function truckStatus(t) {
        var d = parseUpdate(t.last_update);
        if (d && (Date.now() - d.getTime()) > OFFLINE_MS) return 'offline';
        return (parseFloat(t.speed) || 0) > MOVING_KMH ? 'moving' : 'idle';
    }
    function fmtAgo(ms) {
        var s = Math.round(ms / 1000);
        if (s < 60)    return 'baru saja';
        var m = Math.round(s / 60);
        if (m < 60)    return m + ' menit';
        var h = Math.round(m / 60);
        return h + ' jam';
    }
    function fmtClock(s) {
        if (!s) return '-';
        var p = String(s).split(' ');
        return p.length > 1 ? p[1] : s;
    }
    function nowClock() {
        var d = new Date(), z = function (n) { return n < 10 ? '0' + n : n; };
        return z(d.getHours()) + ':' + z(d.getMinutes()) + ':' + z(d.getSeconds());
    }
    // ====== Estimasi jarak/waktu tempuh & tiba (ETA) ke pabrik WGI ======
    // Sudah dihitung & di-cache di server (lihat App\Services\TrackingEtaService,
    // dipakai bareng oleh /tracking/trucks & /api/tracking) -- di sini tinggal
    // ditampilkan dari field truck.estimated_distance (km, garis lurus),
    // truck.estimated_time ("42 menit"/"2 jam" -- ikut jalan lewat Mapbox) &
    // truck.estimated_time_arrived ("Y-m-d H:i:s"), tidak ada lagi panggilan
    // Mapbox dari browser tiap popup truk dibuka.
    function etaDistanceText(t) {
        return (t.estimated_distance === undefined || t.estimated_distance === null) ? '-' : (t.estimated_distance + ' km');
    }
    function etaDurationText(t) {
        return t.estimated_time || '-';
    }
    function etaArrivedText(t) {
        if (!t.estimated_time_arrived) return '-';
        var clock = fmtClock(t.estimated_time_arrived); // ambil bagian jam dari "Y-m-d H:i:s"
        return clock.length >= 5 ? clock.substring(0, 5) : clock;
    }
    function etaRowsHtml(t) {
        return '<tr><td>📏 Jarak (lurus)</td><td>' + escHtml(etaDistanceText(t)) + '</td></tr>' +
               '<tr><td>⏱ Estimasi Tempuh</td><td>' + escHtml(etaDurationText(t)) + '</td></tr>' +
               '<tr><td>🏁 Estimasi Tiba</td><td>' + escHtml(etaArrivedText(t)) + '</td></tr>';
    }

    // ====== Live glide (animasi halus marker ala Gojek) ======
    var LIVE_GLIDE_MS = 1500;
    function glideMarker(marker, to) {
        if (marker._glideRaf) { cancelAnimationFrame(marker._glideRaf); marker._glideRaf = null; }
        var from = marker.getLatLng();
        var f = [from.lat, from.lng];
        if (Math.abs(f[0] - to[0]) < 1e-7 && Math.abs(f[1] - to[1]) < 1e-7) { marker.setLatLng(to); return; }
        var start = performance.now();
        function frame(now) {
            var p = Math.min((now - start) / LIVE_GLIDE_MS, 1);
            var e = p < 0.5 ? 2 * p * p : -1 + (4 - 2 * p) * p; // easeInOutQuad
            marker.setLatLng([ f[0] + (to[0] - f[0]) * e, f[1] + (to[1] - f[1]) * e ]);
            if (p < 1) marker._glideRaf = requestAnimationFrame(frame);
            else marker._glideRaf = null;
        }
        marker._glideRaf = requestAnimationFrame(frame);
    }

    // ====== Playback / replay ======
    var SEG_MS   = 500; // durasi animasi antar titik (ms) @1x
    var playback = { active: false, paused: false, raf: null, items: [], last: 0, speed: 1, maxLen: 2, dateLabel: null };

    // --- Icon factory (warna background ikut status: hijau/kuning/merah) ---
    function truckIcon(noPol, status) {
        var st = status || 'idle';
        return L.divIcon({
            html: '<div class="truck-marker m-' + st + '">' +
                      '<div class="icon"><i class="fas fa-truck"></i></div>' +
                      '<div class="plate">' + escHtml(noPol) + '</div>' +
                  '</div>',
            iconSize:   [70, 46],
            iconAnchor: [35, 46],
            popupAnchor:[0, -46],
            className: ''
        });
    }

    // --- Popup factory ---
    function speedClass(v) {
        if (v <= 60) return 'speed-ok';
        if (v <= 80) return 'speed-mid';
        return 'speed-hi';
    }
    function buildPopup(t, actions) {
        var spd = Math.round((parseFloat(t.speed) || 0) * 10) / 10;
        var driverPhone = t.driver_phone || t.driver_whatsapp || '-';
        var key = String(t.no_tiket || '');
        var html = '<div class="truck-popup">' +
            '<div class="title">🎫 ' + escHtml(t.no_tiket || '-') + '</div>' +
            '<table>' +
              '<tr><td>🚛 No Pol</td><td>' + escHtml(t.no_pol || '-') + '</td></tr>' +
              '<tr><td>👤 Driver</td><td>' + escHtml(t.driver || '-') + '</td></tr>' +
              '<tr><td>📱 No HP Supir</td><td>' + escHtml(driverPhone) + '</td></tr>' +
              '<tr><td>🌾 Petani</td><td>' + escHtml(t.petani_name || '-') + '</td></tr>' +
              '<tr><td>🌳 Kode Kebun</td><td>' + escHtml(t.kodekebun || '-') + '</td></tr>' +
              '<tr><td>📐 Luas</td><td>' + escHtml(t.luas_ha || '-') + ' HA</td></tr>' +
              '<tr><td>⚡ Speed</td><td class="' + speedClass(spd) + '">' + spd + ' km/h</td></tr>' +
              '<tr><td>📍 Lat</td><td>' + escHtml(t.latitude || '-') + '</td></tr>' +
              '<tr><td>📍 Lng</td><td>' + escHtml(t.longitude || '-') + '</td></tr>' +
              '<tr><td>🕒 Update</td><td style="font-size:11px;color:#999;">' + escHtml(t.last_update || '-') + '</td></tr>' +
              (actions ? etaRowsHtml(t) : '') +
            '</table>';
        if (actions) {
            var k = escHtml(key);
            html += '<div class="pp-actions">' +
                '<button class="btn btn-outline-primary pp-hist" data-key="' + k + '">Riwayat</button>' +
                '<button class="btn btn-info pp-replay" data-key="' + k + '">Replay</button>' +
            '</div>';
        }
        html += '</div>';
        return html;
    }

    // Wiring tombol di dalam popup truk
    map.on('popupopen', function (e) {
        var node = e.popup.getElement();
        if (!node) return;
        var h = node.querySelector('.pp-hist');
        var r = node.querySelector('.pp-replay');
        if (h) h.onclick = function () { focusTruck(this.getAttribute('data-key')); };
        if (r) r.onclick = function () { var k = this.getAttribute('data-key'); map.closePopup(); openReplayPicker(k); };
    });

    // ====== Load locations ======
    function loadLocations() {
        setSpinner(true);
        $.ajax({
            url: API_URL, type: 'GET', dataType: 'json',
            success: function (res) {
                setSpinner(false);
                processData((res && res.data) ? res.data : []);
            },
            error: function () {
                setSpinner(false);
                console.warn('Tracking: gagal memuat data lokasi.');
            }
        });
    }

    function processData(list) {
        var newKeys = {};
        list.forEach(function (t) {
            var key = String(t.no_tiket || '');
            if (key === '') return;
            var lat = parseFloat(t.latitude), lng = parseFloat(t.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            newKeys[key] = true;
            trucksData[key] = t;
            if (visible[key] === undefined) visible[key] = true;
            var st = truckStatus(t);

            var replaying = isReplayingKey(key);
            if (markers[key]) {
                if (playback.active) { if (!replaying) markers[key].setLatLng([lat, lng]); } // jangan ganggu replay
                else                   glideMarker(markers[key], [lat, lng]);                  // meluncur halus
                // Selama key ini sedang direplay, biarkan applyReplayPoint() yang mengatur
                // ikon/popup (data historis per titik) — jangan ditimpa data live di sini.
                if (!replaying) {
                    if (markers[key]._st !== st) { markers[key].setIcon(truckIcon(t.no_pol, st)); markers[key]._st = st; }
                    markers[key].setPopupContent(buildPopup(t, true));
                }
            } else {
                var m = L.marker([lat, lng], { icon: truckIcon(t.no_pol, st) }).bindPopup(buildPopup(t, true));
                m._st = st;
                if (visible[key]) m.addTo(map);
                markers[key] = m;
            }

            if (selectedPol === key && !replaying) showVehDetail(key); // panel detail tetap sinkron saat refresh (skip saat replay)
        });

        // hapus truk yang tidak ada lagi
        Object.keys(markers).forEach(function (key) {
            if (!newKeys[key]) {
                map.removeLayer(markers[key]);
                delete markers[key]; delete trucksData[key]; delete visible[key]; delete routeCache[key];
            }
        });

        renderTruckList();
        updateStats();
        setText('lastUpdated', 'Terakhir: ' + nowClock());

        if (selectedPol && trucksData[selectedPol]) loadRoute(selectedPol, false); // refresh rute aktif
    }

    function updateStats() {
        var total = 0, moving = 0, idle = 0, offline = 0;
        Object.keys(trucksData).forEach(function (key) {
            total++;
            var s = truckStatus(trucksData[key]);
            if (s === 'offline') offline++; else if (s === 'moving') moving++; else idle++;
        });
        setText('statTotal', total); setText('statMoving', moving);
        setText('statIdle', idle);   setText('statOffline', offline);
        computeAlerts();
    }

    // ====== Peringatan: offline / idle terlalu lama ======
    function computeAlerts() {
        var now = Date.now();
        var alerts = [];
        Object.keys(trucksData).forEach(function (key) {
            var t = trucksData[key];
            var st = truckStatus(t);

            // lacak sejak kapan truk mulai idle (reset saat bergerak/offline)
            if (st === 'idle') { if (!idleSince[key]) idleSince[key] = now; }
            else               { delete idleSince[key]; }

            if (st === 'offline') {
                var d = parseUpdate(t.last_update);
                var since = d ? fmtAgo(now - d.getTime()) : 'lama';
                alerts.push({ key: key, type: 'off', plate: t.no_tiket || key, msg: 'offline ' + since });
            } else if (st === 'idle' && (now - idleSince[key]) > IDLE_ALERT_MS) {
                alerts.push({ key: key, type: 'idle', plate: t.no_tiket || key, msg: 'idle ' + fmtAgo(now - idleSince[key]) });
            }
        });
        // bersihkan idleSince untuk truk yang sudah hilang
        Object.keys(idleSince).forEach(function (k) { if (!trucksData[k]) delete idleSince[k]; });
        renderAlerts(alerts);
    }

    function renderAlerts(alerts) {
        var box = document.getElementById('alertBox');
        var btn = document.getElementById('alertToggle');
        var cnt = document.getElementById('alertCount');
        var listEl = document.getElementById('alertList');
        if (!box || !btn || !cnt || !listEl) return;

        if (!alerts.length) { box.classList.remove('show'); listEl.style.display = 'none'; return; }

        box.classList.add('show');
        cnt.textContent = alerts.length;
        var hasOffline = alerts.some(function (a) { return a.type === 'off'; });
        btn.classList.toggle('warn', !hasOffline); // merah bila ada offline, kuning bila hanya idle

        listEl.innerHTML = '';
        alerts.forEach(function (a) {
            var row = document.createElement('div');
            row.className = 'al-item ' + a.type;
            row.innerHTML = '<i class="fas ' + (a.type === 'off' ? 'fa-plug' : 'fa-pause-circle') + '"></i>' +
                '<div><span class="al-plate">' + escHtml(a.plate) + '</span> ' + escHtml(a.msg) + '</div>';
            row.addEventListener('click', function () { focusTruck(a.key); });
            listEl.appendChild(row);
        });
    }

    // ====== Render sidebar ======
    function renderTruckList() {
        var list = document.getElementById('truckList');
        var loading = document.getElementById('truckLoading');
        if (loading) loading.remove();

        var keys = Object.keys(trucksData);
        if (playback.active) {
            // Fokus ke tiket yg sedang direplay — tiket lain disembunyikan sementara.
            var replayKeys = playback.items.map(function (it) { return it.key; });
            keys = keys.filter(function (k) { return replayKeys.indexOf(k) !== -1; });
        }
        if (keys.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-3">' +
                (playback.active ? 'Sedang replay…' : 'Tidak ada tiket aktif') + '</div>';
            updateReplayScope();
            return;
        }

        var q = searchQuery;
        list.innerHTML = '';
        var shown = 0;
        keys.forEach(function (key) {
            var t = trucksData[key];
            var st = truckStatus(t);
            if (statusFilter !== 'all' && st !== statusFilter) return;       // chip filter
            var hay = ((t.no_tiket || '') + ' ' + (t.no_pol || '') + ' ' + (t.driver || '')).toLowerCase();
            if (q && hay.indexOf(q) === -1) return;                          // pencarian
            shown++;

            var dot = st === 'moving' ? '🟢' : (st === 'idle' ? '🟡' : '🔴');
            var spd = Math.round((parseFloat(t.speed) || 0) * 10) / 10;
            var d   = parseUpdate(t.last_update);
            var ago = d ? (fmtAgo(Date.now() - d.getTime()) + ' lalu') : 'tidak diketahui';
            var meta;
            if (st === 'offline')   meta = '🔴 Offline · ' + ago;
            else if (st === 'idle') meta = '⚡ Idle · ' + ago;
            else                    meta = '⚡ ' + spd + ' km/h · ' + ago;

            var safeId = key.replace(/[^a-zA-Z0-9]/g, '_');
            var item = document.createElement('div');
            item.className = 'veh-card status-' + st + (selectedPol === key ? ' veh-active' : '');
            item.dataset.key = key;
            item.title = 'Update: ' + (t.last_update || '-');
            item.innerHTML =
                '<input type="checkbox" class="veh-cb" id="cb_' + safeId + '" ' + (visible[key] ? 'checked' : '') + '>' +
                '<div class="veh-body">' +
                    '<div class="veh-plate">' + dot + ' 🎫 ' + escHtml(t.no_tiket || key) + '</div>' +
                    '<div class="veh-driver">' + escHtml(t.no_pol || '-') + ' · ' + escHtml(t.driver || '-') + '</div>' +
                    '<div class="veh-meta">' + escHtml(meta) + '</div>' +
                    '<div class="veh-act">' +
                        '<button type="button" class="btn btn-outline-secondary vc-hist"><i class="fas fa-history"></i> Riwayat</button>' +
                        '<button type="button" class="btn btn-info vc-replay"><i class="fas fa-play"></i> Replay</button>' +
                    '</div>' +
                '</div>';

            // checkbox → toggle marker (jangan trigger focus)
            var cb = item.querySelector('.veh-cb');
            cb.addEventListener('click', function (ev) { ev.stopPropagation(); });
            cb.addEventListener('change', function () { toggleMarker(key, this.checked); });
            // tombol Riwayat / Replay langsung di kartu (jangan ikut trigger klik kartu)
            var bHist = item.querySelector('.vc-hist');
            var bRep  = item.querySelector('.vc-replay');
            bHist.addEventListener('click', function (ev) { ev.stopPropagation(); focusTruck(key); });
            bRep.addEventListener('click', function (ev) { ev.stopPropagation(); openReplayPicker(key); });
            // klik kartu → fokus (flyTo + popup + rute)
            item.addEventListener('click', function () { focusTruck(key); });

            list.appendChild(item);
        });

        if (shown === 0) {
            var why = q ? ('untuk "' + escHtml(q) + '"') : 'pada filter ini';
            list.innerHTML = '<div class="text-center text-muted py-3">Tidak ada kendaraan ' + why + '</div>';
        }
        updateReplayScope();
    }

    function toggleMarker(key, show) {
        visible[key] = show;
        if (!markers[key]) return;
        if (show) markers[key].addTo(map); else map.removeLayer(markers[key]);
    }

    // Klik kendaraan → fokus & tampilkan rute (auto focus)
    function focusTruck(key) {
        var t = trucksData[key];
        if (!t) return;
        if (selectedPol === key) { clearRoute(); return; } // klik lagi = lepas pilihan

        var lat = parseFloat(t.latitude), lng = parseFloat(t.longitude);
        visible[key] = true;
        loadRoute(key, false); // gambar rute + set selectedPol + re-render (highlight)
        showVehDetail(key);    // tampilkan panel detail kendaraan
        if (!isNaN(lat) && !isNaN(lng)) {
            if (markers[key]) markers[key].addTo(map);
            map.flyTo([lat, lng], 16, { animate: true });
            // Popup di peta sengaja TIDAK auto-terbuka di sini — biar tidak dobel
            // dengan panel detail sidebar. Popup baru terbuka kalau markernya
            // sendiri yang diklik langsung di peta.
        }
    }

    // ====== Route (riwayat lokasi satu truk) ======
    function loadRoute(key, fit, replayAfter) {
        selectedPol = key;
        $.ajax({
            url: ROUTE_URL, type: 'GET', dataType: 'json', data: { no_tiket: key },
            success: function (res) {
                var pts = (res && res.data) ? res.data : [];
                drawRoute(pts, fit);
                drawDriveRoute(pts);

                var rows = pts.filter(function (p) { return pointLL(p) !== null; });
                routeCache[key] = rows;

                renderTruckList();

                if (replayAfter) {
                    if (rows.length >= 2 && markers[key]) buildAndRun([{ key: key, rows: rows }]);
                    else setHint('Tidak ada riwayat lokasi untuk di-replay.');
                }
            },
            error: function () { console.warn('Tracking: gagal memuat rute ' + key); }
        });
    }

    function pointLL(p) {
        var a = parseFloat(p.latitude), b = parseFloat(p.longitude);
        return (isNaN(a) || isNaN(b)) ? null : [a, b];
    }
    function driveStartLL(pts) {
        for (var i = 0; i < pts.length; i++) { var ll = pointLL(pts[i]); if (ll) return ll; }
        return settingStart ? [settingStart.lat, settingStart.lng] : null;
    }
    function driveEndLL(pts) {
        for (var i = pts.length - 1; i >= 0; i--) { var ll = pointLL(pts[i]); if (ll) return ll; }
        return destination ? [destination.lat, destination.lng] : null;
    }

    function drawDriveRoute(pts) {
        if (destRoute)   { map.removeLayer(destRoute);   destRoute   = null; }
        if (startMarker) { map.removeLayer(startMarker); startMarker = null; }
        if (endMarker)   { map.removeLayer(endMarker);   endMarker   = null; }

        var startLL = driveStartLL(pts), endLL = driveEndLL(pts);
        if (!startLL || !endLL) return;

        var endLabel = pts.length ? 'Titik Akhir' : (destination ? destination.label : 'Tujuan');

        startMarker = L.circleMarker(startLL, { radius: 7, color: '#0a9d54', fillColor: '#0a9d54', fillOpacity: 1, weight: 2 })
            .bindPopup('<b>Start</b>').addTo(map);
        endMarker = L.marker(endLL, { icon: destIcon, zIndexOffset: 900 })
            .bindPopup('<b>🏁 ' + escHtml(endLabel) + '</b>').addTo(map);
        var plate = endMarker.getElement() && endMarker.getElement().querySelector('#destPlate');
        if (plate) plate.textContent = endLabel;

        var url = DIR_URL + startLL[1] + ',' + startLL[0] + ';' + endLL[1] + ',' + endLL[0] +
                  '?overview=full&geometries=geojson&access_token=' + MAPBOX_TOKEN;

        var thisLayerSel = selectedPol;
        fetch(url).then(function (r) { return r.json(); }).then(function (json) {
            if (selectedPol !== thisLayerSel) return;
            if (!json.routes || !json.routes.length) return;
            var coords = json.routes[0].geometry.coordinates.map(function (c) { return [c[1], c[0]]; });
            coords.unshift(startLL); coords.push(endLL);
            if (destRoute) map.removeLayer(destRoute);
            destRoute = L.polyline(coords, { color: '#0a9d54', weight: 5, opacity: 0.85 }).addTo(map);
            var dist = (json.routes[0].distance / 1000).toFixed(1);
            var dur  = Math.round(json.routes[0].duration / 60);
            destRoute.bindPopup('Rute drive<br>' + dist + ' km · ± ' + dur + ' mnt');
        }).catch(function () { console.warn('Tracking: gagal rute drive.'); });
    }

    function drawRoute(pts, fit) {
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }

        var latlngs = [];
        pts.forEach(function (p) {
            var lat = parseFloat(p.latitude), lng = parseFloat(p.longitude);
            if (!isNaN(lat) && !isNaN(lng)) latlngs.push([lat, lng]);
        });
        if (latlngs.length === 0) return;

        routeLayer = L.layerGroup();
        var histLine = L.polyline(latlngs, { color: '#1a73e8', weight: 4, opacity: 0.45, dashArray: '6' }).addTo(routeLayer);

        pts.forEach(function (p) {
            var lat = parseFloat(p.latitude), lng = parseFloat(p.longitude);
            if (isNaN(lat) || isNaN(lng)) return;
            L.circleMarker([lat, lng], { radius: 3, color: '#1a73e8', fillColor: '#fff', fillOpacity: 1, weight: 2 })
                .bindPopup(buildPopup(p, false)).addTo(routeLayer);
        });

        var first = latlngs[0], last = latlngs[latlngs.length - 1];
        L.marker(first).bindPopup('<b>Mulai</b><br>' + escHtml(pts[0].last_update || '')).addTo(routeLayer);
        L.marker(last).bindPopup('<b>Terakhir</b><br>' + escHtml(pts[pts.length - 1].last_update || '')).addTo(routeLayer);

        routeLayer.addTo(map);
        if (fit) map.fitBounds(L.latLngBounds(latlngs).pad(0.2));

        var thisLayer = routeLayer;
        matchToRoad(latlngs, function (roadLatLngs) {
            if (routeLayer !== thisLayer) return;
            if (roadLatLngs && roadLatLngs.length > 1) {
                routeLayer.removeLayer(histLine);
                L.polyline(roadLatLngs, { color: '#1a73e8', weight: 4, opacity: 0.85 }).addTo(routeLayer);
            } else {
                histLine.setStyle({ opacity: 0.85, dashArray: null });
            }
        });
    }

    function distMeters(a, b) {
        var R = 6371000, toRad = Math.PI / 180;
        var dLat = (b[0] - a[0]) * toRad, dLng = (b[1] - a[1]) * toRad;
        var lat1 = a[0] * toRad, lat2 = b[0] * toRad;
        var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return 2 * R * Math.asin(Math.sqrt(x));
    }
    function dedupPoints(arr, minMeters) {
        if (arr.length < 2) return arr.slice();
        var out = [arr[0]];
        for (var i = 1; i < arr.length; i++) if (distMeters(out[out.length - 1], arr[i]) >= minMeters) out.push(arr[i]);
        if (out[out.length - 1] !== arr[arr.length - 1]) out.push(arr[arr.length - 1]);
        return out;
    }
    function downsample(arr, max) {
        if (arr.length <= max) return arr.slice();
        var out = [], step = (arr.length - 1) / (max - 1);
        for (var i = 0; i < max; i++) out.push(arr[Math.round(i * step)]);
        return out;
    }
    function geomToLatLngs(geometry) {
        if (!geometry || !geometry.coordinates) return [];
        return geometry.coordinates.map(function (c) { return [c[1], c[0]]; });
    }
    function matchToRoad(latlngs, cb) {
        var clean = dedupPoints(latlngs, 12);
        if (clean.length < 2) { cb(null); return; }
        var pts = downsample(clean, 100);
        var coordStr = pts.map(function (p) { return p[1] + ',' + p[0]; }).join(';');
        var radiuses = pts.map(function () { return 25; }).join(';');
        var url = MATCH_URL + coordStr + '?geometries=geojson&overview=full&tidy=true&radiuses=' + radiuses + '&access_token=' + MAPBOX_TOKEN;
        fetch(url).then(function (r) { return r.json(); }).then(function (json) {
            if (json.code === 'Ok' && json.matchings && json.matchings.length) {
                var out = [];
                json.matchings.forEach(function (m) { out = out.concat(geomToLatLngs(m.geometry)); });
                if (out.length > 1) { cb(out); return; }
            }
            directionsThrough(clean, cb);
        }).catch(function () { directionsThrough(clean, cb); });
    }
    function directionsThrough(latlngs, cb) {
        var pts = downsample(latlngs, 25);
        if (pts.length < 2) { cb(null); return; }
        var coordStr = pts.map(function (p) { return p[1] + ',' + p[0]; }).join(';');
        var url = DIR_URL + coordStr + '?geometries=geojson&overview=full&access_token=' + MAPBOX_TOKEN;
        fetch(url).then(function (r) { return r.json(); }).then(function (json) {
            if (json.code === 'Ok' && json.routes && json.routes.length) cb(geomToLatLngs(json.routes[0].geometry));
            else cb(null);
        }).catch(function () { cb(null); });
    }

    function clearRoute() {
        selectedPol = null;
        if (routeLayer)  { map.removeLayer(routeLayer);  routeLayer  = null; }
        if (destRoute)   { map.removeLayer(destRoute);   destRoute   = null; }
        if (startMarker) { map.removeLayer(startMarker); startMarker = null; }
        if (endMarker)   { map.removeLayer(endMarker);   endMarker   = null; }
        hideVehDetail();
        renderTruckList();
    }

    var destIcon = L.divIcon({
        html: '<div style="display:flex;flex-direction:column;align-items:center;">' +
                  '<div style="font-size:28px;line-height:1;">🏁</div>' +
                  '<div style="font-size:10px;font-weight:700;background:#0a9d54;color:#fff;padding:1px 6px;border-radius:3px;white-space:nowrap;margin-top:1px;" id="destPlate"></div>' +
              '</div>',
        iconSize:   [60, 46],
        iconAnchor: [30, 46],
        popupAnchor:[0, -46],
        className: ''
    });

    // ====== Status hint → toast kecil (elemen spHint lama sudah dipindah ke halaman Setting) ======
    var toastTimer = null;
    function setHint(msg) {
        var el = document.getElementById('trackToast');
        if (!el) return;
        if (!msg) { el.classList.remove('show'); return; }
        el.textContent = msg;
        el.classList.add('show');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.classList.remove('show'); }, 2600);
    }

    // ====== Panel detail kendaraan ======
    var vehDetailKey = null;
    function showVehDetail(key, replayRow) {
        var t = replayRow || trucksData[key];
        var panel = document.getElementById('vehDetail');
        if (!t || !panel) return;
        vehDetailKey = key;
        var replaying = !!replayRow;
        var st  = replaying ? replayStatus(t) : truckStatus(t);
        var dot = st === 'moving' ? '🟢 Bergerak' : (st === 'idle' ? '🟡 Idle' : '🔴 Offline');
        var spd = Math.round((parseFloat(t.speed) || 0) * 10) / 10;
        var d   = parseUpdate(t.last_update);
        var ago = d ? (fmtAgo(Date.now() - d.getTime()) + ' lalu') : '-';
        var sd  = parseUpdate(t.start_time);
        var sejak = sd ? (fmtAgo(Date.now() - sd.getTime()) + ' lalu (' + fmtClock(t.start_time) + ')') : '-';

        setText('vdTitle', (t.no_tiket || key) + (replaying ? ' · Replay' : ''));
        document.getElementById('vdBody').innerHTML =
            '<table>' +
                '<tr><td>No Pol</td><td>' + escHtml(t.no_pol || '-') + '</td></tr>' +
                '<tr><td>Driver</td><td>' + escHtml(t.driver || '-') + '</td></tr>' +
                '<tr><td>No HP Supir</td><td>' + escHtml(t.driver_phone || t.driver_whatsapp || '-') + '</td></tr>' +
                '<tr><td>Petani</td><td>' + escHtml(t.petani_name || '-') + '</td></tr>' +
                '<tr><td>Status</td><td>' + dot + '</td></tr>' +
                '<tr><td>Speed</td><td class="' + speedClass(spd) + '">' + spd + ' km/h</td></tr>' +
                '<tr><td>Update</td><td>' + escHtml(fmtClock(t.last_update)) + ' · ' + ago + '</td></tr>' +
                (replaying ? '' : (
                    '<tr><td>Berangkat</td><td>' + escHtml(sejak) + '</td></tr>' +
                    etaRowsHtml(t)
                )) +
            '</table>';

        var g = document.getElementById('vdGmaps');
        if (g) g.href = 'https://www.google.com/maps?q=' + encodeURIComponent((t.latitude || '') + ',' + (t.longitude || ''));
        panel.style.display = 'block';
    }
    function hideVehDetail() {
        var panel = document.getElementById('vehDetail');
        if (panel) panel.style.display = 'none';
        vehDetailKey = null;
    }
    document.getElementById('vdClose').addEventListener('click', function () { clearRoute(); });
    document.getElementById('vdFocus').addEventListener('click', function () {
        var t = vehDetailKey && trucksData[vehDetailKey]; if (!t) return;
        var lat = parseFloat(t.latitude), lng = parseFloat(t.longitude);
        if (!isNaN(lat) && !isNaN(lng)) {
            if (markers[vehDetailKey]) markers[vehDetailKey].addTo(map);
            map.flyTo([lat, lng], 16, { animate: true });
        }
    });
    document.getElementById('vdHist').addEventListener('click', function () { if (vehDetailKey) loadRoute(vehDetailKey, true); });
    document.getElementById('vdReplay').addEventListener('click', function () { if (vehDetailKey) openReplayPicker(vehDetailKey); });

    // ====== Select / clear all ======
    document.getElementById('btnSelectAll').addEventListener('click', function () {
        Object.keys(markers).forEach(function (key) {
            visible[key] = true; markers[key].addTo(map);
            var cb = document.getElementById('cb_' + key.replace(/[^a-zA-Z0-9]/g, '_')); if (cb) cb.checked = true;
        });
    });
    document.getElementById('btnClearAll').addEventListener('click', function () {
        Object.keys(markers).forEach(function (key) {
            visible[key] = false; map.removeLayer(markers[key]);
            var cb = document.getElementById('cb_' + key.replace(/[^a-zA-Z0-9]/g, '_')); if (cb) cb.checked = false;
        });
        if (playback.active) stopPlayback();
        clearRoute();
    });

    // ====== Replay engine ======
    // Tiap item bawa "rows" (data mentah per titik: no_pol, driver, dst — bukan
    // cuma [lat,lng]) supaya marker bisa ikut ganti plat/driver kalau di data
    // tanggal itu memang beda dari no_pol/driver TERKINI (lihat applyReplayPoint).
    function fetchRoutePromise(key) {
        return fetch(ROUTE_URL + '?no_tiket=' + encodeURIComponent(key))
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var pts = (res && res.data) ? res.data : [];
                var rows = pts.filter(function (p) { return pointLL(p) !== null; });
                routeCache[key] = rows;
                return { key: key, rows: rows };
            })
            .catch(function () { return { key: key, rows: [] }; });
    }

    function replayStatus(row) {
        // Beda dari truckStatus(): abaikan ambang "offline" (yg berbasis jarak
        // waktu ke SEKARANG) karena tidak relevan buat titik historis — cukup
        // pakai speed yg tercatat di titik itu.
        return (parseFloat(row.speed) || 0) > MOVING_KMH ? 'moving' : 'idle';
    }
    function applyReplayPoint(key, row) {
        if (!row || !markers[key]) return;
        var st = replayStatus(row);
        markers[key].setIcon(truckIcon(row.no_pol, st));
        markers[key]._st = st;
        if (markers[key].isPopupOpen()) markers[key].setPopupContent(buildPopup(row, false));
        if (vehDetailKey === key) showVehDetail(key, row);
    }
    function isReplayingKey(key) {
        return playback.active && playback.items.some(function (it) { return it.key === key; });
    }

    // Sembunyikan sementara semua marker LAIN di luar yg sedang direplay, biar
    // peta fokus ke 1 (atau beberapa) tiket yang diputar — tidak campur sama
    // tiket lain yang masih "hidup". Dipulihkan lagi begitu replay berhenti.
    function focusReplayMarkers(activeKeys) {
        var activeSet = {};
        activeKeys.forEach(function (k) { activeSet[k] = true; });
        Object.keys(markers).forEach(function (key) {
            if (activeSet[key]) return;
            if (map.hasLayer(markers[key])) {
                map.removeLayer(markers[key]);
                hiddenForReplay[key] = true;
            }
        });
    }
    function restoreHiddenAfterReplay() {
        Object.keys(hiddenForReplay).forEach(function (key) {
            if (markers[key] && visible[key]) markers[key].addTo(map);
        });
        hiddenForReplay = {};
    }

    function buildAndRun(itemsRaw, dateLabel) {
        var items = [], maxLen = 2;
        itemsRaw.forEach(function (r) {
            if (r.rows && r.rows.length >= 2 && markers[r.key]) {
                var coords = r.rows.map(pointLL);
                markers[r.key].addTo(map);
                markers[r.key].setLatLng(coords[0]);
                applyReplayPoint(r.key, r.rows[0]);
                items.push({ key: r.key, rows: r.rows, coords: coords, seg: 0, t: 0, appliedSeg: 0 });
                if (coords.length > maxLen) maxLen = coords.length;
            }
        });
        if (!items.length) { setHint('Tidak ada riwayat lokasi untuk di-replay.'); return; }
        playback.items = items; playback.maxLen = maxLen; playback.dateLabel = dateLabel || null;
        playback.active = true; playback.paused = false;
        playback.last = performance.now();
        focusReplayMarkers(items.map(function (it) { return it.key; }));
        renderTruckList();
        showReplayBar(true);
        setReplayUI(); setHint('Memutar replay…');
        playback.raf = requestAnimationFrame(stepPlayback);
    }

    function doPlay() {
        if (playback.active) { if (playback.paused) resumePlayback(); return; }
        var keys = selectedPol ? [selectedPol] : Object.keys(markers).filter(function (k) { return visible[k]; });
        if (!keys.length) { setHint('Pilih truk dulu untuk replay.'); return; }
        var haveAll = keys.every(function (k) { return routeCache[k] && routeCache[k].length >= 2; });
        if (haveAll) { buildAndRun(keys.map(function (k) { return { key: k, rows: routeCache[k] }; })); return; }
        setHint('Memuat data replay…');
        Promise.all(keys.map(fetchRoutePromise)).then(function (res) { buildAndRun(res); });
    }

    function stepPlayback(now) {
        if (!playback.active || playback.paused) return;
        var dt = (now - playback.last) * playback.speed;
        playback.last = now;

        var anyActive = false;
        playback.items.forEach(function (it) {
            var lastIdx = it.coords.length - 1;
            if (it.seg >= lastIdx) return;
            anyActive = true;
            it.t += dt / SEG_MS;
            while (it.t >= 1 && it.seg < lastIdx) { it.t -= 1; it.seg++; }
            if (it.seg !== it.appliedSeg) { applyReplayPoint(it.key, it.rows[it.seg]); it.appliedSeg = it.seg; }
            if (it.seg >= lastIdx) { if (markers[it.key]) markers[it.key].setLatLng(it.coords[lastIdx]); return; }
            var a = it.coords[it.seg], b = it.coords[it.seg + 1];
            if (markers[it.key]) markers[it.key].setLatLng([a[0] + (b[0] - a[0]) * it.t, a[1] + (b[1] - a[1]) * it.t]);
        });

        updateReplayProgress();
        if (anyActive) playback.raf = requestAnimationFrame(stepPlayback);
        else stopPlayback(true);
    }

    function pausePlayback() {
        if (!playback.active || playback.paused) return;
        playback.paused = true;
        if (playback.raf) { cancelAnimationFrame(playback.raf); playback.raf = null; }
        setReplayUI(); setHint('Replay dijeda.');
    }
    function resumePlayback() {
        if (!playback.active || !playback.paused) return;
        playback.paused = false; playback.last = performance.now();
        playback.raf = requestAnimationFrame(stepPlayback);
        setReplayUI(); setHint('Memutar replay…');
    }
    function stopPlayback(finished) {
        if (playback.raf) { cancelAnimationFrame(playback.raf); playback.raf = null; }
        playback.items.forEach(function (it) {
            if (markers[it.key] && it.coords.length) {
                markers[it.key].setLatLng(it.coords[it.coords.length - 1]);
                applyReplayPoint(it.key, it.rows[it.rows.length - 1]);
            }
        });
        playback.items = []; playback.active = false; playback.paused = false; playback.dateLabel = null;
        restoreHiddenAfterReplay();
        renderTruckList();
        setReplayUI(); setHint(finished ? 'Replay selesai.' : '');
        showReplayBar(false);
    }

    function showReplayBar(on) {
        var el = document.querySelector('.track-replaybar');
        if (el) el.classList.toggle('show', !!on);
    }

    function seekPlayback(f) {
        if (!playback.active) return;
        f = Math.max(0, Math.min(1, f));
        playback.items.forEach(function (it) {
            var lastIdx = it.coords.length - 1, pos = f * lastIdx;
            it.seg = Math.min(Math.floor(pos), lastIdx); it.t = pos - it.seg;
            if (it.seg !== it.appliedSeg) { applyReplayPoint(it.key, it.rows[it.seg]); it.appliedSeg = it.seg; }
            var a = it.coords[it.seg], b = it.coords[Math.min(it.seg + 1, lastIdx)];
            if (markers[it.key]) markers[it.key].setLatLng([a[0] + (b[0] - a[0]) * it.t, a[1] + (b[1] - a[1]) * it.t]);
        });
        updateReplayProgress();
    }

    function replayFraction() {
        var f = 0;
        playback.items.forEach(function (it) {
            var L = it.coords.length - 1, pf = L > 0 ? (it.seg + it.t) / L : 1;
            if (pf > f) f = pf;
        });
        return Math.min(f, 1);
    }
    function updateReplayProgress() {
        var f = replayFraction();
        var pr = document.getElementById('replayProgress');
        if (pr && document.activeElement !== pr) pr.value = Math.round(f * 1000);
        var total = (playback.maxLen - 1) * SEG_MS / 1000; // durasi @1x
        setTimeText('replayElapsed', f * total);
        setTimeText('replayTotal', total);
    }

    function setReplayUI() {
        var on = playback.active, paused = playback.paused;
        var play = document.getElementById('rbPlay'), pause = document.getElementById('rbPause'), stop = document.getElementById('rbStop');
        if (play)  play.disabled  = on && !paused;
        if (pause) pause.disabled = !(on && !paused);
        if (stop)  stop.disabled  = !on;
        if (!on) {
            var pr = document.getElementById('replayProgress'); if (pr) pr.value = 0;
            setTimeText('replayElapsed', 0);
        }
        updateReplayScope();
        updateModeBadge();
    }
    function updateModeBadge() {
        var el = document.getElementById('modeBadge'); if (!el) return;
        if (playback.active) {
            var lbl = playback.items.length > 1
                ? (playback.items.length + ' truk')
                : (playback.items[0] ? playback.items[0].key : '');
            el.className = 'tb-mode mode-replay';
            el.innerHTML = '<i class="fas fa-history"></i> REPLAY' +
                (lbl ? ' · ' + escHtml(lbl) : '') +
                (playback.dateLabel ? ' · ' + escHtml(playback.dateLabel) : '');
        } else {
            el.className = 'tb-mode mode-live';
            el.innerHTML = '<span class="dot"></span> LIVE';
        }
    }
    function updateReplayScope() {
        var el = document.getElementById('rbScope'); if (!el) return;
        if (playback.active) {
            el.textContent = playback.items.length > 1 ? ('Replay ' + playback.items.length + ' truk') : ('Replay ' + (playback.items[0] ? playback.items[0].key : ''));
            return;
        }
        el.textContent = selectedPol ? ('Siap replay: ' + selectedPol) : 'Replay semua truk tampil';
    }

    document.getElementById('rbPlay').addEventListener('click', doPlay);
    document.getElementById('rbPause').addEventListener('click', pausePlayback);
    document.getElementById('rbStop').addEventListener('click', function () { stopPlayback(false); });
    document.getElementById('replayProgress').addEventListener('input', function () { seekPlayback(this.value / 1000); });
    document.querySelectorAll('.replay-speed').forEach(function (b) {
        b.addEventListener('click', function () {
            playback.speed = parseFloat(this.dataset.mul) || 1;
            document.querySelectorAll('.replay-speed').forEach(function (x) { x.classList.toggle('active', x === b); });
        });
    });

    // ====== Search & sidebar toggle ======
    document.getElementById('searchTruck').addEventListener('input', function () {
        searchQuery = this.value.toLowerCase().trim();
        renderTruckList();
    });
    document.querySelectorAll('#sbFilters .qf').forEach(function (b) {
        b.addEventListener('click', function () {
            statusFilter = this.dataset.f || 'all';
            document.querySelectorAll('#sbFilters .qf').forEach(function (x) { x.classList.toggle('active', x === b); });
            renderTruckList();
        });
    });
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        document.querySelector('.tracking-wrapper').classList.toggle('sidebar-collapsed');
        setTimeout(fitHeight, 60);
    });

    // ====== Fit visible trucks (zoom peta ke semua kendaraan tampil) ======
    function fitVisibleTrucks() {
        var lls = [];
        Object.keys(markers).forEach(function (key) {
            if (visible[key]) { var ll = markers[key].getLatLng(); lls.push([ll.lat, ll.lng]); }
        });
        if (!lls.length) { setHint('Tidak ada kendaraan yang tampil di peta.'); return; }
        if (lls.length === 1) { map.flyTo(lls[0], 16, { animate: true }); return; }
        map.flyToBounds(L.latLngBounds(lls).pad(0.2), { animate: true });
    }
    document.getElementById('btnFitVisible').addEventListener('click', fitVisibleTrucks);

    // ====== Legend collapse ======
    document.getElementById('legendToggle').addEventListener('click', function () {
        document.getElementById('mapLegend').classList.toggle('collapsed');
    });

    // ====== Alert list toggle ======
    document.getElementById('alertToggle').addEventListener('click', function () {
        var l = document.getElementById('alertList');
        l.style.display = (l.style.display === 'none' || !l.style.display) ? 'block' : 'none';
    });

    // ====== Countdown & auto-refresh ======
    function startCountdown() {
        countdown = REFRESH_SEC;
        clearInterval(countdownTimer);
        updateCountdownDisplay();
        countdownTimer = setInterval(function () {
            countdown--;
            if (countdown <= 0) { countdown = REFRESH_SEC; if (!playback.active) loadLocations(); }
            updateCountdownDisplay();
        }, 1000);
    }
    function updateCountdownDisplay() {
        var m = Math.floor(countdown / 60), s = countdown % 60;
        setText('countdown', (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s));
        var bar = document.getElementById('refreshProgBar');
        if (bar) bar.style.width = Math.max(0, Math.min(100, (countdown / REFRESH_SEC) * 100)) + '%';
    }
    document.getElementById('btnRefreshNow').addEventListener('click', function () {
        if (playback.active) stopPlayback();
        loadLocations(); startCountdown();
    });

    function setSpinner(on) {
        var el = document.getElementById('spinIcon');
        if (el) el.classList.toggle('spin', !!on);
    }

    // Isi area peta tepat sampai dasar viewport (hilangkan blank di bawah; aman saat fullscreen).
    function fitHeight() {
        var main = document.querySelector('.track-main');
        if (!main) return;
        var top = main.getBoundingClientRect().top;
        var h = window.innerHeight - top;
        main.style.height = (h > 320 ? h : 320) + 'px';
        map.invalidateSize();
    }
    window.addEventListener('resize', fitHeight);
    document.addEventListener('fullscreenchange',       function () { setTimeout(fitHeight, 120); });
    document.addEventListener('webkitfullscreenchange', function () { setTimeout(fitHeight, 120); });

    // ====== Helpers ======
    function setText(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; }
    function val(id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; }
    function setTimeText(id, sec) {
        sec = Math.max(0, Math.round(sec || 0));
        var m = Math.floor(sec / 60), s = sec % 60;
        setText(id, (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s));
    }
    function escHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ====== Replay picker (pilih arsip perjalanan dulu, baru diputar) ======
    var replayPicker = { key: null };

    function todayInputDate() {
        var d = new Date();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }
    function pickerHint(msg) { var el = document.getElementById('replayPickerHint'); if (el) el.textContent = msg || ''; }
    function showReplayPicker() { var el = document.getElementById('replayPickerModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).show() : $(el).modal('show'); }
    function hideReplayPicker() { var el = document.getElementById('replayPickerModal'); (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(el).hide() : $(el).modal('hide'); }

    function openReplayPicker(key) {
        replayPicker.key = key;
        var t = trucksData[key] || {};
        document.getElementById('replayVehicle').value = (t.no_tiket || key) + ' - ' + (t.no_pol || '-') + ' - ' + (t.driver || '-');
        document.getElementById('replayDate').value = todayInputDate();
        document.getElementById('replayPeriod').value = 'all';
        pickerHint('');
        showReplayPicker();
    }

    document.addEventListener('click', function (e) { if (e.target.closest('.rp-close')) hideReplayPicker(); });

    document.getElementById('btnStartReplayByDate').addEventListener('click', function () {
        var key = replayPicker.key;
        var date = document.getElementById('replayDate').value;
        var period = document.getElementById('replayPeriod').value;
        if (!key)  { pickerHint('Pilih kendaraan dulu.'); return; }
        if (!date) { pickerHint('Pilih tanggal replay dulu.'); return; }
        loadRouteByDate(key, date, period, true, true);
    });

    // Ambil rute terfilter tanggal/periode lalu replay. Tidak mengubah loadRoute lama.
    function loadRouteByDate(key, date, period, fit, replayAfter) {
        selectedPol = key;
        pickerHint('Memuat…');
        $.ajax({
            url: ROUTE_URL, type: 'GET', dataType: 'json',
            data: { no_tiket: key, date: date, period: period },
            success: function (res) {
                var pts = (res && res.data) ? res.data : [];
                if (!pts.length) { pickerHint('Tidak ada data perjalanan pada tanggal/periode itu.'); return; }

                hideReplayPicker();
                drawRoute(pts, fit);
                drawDriveRoute(pts);

                var rows = pts.filter(function (p) { return pointLL(p) !== null; });
                routeCache[key + '|' + date + '|' + period] = rows;
                renderTruckList();

                if (replayAfter) {
                    if (rows.length >= 2 && markers[key]) {
                        if (playback.active) stopPlayback();
                        buildAndRun([{ key: key, rows: rows }], date);
                    } else {
                        setHint('Data perjalanan terlalu sedikit untuk di-replay.');
                    }
                }
            },
            error: function () { pickerHint('Gagal memuat perjalanan.'); }
        });
    }

    // ====== Boot ======
    loadLocations();
    startCountdown();
    setReplayUI();
    setTimeout(fitHeight, 200);

})();

// ====== Banner install PWA ======
(function () {
    'use strict';

    var SNOOZE_KEY  = 'wgiPwaSnoozeUntil';
    var SNOOZE_DAYS = 7;

    var banner  = document.getElementById('pwaInstall');
    var btnGo   = document.getElementById('pwaInstallBtn');
    var btnNope = document.getElementById('pwaDismissBtn');
    var subText = document.getElementById('pwaSub');
    if (!banner) return;

    var deferred = null; // event beforeinstallprompt yang ditahan

    // Sudah terpasang? Jangan pernah tawarkan lagi.
    function isInstalled() {
        return ['fullscreen', 'standalone', 'minimal-ui'].some(function (m) {
            return window.matchMedia('(display-mode: ' + m + ')').matches;
        }) || navigator.standalone === true;
    }
    function snoozed() {
        try { return Date.now() < parseInt(localStorage.getItem(SNOOZE_KEY) || '0', 10); }
        catch (e) { return false; }
    }
    function snooze() {
        try { localStorage.setItem(SNOOZE_KEY, Date.now() + SNOOZE_DAYS * 864e5); } catch (e) {}
    }
    function show() { if (!isInstalled() && !snoozed()) banner.classList.add('show'); }
    function hide() { banner.classList.remove('show'); }

    // Chrome / Edge / Android: browser memberi tahu saat halaman layak dipasang.
    //
    // Sengaja TIDAK memanggil e.preventDefault(): memanggilnya akan MEMATIKAN
    // notifikasi bawaan browser (mini-infobar di Android). Karena kita ingin
    // dua-duanya muncul -- notifikasi browser di atas DAN banner kita di bawah --
    // event-nya dibiarkan berjalan normal, lalu tetap kita simpan supaya tombol
    // "Pasang" di banner bisa memanggil prompt() sendiri.
    //
    // Catatan: di Chrome desktop tidak ada mini-infobar. Yang muncul adalah ikon
    // install (⊕) di address bar, dan itu memang otomatis begitu syarat PWA
    // terpenuhi -- tidak bisa dipaksa maupun dimatikan dari kode.
    window.addEventListener('beforeinstallprompt', function (e) {
        deferred = e;
        show();
    });

    btnGo.addEventListener('click', function () {
        if (!deferred) return;          // di iOS tombol ini memang disembunyikan
        hide();
        deferred.prompt();
        deferred.userChoice.then(function (res) {
            if (res.outcome !== 'accepted') snooze();  // ditolak: jangan ganggu 7 hari
            deferred = null;
        });
    });

    btnNope.addEventListener('click', function () { snooze(); hide(); });

    window.addEventListener('appinstalled', function () {
        deferred = null;
        try { localStorage.removeItem(SNOOZE_KEY); } catch (e) {}
        hide();
    });

    // iOS Safari tidak punya beforeinstallprompt sama sekali; install hanya bisa
    // manual lewat menu Bagikan. Jadi banner ditampilkan sebagai petunjuk.
    var ua    = navigator.userAgent;
    var isIOS = /iphone|ipad|ipod/i.test(ua) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(ua);
    if (isIOS && isSafari) {
        subText.textContent = 'Ketuk Bagikan, lalu "Tambahkan ke Layar Utama".';
        btnGo.style.display = 'none';
        show();
    }
})();
</script>
@endsection
