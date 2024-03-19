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
                        <div class="card mt-4">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h4 class="text-primary">Redefição de Senha</h4>
                                    <p class="text-muted">Informe a nova senha de acesso para login <u>{{$email}}</u></p>
                                </div>

                                <div class="alert border-0 alert-warning text-center mb-2 mx-2 p-1" role="alert">
                                    A nova senha deverá conter entre 8 e 20 caracteres.
                                </div>

                                @include('components.alerts')

                                <div class="p-2">
                                    <form class="form-horizontal" method="POST" action="{{ route('passwordResetURL') }}">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        <input type="hidden" name="email" value="{{ $email }}">

                                        <div class="mb-3">
                                            <label for="password-input">Nova Senha</label>
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input type="password" class="form-control @error('password') is-invalid @enderror password-input" name="password" id="password-input" placeholder="Digite aqui" minlength="8" maxlength="20" required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                            {{--
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{!! $message !!}</strong>
                                            </span>
                                            @enderror
                                            --}}
                                        </div>

                                        <div class="mb-3">
                                            <label for="password-input-2">Repita a Senha</label>
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input id="password-input-2" type="password" name="password_confirmation" class="form-control password-input" placeholder="Enter confirm password" minlength="8" maxlength="20" required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon-2"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>

                                        <div>
                                            <button class="btn btn-theme w-100" type="submit">Prosseguir</button>
                                        </div>
                                    </form><!-- end form -->
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        <div class="mt-4 text-center">
                            <p class="mb-0">Espere, eu lembrei minha senha... <a href="{{route('loginURL')}}" class="fw-semibold text-primary text-decoration-underline"> Login </a> </p>
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
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
@endsection
