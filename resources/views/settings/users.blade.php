@extends('layouts.master')
@section('title')
    @lang('translation.users')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('url')
            {{ url('settings') }}
        @endslot
        @slot('li_1')
            @lang('translation.settings')
        @endslot
        @slot('title')
            @lang('translation.users')
        @endslot
    @endcomponent
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
                <div class="col-sm-auto ms-auto">
                    <div class="list-grid-nav hstack gap-1">
                        {{--
                        <button type="button" id="grid-view-button" class="btn btn-soft-theme nav-link btn-icon fs-14 active filter-button"><i class="ri-grid-fill"></i></button>
                        <button type="button" id="list-view-button" class="btn btn-soft-theme nav-link  btn-icon fs-14 filter-button"><i class="ri-list-unordered"></i></button>
                        --}}

                        <button id="btn-add-user" class="btn btn-theme"><i class="ri-add-fill me-1 align-bottom"></i> Adicionar</button>
                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div id="teamlist">
                    <div class="team-list grid-view-filter row" id="team-member-list">
                        @if ($getUsersDataConnectedOnMyAccount->isNotEmpty())
                            @foreach ($getUsersDataConnectedOnMyAccount as $key => $connection)
                                @php
                                    $userId = $connection->user_id;
                                @endphp
                                @include('settings.components.users-card', [ 'user' => getUserData($userId)])
                            @endforeach
                        @else
                            @component('components.nothing')
                                @slot('text', 'Você ainda não adicionou membros a sua equipe')
                            @endcomponent
                        @endif
                    </div>
                </div>

                <hr class="w-50 start-50 position-relative translate-middle-x clearfix mt-4 mb-5">

                {!! \App\Models\User::generatePermissionsTable() !!}

            </div>
        </div><!-- end col -->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script>
        var uploadAvatarURL = "{{ route('uploadAvatarURL') }}";
        var uploadCoverURL = "{{ route('uploadCoverURL') }}";
        var getUserFormContentURL = "{{ route('getUserFormContentURL') }}";
        var settingsUsersStoreURL = "{{ route('settingsUsersStoreURL') }}";
        var settingsUsersUpdateURL = "{{ route('settingsUsersUpdateURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/settings-users.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
