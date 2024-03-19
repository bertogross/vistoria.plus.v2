@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.password-reset')
@endsection
@section('content')
<div class="auth-page-wrapper pt-5">

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="{{ url('/') }}" class="init-loader d-inline-block auth-logo">
                                <img src="{{ URL::asset('build/images/logo-light.png')}}" alt="{{appName()}}" height="39" loading="lazy">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="auth-full-page-content rounded d-flex p-3 my-2">
                                <div class="w-100">
                                    <div class="d-flex flex-column h-100">
                                        <div class="auth-content my-auto">
                                            <div class="text-center">
                                                <div class="avatar-md mx-auto">
                                                    <div class="avatar-title rounded-circle bg-light">
                                                        <i class="bx bx-mail-send h2 mb-0 text-primary"></i>
                                                    </div>
                                                </div>
                                                <div class="mt-4">
                                                    <h4>Ok!</h4>
                                                    <p class="text-muted">Uma mensagem contendo instruções para redefinição da senha foi enviada para <span class="text-theme">{{$email}}</span></p>
                                                    <div class="mt-4">
                                                        <a href="{{route('loginURL')}}" class="btn btn-theme w-100">Login</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->

    <!-- footer -->
    <footer class="footer d-none d-lg-block d-xl-block">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">&copy; {{date('Y')}} {{appName()}}</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- end Footer -->
</div>
@endsection
