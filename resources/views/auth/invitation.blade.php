@extends('layouts.master-without-nav')
@section('title')
    Convite
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
                                    <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="{{appName()}}" height="39" loading="lazy">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($hostId && $guestUserEmail)
                    {{--
                    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                        <i class="ri-check-double-line label-icon"></i>
                        Você redebeu um contive para colaborar com <strong class="text-theme">{{$hostUserName}}</strong>.<br>
                        @if (isset($guestUserEmailFromInvitation))
                            O convite foi enviado para <u>{{$guestUserEmailFromInvitation}}</u> . Utilize-o para registro ou informe outro.
                        @elseif(isset($guestUserEmail))
                            O e-mail <u>{{$guestUserEmail}}</u> já consta como conta em nossa base de dados. Utileze-o para Login.
                        @else
                            Registre-se ou efetue Login.
                        @endif
                    </div>

                    @include('components.alerts')
                    --}}

                    <div class="row justify-content-center">
                        <div class="col-sm-6 col-md-6">
                            <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow pe-2 fade show mt-4" role="alert">
                                <i class="ri-check-double-line label-icon"></i>
                                Você redebeu um contive para colaborar com <strong class="text-info">{{$hostUserName}}</strong>.<br>
                                O e-mail de conexão deverá ser o <u>{{$guestUserEmail}}</u> .<br>
                                @if ($guestExists)
                                    Efetue Login!
                                @else
                                    Registre-se!
                                    <br>Se você já possui uma conta, informe a <u>{{$hostUserName}}</u> que o convite deverá ser enviado a outro e-mail.
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        @if ($guestExists)
                            <div class="col-sm-6 col-md-6">
                                @include('auth.login-card')
                            </div>
                        @else
                            <div class="col-sm-6 col-md-6">
                                @include('auth.register-card')
                            </div>
                        @endif

                    </div>
                @else
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                        <i class="ri-error-warning-fill label-icon"></i>
                        O parâmetros deste convite estão incorretos.<br>Clique no link enviado ao seu e-mail ou solicite novamente.
                    </div>
                @endif
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
    <script src="{{ URL::asset('build/js/register.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
