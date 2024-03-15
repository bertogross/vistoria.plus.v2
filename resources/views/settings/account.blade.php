@extends('layouts.master')
@section('title')
    Meu {{ appName() }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
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
            Meu {{ appName() }}
        @endslot
    @endcomponent

    <p>Atualize seus dados cadastrais e monitore o faturamento de forma contínua</p>

    @include('components.alerts')

    @php
        $subscriptionData = getSubscriptionData();
        //appPrintR($subscriptionData);
        $subscriptionType = $subscriptionData['subscription_type'] ?? 'free';
        //appPrintR($subscriptionType);

        $tab = request('tab', null);
        $tab = $tab && in_array($tab, ['invoices', 'subscription', 'users', 'addons', 'account']) ? $tab : null;
    @endphp

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-2">
                    <div class="nav nav-pills flex-column nav-pills-tab verti-nav-pills custom-verti-nav-pills nav-pills-theme" role="tablist" aria-orientation="vertical">
                        <a class="nav-link text-uppercase {{ !$tab || $tab == 'subscription' ? 'active show' : '' }}" id="v-pills-stripe-subscription-tab" data-bs-toggle="pill" href="#v-pills-stripe-subscription" role="tab" aria-controls="v-pills-stripe-subscription" aria-selected="false">Assinatura</a>

                        <a class="nav-link text-uppercase {{ $tab == 'invoices' ? 'active show' : '' }}" id="v-pills-invoices-tab" data-bs-toggle="pill" href="#v-pills-invoices" role="tab" aria-controls="v-pills-invoices" aria-selected="false">Faturamento</a>

                        <a class="nav-link text-uppercase {{ $tab == 'users' ? 'active show' : '' }}" id="v-pills-stripe-users-tab" data-bs-toggle="pill" href="#v-pills-stripe-users" role="tab" aria-controls="v-pills-stripe-users" aria-selected="false">Usuários Conectados</a>

                        @if (env('APP_DEBUG'))
                            <a class="nav-link text-uppercase {{ $tab == 'addons' ? 'active show' : '' }}" id="v-pills-stripe-addons-tab" data-bs-toggle="pill" href="#v-pills-stripe-addons" role="tab" aria-controls="v-pills-stripe-addons" aria-selected="false">Complementos</a>
                        @endif

                        <a class="nav-link text-uppercase {{ $tab == 'account' ? 'active show' : '' }}" href="#v-pills-account" id="v-pills-account-tab" data-bs-toggle="pill" role="tab" aria-controls="v-pills-account" aria-selected="true">Dados da Conta</a>
                    </div>
                </div> <!-- end col-->
                <div class="col-lg-10">
                    <div class="tab-content text-muted mt-3 mt-lg-2">
                        <div class="tab-pane fade {{ !$tab || $tab == 'subscription' ? 'active show' : '' }}" id="v-pills-stripe-subscription" role="tabpanel" aria-labelledby="v-pills-stripe-subscription-tab">
                            @include('settings.stripe.subscription')
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade {{ $tab == 'invoices' ? 'active show' : '' }}" id="v-pills-invoices" role="tabpanel" aria-labelledby="v-pills-invoices-tab">
                            @include('settings.stripe.invoices')
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade {{ $tab == 'users' ? 'active show' : '' }}" id="v-pills-stripe-users" role="tabpanel" aria-labelledby="v-pills-stripe-users-tab">
                            @include('settings.stripe.users')
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade {{ $tab == 'addons' ? 'active show' : '' }}" id="v-pills-stripe-addons" role="tabpanel" aria-labelledby="v-pills-stripe-addons-tab">
                            @include('settings.stripe.addons')
                        </div><!--end tab-pane-->

                        <div class="tab-pane fade {{ $tab == 'account' ? 'active show' : '' }}" id="v-pills-account" role="tabpanel" aria-labelledby="v-pills-account-tab">
                            @include('settings.account-form')
                        </div><!--end tab-pane-->
                    </div>
                </div> <!-- end col-->
            </div> <!-- end row-->
        </div><!-- end card-body -->
    </div><!--end card-->

    @include('settings.stripe.modal-subscription-details')

    @include('settings.stripe.modal-upcoming')

@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>

    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

    <script>
        var uploadLogoURL = "{{ route('uploadLogoURL') }}";
        var deleteLogoURL = "{{ route('deleteLogoURL') }}";
        var assetURL = "{{ URL::asset('/') }}";
        var stripeSubscriptionURL = "{{ route('stripeSubscriptionURL') }}";
        var stripeCancelSubscriptionURL = "{{ route('stripeCancelSubscriptionURL') }}";
        //var stripeSubscriptionDetailsURL = "{{-- route('stripeSubscriptionDetailsURL') --}}";
        //var stripeCartAddonURL = "{{-- route('stripeCartAddonURL') --}}";
    </script>
    <script src="{{ URL::asset('build/js/settings-account.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

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

    <script type="module">
        import { attachImage } from '{{ URL::asset('build/js/settings-attachments.js') }}';

        var uploadAvatarURL = "{{ route('uploadAvatarURL') }}";

        attachImage("#member-image-input", ".avatar-img", uploadAvatarURL, false);
    </script>

    <script src="{{ URL::asset('build/js/settings-stripe.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
