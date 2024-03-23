@extends('layouts.master')
@section('title')
    @lang('translation.users')
@endsection
@section('content')

    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0 font-size-18">
            Equipe
            <i class="ri-arrow-right-s-fill text-theme ms-2 me-2 align-bottom"></i>
            <span class="text-muted" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Dados originados desta conta">{!!getCurrentConnectionName()!!}</span>
        </h4>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col">
                            <div class="search-box">
                                <input type="text" class="form-control" id="searchMemberList" placeholder="Pesquisar por nome...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <!--end col-->
                        {{--
                        <div class="col-sm-auto ms-auto">
                            <div class="list-grid-nav hstack gap-1">

                                <button type="button" id="grid-view-button" class="btn btn-soft-theme nav-link btn-icon fs-14 active filter-button"><i class="ri-grid-fill"></i></button>
                                <button type="button" id="list-view-button" class="btn btn-soft-theme nav-link  btn-icon fs-14 filter-button"><i class="ri-list-unordered"></i></button>
                            </div>
                        </div>
                        --}}
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
            </div>
            <div id="teamlist">
                <div class="team-list grid-view-filter row" id="team-member-list">
                    @if ($users->isNotEmpty())
                        @foreach ($users as $key => $user)
                            @include('team.users-card', [ 'user' => getUserData($user->id)])
                        @endforeach
                    @else
                        @component('components.nothing')
                            @slot('text', 'Ainda não há membros na equipe de '.getCurrentConnectionName().'')
                        @endcomponent
                    @endif
                </div>
            </div>
        </div>
        <div class="col-auto mb-4">
            <div class="card rounded-2 mb-0">

                <div class="card-header">
                    <a href="#" class="btn btn-sm btn-soft-theme btn-icon init-loader float-end mt-n1 position-absolute ms-auto me-3 end-0" title="Ver todas"><i class="ri-file-list-line"></i></a>
                    <h6 class="text-muted m-0 text-uppercase fw-semibold">Atividades Recentes</h6>
                </div>
                <div class="card-body pt-0">
                    <div
                    id="load-assignment-activities"
                    class="tasks-wrapper-survey overflow-auto h-100"
                    data-subDays="7"
                    style="min-width: 250px;">
                        <div class="text-center">
                            <div class="spinner-border text-theme mt-5 mb-3" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('script')
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

    <script>
        var surveysIndexURL = "{{ route('surveysIndexURL') }}";
        var surveysCreateURL = "{{ route('surveysCreateURL') }}";
        var surveysEditURL = "{{ route('surveysEditURL') }}";
        var surveysChangeStatusURL = "{{ route('surveysChangeStatusURL') }}";
        var surveysShowURL = "{{ route('surveysShowURL') }}";
        var surveysStoreOrUpdateURL = "{{ route('surveysStoreOrUpdateURL') }}";
        var requestAssignmentActivitiesURL = "{{ route('requestAssignmentActivitiesURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
