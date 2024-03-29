@php
    use Carbon\Carbon;
    use App\Models\Survey;
    use App\Models\SurveyResponseTopic;
    use App\Models\SurveyResponse;
    use App\Models\SurveyAssignments;

    $today = Carbon::now();

    $origin = $origin ?? '';

    $countAvaliables = 0;

    $currentUserId = auth()->id();

    $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

    $getSurveyRecurringTranslations = Survey::getSurveyRecurringTranslations();
@endphp
@if ( !empty($data) && is_array($data) )
    @foreach ($data as $key => $assignment)
        @php
            $surveyId = intval($assignment['survey_id']);
            $survey = Survey::find($surveyId);
            if(!$survey){
                break;
            }

            $assignmentId = intval($assignment['id']);
            $createdAt = $assignment['created_at'];

            $title = $survey->title;
            $startAt = $survey->start_at;

            $recurring = $survey->recurring;
            $recurringLabel = $getSurveyRecurringTranslations[$recurring]['label'];

            $deadline = SurveyAssignments::getSurveyAssignmentDeadline($recurring, $createdAt);
            $deadlineFormated = $deadline->format('Ymd');
            $deadline = $deadline->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY');

            $deadlineScheduled = SurveyAssignments::getSurveyAssignmentDeadlineScheduled($recurring, $startAt);
            $deadlineScheduled = $deadlineScheduled->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY');

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

            $totalTopics = SurveyResponseTopic::countSurveyTopics($surveyId);

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
        @endphp
        @if($origin == 'auditListing')
            <div class="{{ $countAvaliables > 0 ? 'col-sm-12 col-md-6 col-lg-4 col-xxl-3 mt-4' : 'col-12 text-center' }}">
                @if($countAvaliables == 0)
                    @component('components.nothing')
                        @slot('text', 'Não há tarefas disponíveis para uma Auditoria')
                    @endcomponent
                @endif
        @endif

        <div class="card tasks-box {{in_array($statusKey, ['losted']) ? ' border-1 border-danger border-opacity-50 ' : ''}}" data-assignment-id="{{$assignmentId}}">
            <div class="card-header border-bottom border-1 border-bottom-dashed {{in_array($statusKey, ['losted']) ? 'border-danger border-opacity-50 bg-danger-subtle' : ' bg-body'}}">
                <div class="row">
                    <div class="col fw-medium fs-15 {{in_array($statusKey, ['losted']) ? 'text-body' : 'text-theme'}}">
                        {{ $companyName }}
                    </div>
                    <div class="col-auto">
                        @if( $surveyorStatus == 'completed' && $auditorStatus == 'completed')
                            {{--
                            <span class="badge bg-success-subtle text-success badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{ $labelTitle }}">
                                <span class="text-info">Vistoria</span>
                                <span class="text-body">+</span>
                                <span class="text-secondary">Auditoria</span>
                            </span>
                            --}}

                            <span class="badge bg-success-subtle text-success badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{ $labelTitle }}">
                                <span class="text-info">Vistoriado</span>
                            </span>

                            <span class="badge bg-success-subtle text-success badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{ $labelTitle }}">
                                <span class="text-secondary">Auditado</span>
                            </span>

                        @elseif($designated == 'surveyor')
                            <span class="badge bg-dark-subtle text-body badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{ $labelTitle }}">
                                Vistoria
                                @if ( in_array($statusKey, ['completed']) && $surveyorStatus == 'completed' && $auditorStatus == 'completed' )
                                    <i class="ri-check-double-fill ms-2 text-success"></i>
                                @endif
                            </span>
                        @elseif($designated == 'auditor')
                            <span class="badge bg-dark-subtle text-body badge-border" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{ $labelTitle }}">
                                Auditoria
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body {{in_array($statusKey, ['losted']) ? ' bg-danger-subtle' : ' bg-body'}}">
                <h5 class="fs-13 text-truncate task-title mb-0" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{ $title }}">
                    {{ $title }}
                </h5>

                <ul class="list-group mt-3">
                    <li class="list-group-item">
                        <i class="ri-checkbox-line align-top me-2 text-info"></i> {{ $totalTopics }} Tópicos
                        @if (in_array($statusKey, ['completed', 'losted']))
                            <div class="progress progress-sm bg-success-subtle rounded-2 mt-3" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{$percentYes}}% de Conformidades relatadas por {{$surveyorName}}">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{$percentYes}}%;" aria-valuenow="{{$percentYes}}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="progress progress-sm bg-danger-subtle rounded-2 mt-2" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="bottom" title="{{$percentNo}}% de Inconformidades relatadas por {{$surveyorName}}">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{$percentNo}}%;" aria-valuenow="{{$percentNo}}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endif
                    </li>
                    <li class="list-group-item"
                    @if($recurring != 'once')
                        data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Recorrência iniciada em {{ $startAt->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY'); }}"
                    @endif
                    >
                        <i class="ri-refresh-line align-top me-2 text-info"></i> Recorrência: {{ $recurringLabel }}
                        @if($recurring != 'once')
                            <div class="fs-10 fw-normal text-muted ms-4">
                                {{ $startAt->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY'); }}
                            </div>
                        @endif
                    </li>
                    <li class="list-group-item" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="{{$surveyorStatus == 'scheduled' ? 'A data em que esta tarefa se tornará Vigente' : 'A data limite para execução desta tarefa'}}">
                        @if($surveyorStatus == 'scheduled')
                            <i class="ri-timer-line align-top me-2 text-warning"></i> Data: {{ $deadlineScheduled }}
                        @else
                            <i class="ri-calendar-todo-line align-top me-2 {{in_array($statusKey, ['losted']) ? 'text-danger' : 'text-info'}}"></i> Prazo: {{ $deadline }}
                        @endif
                    </li>
                </ul>

                @if (in_array($statusKey, ['losted']))
                    @if ( $surveyorStatus == 'losted' && $auditorStatus == 'losted' )
                        <div class="text-danger small mt-2">
                            Esta <u>Auditoria</u> foi perdida pois a <u>Vistoria</u> não foi efetuada na data prevista.
                        </div>
                    @elseif ( $surveyorStatus == 'completed' && $auditorStatus == 'losted' )
                        <div class="text-warning small mt-2">
                            A <u>Vistoria</u> foi completada. Entretanto, a <u>Auditoria</u> não foi concluída em tempo.
                        </div>
                    @elseif ( $surveyorStatus != 'completed' && $surveyorStatus != 'losted' && $auditorStatus == 'losted' )
                        <div class="text-warning small mt-2">
                            Esta tarefa foi perdida pois não foi efetuada na data prevista.
                        </div>
                    @endif
                @endif
            </div>
            <!--end card-body-->
            <div class="card-footer border-top-dashed {{in_array($statusKey, ['losted']) ? ' bg-danger-subtle border-danger border-opacity-50' : ' bg-body'}}">
                <div class="row">
                    <div class="col small">
                        <div class="avatar-group ps-0">
                            @if ($surveyorId === $auditorId)
                                <a href="{{ route('profileShowURL', $surveyorId) }}" class="d-inline-block me-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Tarefas delegadas a <u>{{ $surveyorName }}</u>">
                                    {!!snippetAvatar($surveyorAvatar, $surveyorName, 'rounded-circle avatar-xxs border border-1 border-white')!!}
                                </a>
                            @else
                                <a href="{{ route('profileShowURL', $surveyorId) }}" class="d-inline-block me-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Tarefa de Vistoria delegada a <u>{{ $surveyorName }}</u>">
                                    {!!snippetAvatar($surveyorAvatar, $surveyorName, 'rounded-circle avatar-xxs border border-1 border-white')!!}
                                </a>

                                @if($auditorId)
                                    <a href="{{ route('profileShowURL', $auditorId) }}" class="d-inline-block ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-trigger="hover" data-bs-placement="top" title="Tarefa de Auditoria requisitada por <u>{{ $auditorName }}</u>">
                                        {!!snippetAvatar($auditorAvatar, $auditorName, 'rounded-circle avatar-xxs border border-1 border-secondary')!!}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        @if ($currentUserId == $designatedUserId && in_array($statusKey, ['new','pending','in_progress']) )
                            <button type="button"
                                data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                title="{{ $status['reverse'] ?? '' }}"
                                class="btn btn-sm btn-label right btn-{{$status['color']}} {{ $designated == 'surveyor' ? 'btn-assignment-surveyor-action' : 'btn-assignment-auditor-action' }}"
                                data-survey-id="{{$surveyId}}"
                                data-assignment-id="{{$assignmentId}}"
                                data-current-status="{{$statusKey}}">
                                    <i class="{{$status['icon']}} label-icon align-middle fs-16"></i> {{ $status['reverse'] ?? '' }}
                            </button>

                            @if ( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]) && in_array($statusKey, ['new','pending','in_progress','completed']) )
                                <a href="{{ route('assignmentShowURL', $assignmentId) }}"
                                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                    title="Visualizar"
                                    class="btn btn-sm btn-dark ri-eye-line">
                                </a>
                            @endif
                        @elseif(
                            (
                                ( $currentUserId === $surveyorId || $currentUserId === $auditorId ) &&
                                in_array($statusKey, ['completed', 'losted']) ) ||
                                (
                                    in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2])  &&
                                    in_array($statusKey, ['completed', 'losted'])
                                )
                            )
                            <a href="{{ route('assignmentShowURL', $assignmentId) }}"
                                data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                title="Visualizar"
                                class="btn btn-sm btn-label right btn-{{in_array($statusKey, ['losted']) ? 'light' : 'dark'}}">
                                    <i class="ri-eye-line label-icon align-middle"></i> Visualizar
                            </a>
                        @endif

                        @if($surveyorStatus == 'scheduled')
                            <button type="button"
                            data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                            title="Pré-visualizar Formulário"
                            data-assignment-id="{{ $assignmentId }}"
                            class="btn btn-sm btn-label right btn-dark btn-assignment-form-preview">
                                <i class="ri-survey-line label-icon align-middle"></i> Pré-visualizar
                            </button>
                        @endif

                        {{--
                        @if ( $surveyorStatus == 'completed' && $auditorStatus == 'losted' && in_array($statusKey, ['losted']) )
                            <a href="{{ route('assignmentShowURL', $assignmentId) }}"
                                data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                title="Visualizar"
                                class="btn btn-sm btn-label right btn-dark">
                                    <i class="ri-eye-line label-icon align-middle fs-16"></i> Visualizar
                            </a>
                        @endif
                        --}}

                        @if ( $currentUserId === $designatedUserId && $designated === 'surveyor' && $surveyorId === $auditorId && in_array($statusKey, ['auditing']) )
                            <i class="text-theme ri-questionnaire-fill" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Neste contexto a você foram delegadas tarefas de Vistoria e Auditoria.<br>Procure na coluna <b>Nova</b> o card correspondente a <b>{{ $companyName }}</b> de prazo: <b>{{ $deadline }}</b> e inicialize a tarefa "></i>
                        @endif
                    </div>
                </div>
            </div>
            <!--end card-body-->
            @if ( in_array($statusKey, ['in_progress']) )
                <div class="progress progress-sm animated-progress custom-progress p-0 rounded-bottom-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ $percentage }}%">
                    <div class="progress-bar bg-{{ $progressBarClass }} rounded-0" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            @endif
        </div>

        @if($origin == 'auditListing')
            </div>
        @endif
    @endforeach
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('count-available-surveyors')){
            document.getElementById('count-available-surveyors').innerHTML = '{{$countAvaliables > 0 ? $countAvaliables : ''}}';
        }
    });
</script>
