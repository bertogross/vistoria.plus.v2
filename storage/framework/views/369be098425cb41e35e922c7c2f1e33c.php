
<div class="card mt-4">
    <div class="card-body p-4">
        <div class="text-center mt-2">
            <h4 class="text-theme">Login</h4>
            
        </div>
        <div class="p-2 mt-4">

            <form id="loginForm" class="no-enter-submit" action="<?php echo e(route('login')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="host_user_id" value="<?php echo e(isset($hostUserId) ? $hostUserId : ''); ?>">
                

                <div class="mb-3">
                    <label for="username" class="form-label">E-mail</label>
                    <input type="text" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(isset($guestUserEmail) ? $guestUserEmail : old('email', '')); ?>" id="username" name="email" placeholder="Informe o e-mail" required>
                    <?php $__errorArgs = ['email'];
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
                    <div class="float-end">
                        <a href="<?php echo e(route('passwordRequestFormURL')); ?>" class="text-muted small">Esqueceu a senha?</a>
                    </div>
                    <label class="form-label" for="password-input">Senha</label>
                    <div class="position-relative auth-pass-inputgroup mb-3">
                        <input type="password" class="form-control password-input pe-5 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password" placeholder="Senha aqui" id="password-input" maxlength="20" required>
                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
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
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                    <label class="form-check-label" for="auth-remember-check">Manter conexão</label>
                </div>

                <div class="mt-4">
                    <button id="btn-login" class="btn btn-theme w-100" type="submit">Entrar</button>
                </div>

                
            </form>
        </div>
    </div>
    <!-- end card body -->
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/auth/login-card.blade.php ENDPATH**/ ?>