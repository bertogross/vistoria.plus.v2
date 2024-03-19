<div class="row">
    <div class="col-sm-12 col-md-6">
        <div class="card bg-body h-100">
            <div class="card-body">
                <h4 class="mb-0">Dados da Conta</h4>
                <p>Atualize seus dados cadastrais</p>

                <form id="accountForm" action="<?php echo e(route('settingsAccountUpdateURL')); ?>" method="POST" autocomplete="off" class="no-enter-submit">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Nome da Instituição:</label>
                        <input type="text" name="name" id="name" class="form-control" maxlength="190" value="<?php echo e(isset($settings['name']) ? old('name', $settings['name'] ?? '') : ''); ?>" required>
                    </div>

                    

                    <div class="mb-3">
                        <label class="form-label" for="phone">Número do telefone móvel:</label>
                        <input type="tel" name="phone" id="phone" class="form-control phone-mask" value="<?php echo e(isset($settings['phone']) ? old('phone', formatPhoneNumber($settings['phone']) ?? '') : ''); ?>" maxlength="16" required>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Envie o logotipo de sua empresa</h4>
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

                                    <div class="position-absolute bottom-0 start-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Clique aqui remover logotipo de sua empresa">
                                        <div class="avatar-xs">
                                            <div id="btn-delete-logo" class="avatar-title bg-light border rounded-circle text-muted cursor-pointer <?php echo e(isset($settings['logo']) && $settings['logo'] ? '' : 'd-none'); ?>">
                                                <i class="ri-delete-bin-2-line text-danger"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="avatar-lg">
                                        <div class="avatar-title bg-transparent">
                                            <img
                                            <?php if(isset($settings['logo']) && $settings['logo']): ?>
                                                src="<?php echo e(asset('storage/' . $settings['logo'])); ?>"
                                            <?php else: ?>
                                                src="<?php echo e(URL::asset('build/images/no-logo.png')); ?>"
                                            <?php endif; ?>
                                            id="logo-img" alt="logo" data-user-id="1" style="max-height: 100px;" loading="lazy">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-theme" type="submit">Atualizar Conta</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6">
        <div class="card bg-body h-100">
            <div class="card-body">
                <h4 class="mb-0">Dados de Usuário</h4>
                <p>Altere sua senha ou personalize seu Avatar</p>

                <form id="accountUserForm" action="<?php echo e(route('settingsAccountUserUpdateURL')); ?>" method="POST" autocomplete="off" class="no-enter-submit">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label" for="user_name">E-mail:</label>
                        <span class="form-control cursor-not-allowed text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" title="E-mail não poderá ser modificado"><?php echo e($user->email); ?></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="user_name">Nome Completo:</label>
                        <input type="text" name="user_name" id="user_name" class="form-control" value="<?php echo e($user->name); ?>" maxlength="100" <?php $__errorArgs = ['user_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> required>
                        <?php $__errorArgs = ['user_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="invalid-feedback" role="alert">
                                <strong><?php echo $message; ?></strong>
                            </span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="user_name">Alterar Senha:</label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> password-input" name="password" id="password-input" placeholder="Digite aqui" minlength="8" maxlength="20">
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="invalid-feedback" role="alert">
                                <strong><?php echo $message; ?></strong>
                            </span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-text">
                            Deixe o campo vazio para não modificar.<br>
                            A nova senha deve conter entre 8 e 20 caracteres.
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Avatar</h4>
                            <small class="form-text">Formato suportado: <strong class="text-theme">JPG</strong> com dimensão: <span class="text-theme">300</span> x <span class="text-theme">300</span> pixels</small>
                        </div>
                        <div class="card-body">
                            <div class="team-profile-img text-center">
                                <div class="avatar-lg profile-user position-relative d-inline-block">
                                    <img src="<?php echo e(checkUserAvatar($user->avatar)); ?>" alt="<?php echo e($user->name); ?>" class="img-thumbnail rounded-circle avatar-img" loading="lazy">

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

                    <div class="text-end">
                        <button class="btn btn-theme" type="submit">Atualizar Usuário</button>
                    </div>
                </form>

                
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/settings/account-form.blade.php ENDPATH**/ ?>