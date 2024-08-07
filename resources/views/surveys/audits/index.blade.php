@extends('layouts.master')
@section('title')
    Auditorias
@endsection
@section('css')
@endsection
@section('content')
    @php
        use App\Models\User;

        $currentUserId = auth()->id();

        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);
    @endphp
    @component('components.breadcrumb')
        @slot('url')
            {{ route('surveysAuditIndexURL') }}
        @endslot

        @slot('title')
            Auditorias
            @if (request('userId'))
                de
                <span class="text-theme ms-1">{{getUserData(request('userId'))->name}}</span>
            @endif

        @endslot
    @endcomponent
    <div class="row mb-3">
        <div class="col">
            @if( in_array(getUserRoleById($currentUserId, $currentConnectionId), [1,2]))
                @include('surveys.audits.listing')
            @else
                <div class="alert alert-danger">Acesso não autorizado</div>
            @endif
        </div>
    </div>
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
        var formAssignmentAuditorURL = "{{ route('formAssignmentAuditorURL') }}";
        var listingAssignmentActivitiesURL = "{{ route('listingAssignmentActivitiesURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
