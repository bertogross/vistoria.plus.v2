
<div class="card mt-4 h-100">
    <div class="card-body p-4">
        <div class="text-center mt-2">
            <h4 class="text-theme">Login</h4>
            {{--
            <p class="text-muted">Get your free velzon account now</p>
            --}}
        </div>
        <div class="p-2 mt-4">

            <form id="loginForm" class="no-enter-submit" action="{{ route('login') }}" method="POST">
                @csrf

                <input type="hidden" name="host_user_id" value="{{ isset($hostUserId) ? $hostUserId : '' }}">
                <input type="hidden" name="quest_user_params" value="{{ isset($questUserParams) ? $questUserParams : '' }}">

                <div class="mb-3">
                    <label for="username" class="form-label">E-mail</label>
                    <input type="text" class="form-control @error('email') is-invalid @enderror" value="{{ isset($questUserEmail) ? $questUserEmail : old('email', '') }}" id="username" name="email" placeholder="Informe o e-mail" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{!! $message !!}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="float-end">
                        <a href="{{ route('passwordRequestFormURL') }}" class="text-muted small">Esqueceu a senha?</a>
                    </div>
                    <label class="form-label" for="password-input">Senha</label>
                    <div class="position-relative auth-pass-inputgroup mb-3">
                        <input type="password" class="form-control password-input pe-5 @error('password') is-invalid @enderror" name="password" placeholder="Senha aqui" id="password-input" maxlength="20" required>
                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{!! $message !!}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                    <label class="form-check-label" for="auth-remember-check">Manter conexão</label>
                </div>

                <div class="mt-4">
                    <button id="btn-login" class="btn btn-theme w-100" type="submit">Entrar</button>
                </div>

                {{--
                <div class="mt-4 text-center">
                    <div class="signin-other-title">
                        <h5 class="fs-13 mb-4 title">Ou</h5>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger btn-icon waves-effect waves-light w-100"><i class="ri-google-fill fs-16 me-2"></i>Login com Google</button>
                    </div>
                </div>
                --}}
            </form>
        </div>
    </div>
    <!-- end card body -->
</div>
