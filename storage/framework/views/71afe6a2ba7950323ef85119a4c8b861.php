<?php
    use Carbon\Carbon;
    use App\Models\User;
    use App\Models\SurveyTopic;
    use App\Models\SurveyResponse;
    use App\Models\SurveyTemplates;

    $today = Carbon::today();
    $currentUserId = auth()->id();

    $surveyId = $surveyData->id ?? '';

    $templateData = SurveyTemplates::findOrFail($surveyData->template_id);
    //appPrintR($templateData);

    $authorId = $templateData->user_id;
    $getUserData = getUserData($authorId);

    $title = $surveyData->title ?? '';
    $description = trim($templateData->description) ? nl2br($templateData->description) : '';

    $templateName = $surveyData ? getSurveyTemplateNameById($surveyData->template_id) : '';

    $companyId = $assignmentData->id ?? '';
    $companyName = $companyId ? getCompanyNameById($companyId) : '';

    $assignmentId = $assignmentData->id ?? null;
    $assignmentCreatedAt = $assignmentData->created_at ?? null;

    $auditorId = $assignmentData->auditor_id ?? null;
    $auditorName = $auditorId ? getUserData($auditorId)->name : '';
    //$auditorStatus = $assignmentData->auditor_status ?? null;

    $surveyorId = $assignmentData->surveyor_id ?? null;
    $surveyorName = $surveyorId ? getUserData($surveyorId)->name : '';
    $surveyorStatus = $assignmentData->surveyor_status ?? null;

    $countTopics = SurveyTopic::countSurveyTopics($surveyId);

    // Count the number of steps that have been finished
    $countResponses = SurveyResponse::countSurveySurveyorResponses($surveyorId, $surveyId, $assignmentId);

    $responsesData = SurveyResponse::where('survey_id', $surveyId)
        ->where('assignment_id', $assignmentId)
        ->get()
        ->toArray();

    $purpose = 'validForm';
?>

<?php $__env->startSection('title'); ?>
    Formulário de Checklist
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/glightbox/css/glightbox.min.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('url'); ?>
            <?php echo e(route('profileShowURL')); ?>

        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_1'); ?>
            Tarefas
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Tarefa <i class="ri-arrow-drop-right-line text-theme ms-2 me-2 align-bottom"></i>
            <small>
                #<span class="text-theme"><?php echo e($surveyId); ?></span> <?php echo e(limitChars($templateName ?? '', 20)); ?>

            </small>
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>
    <div id="content" class="rounded rounded-2 mb-4" style="max-width: 700px; margin: 0 auto;">
        <div class="bg-info-subtle position-relative">
            <div class="card-body p-5 text-center">
                <h2>Vistoria</h2>

                <?php if($companyName ): ?>
                    <h2 class="text-theme text-uppercase"><?php echo e($companyName); ?></h2>
                <?php endif; ?>

                <h3><?php echo e($title ? ucfirst($title) : 'NI'); ?></h3>

                <div class="mb-0 text-muted">
                    Vistoriador(a): <?php echo e($surveyorName); ?>

                </div>

                <?php if($auditorName): ?>
                    <div class="mb-0 text-muted">
                        Auditor(a): <?php echo e($surveyorName); ?>

                    </div>
                <?php endif; ?>

                <div class="mb-0 text-muted">
                    Executar até: <?php echo e($assignmentCreatedAt ? \Carbon\Carbon::parse($assignmentCreatedAt)->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY') : '-'); ?>

                </div>
            </div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="1440" height="60" preserveAspectRatio="none" viewBox="0 0 1440 60">
                    <g mask="url(&quot;#SvgjsMask1001&quot;)" fill="none">
                        <path d="M 0,4 C 144,13 432,48 720,49 C 1008,50 1296,17 1440,9L1440 60L0 60z" style="fill: var(--vz-secondary-bg);"></path>
                    </g>
                    <defs>
                        <mask id="SvgjsMask1001">
                            <rect width="1440" height="60" fill="#ffffff"></rect>
                        </mask>
                    </defs>
                </svg>
            </div>
        </div>

        <?php if($currentUserId != $assignmentData->surveyor_id): ?>
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Você não possui autorização para prosseguir com a tarefa delegada a outra pessoa
            </div>
        <?php elseif($surveyorStatus == 'completed'): ?>
            <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Esta tarefa foi finalizada e não poderá mais ser editada.
                <br>
                <a href="<?php echo e(route('assignmentShowURL', $assignmentId)); ?>"
                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                    title="Visualizar" class="btn btn-sm btn-soft-dark mt-2">
                    Visualizar
                </a>
            </div>
        <?php elseif($surveyorStatus == 'auditing'): ?>
            <div class="alert alert-secondary alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Esta tarefa está passando por Auditoria e não poderá ser retificada.
                <br>
                <a href="<?php echo e(route('assignmentShowURL', $assignmentId)); ?>"
                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                    title="Visualizar" class="btn btn-sm btn-soft-success mt-2">
                    Visualizar
                </a>
            </div>
        <?php else: ?>
            <?php if($surveyorStatus == 'losted'): ?>
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                    <i class="ri-alert-line label-icon blink"></i> Esta Tarefa foi perdida pois o prazo expirou e por isso não poderá mais ser editada
                </div>
            <?php endif; ?>

            <?php echo !empty($description) ? '<div class="blockquote custom-blockquote blockquote-outline blockquote-dark rounded mt-2 mb-2"><p class="text-body mb-2">'.$description.'</p><footer class="blockquote-footer mt-0">'.$getUserData->name.' </footer></div>' : ''; ?>


            <div id="assignment-container">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="survey_id" value="<?php echo e($surveyId); ?>">
                <input type="hidden" name="company_id" value="<?php echo e($companyId); ?>">

                <?php if($surveyData): ?>
                    <?php echo $__env->make('surveys.layouts.form-surveyor-step-cards', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    
                <?php else: ?>
                    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-alert-line label-icon"></i> Não há dados para gerar os campos deste formulário
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="survey-progress-bar" class="fixed-bottom mb-0 ms-auto me-auto w-100">
        <div class="flex-grow-1">
            <div class="progress animated-progress progress-label rounded-0">
                <div class="progress-bar rounded-0 bg-<?php echo e(getProgressBarClass($percentage)); ?>" role="progressbar" style="width: <?php echo e($percentage); ?>%" aria-valuenow="" aria-valuemin="0" aria-valuemax="100"><div class="label"><?php echo e($percentage > 0 ? $percentage.'%' : ''); ?></div></div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/glightbox/js/glightbox.min.js')); ?>"></script>

    <script>
        var surveysIndexURL = "<?php echo e(route('surveysIndexURL')); ?>";
        var surveysCreateURL = "<?php echo e(route('surveysCreateURL')); ?>";
        var surveysEditURL = "<?php echo e(route('surveysEditURL')); ?>";
        var surveysChangeStatusURL = "<?php echo e(route('surveysChangeStatusURL')); ?>";
        var surveysShowURL = "<?php echo e(route('surveysShowURL')); ?>";
        var surveysStoreOrUpdateURL = "<?php echo e(route('surveysStoreOrUpdateURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script>
        var assignmentShowURL = "<?php echo e(route('assignmentShowURL')); ?>";
        var profileShowURL = "<?php echo e(route('profileShowURL')); ?>";
        var formSurveyorAssignmentURL = "<?php echo e(route('formSurveyorAssignmentURL')); ?>";
        var changeAssignmentSurveyorStatusURL = "<?php echo e(route('changeAssignmentSurveyorStatusURL')); ?>";
        var responsesSurveyorStoreOrUpdateURL = "<?php echo e(route('responsesSurveyorStoreOrUpdateURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys-surveyor.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script>
        var settingsAccountShowURL = "<?php echo e(route('settingsAccountShowURL')); ?>";
        var uploadPhotoURL = "<?php echo e(route('uploadPhotoURL')); ?>";
        var deletePhotoURL = "<?php echo e(route('deletePhotoURL')); ?>";
        var deleteAttachmentByPathURL = "<?php echo e(route('deleteAttachmentByPathURL')); ?>";
        var assetURL = "<?php echo e(URL::asset('/')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys-attachments.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script type="module">
        import {
            toggleElement,
        } from '<?php echo e(URL::asset('build/js/helpers.js')); ?>';

        toggleElement();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/assignment/form-surveyor.blade.php ENDPATH**/ ?>