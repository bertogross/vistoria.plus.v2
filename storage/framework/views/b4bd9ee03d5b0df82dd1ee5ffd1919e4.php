<div class="card mb-3">
    <div class="card-header">
        <h4 class="card-title text-uppercase mb-0 flex-grow-1">
            Atribuições
            <?php if($swapData): ?>
                <span class="text-theme"><?php echo e(getCompanyNameById($companyId)); ?></span>
            <?php else: ?>
                Globais
            <?php endif; ?>
        </h4>
    </div>
    <div class="card-body h-100 pb-0">
        <div class="row">
            <div class="col-sm-12 col-md-6 mb-3">
                Vistoria:
                <?php if(isset($delegation['surveyors']) && !empty($delegation['surveyors'])): ?>
                    <?php $__currentLoopData = $delegation['surveyors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $userId = $value['user_id'] ?? null;
                            $getUserData = $userId ? getUserData($userId) : null;
                            $userCompanyId = $value['company_id'] ?? null;
                            $companyName = $userCompanyId ? getCompanyNameById($userCompanyId) : '';
                        ?>
                        <?php if($userId && $swapData && $companyId == $userCompanyId): ?>
                            <a href="<?php echo e(route('profileShowURL', $userId)); ?>" class="avatar-group-item ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: <?php echo e($getUserData->name); ?> : <?php echo e($companyName); ?>">
                                <img src="<?php echo e(checkUserAvatar($getUserData->avatar)); ?>" alt="" class="rounded-circle avatar-xxs">
                            </a>
                        <?php elseif($userId && !$swapData): ?>
                            <a href="<?php echo e(route('profileShowURL', $userId)); ?>" class="avatar-group-item ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: <?php echo e($getUserData->name); ?> : <?php echo e($companyName); ?>">
                                <img src="<?php echo e(checkUserAvatar($getUserData->avatar)); ?>" alt="" class="rounded-circle avatar-xxs">
                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>

            <div class="col-sm-12 col-md-6 mb-3">
                Auditoria:
                <?php if(isset($delegation['auditors']) && !empty($delegation['auditors'])): ?>
                    <?php $__currentLoopData = $delegation['auditors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $userId = $value['user_id'] ?? null;
                            $getUserData = $userId ? getUserData($userId) : null;
                            $userCompanyId = $value['company_id'] ?? null;
                            $companyName = $userCompanyId ? getCompanyNameById($userCompanyId) : '';
                        ?>
                        <?php if($userId && $swapData && $companyId == $userCompanyId): ?>
                            <a href="<?php echo e(route('profileShowURL', $userId)); ?>" class="avatar-group-item ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Auditoria: <?php echo e($getUserData->name); ?> : <?php echo e($companyName); ?>">
                                <img src="<?php echo e(checkUserAvatar($getUserData->avatar)); ?>" alt="" class="rounded-circle avatar-xxs">
                            </a>
                        <?php elseif($userId && !$swapData): ?>
                            <a href="<?php echo e(route('profileShowURL', $userId)); ?>" class="avatar-group-item ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Auditoria: <?php echo e($getUserData->name); ?> : <?php echo e($companyName); ?>">
                                <img src="<?php echo e(checkUserAvatar($getUserData->avatar)); ?>" alt="" class="rounded-circle avatar-xxs">
                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/layouts/card-delegation.blade.php ENDPATH**/ ?>