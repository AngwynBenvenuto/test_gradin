@php
    $isAdmin = request()->is('admin*');
    $isDocs  = request()->is('docs*');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js material-style {{ ($isAdmin || $isDocs) ? 'layout-fixed layout-navbar-fixed' : '' }}" dir="ltr">
<head>
    @stack('meta')
    @stack('favicon')
    @stack('page_title')
    @stack('script')
</head>
<body
    @if(!($isAdmin || $isDocs))
        data-bs-spy="scroll"
        data-bs-target="#navbar-navlist"
        data-bs-offset="71"
    @endif
>
    @yield('main_content')    
    @stack('footer_js')
</body>
</html>
