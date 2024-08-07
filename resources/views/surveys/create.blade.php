@php
    use App\Models\UserConnections;

    $currentUserId = auth()->id();
    $currentConnectionId = getCurrentConnectionByUserId($currentUserId);
    $currentConnectionName = getConnectionNameById($currentConnectionId);

    $authorId = $data && $data->user_id ? $data->user_id : '';
    $surveyId = $data && $data->id ? $data->id : '';
    $templateId = $data && $data->template_id ? $data->template_id : '';
    $title = $data->title ?? '';
    $startDate = $data ? $data->start_date : null;
        $startDate = $startDate ? date("d/m/Y", strtotime($startDate)) : date('d/m/Y');
    $recurring = $data->recurring ?? '';
    $surveyStartAt = $data && $data->start_at ? date("d/m/Y", strtotime($data->start_at)) : '';
    $recurringSubLabel = $data && $data->start_at && $recurring == 'weekly' ? dayOfTheWeek($data->start_at) : '';
    $endIn = $data && $data->end_in ? date("d/m/Y", strtotime($data->end_in)) : '';
    $surveyStatus = $data->status ?? '';

    $alertMessage0 = $data && $countAllResponses > 0 ? '<div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-2 mb-3" role="alert">
            <i class="ri-alert-line label-icon"></i> Esta rotina já foi iniciada e a alteração do Modelo não poderá ser efetuada. Prossiga se necessitar editar as atribuições.<br>
            Se a intenção for a de modificar tópicos dos processos em andamento, não será possível devido ao armazenamento de informações para comparativo. Portanto, o caminho adequado será o de encerrar/arquivar esta atividade e gerar um novo registro.
        </div>' : '';
    $alertMessage1 = $data && $countAllResponses > 0 ? '<div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show mt-2 mb-3" role="alert">
            <i class="ri-alert-line label-icon"></i> Esta rotina já foi iniciada e a alteração da Recorrência não poderá ser efetuada. Prossiga se necessitar editar as atribuições.<br>
            Se a intenção for a de modificar a recorrência de processos em andamento, não será possível devido ao armazenamento de informações para comparativo. Portanto, o caminho adequado será o de encerrar/arquivar esta atividade e gerar um novo registro.
        </div>' : '';

    $alertMessage2 = $data && $countAllResponses > 0 ? '<div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show mt-2 mb-3" role="alert">
            <i class="ri-alert-line label-icon"></i> Esta rotina já foi iniciada e a alteração em Unidades não poderá ser efetuada. Prossiga se necessitar editar as atribuições.<br>
            Se a intenção for a de ativar/desativar unidades de processos em andamento, não será possível devido ao armazenamento de informações para comparativo. Portanto, o caminho adequado será o de encerrar/arquivar esta atividade e gerar um novo registro.
        </div>' : '';

    $alertMessage3 = $data && $countTodayResponses > 0 ? '<div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show mt-2 mb-3" role="alert">
        <i class="ri-alert-line label-icon"></i> Esta tarefa já está recebendo dados. Portanto, alterações em Atribuições poderão ser efetuadas mas só terão efeito a partir da próxima interação. Por exemplo, no caso de tarefas diárias, a próxima interação ocorrerá amanhã.
    </div>' : '';

    //appPrintR($users);
    //appPrintR($distributedData);
@endphp
<div class="modal fade" id="surveysModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-soft-info">
                <h5 class="modal-title">
                    @if($surveyId)
                        Edição de: <span class="text-theme">{{ limitChars(getSurveyNameById($surveyId), 80) }}</span>
                    @else
                        Novo Checklist
                    @endif
                </h5>
                <button type="button" class="btn-close btn-destroy" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-body bg-gradient">
                @if( $data && $authorId && $authorId != auth()->id() )
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                        <i class="ri-alert-line label-icon blink"></i> Você não possui autorização para editar um registro gerado por outra pessoa
                    </div>
                    @php
                        exit;
                    @endphp
                @endif

                @if(!in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]))
                    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-alert-line label-icon"></i> <strong><u>{{$currentConnectionName}}</u></strong> não lhe concedeu o Nível de Autorização para gerar o registro de um Checklist
                    </div>
                @else
                    <form id="surveysForm" method="POST" class="needs-validation form-steps" novalidate autocomplete="off">
                        @csrf
                        <input type="hidden" name="id" value="{{ $surveyId }}">

                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-pills-theme nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link active" data-bs-toggle="pill" role="tab"
                                    id="steparrow-users-info-tab"
                                    data-bs-target="#steparrow-users-info"
                                    aria-controls="steparrow-users-info"
                                    aria-selected="false" disabled>
                                        <i class="ri-team-line"></i>
                                        <span class="d-none d-lg-inline-block ms-1">Atribuições</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="pill" role="tab"
                                    id="steparrow-template-info-tab"
                                    data-bs-target="#steparrow-template-info"
                                    aria-controls="steparrow-template-info"
                                    aria-selected="true" disabled>
                                        <i class="ri-todo-line"></i>
                                        <span class="d-none d-lg-inline-block ms-1">Modelo</span>
                                    </button>
                                </li>
                                {{--
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="pill" role="tab"
                                    id="steparrow-companies-info-tab"
                                    data-bs-target="#steparrow-companies-info"
                                    aria-controls="steparrow-companies-info"
                                    aria-selected="true" disabled>
                                        <i class="ri-store-2-line"></i>
                                        <span class="d-none d-lg-inline-block ms-1">Unidades</span>
                                    </button>
                                </li>
                                --}}
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="pill" role="tab"
                                    id="steparrow-recurring-info-tab"
                                    data-bs-target="#steparrow-recurring-info"
                                    aria-controls="steparrow-recurring-info"
                                    aria-selected="false" disabled>
                                        <i class="ri-calendar-2-line"></i>
                                        <span class="d-none d-lg-inline-block ms-1">Recorrencia</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="pill"
                                    id="steparrow-success-tab"
                                    data-bs-target="#steparrow-success"
                                    aria-controls="steparrow-success"
                                    aria-selected="false" disabled>
                                        <i class="ri-checkbox-circle-line"></i>
                                        <span class="d-none d-lg-inline-block ms-1">Finalizado</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">

                            <div id="steparrow-users-info" class="tab-pane fade show active" role="tabpanel" aria-labelledby="steparrow-users-info-tab">
                                @if ( empty($getActiveCompanies) || !is_array($getActiveCompanies) )
                                    @component('components.nothing')
                                        @slot('warning', 'Será necessário primeiramente solicitar a(o) Administrador(a) a autorização de acesso a Unidades')
                                    @endcomponent
                                @elseif ($users->isEmpty())
                                    @component('components.nothing')
                                        @slot('warning', 'Não há usuários cadastrados/ativos. Consulte o(a) Administrador(a)')
                                    @endcomponent
                                @else
                                    <div>
                                        <label class="form-label mb-0">Atribuições para este Checklist:</label>

                                        @if (in_array(getUserRoleById($currentUserId, $currentConnectionId), [1]))
                                            <button id="btn-add-user" type="button" class="btn btn-sm btn-outline-info float-end" data-origin="survey" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="Convidar Usuário" data-bs-content="Caso necessite convidar outro usuário para colaborar com esta atividade, clique em adicionar.">
                                                Convidar
                                            </button>
                                        @endif

                                        {{--
                                        @if ($getActiveCompanies && count($getActiveCompanies) > 1)
                                            <div class="form-text mt-0 mb-3">Selecione quem irá desempenhar esta tarefa. 1 colaborador(a) por Unidade</div>
                                        @else
                                            <div class="form-text mt-0 mb-3">Selecione o(a) colaborador(a) que irá desempenhar esta tarefa</div>
                                        @endif
                                        --}}
                                        <div class="form-text mt-0 mb-3">Ative as unidades que irá vistoriar e selecione para cada quem irá desempenhar as tarefas. 1 colaborador(a) por Unidade</div>

                                        {!! $alertMessage3 !!}

                                        <div id="load-users-tab" data-survey-id="{{$surveyId}}" class="row">
                                            @include('surveys.layouts.create-users-tab')
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start gap-3 mt-4">
                                        <button type="button" class="btn btn-outline-theme btn-label right ms-auto nexttab" data-nexttab="steparrow-template-info-tab"><i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Próximo</button>
                                    </div>
                                @endif
                            </div>

                            <div id="steparrow-template-info" class="tab-pane fade" role="tabpanel" aria-labelledby="steparrow-template-info-tab">

                                @if ( !$templates || $templates->isEmpty() )
                                    @component('components.nothing')
                                        @slot('warning', 'Será necessário primeiramente <u>Compor</u> um Formulário <u>Modelo</u>')
                                    @endcomponent
                                @else

                                    <div class="mb-3">
                                        <label for="title-field" class="form-label">Título deste Checklist:</label>
                                        <input type="text" class="form-control wizard-input-control" maxlength="100" id="title-field" name="title" name="template_id" value="{{$title}}" required>
                                        <div class="form-text">O título servirá para identificar este registro na listagem e também será exibido no formulário da tarefa.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="template-field" class="form-label">Importar o Modelo:</label>
                                        @if ( $data && $countAllResponses > 0 && !in_array($surveyStatus, ['new', 'scheduled']) )
                                            {!! $alertMessage0 !!}
                                            <input type="hidden" name="template_id" value="{{$templateId}}">
                                        @else
                                            <select name="template_id" id="template-field" class="form-control form-select wizard-input-control" {{ $countAllResponses > 0 ? 'readonly' : '' }} required>
                                                <option value="" {{ !$templateId ? 'selected' : 'disabled'  }}>- Selecione -</option>
                                                @foreach ($templates as $template)
                                                    <option value="{{$template->id}}" {{ isset($template->id) && $template->id == $templateId ? 'selected' : ''}}>{{ $template->title ? e($template->title) : '' }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>

                                    <div class="d-flex align-items-start gap-3 mt-4">
                                        <button type="button" class="btn btn-light btn-label previestab" data-previous="steparrow-users-info-tab"><i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Voltar</button>

                                        <button type="button" class="btn btn-outline-theme btn-label right ms-auto nexttab" data-nexttab="steparrow-recurring-info-tab"><i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Próximo</button>
                                    </div>
                                @endif
                            </div>

                            {{--
                            <div id="steparrow-companies-info" class="tab-pane fade" role="tabpanel" aria-labelledby="steparrow-companies-info-tab">
                                <div class="mb-4">
                                    <label class="mb-0">Unidades:</label>
                                    @if ( $data && $countAllResponses > 0 && !in_array($surveyStatus, ['new', 'scheduled']) )
                                        {!! $alertMessage2 !!}

                                        @foreach($getActiveCompanies as $company)
                                            @if (!empty($selectedCompanies) && is_array($selectedCompanies) && in_array(intval($company->id), $selectedCompanies))
                                                <input type="hidden" name="companies[]" value="{{ $company->id }}">
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="form-text mb-3">Selecione a(s) unidade(s) que serão relacionadas a este Checklist</div>
                                        <div class="row">
                                            @foreach($getActiveCompanies as $company)
                                                <div class="col">
                                                    <div class="form-check form-switch form-switch-success form-switch-md">
                                                        <input
                                                        class="form-check-input form-check-input-companies wizard-switch-control"
                                                        type="checkbox"
                                                        role="switch"
                                                        {{ !empty($selectedCompanies) && is_array($selectedCompanies) && in_array(intval($company->id), $selectedCompanies) ? 'checked' : '' }}
                                                        id="company-{{ $company->id }}"
                                                        name="companies[]"
                                                        value="{{ $company->id }}"
                                                        required>
                                                        <label class="form-check-label" for="company-{{ $company->id }}">{{ empty($company->name) ? e($company->name) : e($company->name) }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex align-items-start gap-3 mt-5">
                                    <button type="button" class="btn btn-light btn-label previestab" data-previous="steparrow-template-info-tab"><i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Voltar</button>

                                    <button type="button" class="btn btn-outline-theme btn-label right ms-auto nexttab" data-nexttab="steparrow-recurring-info-tab"><i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Próximo</button>
                                </div>
                            </div>
                            --}}

                            <div id="steparrow-recurring-info" class="tab-pane fade" role="tabpanel" aria-labelledby="steparrow-recurring-info-tab">
                                <div class="mb-3">
                                    <label for="date-recurring-field" class="form-label">
                                        Tipo de Recorrência:
                                        {!! isset($getSurveyRecurringTranslations[$recurring]['label']) ? '<small class="badge badge-border bg-light-subtle text-body text-uppercase">' .$getSurveyRecurringTranslations[$recurring]['label']. '</small>' : '' !!}
                                        {!! $recurringSubLabel ? '<small class="badge badge-border bg-light-subtle text-body text-uppercase">' . $recurringSubLabel . '</small>' : '' !!}

                                    </label>
                                    @if ( $data && $countAllResponses > 0 && !in_array($surveyStatus, ['new', 'scheduled']) )
                                        {!! $alertMessage1 !!}
                                        <input type="hidden" name="recurring" value="{{$recurring}}">
                                    @else
                                        <select name="recurring" id="date-recurring-field" class="form-control form-select wizard-input-control" required {{ $countAllResponses > 0 ? 'readonly' : '' }}>
                                            <option value="" {{ !$recurring ? 'selected disabled' : 'disabled' }}>- Selecione -</option>
                                            @foreach ($getSurveyRecurringTranslations as $index => $value)
                                                <option value="{{$index}}" {{ $recurring == $index ? 'selected' : ''}}>{{ $value['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col">
                                            <label for="date-recurring-start" class="form-label">Data Inicial:</label>
                                            <input id="date-recurring-start" type="text" class="form-control flatpickr-input flatpickr-between wizard-input-control" {{ $countAllResponses > 0 && in_array($surveyStatus, ['started', 'stopped', 'completed', 'filed']) ? 'disabled readonly' : '' }} name="start_at" data-date-format="d/m/Y" value="{{ $surveyStartAt }}">
                                            @if ( $countAllResponses > 0 && in_array($surveyStatus, ['started', 'stopped', 'completed', 'filed']) )
                                                <div class="form-text text-warning">O campo Data Inicial não poderá ser modificado pois esta rotina já foi inicializada.</div>
                                            @else
                                                <div class="form-text">Deixe vazio para iniciar manualmente (botão "Inicializar").</div>
                                                <div class="form-text">Preencha para iniciar automaticamente (poderá interromper quando quiser).</div>
                                            @endif
                                        </div>

                                        <div class="col">
                                            <label for="date-recurring-end" class="form-label">Data Final:</label>
                                            <input id="date-recurring-end" type="text" class="form-control flatpickr-input flatpickr-between wizard-input-control" name="end_in" data-date-format="d/m/Y" value="{{ $endIn }}">
                                            <div class="form-text">Deixe vazio se o prazo for indeterminado.</div>
                                            <div class="form-text">Preencha para determinar o encerramento do ciclo de vistoria.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="button" class="btn btn-light btn-label previestab" data-previous="steparrow-template-info-tab"><i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Voltar</button>

                                    <button type="button" class="btn btn-outline-theme btn-label right ms-auto nexttab" data-nexttab="steparrow-success-tab"><i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Próximo</button>
                                </div>
                            </div>

                            <div id="steparrow-success" class="tab-pane fade" role="tabpanel" aria-labelledby="steparrow-success-tab">
                                <div class="text-center">
                                    <div class="avatar-md mt-5 mb-4 mx-auto">
                                        <div class="avatar-title bg-light text-success display-4 rounded-circle">
                                            <i class="ri-checkbox-circle-fill"></i>
                                        </div>
                                    </div>
                                    <h5>Formulário preenchido com sucesso!</h5>
                                </div>

                                <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="button" class="btn btn-light btn-label previestab" data-previous="steparrow-recurring-info-tab"><i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i> Voltar</button>

                                    <button type="button" id="btn-surveys-store-or-update" class="btn btn-outline-success btn-label right ms-auto"><i class="ri-save-3-line label-icon align-middle fs-16 ms-2"></i>Salvar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
