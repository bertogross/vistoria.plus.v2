@extends('layouts.master')
@section('title')
    {{ $user->name }}
@endsection
@section('css')
@endsection
@section('content')
    @php
        $phone = getUserMeta($profileUserId, 'phone');
        $phone = formatPhoneNumber($phone);

        $countSurveyorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentSurveyorTasks($profileUserId);

        $countAuditorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentAuditorTasks($profileUserId);

        $currentConnectionId = getCurrentConnectionByUserId($profileUserId);
        $connectedToName = getConnectionNameById($currentConnectionId);
        //appPrintR($profileUserId);

        $getUserIdsConnectedOnMyAccount = getUserIdsConnectedOnMyAccount();
        //appPrintR($getUserIdsConnectedOnMyAccount);

        $titleLabel = $connectedToName ? '<span class="badge bg-light-subtle text-body badge-border float-end" title="Conta Conectada">'.$connectedToName.'</span>' : '';


        //appPrintR($assignmentData);
        //appPrintR($auditorData);
        //appPrintR($filteredStatuses);
        //appPrintR($assignmentData);
    @endphp

    @if ( $profileUserId == auth()->id() || in_array($profileUserId, $getUserIdsConnectedOnMyAccount) )
        <div class="profile-foreground position-relative mx-n4 mt-n5">
            <div class="profile-wid-bg">
                <img src="{{checkUserCover($user->cover)}}" alt="cover" class="profile-wid-img" loading="lazy"/>
            </div>
        </div>

        <div class="pt-5 mb-2 mb-lg-1 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-auto">
                    <div class="avatar-lg profile-user position-relative d-inline-block">
                        <img src="{{checkUserAvatar($user->avatar)}}" alt="avatar" class="img-thumbnail rounded-circle avatar-img" loading="lazy" />
                        @if($profileUserId == auth()->id())
                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="right" title="Alterar Avatar">
                                <input class="d-none" name="avatar" id="member-image-input" type="file" accept="image/jpeg">
                                <label for="member-image-input" class="profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle bg-light text-body">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1 text-shadow">{{ $user->name }}</h3>
                        <p class="text-white mb-2 text-shadow">{{-- $profileRoleName --}}</p>
                        <div class="hstack text-white gap-1">
                            <div class="me-2 text-shadow">
                                <i class="ri-mail-line text-white fs-16 align-middle me-2"></i>{{ $user->email }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-auto order-last order-lg-0">
                    <div class="row text text-white-50 text-center">
                        {{--
                        <div class="col-lg-6 col-4">
                            <div class="p-2">
                                <h4 class="text-white mb-1">24.3K</h4>
                                <p class="fs-14 mb-0">Followers</p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-4">
                            <div class="p-2">
                                <h4 class="text-white mb-1">1.3K</h4>
                                <p class="fs-14 mb-0">Following</p>
                            </div>
                        </div>
                        --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="{{ $assignmentData ? 'col-sm-12 col-md-7 col-lg-9 col-xxl-10' : 'col-sm-12 col-md-12 col-lg-12 col-xxl-12' }} ">
                <div class="card h-100">
                    <div class="card-header">
                        {!! $titleLabel !!}
                        <h5 class="card-title mb-0 flex-grow-1">
                            <i class="ri-todo-fill fs-16 align-bottom text-theme me-2"></i>
                            @if($profileUserId == $currentConnectionId)
                                Minhas Tarefas
                            @else
                                Tarefas de <span class="text-theme">{{ $user->name }}</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body pb-0" style="min-height: 150px">
                        @if ( $assignmentData && is_array($assignmentData) )
                            <div class="tasks-board mb-0 position-relative" id="kanbanboard">
                                @foreach ($filteredStatuses as $key => $status)
                                    @php
                                        $filteredSurveyorData = [];
                                        $filteredAuditorData = [];

                                        array_walk($assignmentData, function ($item) use (&$filteredSurveyorData, $key, $profileUserId) {
                                            if ($item['surveyor_status'] == $key && $item['surveyor_id'] == $profileUserId) {
                                                $filteredSurveyorData[] = $item;
                                            }
                                        });

                                        array_walk($assignmentData, function ($item) use (&$filteredAuditorData, $key, $profileUserId) {
                                            if ($item['auditor_status'] == $key && $item['auditor_id'] == $profileUserId) {
                                                $filteredAuditorData[] = $item;
                                            }
                                        });

                                        $countFilteredSurveyorData = is_array($filteredSurveyorData) ? count($filteredSurveyorData) : 0;

                                        $countFilteredAuditorData = is_array($filteredAuditorData) ? count($filteredAuditorData) : 0;

                                        $countTotal = $countFilteredSurveyorData + $countFilteredAuditorData;
                                    @endphp

                                    <div class="tasks-list
                                    {{-- in_array($key, ['waiting', 'auditing', 'pending', 'completed', 'in_progress', 'losted']) && $countTotal < 1 ? 'd-none' : '' --}}
                                    {{ in_array($key, ['waiting', 'pending', 'auditing', 'losted']) && $countTotal < 1 ? 'd-none' : '' }}
                                    {{-- $countTotal < 1 ? 'd-none' : '' --}}
                                    p-2">
                                        <div class="d-flex mb-3">
                                            <div class="flex-grow-1">
                                                <h6 class="fs-14 text-uppercase fw-semibold mb-1">
                                                    <span data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="{{$status['label']}}" data-bs-content="{{$status['description']}}">
                                                        {{$status['label']}}
                                                    </span>
                                                    <small class="badge bg-{{$status['color']}} align-bottom ms-1 totaltask-badge">
                                                        {{ $countTotal }}
                                                    </small>
                                                </h6>
                                                <p class="text-muted mb-2">{{$status['description']}}</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                {{--
                                                <div class="dropdown card-header-dropdown">
                                                    <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span class="fw-medium text-muted fs-12">Priority<i
                                                                class="mdi mdi-chevron-down ms-1"></i></span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#">Priority</a>
                                                        <a class="dropdown-item" href="#">Date Added</a>
                                                    </div>
                                                </div>
                                                --}}
                                            </div>
                                        </div>
                                        <div data-simplebar class="tasks-wrapper">
                                            <div id="{{$key}}-task" class="tasks mb-2 pb-3">

                                                @include('surveys.layouts.profile-task-card', [
                                                    'status' => $status,
                                                    'statusKey' => $key,
                                                    'designated' => 'surveyor',
                                                    'data' => $filteredSurveyorData
                                                ])

                                                @include('surveys.layouts.profile-task-card', [
                                                    'status' => $status,
                                                    'statusKey' => $key,
                                                    'designated' => 'auditor',
                                                    'data' => $filteredAuditorData
                                                ])

                                            </div>
                                        </div>
                                    </div>
                                    <!--end tasks-list-->
                                @endforeach
                                {{--
                                @if ($countTasks === 0)
                                    <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show ms-auto me-auto" role="alert">
                                        <i class="ri-alert-line label-icon"></i> Tarefas ainda não lhe foram atribuídas
                                    </div>
                                @endif
                                --}}
                            </div>
                        @else
                            <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="ri-alert-line label-icon"></i> Tarefas ainda não foram delegadas
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @if ($assignmentData)
                <div class="col-sm-12 col-md-5 col-lg-3 col-xxl-2">
                    <div class="card h-100">
                        <div class="card-header align-items-center d-flex">
                            <h5 class="card-title mb-0 flex-grow-1"><i class="ri-line-chart-fill fs-16 align-bottom text-theme me-2"></i>Síntese</h5>
                        </div>
                        <div class="card-body" style="min-height: 150px">

                            @if($countSurveyorTasks > 0)
                                <div class="text-center">
                                    <div class="text-muted"><span class="fw-medium">{{$countSurveyorTasks}}</span> {{ $countSurveyorTasks > 1 ? 'Vistorias' : 'Vistoria' }} {{ $countSurveyorTasks > 1 ? 'Atribuídas' : 'Atribuída' }}</div>
                                </div>
                                <div class="mt-2 mb-4">
                                    @foreach ($filteredStatuses as $key => $status)
                                        @php
                                            $filteredSurveyorData = [];

                                            array_walk($assignmentData, function ($item) use (&$filteredSurveyorData, $key, $profileUserId) {
                                                if ($item['surveyor_status'] == $key && $item['surveyor_id'] == $profileUserId) {
                                                    $filteredSurveyorData[] = $item;
                                                }
                                            });

                                            $countFilteredSurveyorData = is_array($filteredSurveyorData) ? count($filteredSurveyorData) : 0;

                                            $countTotal = $countFilteredSurveyorData;

                                            $percentage = $countSurveyorTasks > 0 && $countTotal > 0 ? ($countTotal / $countSurveyorTasks) * 100 : 0;
                                            $percentage = number_format($percentage, 0);
                                        @endphp
                                        @if($percentage > 0)
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto">
                                                    <div class="p-1" style="min-width: 100px;">
                                                        <h6 class="mb-0" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="{{$status['label']}}" data-bs-content="{{$status['description']}}">
                                                            {{$status['label']}}
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="p-1">
                                                        <div class="progress animated-progress progress-sm" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Equivalente a {{ $percentage }}% de {{$countSurveyorTasks}} tarefas">
                                                            <div class="progress-bar bg-{{getProgressBarClass($percentage)}}" role="progressbar" style="width: {{$percentage}}%" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="p-1">
                                                        <h6 class="mb-0 text-{{$status['color']}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Total de tarefas relacionadas ao status {{$status['label']}}">{{ $countTotal }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            @if($countAuditorTasks > 0)
                                <div class="text-center">
                                    <div class="text-muted"><span class="fw-medium">{{$countAuditorTasks}}</span> {{ $countAuditorTasks > 1 ? 'Auditorias' : 'Auditoria' }} {{ $countAuditorTasks > 1 ? 'Requisitadas' : 'Requisitada' }}</div>
                                </div>
                                <div class="mt-2 mb-4">
                                    @foreach ($filteredStatuses as $key => $status)
                                        @php
                                            $filteredAuditorData = [];

                                            array_walk($assignmentData, function ($item) use (&$filteredAuditorData, $key, $profileUserId) {
                                                if ($item['auditor_status'] == $key && $item['auditor_id'] == $profileUserId) {
                                                    $filteredAuditorData[] = $item;
                                                }
                                            });

                                            $countFilteredAuditorData = is_array($filteredAuditorData) ? count($filteredAuditorData) : 0;

                                            $countTotal = $countFilteredAuditorData;

                                            $percentage = $countAuditorTasks > 0 && $countTotal > 0 ? ($countTotal / $countAuditorTasks) * 100 : 0;
                                            $percentage = number_format($percentage, 0);
                                        @endphp
                                        @if($percentage > 0)
                                            <div class="row align-items-center g-2">
                                                <div class="col-auto">
                                                    <div class="p-1" style="min-width: 100px;">
                                                        <h6 class="mb-0" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="{{$status['label']}}" data-bs-content="{{$status['description']}}">
                                                            {{$status['label']}}
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="p-1">
                                                        <div class="progress animated-progress progress-sm" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Equivalente a {{ $percentage }}% de {{$countSurveyorTasks}} tarefas">
                                                            <div class="progress-bar bg-{{getProgressBarClass($percentage)}}" role="progressbar" style="width: {{$percentage}}%" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="p-1">
                                                        <h6 class="mb-0 text-{{$status['color']}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Total de tarefas relacionadas ao status {{$status['label']}}">{{ $countTotal }}</h6>
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
            @endif
        </div>
    @else
        <div class="alert alert-danger">Você não possui permissão de acesso a este usuário</div>
    @endif
@endsection
@section('script')
<script>
    var profileShowURL = "{{ route('profileShowURL') }}";

    var surveysIndexURL = "{{ route('surveysIndexURL') }}";
    var surveysCreateURL = "{{ route('surveysCreateURL') }}";
    var surveysEditURL = "{{ route('surveysEditURL') }}";
    var surveysChangeStatusURL = "{{ route('surveysChangeStatusURL') }}";
    var surveysShowURL = "{{ route('surveysShowURL') }}";
    var surveysStoreOrUpdateURL = "{{ route('surveysStoreOrUpdateURL') }}";
    var formSurveyorAssignmentURL = "{{ route('formSurveyorAssignmentURL') }}";
    var formAuditorAssignmentURL = "{{ route('formAuditorAssignmentURL') }}";
    var changeAssignmentSurveyorStatusURL = "{{ route('changeAssignmentSurveyorStatusURL') }}";
    var changeAssignmentAuditorStatusURL = "{{ route('changeAssignmentAuditorStatusURL') }}";
</script>
<script src="{{ URL::asset('build/js/surveys.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

<script>
    var assignmentShowURL = "{{ route('assignmentShowURL') }}";
    var formSurveyorAssignmentURL = "{{ route('formSurveyorAssignmentURL') }}";
    var changeAssignmentSurveyorStatusURL = "{{ route('changeAssignmentSurveyorStatusURL') }}";
    var responsesSurveyorStoreOrUpdateURL = "{{ route('responsesSurveyorStoreOrUpdateURL') }}";
</script>
<script src="{{ URL::asset('build/js/surveys-surveyor.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

<script>
    var changeAssignmentAuditorStatusURL = "{{ route('changeAssignmentAuditorStatusURL') }}";
    var responsesAuditorStoreOrUpdateURL = "{{ route('responsesAuditorStoreOrUpdateURL') }}";
    var enterAssignmentAuditorURL = "{{ route('enterAssignmentAuditorURL') }}";
    //var requestAssignmentAuditorURL = "{{-- route('requestAssignmentAuditorURL') --}}";
    var revokeAssignmentAuditorURL = "{{ route('revokeAssignmentAuditorURL') }}";
</script>
<script src="{{ URL::asset('build/js/surveys-auditor.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

<script type="module">
    import { attachImage } from '{{ URL::asset('build/js/settings-attachments.js') }}';

    var uploadAvatarURL = "{{ route('uploadAvatarURL') }}";

    attachImage("#member-image-input", ".avatar-img", uploadAvatarURL, false);
</script>

<script>
    // Auto refresh page
    setInterval(function() {
        window.location.reload();// true to cleaning cache
    }, 600000); // 600000 milliseconds = 10 minutes
</script>
@endsection
