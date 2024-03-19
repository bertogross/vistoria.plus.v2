<div class="d-flex justify-content-end mt-2 mb-2">
    <div class="pagination-wrap hstack gap-2">
        <?php if($paginator->onFirstPage()): ?>
            <span class="page-item pagination-prev disabled">
                Anterior
            </span>
        <?php else: ?>
            <a class="page-item pagination-prev" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">
                Anterior
            </a>
        <?php endif; ?>

        <ul class="pagination listjs-pagination mb-0">
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <li class="disabled" aria-disabled="true"><span><?php echo e($element); ?></span></li>
                <?php endif; ?>

                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active" aria-current="page"><span class="page-link"><?php echo e($page); ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <?php if($paginator->hasMorePages()): ?>
            <a class="page-item pagination-next" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">
                Próxima
            </a>
        <?php else: ?>
            <span class="page-item pagination-next disabled">
                Próxima
            </span>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/layouts/custom-pagination.blade.php ENDPATH**/ ?>