<div class="modal fade zoomIn" id="assignmentsListingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{limitChars($title ?? '', 30) }} #<span class="text-theme me-2">{{$surveyId}}</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card border border-light rounded rounded-2 border-bottom-0 mb-0">
                    <div class="card-body">
                        @if($surveyAssignmentData->isNotEmpty())
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
                                        @foreach ($surveyAssignmentData as $key => $assignment)
                                            @php
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
                                            @endphp
                                            <tr class="main-row" data-id="{{ $assignmentId }}">
                                                <td>
                                                    {{ $assignment->updated_at ? date('d/m/Y H:i', strtotime($assignment->updated_at)) . 'hs' : '-' }}
                                                </td>
                                                <td>
                                                    {{getCompanyNameById($companyId)}}
                                                </td>
                                                <td>
                                                    <img src="{{ $surveyorAvatar }}" alt="{{ $surveyorName }}" class="rounded-circle avatar-xxs"> {{$surveyorName}}
                                                    @if($surveyorStatus)
                                                        <br>
                                                        <span
                                                            class="badge bg-{{ $getSurveyAssignmentStatusTranslations[$surveyorStatus]['color'] }}-subtle text-{{ $getSurveyAssignmentStatusTranslations[$surveyorStatus]['color'] }} text-uppercase mt-2"
                                                            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                            title="{{ $getSurveyAssignmentStatusTranslations[$surveyorStatus]['singular_description'] }}">
                                                            {{ $getSurveyAssignmentStatusTranslations[$surveyorStatus]['label'] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($auditorId)
                                                        <img src="{{ $auditorAvatar }}" alt="{{ $auditorName }}" class="rounded-circle avatar-xxs"> {{$auditorName}}
                                                    @endif

                                                    @if( $auditorStatus && $auditorId )
                                                        <br>
                                                        <span
                                                            class="badge bg-{{ $getSurveyAssignmentStatusTranslations[$auditorStatus]['color'] }}-subtle text-{{ $getSurveyAssignmentStatusTranslations[$auditorStatus]['color'] }} text-uppercase mt-2"
                                                            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top"
                                                            title="{{ $getSurveyAssignmentStatusTranslations[$auditorStatus]['singular_description'] }}">
                                                            {{ $getSurveyAssignmentStatusTranslations[$auditorStatus]['label'] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td scope="row" class="text-end">
                                                    <a class="btn btn-sm btn-outline-dark ri-eye-line {{ $surveyorStatus == 'new' ? 'cursor-not-allowed' : 'btn-assignment-view-form' }}"
                                                    @if ($surveyorStatus == 'new')
                                                        onclick="alert('Tarefa ainda não foi inicializada e por isso não há dados disponíveis');"
                                                    @else
                                                        href="{{route('assignmentShowURL', $assignmentId)}}"
                                                    @endif
                                                    data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ $surveyorStatus == 'new' ? 'Tarefa ainda não foi inicializada e por isso não há dados disponíveis' : 'Visualizar' }}" data-assignment-id="{{ $assignmentId }}" data-assignment-title="{{limitChars($title ?? '', 30) }}"></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            @component('components.nothing')
                                @slot('text', 'Ainda não há dados')
                            @endcomponent
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
