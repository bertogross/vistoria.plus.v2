<div id="scrollbar">
    <div class="container-fluid">

        <div id="two-column-menu">
        </div>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.components')</span></li>

            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('settings/account/*') ? 'active' : '' }}" href="{{ route('settingsAccountShowURL') }}">
                    <i class="ri-add-fill"></i> <span>Meu {{ appName() }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('settings/users') ? 'active' : '' }}" href="{{ route('settingsUsersIndexURL') }}">
                    <i class="ri-team-fill"></i> <span>@lang('translation.users')</span>
                </a>
            </li>

            {{--
            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('settings/api-keys') ? 'active' : '' }}" href="{{ route('settingsApiKeysURL') }}">
                    <i class="ri-cloud-windy-fill"></i> @lang('translation.api-conections')
                </a>
            </li>

            @if ( getDropboxToken() )
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->is('settings/dropbox') ? 'active' : '' }}" href="{{ route('DropboxIndexURL') }}">
                        <i class="ri-dropbox-fill {{ request()->is('settings/dropbox') ? 'text-primary' : '' }}"></i> <span class="{{ request()->is('settings/dropbox') ? 'text-white' : '' }}">Armazenamento</span>
                    </a>
                </li>
            @endif
            --}}


            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('settings/companies') ? 'active' : '' }}" href="{{ route('settingsCompaniesIndexURL') }}">
                    <i class="ri-store-3-fill"></i> <span>Unidades Corporativas</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('settings/storage') ? 'active' : '' }}" href="{{ route('settingsStorageIndexURL') }}">
                    <i class="ri-server-line"></i> <span>Armazenamento</span>
                </a>
            </li>


            <!--
            <li class="nav-item">
                <a class="nav-link menu-link {{ request()->is('settings/security') ? 'active' : '' }}" href="{{-- route('settingsSecurityIndexURL') --}}#">
                    <i class="ri-shield-keyhole-line"></i> <span>@lang('translation.security')</span>
                </a>
            </li>
            -->
        </ul>
    </div>
</div>
<div class="sidebar-background d-none"></div>
