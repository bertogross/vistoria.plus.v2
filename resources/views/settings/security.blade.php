@extends('layouts.master')
@section('title')
    @lang('translation.security')
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
            @lang('translation.file-manager')
        @endslot
    @endcomponent

    Content HERE

@endsection
@section('script')

@endsection
