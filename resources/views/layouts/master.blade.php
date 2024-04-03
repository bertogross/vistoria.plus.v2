@php
    $userTheme = getUserMeta(auth()->id(), 'theme');
@endphp
<!doctype html>
<html class="no-focus" moznomarginboxes mozdisallowselectionprint lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="horizontal" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="enable" data-bs-theme="{{ $userTheme ?? 'dark' }}" data-layout-width="fluid" data-layout-position="fixed" data-layout-style="default" data-sidebar-visibility="show">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title') | {{appName()}}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

        {{--
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="pragma" content="no-cache">
        --}}
        <meta http-equiv="Expires" content="1">

        <meta name="robots" content="noindex,nofollow,nopreview,nosnippet,notranslate,noimageindex,nomediaindex,novideoindex,noodp,noydir">
        <meta property="og:image" content="{{ URL::asset('build/images/logo-sm.png') }}">
        <meta content="{{ appDescription() }}" name="description" />
        <meta name="author" content="{{appName()}}" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicons/favicon.ico')}}">

        @laravelPWA

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

        <!-- Begin page -->
        <div id="layout-wrapper">
            @include('layouts.topbar')
            @include('layouts.sidebar')
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content @if ( Request::is('profile*') )mt-n3 @endif">
                <div class="page-content @if ( Request::is('profile*') )pb-0 @endif">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                @if ( !Request::is('profile*') )
                    @include('layouts.footer')
                @endif
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->

        <!-- JAVASCRIPT -->
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
