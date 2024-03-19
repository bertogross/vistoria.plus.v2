<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.surveys'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php
        use App\Models\User;
    ?>
    
    <div class="row mb-3">
        <div class="col">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1 text-uppercase">
                                <?php echo app('translator')->get('translation.surveys'); ?>
                                <i class="ri-arrow-right-s-fill text-theme ms-2 me-2 align-bottom"></i>
                                <span class="text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Dados originados desta conta"><?php echo getCurrentConnectionName(); ?></span>
                            </h4>
                            <p class="text-muted mb-0">Aqui estão os componentes necessários para suas tarefas de vistoria</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-auto">
                                        <a class="btn btn-label right btn-soft-theme float-end" href="<?php echo e(route('surveysTemplateCreateURL')); ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Adicionar/Editar Modelo">
                                            <i class="ri-edit-box-fill label-icon align-middle fs-16 ms-2"></i>Modelo
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-soft-theme btn-icon layout-rightside-btn" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Exibir/Ocultar Atividades"><i class="ri-pulse-line"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                
            </div>
            <?php if( auth()->user()->hasAnyRole(User::ROLE_ADMIN) ): ?>
                

                <?php echo $__env->make('surveys.listing', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
                <div class="alert alert-danger">Acesso não autorizado</div>
            <?php endif; ?>
        </div>

        <?php if( auth()->user()->hasAnyRole(User::ROLE_ADMIN) ): ?>
            <div class="col-auto layout-rightside-col d-block">
                <div class="overlay"></div>
                <div class="layout-rightside pb-2">
                    <div class="card rounded-2 mb-0">
                        <div class="card-body p-3">
                            <div class="tasks-wrapper-survey overflow-auto h-100" id="load-surveys-activities" data-subDays="1">
                                <div class="text-center"><div class="spinner-border text-theme mt-3 mb-3" role="status"><span class="sr-only">Loading...</span></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/flatpickr.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/l10n/pt.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/plugins/monthSelect/index.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/plugins/confirmDate/confirmDate.js')); ?>"></script>

    <script>
        var surveysIndexURL = "<?php echo e(route('surveysIndexURL')); ?>";
        var surveysCreateURL = "<?php echo e(route('surveysCreateURL')); ?>";
        var surveysEditURL = "<?php echo e(route('surveysEditURL')); ?>";
        var surveysChangeStatusURL = "<?php echo e(route('surveysChangeStatusURL')); ?>";
        var surveysShowURL = "<?php echo e(route('surveysShowURL')); ?>";
        var surveysStoreOrUpdateURL = "<?php echo e(route('surveysStoreOrUpdateURL')); ?>";
        var surveyReloadUsersTabURL = "<?php echo e(route('surveyReloadUsersTabURL')); ?>";
        var getRecentActivitiesURL = "<?php echo e(route('getRecentActivitiesURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script>
        var settingsIndexURL = "<?php echo e(route('settingsIndexURL')); ?>";
        var uploadAvatarURL = "<?php echo e(route('uploadAvatarURL')); ?>";
        var uploadCoverURL = "<?php echo e(route('uploadCoverURL')); ?>";
        var getUserFormContentURL = "<?php echo e(route('getUserFormContentURL')); ?>";
        var settingsUsersStoreURL = "<?php echo e(route('settingsUsersStoreURL')); ?>";
        var settingsUsersUpdateURL = "<?php echo e(route('settingsUsersUpdateURL')); ?>";
        var settingsAccountShowURL = "<?php echo e(route('settingsAccountShowURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/settings-users.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/index.blade.php ENDPATH**/ ?>