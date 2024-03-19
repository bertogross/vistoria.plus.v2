<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.users'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0 font-size-18">
            Equipe
            <i class="ri-arrow-right-s-fill text-theme ms-2 me-2 align-bottom"></i>
            <span class="text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Dados originados desta conta"><?php echo getCurrentConnectionName(); ?></span>
        </h4>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col">
                            <div class="search-box">
                                <input type="text" class="form-control" id="searchMemberList" placeholder="Pesquisar por nome...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <!--end col-->
                        
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
            </div>
            <div id="teamlist">
                <div class="team-list grid-view-filter row" id="team-member-list">
                    <?php if($users->isNotEmpty()): ?>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('team.users-card', [ 'user' => getUserData($user->id)], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <?php $__env->startComponent('components.nothing'); ?>
                            <?php $__env->slot('text', 'Ainda não há membros na equipe de '.getCurrentConnectionName().''); ?>
                        <?php echo $__env->renderComponent(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-auto mb-4">
            <div class="card rounded-2 mb-0">
                <div class="card-body p-3">
                    <div class="tasks-wrapper-survey overflow-auto h-100" id="load-surveys-activities" data-subDays="7" style="min-width: 250px;">
                        <div class="text-center">
                            <div class="spinner-border text-theme mt-3 mb-3" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
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

    <script>
        var surveysIndexURL = "<?php echo e(route('surveysIndexURL')); ?>";
        var surveysCreateURL = "<?php echo e(route('surveysCreateURL')); ?>";
        var surveysEditURL = "<?php echo e(route('surveysEditURL')); ?>";
        var surveysChangeStatusURL = "<?php echo e(route('surveysChangeStatusURL')); ?>";
        var surveysShowURL = "<?php echo e(route('surveysShowURL')); ?>";
        var surveysStoreOrUpdateURL = "<?php echo e(route('surveysStoreOrUpdateURL')); ?>";
        var getRecentActivitiesURL = "<?php echo e(route('getRecentActivitiesURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/team/index.blade.php ENDPATH**/ ?>