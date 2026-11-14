@extends('layouts.admin')
@section('title', $title)

@section('extend_script')
    <link href="{{ asset('media/plugins/leafletjs/leaflet.css') }}" rel='stylesheet' media='screen' />
    <link href="{{ asset('media/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('media/plugins/sweetalert/sweetalert.min.js') }}"></script>
    <style>
        /* --- Geofence: peta & daftar (dipindah dari halaman Peta & Geofence lama) --- */
        #setMap { width: 100%; height: calc(100vh - 200px); min-height: 420px; border-radius: 8px; z-index: 1; }
        .setmap-wrap { position: relative; }
        .setmap-wrap.pick-active #setMap { cursor: crosshair; }
        .map-tools { position: absolute; top: 10px; right: 10px; z-index: 500; display: flex; gap: 6px; }
        .map-tools .btn { box-shadow: 0 1px 4px rgba(0,0,0,.25); }
        .set-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 14px; }
        .set-card .sc-head { padding: 10px 14px; border-bottom: 1px solid #eef0f2; font-weight: 600; display: flex; align-items: center; gap: 6px; }
        .set-card .sc-head small { font-weight: 400; color: #9ca3af; margin-left: auto; }
        .set-card .sc-body { padding: 12px 14px; }
        .set-label { font-size: 12px; font-weight: 600; margin-bottom: 3px; display: block; }
        .coord-row { display: flex; gap: 6px; }
        .geo-row { border: 1px solid #eef0f2; border-radius: 7px; padding: 8px; margin-bottom: 8px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .geo-row:hover { border-color: #c7d2fe; }
        .geo-row.active { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8 inset; }
        .geo-row .geo-title { font-size: 12px; font-weight: 700; color: #374151; display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
        .geo-badge { font-size: 10px; padding: 1px 6px; border-radius: 10px; background: #eef2ff; color: #3730a3; }
        .geo-grid { display: grid; grid-template-columns: 1fr 1fr 90px; gap: 6px; }
        .geo-radius-wrap { display: flex; align-items: center; gap: 6px; margin-top: 6px; }
        .geo-radius-wrap .geo-radius-slider { flex: 1; }
        .geo-foot { display: flex; align-items: center; gap: 6px; margin-top: 6px; }
        .geo-foot .btn { flex: 1; }
        .geo-status { font-size: 10px; padding: 1px 6px; border-radius: 10px; }
        .geo-status.ok { background: #dcfce7; color: #166534; }
        .geo-status.warn { background: #fef9c3; color: #854d0e; }
        .geo-status.off { background: #f3f4f6; color: #6b7280; }
        .geo-dest-badge { font-size: 10px; padding: 1px 6px; border-radius: 10px; background: #1a73e8; color: #fff; }
        .grp-title { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 700; margin: 6px 0; }
        #pickHint { min-height: 18px; font-size: 12px; color: #1a73e8; }
        .dest-display { font-size: 13px; font-weight: 600; padding: 4px 0; }
        .set-pin span { display: block; width: 16px; height: 16px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,.35); }
        #btnSaveGeofence.dirty { background-color: #f59e0b; border-color: #f59e0b; }

        /* --- Area Kebun: checkbox ---
           media/css/admin/main.bs4.css punya rule "input[type=checkbox]{border:0;background:transparent;width:auto;height:auto}"
           yang (gara-gara attribute-selector lebih spesifik dari satu class) menimpa .form-check-input bawaan Bootstrap 5
           dan menghapus kotak/bordernya, sementara appearance:none dari Bootstrap tetap aktif -> checkbox jadi tanpa bentuk.
           Gambar ulang di sini lewat selector ID (spesifisitas tertinggi) biar jelas kelihatan kotak + centangnya. */
        #tracking_garden_area_check {
            appearance: none; -webkit-appearance: none;
            width: 1.3em; height: 1.3em; margin-top: 0; flex-shrink: 0;
            border: 1.5px solid #9ca3af; border-radius: 4px; background-color: #fff;
            background-repeat: no-repeat; background-position: center; background-size: 70% 70%;
            cursor: pointer; transition: background-color .15s, border-color .15s;
        }
        #tracking_garden_area_check:checked {
            background-color: #1a73e8; border-color: #1a73e8;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
        }
        #tracking_garden_area_check:focus { outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,.25); }
        .form-check-label[for="tracking_garden_area_check"] { cursor: pointer; padding-top: .05em; }
        .garden-check-status { font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; margin-left: 8px; vertical-align: middle; }
        .garden-check-status.on { background: #dcfce7; color: #166534; }
        .garden-check-status.off { background: #f3f4f6; color: #6b7280; }
    </style>
@endsection

@section('content')
<?php
use Cete\Libraries\CTAdmin;
use Illuminate\Support\Facades\Storage;

// Logo Dark/Light & Favicon: kosong (belum pernah diupload) -> fallback ke
// gambar default lama. Sudah diupload -> ambil dari disk `public`.
$logoUrl = fn ($path) => $path ? Storage::disk('public')->url($path) : asset('media/img/no_image.png');
?>
<div class="content-wrapper" id="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title no-bg clearfix mb5 no-border">
                <div><h1 class="pl0">@yield('title')</h1></div>
            </div>
            <div class="row mt-3">
                <div class="col-xl-3">
                    <div class="card sticky-top setting-sidebar">
                        <div class="list-group list-group-flush useradd-sidenav" id="useradd-sidenav">
                            <a href="{{route('admin.setting.system')}}#app-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('App Settings') }}
                                <div class="float-end"><i class="fa fa-chevron-right"></i></div>
                            </a>
                            <a href="{{route('admin.setting.system')}}#api-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('API Settings') }}
                                <div class="float-end"><i class="fa fa-chevron-right"></i></div>
                            </a>
                            <a href="{{route('admin.setting.system')}}#app-version-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('App Version Settings') }}
                                <div class="float-end"><i class="fa fa-chevron-right"></i></div>
                            </a>
                            <a href="{{route('admin.setting.system')}}#geofence-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Geofence Settings') }}
                                <div class="float-end"><i class="fa fa-chevron-right"></i></div>
                            </a>
                        </div>
                    </div>
                </div>
                <div data-spy="scroll" class="col-xl-9 setting-menu-div">

                    {{-- ======================= APP SETTINGS ======================= --}}
                    <form method="post" name="form_app" id="form_app" enctype="multipart/form-data">
                        @csrf
                        <div id="app-settings" class="card">
                            <div class="card-header p-3">
                                <h5>{{ __('App Settings') }}</h5>
                                <small class="text-muted">{{ __('Edit your details.') }}</small>
                            </div>
                            <div class="card-body p-3">
                                <div class="row row-gap-1 mb-4">
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="card">
                                            <div class="card-header p-3">
                                                <h5>{{ __('Logo dark') }}</h5>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class=" setting-card setting-logo-box">
                                                    <div class="logo-content dark-logo">
                                                        <img id="image"
                                                            src="{{ $logoUrl($setting->logo_dark ?? null) }}">
                                                    </div>
                                                    <div class="choose-files mt-3 text-center">
                                                        <label for="full_logo">
                                                            <div class=" bg-primary company_logo_update"> <i
                                                                    class="fa fa-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" name="logo_dark" id="full_logo" accept="image/*"
                                                                class="form-control file" data-filename="full_logo" data-preview="image">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="card">
                                            <div class="card-header p-3">
                                                <h5>{{ __('Logo Light') }}</h5>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="setting-card setting-logo-box">
                                                    <div class="logo-content">
                                                        <img id="image1"
                                                            src="{{ $logoUrl($setting->logo_light ?? null) }}"
                                                            class="big-logo img_setting">
                                                    </div>
                                                    <div class="choose-files mt-3 text-center">
                                                        <label for="logo_light">
                                                            <div class=" bg-primary dark_logo_update"> <i
                                                                    class="fa fa-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" class="form-control file" name="logo_light" accept="image/*"
                                                                id="logo_light" data-filename="logo_light" data-preview="image1">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="card">
                                            <div class="card-header p-3">
                                                <h5>{{ __('Favicon') }}</h5>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="setting-card setting-logo-box">
                                                    <div class="logo-content">
                                                        <img id="image2"
                                                            src="{{ $logoUrl($setting->favicon ?? null) }}"
                                                            width="50px" class="img_setting">
                                                    </div>
                                                    <div class="choose-files mt-3 text-center">
                                                        <label for="favicon">
                                                            <div class="bg-primary company_favicon_update"> <i
                                                                    class="fa fa-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" class="form-control file" id="favicon" accept="image/*,.ico"
                                                                name="favicon" data-filename="favicon" data-preview="image2">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row-gap-1">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label">Title Text</label>
                                            <input class="form-control" placeholder="Title Text"
                                                name="app_title" type="text" value="{{ old('app_title', $setting->app_title ?? '') }}" id="app_title">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label">Footer Text</label>
                                            <input class="form-control" placeholder="Enter Footer Text"
                                                name="app_name" type="text" value="{{ old('app_name', $setting->app_name ?? '') }}" id="app_name">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body border-top border-1 p-3">
                                <div class="setting-card setting-logo-box">
                                    <div class="row row-gap-3">
                                        <div class="col-xxl-4 col-md-4 col-sm-6 col-12">
                                            <div class="card h-100 mb-0">
                                                <div class="card-header p-2">
                                                    <h6 class="mb-0 d-flex align-items-center gap-2">
                                                        <i class="fa fa-bell"></i> {{ __('Push Notification') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <label class="switcher switcher-block">
                                                        <input type="checkbox" class="switcher-input" name="use_push_notification" value="1"
                                                            {{ ! empty($setting->use_push_notification) ? 'checked' : '' }}>
                                                        <span class="switcher-indicator"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-4 col-md-4 col-sm-6 col-12">
                                            <div class="card h-100 mb-0">
                                                <div class="card-header p-2">
                                                    <h6 class="mb-0 d-flex align-items-center gap-2">
                                                        <i class="fa fa-folder"></i> {{ __('Email Notification') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <label class="switcher switcher-block">
                                                        <input type="checkbox" class="switcher-input" name="use_email_notification" value="1"
                                                            {{ ! empty($setting->use_email_notification) ? 'checked' : '' }}>
                                                        <span class="switcher-indicator"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body border-top border-1 p-3">
                                <h6 class="mb-3">{{ __('Peta Tracking') }}</h6>
                                <div class="row row-gap-3">
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Pusat Peta (center)</label>
                                        <div class="coord-row">
                                            <input type="text" class="form-control form-control-sm" name="center_lat" id="center_lat" placeholder="lat">
                                            <input type="text" class="form-control form-control-sm" name="center_lng" id="center_lng" placeholder="lng">
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-block mt-1" data-pick="center">
                                            <i class="uil uil-location-point"></i> Pilih di peta
                                        </button>
                                        <small class="text-muted d-block mt-1">Peta ada di bagian <b>Geofence Settings</b> di bawah.</small>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Titik Start (fallback)</label>
                                        <input type="text" class="form-control form-control-sm mb-1" name="start_label" id="start_label" placeholder="Nama start">
                                        <div class="coord-row">
                                            <input type="text" class="form-control form-control-sm" name="start_lat" id="start_lat" placeholder="lat">
                                            <input type="text" class="form-control form-control-sm" name="start_lng" id="start_lng" placeholder="lng">
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-block mt-1" data-pick="start">
                                            <i class="uil uil-play"></i> Pilih di peta
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer p-3 text-end">
                                <button class="btn btn-primary" type="button" id="btnSaveApp">
                                    <i class="fa fa-save"></i> {{ __('Simpan App Settings') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ======================= API SETTINGS ======================= --}}
                    <form method="post" name="form_api" id="form_api">
                        @csrf
                        <div id="api-settings" class="card">
                            <div class="card-header p-3">
                                <h5>{{ __('API Settings') }}</h5>
                                <small class="text-muted">{{ __('Kunci integrasi pihak ketiga.') }}</small>
                            </div>
                            <div class="card-body p-3">
                                <div class="row row-gap-3">
                                    <div class="col-md-4 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">Firebase Key</label>
                                            <input class="form-control" placeholder="Enter Firebase Key"
                                                name="firebase_key" type="text" value="{{ old('firebase_key', $setting->firebase_key ?? '') }}" id="firebase_key">
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">WhatsApp Meta Key</label>
                                            <input class="form-control" placeholder="Enter WhatsApp Meta Key"
                                                name="whatsapp_key" type="text" value="{{ old('whatsapp_key', $setting->whatsapp_key ?? '') }}" id="whatsapp_key">
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">Accurate Key</label>
                                            <input class="form-control" placeholder="Enter Accurate Key"
                                                name="accurate_key" type="text" value="{{ old('accurate_key', $setting->accurate_key ?? '') }}" id="accurate_key">
                                        </div>
                                    </div>
                                </div>
                                <div class="row row-gap-3 mt-3">
                                    <div class="col-md-4 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">Chatbot API Key</label>
                                            <input class="form-control" placeholder="Enter Chatbot API Key"
                                                name="chatbot_key" type="text" value="{{ old('chatbot_key', $setting->chatbot_key ?? '') }}" id="chatbot_key">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Wajib/tidaknya sopir di dalam area kebun sebelum foto truk --}}
                            <div class="card-body border-top border-1 p-3">
                                <h6 class="mb-3"><i class="uil uil-shield-exclamation"></i> {{ __('Area Kebun (Foto Truk)') }}</h6>
                                <div class="form-check d-flex align-items-start">
                                    <input type="checkbox" class="form-check-input" name="tracking_garden_area_check" value="1" id="tracking_garden_area_check">
                                    <label class="form-check-label" for="tracking_garden_area_check">
                                        Wajibkan sopir berada di dalam area kebun sebelum bisa memfoto truk
                                        <span id="gardenCheckStatus" class="garden-check-status off">Opsional</span>
                                    </label>
                                </div>
                                <small class="text-muted">
                                    Kalau <b>tidak dicentang</b> (default, 2026 masih opsional): sopir di luar area kebun cuma
                                    dikasih peringatan, tetap bisa lanjut foto lewat tombol "Tetap Lanjutkan".
                                    Kalau <b>dicentang</b>: sopir WAJIB mendekat ke area kebun dulu, tidak bisa lanjut sebelum itu.
                                </small>
                            </div>

                            {{-- Perilaku tracking yang dulu hardcoded di app (radius, umur tiket, interval, toleransi area) --}}
                            <div class="card-body border-top border-1 p-3">
                                <h6 class="mb-1"><i class="uil uil-setting"></i> {{ __('Perilaku Tracking (Advanced)') }}</h6>
                                <small class="text-muted d-block mb-3">
                                    Kosongkan field apa pun untuk pakai nilai default app (ditunjukkan sebagai placeholder).
                                    Berlaku untuk app yang baru fetch setelah disimpan (sekali per mulai/lanjut tracking).
                                </small>
                                <div class="row row-gap-3">
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Umur Max Tiket Lokal (jam)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_ticket_max_age_hours" id="tracking_ticket_max_age_hours" placeholder="31">
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Radius Keluar Kebun (m)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_leaving_farm_radius_m" id="tracking_leaving_farm_radius_m" placeholder="200">
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Interval Cek Selesai (detik)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_completion_poll_interval_s" id="tracking_completion_poll_interval_s" placeholder="20">
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Interval Kirim Lokasi (detik)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_location_post_interval_s" id="tracking_location_post_interval_s" placeholder="10">
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">Interval Update Notifikasi (detik)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_notification_update_interval_s" id="tracking_notification_update_interval_s" placeholder="15">
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <label class="set-label">GPS Distance Filter (m)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_gps_distance_filter_m" id="tracking_gps_distance_filter_m" placeholder="5">
                                    </div>
                                    <div class="col-12">
                                        <label class="set-label">Akurasi GPS Maks untuk Cek Area Kebun (m)</label>
                                        <input type="number" class="form-control form-control-sm" name="tracking_area_max_gps_accuracy_m" id="tracking_area_max_gps_accuracy_m" placeholder="20">
                                    </div>
                                    <div class="col-12">
                                        <label class="set-label">Aturan Toleransi Area (JSON)</label>
                                        <textarea class="form-control form-control-sm" rows="2" name="tracking_area_tolerance_rules" id="tracking_area_tolerance_rules"
                                            placeholder='[{"max_area_ha":0.2,"base_tolerance":3},{"max_area_ha":0.5,"base_tolerance":4},{"max_area_ha":1,"base_tolerance":6},{"max_area_ha":2,"base_tolerance":8},{"max_area_ha":null,"base_tolerance":10}]'></textarea>
                                        <small class="text-muted">Toleransi tepi area (meter) berdasar luas polygon kebun (hektar). <code>max_area_ha: null</code> = tanpa batas atas.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="set-label">Aturan Toleransi GPS (JSON)</label>
                                        <textarea class="form-control form-control-sm" rows="2" name="tracking_gps_tolerance_rules" id="tracking_gps_tolerance_rules"
                                            placeholder='[{"max_accuracy":3,"extra_tolerance":0},{"max_accuracy":5,"extra_tolerance":0.5},{"max_accuracy":8,"extra_tolerance":1},{"max_accuracy":12,"extra_tolerance":2},{"max_accuracy":20,"extra_tolerance":3}]'></textarea>
                                        <small class="text-muted">Tambahan toleransi (meter) berdasar akurasi GPS sopir saat ini.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer p-3 text-end">
                                <button class="btn btn-primary" type="button" id="btnSaveApi">
                                    <i class="fa fa-save"></i> {{ __('Simpan API Settings') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ======================= APP VERSION SETTINGS ======================= --}}
                    <form method="post" name="form_app_version" id="form_app_version">
                        @csrf
                        <div id="app-version-settings" class="card">
                            <div class="card-header p-3">
                                <h5>{{ __('App Version / Force Update') }}</h5>
                                <small class="text-muted">{{ __('Untuk Force Update App') }}</small>
                            </div>
                            <div class="card-body p-3">
                                <div class="row row-gap-3">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">{{ __('Minimum Build Number') }}</label>
                                            <input class="form-control" placeholder="Contoh: 30"
                                                name="wgidriver_min_build" type="number" min="0"
                                                value="{{ old('wgidriver_min_build', $setting->wgidriver_min_build ?? '') }}" id="wgidriver_min_build">
                                            <small class="text-muted">Device dengan build number (versionCode) di bawah angka ini akan diminta update. Lihat build number saat ini di <code>pubspec.yaml</code> pada repo app (mis. <code>2.0.30+29</code> → build 29). Kosongkan untuk mematikan pengecekan.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">{{ __('Versi Terbaru (tampilan)') }}</label>
                                            <input class="form-control" placeholder="Contoh: 2.0.31"
                                                name="wgidriver_latest_version" type="text"
                                                value="{{ old('wgidriver_latest_version', $setting->wgidriver_latest_version ?? '') }}" id="wgidriver_latest_version">
                                            <small class="text-muted">Cuma ditampilkan di dialog update, tidak dipakai untuk perbandingan.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row-gap-3 mt-1">
                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">{{ __('Link Update') }}</label>
                                            <input class="form-control" placeholder="https://play.google.com/store/apps/details?id=com.wgi.driver"
                                                name="wgidriver_update_url" type="text"
                                                value="{{ old('wgidriver_update_url', $setting->wgidriver_update_url ?? '') }}" id="wgidriver_update_url">
                                            <small class="text-muted">Link Play Store, atau link download APK langsung. Dibuka saat sopir menekan tombol Update.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row-gap-3 mt-1">
                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-dark">{{ __('Pesan Custom (opsional)') }}</label>
                                            <textarea class="form-control" rows="2" placeholder="Kosongkan untuk pakai pesan default aplikasi"
                                                name="wgidriver_update_message" id="wgidriver_update_message">{{ old('wgidriver_update_message', $setting->wgidriver_update_message ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="setting-card setting-logo-box mt-3">
                                    <div class="row row-gap-3">
                                        <div class="col-xxl-4 col-md-4 col-sm-6 col-12">
                                            <div class="card h-100 mb-0">
                                                <div class="card-header p-2">
                                                    <h6 class="mb-0 d-flex align-items-center gap-2">
                                                        <i class="fa fa-exclamation-triangle"></i> {{ __('Update Wajib') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <label class="switcher switcher-block">
                                                        <input type="checkbox" class="switcher-input" name="wgidriver_force_update" value="1"
                                                            {{ ! empty($setting->wgidriver_force_update) ? 'checked' : '' }}>
                                                        <span class="switcher-indicator"></span>
                                                    </label>
                                                    <small class="text-muted d-block mt-1">Aktif: dialog update tidak bisa ditutup, sopir wajib update dulu. Nonaktif: cuma nudge, boleh di-skip ("Nanti").</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-4 col-md-4 col-sm-6 col-12">
                                            <div class="card h-100 mb-0">
                                                <div class="card-header p-2">
                                                    <h6 class="mb-0 d-flex align-items-center gap-2">
                                                        <i class="fa fa-user-slash"></i> {{ __('Menu Tiket Tamu') }}
                                                    </h6>
                                                </div>
                                                <div class="card-body p-2">
                                                    <label class="switcher switcher-block">
                                                        <input type="checkbox" class="switcher-input" name="tracking_guest_ticket_enabled" value="1"
                                                            {{ ($setting->tracking_guest_ticket_enabled ?? 1) ? 'checked' : '' }}>
                                                        <span class="switcher-indicator"></span>
                                                    </label>
                                                    <small class="text-muted d-block mt-1">Aktif (default): tombol "Petani belum terdaftar? Mulai tanpa tiket" tampil di halaman Pelacakan. Nonaktif: tombolnya disembunyikan dari semua app tanpa perlu build ulang APK.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer p-3 text-end">
                                <button class="btn btn-primary" type="button" id="btnSaveAppVersion">
                                    <i class="fa fa-save"></i> {{ __('Simpan App Version Settings') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ======================= GEOFENCE SETTINGS ======================= --}}
                    <div id="geofence-settings" class="card">
                        <div class="card-header p-3">
                            <h5>{{ __('Geofence Settings') }}</h5>
                            <small class="text-muted">{{ __('Titik tujuan tracking & daftar geofence per modul.') }}</small>
                        </div>
                        <div class="card-body p-3">
                            <div class="form-group mb-3">
                                <label class="set-label">Titik Akhir Aktif (tujuan tracking)</label>
                                <div id="destDisplay" class="dest-display text-muted">— Belum dipilih —</div>
                                <input type="hidden" id="dest_geofence_id">
                                <small class="text-muted">Pilih dari geofence modul <b>tracking</b> di bawah lewat tombol <b>Jadikan tujuan</b>.</small>
                            </div>
                            <div class="row">
                                <div class="col-lg-5">
                                    <div class="set-card">
                                        <div class="sc-head"><i class="uil uil-map-marker-alt"></i> Geofence
                                            <small><a href="#" id="btnFitLeft"><i class="uil uil-focus-target"></i> Fit semua</a></small>
                                        </div>
                                        <div class="sc-body" id="geoContainer">
                                            {{-- diisi via JS --}}
                                        </div>
                                    </div>
                                    <div id="pickHint" class="mt-2"></div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="setmap-wrap">
                                        <div class="map-tools">
                                            <button class="btn btn-sm btn-light" id="btnFitMap" title="Lihat semua titik">
                                                <i class="uil uil-focus-target"></i> Fit semua
                                            </button>
                                        </div>
                                        <div id="setMap"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer p-3 text-end">
                            <button class="btn btn-success" id="btnSaveGeofence">
                                <i class="uil uil-save"></i> <span class="btn-save-text">{{ __('Simpan Geofence Settings') }}</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer_js')
<script src="{{ asset('media/plugins/leafletjs/leaflet.js') }}"></script>
<script>
$(document).ready(function() {
    // --- Highlight menu samping sesuai hash aktif ---
    function setActiveMenu() {
        var hash = window.location.hash;
        $('#useradd-sidenav a').removeClass('active');
        if (hash) {
            $('#useradd-sidenav a').each(function() {
                if ($(this).attr('href').indexOf(hash) !== -1) {
                    $(this).addClass('active');
                }
            });
        }
    }
    setActiveMenu();
    $(window).on('hashchange', function() {
        setActiveMenu();
    });

    function simpleSave(btnId, formId, url, successMsg) {
        $('#' + btnId).click(function() {
            var btn = $(this).prop('disabled', true);
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: $('#' + formId).serialize(),
                success: function(res) {
                    btn.prop('disabled', false);
                    if (res && res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Tersimpan', text: successMsg || 'Pengaturan berhasil disimpan.' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.errMsg) ? res.errMsg : 'Gagal menyimpan pengaturan.' });
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menyimpan pengaturan.' });
                }
            });
        });
    }

    // --- API / App Version: form sederhana, serialize + simpan (tidak ada file) ---
    simpleSave('btnSaveApi', 'form_api', '{{ route('admin.setting.system.save_api') }}', 'API Settings berhasil disimpan.');
    simpleSave('btnSaveAppVersion', 'form_app_version', '{{ route('admin.setting.system.save_app_version') }}', 'App Version Settings berhasil disimpan.');

    // --- Preview langsung saat Logo Dark/Light/Favicon dipilih (sebelum disimpan) ---
    $(document).on('change', '#form_app input[type=file][data-preview]', function () {
        var file = this.files && this.files[0];
        var $img = $('#' + $(this).data('preview'));
        if (!file || !$img.length) return;
        var reader = new FileReader();
        reader.onload = function (e) { $img.attr('src', e.target.result); };
        reader.readAsDataURL(file);
    });

    // --- App Settings: ada file upload, jadi kirim FormData (bukan serialize) ---
    $('#btnSaveApp').click(function () {
        var btn = $(this).prop('disabled', true);
        var fd = new FormData(document.getElementById('form_app'));
        $.ajax({
            url: '{{ route('admin.setting.system.save_app') }}',
            type: 'POST',
            dataType: 'json',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.prop('disabled', false);
                if (res && res.status === 'success') {
                    if (res.logo_dark_url) $('#image').attr('src', res.logo_dark_url);
                    if (res.logo_light_url) $('#image1').attr('src', res.logo_light_url);
                    if (res.favicon_url) $('#image2').attr('src', res.favicon_url);
                    Swal.fire({ icon: 'success', title: 'Tersimpan', text: 'App Settings berhasil disimpan.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.errMsg) ? res.errMsg : 'Gagal menyimpan pengaturan.' });
                }
            },
            error: function () {
                btn.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menyimpan pengaturan.' });
            }
        });
    });
});
</script>
<script>
(function () {
    'use strict';

    var SAVE_GEOFENCE_URL = "{{ route('admin.setting.system.save_geofence') }}";
    var SETTING   = @json($setting);
    var GEOFENCES = @json($geofences);

    var DEFAULT = [-6.8726682, 109.3512368];
    var CENTER = [
        (SETTING && SETTING.map_center_lat != null) ? parseFloat(SETTING.map_center_lat) : DEFAULT[0],
        (SETTING && SETTING.map_center_lng != null) ? parseFloat(SETTING.map_center_lng) : DEFAULT[1]
    ];

    var CAT_COLOR = { office:'#1a73e8', plant:'#f59e0b', factory:'#7c3aed', warehouse:'#0d9488', default:'#6b7280' };

    // --- Map ---
    var map = L.map('setMap').setView(CENTER, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
    }).addTo(map);

    var markers = {};   // key -> {marker, circle}
    var pickTarget = null;
    var booting = true;

    function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function val(id){ var el = document.getElementById(id); return el ? el.value.trim() : ''; }
    function setVal(id, v){ var el = document.getElementById(id); if (el) el.value = (v == null ? '' : v); }
    function hint(msg){ document.getElementById('pickHint').textContent = msg || ''; }
    function num(v){ v = parseFloat(v); return isNaN(v) ? null : v; }

    function isValidLatLng(lat, lng) {
        lat = parseFloat(lat); lng = parseFloat(lng);
        return !isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
    }

    function pinIcon(color) {
        return L.divIcon({
            className: 'set-pin',
            html: '<span style="background:' + color + '"></span>',
            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });
    }

    // --- Dirty state (tombol simpan geofence) ---
    function markDirty() {
        if (booting) return;
        var btn = document.getElementById('btnSaveGeofence');
        btn.classList.add('dirty');
        btn.querySelector('.btn-save-text').textContent = 'Ada perubahan — Simpan';
    }
    function markClean() {
        var btn = document.getElementById('btnSaveGeofence');
        btn.classList.remove('dirty');
        btn.querySelector('.btn-save-text').textContent = 'Simpan Geofence Settings';
    }

    // --- Marker helper (draggable) ---
    function onMarkerDrag(key, latlng) {
        var lat = latlng.lat.toFixed(7), lng = latlng.lng.toFixed(7);
        if (key === 'center') { setVal('center_lat', lat); setVal('center_lng', lng); refreshCenter(); }
        else if (key === 'start') { setVal('start_lat', lat); setVal('start_lng', lng); refreshStart(); }
        else if (key.indexOf('geo:') === 0) {
            var id = key.slice(4);
            var row = document.querySelector('.geo-row[data-id="' + id + '"]');
            if (row) { row.querySelector('.geo-lat').value = lat; row.querySelector('.geo-lng').value = lng; refreshGeoMarker(row); }
        }
        markDirty();
    }

    function setMarker(key, lat, lng, opts) {
        opts = opts || {};
        if (lat == null || lng == null || isNaN(lat) || isNaN(lng)) {
            if (markers[key]) { map.removeLayer(markers[key].marker); if (markers[key].circle) map.removeLayer(markers[key].circle); delete markers[key]; }
            return;
        }
        var color = opts.color || '#6b7280';
        if (markers[key]) {
            markers[key].marker.setLatLng([lat, lng]).setIcon(pinIcon(color));
        } else {
            var m = L.marker([lat, lng], { icon: pinIcon(color), draggable: true }).addTo(map);
            m.on('dragend', function () { onMarkerDrag(key, m.getLatLng()); });
            markers[key] = { marker: m, circle: null };
        }
        markers[key].marker.bindTooltip(opts.label || key, { direction: 'top' });
        // lingkaran radius (geofence)
        if (markers[key].circle) { map.removeLayer(markers[key].circle); markers[key].circle = null; }
        if (opts.radius && opts.radius > 0) {
            markers[key].circle = L.circle([lat, lng], { radius: opts.radius, color: color, weight: 1, fillOpacity: .08 }).addTo(map);
        }
    }

    // --- Dest tujuan tracking ---
    function setDest(id, name) {
        setVal('dest_geofence_id', id || '');
        var disp = document.getElementById('destDisplay');
        if (id) { disp.textContent = name || ('#' + id); disp.classList.remove('text-muted'); }
        else { disp.textContent = '— Belum dipilih —'; disp.classList.add('text-muted'); }
        // refresh badges & buttons
        document.querySelectorAll('.geo-row').forEach(function (row) {
            var badge = row.querySelector('.geo-dest-badge');
            var btn = row.querySelector('.geo-dest-btn');
            var isDest = String(row.dataset.id) === String(id);
            if (badge) badge.style.display = isDest ? '' : 'none';
            if (btn) { btn.classList.toggle('btn-primary', isDest); btn.classList.toggle('btn-outline-primary', !isDest);
                       btn.innerHTML = isDest ? '<i class="uil uil-check"></i> Tujuan aktif' : '<i class="uil uil-map-pin"></i> Jadikan tujuan'; }
        });
    }

    // --- Status geofence ---
    function refreshStatus(row) {
        var el = row.querySelector('.geo-status');
        if (!el) return;
        var lat = row.querySelector('.geo-lat').value, lng = row.querySelector('.geo-lng').value;
        var rad = num(row.querySelector('.geo-radius').value);
        var active = row.querySelector('.geo-active').checked;
        el.className = 'geo-status';
        if (!active) { el.classList.add('off'); el.textContent = 'Nonaktif'; return; }
        if (!isValidLatLng(lat, lng)) { el.classList.add('warn'); el.textContent = 'Belum ada koordinat'; return; }
        if (!rad || rad <= 0) { el.classList.add('warn'); el.textContent = 'Radius belum diisi'; return; }
        el.classList.add('ok'); el.textContent = 'Lengkap';
    }

    // --- Render geofence rows, grouped by module ---
    function renderGeofences() {
        var cont = document.getElementById('geoContainer');
        if (!GEOFENCES || !GEOFENCES.length) { cont.innerHTML = '<div class="text-muted">Belum ada geofence.</div>'; return; }

        var groups = {};
        GEOFENCES.forEach(function (g) { (groups[g.module || 'lainnya'] = groups[g.module || 'lainnya'] || []).push(g); });

        var html = '';
        Object.keys(groups).forEach(function (mod) {
            html += '<div class="grp-title">' + esc(mod) + '</div>';
            groups[mod].forEach(function (g) {
                var cat = (g.category || '').toLowerCase();
                var isTracking = (g.module || '') === 'tracking';
                html +=
                    '<div class="geo-row" data-id="' + g.id + '" data-cat="' + esc(cat) + '" data-module="' + esc(g.module || '') + '">' +
                        '<div class="geo-title">' +
                            '<span class="geo-badge">' + esc(g.code || '') + '</span>' +
                            '<input type="text" class="form-control form-control-sm geo-name" value="' + esc(g.name || '') + '" placeholder="Nama lokasi" style="max-width:52%">' +
                            '<span class="geo-dest-badge" style="display:none">TUJUAN</span>' +
                            '<label class="ml-auto mb-0" style="font-size:11px;"><input type="checkbox" class="geo-active" ' + (g.is_active ? 'checked' : '') + '> aktif</label>' +
                        '</div>' +
                        '<div class="geo-grid">' +
                            '<input type="text" class="form-control form-control-sm geo-lat" value="' + esc(g.latitude != null ? g.latitude : '') + '" placeholder="lat">' +
                            '<input type="text" class="form-control form-control-sm geo-lng" value="' + esc(g.longitude != null ? g.longitude : '') + '" placeholder="lng">' +
                            '<input type="text" class="form-control form-control-sm geo-radius" value="' + esc(g.radius != null ? g.radius : '') + '" placeholder="radius m">' +
                        '</div>' +
                        '<div class="geo-radius-wrap">' +
                            '<input type="range" class="form-control-range geo-radius-slider" min="10" max="1000" step="10" value="' + esc(g.radius != null && g.radius > 0 ? g.radius : 100) + '">' +
                            '<span class="geo-status"></span>' +
                        '</div>' +
                        '<div class="geo-foot">' +
                            '<button type="button" class="btn btn-outline-secondary btn-sm" data-pick="geo:' + g.id + '">' +
                                '<i class="uil uil-location-point"></i> Pilih di peta' +
                            '</button>' +
                            (isTracking ? '<button type="button" class="btn btn-outline-primary btn-sm geo-dest-btn"><i class="uil uil-map-pin"></i> Jadikan tujuan</button>' : '') +
                        '</div>' +
                    '</div>';
            });
        });
        cont.innerHTML = html;

        // listener tiap geofence row
        document.querySelectorAll('.geo-row').forEach(function (row) {
            var latEl = row.querySelector('.geo-lat');
            var lngEl = row.querySelector('.geo-lng');
            var radEl = row.querySelector('.geo-radius');
            var sliderEl = row.querySelector('.geo-radius-slider');
            var refresh = function () { refreshGeoMarker(row); refreshStatus(row); markDirty(); };

            latEl.addEventListener('input', refresh);
            lngEl.addEventListener('input', refresh);
            row.querySelector('.geo-name').addEventListener('input', refresh);
            row.querySelector('.geo-active').addEventListener('change', refresh);

            radEl.addEventListener('input', function () { sliderEl.value = radEl.value || 0; refresh(); });
            sliderEl.addEventListener('input', function () { radEl.value = sliderEl.value; refresh(); });

            // klik row -> fokus marker
            row.addEventListener('click', function (e) {
                if (e.target.closest('input, button, label, a')) return;
                document.querySelectorAll('.geo-row.active').forEach(function (r) { r.classList.remove('active'); });
                row.classList.add('active');
                var lat = num(latEl.value), lng = num(lngEl.value);
                if (lat != null && lng != null) map.flyTo([lat, lng], 17);
            });

            // jadikan tujuan
            var destBtn = row.querySelector('.geo-dest-btn');
            if (destBtn) {
                destBtn.addEventListener('click', function () {
                    var cur = val('dest_geofence_id');
                    if (String(cur) === String(row.dataset.id)) { setDest('', ''); }
                    else { setDest(row.dataset.id, row.querySelector('.geo-name').value || row.dataset.id); }
                    markDirty();
                });
            }
        });

        // set dest awal dari setting
        if (SETTING && SETTING.dest_geofence_id) {
            var destRow = document.querySelector('.geo-row[data-id="' + SETTING.dest_geofence_id + '"]');
            setDest(SETTING.dest_geofence_id, destRow ? destRow.querySelector('.geo-name').value : SETTING.dest_geofence_id);
        }
    }

    function refreshGeoMarker(row) {
        var id  = row.dataset.id;
        var cat = row.dataset.cat;
        var lat = num(row.querySelector('.geo-lat').value);
        var lng = num(row.querySelector('.geo-lng').value);
        var rad = num(row.querySelector('.geo-radius').value);
        setMarker('geo:' + id, lat, lng, {
            color: CAT_COLOR[cat] || CAT_COLOR.default,
            label: row.querySelector('.geo-name').value || cat,
            radius: rad
        });
    }

    function refreshCenter() { setMarker('center', num(val('center_lat')), num(val('center_lng')), { color:'#dc2626', label:'Pusat Peta' }); }
    function refreshStart()  { setMarker('start',  num(val('start_lat')),  num(val('start_lng')),  { color:'#16a34a', label: val('start_label') || 'Start' }); }

    function refreshGardenCheckStatus() {
        var el = document.getElementById('tracking_garden_area_check');
        var badge = document.getElementById('gardenCheckStatus');
        if (!el || !badge) return;
        badge.classList.toggle('on', el.checked);
        badge.classList.toggle('off', !el.checked);
        badge.textContent = el.checked ? 'Wajib' : 'Opsional';
    }

    // --- Fit semua titik ---
    function fitAllMarkers() {
        var points = [];
        Object.keys(markers).forEach(function (key) {
            if (markers[key] && markers[key].marker) points.push(markers[key].marker.getLatLng());
        });
        if (points.length) map.fitBounds(L.latLngBounds(points), { padding: [40, 40], maxZoom: 17 });
    }

    // --- Pick mode (dipicu dari tombol "Pilih di peta", baik di App Settings maupun di sini) ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-pick]');
        if (!btn) return;
        e.preventDefault();
        pickTarget = btn.getAttribute('data-pick');
        document.querySelector('.setmap-wrap').classList.add('pick-active');
        hint('Klik di peta untuk menentukan titik: ' + pickTarget);
        // peta-nya ada di bagian Geofence Settings -- scroll ke sana biar kelihatan
        var geoCard = document.getElementById('geofence-settings');
        if (geoCard) { geoCard.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });

    map.on('click', function (e) {
        if (!pickTarget) return;
        var lat = e.latlng.lat.toFixed(7), lng = e.latlng.lng.toFixed(7);
        if (pickTarget === 'center') { setVal('center_lat', lat); setVal('center_lng', lng); refreshCenter(); }
        else if (pickTarget === 'start') { setVal('start_lat', lat); setVal('start_lng', lng); refreshStart(); }
        else if (pickTarget.indexOf('geo:') === 0) {
            var id = pickTarget.slice(4);
            var row = document.querySelector('.geo-row[data-id="' + id + '"]');
            if (row) { row.querySelector('.geo-lat').value = lat; row.querySelector('.geo-lng').value = lng; refreshGeoMarker(row); refreshStatus(row); }
        }
        markDirty();
        pickTarget = null;
        document.querySelector('.setmap-wrap').classList.remove('pick-active');
        hint('');
    });

    // --- Isi field dari setting ---
    if (SETTING) {
        setVal('center_lat', SETTING.map_center_lat);
        setVal('center_lng', SETTING.map_center_lng);
        setVal('start_lat',  SETTING.start_lat);
        setVal('start_lng',  SETTING.start_lng);
        setVal('start_label', SETTING.start_label);

        var gardenCheckEl = document.getElementById('tracking_garden_area_check');
        if (gardenCheckEl) gardenCheckEl.checked = !!SETTING.tracking_garden_area_check;
        refreshGardenCheckStatus();

        setVal('tracking_ticket_max_age_hours', SETTING.tracking_ticket_max_age_hours);
        setVal('tracking_leaving_farm_radius_m', SETTING.tracking_leaving_farm_radius_m);
        setVal('tracking_completion_poll_interval_s', SETTING.tracking_completion_poll_interval_s);
        setVal('tracking_location_post_interval_s', SETTING.tracking_location_post_interval_s);
        setVal('tracking_notification_update_interval_s', SETTING.tracking_notification_update_interval_s);
        setVal('tracking_gps_distance_filter_m', SETTING.tracking_gps_distance_filter_m);
        setVal('tracking_area_max_gps_accuracy_m', SETTING.tracking_area_max_gps_accuracy_m);
        setVal('tracking_area_tolerance_rules', SETTING.tracking_area_tolerance_rules
            ? JSON.stringify(JSON.parse(SETTING.tracking_area_tolerance_rules)) : '');
        setVal('tracking_gps_tolerance_rules', SETTING.tracking_gps_tolerance_rules
            ? JSON.stringify(JSON.parse(SETTING.tracking_gps_tolerance_rules)) : '');
    }
    document.getElementById('center_lat').addEventListener('input', refreshCenter);
    document.getElementById('center_lng').addEventListener('input', refreshCenter);
    document.getElementById('start_lat').addEventListener('input', refreshStart);
    document.getElementById('start_lng').addEventListener('input', refreshStart);
    document.getElementById('start_label').addEventListener('input', refreshStart);
    if (document.getElementById('tracking_garden_area_check')) {
        document.getElementById('tracking_garden_area_check').addEventListener('change', refreshGardenCheckStatus);
    }

    // --- Fit buttons ---
    document.getElementById('btnFitMap').addEventListener('click', fitAllMarkers);
    document.getElementById('btnFitLeft').addEventListener('click', function (e) { e.preventDefault(); fitAllMarkers(); });

    // --- Boot ---
    renderGeofences();
    refreshCenter();
    refreshStart();
    document.querySelectorAll('.geo-row').forEach(function (row) { refreshGeoMarker(row); refreshStatus(row); });
    booting = false;

    // --- Save (khusus Geofence Settings: dest_geofence_id + daftar geofence) ---
    document.getElementById('btnSaveGeofence').addEventListener('click', function () {
        var geofences = [];
        var invalid = null;
        document.querySelectorAll('.geo-row').forEach(function (row) {
            var lat = row.querySelector('.geo-lat').value.trim();
            var lng = row.querySelector('.geo-lng').value.trim();
            if (!invalid && lat !== '' && !isValidLatLng(lat, lng)) {
                invalid = row.querySelector('.geo-name').value || row.dataset.id;
            }
            geofences.push({
                id:        row.dataset.id,
                name:      row.querySelector('.geo-name').value.trim(),
                latitude:  lat,
                longitude: lng,
                radius:    row.querySelector('.geo-radius').value.trim(),
                is_active: row.querySelector('.geo-active').checked ? 1 : 0
            });
        });
        if (invalid) { hint('Koordinat geofence "' + invalid + '" tidak valid.'); return; }

        var btn = this; btn.disabled = true; hint('Menyimpan…');
        $.ajax({
            url: SAVE_GEOFENCE_URL, type: 'POST', dataType: 'json',
            headers: { 'X-CSRF-TOKEN': AppHelper.csrfToken },
            data: {
                dest_geofence_id: val('dest_geofence_id'),
                geofences: geofences
            },
            success: function (res) {
                btn.disabled = false;
                if (res && res.status === 'success') { markClean(); hint('Tersimpan.'); }
                else { hint((res && res.errMsg) ? res.errMsg : 'Gagal menyimpan.'); }
            },
            error: function () { btn.disabled = false; hint('Gagal menyimpan.'); }
        });
    });

})();
</script>
@endsection
