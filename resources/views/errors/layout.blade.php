<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <title>@yield('title')</title>
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('media/fonts/nunito/nunito.css') }}"/>
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('media/css/reset.css') }}"/>
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('media/css/bootstrap-4/bootstrap.min.css') }}"/>
    <style>
    body {
        background: url('media/img/bg.jpg');
        color: #838383;
    }
    h3 {
        line-height: 27px;
        font-size: 30px;
    }
    .gray-bg, .bg-muted {
        background-color: #f3f3f4;
    }
    .middle-box {
        max-width: 400px;
        z-index: 100;
        margin: 0 auto;
        padding-top: 40px;
    }
    .middle-box h1 {
        font-size: 170px;
        line-height: 1.1;
        text-align:center;
    }
    .text-center {
        text-align: center;
    }
    .font-bold {
        font-weight: 600;
    }
    </style>
</head>
<body class="gray-bg">
    <div class="middle-box text-center animated fadeInDown">
        <h1>@yield('code')</h1>
        <h3 class="font-bold text-center">@yield('message')</h3>
        <div class="error-desc">
            @yield('note')
        </div>
    </div>
</body>
</html>
