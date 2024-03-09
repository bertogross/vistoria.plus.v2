@php
    use App\Models\UserConnections;
@endphp
@foreach ($getActiveCompanies as $company)
    <div class="col-sm-12 col-md-6 col-lg-4" id="distributed-tab-company-{{ $company->id }}"
        @if (in_array($company->id, $selectedCompanies))
            style="display: block;"
        @else
            style="display: none;"
        @endif
        >
        <div class="card bg-body">
            <div class="card-header bg-body text-uppercase fw-bold text-theme">
                {{ $company->name }}
            </div>
            <div class="card-body">
                <ul class="list-unstyled vstack gap-2 mb-0">
                    @foreach ($users as $user)
                        @php
                            $userId = $user->id;
                            $userName = $user->name;
                            $userAvatar = checkUserAvatar($user->avatar);
                            $userCompanies = getAuthorizedCompanies($userId) ?? null;
                            $isDelegated = false;

                            if($userId == auth()->id()){
                                $userCompanies = getActiveCompanieIds();
                                $userRole = 1;
                                $userStatus = 'active';
                            }else{
                                $connection = UserConnections::getUserDataFromConnectedAccountId($userId, auth()->id());

                                $userCompanies = $connection->companies;
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
                        @if ( is_array($userCompanies) && in_array($company->id, $userCompanies) && in_array($userRole, [1, 3]) && $userStatus == 'active' )
                            <li>
                                <div class="form-check form-switch form-switch-success form-switch-md d-flex align-items-center">
                                    <input
                                        id="surveyor-user-{{ $company->id.$userId }}"
                                        class="form-check-input form-check-input-users me-3 wizard-switch-control"
                                        type="radio"
                                        name="surveyor[{{$company->id}}]"
                                        {{ $isDelegated ? 'checked' : '' }}
                                        value="{{ $userId }}">
                                    <label class="form-check-label d-flex align-items-center"
                                        for="surveyor-user-{{ $company->id.$userId }}">
                                        <span class="flex-shrink-0">
                                            <img src="{{$userAvatar}}" alt="{{ $userName }}" class="avatar-xxs rounded-circle">
                                        </span>
                                        <span class="flex-grow-1 ms-2">{{ $userName }}</span>
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
