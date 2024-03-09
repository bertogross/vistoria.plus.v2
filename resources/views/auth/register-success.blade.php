@extends('layouts.master-without-nav')
@section('title')
    Registro Realizado
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
                                <a href="index" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="{{appName()}}" height="39">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h4 class="text-theme">Pronto!</h4>
                                    {{--
                                    <p class="text-muted">Get your free velzon account now</p>
                                    --}}
                                </div>
                                <div class="p-2 mt-4">
                                    @include('components.alerts')
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                        
                        <div class="mt-4 text-center">
                            <p class="mb-0">Copiou e salvou seus dados de acesso? <a href="{{ route('loginURL') }}" class="fw-semibold text-theme text-decoration-underline"> Entrar </a></p>
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
    <!-- end auth-page-wrapper -->
@endsection
@section('script')
@endsection
