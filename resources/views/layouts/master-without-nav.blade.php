<!doctype html>
<html class="no-focus" moznomarginboxes mozdisallowselectionprint lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="horizontal" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="enable" data-bs-theme="{{ userLayout() }}" data-layout-width="fluid" data-layout-position="fixed" data-layout-style="default" data-sidebar-visibility="show">
    <head>
        <meta charset="utf-8">
        <title>@yield('title') | {{appName()}}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="robots" content="noindex,nofollow,nopreview,nosnippet,notranslate,noimageindex,nomediaindex,novideoindex,noodp,noydir">

        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="Expires" content="-1">

        <meta content="{{ appDescription() }}" name="description">
        <meta property="og:image" content="{{ URL::asset('build/images/logo-sm.png') }}">
        <meta name="author" content="{{ appName() }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="current-user-id" content="{{ auth()->id() }}">
        <!-- App favicon -->
        <link rel="icon" type="image/png" href="{{ URL::asset('build/images/logo-sm.png') }}">
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicons/favicon.ico')}}">

        @include('layouts.head-css')
    </head>
    <body class="{{ str_contains($_SERVER['SERVER_NAME'], 'development.') ? 'development' : 'production' }}">
        <script>0</script>

        <div id="preloader">
            <div id="status">
                <div class="spinner-border text-theme avatar-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        @yield('content')

        @include('layouts.vendor-scripts')

        <div id="custom-backdrop" class="d-none text-white">
            <div style="display: flex; align-items: flex-end; justify-content: flex-start; height: 100vh; padding: 25px; padding-bottom: 70px;">
                Para continuar navegando enquanto este processo está em andamento, <a href="{{ url('/') }}" target="_blank" class="text-theme me-1 ms-1" title="clique aqui">clique aqui</a> para abrir o {{ appName() }} em nova guia
            </div>
        </div>

        <div id="modalContainer"></div>
        <div id="modalContainer2"></div>
    </body>
</html>
