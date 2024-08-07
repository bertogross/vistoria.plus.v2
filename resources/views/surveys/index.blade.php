@php
    use App\Models\User;
    $connectionId = getUserMeta(auth()->id(), 'current_database_connection');
@endphp
@extends('layouts.master')
@section('title')
    @lang('translation.surveys')
@endsection
@section('css')
@endsection
@section('content')

    <div class="row mb-3">
        <div class="col">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1 text-uppercase">
                                @lang('translation.surveys')

                                @if (auth()->id() != $connectionId)
                                    <i class="ri-arrow-right-s-fill text-theme ms-2 me-2 align-bottom"></i>
                                    <span class="text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Dados originados desta conta">{!!getCurrentConnectionName()!!}</span>
                                @endif
                            </h4>
                            <p class="text-muted mb-0">Aqui estão os componentes necessários para suas tarefas de vistoria</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-auto">
                                        <a class="btn btn-label right btn-soft-theme float-end" href="{{ route('surveysTemplateCreateURL') }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Adicionar/Editar Modelo">
                                            <i class="ri-edit-box-fill label-icon align-middle fs-16 ms-2"></i>Modelo
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-soft-theme btn-icon layout-rightside-btn" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Exibir/Ocultar Atividades"><i class="ri-pulse-line"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{--
                <div class="row">
                    @foreach ($getSurveyStatusTranslations as $key => $value)
                    <div class="col">
                        <div class="card card-animate" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="{{ $value['description'] }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0">{{ $value['label'] }}</p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="{{ $surveyStatusCount[$key] ?? 0 }}"></span></h2>
                                        <!--
                                        <p class="mb-0 text-muted"><span class="badge bg-light text-{{ $value['color'] }} mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> 0.63 %
                                            </span> vs. previous month
                                        </p>
                                        -->
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-{{ $value['color'] }}-subtle text-{{ $value['color'] }} rounded-circle fs-4">
                                                <i class="{{ !empty($value['icon']) ? $value['icon'] : 'ri-ticket-2-line' }}"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div>
                    </div>
                    <!--end col-->
                    @endforeach
                </div>
                --}}
            </div>
            @if( auth()->user()->hasAnyRole(User::ROLE_ADMIN) )
                {{--
                <div class="row">
                    @if (!$templates->isEmpty())
                        <div class="col-sm-12 col-md-12 col-lg-4 col-xxl-3 mb-3">
                            @include('surveys.templates.listing')
                        </div>
                    @endif

                    <div class="{{ $templates->isEmpty() ? 'col-sm-12 col-md-12 col-lg-12 col-xxl-12' : 'col-sm-12 col-md-12 col-lg-8 col-xxl-9' }} mb-3">
                        @include('surveys.listing')
                    </div>
                </div>
                --}}

                @include('surveys.listing')
            @else
                <div class="alert alert-danger">Acesso não autorizado</div>
            @endif
        </div>

        @if( auth()->user()->hasAnyRole(User::ROLE_ADMIN) )
            <div class="col-auto layout-rightside-col">
                <div class="overlay"></div>
                <div class="layout-rightside pb-2">

                    <div class="card bg-body border-1 border-light rounded-2 h-100 mb-0">
                        <div class="card-header">
                            <button type="button" class="btn-close btn-close-overlay float-end"></button>
                            <h6 class="text-muted m-0 text-uppercase fw-semibold">Atividades Recentes</h6>
                        </div>
                        <div class="card-body pt-0">
                            <div
                            id="load-assignment-activities"
                            class="tasks-wrapper-survey overflow-auto h-100 mb-3"
                            data-subDays="1">
                                <div class="text-center">
                                    <div class="spinner-border text-theme mt-5 mb-3" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            {{--
                            <a id="btn-show-all-assignments" href="{{route('listingAssignmentAllURL')}}" class="btn btn-sm btn-soft-theme btn-icon init-loader w-100" title="Ver todas">Ver todas</a>
                            --}}
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>

    @include('surveys.layouts.modal-listing-assignments')

    @include('surveys.layouts.modal-preview-form-assignment')

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/l10n/pt.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/plugins/monthSelect/index.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/plugins/confirmDate/confirmDate.js') }}"></script>

    <script>
        var surveysIndexURL = "{{ route('surveysIndexURL') }}";
        var surveysCreateURL = "{{ route('surveysCreateURL') }}";
        var surveysEditURL = "{{ route('surveysEditURL') }}";
        var surveysChangeStatusURL = "{{ route('surveysChangeStatusURL') }}";
        var surveysShowURL = "{{ route('surveysShowURL') }}";
        var surveysStoreOrUpdateURL = "{{ route('surveysStoreOrUpdateURL') }}";
        var formAssignmentSurveyorURL = "{{ route('formAssignmentSurveyorURL') }}";
        var surveyReloadUsersTabURL = "{{ route('surveyReloadUsersTabURL') }}";
        var listingAssignmentActivitiesURL = "{{ route('listingAssignmentActivitiesURL') }}";
        var listingAssignmentByIdURL = "{{ route('listingAssignmentByIdURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

    <script>
        var settingsIndexURL = "{{ route('settingsIndexURL') }}";
        var uploadAvatarURL = "{{ route('uploadAvatarURL') }}";
        var uploadCoverURL = "{{ route('uploadCoverURL') }}";
        var getUserFormContentURL = "{{ route('getUserFormContentURL') }}";
        var settingsUsersStoreURL = "{{ route('settingsUsersStoreURL') }}";
        var settingsUsersUpdateURL = "{{ route('settingsUsersUpdateURL') }}";
        var settingsAccountShowURL = "{{ route('settingsAccountShowURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/settings-users.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
