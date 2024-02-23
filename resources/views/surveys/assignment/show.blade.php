@php
    use Carbon\Carbon;
    use App\Models\Survey;
    use App\Models\SurveyTopic;
    use App\Models\SurveyResponse;
    use App\Models\SurveyAssignments;

    $currentUserId = auth()->id();

    $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

    $getSurveyRecurringTranslations = Survey::getSurveyRecurringTranslations();

    $assignmentId = $assignmentData->id;
    $surveyId = $assignmentData->survey_id;
    $companyId = $assignmentData->company_id;

    $surveyorId = $assignmentData->surveyor_id;
    $getSurveyorUserData = getUserData($surveyorId);
    $surveyorName = $getSurveyorUserData->name ?? '';
    $surveyorAvatar = checkUserAvatar($getSurveyorUserData->avatar);

    $auditorId = $assignmentData->auditor_id;
    $getAuditorUserData = getUserData($auditorId);
    $auditorName = $auditorId ? $getAuditorUserData->name : '';
    $auditorAvatar = $auditorId ? checkUserAvatar($getAuditorUserData->avatar) : '';

    $surveyorStatus = $assignmentData->surveyor_status;
    $auditorStatus = $assignmentData->auditor_status;

    $title = $surveyData->title;

    $assignmentCreatedAt = $assignmentData->created_at;
    $now = Carbon::now()->startOfDay();
    $timeLimit = $assignmentCreatedAt->endOfDay();

    $recurring = $surveyData->recurring;
    $recurringLabel = $getSurveyRecurringTranslations[$recurring]['label'];

    $deadline = SurveyAssignments::getSurveyAssignmentDeadline($recurring, $assignmentCreatedAt);
    $deadline = $deadline ? $deadline->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY') : '-';

    $templateName = $surveyData ? getSurveyTemplateNameById($surveyData->template_id) : '';
    $templateDescription = $surveyData ? getTemplateDescriptionById($surveyData->template_id) : '';

    $companyName = $companyId ? getCompanyNameById($companyId) : '';

    $countSurveyAssignmentBySurveyId = SurveyAssignments::countSurveyAssignmentBySurveyId($surveyId);

    $responsesData = SurveyResponse::where('assignment_id', $assignmentId)
        ->get()
        ->toArray();

    $complianceSurveyorYesCount = $complianceSurveyorNoCount = $complianceAuditorYesCount = $complianceAuditorNoCount = 0;

    foreach ($responsesData as $item) {
        if (isset($item['compliance_survey'])) {
            if ($item['compliance_survey'] === 'yes') {
                $complianceSurveyorYesCount++;
            } elseif ($item['compliance_survey'] === 'no') {
                $complianceSurveyorNoCount++;
            }
        }
        if (isset($item['compliance_audit'])) {
            if ($item['compliance_audit'] === 'yes') {
                $complianceAuditorYesCount++;
            } elseif ($item['compliance_audit'] === 'no') {
                $complianceAuditorNoCount++;
            }
        }
    }

    $countTopics = SurveyTopic::countSurveyTopics($surveyId);

    $countResponses = SurveyResponse::countSurveySurveyorResponses($surveyorId, $surveyId, $assignmentId);

    $percentage = $countResponses > 0 ? ($countResponses / $countTopics) * 100 : 0;
    $percentage = number_format($percentage, 0);

@endphp
@extends('layouts.master')
@section('title')
    Resultado da Vistoria
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
@endsection
@section('content')

    <div id="content">

        <div class="card mt-n4 mx-n3">
            <div class="bg-warning-subtle">
                <div class="card-body pb-4">
                    <h4 class="fw-semibold">
                        <span class="text-theme">{{ $companyName }}</span> <i class="ri-arrow-right-s-fill align-bottom"></i> {{ limitChars($title ?? '', 100) }}
                    </h4>
                    <div class="hstack gap-3 flex-wrap">

                        <div class="text-muted" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="left" title="A data limite para realizar esta tarefa">
                            Prazo: {{ $deadline }}
                        </div>

                        <div class="vr"></div>

                        <div class="text-muted" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="left" title="O tipo de recorrência">
                            Recorrência: {{ $recurringLabel }}
                        </div>

                        <div class="vr"></div>

                        <div class="text-muted">
                            Vistoria: <a href="{{ route('profileShowURL', $surveyorId) }}" class="text-muted" title="Acessar Perfil">{{$surveyorName}}</a>
                        </div>

                        <div class="vr"></div>

                        <div class="text-muted">
                            Auditoria: <a href="{{ route('profileShowURL', $auditorId) }}" class="text-muted" title="Acessar Perfil">{{$auditorName}}</a>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div><!-- end card -->


        @if( $recurring != 'once' && $countSurveyAssignmentBySurveyId > 0 )
            <a href="{{ route('surveysShowURL', $surveyId) }}" class="btn btn-lg btn-soft-theme float-end position-relative" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Visualização Analítica em Checklists Recorrentes">
                <i class="ri-line-chart-fill"></i> <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">{{$countSurveyAssignmentBySurveyId}} <span class="visually-hidden">registros</span></span>
            </a>
        @endif

        @if ($templateDescription)
            <h6 class="text-uppercase mb-3">Descrição da Tarefa</h6>
            <p class="text-muted">
                {!! nl2br($templateDescription) !!}
            </p>
        @endif

        <div class="clearfix"></div>

        @if ($surveyorStatus == 'new')
            <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Esta tarefa ainda não foi inicializada
            </div>
        @elseif ($surveyorStatus == 'in_progress')
            <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon"></i> Esta tarefa está sendo executada por <a href="{{ route('profileShowURL', $surveyorId) }}" title="Acessar Perfil">{{$surveyorName}}</a>.<br>
                Esta sessão irá recarregar a cada 60 segundos.
            </div>
        @elseif ( $surveyorStatus == 'losted' && !$countResponses  )
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Tarefa de Vistoria perdida por não ter sido concluída no prazo
            </div>
        @elseif ($surveyorStatus == 'losted' && $countResponses)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Tarefa de Vistoria perdida por não ter sido concluída no prazo. Mas, alguns dados foram capturados.
            </div>
        {{--
        @elseif ($surveyorStatus == 'completed')
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-check-double-fill label-icon"></i> Tarefa de Vistoria concluída
            </div>
        --}}

        {{--
        @elseif($surveyorStatus == 'completed' && $auditorStatus == 'losted')
            <div class="alert alert-secondary alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Tarefa de Auditoria perdida por não ter sido concluída no prazo
            </div>
        --}}

        @endif

        @if ($countResponses )
            <div class="row mb-2 mt-4">
                <div class="col-sm-6 col-md-4">
                    <div class="row">
                        <div class="col">
                            <div class="card">
                                <div class="card-body" style="height: 145px;">
                                    <img
                                    src="{{ $surveyorAvatar }}"
                                    alt="{{$surveyorName}}"
                                    class="avatar-xs rounded-circle float-end" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Vistoria realizada por {{$surveyorName}}" />
                                    <h6 class="text-muted text-uppercase mb-4">Vistoria</h6>
                                    <span class="text-success">Conforme</span>: {{$complianceSurveyorYesCount}}
                                    <br><br>
                                    <span class="text-danger">Não Conforme</span>: {{$complianceSurveyorNoCount}}
                                </div>
                            </div>
                        </div>
                        <div class="col {{-- !$auditorId || !in_array($auditorStatus, ['losted', 'bypass']) ? 'col' : 'd-none' --}}">
                            <div class="card">
                                <div class="card-body" style="height: 145px;">
                                    @if(in_array($auditorStatus, ['losted', 'bypass']))
                                        <span class="fs-5 float-end ri-alert-fill text-warning" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Auditoria não foi realizada"></span>
                                    @elseif($timeLimit->gt($now) && $auditorStatus != 'completed')
                                        <span class="fs-5 float-end ri-time-line text-secondary" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="Dentro do prazo para realizar Auditoria"></span>
                                    @else
                                        <img
                                        src="{{$auditorAvatar}}"
                                        alt="{{$auditorName}}"
                                        class="avatar-xs rounded-circle float-end" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Auditoria realizada por {{$auditorName}}" />
                                    @endif

                                    <h6 class="text-muted text-uppercase mb-4">Auditoria</h6>

                                    @if( !$complianceAuditorYesCount && !$complianceAuditorNoCount )
                                        @if ( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]) && in_array($surveyorStatus, ['new','pending','in_progress','completed']) && $timeLimit->gt($now) )
                                            <button type="button"
                                            data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                            title="Requisitar esta tarefa de Auditoria"
                                            class="btn btn-label right waves-effect btn-soft-secondary btn-assignment-audit-enter w-100"
                                            data-assignment-id="{{$assignmentId}}">
                                                <i class="ri-fingerprint-2-line label-icon align-middle fs-16"></i> Auditar
                                            </button>

                                            <div class="form-text mt-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="A data limite para realizar esta tarefa">
                                                Prazo: {{ $deadline }}
                                            </div>
                                        @elseif ( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]) && $auditorId == auth()->id() && $timeLimit->gt($now) )
                                            <div class="row mb-3">
                                                <div class="col-6 pe-1">
                                                    <button type="button"
                                                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                                    title="Revogar esta tarefa de Auditoria"
                                                    class="btn btn-sm btn-label right waves-effect btn-soft-warning btn-assignment-audit-enter w-100"
                                                    data-assignment-id="{{$assignmentId}}">
                                                        <i class="ri-subtract-line label-icon align-middle fs-16"></i> Revogar
                                                    </button>
                                                </div>
                                                <div class="col-6 ps-1">
                                                    <a
                                                    @if (in_array($surveyorStatus, ['completed', 'auditing']))
                                                        href="{{route('formAuditorAssignmentURL', $assignmentId)}}"
                                                    @else
                                                        onclick="alert('Necessário aguardar finalização da Vistoria')"
                                                    @endif
                                                    class="btn btn-sm btn-label right waves-effect btn-soft-secondary w-100" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Abrir formulário">
                                                        <i class="ri-fingerprint-2-line label-icon align-middle fs-16"></i> Auditar
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="form-text mt-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="A data limite para realizar esta tarefa">
                                                Prazo: {{ $assignmentCreatedAt ? $deadline : 'Indefinido' }}
                                            </div>
                                        @else
                                            <button type="button" onclick="alert('Não é possível Auditar uma Vistoria por você realizada.')"
                                            class="btn btn-sm btn-label right waves-effect btn-soft-secondary w-100 cursor-not-allowed" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Não é possível Auditar uma Vistoria por você realizada.">
                                                <i class="ri-fingerprint-2-line label-icon align-middle fs-16"></i> Auditar
                                            </button>

                                            <div class="form-text text-warning text-opacity-75 mt-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="left" title="A data limite para realizar esta tarefa">
                                                Prazo: {{ $assignmentCreatedAt ? $deadline : 'Indefinido' }}
                                            </div>
                                        @endif
                                    @elseif($auditorStatus == 'in_progress')
                                        @if(in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]))
                                            <a href="{{route('formAuditorAssignmentURL', $assignmentId)}}"
                                            class="btn btn-label right waves-effect btn-soft-secondary mb-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Abrir formulário">
                                                <i class="ri-fingerprint-2-line label-icon align-middle fs-16 blink"></i> Prosseguir com a Auditoria
                                            </a>
                                        @else
                                            <p class="blink mb-1">Em progresso...</p>
                                            <span class="text-secondary">De Acordo</span>: {{$complianceAuditorYesCount}}
                                            <br>
                                            <span class="text-warning">Indeferida</span>: {{$complianceAuditorNoCount}}
                                        @endif
                                    @elseif($auditorStatus == 'completed')
                                        <span class="text-secondary">De Acordo</span>: {{$complianceAuditorYesCount}}
                                        <br><br>
                                        <span class="text-warning">Indeferida</span>: {{$complianceAuditorNoCount}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body h-100">
                            <div id="polarTermsAreaChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div id="barTermsChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="mixedTermsChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{--
            @if ( $surveyorStatus == 'completed' && $auditorStatus == 'losted')
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                    <i class="ri-alert-line label-icon blink"></i> A tarefa foi completada. Entretanto, o prazo da Auditoria expirou.
                </div>
            @elseif ( $surveyorStatus == 'losted' && $auditorStatus == 'losted')
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                    <i class="ri-alert-line label-icon blink"></i> A Checklist e a Auditoria não foram realizadas no prazo.
                </div>
            @elseif ($surveyorStatus == 'losted')
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                    <i class="ri-alert-line label-icon blink"></i> O prazo expirou e esta Vistoria foi perdido
                </div>
            @elseif ($auditorStatus == 'losted')
                <div class="alert alert-secondary alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                    <i class="ri-alert-line label-icon blink"></i> O prazo expirou e esta Auditoria foi perdida
                </div>
            @endif
            --}}

            <h5 class="text-uppercase">Resultado:</h5>
            @foreach ($stepsWithTopics as $stepIndex => $step)
                @php
                    $topicBadgeIndex = 0;

                    $stepId = isset($step['step_id']) ? intval($step['step_id']) : '';
                    $termId = isset($step['term_id']) ? intval($step['term_id']) : '';
                    $termName = $termId >= 100000 ? getWarehouseTermNameById($termId) : getTermNameById($termId);
                    $originalPosition = isset($step['step_order']) ? intval($step['step_order']) : 0;
                    $newPosition = $originalPosition;
                    $topics = $step['topics'];
                @endphp

                @if( $topics )
                    <div class="card joblist-card">
                        <div class="card-header border-bottom-dashed">
                            <h5 class="job-title text-theme text-uppercase">{{ $termName }}</h5>
                        </div>
                        @if ( $topics && is_array($topics))
                            @php
                                $bg = 'bg-opacity-75';
                            @endphp
                            @foreach ($topics as $topicIndex => $topic)
                                @php
                                    $topicBadgeIndex++;

                                    $topicId = isset($topic['topic_id']) ? intval($topic['topic_id']) : '';
                                    $question = $topic['question'] ?? '';

                                    $originalPosition = 0;
                                    $newPosition = 0;

                                    $stepIdToFind = $stepId;
                                    $topicIdToFind = $topicId;

                                    $filteredItems = array_filter($responsesData, function ($item) use ($stepIdToFind, $topicIdToFind) {
                                        return $item['step_id'] == $stepIdToFind && $item['topic_id'] == $topicIdToFind;
                                    });

                                    // Reset array keys
                                    $filteredItems = array_values($filteredItems);

                                    $responseId = $filteredItems[0]['id'] ?? '';

                                    $surveyAttachmentIds =  $filteredItems[0]['attachments_survey'] ?? '';
                                    $surveyAttachmentIds = $surveyAttachmentIds ? json_decode($surveyAttachmentIds, true) : '';

                                    $auditAttachmentIds =  $filteredItems[0]['attachments_audit'] ?? '';
                                    $auditAttachmentIds = $auditAttachmentIds ? json_decode($auditAttachmentIds, true) : '';

                                    $commentSurvey = $filteredItems[0]['comment_survey'] ?? '';
                                    $complianceSurvey = $filteredItems[0]['compliance_survey'] ?? '';

                                    $commentAudit = $filteredItems[0]['comment_audit'] ?? '';
                                    $complianceAudit = $filteredItems[0]['compliance_audit'] ?? '';

                                    $bgSurveyor = $complianceSurvey == 'yes' ? 'bg-opacity-10 bg-success' : 'bg-opacity-10 bg-danger';
                                    $bgSurveyor = $complianceSurvey ? $bgSurveyor : 'bg-opacity-10 bg-warning';

                                    $bgAuditor = $complianceAudit == 'yes' ? 'bg-opacity-10 bg-secondary' : 'bg-opacity-10 bg-warning';
                                    $bgAuditor = $complianceAudit ? $bgAuditor : 'bg-opacity-10 bg-secondary';

                                    $topicBadgeColor = $complianceSurvey == 'no' && $complianceAudit == 'yes' ? 'warning' : 'success';

                                    if($complianceSurvey == 'no' && $complianceAudit){
                                        $topicLabelColor = $complianceSurvey == 'no' && $complianceAudit == 'yes' ? '<span class="ri-emotion-normal-fill text-warning float-end blink fs-3 mt-n2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Vistoria Aprovada mas necessita de ações"></span>' : '<span class="ri-emotion-sad-fill text-warning float-end fs-3 mt-n2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Vistoria Indeferida mesmo que com Status Não Conforme"></span>'; // $complianceSurvey == 'no' ||

                                        $topicBadgeColor = 'warning';

                                    } else if($complianceSurvey && $complianceAudit){
                                        $topicLabelColor = $complianceAudit == 'no' ? '<span class="ri-emotion-unhappy-fill text-warning float-end blink fs-3 mt-n2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Não Conforme"></span>' : '<span class="ri-emotion-fill text-success float-end fs-3 mt-n2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Em conformidade"></span>';

                                        $topicBadgeColor = $complianceAudit == 'no' ? 'warning' : 'success';
                                    } else{
                                        $topicLabelColor = $auditorId ? '<span class="fs-4 ri-alert-fill text-secondary float-end" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Não Comparável"></span>' : '';

                                        $topicBadgeColor = 'secondary';
                                    }

                                @endphp
                                <div class="card-body pb-0">
                                    {!! $topicLabelColor !!}
                                    <h5 class="mb-0">
                                        <span class="badge bg-light-subtle badge-border text-{{$topicBadgeColor}} align-bottom me-1">{{ $topicBadgeIndex }}</span>
                                        {{ $question ? ucfirst($question) : 'NI' }}
                                    </h5>
                                    <div class="row mt-3">
                                        <div class="{{ $auditorId ? 'col-md-6' : 'col-md-12' }} pb-3">
                                            <div class="card border-0 h-100">
                                                <div class="card-header border-1 border-bottom-dashed {{ $bgSurveyor }}">
                                                    <h6 class="card-title mb-0">
                                                        @if ($auditorId)
                                                            Checklist:
                                                        @endif
                                                        {!! $complianceSurvey && $complianceSurvey == 'yes' ? '<span class="text-success">Conforme</span>' : '' !!}
                                                        {!! $complianceSurvey && $complianceSurvey == 'no' ? '<span class="text-danger">Não Conforme</span>' : '' !!}
                                                        {!! !$complianceSurvey ? '<span class="text-warning">Não Informado</span>' : '' !!}
                                                    </h6>
                                                </div>
                                                <div class="card-body rounded-bottom-2 {{ $bgSurveyor }} pb-0">
                                                    {!! $commentSurvey ? '<p>'.nl2br($commentSurvey).'</p>' : '' !!}

                                                    @if ( !empty($surveyAttachmentIds) && is_array($surveyAttachmentIds) )
                                                        <div class="row">
                                                            @foreach ($surveyAttachmentIds as $attachmentId)
                                                                @php
                                                                    $attachmentUrl = $dateAttachment = '';
                                                                    if (!empty($attachmentId)) {
                                                                        $attachmentUrl = App\Models\Attachments::getAttachmentPathById($attachmentId);

                                                                        $dateAttachment = App\Models\Attachments::getAttachmentDateById($attachmentId);
                                                                    }
                                                                @endphp
                                                                @if ($attachmentUrl)
                                                                    <div class="element-item col-auto">
                                                                        <div class="gallery-box card p-0 m-1">
                                                                            <div class="gallery-container">
                                                                                <a href="{{ $attachmentUrl }}" class="image-popup" title="Imagem capturada em {{$dateAttachment}}hs" data-gallery="gallery-{{$responseId}}">
                                                                                    <img class="rounded gallery-img" alt="image" height="70" src="{{ $attachmentUrl }}">

                                                                                    <div class="gallery-overlay">
                                                                                        <h5 class="overlay-caption fs-10">{{$dateAttachment}}</h5>
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="{{ $auditorId ? 'col-md-6' : 'd-none' }} pb-3">
                                            <div class="card border-0 h-100">
                                                <div class="card-header border-1 border-bottom-dashed {{ $bgAuditor }}">
                                                    <h6 class="card-title mb-0">
                                                        Auditoria:
                                                        {!! $complianceAudit && $complianceAudit == 'yes' ? '<span class="text-secondary">Aprovada</span>' : '' !!}
                                                        {!! $complianceAudit && $complianceAudit == 'no' ? '<span class="text-warning">Indeferida</span>' : '' !!}
                                                        {!! !$complianceAudit ? '<span class="text-secondary">Não Informado</span>' : '' !!}
                                                    </h6>
                                                </div>
                                                <div class="card-body rounded-bottom-2 {{ $bgAuditor }} pb-0">
                                                    {!! $commentAudit ? '<p>'.nl2br($commentAudit).'</p>' : '' !!}

                                                    @if ( !empty($auditAttachmentIds) && is_array($auditAttachmentIds) )
                                                        <div class="row">
                                                            @foreach ($auditAttachmentIds as $attachmentId)
                                                                @php
                                                                    $attachmentUrl = $dateAttachment = '';
                                                                    if (!empty($attachmentId)) {
                                                                        $attachmentUrl = App\Models\Attachments::getAttachmentPathById($attachmentId);

                                                                        $dateAttachment = App\Models\Attachments::getAttachmentDateById($attachmentId);
                                                                    }
                                                                @endphp
                                                                @if ($attachmentUrl)
                                                                    <div class="element-item col-auto">
                                                                        <div class="gallery-box card p-0 m-1">
                                                                            <div class="gallery-container">
                                                                                <a href="{{ $attachmentUrl }}" class="image-popup" title="Imagem capturada em {{$dateAttachment}}hs" data-gallery="gallery-{{$responseId}}">
                                                                                    <img class="rounded gallery-img" alt="image" height="70" src="{{ $attachmentUrl }}">

                                                                                    <div class="gallery-overlay">
                                                                                        <h5 class="overlay-caption fs-10">{{$dateAttachment}}</h5>
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    @if ($surveyorStatus != 'completed')
        <div class="fixed-bottom mb-0 ms-auto me-auto w-100" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="left" title="Esta barra indica a evolução de uma tarefa com base no percentual">
            <div class="flex-grow-1">
                <div class="progress animated-progress progress-label rounded-0">
                    <div class="progress-bar rounded-0 bg-{{getProgressBarClass($percentage)}}" role="progressbar" style="width: {{$percentage}}%" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">
                        <div class="label">{{ $percentage > 0 ? $percentage.'%' : ''}}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('script')
    <script>
        var formAuditorAssignmentURL = "{{ route('formAuditorAssignmentURL') }}";
        var changeAssignmentAuditorStatusURL = "{{ route('changeAssignmentAuditorStatusURL') }}";
        var responsesAuditorStoreOrUpdateURL = "{{ route('responsesAuditorStoreOrUpdateURL') }}";
        var enterAssignmentAuditorURL = "{{ route('enterAssignmentAuditorURL') }}";
        //var requestAssignmentAuditorURL = "{{-- route('requestAssignmentAuditorURL') --}}";
        var revokeAssignmentAuditorURL = "{{ route('revokeAssignmentAuditorURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys-auditor.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

    <script type="module">
        import {
            autoReloadPage
        } from '{{ URL::asset('build/js/helpers.js') }}';

        @if ($surveyorStatus == 'in_progress')
            autoReloadPage(60);
        @endif
    </script>

    @if ($countResponses )
        <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

        <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>

        <script>
            const rawTermsData = @json($analyticTermsData);
            const terms = @json($terms);

            document.addEventListener('DOMContentLoaded', function() {
                ///////////////////////////////////////////////////////////////
                // START #barTermsChart
                var seriesData = [];
                var categories = [];

                /*for (var termId in rawTermsData) {
                    var totalComplianceYes = 0;
                    var totalComplianceNo = 0;

                    for (var date in rawTermsData[termId]) {
                        var termData = rawTermsData[termId][date];
                        totalComplianceYes += termData.filter(item => item.compliance_survey === 'yes').length;
                        totalComplianceNo += termData.filter(item => item.compliance_survey === 'no').length;
                    }

                    seriesData.push({
                        x: terms[termId]['name'],
                        y: totalComplianceYes - totalComplianceNo
                    });

                    categories.push(terms[termId]['name']);
                }*/
                for (var termId in rawTermsData) {
                    var totalComplianceYes = 0;
                    var totalComplianceNo = 0;

                    for (var date in rawTermsData[termId]) {
                        var termData = rawTermsData[termId][date];
                        totalComplianceYes += termData.filter(item => item.compliance_survey === 'yes').length;
                        totalComplianceNo += termData.filter(item => item.compliance_survey === 'no').length;
                    }

                    var totalResponses = totalComplianceYes + totalComplianceNo;
                    var complianceScore = totalResponses > 0 ? (totalComplianceYes / totalResponses) * 100 : 0;

                    seriesData.push({
                        x: terms[termId]['name'],
                        y: parseFloat(complianceScore.toFixed(0))
                    });

                    categories.push(terms[termId]['name']);
                }

                var optionsTermsChart = {
                    series: [{
                        name: 'Score',
                        data: seriesData
                    }],
                    title: {
                        text: 'Dinâmica de Pontuação na Conformidade entre Termos'
                    },
                    chart: {
                        type: 'bar',
                        height: 402,
                        toolbar: {
                            show: false,
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            colors: {
                                ranges: [{
                                    from: -1000,
                                    to: 0,
                                    color: '#DF5253'
                                }, {
                                    from: 1,
                                    to: 1000,
                                    color: '#13c56b'
                                }],
                            },
                            dataLabels: {
                                position: 'top',
                            },
                        },
                    },
                    xaxis: {
                        categories: categories
                    },
                    fill: {
                        opacity: 1
                    },
                };

                var barTermsChart = new ApexCharts(document.querySelector("#barTermsChart"), optionsTermsChart);
                barTermsChart.render();
                // END #barTermsChart
                ///////////////////////////////////////////////////////////////

                ///////////////////////////////////////////////////////////////
                // START #mixedTermsChart
                var columnSeriesData = [];
                var lineSeriesData = [];
                var categories = [];

                /*for (var termId in rawTermsData) {
                    var totalComplianceYes = 0;
                    var totalComplianceNo = 0;

                    for (var date in rawTermsData[termId]) {
                        var termData = rawTermsData[termId][date];
                        totalComplianceYes += termData.filter(item => item.compliance_survey === 'yes').length;
                        totalComplianceNo += termData.filter(item => item.compliance_survey === 'no').length;
                    }

                    columnSeriesData.push(totalComplianceYes);
                    lineSeriesData.push(totalComplianceNo);
                    categories.push(terms[termId]['name']);
                }*/
                for (var termId in rawTermsData) {
                    var totalComplianceYes = 0;
                    var totalComplianceNo = 0;

                    for (var date in rawTermsData[termId]) {
                        var termData = rawTermsData[termId][date];
                        totalComplianceYes += termData.filter(item => item.compliance_survey === 'yes').length;
                        totalComplianceNo += termData.filter(item => item.compliance_survey === 'no').length;
                    }

                    var totalResponses = totalComplianceYes + totalComplianceNo;
                    var complianceYesPercentage = totalResponses > 0 ? parseFloat(((totalComplianceYes / totalResponses) * 100).toFixed(0)) : 0;
                    var complianceNoPercentage = totalResponses > 0 ? parseFloat(((totalComplianceNo / totalResponses) * 100).toFixed(0)) : 0;

                    columnSeriesData.push(complianceYesPercentage);
                    lineSeriesData.push(complianceNoPercentage);
                    categories.push(terms[termId]['name']);
                }

                var optionsMixedTermsChart = {
                    series: [{
                        name: 'Conforme',
                        type: 'column',
                        data: columnSeriesData
                    }, {
                        name: 'Não Conforme',
                        type: 'line',
                        data: lineSeriesData
                    }],
                    chart: {
                        height: 402,
                        type: 'line',
                        toolbar: {
                            show: false,
                        }
                    },
                    stroke: {
                        width: [0, 4]
                    },
                    title: {
                        text: 'Insights Comparativos de Conformidade'// Compliance Overview by Term
                    },
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [1]
                    },
                    labels: categories,
                    xaxis: {
                        type: 'category'
                    },
                    yaxis: [{
                        title: {
                            text: 'Conforme'
                        }
                    }, {
                        opposite: true,
                        title: {
                            text: 'Não Conforme'
                        }
                    }],
                    colors: ['#13c56b', '#DF5253']  // Assign custom colors to Compliance Yes and No
                };

                var mixedTermsChart = new ApexCharts(document.querySelector("#mixedTermsChart"), optionsMixedTermsChart);
                mixedTermsChart.render();
                // END #mixedTermsChart
                ///////////////////////////////////////////////////////////////

                ///////////////////////////////////////////////////////////////
                // START #polarTermsAreaChart
                var seriesData = [];
                var labels = [];

                var termMetrics = {};

                /*
                // Aggregate data for each term
                for (var termId in rawTermsData) {
                    for (var date in rawTermsData[termId]) {
                        var termData = rawTermsData[termId][date];
                        var totalCompliance = termData.filter(item => item.compliance_survey === 'yes').length;

                        if (!termMetrics[termId]) {
                            termMetrics[termId] = 0;
                        }
                        termMetrics[termId] += totalCompliance;
                    }
                }

                // Prepare data for the chart
                for (var termId in termMetrics) {
                    seriesData.push(termMetrics[termId]);
                    // Assuming 'terms' is an object where keys are term IDs and values contain term details
                    labels.push(terms[termId]['name']);
                }
                */

                // Aggregate data for each term
                for (var termId in rawTermsData) {
                    var totalComplianceYes = 0;
                    var totalComplianceNo = 0;

                    for (var date in rawTermsData[termId]) {
                        var termData = rawTermsData[termId][date];
                        totalComplianceYes += termData.filter(item => item.compliance_survey === 'yes').length;
                        totalComplianceNo += termData.filter(item => item.compliance_survey === 'no').length;
                    }

                    var totalResponses = totalComplianceYes + totalComplianceNo;
                    var compliancePercentage = totalResponses > 0 ? parseFloat(((totalComplianceYes / totalResponses) * 100).toFixed(0)) : 0;

                    termMetrics[termId] = compliancePercentage;
                }

                // Prepare data for the chart
                for (var termId in termMetrics) {
                    seriesData.push(termMetrics[termId]);
                    labels.push(terms[termId]['name']);
                }


                var optionsTermsAreaChart = {
                    series: seriesData,
                    chart: {
                        height: 280,
                        type: 'polarArea',
                        toolbar: {
                            show: false,
                        }
                    },
                    title: {
                        text: 'Análise Polar de Conformidade'// Terms Compliance Polar Analysis
                    },
                    labels: labels,
                    stroke: {
                        colors: ['#fff']
                    },
                    fill: {
                        opacity: 0.8
                    },
                    legend: {
                        show: true,
                        position: 'bottom'
                    },
                    yaxis: {
                        show: false // Disable Y-axis labels
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            }
                        }
                    }]
                };

                var polarTermsAreaChart = new ApexCharts(document.querySelector("#polarTermsAreaChart"), optionsTermsAreaChart);
                polarTermsAreaChart.render();
                // END #polarTermsAreaChart
                ///////////////////////////////////////////////////////////////
            });
        </script>
    @endif

@endsection
