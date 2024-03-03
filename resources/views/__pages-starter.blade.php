@extends('layouts.master')
@section('title')
    Title HERE
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            @lang('translation.session')
        @endslot
        @slot('title') Starter  @endslot
    @endcomponent


    Content HERE


@endsection
@section('script')

@endsection
