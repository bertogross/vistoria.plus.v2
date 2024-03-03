@php
    use App\Models\User;
    use App\Models\SurveyAssignments;

    $user = auth()->user();

    $getUsersDataFromMyConnections = getUsersDataFromMyConnections();

    $currentConnectionId = getCurrentConnectionByUserId($user->id);
    $currentConnectionName = getConnectionNameById($currentConnectionId);

    $currentConnectionRoleName = getCurrentConnectionUserRoleName();

    $countSurveyAssignmentSurveyorTasks = SurveyAssignments::countSurveyAssignmentSurveyorTasks($user->id, ['new', 'pending', 'in_progress']);
    $countSurveyAssignmentAuditorTasks = SurveyAssignments::countSurveyAssignmentAuditorTasks($user->id, ['new', 'pending', 'in_progress']);

    $profileUrl = route('profileShowURL', ['id' => $user->id]) . '?d=' . now()->timestamp;

    $companyLogo = getCompanyLogo();
@endphp
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ url('/') }}" class="logo logo-dark" title="Ir para inicial do {{appName()}}">
                        <span class="logo-sm">
                            @if ($companyLogo)
                                <img src="{{ $companyLogo }}" alt="{{appName()}}" height="31" loading="lazy">
                            @else
                                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="{{appName()}}" height="31" loading="lazy">
                            @endif
                        </span>
                        <span class="logo-lg">
                            @if ($companyLogo)
                                <img src="{{ $companyLogo }}" alt="{{appName()}}" height="31" loading="lazy">
                            @else
                                <img src="{{URL::asset('build/images/logo-dark.png')}}" alt="{{appName()}}" height="31" loading="lazy">
                            @endif
                        </span>
                    </a>

                    <a href="{{ url('/') }}" class="logo logo-light" title="Ir para inicial do {{appName()}}">
                        <span class="logo-sm">
                            @if ($companyLogo)
                                <img src="{{ $companyLogo }}" alt="{{appName()}}" height="31" loading="lazy">
                            @else
                                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="{{appName()}}" height="31" loading="lazy">
                            @endif
                        </span>
                        <span class="logo-lg">
                            @if ($companyLogo)
                                <img src="{{ $companyLogo }}" alt="{{appName()}}" height="31" loading="lazy">
                            @else
                                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="{{appName()}}" height="31" loading="lazy">
                            @endif
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <!--
                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                -->

                <div class="dropdown topbar-head-dropdown ms-1 header-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Módulos">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Módulos">
                        <i class='bx bx-category-alt fs-22'></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end">
                        {{--
                        <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fw-semibold fs-15"> Web Apps </h6>
                                </div>
                                <div class="col-auto">
                                    <a href="#!" class="btn btn-sm btn-soft-info"> View All Apps
                                        <i class="ri-arrow-right-s-line align-middle"></i></a>
                                </div>
                            </div>
                        </div>
                        --}}

                        <div class="p-2">
                            <div class="row g-0">
                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ route('surveysIndexURL') }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="bottom"  title="Acessar a sessão Checklists da conta <strong>{{$currentConnectionName}}</strong>">
                                        <i class="ri-checkbox-line text-theme fs-1"></i>
                                        <span>Checklists</span>
                                    </a>
                                </div>

                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ route('teamIndexURL') }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="bottom" title="Listar membros da Equipe <strong>{{$currentConnectionName}}</strong>">
                                        <i class="ri-team-line text-theme fs-1"></i>
                                        <span>Equipe</span>
                                    </a>
                                </div>

                                {{--
                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ $profileUrl }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Acessar minha lista de Tarefas">
                                        <i class="ri-todo-fill text-theme fs-1"></i>
                                        <span>Tarefas</span>
                                    </a>
                                </div>
                                --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{--
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>
                --}}

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" id="btn-light-dark-mode" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"  data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Alternar Visual">
                        <i class="bx bx-moon fs-22"></i>
                    </button>
                </div>

                @component('components.notifications')
                    @slot('url')
                        {{-- route('notificationsIndexURL') --}}
                    @endslot
                @endcomponent

                @if ($getUsersDataFromMyConnections->isNotEmpty())
                    <div class="dropdown ms-1 topbar-head-dropdown header-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="left" title="Atualmente conectado a conta <u>{{$currentConnectionName}}</u>">
                        <button type="button" class="btn btn-sm btn-outline-{{$currentConnectionId != $user->id ? 'theme' : 'light' }} btn-label waves-effect waves-light text-body-secondary bg-light-subtle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ri-share-line label-icon align-middle fs-16 me-2"></i> {{$currentConnectionName}}
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <ul class="list-unstyled ps-3 pe-3 mb-0" style="min-width: 270px;">
                                <h6 class="dropdown-header mb-2 ps-0">Alternar Conexões</h6>

                                <li class="form-check form-switch form-switch-theme mb-0" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="Alternar para a conta Principal, <strong>{{$user->name}}</strong>">
                                    <input id="toggle-connection-{{$user->id}}" class="form-check-input {{$currentConnectionId != $user->id ? 'toggle-connection' : ''}}" type="radio" role="switch" name="connection" value="{{$user->id}}" {{ !$getUsersDataFromMyConnections || $currentConnectionId == $user->id ? 'checked' : '' }}>
                                    <label class="form-check-label w-100 text-uppercase" for="toggle-connection-{{$user->id}}">
                                        <span class="badge border border-dark text-body float-end ms-2 me-4 float-end ms-2 fs-10">
                                            Principal
                                        </span>
                                        {{$user->name}}
                                    </label>
                                </li>

                                @foreach ($getUsersDataFromMyConnections as $key => $connection)
                                    @php
                                        $connectionUserId = $connection->connected_to;
                                        $connectionName = getConnectionNameById($connectionUserId);
                                        $connectionStatus = $connection->status;
                                        $connectionRole = $connection->role;
                                        $connectionRoleName = User::getRoleName($connectionRole);
                                    @endphp
                                    <li class="form-check form-switch form-switch-theme mt-4" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="left" title="Alternar para <strong>{{$connectionName}}</strong>">

                                        <input
                                        id="toggle-connection-{{$connectionUserId}}"
                                        class="form-check-input {{$connectionUserId != $currentConnectionId ? 'toggle-connection' : ''}}"
                                        type="radio"
                                        role="switch"
                                        name="connection"
                                        {{$connectionStatus != 'active' ? 'disabled' : ''}}
                                        value="{{$connectionUserId}}" {{ $connectionUserId == $currentConnectionId ? 'checked' : '' }}>

                                        <label class="form-check-label w-100 text-uppercase" for="toggle-connection-{{$connectionUserId}}">
                                            @switch($connectionStatus)
                                                @case('waiting')
                                                    <span class="position-absolute text-warning float-end me-0 end-0 ri-question-line align-middle mt-n1 fs-16 btn-accept-invitation cursor-pointer"
                                                    data-bs-html="true"
                                                    data-bs-toggle="popover"
                                                    data-bs-trigger="hover focus"
                                                    data-bs-placement="bottom"
                                                    data-bs-title="Status da Conexão"
                                                    data-bs-content="Aguardando seu consentimento para acesso ao <strong>{{$connectionName}}</strong>.<br>Clique para aceitar."></span>
                                                    @break
                                                @case('inactive')
                                                    <span class="position-absolute text-danger float-end me-0 end-0 ri-close-circle-line align-middle mt-n1 fs-16"
                                                    data-bs-html="true"
                                                    data-bs-toggle="popover"
                                                    data-bs-trigger="hover focus"
                                                    data-bs-placement="bottom"
                                                    data-bs-title="Status da Conexão"
                                                    data-bs-content="<strong class='text-danger'>Inoperante</strong><br><br><strong>{{$connectionName}}</strong> inativou seu acesso"></span>
                                                    @break
                                                @default
                                                    <span class="position-absolute text-success float-end me-0 end-0 ri-checkbox-circle-line align-middle mt-n1 fs-16" data-bs-html="true"
                                                    data-bs-toggle="popover"
                                                    data-bs-trigger="hover focus"
                                                    data-bs-placement="bottom"
                                                    data-bs-title="Status da Conexão"
                                                    data-bs-content="Seu acesso ao <strong>{{$connectionName}}</strong> está <span class='text-success'>Ativo</span>"></span>
                                            @endswitch

                                            <span class="badge border border-dark text-body float-end ms-2 me-4" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="Relacionado a conta <strong>{!!$connectionName!!}</strong>, o seu Nível possui a permissão para realizar: <strong>{{$connectionRoleName}}</strong>">
                                                {{$connectionRoleName}}
                                            </span>

                                            {!! $connectionName !!}
                                        </label>
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                    </div>
                @endif

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <span class="position-absolute translate-middle badge border border-light rounded-circle bg-theme p-1 {{ $countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks > 0 ? 'blink' : 'd-none' }}" style="margin-left: 30px;margin-top: 15px;" title="Tarefas Pendentes"><span class="visually-hidden">{{$countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks}} Tarefas Pendentes</span></span>
                            <img class="rounded-circle header-profile-user" src="{{checkUserAvatar($user->avatar)}}" alt="Avatar" loading="lazy">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{$user->name}}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="Este é seu nível de autorização relacionado a conta <strong>{{$currentConnectionName}}<strong>">{{$currentConnectionRoleName}}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!--
                        <h6 class="dropdown-header">Welcome Anna!</h6>

                        <a class="dropdown-item" href="pages-profile"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>

                        <a class="dropdown-item" href="apps-chat"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>

                        <a class="dropdown-item" href="apps-tasks-kanban"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a>

                        <a class="dropdown-item" href="pages-faqs"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a>
                        <div class="dropdown-divider"></div>
                        -->

                        <a class="dropdown-item" href="{{ route('profileShowURL') }}">
                            <i class="ri-todo-fill text-muted fs-16 align-middle me-1"></i>
                            <span class="align-bottom">
                                Minhas Tarefas
                                @if($countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks > 0)
                                    <span class="badge border border-theme text-body ms-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Tarefas por executar">{{$countSurveyAssignmentSurveyorTasks+$countSurveyAssignmentAuditorTasks}}<span class="visually-hidden">tasks</span></span>
                                @endif
                            </span>
                        </a>

                        @if(in_array(getUserRoleById($user->id, $currentConnectionId), [1,2]))
                            <a class="dropdown-item" href="{{ route('surveysAuditIndexURL', $user->id) }}">
                                <i class="ri-fingerprint-2-line text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle">
                                    Minhas Auditorias
                                    @if($countSurveyAssignmentAuditorTasks > 0)
                                        <span class="badge border border-theme text-body ms-2" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Auditorias por executar">{{$countSurveyAssignmentAuditorTasks}}<span class="visually-hidden">tasks</span></span>
                                    @endif
                                </span>
                            </a>
                        @endif


                        <!--
                        <a class="dropdown-item" href="auth-lockscreen-basic"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
                        -->

                        <a class="dropdown-item"
                            @if ($user->id != $currentConnectionId)
                                onclick="alert('Para acessar suas configurações alterne para conta Principal')"
                            @else
                                href="{{ route('settingsUsersIndexURL') }}"
                            @endif
                            >
                            <i class="ri-settings-4-fill text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Configurações Gerais</span>
                        </a>

                        <button class="dropdown-item" id="pwa_install_button" hidden>
                            <i class="ri-smartphone-fill text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle" title="Instalar PWA">Instalar App</span>
                        </button>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item" href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off font-size-16 align-middle me-1"></i> <span key="t-logout">Sair</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>
