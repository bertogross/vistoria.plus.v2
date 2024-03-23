@php
    use App\Models\UserConnections;
@endphp
@foreach ($getActiveCompanies as $company)
    @php
        $colVisibility = 'style="display: block;"';
        if($surveyId && $data && ( $countTodayResponses > 0 || $countAllResponses > 0 )){
            if(!in_array(intval($company->id), $selectedCompanies)){
                $colVisibility = 'style="display: none;"';
            }
        }
    @endphp
    <div class="col-sm-12 col-md-6 col-lg-4" id="distributed-tab-company-{{ $company->id }}" {!! $colVisibility !!}>
        <div class="card bg-body">
            <div class="card-header bg-body text-uppercase fw-bold text-theme">
                <div class="form-check form-switch form-switch-success form-switch-md">
                    <input
                    class="form-check-input form-check-input-companies wizard-switch-control"
                    type="checkbox"
                    role="switch"
                    {{ !empty($selectedCompanies) && is_array($selectedCompanies) && in_array(intval($company->id), $selectedCompanies) ? 'checked' : '' }}
                    id="company-{{ $company->id }}"
                    name="companies[]"
                    value="{{ $company->id }}"
                    required>
                    <label class="form-check-label" for="company-{{ $company->id }}">{{ empty($company->name) ? e($company->name) : e($company->name) }}</label>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled vstack gap-2 mb-0">
                    @foreach ($users as $user)
                        @php
                            $userId = $user->id;
                            $userName = $user->name;
                            $userAvatar = checkUserAvatar($user->avatar);
                            $isDelegated = false;

                            if($userId == auth()->id()){
                                //$userCompanies = getActiveCompanieIds();
                                $userRole = 1;
                                $userStatus = 'active';
                            }else{
                                $connection = UserConnections::getGuestDataFromConnectedHostId($userId, auth()->id());

                                //$userCompanies = $connection->companies;
                                $userRole = $connection->role;
                                $userStatus = $connection->status;
                            }

                            // Loop through the distributed data to find if this user has been delegated to this company
                            if( $data && isset($distributedData) && is_array($distributedData['surveyor']) ){
                                foreach ($distributedData['surveyor'] as $delegation) {
                                    if ($delegation['company_id'] == $company->id && $delegation['user_id'] == $userId) {
                                        $isDelegated = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if ( in_array($userRole, [1, 3]) )
                            <li
                            @if (in_array($userStatus, ['inactive', 'revoked']))
                                data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" title="<span class='text-danger'>Usuário indisponível</span>"
                            @endif
                            >
                                <div class="form-check form-switch form-switch-success form-switch-md d-flex align-items-center">
                                    <input
                                        id="surveyor-user-{{ $company->id.$userId }}"
                                        class="form-check-input form-check-input-users me-3 wizard-switch-control"
                                        type="radio"
                                        name="surveyor[{{$company->id}}]"
                                        {{ $isDelegated ? 'checked' : '' }}
                                        {{ in_array($userStatus, ['inactive', 'revoked']) ? 'readonly' : '' }}
                                        value="{{ $userId }}">
                                    <label class="form-check-label d-flex align-items-center"
                                        for="surveyor-user-{{ $company->id.$userId }}">
                                        <span class="flex-shrink-0">
                                            <img src="{{$userAvatar}}" alt="{{ $userName }}" class="avatar-xxs rounded-circle">
                                        </span>
                                        <span class="flex-grow-1 ms-2 {{ in_array($userStatus, ['inactive', 'revoked']) ? 'text-danger' : '' }}">{{ $userName }}</span>
                                    </label>
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endforeach
