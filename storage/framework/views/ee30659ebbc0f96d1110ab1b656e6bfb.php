<?php
    use App\Models\User;
    //appPrintR($data);
    //appPrintR($analyticTermsData);
    //appPrintR($surveyAssignmentData);

    $getSurveyRecurringTranslations = \App\Models\Survey::getSurveyRecurringTranslations();

    $title = $data->title;
    $surveyId = $data->id;
    $companies = $data->companies ? json_decode($data->companies, true) : [];

    $recurring = $data->recurring;
    $recurringLabel = $getSurveyRecurringTranslations[$recurring]['label'];

    // Format the output
    $createdAt = request('created_at') ?? null;
    $createdAt = request('created_at') ?? null;
    $createdAt = !$createdAt && $firstDate && $lastDate ? date("d/m/Y", strtotime($firstDate)) . ' até ' . date("d/m/Y", strtotime($lastDate)) : $createdAt;

    $startAt = $data->start_at ?? null;
    $startAt = $startAt ? date("d/m/Y", strtotime($startAt)) : '';

    $endIn = $data->end_in ?? null;
    $endIn = $endIn ? date("d/m/Y", strtotime($endIn)) : 'Data Indefinida';

    $filterCompanies = request('companies') ?? $companies;

    $templateData = \App\Models\SurveyTemplates::findOrFail($data->template_id);

    $authorId = $templateData->user_id;
    $getUserData = getUserData($authorId);
    $authorRoleName = \App\Models\User::getRoleName($getUserData->role);
    $templateName = trim($templateData->title) ? nl2br($templateData->title) : '';
    $templateDescription = trim($templateData->description) ? nl2br($templateData->description) : '';

    $delegation = \App\Models\SurveyAssignments::getAssignmentDelegatedsBySurveyId($surveyId);
    //appPrintR($delegation);

    //Reorganize the analyticTermsData to separate companies
    $companiesAnalyticTermsData = [];
    foreach ($analyticTermsData as $termId => $dates) {
        foreach ($dates as $date => $records) {
            foreach ($records as $record) {
                $companyId = $record['company_id'];
                if (!isset($companiesAnalyticTermsData[$companyId])) {
                    $companiesAnalyticTermsData[$companyId] = [];
                }
                $companiesAnalyticTermsData[$companyId][$termId][$date][] = $record;
            }
        }
    }
    asort($companiesAnalyticTermsData);

    //appPrintR($swapData);
?>

<?php $__env->startSection('title'); ?>
    Análise do Checklist
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('url'); ?>
            <?php echo e(route('surveysIndexURL')); ?>

        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_1'); ?>
            <?php echo app('translator')->get('translation.surveys'); ?>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Análise do Checklist
            <i class="ri-arrow-right-s-fill text-theme ms-2 me-2 align-bottom"></i>
            <span class="text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Dados originados desta conta"><?php echo getCurrentConnectionName(); ?></span>
            <small class="d-none d-lg-block d-xl-block">
                <i class="ri-arrow-drop-right-line text-theme ms-2 me-2 align-bottom"></i>
                <?php echo e(limitChars($title ?? '', 30)); ?> #<span class="text-theme me-2"><?php echo e($surveyId); ?></span>
            </small>
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <?php if( auth()->user()->hasAnyRole(User::ROLE_ADMIN) ): ?>
        
        <div id="print-this">
            <?php if($templateDescription): ?>
                <?php echo !empty($templateDescription) ? '<div class="blockquote custom-blockquote blockquote-outline blockquote-dark rounded mt-2 mb-3"><h5 class="text-uppercase">'.$title.'</h5><p class="text-body mb-2">'.$templateDescription.'</p><footer class="blockquote-footer mt-0">'.$getUserData->name.' <cite title="'.$authorRoleName.'">'.$authorRoleName.'</cite></footer></div>' : ''; ?>

            <?php endif; ?>

            <?php if( $analyticTermsData || isset($_REQUEST['filter']) ): ?>
                <div class="row">
                    <div class="col-sm-12 col-md mb-4">
                        <div id="filter" class="p-3 bg-light-subtle rounded position-relative" style="z-index: 3; display: block;">
                            <form action="<?php echo e(route('surveysShowURL', $surveyId)); ?>" method="get" autocomplete="off" class="mb-0">
                                <div class="row g-2">

                                    <div class="col-sm-12 col-md col-lg">
                                        <input type="text" class="form-control flatpickr-range" name="created_at" placeholder="- Período -" data-min-date="<?php echo e($firstDate ?? ''); ?>" data-max-date="<?php echo e($lastDate ?? ''); ?>" value="<?php echo e($createdAt); ?>">
                                    </div>

                                    <?php if(!empty($companies) && is_array($companies) && count($companies) > 1): ?>
                                        <div class="col-sm-12 col-md col-lg" title="Exibir somente Empresas selecionadas">
                                            <select class="form-control filter-companies" name="companies[]" multiple data-placeholder="- Empresa -">
                                                <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $companyId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option <?php echo e(in_array($companyId, $filterCompanies) ? 'selected' : ''); ?> value="<?php echo e($companyId); ?>"><?php echo e(getCompanyNameById($companyId)); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-sm-12 col-md-auto col-lg-auto wrap-form-btn">  
                                        <button type="submit" name="filter" value="true" class="btn btn-theme init-loader w-100">
                                            <i class="ri-equalizer-fill me-1 align-bottom"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-auto mb-4">
                        <div class="p-3 bg-light-subtle rounded position-relative">
                            <button type="button"
                                <?php if(count($filterCompanies) > 1): ?>
                                    id="btn-surveys-swap-toggle"
                                <?php else: ?>
                                    onclick="alert('Esta ação requer dados de duas ou mais Unidades')"
                                <?php endif; ?>
                                class="btn btn-<?php echo e(!$swapData ? 'soft-' : ''); ?>theme"
                                data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="left" data-bs-title="Ativar/Desativar o Secionamento" data-bs-content="O <strong>Secionamento</strong> quando ativo permite visualizar os dados independentementes de cada das Unidades.
                                <br><br><?php echo e($swapData ? '<span class="text-success">Secionamento Ativo</span>' : '<span class="text-danger">Secionamento Inativo</span>'); ?>">
                                    <i class="ri-swap-box-line me-1 align-bottom"></i>
                                <?php if($swapData): ?>
                                    Secionamento
                                <?php else: ?>
                                    Secionar
                                <?php endif; ?>
                            </button>

                            <button class="btn btn-soft-theme ms-2" title="Listar Tarefas" data-bs-toggle="modal" data-bs-target="#assignmentsListingModal">
                                <i class="ri-file-list-line"></i>
                                <span class="ms-1 d-none d-lg-block d-xl-block">Listar Tarefas</span>
                            </button>

                            <button class="btn btn-soft-theme ms-2 <?php echo e($swapData ? '' : 'btn-print-this'); ?>"
                                <?php if($swapData): ?>
                                    onclick="alert('Para gerar o PDF, desligue o Secionamento')"
                                <?php endif; ?>
                                data-target-id="print-this" data-pdf-name="Relatório Checklist <?php echo e($surveyId); ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="Gerar PDF">
                                <i class="ri-printer-fill"></i>
                                <span class="ms-1 d-none d-lg-block d-xl-block">Imprimir</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-sm-12 <?php echo e(!$swapData ? 'col-md-6' : ''); ?>">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-title text-uppercase mb-0 flex-grow-1">Aspectos</h4>
                        </div>
                        <div class="card-body h-100" style="min-height: 56px;">
                            <div class="hstack gap-4 flex-wrap">
                                <div data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="O tipo de repetição">
                                    Recorrência: <?php echo e($recurringLabel); ?>

                                </div>
                                <div class="vr"></div>

                                <div data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="A data da primeira interação">
                                    Início: <?php echo e($startAt); ?>

                                </div>
                                <div class="vr"></div>

                                <div data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="A data da última interação">
                                    Fim: <?php echo e($endIn); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!$swapData): ?>
                    <div class="col-sm-12 col-md-6">
                        <?php echo $__env->make('surveys.layouts.card-delegation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row mb-3">
                <?php if( $swapData && count($filterCompanies) > 1 ): ?>
                    <div class="fs-6 text-uppercase text-center mb-3">
                        Dados <span class="text-theme">Secionados</span> :
                        <?php $__currentLoopData = $filterCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $companyId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $exists = $companiesAnalyticTermsData[$companyId] ?? null;
                            ?>
                            <span class="badge bg-dark-subtle <?php echo e(!$exists ? 'text-danger' : 'text-body'); ?> badge-border ms-2" <?php echo !$exists ? 'data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Não há dados"' : ''; ?>>
                                <?php echo e(getCompanyNameById($companyId)); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="p-3">
                        <ul class="nav nav-tabs nav-justified nav-border-top nav-border-top-theme mb-0 sticky-top sticky-top-70 bg-body" role="tablist">
                            <?php
                                $index = 0;
                            ?>
                            <?php $__currentLoopData = $filterCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $companyId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $exists = $companiesAnalyticTermsData[$companyId] ?? null;
                                ?>
                                <li class="nav-item" role="presentation" <?php echo !$exists ? 'data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Não há dados"' : ''; ?>>
                                    <a class="nav-link <?php echo e($index == 0 ? 'active' : ''); ?> <?php echo e(!$exists ? 'no-data' : ''); ?>" data-bs-toggle="tab" href="#nav-border-justified-<?php echo e($companyId); ?>" role="tab" <?php echo e($index > 0 ?? 'aria-selected="true"'); ?>>
                                        <?php echo e(getCompanyNameById($companyId)); ?>

                                        <?php echo !$exists ? '<i class="ri-close-circle-line text-danger align-top fs-14 ms-2"></i>' : ''; ?>

                                    </a>
                                </li>
                                <?php
                                    $index++;
                                ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <div class="tab-content border border-1 border-light p-3">
                            <?php
                                $index = 0;
                            ?>

                            
                            <?php $__currentLoopData = $filterCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $companyId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="tab-pane <?php echo e($index == 0 ? 'active' : ''); ?>" id="nav-border-justified-<?php echo e($companyId); ?>" role="tabpanel">

                                    <?php echo $__env->make('surveys.layouts.card-delegation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                                    <?php if(isset($companiesAnalyticTermsData[$companyId])): ?>
                                        <div class="row">
                                            <?php echo $__env->make('surveys.layouts.chart-terms', ['analyticTermsData' => $companiesAnalyticTermsData[$companyId], 'companyId' => $companyId, 'tabMode' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                                            <?php echo $__env->make('surveys.layouts.chart-calendar', ['analyticTermsData' => $companiesAnalyticTermsData[$companyId], 'companyId' => $companyId, 'tabMode' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                        </div>
                                    <?php else: ?>
                                        <?php $__env->startComponent('components.nothing'); ?>
                                            <?php $__env->slot('text', 'Não foram realizadas Vistorias no período selecionado'); ?>
                                        <?php echo $__env->renderComponent(); ?>
                                    <?php endif; ?>
                                </div>

                                <?php
                                    $index++;
                                ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if($analyticTermsData): ?>
                        <?php if(count($filterCompanies) > 1): ?>
                            <div class="fs-6 text-uppercase text-center mb-3">
                                Dados <span class="text-theme">Globais</span> :
                                <?php $__currentLoopData = $filterCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $companyId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-dark-subtle text-body badge-border ms-2"><?php echo e(getCompanyNameById($companyId)); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                        <?php echo $__env->make('surveys.layouts.chart-terms', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <?php echo $__env->make('surveys.layouts.chart-calendar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php else: ?>
                        <?php $__env->startComponent('components.nothing'); ?>
                        <?php echo $__env->renderComponent(); ?>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>

        <?php echo $__env->make('surveys.layouts.modal-listing-assignments', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php else: ?>
        <div class="alert alert-danger">Acesso autorizado somente aos usuários de Nível Controladoria ou Auditoria</div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        var assignmentShowURL = "<?php echo e(route('assignmentShowURL')); ?>";
        var surveysChangeStatusURL = "<?php echo e(route('surveysChangeStatusURL')); ?>";
    </script>
    <script src="<?php echo e(URL::asset('build/js/surveys.js')); ?>?v=<?php echo e(env('APP_VERSION')); ?>" type="module"></script>

    <script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/fullcalendar/index.global.min.js')); ?>"></script>

    <script src="<?php echo e(URL::asset('build/libs/flatpickr/flatpickr.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/l10n/pt.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/plugins/monthSelect/index.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/flatpickr/plugins/confirmDate/confirmDate.js')); ?>"></script>

    <script src="<?php echo e(URL::asset('build/libs/html2pdf.js/html2pdf.bundle.min.js')); ?>"></script>
    <script>
        const userAvatars = <?php echo json_encode($userAvatars, 15, 512) ?>;
    </script>

    <script type="module">
        import {
            initFlatpickr,
            printThis
        } from '<?php echo e(URL::asset('build/js/helpers.js')); ?>';

        initFlatpickr();
        printThis();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development.vistoria.plus/public_html/resources/views/surveys/show.blade.php ENDPATH**/ ?>