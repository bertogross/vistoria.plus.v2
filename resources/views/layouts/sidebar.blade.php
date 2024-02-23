<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    {{--
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ url('/') }}" class="logo logo-dark" title="Ir para inicial do {{appName()}}">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="{{appName()}}" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{URL::asset('build/images/logo-dark.png')}}" alt="{{appName()}}" height="31" loading="lazy">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ url('/') }}" class="logo logo-light" title="Ir para inicial do {{appName()}}">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="{{appName()}}" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{URL::asset('build/images/logo-light.png')}}" alt="{{appName()}}" height="31" loading="lazy">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    --}}

    @if ( Request::is('settings*') )
        @component('settings.components.nav')
        @endcomponent
    @endif
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
