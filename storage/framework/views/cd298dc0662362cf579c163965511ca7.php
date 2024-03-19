<?php
    use App\Models\User;
    use App\Models\UserConnections;
    use App\Models\SurveyAssignments;

    $userId = $user->id;
    $avatar = $user->avatar;
    $cover = $user->cover;
    $name = $user->name;
    $email = $user->email;

    $profileUrl = route('profileShowURL', ['id' => $userId]) . '?d=' . now()->timestamp;

    $currentConnectionId = getCurrentConnectionByUserId(auth()->id());

    if($userId == $currentConnectionId){
        $status = 'active';
        $userCompanies = getActiveCompanieIds();
        $role = 1;
    }else{
        $connection = UserConnections::getGuestDataFromConnectedHostId($userId, $currentConnectionId);

        $status = $connection->status ?? 'active';
        $userCompanies = $connection->companies ?? getActiveCompanieIds();
        $role = $connection->role ?? 1;
    }

    $roleName = User::getRoleName($role);
?>
<div class="col" data-search-user-id="<?php echo e($userId); ?>" data-search-user-name="<?php echo e($name); ?>">
    <div class="card team-box">
        <div class="team-cover" style="min-height: 130px">
            <img src="<?php echo e(checkUserCover($cover)); ?>" alt="<?php echo e($name); ?>" class="img-fluid" height="130" loading="lazy">
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center team-row">
                <?php if(request()->is('settings/users')): ?>
                    <div class="col team-settings">
                        <div class="row">
                            <div class="col">
                                <div class="flex-shrink-0 me-2">
                                    <!--
                                    <button type="button" class="btn btn-light btn-icon rounded-circle btn-sm favourite-btn "> <i class="ri-star-fill fs-14"></i> </button>
                                    -->
                                </div>
                            </div>
                            <div class="col text-end dropdown">
                                <button type="button"  data-bs-toggle="dropdown" class="btn btn-sm btn-soft-dark ri-more-fill text-theme fs-17 rounded-pill" aria-expanded="false"></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item btn-edit-user cursor-pointer" data-user-id="<?php echo e($userId ?? ''); ?>" data-user-title="<?php echo e($name ?? ''); ?>"><i class="ri-pencil-line me-2 align-bottom text-muted"></i>Editar</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-lg-4 col">
                    <div class="team-profile-img">
                        <div class="avatar-lg img-thumbnail rounded-circle flex-shrink-0">
                            <img src="<?php echo e(checkUserAvatar($avatar)); ?>" alt="<?php echo e($name); ?>" class="member-img img-fluid d-block rounded-circle" loading="lazy">
                        </div>
                        <div class="team-content">
                            <h5 class="fs-16 mb-1 text-uppercase"><?php echo e($name); ?></h5>
                            <h6 class="fs-11 mb-1 text-muted mb-3"><?php echo e($email); ?></h6>
                            <p class="text-muted member-designation mb-0 fw-bold" data-bs-toggle="tooltip" data-bs-placement="top" title="Nível do usuário">
                                <?php echo e($roleName); ?>

                            </p>
                            <p class="text-muted mb-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Status do usuário">
                                <?php if( isset($status) && $status == 'active'): ?>
                                    <span class="text-success">Ativo</span>
                                <?php else: ?>
                                    <span class="text-danger">Inoperante</span>
                                <?php endif; ?>
                            </p>
                            
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col">
                    <div class="row text-muted text-center">
                        <?php
                            $requiredKeys = ['new', 'pending', 'in_progress', 'auditing', 'completed', 'losted'];

                            $countSurveyorTasks = SurveyAssignments::countSurveyAssignmentSurveyorTasks($userId, $requiredKeys);
                            $countAuditorTasks = SurveyAssignments::countSurveyAssignmentAuditorTasks($userId, $requiredKeys);
                        ?>

                        <div class="<?php echo e(in_array($role, [1,2]) || $countAuditorTasks > 0 ? 'col-6' : 'col-12'); ?>">
                            <h5 class="mb-1 tasks-num"><?php echo e($countSurveyorTasks); ?></h5>
                            <p class="text-muted mb-0">Vistorias</p>
                        </div>

                        <?php if( in_array($role, [1,2]) || $countAuditorTasks > 0 ): ?>
                            <div class="col-6 border-end border-end-dashed">
                                <h5 class="mb-1 projects-num"><?php echo e($countAuditorTasks); ?></h5>
                                <p class="text-muted mb-0">Auditorias</p>
                            </div>
                        <?php endif; ?>

                        <div class="col-12 border-end border-end-dashed mt-4">
                            <h6 class="mb-1 projects-num">Unidade<?php echo e(is_array($userCompanies) && count($userCompanies) > 1 ? 's' : ''); ?> Autorizada<?php echo e(is_array($userCompanies) && count($userCompanies) > 1 ? 's' : ''); ?></h6>
                            <?php if(is_array($userCompanies)): ?>
                                <ul class="list-unstyled list-inline text-muted mb-0" style="min-height: 40px;">
                                    <?php $__currentLoopData = $userCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $companyId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="list-inline-item"><i class="ri-store-3-fill text-theme align-bottom me-1"></i><?php echo e(getCompanyNameById($companyId)); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col">
                    <div class="text-end">
                        <a href="<?php echo e($profileUrl); ?>" class="btn btn-light view-btn">Visualizar Tarefas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/team/users-card.blade.php ENDPATH**/ ?>