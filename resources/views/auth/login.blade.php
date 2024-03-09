@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.signin')
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
                                <a href="{{ url('/') }}" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('build/images/logo-light.png')}}" alt="{{appName()}}" height="39" loading="lazy">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                @include('components.alerts')

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">

                        @include('auth.login-card')

                        <div class="mt-4 text-center">
                            <p class="mb-0">Não possui uma conta? <a href="{{ route('registerURL') }}" class="fw-semibold text-theme text-decoration-underline"> Registre-se </a></p>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
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
@section('script')
    <script src="{{ URL::asset('build/js/login.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
@endsection
