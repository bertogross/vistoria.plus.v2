@php
    use Carbon\Carbon;
    use App\Models\SurveyTopic;
    use App\Models\SurveyResponse;
    use App\Models\SurveyTemplates;
    use App\Models\User;

    $today = Carbon::today();
    $currentUserId = auth()->id();

    $surveyId = $surveyData->id ?? '';
    $title = $surveyData->title ?? '';

    $templateData = SurveyTemplates::findOrFail($surveyData->template_id);

    $authorId = $templateData->user_id;
    $getUserData = getUserData($authorId);
    $authorRoleName = \App\Models\User::getRoleName($getUserData->role);
    $description = trim($templateData->description) ? nl2br($templateData->description) : '';

    $templateName = $surveyData ? getSurveyTemplateNameById($surveyData->template_id) : '';

    $companyId = $assignmentData->id ?? '';
    $companyName = $companyId ? getCompanyNameById($companyId) : '';

    $assignmentId = $assignmentData->id ?? null;
    $assignmentCreatedAt = $assignmentData->created_at ?? null;

    $auditorId = $assignmentData->auditor_id ?? null;
    $auditorName = $auditorId ? getUserData($auditorId)->name : '';
    $auditorStatus = $assignmentData->auditor_status ?? null;

    $surveyorId = $assignmentData->surveyor_id ?? null;
    $surveyorName = getUserData($surveyorId)->name ?? '';
    //$surveyorStatus = $assignmentData->surveyor_status ?? null;

    $countTopics = SurveyTopic::countSurveyTopics($surveyId);

    // Count the number of steps that have been finished
    $countResponses = SurveyResponse::countSurveyAuditorResponses($auditorId, $surveyId, $assignmentId);

    $responsesData = SurveyResponse::where('survey_id', $surveyId)
        ->where('assignment_id', $assignmentId)
        ->get()
        ->toArray();
@endphp
@extends('layouts.master')
@section('title')
    Formulário de Auditoria
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('url')
            {{ route('surveysIndexURL') }}
        @endslot
        @slot('li_1')
            Checklists
        @endslot
        @slot('title')
            Auditoria <i class="ri-arrow-drop-right-line text-theme ms-2 me-2 align-bottom"></i>
            <small>#<span class="text-theme">{{$surveyId}}</span> {{ limitChars($templateName ?? '', 20) }}</small>
        @endslot
    @endcomponent
    <div id="content" class="rounded rounded-2 mb-4" style="max-width: 700px; margin: 0 auto;">
        <div class="bg-secondary-subtle position-relative">
            <div class="card-body p-5 text-center">
                <h2 class="text-secondary">Auditoria</h2>

                @if ($companyName )
                    <h2 class="text-theme text-uppercase">{{ $companyName }}</h2>
                @endif

                <h3>{{ $title ? ucfirst($title) : 'NI' }}</h3>

                <div class="mb-0 text-muted">
                    Vistoriador(a): {{ $surveyorName }}
                </div>

                <div class="mb-0 text-muted">
                    Auditor(a): {{ $auditorName }}
                </div>
                <div class="mb-0 text-muted">
                    Executar até: {{ $assignmentCreatedAt ? \Carbon\Carbon::parse($assignmentCreatedAt)->locale('pt_BR')->isoFormat('D [de] MMMM, YYYY') : '-' }}
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

        @if ($currentUserId != $auditorId)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Você não possui autorização para prosseguir com a tarefa delegada a outra pessoa
            </div>
        @elseif ($auditorStatus == 'completed')
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                <i class="ri-alert-line label-icon blink"></i> Esta Auditoria já foi finalizada e não poderá ser retificada.
                <br>
                <a href="{{ route('assignmentShowURL', $assignmentId) }}"
                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                    title="Visualizar" class="btn btn-sm waves-effect btn-soft-secondary mt-2">
                    Visualizar
                </a>
            </div>
        @else
            @if ($auditorStatus == 'losted')
                <div class="alert alert-secondary alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                    <i class="ri-alert-line label-icon blink"></i> Esta Auditoria foi perdida pois o prazo expirou e por isso não poderá mais ser editada
                    <br>
                    <a href="{{ route('assignmentShowURL', $assignmentId) }}"
                        data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                        title="Visualizar" class="btn btn-sm waves-effect btn-soft-success mt-2">
                        Visualizar
                    </a>
                </div>
            @endif

            {!! !empty($description) ? '<div class="blockquote custom-blockquote blockquote-outline blockquote-dark rounded mt-2 mb-2"><p class="text-body mb-2">'.$description.'</p><footer class="blockquote-footer mt-0">'.$getUserData->name.' <cite title="'.$authorRoleName.'">'.$authorRoleName.'</cite></footer></div>' : '' !!}

            <div id="assignment-container">
                @csrf
                <input type="hidden" name="survey_id" value="{{$surveyId}}">
                <input type="hidden" name="company_id" value="{{$companyId}}">

                @if ($surveyData && $responsesData)
                    @include('surveys.layouts.form-auditor-step-cards')
                    {{--
                    @component('surveys.layouts.form-auditor-step-cards')
                        @slot('stepsWithTopics', $stepsWithTopics)
                        @slot('responsesData', $responsesData)
                        @slot('auditorStatus', $auditorStatus)
                        @slot('assignmentId', $assignmentId)
                        @slot('countTopics', $countTopics)
                        @slot('countResponses', $countResponses)
                    @endcomponent
                    --}}
                @else
                    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="ri-alert-line label-icon"></i> Não há dados para gerar os campos deste formulário de Auditoria
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div id="survey-progress-bar" class="fixed-bottom mb-0 ms-auto me-auto w-100">
        <div class="flex-grow-1">
            <div class="progress animated-progress progress-label rounded-0">
                <div class="progress-bar rounded-0 bg-{{getProgressBarClass($percentage)}}" role="progressbar" style="width: {{$percentage}}%" aria-valuenow="" aria-valuemin="0" aria-valuemax="100"><div class="label">{{ $percentage > 0 ? $percentage.'%' : ''}}</div></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

    <script>
        var surveysIndexURL = "{{ route('surveysIndexURL') }}";
        var surveysCreateURL = "{{ route('surveysCreateURL') }}";
        var surveysEditURL = "{{ route('surveysEditURL') }}";
        var surveysChangeStatusURL = "{{ route('surveysChangeStatusURL') }}";
        var surveysShowURL = "{{ route('surveysShowURL') }}";
        var surveysStoreOrUpdateURL = "{{ route('surveysStoreOrUpdateURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

    <script>
        var profileShowURL = "{{ route('profileShowURL') }}";
        var assignmentShowURL = "{{ route('assignmentShowURL') }}";
        var formAuditorAssignmentURL = "{{ route('formAuditorAssignmentURL') }}";
        var changeAssignmentAuditorStatusURL = "{{ route('changeAssignmentAuditorStatusURL') }}";
        var responsesAuditorStoreOrUpdateURL = "{{ route('responsesAuditorStoreOrUpdateURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys-auditor.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

    <script>
        var settingsAccountShowURL = "{{ route('settingsAccountShowURL') }}";
        var uploadPhotoURL = "{{ route('uploadPhotoURL') }}";
        var deletePhotoURL = "{{ route('deletePhotoURL') }}";
        var deleteAttachmentByPathURL = "{{ route('deleteAttachmentByPathURL') }}";
        var assetURL = "{{ URL::asset('/') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys-attachments.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

    <script type="module">
        import {
            toggleElement,
        } from '{{ URL::asset('build/js/helpers.js') }}';

        toggleElement();
    </script>
@endsection
