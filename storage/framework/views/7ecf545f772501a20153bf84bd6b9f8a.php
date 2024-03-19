<?php $__env->startSection('title'); ?>
    Auditorias
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php
        use App\Models\User;

        $currentUserId = auth()->id();

        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);
    ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('url'); ?>
            <?php echo e(route('surveysAuditIndexURL')); ?>

        <?php $__env->endSlot(); ?>

        <?php $__env->slot('title'); ?>
            Auditorias
            <?php if(request('userId')): ?>
                de
                <span class="text-theme ms-1"><?php echo e(getUserData(request('userId'))->name); ?></span>
            <?php endif; ?>

        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>
    <div class="row mb-3">
        <div class="col">
            <?php if( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2])): ?>
                <?php echo $__env->make('surveys.audits.listing', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
                <div class="alert alert-danger">Acesso não autorizado</div>
            <?php endif; ?>
        </div>
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
        var getRecentActivitiesURL = "<?php echo e(route('getRecentActivitiesURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/audits/index.blade.php ENDPATH**/ ?>