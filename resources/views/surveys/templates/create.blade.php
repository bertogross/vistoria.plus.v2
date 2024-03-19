@extends('layouts.master')
@section('title')
    @lang('translation.surveys')
@endsection
@section('css')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('url')
            {{ route('surveysIndexURL') }}
        @endslot
        @slot('li_1')
            @lang('translation.surveys')
        @endslot
        @slot('title')
            @if($data)
                Edição de Modelo <i class="ri-arrow-drop-right-line text-theme ms-2 me-2 align-bottom"></i>
                <small>
                    #<span class="text-theme me-2">{{$data->id}}</span> {{ limitChars($data->title ?? '', 20) }}
                </small>
            @else
                Compor Formulário Modelo
            @endif
        @endslot
    @endcomponent

    @include('components.alerts')

    @php
        //appPrintR($data);
        //appPrintR($result);
        //appPrintR($templates);
        $templateId = $data->id ?? '';
        $authorId = $data->user_id ?? '';
        $title = $data->title ?? '';
        $description = $data->description ?? '';

        $countSurveys = isset($surveysCount) && $surveysCount > 0 ? $surveysCount : 0;
        $countSurveysText = $surveysCount > 1 ? 'Este modelo está sendo utilizado por '.$countSurveys.' Checklists. A edição deste não influênciará nos dados das rotinas que estão em andamento.' : 'Este modelo está sendo utilizado em 1 Checklist. A edição deste não influênciará nos dados da rotina que está em andamento.';
        $countSurveysText .= '<br><br>Se a intenção for a de modificar tópicos dos processos em andamento, não será possível devido ao armazenamento de informações para comparativo. Portanto, o caminho ideal será encerrar determinado Checklist e gerar um novo registro. Se este for o caso, prossiga com a edição deste modelo e reutilize-o gerando um novo Checklist.'
    @endphp

    @if( $authorId && $authorId != auth()->id())
        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
            <i class="ri-alert-line label-icon blink"></i> Você não possui autorização para editar um registro gerado por outra pessoa
        </div>
    @else
        @if($countSurveys)
            <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon"></i> {!! $countSurveysText !!}
            </div>
        @endif

        <div class="card">
            @if ($data && $data->title)
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="ri-checkbox-line fs-16 align-middle text-theme me-2"></i>{{ $data->title }}</h4>
                </div>
            @endif
            <div class="card-body">
                <form id="surveyTemplateForm" method="POST" class="needs-validation" novalidate autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg mb-5">
                            <div class="p-3">
                                @if ($templateId)
                                    <p class="text-body mb-4">A edição deste não implicará em tarefas outrora delegadas e não serão modificados Termos e Tópicos de Checklists já inicializados.</p>
                                @endif

                                @if (!$templateId)
                                    <div id="accordion-templates-label">
                                        <p class="text-body mb-4">Componha seu Modelo de Checklist.<br>Você poderá configurar seu próprio formulário cadastrando seus Termos e Tópicos inicializando com um dos Modelos pré-configurados.</p>

                                        <div class="dropstart float-end mt-n2">
                                            <button type="button" class="btn btn-sm fs-4 pe-0 me-n2" data-bs-toggle="dropdown" aria-expanded="true" data-bs-auto-close="outside" title="Opções"><i class="ri-more-2-line text-theme"></i></button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item cursor-pointer text-body" id="btn-start-empty-template" data-bs-toggle="tooltip" data-bs-placement="top" title="Nesta opção nenhum modelo será carregado e você terá a liberade de criar os Termos e Tópicos da forma que desejar.">Carregar Ambiente Limpo</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <label for="warehouse" class="form-label">Selecione um Modelo:</label>
                                    </div>

                                    <!-- Base Example -->
                                    <div class="accordion" id="accordion-templates">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button {{ $templates->isEmpty() ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="{{ $templates->isEmpty() ? 'true' : '' }}" aria-controls="collapseOne" title="Expandir/Contrair">
                                                    <strong class="text-theme me-1">{{ count(getWarehouseTrakings()) }}</strong>
                                                    Modelos Pré-configurados
                                                </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse {{ $templates->isEmpty() ? 'show' : '' }}" aria-labelledby="headingOne" data-bs-parent="#accordion-templates">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        @foreach (getWarehouseTrakings() as $tracking)
                                                            @php
                                                                $TermsCount = getWarehouseTermsCount($tracking->id);
                                                            @endphp
                                                            <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3">
                                                                <div class="card bg-body">
                                                                    <div class="card-header bg-body fw-bold text-theme">
                                                                        <span class="badge badge-pill bg-body-tertiary text-body-tertiary float-end" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{$tracking->name}} contém {{$TermsCount}} termos">
                                                                            {{$TermsCount}}
                                                                        </span>
                                                                        {{$tracking->name}}
                                                                    </div>
                                                                    <div class="card-body bg-body">
                                                                        <p style="min-height: 67px;">{{$tracking->description}}</p>
                                                                    </div>
                                                                    <div class="card-footer bg-body">
                                                                        <div class="row">
                                                                            <div class="col">
                                                                                <button type="button" class="btn btn-sm btn-outline-info w-100 mt-0 btn-warehouse-template-preview" data-id="{{$tracking->id}}" data-title="{{$tracking->name}}" title="Clique para visualizar Modelo">Visualizar</button>
                                                                            </div>
                                                                            <div class="col">
                                                                                <button type="button" class="btn btn-sm btn-outline-success w-100 mt-0 btn-warehouse-load-selected-template" data-id="{{$tracking->id}}" title="Clique para personalizar este Modelo">Personalizar</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($templates->isNotEmpty())
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingTwo">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" title="Expandir/Contrair">
                                                        @php
                                                            $teampltesCount = $templates->isNotEmpty() ? count($templates) : 0;
                                                            $plural = $teampltesCount > 1 ? 's' : '';
                                                        @endphp
                                                        {!! $teampltesCount > 0 ? '<strong class="text-theme">'.count($templates).'</strong> ' : 'Nenhum ' !!}
                                                        <span class="ms-1">Modelo{{$plural}} Personalizado{{$plural}}</span>
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordion-templates">
                                                    <div class="accordion-body">

                                                        @if ($templates->isEmpty())
                                                            @component('components.nothing')
                                                                {{--
                                                                @slot('url', route('surveysTemplateCreateURL'))
                                                                --}}
                                                            @endcomponent
                                                        @else
                                                            <div class="row">
                                                                @foreach ($templates as $template)
                                                                    <div class="col-sm-12 col-md-6 col-lg-4 col-xxl-3">
                                                                        <div class="card bg-body">
                                                                            <div class="card-header bg-body fw-bold text-theme">
                                                                                <button type="button" class="btn btn-sm btn-outline-{{ $template->condition_of == 'filed' ? 'warning' : 'success' }} float-end mt-n1 position-absolute end-0 me-3 btn-change-template-status" data-bs-toggle="popover" data-bs-placement="top" data-bs-html="true" data-bs-trigger="hover focus" data-bs-title="Status" data-bs-content="Se arquivado, nenhuma alteração será efetuada em tarefas em andamento, somente deixará de ser exibido para seleção no registro de um novo Checklist.<br><br><small>Clique se desejar alternar o Status entre Ativo/Arquivado.</small>" data-id="{{$template->id}}" data-title="{{$template->title}}">{{ $template->condition_of == 'filed' ? 'Arquivado' : 'Ativo' }}</button>
                                                                                {{$template->title}}
                                                                            </div>
                                                                            <div class="card-body bg-body">
                                                                                <p style="min-height: 67px;">
                                                                                    {!! limitChars($template->description ?? '<span class="text-warning text-opacity-50">Não foi inserida a descrição</span>', 190) !!}
                                                                                </p>
                                                                            </div>
                                                                            <div class="card-footer bg-body">
                                                                                <div class="row">
                                                                                    <div class="col">
                                                                                        <div class="btn-group">
                                                                                            <button type="button" class="btn btn-sm btn-outline-info w-100 btn-user-template-preview" data-id="{{$template->id}}" data-title="{{$template->title}}" title="Clique para visualizar este Modelo">Visualizar</button>

                                                                                            <a href="{{ route('surveysTemplateEditURL', $template->id) }}" class="btn btn-sm btn-outline-info w-100" title="Clique para editar este Modelo">Editar</a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col">
                                                                                        <button type="button" class="btn btn-sm btn-outline-success w-100 btn-SurveyTemplate-load-selected-template" data-id="{{$template->id}}" title="Clique para clonar e utilizar este Modelo">Clonar</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div id="nested-compose-area" style="min-height: 250px; display: {{ $result && $templateId ? 'block' : 'none' }};">
                                    <input type="hidden" name="id" value="{{ $templateId }}">

                                    <div class="mb-4">
                                        <label for="title" class="form-label">Nome do Modelo:</label>
                                        <input type="text" id="title" name="title" class="form-control" value="{{ $title }}" maxlength="100" required>
                                        <div class="form-text">O "Nome do Modelo" será exibido na listagem</div>
                                    </div class="mb-4">

                                    <div class="mb-5">
                                        <label for="description" class="form-label">Descrição:</label>
                                        <textarea name="description" class="form-control maxlength" id="description" rows="5" maxlength="500" placeholder="Descreva, por exemplo, as diretrizes para execução dos tópicos relacionados a este modelo">{{ $description }}</textarea>
                                        <div class="form-text">Opcional</div>
                                    </div>

                                    <hr class="w-50 start-50 position-relative translate-middle-x clearfix mt-5 mb-5">

                                    @if (!$templateId)
                                        <button type="button" id="btn-select-another-template" class="btn btn-sm btn-outline-theme float-end" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Este formulário ainda não foi salvo e por isto você ainda poderá escolher outro modelo">Escolher outro Modelo</button>
                                        <p>
                                            <strong class="text-info">Este é seu novo modelo.</strong><br>
                                            Você poderá editar, reordenar, adicionar ou remover Termos/Tópicos.
                                        </p>
                                    @endif

                                    <div id="load-selected-template-form" class="accordion list-group nested-list nested-sortable-block">@if ($result && $templateId)
                                        @include('surveys.templates.form', ['data' => $result] )
                                    @endif</div>

                                    <button type="button" class="btn btn-sm btn-outline-theme btn-label right mt-4" data-bs-toggle="modal" data-bs-target="#addTermModal" tabindex="-1" title="Adicionar Termo Customizado"><i class="ri-terminal-window-line label-icon align-middle fs-16 ms-2"></i>Adicionar Termo</button>

                                    <div class="mt-2 text-end">
                                        {{--
                                            <button type="button" class="d-none" id="btn-survey-template-autosave" data-autosave="yes" tabindex="-1"></button>
                                        --}}

                                        <a href="{{route('surveysIndexURL')}}" onclick="return confirm('Deseja sair sem {{ $data ? 'Atualizar' : 'Salvar' }} o Modelo?');" class="btn btn-sm btn-label left btn-dark mt-5 float-start" tabindex="-1" data-autosave="no"><i class="ri-logout-box-line label-icon align-middle fs-16 me-1"></i>Sair</a>

                                        <button type="button" class="btn btn-label right btn-theme mt-5" id="btn-survey-template-store-or-update" tabindex="-1" data-autosave="no"><i class="ri-save-3-line label-icon align-middle fs-16 ms-1"></i>{{ $data ? 'Atualizar' : 'Salvar' }} Modelo</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div id="load-preview" class="col-sm-12 col-md-12 col-lg load-preview-container p-3 border border-1 border-light rounded h-100"></div>
                    </div>
                </form>
            </div>
        </div>

        <div id="addTermModal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-right">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalgridLabel">Termos Customizados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div id="load-terms-form" class="modal-body">
                            @include('surveys.terms.form', ['terms' => $terms] )
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="previewTemplateModal" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header pb-3">
                            <h5 class="modal-title text-uppercase" id="modal-template-title"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div id="load-template-preview" class="modal-body bg-body">
                            <div class="text-center"><div class="spinner-border text-theme" role="status"><span class="sr-only">Loading...</span></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/sortablejs/Sortable.min.js') }}"></script>

    <script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/l10n/pt.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/plugins/monthSelect/index.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/plugins/confirmDate/confirmDate.js') }}"></script>

    <script>
        var surveysIndexURL = "{{ route('surveysIndexURL') }}";
        var surveysTemplateEditURL = "{{ route('surveysTemplateEditURL') }}";
        var surveysTemplatePreviewFromSurveyTemplatesURL = "{{ route('surveysTemplatePreviewFromSurveyTemplatesURL') }}";
        var surveysTemplatePreviewFromWarehouseURL = "{{ route('surveysTemplatePreviewFromWarehouseURL') }}";
        var surveysTemplateStoreOrUpdateURL = "{{ route('surveysTemplateStoreOrUpdateURL') }}";

        var surveysTemplateChangeStatusURL = "{{ route('surveysTemplateChangeStatusURL') }}";
        var surveysTermsStoreOrUpdateURL = "{{ route('surveysTermsStoreOrUpdateURL') }}";
        var surveysTermsFormURL = "{{ route('surveysTermsFormURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys-templates.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

    <script>
        var surveysTemplateSelectedFromWarehouseURL = "{{ route('surveysTemplateSelectedFromWarehouseURL') }}";
        var surveysTemplateSelectedFromSurveyTemplateURL = "{{ route('surveysTemplateSelectedFromSurveyTemplateURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys-sortable.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
