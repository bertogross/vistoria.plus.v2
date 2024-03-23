<div class="row">
    <div class="col-sm-12 col-md-6">
        <div class="card bg-body h-100">
            <div class="card-body">
                <h4 class="mb-0">Dados da Conta</h4>
                <p>Atualize seus dados cadastrais</p>

                <form id="accountForm" action="{{ route('settingsAccountUpdateURL') }}" method="POST" autocomplete="off" class="no-enter-submit">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Nome da Instituição:</label>
                        <input type="text" name="name" id="name" class="form-control" maxlength="190" value="{{ isset($settings['name']) ? old('name', $settings['name'] ?? '') : '' }}" required>
                    </div>

                    {{--
                    <div class="mb-3">
                        <label class="form-label" for="user_name">Seu Nome Completo:</label>
                        <input type="text" name="user_name" id="user_name" class="form-control" value="{{ isset($settings['user_name']) ? old('user_name', $settings['user_name'] ?? '') : '' }}" maxlength="100" required>
                        <div class="form-text">O responsável pela administração das configurações desta aplicação</div>
                    </div>
                    --}}

                    <div class="mb-3">
                        <label class="form-label" for="phone">Número do telefone móvel:</label>
                        <input type="text" name="phone" id="phone" class="form-control phone-mask" value="{{ isset($settings['phone']) ? old('phone', formatPhoneNumber($settings['phone']) ?? '') : '' }}" maxlength="16" required>
                    </div>

                    <button type="submit" class="btn btn-theme">Atualizar Conta</button>
                </form>

                <hr class="w-50 start-50 position-relative translate-middle-x clearfix mt-4 mb-4">

                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Envie o logotipo de sua unidade/empresa/organização</h4>
                        <small class="form-text">Formato suportado: <strong class="text-theme">JPG/PNG</strong> | Recomendado PNG transparente na dimensão: <span class="text-theme">300</span> x <span class="text-theme">300</span> pixels</small>
                    </div>
                    <div class="card-body">
                        <div class="text-center responses-data-container">
                            <div class="position-relative d-inline-block">
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="logo-image-input" class="mb-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Clique aqui e envie o logotipo de sua empresa">
                                        <div class="avatar-xs">
                                            <div class="avatar-title bg-light border rounded-circle text-muted cursor-pointer">
                                                <i class="ri-image-fill text-theme"></i>
                                            </div>
                                        </div>
                                    </label>
                                    <input class="form-control d-none" name="logo" id="logo-image-input" type="file" accept="image/png, image/jpeg">
                                </div>

                                <div class="position-absolute bottom-0 start-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Clique aqui remover logotipo de sua unidade/empresa/organização">
                                    <div class="avatar-xs">
                                        <div id="btn-delete-logo" class="avatar-title bg-light border rounded-circle text-muted cursor-pointer {{ isset($settings['logo']) && $settings['logo'] ? '' : 'd-none' }}">
                                            <i class="ri-delete-bin-2-line text-danger"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="avatar-lg">
                                    <div class="avatar-title bg-transparent">
                                        <img
                                        @if(isset($settings['logo']) && $settings['logo'])
                                            src="{{ asset('storage/' . $settings['logo']) }}"
                                        @else
                                            src="{{URL::asset('build/images/no-logo.png')}}"
                                        @endif
                                        id="logo-img" alt="logo" data-user-id="1" style="max-height: 100px;" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6">
        <div class="card bg-body h-100">
            <div class="card-body">
                <h4 class="mb-0">Dados de Usuário</h4>
                <p>Altere sua senha ou personalize seu Avatar</p>

                <form id="accountUserForm" action="{{ route('settingsAccountUserUpdateURL') }}" method="POST" autocomplete="off" class="no-enter-submit">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="user_name">E-mail:</label>
                        <span class="form-control cursor-not-allowed text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" title="E-mail não poderá ser modificado">{{$user->email}}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="user_name">Nome Completo:</label>
                        <input type="text" name="user_name" id="user_name" class="form-control" value="{{$user->name}}" maxlength="100" @error('user_name') is-invalid @enderror required>
                        @error('user_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{!! $message !!}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="user_name">Alterar Senha:</label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" class="form-control @error('password') is-invalid @enderror password-input" name="password" id="password-input" placeholder="Digite aqui" minlength="8" maxlength="20">
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{!! $message !!}</strong>
                            </span>
                        @enderror
                        <div class="form-text">
                            Deixe o campo vazio para não modificar.<br>
                            A nova senha deve conter entre 8 e 20 caracteres.
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-theme">Atualizar Usuário</button>
                    </div>
                </form>

                <hr class="w-50 start-50 position-relative translate-middle-x clearfix mt-4 mb-4">

                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Avatar</h4>
                        <small class="form-text">Formato suportado: <strong class="text-theme">JPG</strong> com dimensão: <span class="text-theme">300</span> x <span class="text-theme">300</span> pixels</small>
                    </div>
                    <div class="card-body">
                        <div class="team-profile-img text-center">
                            <div class="avatar-lg profile-user position-relative d-inline-block">
                                <img src="{{ checkUserAvatar($user->avatar) }}" alt="{{$user->name}}" class="img-thumbnail rounded-circle avatar-img" loading="lazy">

                                <div class="avatar-xs p-0 rounded-circle profile-photo-edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" title="Alterar Avatar">
                                    <input class="d-none" name="avatar" id="member-image-input" type="file" accept="image/jpeg">
                                    <label for="member-image-input" class="profile-photo-edit avatar-xs">
                                        <span class="avatar-title rounded-circle bg-light text-body">
                                            <i class="ri-camera-fill"></i>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--

                {!! $user->last_login ? '<p class="small text-muted mb-0">Último Login: '.date('d/m/Y H:i', strtotime($user->last_login)).'</p>' : '' !!}

                <hr>
                <h5>Cover</h5>
                <div class="team-cover" style="min-height: 140px">
                    <img src="{{checkUserCover($user->cover)}}"
                    alt="{{$user->name}}" class="img-fluid" height="140" loading="lazy">
                </div>
                --}}
            </div>
        </div>
    </div>
</div>
