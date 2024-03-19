<?php if(session('success')): ?>
    <!-- Success Alert -->
    <div id="success-alert" class="alert alert-theme alert-dismissible alert-label-icon label-arrow fade show" role="alert">
        <i class="ri-check-double-line label-icon"></i><?php echo session('success'); ?>

        <button type="button" class="btn-close" data-bs-dismiss=" alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if(session('warning')): ?>
    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
        <i class="ri-alert-line label-icon"></i> <?php echo session('warning'); ?>

        <button type="button" class="btn-close" data-bs-dismiss=" alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
        <i class="ri-error-warning-fill label-icon"></i> <?php echo session('error'); ?>

        <button type="button" class="btn-close" data-bs-dismiss=" alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <ul class="list-unstyled mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><i class="ri-close-fill align-bottom me-1"></i> <?php echo $error; ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/components/alerts.blade.php ENDPATH**/ ?>