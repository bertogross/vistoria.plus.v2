
<?php
    use App\Models\User;
    $countActive = 0;
    //appPrintR($users);
?>
<button id="btn-add-user" type="button" class="btn btn-sm btn-label right btn-outline-theme float-end" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Adicionar Usuário">
    Adicionar<i class="ri-user-add-line label-icon align-middle fs-16 ms-2"></i>
</button>

<h4 class="mb-0">Usuários Conectados</h4>

<p>Expanda sua equipe no <?php echo e(appName()); ?> adicionando novos membros para potencializar a colaboração e a produtividade</p>

<div class="row mt-4">

    <?php if($users->isNotEmpty()): ?>
        <div class="table-responsive">
            <table class="table align-middle table-nowrap table-bordered table-striped">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th scope="col">Usuário</th>
                        <th scope="col" width="140">Desde</th>
                        <th scope="col">Nível</th>
                        
                        <th scope="col">Status</th>
                        
                        <th scope="col" width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $userId = $user->user_id;
                            $role = $user->role ?? 4;
                            $roleName = User::getRoleName($role);
                            $status = $user->status ?? 'inactive';
                                $countActive += $status == 'active' ? 1 : 0;
                            $since = $user->since ?? null;
                            //$companies = $user->companies ?? getActiveCompanieIds();
                            $profileUrl = route('profileShowURL', ['id' => $userId]) . '?d=' . now()->timestamp;
                            $getUserData = getUserData($userId);
                            $avatar = $getUserData->avatar;
                            $name = $getUserData->name;
                            $email = $getUserData->email;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="<?php echo e(checkUserAvatar($avatar)); ?>" alt=""
                                            class="avatar-xs rounded-circle" loading="lazy">
                                    </div>
                                    <div class="flex-grow-1" style="line-height: 16px;">
                                        <?php echo e($name); ?>

                                        <div class="small text-muted"><?php echo e($email); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($since ? date("d/m/Y H:i", strtotime($since)) : ''); ?></td>
                            <td><?php echo e($roleName); ?></td>
                            
                            <td>
                                <?php switch($status):
                                    case ('active'): ?>
                                        <span class="text-success">
                                            <i class="ri-checkbox-circle-line fs-17 align-middle"></i> Ativo
                                        </span>
                                        <?php break; ?>

                                    <?php case ('inactive'): ?>
                                        <span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Inoperante pois foi por você desativado">
                                            <i class="ri-close-circle-line fs-17 align-middle"></i> Inativo
                                        </span>
                                        <?php break; ?>

                                    <?php case ('revoked'): ?>
                                        <span class="text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Quando o usuário revogou a conexão">
                                            <i class="ri-alert-line fs-17 align-middle"></i> Desconectado
                                        </span>
                                        <?php break; ?>

                                    <?php case ('waiting'): ?>
                                        <span class="text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Aguardando o aceite de seu convite">
                                            <i class="ri-information-line fs-17 align-middle"></i> Aguardando
                                        </span>
                                        <?php break; ?>

                                    <?php default: ?>
                                        <span class="text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Aguardando o aceite de seu convite">
                                            <i class="ri-information-line fs-17 align-middle"></i> Aguardando
                                        </span>
                                <?php endswitch; ?>
                            </td>
                            
                            <td>
                                <button type="button" class="btn btn-sm btn-soft-dark btn-edit-user ri-edit-line" data-user-id="<?php echo e($userId ?? ''); ?>" data-user-title="<?php echo e($name ?? ''); ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Editar"></button>
                                <a href="<?php echo e($profileUrl); ?>" class="btn btn-sm btn-soft-dark ri-eye-line" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Visualizar Tarefas"></a>
                                

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                
            </table>
        </div>
    <?php else: ?>
        <?php $__env->startComponent('components.nothing'); ?>
            <?php $__env->slot('text', 'Ainda não há membros na equipe de '.getCurrentConnectionName().''); ?>
        <?php echo $__env->renderComponent(); ?>
    <?php endif; ?>

</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/settings/stripe/users.blade.php ENDPATH**/ ?>