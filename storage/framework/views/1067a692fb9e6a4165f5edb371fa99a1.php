<?php
    use Carbon\Carbon;
    use App\Models\Survey;
    use App\Models\SurveyAssignments;

    $today = Carbon::now();
?>
<div id="surveysList" class="card mb-3">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">
                <i class="ri-fingerprint-2-line fs-16 align-bottom text-theme me-2"></i>Listagem
            </h5>
            <div class="flex-shrink-0">
                <div class="d-flex flex-wrap gap-2">

                </div>
            </div>
        </div>
    </div>

    <div class="card-body pb-0">
        <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-theme mb-0" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#nav-border-justified-done" role="tab" aria-selected="true">
                    Auditorias
                    <?php echo $dataDone ? '<span class="badge border border-dark text-body ms-2">'.count($dataDone).'</span>' : ''; ?>

                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#nav-border-justified-available" role="tab" aria-selected="false" tabindex="-1">
                    Vistorias Disponíveis
                    <span class="badge border border-dark text-body ms-2" id="count-available-surveyors"></span>
                </a>
            </li>
        </ul>
        <div class="tab-content text-muted">
            <div class="tab-pane active border border-1 border-light" id="nav-border-justified-done" role="tabpanel">
                <div class="card-body border border-dashed border-end-0 border-start-0 border-top-0" style="flex: inherit !important;">
                    <form action="<?php echo e(route('surveysAuditIndexURL')); ?>" method="get" autocomplete="off">
                        <div class="row g-3">

                            <div class="col-sm-12 col-md col-lg">
                                <input type="text" class="form-control flatpickr-range" name="created_at" placeholder="- Período -" data-min-date="<?php echo e($firstDate ?? ''); ?>" data-max-date="<?php echo e($lastDate ?? ''); ?>" value="<?php echo e(request('created_at', '')); ?>" title="Selecione o Período">
                            </div>

                            <div class="col-sm-12 col-md col-lg">
                                <label for="select-status" class="d-none" title="Selecione o Status">"Status</label>
                                <select class="form-control form-select" name="status" id="select-status" title="Selecione o Status">
                                    <option value="">- Status -</option>
                                    <?php $__currentLoopData = ['pending', 'in_progress', 'completed', 'losted']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                        <?php echo e($key == request('status', null) ? 'selected' : ''); ?>

                                        title="<?php echo e($getSurveyAssignmentStatusTranslations[$key]['description']); ?>"
                                        value="<?php echo e($key); ?>">
                                            <?php echo e($getSurveyAssignmentStatusTranslations[$key]['label']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-sm-12 col-md-auto col-lg-auto wrap-form-btn">
                                <button type="submit" name="filter" value="true" class="btn btn-theme w-100 init-loader">
                                    <i class="ri-equalizer-fill me-1 align-bottom"></i> Filtrar
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <?php if(!$dataDone || $dataDone->isEmpty()): ?>
                    <?php $__env->startComponent('components.nothing'); ?>
                        
                    <?php echo $__env->renderComponent(); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-nowrap table-striped mb-0" id="tasksTable">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Título do modelo que serviu de base para gerar os tópicos desta vistoria">
                                        Tarefa
                                    </th>
                                    <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="A Unidade Auditada">
                                        Unidade
                                    </th>
                                    <th class="text-left" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Usário ao qual foi designada a tarefa">
                                        Vistoriado por
                                    </th>
                                    
                                    <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Usuário Auditor(a)">Auditado por</th>
                                    <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="A Data da Auditoria">
                                        Auditado em
                                    </th>
                                    <th class="text-center">
                                        Status
                                    </th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $dataDone; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $assignmentId = $assignment->id;
                                        $title = $assignment->title;
                                        $surveyId = $assignment->survey_id;

                                        $surveyorId = $assignment->surveyor_id;
                                        $auditorId = $assignment->auditor_id;

                                        $surveyorStatus = $assignment->surveyor_status;
                                        $auditorStatus = $assignment->auditor_status;

                                        $companyId = $assignment->company_id;
                                        $companyName = $companyId ? getCompanyNameById($companyId) : '';
                                        $assignmentStatus = $assignment->auditor_status;

                                        $getSurveyTemplateNameById = getSurveyTemplateNameById($assignment->template_id);

                                        $countSurveyAssignmentBySurveyId = \App\Models\SurveyAssignments::countSurveyAssignmentBySurveyId($surveyId);

                                        $delegation = \App\Models\SurveyAssignments::getAssignmentDelegatedsBySurveyId($surveyId);
                                    ?>
                                    <tr class="main-row" data-id="<?php echo e($surveyId); ?>">
                                        <td>
                                            <span class="text-body" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e(ucfirst($title)); ?>">
                                                <?php echo e(limitChars(ucfirst($title), 50)); ?>

                                            </span>

                                            <div class="text-muted small" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e(limitChars(ucfirst($getSurveyTemplateNameById), 200)); ?>">
                                                <strong>Modelo:</strong> <span class="text-body"></span><?php echo e(limitChars(ucfirst($getSurveyTemplateNameById), 100)); ?>

                                            </div>
                                        </td>
                                        <td>
                                            <?php echo e($companyName); ?>

                                        </td>
                                        <td>
                                            <div class="avatar-group flex-nowrap d-inline-block align-middle">
                                                <?php
                                                    $getUserData = getUserData($surveyorId);
                                                ?>
                                                <a href="<?php echo e(route('profileShowURL', $surveyorId)); ?>" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: <?php echo e($getUserData->name); ?> : <?php echo e($companyName); ?>">
                                                    <img src="<?php echo e(checkUserAvatar($getUserData->avatar)); ?>" alt="" class="rounded-circle avatar-xxs">
                                                </a> <?php echo e($getUserData->name); ?>

                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="avatar-group">
                                                <?php
                                                    $getUserData = getUserData($auditorId);
                                                ?>
                                                <div class="avatar-group flex-nowrap d-inline-block align-middle">
                                                    <a href="<?php echo e(route('profileShowURL', $auditorId)); ?>" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: <?php echo e($getUserData->name); ?> : <?php echo e($companyName); ?>">
                                                        <img src="<?php echo e(checkUserAvatar($getUserData->avatar)); ?>" alt="" class="rounded-circle avatar-xxs">
                                                    </a> <?php echo e($getUserData->name); ?>

                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo e($assignment->updated_at ? date('d/m/Y H:i', strtotime($assignment->updated_at)) : '-'); ?>

                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-<?php echo e($getSurveyAssignmentStatusTranslations[$assignmentStatus]['color']); ?>-subtle text-<?php echo e($getSurveyAssignmentStatusTranslations[$assignmentStatus]['color']); ?> text-uppercase"
                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                title="<?php echo e($getSurveyAssignmentStatusTranslations[$assignmentStatus]['description']); ?>">
                                                <?php echo e($getSurveyAssignmentStatusTranslations[$assignmentStatus]['label']); ?>

                                                <?php if($assignmentStatus == 'started'): ?>
                                                    <span class="spinner-border align-top ms-1"></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td scope="row" class="text-end">
                                            <?php if(in_array($auditorStatus, ['new', 'pending', 'in_progress'])): ?>
                                                <a
                                                <?php if(in_array($surveyorStatus, ['completed', 'auditing'])): ?>
                                                    href="<?php echo e(route('formAuditorAssignmentURL', $assignmentId)); ?>"
                                                <?php else: ?>
                                                    onclick="alert('Necessário aguardar finalização da Vistoria')"
                                                <?php endif; ?>
                                                class="btn btn-sm btn-label right btn-soft-secondary" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Abrir formulário">
                                                    <i class="ri-fingerprint-2-line label-icon align-middle fs-16"></i> Auditar
                                                </a>
                                            <?php elseif(in_array($auditorStatus, ['completed', 'losted'])): ?>
                                                <a href="<?php echo e(route('assignmentShowURL', $assignmentId)); ?>" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Visualizar resultado" class="btn btn-sm btn-soft-dark ri-eye-line"></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3 mb-3">
                        <?php echo $dataDone->links('layouts.custom-pagination'); ?>

                    </div>
                <?php endif; ?>
            </div>
            <div class="tab-pane border border-1 border-light p-3" id="nav-border-justified-available" role="tabpanel">
                <?php if( $dataAvailable && is_array($dataAvailable) ): ?>
                    <p class="mb-0">Aqui estão informações sobre vistorias que foram realizadas e dentro do prazo para uma possível Auditoria.</p>
                    <div class="row">
                        <?php echo $__env->make('surveys.layouts.profile-task-card', [
                            'status' => 'completed',
                            'statusKey' => 'completed',
                            'designated' => 'surveyor',
                            'origin' => 'auditListing',
                            'data' => $dataAvailable
                        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show mb-0" role="alert">
                        <i class="ri-alert-line label-icon"></i> Não há Vistorias disponíveis para que sejam Auditadas
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/audits/listing.blade.php ENDPATH**/ ?>