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
                                    <h3 class="text-theme">Redefinir Senha</h3>
                                    <p class="text-muted">Preencha o formulário e instruções serão enviadas ao seu e-mail</p>
                                </div>

                                <div class="p-2">
                                    @if (session('status'))
                                        <div class="alert alert-success text-center mb-4" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    @include('components.alerts')

                                    <form class="form-horizontal" method="POST" action="{{ route('passwordSendResetLinkURL') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="useremail" class="form-label d-none">E-mail</label>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="useremail"
                                                name="email" placeholder="Digite aqui o e-mail registrado no {{appName()}}" value="{{ old('email') }}"
                                                id="email" maxlength="150" required autofocus>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{-- $message --}}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="text-end">
                                            <button class="btn btn-theme w-100" type="submit">Requisitar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        <div class="mt-4 card border-1 border-light bg-body">
                            <div class="card-body text-center">
                                Espere, eu lembrei minha senha... <a href="{{route('loginURL')}}" class="fw-semibold text-theme text-decoration-underline"> Login </a>
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
