@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('title')
            @lang('translation.settings')
        @endslot
    @endcomponent
    <div>
        Content HERE
    </div>
@endsection
@section('script')

@endsection
