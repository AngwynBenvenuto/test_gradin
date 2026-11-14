<?php
    use Cete\Libraries\CTAdmin;
    use Cete\Libraries\CTClientModules;
    use Cete\Helpers\cmsg;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;

    $user = CTAdmin::appUserData();
    $userId = !empty($user) ? $user['user_id'] : null;
    $nameDisplay = !empty($user) ? $user['first_name'] : null;
    $appTitle = strtoupper(env('APP_NAME', 'CTLAB'));

    // Logo/favicon yang diupload admin lewat Setting > App Settings (kolom
    // logo_dark/favicon) -- try/catch supaya kalau migrasinya belum
    // dijalankan di server ini, layout admin tidak ikut 500 di SEMUA halaman.
    $brandSetting = null;
    try {
        $brandSetting = DB::table('setting')->where('status', 1)->orderBy('id')
            ->select('logo_dark', 'favicon')->first();
    } catch (\Throwable $e) {
        $brandSetting = null;
    }
    $appImageUrl = !empty($brandSetting->logo_dark ?? null)
        ? Storage::disk('public')->url($brandSetting->logo_dark)
        : asset('media/img/logowgi.png');
    $faviconUrl = !empty($brandSetting->favicon ?? null)
        ? Storage::disk('public')->url($brandSetting->favicon)
        : asset('media/img/favicon/favicon.ico');
?>
@extends('app')
@push('meta')
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" >
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="/"/>
@endpush

@push('page_title') 
    <title>@yield('title') | {{ $appTitle }}</title>
@endpush

@push('favicon')
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ $faviconUrl }}" type="image/x-icon"/>
@endpush

@push('script')
    <script type="text/javascript">
        AppHelper = {};
        AppHelper.baseUrl = {!! json_encode(url('/')) !!};
        AppHelper.assetsDirectory = {!! json_encode(url('media')) !!};
        AppHelper.csrfToken = '{{ csrf_token() }}';
        AppHelper.settings = {};
        AppHelper.settings.themeColor = '';
        AppHelper.settings.currency = '';
        AppHelper.settings.currencySymbol = '';
        AppHelper.settings.dateFormat = '';
        AppHelper.settings.timeFormat = '';
        AppHelper.settings.enablePushNotification = '';
        AppHelper.settings.displayLength = '10';
    </script>
    <?php
        echo CTClientModules::instance()->renderScriptCss();
        echo CTClientModules::instance()->renderScriptJs();
    ?>
    <style>
        /* Tema admin set .modal z-index:1090, di atas default SweetAlert2 (1060) —
           naikkan biar SweetAlert selalu tampil di atas modal Bootstrap manapun,
           bukan ketimpa/redup di belakangnya. Berlaku untuk semua halaman admin. */
        div:where(.swal2-container) { z-index: 1100 !important; }
    </style>
    @yield('extend_script')
@endpush

@section('main_content')
    <div class="page-loader">
        <div class="bg-primary"></div>
    </div>
    <?php
        $showNavigation = true;
        $pageWrapperAttr = '';
    ?>
    <div id="wrapper" class="layout-wrapper layout-2">
        <div class="layout-inner">
            <?php if ($showNavigation): ?>
                <nav id="layout-sidenav" class="layout-sidenav sidenav sidenav-vertical bg-sidenav-theme navbar-default navbar-static-side" role="navigation">
                    <div class="app-brand brand">
                        <span class="app-brand-logo brand-logo">
                            <img src="<?php echo $appImageUrl ?>" />
                        </span>
                        <a href="{{ url('/admin') }}" class="app-brand-text brand-name sidenav-text font-weight-normal ml-2"><?php echo $appTitle ?></a>
                        <a href="javascript:void(0)" class="layout-sidenav-toggle sidenav-link text-large ml-auto">
                            <i class="ri-menu-5-line align-middle"></i>
                        </a>
                    </div>
                    <div class="sidenav-divider mt-0"></div>
                    @include('layouts.nav.nav');
                    <?php    
                        //echo CTAdmin::navigation()->render(array('engine'=>'SideNav'));
                    ?>
                </nav>
            <?php endif; ?>

            <div id="page-wrapper" class="layout-container" <?php echo $pageWrapperAttr; ?>>
                <?php if ($showNavigation): ?>
                    <nav class="layout-navbar navbar navbar-expand-lg align-items-lg-center container-p-x bg-navbar-theme font-color-white" id="layout-navbar">
                        <a href="{{ url('/admin') }}" class="nav-brand d-lg-none">
                            <span class="app-brand-logo brand-logo" style="margin:0 7px 0 0">
                                <img src="<?php echo $appImageUrl ?>" />
                            </span>
                            <a class="app-brand-text brand-name d-lg-none font-weight-normal py-0 mr-3"><?php echo $appTitle ?></a>
                        </a>
                        <div class="layout-sidenav-toggle d-lg-none navbar-nav align-items-lg-center mr-auto">
                            <a class="nav-item nav-link px-0 mr-lg-4" href="javascript:void(0)">
                                <i class="ri-menu-5-line text-large align-middle"></i>
                            </a>
                        </div>
                        <button class="navbar-toggler focus:outline-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#layout-navbar-collapse">
                            <span class="fas fa-ellipsis-v text-lg ml-3"></span>
                        </button>
                        <span class="ml-3"></span>
                        <div class="navbar-collapse collapse" id="layout-navbar-collapse" style="">
                            <a href="javascript:void(0)" class="layout-sidenav-toggle sidenav-link text-large"></a>
                            <!-- Divider -->
                            <hr class="d-lg-none w-100 my-2">
                            {{-- <div class="navbar-nav align-items-lg-center ">
                                <label class="nav-item navbar-text navbar-search-box p-0 active">
                                    <i class="ion ion-ios-search navbar-icon align-middle"></i>
                                    <span class="navbar-search-input pl-2">
                                        <input type="text" class="form-control navbar-text mx-2" placeholder="Search..." style="width:200px">
                                    </span>
                                </label>
                            </div> --}}
                            <div class="navbar-nav align-items-lg-center ml-auto">
                                <div id="servertime" class="nav-monitor nav-item d-none d-lg-block mr-lg-2">
                                    <a class="text-center waktu-monitor"></a>
                                </div>
                                <div class="navbar-switcher nav-item ml-lg-1 mr-lg-1">
                                    <a id="toggle-switcher-theme" data-theme-toggle class="nav-link hide-arrow" href="javascript:void(0)" >
                                        <i class="ri-moon-line navbar-icon align-middle"></i>
                                        <span class="d-lg-none align-middle">&nbsp; Switch</span>
                                    </a>
                                </div>
                                {{-- <div class="navbar-notification nav-item dropdown mr-lg-1">
                                    <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                                        <i class="ri-notification-3-line navbar-icon align-middle"></i>
                                        <span class="badge badge-primary badge-dot indicator"></span>
                                        <span class="d-lg-none align-middle">&nbsp; Notifications</span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <div class="bg-primary text-center text-white font-weight-bold p-3">
                                            4 New Notifications
                                        </div>
                                        <div class="list-group list-group-flush">
                                            <a href="javascript:void(0)" class="list-group-item list-group-item-action media d-flex align-items-center">
                                                <div class="ui-icon ui-icon-sm ion ion-md-home bg-secondary border-0 text-white"></div>
                                                <div class="media-body line-height-condenced ml-3">
                                                    <div class="text-body">Login from 192.168.1.1</div>
                                                    <div class="text-light small mt-1">
                                                        Aliquam ex eros, imperdiet vulputate hendrerit et.
                                                    </div>
                                                    <div class="text-light small mt-1">12h ago</div>
                                                </div>
                                            </a>
                                        </div>
                                        <a href="javascript:void(0)" class="d-block text-center text-light small p-2 my-1">Show all notifications</a>
                                    </div>
                                </div>  --}}
                                <div class="navbar-fullscreen nav-item mr-lg-1">
                                    <a id="toggle-fullscreen" class="nav-link hide-arrow" href="javascript:void(0)" >
                                        <i class="ri-fullscreen-line navbar-icon align-middle"></i>
                                        <span class="d-lg-none align-middle">&nbsp; Fullscreen</span>
                                    </a>
                                </div>
                                <div class="nav-item d-none d-lg-block text-big font-weight-light line-height-1 opacity-25 mr-2 ml-1">|</div>
                                <div class="navbar-user nav-item dropdown">
                                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                        <span class="d-inline-flex flex-lg-row-reverse align-items-center align-middle">
                                            <i class="uil-user text-xl"></i>
                                            <span class="px-1 mr-lg-2 ml-2 ml-lg-0 account-user-name">
                                                <?php echo $nameDisplay ?>
                                            </span>
                                        </span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        {{-- <a href="{{ $userId ? route('admin.user.detail', $userId) : '#' }}" class="dropdown-item"><i class="fa fa-user text-light"></i>&nbsp;&nbsp;<?php echo __('My Account'); ?></a>
                                        <a href="{{ $userId ? route('admin.user.account', $userId) : '#' }}" class="dropdown-item"><i class="fa fa-lock text-light"></i>&nbsp;&nbsp;<?php echo __('Change Password'); ?></a> --}}
                                        <a href="{{ route('admin.logout') }}" class="dropdown-item"><i class="ion ion-ios-log-out text-light"></i>&nbsp;&nbsp;<?php echo __('Logout'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>
                <?php endif; ?>

                <div class="main layout-content">
                    <div class="main-inner container-fluid flex-grow-1 container-p-y animated fadeInDown">
                        <div class="row mb-2">
                            <?php if(\Route::is('admin.dashboard')) { } else { ?>
                                <div class="col-lg-12">
                                    <div class="page-title-box">
                                        <div class="page-title-right">
                                            <?php if(\Route::is('admin.dashboard')) { } else { ?>
                                                @if (Breadcrumbs::exists())
                                                    {{ Breadcrumbs::render() }}
                                                @endif
                                            <?php } ?>
                                        </div>
                                        <h4 class="page-title font-weight-bold ">&nbsp;</h4>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                        $msg = cmsg::flash_all();
                        if (strlen($msg) > 0) {
                            echo '<div class="row-fluid"><div class="span12">' . $msg . '</div></div>';
                        }
                        ?>
                        <div class="wrapper wrapper-content">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php if (!$showNavigation): ?>
                                        <div class="container">
                                    <?php endif; ?>
                                            @yield('content')
                                    <?php if (!$showNavigation): ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Layout footer -->
                    <div class="layout-footer footer bg-footer-theme d-none">
                        <div class="container-fluid d-flex flex-wrap justify-content-between text-center container-p-x pb-3">
                            <div class="pt-3">
                                <span class="footer-text font-weight-bolder">
                                    &copy; <?php echo '2024' . '-' . date('Y') ?>. All right Reserved
                                </span>
                            </div>
                        </div> 
                    </div>
                    <!-- / Layout footer -->
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-sidenav-toggle"></div>
    </div>
    <div class="ct-gtop">
        <img src="{{ asset('media/img/back-to-top.png') }}" style="width:51px; height:42px">
    </div>  
    
    @include('template.modal')
    @include('template.cropper')

    @yield('footer_js') 

    <script type="text/javascript">
        setInterval(function () {
            var hours = new Date().getHours();
            var minutes = new Date().getMinutes();
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            $('.waktu-monitor').html(
                days[new Date().getDay()] + ", " +
                ((new Date().getDate() < 10) ? "0" + new Date().getDate() : new Date().getDate()) + " " +
                monthNames[new Date().getMonth()] + " " +
                new Date().getFullYear() + " " +
                ((new Date().getHours() < 10) ? "0" + new Date().getHours() : new Date().getHours()) + ":" +
                ((new Date().getMinutes() < 10) ? "0" + new Date().getMinutes() : new Date().getMinutes()) + ":" +
                ((new Date().getSeconds() < 10) ? "0" + new Date().getSeconds() : new Date().getSeconds())
            );
        }, 1000);

        if (window) {
            window.onload = function () {
                
            }
        }
        //script UA-xxx-xxx google analytics
    </script>
@endsection


