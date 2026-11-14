@extends('app')

@push('meta')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="/"/>
@endpush

@push('page_title')
    <title>@yield('title')</title>
@endpush

@push('favicon')
    <link rel="icon" href="{{ asset('media/img/favicon/favicon.ico') }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ asset('media/img/favicon/favicon.ico') }}" type="image/x-icon"/>
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
        echo \Cete\Libraries\CTClientModules::instance()->renderScriptCss();
        echo \Cete\Libraries\CTClientModules::instance()->renderScriptJs();
    ?>

    <style>
        html, body { height: 100%; }
        body { margin: 0; padding: 0; }
    </style>

    @yield('extend_script')
@endpush

@section('main_content')
    <div class="page-loader">
        <div class="bg-primary"></div>
    </div>

    <div class="fullbleed-shell">
        @yield('content')
    </div>

    @yield('footer_js')
@endsection
