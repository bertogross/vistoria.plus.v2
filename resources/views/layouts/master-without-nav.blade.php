<!doctype html>
<html class="no-focus" moznomarginboxes mozdisallowselectionprint lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="horizontal" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark" data-layout-style="default" data-layout-width="fluid" data-layout-position="fixed" data-preloader="enable" data-bs-theme="dark">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title') | {{appName()}}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="Expires" content="-1">
        <meta name="robots" content="noindex,nofollow,nopreview,nosnippet,notranslate,noimageindex,nomediaindex,novideoindex,noodp,noydir">
        <meta content="{{ appDescription() }}" name="description" />
        <meta property="og:image" content="{{ URL::asset('build/images/logo-sm.png') }}">
        <meta name="author" content="{{appName()}}" />
        <meta name="theme-color" content="#1a1d21" />
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="icon" type="image/png" href="{{ URL::asset('build/images/logo-sm.png') }}">
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicons/favicon.ico')}}">
        <link rel="manifest" href="{{ URL::asset('build/json/manifest.json') }}">
        @include('layouts.head-css')
    </head>

    @yield('body')

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

    </body>
</html>
