<?php
    $user = auth()->user();
    $userId = $user->id ?? null;
    if($userId){
        $userName = $user->name;

        $hostConnections = getHostConnections();

        $currentConnectionId = getCurrentConnectionByUserId($userId);
        $currentConnectionName = getConnectionNameById($currentConnectionId);
        $currentConnectionRoleName = getCurrentConnectionUserRoleName();

        $countSurveyAssignmentSurveyorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentSurveyorTasks($userId, ['new', 'pending', 'in_progress']);
        $countSurveyAssignmentAuditorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentAuditorTasks($userId, ['new', 'pending', 'in_progress']);

        $profileUrl = $userId ? route('profileShowURL', ['id' => $userId]) . '?d=' . now()->timestamp : route('profileShowURL');

        $companyLogo = getCompanyLogo();
    }
?>
<?php if($userId): ?>
    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box horizontal-logo">
                        <a href="<?php echo e(url('/')); ?>" class="init-loader logo logo-dark" title="Ir para inicial do <?php echo e(appName()); ?>">
                            <span class="logo-sm">
                                <?php if($companyLogo): ?>
                                    <img src="<?php echo e($companyLogo); ?>" alt="<?php echo e(appName()); ?>" height="31" loading="lazy">
                                <?php else: ?>
                                    <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="<?php echo e(appName()); ?>" height="31" loading="lazy">
                                <?php endif; ?>
                            </span>
                            <span class="logo-lg">
                                <?php if($companyLogo): ?>
                                    <img src="<?php echo e($companyLogo); ?>" alt="<?php echo e(appName()); ?>" height="31" loading="lazy">
                                <?php else: ?>
                                    <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="<?php echo e(appName()); ?>" width="202" height="31" loading="lazy">
                                <?php endif; ?>
                            </span>
                        </a>

                        <a href="<?php echo e(url('/')); ?>" class="init-loader logo logo-light" title="Ir para inicial do <?php echo e(appName()); ?>">
                            <span class="logo-sm">
                                <?php if($companyLogo): ?>
                                    <img src="<?php echo e($companyLogo); ?>" alt="<?php echo e(appName()); ?>" height="31" loading="lazy">
                                <?php else: ?>
                                    <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="<?php echo e(appName()); ?>" height="31" loading="lazy">
                                <?php endif; ?>
                            </span>
                            <span class="logo-lg">
                                <?php if($companyLogo): ?>
                                    <img src="<?php echo e($companyLogo); ?>" alt="<?php echo e(appName()); ?>" height="31" loading="lazy">
                                <?php else: ?>
                                    <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="<?php echo e(appName()); ?>" width="202" height="31" loading="lazy">
                                <?php endif; ?>
                            </span>
                        </a>
                    </div>

                    <?php if( Request::is('settings*') ): ?>
                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center">
                    <!--
                    <div class="dropdown d-md-none topbar-head-dropdown header-item">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-search fs-22"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                            <form class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    -->

                    <div class="dropdown topbar-head-dropdown ms-1 header-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Módulos">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Módulos">
                            <i class='bx bx-category-alt fs-22'></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end">
                            

                            <div class="p-2">
                                <div class="row g-0">
                                    <div class="col">
                                        <a class="dropdown-icon-item <?php echo e($userId == $currentConnectionId ? ' init-loader' : ''); ?>"
                                        <?php if($userId != $currentConnectionId): ?>
                                            onclick="alert('Para acessar seus Checklists, alterne para conta Principal')"
                                        <?php else: ?>
                                            href="<?php echo e(route('surveysIndexURL')); ?>"
                                        <?php endif; ?>
                                        data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="bottom" title="Acessar a sessão Checklists da conta <strong><?php echo e($currentConnectionName); ?></strong>">
                                            <i class="ri-checkbox-line text-theme fs-1"></i>
                                            <span>Checklists</span>
                                        </a>
                                    </div>

                                    <div class="col">
                                        <a class="dropdown-icon-item init-loader" href="<?php echo e(route('teamIndexURL')); ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="bottom" title="Listar membros da Equipe <strong><?php echo e($currentConnectionName); ?></strong>">
                                            <i class="ri-team-line text-theme fs-1"></i>
                                            <span>Equipe</span>
                                        </a>
                                    </div>

                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    

                    <div class="ms-1 header-item">
                        <button type="button" id="btn-light-dark-mode" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"  data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Alternar Visual">
                            <i class="bx bx-moon fs-22"></i>
                        </button>
                    </div>

                    <?php $__env->startComponent('components.notifications'); ?>
                        <?php $__env->slot('url'); ?>
                            
                        <?php $__env->endSlot(); ?>
                    <?php echo $__env->renderComponent(); ?>

                    <?php if($hostConnections->isNotEmpty()): ?>
                        <div class="dropdown ms-1 topbar-head-dropdown header-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="left" title="Conexão atual: <u><?php echo e($currentConnectionName); ?></u>">
                            <button type="button" class="btn btn-sm btn-outline-<?php echo e($currentConnectionId != $userId ? 'theme' : 'light'); ?> btn-label text-body-secondary bg-light-subtle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-share-line label-icon align-middle fs-16 me-2"></i> <?php echo e($currentConnectionName); ?>

                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <ul class="list-unstyled ps-3 pe-3 mb-0" style="min-width: 270px;">
                                    <h6 class="dropdown-header mb-2 ps-0">Alternar Conexões</h6>

                                    <li class="form-check form-switch form-switch-theme mb-0" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="Alternar para a conta Principal, <strong><?php echo e($userName); ?></strong>">
                                        <input id="toggle-connection-<?php echo e($userId); ?>" class="form-check-input <?php echo e($currentConnectionId != $userId ? 'toggle-connection' : ''); ?>" type="radio" role="switch" name="connection" value="<?php echo e($userId); ?>" <?php echo e(!$hostConnections || $currentConnectionId == $userId ? 'checked' : ''); ?>>
                                        <label class="form-check-label w-100 text-uppercase" for="toggle-connection-<?php echo e($userId); ?>">
                                            <span class="badge border border-dark text-body float-end ms-2 me-4 float-end ms-2 fs-10">
                                                Principal
                                            </span>
                                            <?php echo e($userName); ?>

                                        </label>
                                    </li>

                                    <?php $__currentLoopData = $hostConnections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $connection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $hostUserId = $connection->user_id;
                                            $hostUserName = getConnectionNameById($hostUserId);

                                            $questStatus = $connection->status;
                                            $questRole = $connection->role;
                                            $questRoleName = \App\Models\User::getRoleName($questRole);
                                        ?>
                                        <li class="form-check form-switch form-switch-theme mt-4" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="left" title="Alternar para <strong><?php echo e($hostUserName); ?></strong>">

                                            <input
                                            id="toggle-connection-<?php echo e($hostUserId); ?>"
                                            class="form-check-input <?php echo e($hostUserId != $currentConnectionId ? 'toggle-connection' : ''); ?>"
                                            type="radio"
                                            role="switch"
                                            name="connection"
                                            <?php echo e($questStatus != 'active' ? 'disabled' : ''); ?>

                                            value="<?php echo e($hostUserId); ?>" <?php echo e($hostUserId == $currentConnectionId ? 'checked' : ''); ?>>

                                            <label class="form-check-label w-100 text-uppercase" for="toggle-connection-<?php echo e($hostUserId); ?>">
                                                <?php
                                                    $dataBs = 'data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="bottom"';
                                                ?>
                                                <?php switch($questStatus):
                                                    case ('waiting'): ?>
                                                        <span class="position-absolute text-warning float-end me-0 end-0 ri-question-line align-middle mt-n1 fs-16 btn-accept-invitation cursor-pointer"
                                                        <?php echo $dataBs; ?>

                                                        data-bs-title="Status da Conexão"
                                                        data-bs-content="Aguardando seu consentimento para acesso ao <strong><?php echo e($hostUserName); ?></strong>.<br>Clique para aceitar." data-host-id="<?php echo e($hostUserId); ?>" data-host-name="<?php echo e($hostUserName); ?>"></span>
                                                        <?php break; ?>
                                                    <?php case ('inactive'): ?>
                                                        <span class="position-absolute text-danger float-end me-0 end-0 ri-question-line align-middle mt-n1 fs-16"
                                                        <?php echo $dataBs; ?>

                                                        data-bs-title="Status da Conexão"
                                                        data-bs-content="<strong class='text-danger'>Inoperante</strong><br><br>Consulte <u><?php echo e($hostUserName); ?></u> quanto da viabilidade de reativação"></span>
                                                        <?php break; ?>
                                                    <?php case ('revoked'): ?>
                                                        <span class="position-absolute text-warning float-end me-0 end-0 ri-question-line align-middle mt-n1 fs-16"
                                                        <?php echo $dataBs; ?>

                                                        data-bs-html="true"
                                                        data-bs-title="Status da Conexão"
                                                        data-bs-content="<strong class='text-warning'>Revogado</strong><br><br>Você revogou seu acesso.<br><br>Para reconectar acesse Configurações Gerais >> Minhas Conexões"></span>
                                                        <?php break; ?>
                                                    <?php default: ?>
                                                        <span class="position-absolute text-success float-end me-0 end-0 ri-checkbox-circle-line align-middle mt-n1 fs-16"
                                                        <?php echo $dataBs; ?>

                                                        data-bs-title="Status da Conexão"
                                                        data-bs-content="Seu conexão a conta de <u><?php echo e($hostUserName); ?></u> está <span class='text-success'>Ativa</span>"></span>
                                                <?php endswitch; ?>

                                                <span class="badge border border-dark text-body float-end ms-2 me-4"
                                                data-bs-toggle="tooltip"
                                                data-bs-html="true"
                                                data-bs-placement="top"
                                                title="Relacionado a conta <strong><?php echo $hostUserName; ?></strong>, o seu Nível possui a permissão para realizar: <strong><?php echo e($questRoleName); ?></strong>">
                                                    <?php echo e($questRoleName); ?>

                                                </span>

                                                <?php echo $hostUserName; ?>

                                            </label>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>

                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="dropdown ms-sm-3 header-item topbar-user">
                        <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <span class="position-absolute translate-middle badge border border-light rounded-circle bg-theme p-1 <?php echo e($countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks > 0 ? 'blink' : 'd-none'); ?>" style="margin-left: 30px;margin-top: 15px;" title="Tarefas Pendentes"><span class="visually-hidden"><?php echo e($countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks); ?> Tarefas Pendentes</span></span>
                                <img class="rounded-circle header-profile-user avatar-img" src="<?php echo e(checkUserAvatar($user->avatar)); ?>" alt="Avatar" loading="lazy">
                                <span class="text-start ms-xl-2">
                                    <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?php echo e($userName); ?></span>
                                    <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="Este é seu nível de autorização relacionado a conta <strong><?php echo e($currentConnectionName); ?><strong>"><?php echo e($currentConnectionRoleName); ?></span>
                                </span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!--
                            <h6 class="dropdown-header">Welcome Anna!</h6>

                            <a class="dropdown-item" href="pages-profile"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>

                            <a class="dropdown-item" href="apps-chat"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>

                            <a class="dropdown-item" href="apps-tasks-kanban"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a>

                            <a class="dropdown-item" href="pages-faqs"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a>
                            <div class="dropdown-divider"></div>
                            -->

                            <a class="dropdown-item init-loader" href="<?php echo e(route('profileShowURL')); ?>">
                                <i class="ri-todo-fill text-muted fs-16 align-middle me-1"></i>
                                <span class="align-bottom">
                                    Minhas Tarefas
                                    <?php if($countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks > 0): ?>
                                        <span class="badge border border-theme text-body ms-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Tarefas por executar"><?php echo e($countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks); ?><span class="visually-hidden">tasks</span></span>
                                    <?php endif; ?>
                                </span>
                            </a>

                            <?php if(in_array(getUserRoleById($userId, $currentConnectionId), [1,2])): ?>
                                <a class="dropdown-item init-loader" href="<?php echo e(route('surveysAuditIndexURL', $userId)); ?>">
                                    <i class="ri-fingerprint-2-line text-muted fs-16 align-middle me-1"></i>
                                    <span class="align-middle">
                                        Minhas Auditorias
                                        <?php if($countSurveyAssignmentAuditorTasks > 0): ?>
                                            <span class="badge border border-theme text-body ms-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Auditorias por executar"><?php echo e($countSurveyAssignmentAuditorTasks); ?><span class="visually-hidden">tasks</span></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            <?php endif; ?>


                            <!--
                            <a class="dropdown-item" href="auth-lockscreen-basic"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
                            -->

                            <a class="dropdown-item <?php echo e($userId == $currentConnectionId ? ' init-loader' : ''); ?>"
                                <?php if($userId != $currentConnectionId): ?>
                                    onclick="alert('Para acessar Configurações, alterne para conta Principal')"
                                <?php else: ?>
                                    href="<?php echo e(route('settingsAccountShowURL')); ?>"
                                <?php endif; ?>
                                >
                                <i class="ri-settings-4-fill text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">Configurações Gerais</span>
                            </a>

                            <button class="dropdown-item" id="pwa_install_button" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Instalar <?php echo e(appName()); ?> em seu Dispositivo">
                                <i class="ri-smartphone-fill text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">Instalar App</span>
                            </button>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item init-loader" href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bx bx-power-off font-size-16 align-middle me-1"></i> <span key="t-logout">Sair</span>
                            </a>
                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                                <?php echo csrf_field(); ?>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </header>
<?php endif; ?>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/layouts/topbar.blade.php ENDPATH**/ ?>