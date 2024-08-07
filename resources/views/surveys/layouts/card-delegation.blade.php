<div class="card mb-3">
    <div class="card-header">
        <h4 class="card-title text-uppercase mb-0 flex-grow-1">
            Atribuições
            @if($swapData)
                <span class="text-theme">{{getCompanyNameById($companyId)}}</span>
            @else
                Globais
            @endif
        </h4>
    </div>
    <div class="card-body h-100 pb-0">
        <div class="row">
            <div class="col-sm-12 col-md-6 mb-3">
                Vistoria:
                @if (isset($delegation['surveyors']) && !empty($delegation['surveyors']))
                    @foreach ($delegation['surveyors'] as $key => $value)
                        @php
                            $userId = $value['user_id'] ?? null;
                            $getUserData = $userId ? getUserData($userId) : null;
                                $surveyorsName = $getUserData->name ?? null;
                                $surveyorsAvatar = $getUserData->avatar ?? null;
                            $userCompanyId = $value['company_id'] ?? null;
                            $companyName = $userCompanyId ? getCompanyNameById($userCompanyId) : '';
                        @endphp
                        @if ($userId)
                            <a href="{{ route('profileShowURL', $userId) }}" class="avatar-group-item ms-2 {{ $swapData && $userCompanyId != $companyId ? 'd-none' : '' }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Vistoria: {{ $surveyorsName }} : {{ $companyName }}">
                                {!!snippetAvatar($surveyorsAvatar, $surveyorsName, 'rounded-circle avatar-xxs')!!}
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>

            <div class="col-sm-12 col-md-6 mb-3">
                Auditoria:
                @if (isset($delegation['auditors']) && !empty($delegation['auditors']))
                    @foreach ($delegation['auditors'] as $key => $value)
                        @php
                            $userId = $value['user_id'] ?? null;
                            $getUserData = $userId ? getUserData($userId) : null;
                                $auditorName = $getUserData->name ?? null;
                                $auditorAvatar = $getUserData->avatar ?? null;
                            $userCompanyId = $value['company_id'] ?? null;
                            $companyName = $userCompanyId ? getCompanyNameById($userCompanyId) : '';
                        @endphp
                        @if ($userId)
                            <a href="{{ route('profileShowURL', $userId) }}" class="avatar-group-item ms-2 {{ $swapData && $userCompanyId != $companyId ? 'd-none' : '' }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Auditoria: {{ $auditorName }} : {{ $companyName }}">
                                {!!snippetAvatar($auditorAvatar, $auditorName, 'rounded-circle avatar-xxs')!!}
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
