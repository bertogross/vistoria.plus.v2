@php
    use Carbon\Carbon;
    use App\Models\Survey;
    use App\Models\SurveyAssignments;

    $today = Carbon::now();
@endphp
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
                    {!! $dataDone && count($dataDone) > 0 ? '<span class="badge border border-dark text-body ms-2">'.count($dataDone).'</span>' : '' !!}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#nav-border-justified-available" role="tab" aria-selected="false" tabindex="-1">
                    Vistorias <span class="d-none d-sm-inline-block d-lg-inline-block">Disponíveis</span>
                    <span class="badge border border-dark text-body ms-2" id="count-available-surveyors"></span>
                </a>
            </li>
        </ul>
        <div class="tab-content text-muted">
            <div class="tab-pane active border border-1 border-light" id="nav-border-justified-done" role="tabpanel">
                <div class="card-body border border-dashed border-end-0 border-start-0 border-top-0" style="flex: inherit !important;">
                    <form action="{{ route('surveysAuditIndexURL') }}" method="get" autocomplete="off">
                        <div class="row g-3">

                            <div class="col-sm-12 col-md col-lg">
                                <input type="text" class="form-control flatpickr-range" name="created_at" placeholder="- Período -" data-min-date="{{ $firstDate ?? '' }}" data-max-date="{{ $lastDate ?? '' }}" value="{{ request('created_at', '') }}" title="Selecione o Período">
                            </div>

                            <div class="col-sm-12 col-md col-lg">
                                <label for="select-status" class="d-none" title="Selecione o Status">"Status</label>
                                <select class="form-control form-select" name="status" id="select-status" title="Selecione o Status">
                                    <option value="">- Status -</option>
                                    @foreach ( ['pending', 'in_progress', 'completed', 'losted'] as $key)
                                        <option
                                        {{ $key == request('status', null) ? 'selected' : '' }}
                                        title="{{ $getSurveyAssignmentStatusTranslations[$key]['description'] }}"
                                        value="{{ $key }}">
                                            {{ $getSurveyAssignmentStatusTranslations[$key]['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-12 col-md-auto col-lg-auto wrap-form-btn">{{-- d-none --}}
                                <button type="submit" name="filter" value="true" class="btn btn-outline-theme w-100 init-loader">
                                    <i class="ri-equalizer-fill me-1 align-bottom"></i> Filtrar
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                @if (!$dataDone || $dataDone->isEmpty())
                    @component('components.nothing')
                        {{--
                        @slot('url', route('surveysCreateURL'))
                        --}}
                    @endcomponent
                @else
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
                                    {{--
                                    <th data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="A Data da Vistoria">
                                        Vistoriado em
                                    </th>
                                    --}}
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
                                @foreach ($dataDone as $assignment)
                                    @php
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
                                    @endphp
                                    <tr class="main-row" data-id="{{ $surveyId }}">
                                        <td>
                                            <span class="text-body" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ucfirst($title)}}">
                                                {{ limitChars(ucfirst($title), 50) }}
                                            </span>

                                            <div class="text-muted small" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ limitChars(ucfirst($getSurveyTemplateNameById), 200) }}">
                                                <strong>Modelo:</strong> <span class="text-body"></span>{{ limitChars(ucfirst($getSurveyTemplateNameById), 100) }}
                                            </div>
                                        </td>
                                        <td>
                                            {{ $companyName }}
                                        </td>
                                        <td>
                                            <div class="avatar-group flex-nowrap d-inline-block align-middle">
                                                @php
                                                    $getUserData = getUserData($surveyorId);
                                                @endphp
                                                <a href="{{ route('profileShowURL', $surveyorId) }}" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: {{ $getUserData->name }} : {{ $companyName }}">
                                                    {!!snippetAvatar($getUserData->avatar, $getUserData->name, 'rounded-circle avatar-xxs')!!}
                                                </a> {{ $getUserData->name }}
                                            </div>
                                        </td>
                                        {{--
                                        <td>
                                            {{ $assignment->created_at ? date('d/m/Y', strtotime($assignment->created_at)) : '-' }}
                                        </td>
                                        --}}
                                        <td>
                                            <div class="avatar-group">
                                                @php
                                                    $getUserData = getUserData($auditorId);
                                                @endphp
                                                <div class="avatar-group flex-nowrap d-inline-block align-middle">
                                                    <a href="{{ route('profileShowURL', $auditorId) }}" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: {{ $getUserData->name }} : {{ $companyName }}">
                                                        {!!snippetAvatar($getUserData->avatar, $getUserData->name, 'rounded-circle avatar-xxs')!!}
                                                    </a> {{ $getUserData->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $assignment->updated_at ? date('d/m/Y H:i', strtotime($assignment->updated_at)) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $getSurveyAssignmentStatusTranslations[$assignmentStatus]['color'] }}-subtle text-{{ $getSurveyAssignmentStatusTranslations[$assignmentStatus]['color'] }} text-uppercase"
                                                data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                title="{{ $getSurveyAssignmentStatusTranslations[$assignmentStatus]['description'] }}">
                                                {{ $getSurveyAssignmentStatusTranslations[$assignmentStatus]['label'] }}
                                                @if ($assignmentStatus == 'started')
                                                    <span class="spinner-border align-top ms-1"></span>
                                                @endif
                                            </span>
                                        </td>
                                        <td scope="row" class="text-end">
                                            @if (in_array($auditorStatus, ['new', 'pending', 'in_progress']))
                                                <a
                                                @if (in_array($surveyorStatus, ['completed', 'auditing']))
                                                    href="{{route('formAssignmentAuditorURL', $assignmentId)}}"
                                                @else
                                                    onclick="alert('Necessário aguardar finalização da Vistoria')"
                                                @endif
                                                class="btn btn-sm btn-label right btn-soft-secondary" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Abrir formulário">
                                                    <i class="ri-fingerprint-2-line label-icon align-middle fs-16"></i> Auditar
                                                </a>
                                            @elseif (in_array($auditorStatus, ['completed', 'losted']))
                                                <a href="{{ route('assignmentShowURL', $assignmentId) }}" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Visualizar resultado" class="btn btn-sm btn-soft-dark ri-eye-line"></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3 mb-3">
                        {!! $dataDone->links('layouts.custom-pagination') !!}
                    </div>
                @endif
            </div>
            <div class="tab-pane border border-1 border-light p-3" id="nav-border-justified-available" role="tabpanel">
                @if ( $dataAvailable && is_array($dataAvailable) )
                    <p class="mb-0">Aqui estão informações sobre vistorias que foram realizadas e dentro do prazo para uma possível Auditoria.</p>
                    <div class="row">
                        @include('surveys.layouts.profile-task-card', [
                            'status' => 'completed',
                            'statusKey' => 'completed',
                            'designated' => 'surveyor',
                            'origin' => 'auditListing',
                            'data' => $dataAvailable
                        ])
                    </div>
                @else
                    <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show mb-0" role="alert">
                        <i class="ri-alert-line label-icon"></i> Não há Vistorias disponíveis para que sejam Auditadas
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
