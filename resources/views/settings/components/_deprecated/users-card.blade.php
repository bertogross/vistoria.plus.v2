<div class="col" data-search-user-name="{{$user->name}}">
    <div class="card team-box">
        <div class="team-cover" style="min-height: 140px">
            <img src="{{checkUserCover($user->cover)}}"
            alt="{{$user->name}}" class="img-fluid" height="140" loading="lazy">
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
                                    <li><a class="dropdown-item btn-edit-user cursor-pointer" data-user-id="{{$user->id ?? ''}}" data-user-name="{{$user->name ?? ''}}"><i class="ri-pencil-line me-2 align-bottom text-muted"></i>Editar</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-lg-4 col">
                    <div class="team-profile-img">
                        <div class="avatar-lg img-thumbnail rounded-circle flex-shrink-0"><img src="{{ checkUserAvatar($user->avatar) }}"
                            alt="{{$user->name}}"
                            class="member-img img-fluid d-block rounded-circle"loading="lazy">
                        </div>
                        <div class="team-content">
                            <h5 class="fs-16 mb-1 text-uppercase">{{$user->name}}</h5>
                            {{--
                            <p class="text-muted member-designation mb-0 fw-bold">
                                @if(isset($role))
                                    {{ $role }}
                                @endif
                            </p>
                            <p class="text-muted mb-0">
                                @if(isset($status) && $status == '1')
                                    <span class="text-success">Ativo</span>
                                @else
                                    <span class="text-danger">Inoperante</span>
                                @endif
                            </p>
                            --}}
                            {!! $user->last_login ? '<p class="small text-muted mb-0">Último Login: '.date('d/m/Y H:i', strtotime($user->last_login)).'</p>' : '' !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col">
                    <div class="row text-muted text-center">
                        @php
                            $requiredKeys = ['new', 'pending', 'in_progress', 'auditing', 'completed', 'losted'];

                            $countSurveyorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentSurveyorTasks($user->id, $requiredKeys);
                            $countAuditorTasks = \App\Models\SurveyAssignments::countSurveyAssignmentAuditorTasks($user->id, $requiredKeys);

                            $profileUrl = route('profileShowURL', ['id' => $user->id]) . '?d=' . now()->timestamp;
                        @endphp
                        {{--
                        @if ( in_array('audit', $capabilities) || $countAuditorTasks > 0 )
                            <div class="col-6 border-end border-end-dashed">
                                <h5 class="mb-1 projects-num">{{ $countAuditorTasks }}</h5>
                                <p class="text-muted mb-0">Auditorias</p>
                            </div>
                        @endif
                        <div class="{{ in_array('audit', $capabilities) || $countAuditorTasks > 0 ? 'col-6' : 'col-12' }}">
                            <h5 class="mb-1 tasks-num">{{ $countSurveyorTasks }}</h5>
                            <p class="text-muted mb-0">Vistorias</p>
                        </div>
                        --}}
                    </div>
                </div>
                <div class="col-lg-2 col">
                    <div class="text-end"> <a href="{{ $profileUrl }}" class="btn btn-light view-btn">Visualizar Tarefas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
