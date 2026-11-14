<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }}</title>
<script>
    // Absolut dan ikut APP_URL (bisa berisi path subfolder, mis.
    // http://host/wgi) -- semua fetch() di absensi-mandiri.js HARUS
    // dibangun dari ini, bukan path root-relative ("/absensi/..."),
    // supaya tidak 404 saat app ini dipasang di subfolder.
    window.AbsensiMandiriBase = @json(url('/absensi'));
</script>
<link rel="icon" href="{{ asset('media/img/favicon/favicon.ico') }}" type="image/x-icon">

{{-- ===== PWA ===== --}}
<link rel="manifest" href="{{ asset('media/manifest-absensi.json') }}">
<meta name="theme-color" content="#189e31">
<link rel="apple-touch-icon" href="{{ asset('media/img/pwa/icon-192x192.png') }}">
<script type="text/javascript">
    (function () {
        if (!('serviceWorker' in navigator)) return;

        // Skrip SW berada di root aplikasi, jadi scope maksimalnya adalah root.
        // Kita persempit ke /absensi supaya halaman lain (/admin,
        // /tracking, ...) tidak pernah dikontrol service worker ini. Karena
        // scope lebih SEMPIT dari lokasi skrip, header Service-Worker-Allowed
        // tidak diperlukan.
        var swUrl   = @json(url('/service-worker-absensi.js'));
        var swScope = @json(parse_url(url('/absensi'), PHP_URL_PATH));

        window.addEventListener('load', function () {
            navigator.serviceWorker.register(swUrl, { scope: swScope }).then(
                function (reg) { console.log('ServiceWorker aktif, scope:', reg.scope); },
                function (err) { console.warn('ServiceWorker gagal didaftarkan:', err); }
            );
        });
    })();
</script>

<style>
    :root {
        --brand: #189e31;
        --brand-dark: #137a26;
        --ink: #1f2937;
        --ink-soft: #6b7280;
        --line: #e5e7eb;
        --bg: #f3f4f6;
        --danger: #dc2626;
    }

    * { box-sizing: border-box; }
    html, body {
        height: 100%; margin: 0; padding: 0;
        font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
        background: var(--bg); color: var(--ink);
    }

    .absensi-wrapper { max-width: 480px; margin: 0 auto; padding: 16px 16px 40px; }

    h1, h2, h3 { margin: 0 0 8px; }
    p { margin: 0 0 10px; color: var(--ink-soft); }

    /* ====== Tabs ====== */
    .absensi-tabs { display: flex; gap: 6px; margin-bottom: 16px; }
    .absensi-tabs button {
        flex: 1; padding: 10px 6px; border: 1px solid var(--line); background: #fff;
        border-radius: 10px; font-size: 13px; font-weight: 600; color: var(--ink-soft);
        transition: background .15s, color .15s, border-color .15s;
    }
    .absensi-tabs button.active { background: var(--brand); color: #fff; border-color: var(--brand); }

    .absensi-panel { display: none; }
    .absensi-panel.active { display: block; }

    /* ====== Form controls (shared by Absen/Riwayat/Profil) ====== */
    input[type="text"], input[type="date"], textarea {
        width: 100%; padding: 10px 12px; margin-bottom: 12px; border: 1px solid var(--line);
        border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; color: var(--ink);
    }
    textarea { min-height: 70px; resize: vertical; }
    label { display: block; font-size: 13px; font-weight: 600; color: var(--ink-soft); margin-bottom: 4px; }

    button {
        font-family: inherit; cursor: pointer; border: none; border-radius: 8px;
        padding: 10px 16px; font-size: 14px; font-weight: 600; background: #fff;
        color: var(--ink); border: 1px solid var(--line);
    }
    button[disabled] { opacity: .5; cursor: not-allowed; }

    /* Tombol aksi utama (hijau, isi penuh) -- di-apply lewat ID langsung
       supaya tidak perlu ubah markup yang dibentuk JS. */
    #profile-gate button[type="submit"],
    #absen-submit,
    #riwayat-download,
    #profil-form button[type="submit"] {
        background: var(--brand); color: #fff; border-color: var(--brand); width: 100%;
    }
    #profile-gate button[type="submit"]:not([disabled]):active,
    #absen-submit:not([disabled]):active,
    #riwayat-download:active,
    #profil-form button[type="submit"]:active {
        background: var(--brand-dark); border-color: var(--brand-dark);
    }

    #absen-retake, #absen-location-retry { width: 100%; }
    .riwayat-detail-box button { width: 100%; }

    /* ====== Profile gate ====== */
    #profile-gate {
        position: fixed; inset: 0; background: #fff; z-index: 1000;
        display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    #profile-gate.hidden { display: none; }
    #profile-gate form { max-width: 320px; width: 100%; text-align: center; }
    #profile-gate h3 { font-size: 20px; }
    #profile-gate input[type="text"] { text-align: left; }

    /* ====== Absen tab ====== */
    .absen-type-buttons { display: flex; gap: 8px; margin-bottom: 4px; }
    .absen-type-buttons button {
        flex: 1; background: #fff; color: var(--brand); border: 2px solid var(--brand) !important;
        padding: 14px 8px;
    }
    .absen-type-buttons button:active { background: var(--brand); color: #fff; }

    #absen-form { margin-top: 14px; }
    #absen-form.hidden { display: none; }
    #absen-tipe-label { font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 10px; }

    #absen-camera-wrap { position: relative; margin-bottom: 12px; border-radius: 10px; overflow: hidden; }
    #absen-video { display: block; width: 100%; max-height: 320px; background: #000; object-fit: cover; }
    #absen-capture {
        position: absolute; left: 50%; bottom: 12px; transform: translateX(-50%);
        padding: 12px 22px; background: #fff; color: var(--ink); border: none;
        border-radius: 999px; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,.3);
    }
    #absen-camera-error { color: var(--danger); font-size: 13px; margin-bottom: 10px; }

    #absen-foto { width: 100%; margin-bottom: 12px; }

    #absen-preview {
        display: block; width: 100%; max-width: 100%; height: auto; border-radius: 10px;
        margin-bottom: 10px; border: 1px solid var(--line);
    }
    #absen-retake { margin-bottom: 14px; }

    #absen-location-status { font-size: 13px; color: var(--ink-soft); margin-bottom: 4px; }
    #absen-location-retry { margin-bottom: 12px; }

    #absen-submit { margin-top: 4px; }
    #absen-message { font-size: 13px; text-align: center; margin: 10px 0 0; min-height: 18px; }

    /* ====== Riwayat tab ====== */
    .riwayat-filters { display: flex; gap: 8px; }
    .riwayat-filters input[type="date"] { margin-bottom: 0; }

    .riwayat-mode { display: flex; gap: 6px; margin: 12px 0; }
    .riwayat-mode button {
        flex: 1; background: #fff; border: 1px solid var(--line) !important; color: var(--ink-soft);
        padding: 8px;
    }
    .riwayat-mode button.active { background: var(--ink); color: #fff; border-color: var(--ink) !important; }

    #riwayat-list, #riwayat-recap {
        background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden;
        margin-bottom: 14px;
    }
    .riwayat-row {
        padding: 10px 14px; border-bottom: 1px solid var(--line); cursor: pointer;
        display: flex; flex-direction: column; gap: 2px; font-size: 13px;
    }
    .riwayat-row:last-child { border-bottom: none; }
    .riwayat-row:active { background: #f9fafb; }
    .riwayat-row strong { font-size: 14px; }
    .riwayat-row img { margin-top: 4px; border-radius: 6px; object-fit: cover; }

    #tab-riwayat label {
        display: block; font-weight: 400; color: var(--ink); font-size: 13px;
        margin: 10px 0 12px;
    }
    #riwayat-include-photos { width: auto; margin: 0 6px 0 0; vertical-align: middle; }

    .riwayat-actions { display: flex; gap: 8px; }
    .riwayat-actions button { flex: 1; }
    #riwayat-cleanup { background: #fff; color: var(--danger); border: 1px solid var(--danger) !important; }

    #riwayat-toast { font-size: 13px; text-align: center; color: var(--ink-soft); margin: 10px 0 0; min-height: 18px; }

    .riwayat-detail-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 1100;
        display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    .riwayat-detail-box {
        background: #fff; border-radius: 12px; padding: 16px; max-width: 420px;
        width: 100%; max-height: 90vh; overflow-y: auto;
    }
    .riwayat-detail-box img { max-width: 100%; border-radius: 8px; margin-bottom: 10px; }
    .riwayat-detail-box button { margin-top: 10px; width: 100%; }

    /* ====== Profil tab ====== */
    #profil-form button { width: 100%; margin-top: 4px; }
    #profil-message { font-size: 13px; color: var(--brand); text-align: center; margin: 10px 0 0; }
</style>
</head>
<body>

<div id="profile-gate">
    <form id="profile-gate-form">
        <h3>Absensi Mandiri WGI</h3>
        <p>Isi profil dulu untuk mulai (tanpa login).</p>
        <input type="text" id="profile-name" placeholder="Nama" required>
        <input type="text" id="profile-role" placeholder="Peran" required>
        <button type="submit">Mulai</button>
    </form>
</div>

<div class="absensi-wrapper">
    <div class="absensi-tabs">
        <button type="button" class="tab-btn active" data-tab="tab-absen">Absen</button>
        <button type="button" class="tab-btn" data-tab="tab-riwayat">Riwayat</button>
        <button type="button" class="tab-btn" data-tab="tab-profil">Profil</button>
    </div>

    <div id="tab-absen" class="absensi-panel active"></div>
    <div id="tab-riwayat" class="absensi-panel"></div>
    <div id="tab-profil" class="absensi-panel"></div>
</div>

<script src="{{ asset('media/js/absensi-mandiri.js') }}?v={{ time() }}"></script>
</body>
</html>
