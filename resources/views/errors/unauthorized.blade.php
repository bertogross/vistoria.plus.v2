@extends('layouts.master-without-nav')

@section('title')
    Não autorizado
@endsection

@section('content')
    @php
        $currentUserId = auth()->id();

        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);
        $currentConnectionName = getConnectionNameById($currentConnectionId);
    @endphp
        <div class="auth-page-wrapper pt-5">
            <!-- auth page bg -->
            <div class="auth-one-bg-position auth-one-bg"  id="auth-particles">
                <div class="bg-overlay"></div>

                <div class="shape">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                        <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                    </svg>
                </div>
            </div>

            <!-- auth page content -->
            <div class="auth-page-content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center pt-4">
                                <div class="">
                                    <img src="{{ URL::asset('build/images/file.png') }}" alt="" class="error-basic-img move-animation">
                                </div>
                                <div class="mt-3">
                                    <h3 class="text-uppercase">😭</h3>
                                    <h5 class="text-muted mb-4">Você não possui autorização para acessar a conexão <strong>{{$currentConnectionName}}</strong></h5>
                                    <a href="{{ url('/') }}" class="init-loader btn btn-theme"><i class="mdi mdi-home me-1"></i>Voltar ao início</a>
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
        <!-- end auth-page-wrapper -->

@endsection
@section('script')
<!-- particles js -->
<script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
<!-- particles app js -->
<script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
@endsection
