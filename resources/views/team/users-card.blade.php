@php
    use App\Models\User;
    use App\Models\UserConnections;
    use App\Models\SurveyAssignments;

    $userId = $user->id;
    $avatar = $user->avatar ?? null;
    $cover = $user->cover ?? null;
    $name = $user->name ?? null;
    $email = $user->email ?? null;

    $profileUrl = route('profileShowURL', ['id' => $userId]) . '?d=' . now()->timestamp;

    $currentConnectionId = getCurrentConnectionByUserId(auth()->id());

    if($userId == $currentConnectionId){
        $status = 'active';
        $userCompanies = getActiveCompanieIds();
        $role = 1;
    }else{
        $connection = UserConnections::getGuestDataFromConnectedHostId($userId, $currentConnectionId);

        $status = $connection->status ?? 'active';
        $userCompanies = $connection->companies ?? getActiveCompanieIds();
        $role = $connection->role ?? 1;
    }

    $roleName = User::getRoleName($role);
@endphp
<div class="col" data-search-user-id="{{$userId}}" data-search-user-name="{{$name}}">
    <div class="card team-box">
        <div class="team-cover" style="min-height: 130px">
            <img src="{{checkUserCover($cover)}}" alt="{{$name}}" class="img-fluid" height="130" loading="lazy">
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center team-row">
                @if (request()->is('settings/users'))
                    <div class="col team-settings">
                        <div class="row">
                            <div class="col">
                                <div class="flex-shrink-0 me-2">
                                    <!--
                                    <button type="button" class="btn btn-light btn-icon rounded-circle btn-sm favourite-btn "> <i class="ri-star-fill fs-14"></i> </button>
                                    -->
                                </div>
                            </div>
                            <div class="col text-end dropdown">
                                <button type="button"  data-bs-toggle="dropdown" class="btn btn-sm btn-soft-dark ri-more-fill text-theme fs-17 rounded-pill" aria-expanded="false"></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item btn-edit-user cursor-pointer" data-user-id="{{$userId ?? ''}}" data-user-title="{{$name ?? ''}}"><i class="ri-pencil-line me-2 align-bottom text-muted"></i>Editar</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-lg-4 col">
                    <div class="team-profile-img">
                        {!!snippetAvatar($avatar, $name, 'member-img avatar-lg img-thumbnail rounded-circle flex-shrink-0 overflow-hidden img-fluid rounded-circle img-thumbnail display-1', 'font-size: 65px !important;')!!}
                        <div class="team-content">
                            <h5 class="fs-16 mb-1 text-uppercase">{{$name}}</h5>
                            <h6 class="fs-11 mb-1 text-muted mb-3">{{$email}}</h6>
                            <p class="text-muted member-designation mb-2 fw-bold" data-bs-toggle="tooltip" data-bs-placement="top" title="Nível do usuário">
                                {{$roleName}}
                            </p>
                            <p class="text-muted mb-0" title="Status do usuário">
                                {!! User::statusTranslationLabel($status, false) !!}
                            </p>
                            {{--
                            {!! $user->last_login ? '<p class="small text-muted mb-0">Último Login: '.date('d/m/Y H:i', strtotime($user->last_login)).'</p>' : '' !!}
                            --}}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col">
                    <div class="row text-muted text-center">
                        @php
                            $requiredKeys = ['new', 'pending', 'in_progress', 'auditing', 'completed', 'losted'];

                            $countSurveyorTasks = SurveyAssignments::countSurveyAssignmentSurveyorTasks($userId, $requiredKeys);
                            $countAuditorTasks = SurveyAssignments::countSurveyAssignmentAuditorTasks($userId, $requiredKeys);
                        @endphp

                        <div class="{{ in_array($role, [1,2]) || $countAuditorTasks > 0 ? 'col-6' : 'col-12' }}">
                            <h5 class="mb-1 tasks-num">{{ $countSurveyorTasks }}</h5>
                            <p class="text-muted mb-0">Vistorias</p>
                        </div>

                        @if ( in_array($role, [1,2]) || $countAuditorTasks > 0 )
                            <div class="col-6 border-end border-end-dashed">
                                <h5 class="mb-1 projects-num">{{ $countAuditorTasks }}</h5>
                                <p class="text-muted mb-0">Auditorias</p>
                            </div>
                        @endif

                        {{--
                        <div class="col-12 border-end border-end-dashed mt-4">
                            <h6 class="mb-1 projects-num">Unidade{{ is_array($userCompanies) && count($userCompanies) > 1 ? 's' : '' }} Autorizada{{ is_array($userCompanies) && count($userCompanies) > 1 ? 's' : '' }}</h6>
                            @if (is_array($userCompanies))
                                <ul class="list-unstyled list-inline text-muted mb-0" style="min-height: 40px;">
                                    @foreach ($userCompanies as $key => $companyId)
                                        <li class="list-inline-item"><i class="ri-store-3-fill text-theme align-bottom me-1"></i>{{getCompanyNameById($companyId)}}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        --}}
                    </div>
                </div>
                <div class="col-lg-2 col">
                    <div class="text-end">
                        <a href="{{ $profileUrl }}" class="btn btn-light view-btn">Visualizar Tarefas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
