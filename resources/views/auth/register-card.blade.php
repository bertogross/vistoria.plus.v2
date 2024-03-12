<div class="card mt-4 h-100">
    <div class="card-body p-4">
        <div class="text-center mt-2">
            <h4 class="text-theme">Registre-se</h4>
            {{--
            <p class="text-muted">Get your free velzon account now</p>
            --}}
        </div>
        <div class="p-2 mt-4">

            <form id="registerForm" class="no-enter-submit" action="{{ route('register') }}" method="POST">
                @csrf

                <input type="hidden" name="host_user_id" value="{{ isset($hostUserId) ? $hostUserId : '' }}">
                <input type="hidden" name="quest_user_params" value="{{ isset($questUserParams) ? $questUserParams : '' }}">

                <div class="mb-3">
                    <label for="new_useremail" class="form-label">E-mail</label>
                    <input type="email"  class="form-control @error('register_email') is-invalid @enderror" name="register_email" value="{{ isset($questUserEmailFromInvitation) && !$questUserEmail ? $questUserEmailFromInvitation : old('register_email') }}" id="new_useremail" placeholder="Informe seu e-mail" maxlength="150" required>
                    @error('register_email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{!! $message !!}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="new_username" class="form-label">Nome</label>
                    <input type="text" class="form-control @error('register_name') is-invalid @enderror" name="register_name" value="{{ old('register_name') }}" id="new_username" placeholder="Informe seu nome" maxlength="100" required>
                    @error('register_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{!! $message !!}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <p class="mb-0 fs-12 text-muted fst-italic">Antes de registrar-se, leia os <a href="{{env('WEBSITE_URL')}}" class="text-theme text-decoration-underline fst-normal fw-medium" target="_blank">Termos e Políticas</a> do {{appName()}}. Se de acordo, prossiga.</p>
                </div>

                <div class="mt-3">
                    <button id="btn-register" class="btn btn-theme w-100" type="submit">Registrar</button>
                </div>

                {{--
                <div class="mt-3 text-center">
                    <div class="signin-other-title">
                        <h5 class="fs-13 mb-4 title text-muted">Ou...</h5>
                    </div>

                    <div>
                        <button type="button"
                            class="btn btn-primary btn-icon waves-effect waves-light"><i
                                class="ri-facebook-fill fs-16"></i></button>
                        <button type="button"
                            class="btn btn-danger btn-icon waves-effect waves-light"><i
                                class="ri-google-fill fs-16"></i></button>
                        <button type="button"
                            class="btn btn-dark btn-icon waves-effect waves-light"><i
                                class="ri-github-fill fs-16"></i></button>
                        <button type="button"
                            class="btn btn-info btn-icon waves-effect waves-light"><i
                                class="ri-twitter-fill fs-16"></i></button>
                    </div>
                </div>
                --}}
            </form>
        </div>
    </div>
    <!-- end card body -->
</div>
