<h4 class="mb-4">Dados da Conta</h4>

<form action="{{ route('settingsAccountStoreOrUpdateURL') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="name">Nome da Instituição:</label>
        <input type="text" name="name" id="name" class="form-control" maxlength="190" value="{{ isset($settings['name']) ? old('name', $settings['name'] ?? '') : '' }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label" for="user_name">Seu Nome Completo:</label>
        <input type="text" name="user_name" id="user_name" class="form-control" value="{{ isset($settings['user_name']) ? old('user_name', $settings['user_name'] ?? '') : '' }}" maxlength="100" required>
        <div class="form-text">O responsável pela administração das configurações desta aplicação</div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="phone">Número do telefone móvel:</label>
        <input type="tel" name="phone" id="phone" class="form-control phone-mask" value="{{ isset($settings['phone']) ? old('phone', formatPhoneNumber($settings['phone']) ?? '') : '' }}" maxlength="16" required>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h4 class="card-title mb-0">Envie o logotipo de sua empresa</h4>
            <small class="form-text">Formato suportado: <strong class="text-theme">JPG/PNG</strong> | Recomendado PNG transparente na dimensão: <span class="text-theme">360</span> x <span class="text-theme">360</span> pixels</small>
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

                    <div class="position-absolute bottom-0 start-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Clique aqui remover logotipo de sua empresa">
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
                            id="logo-img" alt="logo" data-user-id="1" style="max-height: 100px;" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="submit" class="btn btn-theme" value="Atualizar">
</form>
