    <script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>

@yield('script')
@yield('script-bottom')

<script>
    var assetURL = "{{ URL::asset('/') }}";
    var appVersion = "{{ env('APP_VERSION') }}";
    var profileChangeLayoutModeURL = "{{ route('profileChangeLayoutModeURL') }}";
</script>
<script src="{{ URL::asset('build/js/app.js') }}?v={{env('APP_VERSION')}}"></script>

<script>
    var changeConnectionURL = "{{ route('changeConnectionURL') }}";
</script>
<script src="{{ URL::asset('build/js/app-custom.js') }}?v={{env('APP_VERSION')}}" type="module"></script>

@php
    $HTTP_HOST = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $SUBDOMAIN = $HTTP_HOST ? strtok($HTTP_HOST, '.') : '';
@endphp
@if ( $SUBDOMAIN && ( $SUBDOMAIN != 'app' && $SUBDOMAIN != 'checklist' ) )
    @php
        $replacements = [
            'localhost:8000' => 'local',
            'localhost' => 'local',
            'development' => 'dev',
            'testing' => 'test'
        ];

        foreach ($replacements as $search => $replace) {
            $SUBDOMAIN = $SUBDOMAIN != 'vistoria' ? str_replace($search, $replace, $SUBDOMAIN) : '';
        }
    @endphp
    @if ($SUBDOMAIN)
        <div class="ribbon-box border-0 ribbon-fill position-fixed top-0 start-0 d-none d-lg-block d-xl-block" data-bs-toggle="tooltip" data-bs-placement="right" title="{{$SUBDOMAIN}} Environment" style="z-index:5000; width: 60px; height:60px;">
            <div class="ribbon ribbon-{{$SUBDOMAIN == 'development' ? 'danger' : 'warning'}} text-uppercase">{{ $SUBDOMAIN }}</div>
        </div>
    @endif
@endif
