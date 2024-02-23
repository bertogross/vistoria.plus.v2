<?php $__env->startSection('title'); ?>
    <?php echo e($user->name); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php
        $phone = getUserMeta($profileUserId, 'phone');
        $phone = formatPhoneNumber($phone);

        $countSurveyorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentSurveyorTasks($profileUserId);

        $countAuditorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentAuditorTasks($profileUserId);

        $currentConnectionId = getCurrentConnectionByUserId($profileUserId);
        $connectedToName = getConnectionNameById($currentConnectionId);
        //appPrintR($profileUserId);

        $getUserIdsConnectedOnMyAccount = getUserIdsConnectedOnMyAccount();
        //appPrintR($getUserIdsConnectedOnMyAccount);

        $titleLabel = $connectedToName ? '<span class="badge bg-light-subtle text-body badge-border float-end" title="Conta Conectada">'.$connectedToName.'</span>' : '';


        //appPrintR($assignmentData);
        //appPrintR($auditorData);
        //appPrintR($filteredStatuses);
        //appPrintR($assignmentData);
    ?>

    <?php if( $profileUserId == auth()->id() || in_array($profileUserId, $getUserIdsConnectedOnMyAccount) ): ?>
        <div class="profile-foreground position-relative mx-n4 mt-n5">
            <div class="profile-wid-bg">
                <img src="<?php echo e(checkUserCover($user->cover)); ?>" alt="cover" class="profile-wid-img" loading="lazy"/>
            </div>
        </div>

        <div class="pt-5 mb-2 mb-lg-1 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-auto">
                    <div class="avatar-lg profile-user position-relative d-inline-block">
                        <img id="avatar-img" src="<?php echo e(checkUserAvatar($user->avatar)); ?>" alt="avatar" class="img-thumbnail rounded-circle" loading="lazy" />
                        <?php if($profileUserId == auth()->id()): ?>
                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" title="Alterar Avatar">
                                <input class="d-none" name="avatar" id="member-image-input" type="file" accept="image/jpeg">
                                <label for="member-image-input" class="profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle bg-light text-body">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1 text-shadow"><?php echo e($user->name); ?></h3>
                        <p class="text-white mb-2 text-shadow"></p>
                        <div class="hstack text-white gap-1">
                            <div class="me-2 text-shadow">
                                <i class="ri-mail-line text-white fs-16 align-middle me-2"></i><?php echo e($user->email); ?>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-auto order-last order-lg-0">
                    <div class="row text text-white-50 text-center">
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="<?php echo e($assignmentData ? 'col-sm-12 col-md-7 col-lg-9 col-xxl-10' : 'col-sm-12 col-md-12 col-lg-12 col-xxl-12'); ?> ">
                <div class="card h-100">
                    <div class="card-header">
                        <?php echo $titleLabel; ?>

                        <h5 class="card-title mb-0 flex-grow-1">
                            <i class="ri-todo-fill fs-16 align-bottom text-theme me-2"></i>
                            <?php if($profileUserId == $currentConnectionId): ?>
                                Minhas Tarefas
                            <?php else: ?>
                                Tarefas de <span class="text-theme"><?php echo e($user->name); ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body pb-0" style="min-height: 150px">
                        <?php if( $assignmentData && is_array($assignmentData) ): ?>
                            <div class="tasks-board mb-0 position-relative" id="kanbanboard">
                                <?php $__currentLoopData = $filteredStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $filteredSurveyorData = [];
                                        $filteredAuditorData = [];

                                        array_walk($assignmentData, function ($item) use (&$filteredSurveyorData, $key, $profileUserId) {
                                            if ($item['surveyor_status'] == $key && $item['surveyor_id'] == $profileUserId) {
                                                $filteredSurveyorData[] = $item;
                                            }
                                        });

                                        array_walk($assignmentData, function ($item) use (&$filteredAuditorData, $key, $profileUserId) {
                                            if ($item['auditor_status'] == $key && $item['auditor_id'] == $profileUserId) {
                                                $filteredAuditorData[] = $item;
                                            }
                                        });

                                        $countFilteredSurveyorData = is_array($filteredSurveyorData) ? count($filteredSurveyorData) : 0;

                                        $countFilteredAuditorData = is_array($filteredAuditorData) ? count($filteredAuditorData) : 0;

                                        $countTotal = $countFilteredSurveyorData + $countFilteredAuditorData;
                                    ?>

                                    <div class="tasks-list
                                    
                                    <?php echo e(in_array($key, ['waiting', 'pending', 'auditing', 'losted']) && $countTotal < 1 ? 'd-none' : ''); ?>

                                    
                                    p-2">
                                        <div class="d-flex mb-3">
                                            <div class="flex-grow-1">
                                                <h6 class="fs-14 text-uppercase fw-semibold mb-1">
                                                    <span data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="<?php echo e($status['label']); ?>" data-bs-content="<?php echo e($status['description']); ?>">
                                                        <?php echo e($status['label']); ?>

                                                    </span>
                                                    <small class="badge bg-<?php echo e($status['color']); ?> align-bottom ms-1 totaltask-badge">
                                                        <?php echo e($countTotal); ?>

                                                    </small>
                                                </h6>
                                                <p class="text-muted mb-2"><?php echo e($status['description']); ?></p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                
                                            </div>
                                        </div>
                                        <div data-simplebar class="tasks-wrapper">
                                            <div id="<?php echo e($key); ?>-task" class="tasks mb-2 pb-3">

                                                <?php echo $__env->make('surveys.layouts.profile-task-card', [
                                                    'status' => $status,
                                                    'statusKey' => $key,
                                                    'designated' => 'surveyor',
                                                    'data' => $filteredSurveyorData
                                                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                                                <?php echo $__env->make('surveys.layouts.profile-task-card', [
                                                    'status' => $status,
                                                    'statusKey' => $key,
                                                    'designated' => 'auditor',
                                                    'data' => $filteredAuditorData
                                                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                                            </div>
                                        </div>
                                    </div>
                                    <!--end tasks-list-->
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="ri-alert-line label-icon"></i> Tarefas ainda não foram delegadas
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if($assignmentData): ?>
                <div class="col-sm-12 col-md-5 col-lg-3 col-xxl-2">
                    <div class="card h-100">
                        <div class="card-header align-items-center d-flex">
                            <h5 class="card-title mb-0 flex-grow-1"><i class="ri-line-chart-fill fs-16 align-bottom text-theme me-2"></i>Síntese</h5>
                        </div>
                        <div class="card-body" style="min-height: 150px">

                            <?php if($countSurveyorTasks > 0): ?>
                                <div class="text-center">
                                    <div class="text-muted"><span class="fw-medium"><?php echo e($countSurveyorTasks); ?></span> <?php echo e($countSurveyorTasks > 1 ? 'Vistorias' : 'Vistoria'); ?> <?php echo e($countSurveyorTasks > 1 ? 'Atribuídas' : 'Atribuída'); ?></div>
                                </div>
                                <div class="mt-2 mb-4">
                                    <?php $__currentLoopData = $filteredStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $filteredSurveyorData = [];

                                            array_walk($assignmentData, function ($item) use (&$filteredSurveyorData, $key, $profileUserId) {
                                                if ($item['surveyor_status'] == $key && $item['surveyor_id'] == $profileUserId) {
                                                    $filteredSurveyorData[] = $item;
                                                }
                                            });

                                            $countFilteredSurveyorData = is_array($filteredSurveyorData) ? count($filteredSurveyorData) : 0;

                                            $countTotal = $countFilteredSurveyorData;

                                            $percentage = $countSurveyorTasks > 0 && $countTotal > 0 ? ($countTotal / $countSurveyorTasks) * 100 : 0;
                                            $percentage = number_format($percentage, 0);
                                        ?>
                                        <?php if($percentage > 0): ?>
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto">
                                                    <div class="p-1" style="min-width: 100px;">
                                                        <h6 class="mb-0" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="<?php echo e($status['label']); ?>" data-bs-content="<?php echo e($status['description']); ?>">
                                                            <?php echo e($status['label']); ?>

                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="p-1">
                                                        <div class="progress animated-progress progress-sm" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Equivalente a <?php echo e($percentage); ?>% de <?php echo e($countSurveyorTasks); ?> tarefas">
                                                            <div class="progress-bar bg-<?php echo e(getProgressBarClass($percentage)); ?>" role="progressbar" style="width: <?php echo e($percentage); ?>%" aria-valuenow="<?php echo e($percentage); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="p-1">
                                                        <h6 class="mb-0 text-<?php echo e($status['color']); ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Total de tarefas relacionadas ao status <?php echo e($status['label']); ?>"><?php echo e($countTotal); ?></h6>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>

                            <?php if($countAuditorTasks > 0): ?>
                                <div class="text-center">
                                    <div class="text-muted"><span class="fw-medium"><?php echo e($countAuditorTasks); ?></span> <?php echo e($countAuditorTasks > 1 ? 'Auditorias' : 'Auditoria'); ?> <?php echo e($countAuditorTasks > 1 ? 'Requisitadas' : 'Requisitada'); ?></div>
                                </div>
                                <div class="mt-2 mb-4">
                                    <?php $__currentLoopData = $filteredStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $filteredAuditorData = [];

                                            array_walk($assignmentData, function ($item) use (&$filteredAuditorData, $key, $profileUserId) {
                                                if ($item['auditor_status'] == $key && $item['auditor_id'] == $profileUserId) {
                                                    $filteredAuditorData[] = $item;
                                                }
                                            });

                                            $countFilteredAuditorData = is_array($filteredAuditorData) ? count($filteredAuditorData) : 0;

                                            $countTotal = $countFilteredAuditorData;

                                            $percentage = $countAuditorTasks > 0 && $countTotal > 0 ? ($countTotal / $countAuditorTasks) * 100 : 0;
                                            $percentage = number_format($percentage, 0);
                                        ?>
                                        <?php if($percentage > 0): ?>
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto">
                                                    <div class="p-1" style="min-width: 100px;">
                                                        <h6 class="mb-0" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="<?php echo e($status['label']); ?>" data-bs-content="<?php echo e($status['description']); ?>">
                                                            <?php echo e($status['label']); ?>

                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="p-1">
                                                        <div class="progress animated-progress progress-sm" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Equivalente a <?php echo e($percentage); ?>% de <?php echo e($countSurveyorTasks); ?> tarefas">
                                                            <div class="progress-bar bg-<?php echo e(getProgressBarClass($percentage)); ?>" role="progressbar" style="width: <?php echo e($percentage); ?>%" aria-valuenow="<?php echo e($percentage); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="p-1">
                                                        <h6 class="mb-0 text-<?php echo e($status['color']); ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Total de tarefas relacionadas ao status <?php echo e($status['label']); ?>"><?php echo e($countTotal); ?></h6>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">Você não possui permissão de acesso a este usuário</div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
    var profileShowURL = "<?php echo e(route('profileShowURL')); ?>";

    var surveysIndexURL = "<?php echo e(route('surveysIndexURL')); ?>";
    var surveysCreateURL = "<?php echo e(route('surveysCreateURL')); ?>";
    var surveysEditURL = "<?php echo e(route('surveysEditURL')); ?>";
    var surveysChangeStatusURL = "<?php echo e(route('surveysChangeStatusURL')); ?>";
    var surveysShowURL = "<?php echo e(route('surveysShowURL')); ?>";
    var surveysStoreOrUpdateURL = "<?php echo e(route('surveysStoreOrUpdateURL')); ?>";
    var formSurveyorAssignmentURL = "<?php echo e(route('formSurveyorAssignmentURL')); ?>";
    var formAuditorAssignmentURL = "<?php echo e(route('formAuditorAssignmentURL')); ?>";
    var changeAssignmentSurveyorStatusURL = "<?php echo e(route('changeAssignmentSurveyorStatusURL')); ?>";
    var changeAssignmentAuditorStatusURL = "<?php echo e(route('changeAssignmentAuditorStatusURL')); ?>";
</script>
<script src="<?php echo e(URL::asset('build/js/surveys.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

<script>
    var assignmentShowURL = "<?php echo e(route('assignmentShowURL')); ?>";
    var formSurveyorAssignmentURL = "<?php echo e(route('formSurveyorAssignmentURL')); ?>";
    var changeAssignmentSurveyorStatusURL = "<?php echo e(route('changeAssignmentSurveyorStatusURL')); ?>";
    var responsesSurveyorStoreOrUpdateURL = "<?php echo e(route('responsesSurveyorStoreOrUpdateURL')); ?>";
</script>
<script src="<?php echo e(URL::asset('build/js/surveys-surveyor.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

<script>
    var changeAssignmentAuditorStatusURL = "<?php echo e(route('changeAssignmentAuditorStatusURL')); ?>";
    var responsesAuditorStoreOrUpdateURL = "<?php echo e(route('responsesAuditorStoreOrUpdateURL')); ?>";
    var enterAssignmentAuditorURL = "<?php echo e(route('enterAssignmentAuditorURL')); ?>";
    
    var revokeAssignmentAuditorURL = "<?php echo e(route('revokeAssignmentAuditorURL')); ?>";
</script>
<script src="<?php echo e(URL::asset('build/js/surveys-auditor.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

<script type="module">
    import { attachImage } from '<?php echo e(URL::asset('build/js/settings-attachments.js')); ?>';

    var uploadAvatarURL = "<?php echo e(route('uploadAvatarURL')); ?>";

    attachImage("#member-image-input", "#avatar-img", uploadAvatarURL, false);
</script>

<script>
    // Auto refresh page
    setInterval(function() {
        window.location.reload();// true to cleaning cache
    }, 600000); // 600000 milliseconds = 10 minutes
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\www\vistoria.plus\application\development2.vistoria.plus\public_html\resources\views/profile/index.blade.php ENDPATH**/ ?>