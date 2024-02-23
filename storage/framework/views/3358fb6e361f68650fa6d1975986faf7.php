<div class="modal fade zoomIn" id="assignmentsListingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(limitChars($title ?? '', 30)); ?> #<span class="text-theme me-2"><?php echo e($surveyId); ?></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card border border-light rounded rounded-2 border-bottom-0 mb-0">
                    <div class="card-body">
                        <?php if($surveyAssignmentData->isNotEmpty()): ?>
                            <div class="table-responsive table-card">
                                <table class="table table-sm align-middle table-nowrap mb-0 table-striped" id="assignmentsTable">
                                    <thead class="table-light text-muted text-uppercase">
                                        <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="A Data e Hora de atualizacão desta tarefa">
                                            Data
                                        </th>
                                        <th>
                                            Unidade
                                        </th>
                                        <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Vistoria delegada a este(a) Usuário(a)">
                                            Vistoria
                                        </th>
                                        <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Auditoria reqsuisitada por este(a) Usuário(a)">
                                            Auditoria
                                        </th>
                                        <th scope="col" width="50"></th>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $surveyAssignmentData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $assignmentId = $assignment->id;
                                                $surveyId = $assignment->survey_id;
                                                $companyId = $assignment->company_id;
                                                $surveyorStatus = $assignment->surveyor_status;
                                                $surveyorId = $assignment->surveyor_id;
                                                    $getUserData = getUserData($surveyorId);
                                                $surveyorAvatar = checkUserAvatar($getUserData->avatar);
                                                $surveyorName = $getUserData->name;

                                                $auditorStatus = $assignment->auditor_status;
                                                $auditorId = $assignment->auditor_id;
                                                    $getUserData = getUserData($auditorId);
                                                $auditorAvatar = checkUserAvatar($getUserData->avatar);
                                                $auditorName = $getUserData->name;
                                            ?>
                                            <tr class="main-row" data-id="<?php echo e($assignmentId); ?>">
                                                <td>
                                                    <?php echo e($assignment->updated_at ? date('d/m/Y H:i', strtotime($assignment->updated_at)) . 'hs' : '-'); ?>

                                                </td>
                                                <td>
                                                    <?php echo e(getCompanyNameById($companyId)); ?>

                                                </td>
                                                <td>
                                                    <img src="<?php echo e($surveyorAvatar); ?>" alt="<?php echo e($surveyorName); ?>" class="rounded-circle avatar-xxs"> <?php echo e($surveyorName); ?>

                                                    <?php if($surveyorStatus): ?>
                                                        <br>
                                                        <span
                                                            class="badge bg-<?php echo e($getSurveyAssignmentStatusTranslations[$surveyorStatus]['color']); ?>-subtle text-<?php echo e($getSurveyAssignmentStatusTranslations[$surveyorStatus]['color']); ?> text-uppercase mt-2"
                                                            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                            title="<?php echo e($getSurveyAssignmentStatusTranslations[$surveyorStatus]['singular_description']); ?>">
                                                            <?php echo e($getSurveyAssignmentStatusTranslations[$surveyorStatus]['label']); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($auditorId): ?>
                                                        <img src="<?php echo e($auditorAvatar); ?>" alt="<?php echo e($auditorName); ?>" class="rounded-circle avatar-xxs"> <?php echo e($auditorName); ?>

                                                    <?php endif; ?>

                                                    <?php if( $auditorStatus && $auditorId ): ?>
                                                        <br>
                                                        <span
                                                            class="badge bg-<?php echo e($getSurveyAssignmentStatusTranslations[$auditorStatus]['color']); ?>-subtle text-<?php echo e($getSurveyAssignmentStatusTranslations[$auditorStatus]['color']); ?> text-uppercase mt-2"
                                                            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                            title="<?php echo e($getSurveyAssignmentStatusTranslations[$auditorStatus]['singular_description']); ?>">
                                                            <?php echo e($getSurveyAssignmentStatusTranslations[$auditorStatus]['label']); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td scope="row" class="text-end">
                                                    <buttom type="button" class="btn btn-sm btn-outline-dark ri-eye-line <?php echo e($surveyorStatus == 'new' ? 'cursor-not-allowed' : 'btn-view-assignment-data'); ?>"
                                                    <?php if($surveyorStatus == 'new'): ?>
                                                        onclick="alert('Tarefa ainda não foi inicializada e por isso não há dados disponíveis');"
                                                    <?php endif; ?>
                                                    data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($surveyorStatus == 'new' ? 'Tarefa ainda não foi inicializada e por isso não há dados disponíveis' : 'Visualizar'); ?>" data-id="<?php echo e($assignmentId); ?>"></buttom>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <?php $__env->startComponent('components.nothing'); ?>
                                <?php $__env->slot('text', 'Ainda não há dados'); ?>
                            <?php echo $__env->renderComponent(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\www\vistoria.plus\application\development2.vistoria.plus\public_html\resources\views/surveys/layouts/modal-listing-assignments.blade.php ENDPATH**/ ?>