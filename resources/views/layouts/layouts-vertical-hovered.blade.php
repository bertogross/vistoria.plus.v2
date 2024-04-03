<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-layout-style="default"
    data-layout-position="fixed" data-topbar="light" data-sidebar="dark" data-sidebar-size="sm-hover"
    data-layout-width="fluid">
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
        <meta content="{{ appDescription() }}" name="description" />
        <meta property="og:image" content="{{ URL::asset('build/images/logo-sm.png') }}">
        <meta name="author" content="{{appName()}}" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicons/favicon.ico')}}">

        @laravelPWA

        @include('layouts.head-css')
    </head>
    <body>
        <!-- Begin page -->
        <div id="layout-wrapper">

            @include('layouts.topbar')
            @include('layouts.sidebar')
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                <div class="page-content">
                    <!-- Start content -->
                    <div class="container-fluid">
                        @yield('content')
                    </div> <!-- content -->
                </div>
                @include('layouts.footer')
            </div>
            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->

        @include('layouts.vendor-scripts')
    </body>
</html>
