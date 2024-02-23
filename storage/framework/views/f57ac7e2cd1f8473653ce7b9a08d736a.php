<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18"><?php echo e($title); ?></h4>
            <div class="page-title-right">
                <?php if(isset($li_1)): ?>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">
                        <a href="<?php if(isset($url)): ?><?php echo e($url); ?><?php else: ?> javascript: void(0);<?php endif; ?>">
                            <?php echo e($li_1); ?>

                        </a>
                    </li>
                    <?php if(isset($title) && isset($li_1)): ?>
                        <li class="breadcrumb-item active"><?php echo e($title); ?></li>
                    <?php endif; ?>
                </ol>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\www\vistoria.plus\application\development2.vistoria.plus\public_html\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>