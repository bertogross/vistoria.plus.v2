<?php
    use Carbon\Carbon;
    use App\Models\Survey;
    use App\Models\SurveyTopic;
    use App\Models\SurveyResponse;
    use App\Models\SurveyAssignments;

    $today = Carbon::now();

    $origin = $origin ?? '';

    $countAvaliables = 0;

    $currentUserId = auth()->id();

    $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

    $getSurveyRecurringTranslations = Survey::getSurveyRecurringTranslations();
?>
<?php if( !empty($data) && is_array($data) ): ?>
    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $surveyId = intval($assignment['survey_id']);
            $survey = Survey::find($surveyId);
            if(!$survey){
                break;
            }

            $assignmentId = intval($assignment['id']);
            $createdAt = $assignment['created_at'];

            $title = $survey->title;

            $recurring = $survey->recurring;
            $recurringLabel = $getSurveyRecurringTranslations[$recurring]['label'];

            $deadline = SurveyAssignments::getSurveyAssignmentDeadline($recurring, $createdAt);
            $deadlineFormated = $deadline->format('Ymd');
            $deadline = $deadline->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY');

            $templateName = getSurveyTemplateNameById($survey->template_id);

            $companyId = intval($assignment['company_id']);
            $companyName = getCompanyNameById($companyId);

            $surveyorId = $assignment['surveyor_id'] ?? null;
            $surveyorStatus = $assignment['surveyor_status'] ?? null;
            $getUserDataSurveyor = $surveyorId ? getUserData($surveyorId) : null ;
            $surveyorName = $getUserDataSurveyor->name ?? '';
            $surveyorAvatar = $getUserDataSurveyor->avatar ?? '';

            $auditorId = $assignment['auditor_id'] ?? null;
            $auditorStatus = $assignment['auditor_status'] ?? null;
            $getUserDataAuditor = $auditorId ? getUserData($auditorId) : null ;
            $auditorName = $getUserDataAuditor->name ?? '';
            $auditorAvatar = $getUserDataAuditor->avatar ?? '';

            $labelTitle = SurveyAssignments::getSurveyAssignmentLabelTitle($surveyorStatus, $auditorStatus, $statusKey);

            if($designated == 'auditor'){
                $designatedUserId = $auditorId;
            }elseif($designated == 'surveyor'){
                $designatedUserId = $surveyorId;
            }

            $percentage = SurveyAssignments::calculateSurveyPercentage($surveyId, $companyId, $assignmentId, $surveyorId, $auditorId, $designated);
            $progressBarClass = getProgressBarClass($percentage);

            $totalTopics = SurveyTopic::countSurveyTopics($surveyId);

            $countResponsesYes = SurveyResponse::countSurveySurveyorResponsesByCompliance($surveyorId, $surveyId, $assignmentId, 'yes');

            $countResponsesNo = SurveyResponse::countSurveySurveyorResponsesByCompliance($surveyorId, $surveyId, $assignmentId, 'no');

            $percentYes = $countResponsesYes > 0 ? ($countResponsesYes / $totalTopics) * 100 : 0;
            $percentYes = number_format($percentYes, 0);

            $percentNo = $countResponsesNo > 0 ? ($countResponsesNo / $totalTopics) * 100 : 0;
            $percentNo = number_format($percentNo, 0);

            if( $origin == 'auditListing' && $deadlineFormated < $today->format('Ymd') ){
                break;
            }
            $countAvaliables++;
        ?>
        <?php if($origin == 'auditListing'): ?>
            <div class="<?php echo e($countAvaliables > 0 ? 'col-sm-12 col-md-6 col-lg-4 col-xxl-3 mt-4' : 'col-12 text-center'); ?>">
                <?php if($countAvaliables == 0): ?>
                    <?php $__env->startComponent('components.nothing'); ?>
                        <?php $__env->slot('text', 'Não há tarefas disponíveis para uma Auditoria'); ?>
                    <?php echo $__env->renderComponent(); ?>
                <?php endif; ?>
        <?php endif; ?>

        <div class="card tasks-box bg-body" data-assignment-id="<?php echo e($assignmentId); ?>">
            <div class="card-header border-bottom border-1 border-bottom-dashed bg-body">
                <div class="row">
                    <div class="col text-theme fw-medium fs-15">
                        <?php echo e($companyName); ?>

                    </div>
                    <div class="col-auto">
                        <?php if( $surveyorStatus == 'completed' && $auditorStatus == 'completed'): ?>
                            

                            <span class="badge bg-success-subtle text-success badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($labelTitle); ?>">
                                <span class="text-info">Vistoriado</span>
                            </span>

                            <span class="badge bg-success-subtle text-success badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($labelTitle); ?>">
                                <span class="text-secondary">Auditado</span>
                            </span>

                        <?php elseif($designated == 'surveyor'): ?>
                            <span class="badge bg-dark-subtle text-body badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($labelTitle); ?>">
                                Vistoria
                                <?php if( in_array($statusKey, ['completed']) && $surveyorStatus == 'completed' && $auditorStatus == 'completed' ): ?>
                                    <i class="ri-check-double-fill ms-2 text-success"></i>
                                <?php endif; ?>
                            </span>
                        <?php elseif($designated == 'auditor'): ?>
                            <span class="badge bg-dark-subtle text-body badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($labelTitle); ?>">
                                Auditoria
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body bg-body">
                <h5 class="fs-13 text-truncate task-title mb-0">
                    <?php echo e($title); ?>

                </h5>

                <ul class="list-group mt-3">
                    <li class="list-group-item">
                        <i class="ri-checkbox-line align-top me-2 text-info"></i> <?php echo e($totalTopics); ?> Tópicos
                        <?php if(in_array($statusKey, ['completed'])): ?>
                            <div class="progress progress-sm bg-success-subtle rounded-2 mt-3" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($percentYes); ?>% de Conformidades relatadas por <?php echo e($surveyorName); ?>">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($percentYes); ?>%;" aria-valuenow="<?php echo e($percentYes); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="progress progress-sm bg-danger-subtle rounded-2 mt-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="bottom" title="<?php echo e($percentNo); ?>% de Inconformidades relatadas por <?php echo e($surveyorName); ?>">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo e($percentNo); ?>%;" aria-valuenow="<?php echo e($percentNo); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Da repetição desta tarefa">
                        <i class="ri-refresh-line align-top me-2 text-info"></i> Recorrência: <?php echo e($recurringLabel); ?>

                    </li>
                    <li class="list-group-item" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="A data limite para execução desta tarefa">
                        <i class="ri-calendar-todo-line align-top me-2 text-info"></i> Prazo: <?php echo e($deadline); ?>

                    </li>
                </ul>

                <?php if(in_array($statusKey, ['losted'])): ?>
                    <?php if( $surveyorStatus == 'losted' && $auditorStatus == 'losted' ): ?>
                        <div class="text-danger small mt-2">
                            Esta <u>Auditoria</u> foi perdida pois a <u>Vistoria</u> não foi efetuada na data prevista.
                        </div>
                    <?php elseif( $surveyorStatus == 'completed' && $auditorStatus == 'losted' ): ?>
                        <div class="text-warning small mt-2">
                            A <u>Vistoria</u> foi completada. Entretanto, a <u>Auditoria</u> não foi concluída em tempo.
                        </div>
                    <?php elseif( $surveyorStatus != 'completed' && $surveyorStatus != 'losted' && $auditorStatus == 'losted' ): ?>
                        <div class="text-warning small mt-2">
                            Esta tarefa foi perdida pois não foi efetuada na data prevista.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <!--end card-body-->
            <div class="card-footer border-top-dashed bg-body">
                <div class="row">
                    <div class="col small">
                        <div class="avatar-group ps-0">
                            <?php if($surveyorId === $auditorId): ?>
                                <a href="<?php echo e(route('profileShowURL', $surveyorId)); ?>" class="d-inline-block me-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Tarefas delegadas a <u><?php echo e($surveyorName); ?></u>">
                                    <img src="<?php echo e(checkUserAvatar($surveyorAvatar)); ?>"
                                    alt="<?php echo e($surveyorName); ?>" class="rounded-circle avatar-xxs border border-1 border-white" loading="lazy">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('profileShowURL', $surveyorId)); ?>" class="d-inline-block me-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Tarefa de Vistoria delegada a <u><?php echo e($surveyorName); ?></u>">
                                    <img src="<?php echo e(checkUserAvatar($surveyorAvatar)); ?>"
                                    alt="<?php echo e($surveyorName); ?>" class="rounded-circle avatar-xxs border border-1 border-white" loading="lazy">
                                </a>

                                <?php if($auditorId): ?>
                                    <a href="<?php echo e(route('profileShowURL', $auditorId)); ?>" class="d-inline-block ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Tarefa de Auditoria requisitada por <u><?php echo e($auditorName); ?></u>">
                                        <img src="<?php echo e(checkUserAvatar($auditorAvatar)); ?>"
                                        alt="<?php echo e($auditorName); ?>" class="rounded-circle avatar-xxs border border-1 border-secondary" loading="lazy">
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <?php if($currentUserId == $designatedUserId && in_array($statusKey, ['new','pending','in_progress']) ): ?>
                            <button type="button"
                                data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                title="<?php echo e($status['reverse']); ?>"
                                class="btn btn-sm btn-label right btn-<?php echo e($status['color']); ?> <?php echo e($designated == 'surveyor' ? 'btn-assignment-surveyor-action' : 'btn-assignment-auditor-action'); ?>"
                                data-survey-id="<?php echo e($surveyId); ?>"
                                data-assignment-id="<?php echo e($assignmentId); ?>"
                                data-current-status="<?php echo e($statusKey); ?>">
                                    <i class="<?php echo e($status['icon']); ?> label-icon align-middle fs-16"></i> <?php echo e($status['reverse']); ?>

                            </button>

                            <?php if( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]) && in_array($statusKey, ['new','pending','in_progress','completed']) ): ?>
                                <a href="<?php echo e(route('assignmentShowURL', $assignmentId)); ?>"
                                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                    title="Visualizar"
                                    class="btn btn-sm btn-dark ri-eye-line">
                                </a>
                            <?php endif; ?>
                        <?php elseif( ( ( $currentUserId === $surveyorId || $currentUserId === $auditorId ) && in_array($statusKey, ['completed']) ) || ( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]) && in_array($statusKey, ['completed']) ) ): ?>
                            <a href="<?php echo e(route('assignmentShowURL', $assignmentId)); ?>"
                                data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                title="Visualizar"
                                class="btn btn-sm btn-label right btn-dark">
                                    <i class="ri-eye-line label-icon align-middle"></i> Visualizar
                            </a>
                        <?php endif; ?>

                        

                        <?php if( $currentUserId === $designatedUserId && $designated === 'surveyor' && $surveyorId === $auditorId && in_array($statusKey, ['auditing']) ): ?>
                            <i class="text-theme ri-questionnaire-fill" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Neste contexto a você foram delegadas tarefas de Vistoria e Auditoria.<br>Procure na coluna <b>Nova</b> o card correspondente a <b><?php echo e($companyName); ?></b> de prazo: <b><?php echo e($deadline); ?></b> e inicialize a tarefa "></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!--end card-body-->
            <?php if( in_array($statusKey, ['in_progress']) ): ?>
                <div class="progress progress-sm animated-progress custom-progress p-0 rounded-bottom-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($percentage); ?>%">
                    <div class="progress-bar bg-<?php echo e($progressBarClass); ?> rounded-0" role="progressbar" style="width: <?php echo e($percentage); ?>%" aria-valuenow="<?php echo e($percentage); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($origin == 'auditListing'): ?>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('count-available-surveyors')){
            document.getElementById('count-available-surveyors').innerHTML = '<?php echo e($countAvaliables > 0 ? $countAvaliables : ''); ?>';
        }
    });
</script>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/layouts/profile-task-card.blade.php ENDPATH**/ ?>