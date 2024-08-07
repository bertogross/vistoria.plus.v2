
@php
    use App\Models\User;
    $countActive = 0;
    //appPrintR($users);
@endphp
<button id="btn-add-user" type="button" class="btn btn-sm btn-outline-theme float-end" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Adicionar Usuário">
    Adicionar
</button>

<h4 class="mb-0">Usuários Conectados</h4>

<p>Expanda sua equipe no {{ appName() }} adicionando novos membros para potencializar a colaboração e a produtividade.
    @if ($users->isNotEmpty())
        <br>Aqui estão os Usuários que você convidou para colaborar:
    @endif()
</p>

<div class="row mt-4">

    @if ($users->isNotEmpty())
        <div class="table-responsive">
            <table class="table align-middle table-nowrap table-bordered table-striped">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th scope="col">Usuário</th>
                        <th scope="col" width="140">Desde</th>
                        <th scope="col">Nível</th>
                        {{--
                        <th scope="col">Unidades Autorizadas</th>
                        --}}
                        <th scope="col">Status</th>
                        {{--
                        <th scope="col">Valor</th>
                        --}}
                        <th scope="col" width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $key => $user)
                        @php
                            $userId = $user->user_id;
                            $role = $user->role ?? 4;
                            $roleName = User::getRoleName($role);
                            $status = $user->status ?? 'inactive';
                                $countActive += $status == 'active' ? 1 : 0;
                            $since = $user->since ?? null;
                            //$companies = $user->companies ?? getActiveCompanieIds();
                            $profileUrl = route('profileShowURL', ['id' => $userId]) . '?d=' . now()->timestamp;
                            $getUserData = getUserData($userId);
                            $avatar = $getUserData->avatar ?? null;
                            $name = $getUserData->name ?? null;
                            $email = $getUserData->email ?? null;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="flex-shrink-0">
                                        {!!snippetAvatar($avatar, $name, 'avatar-xs rounded-circle fs-20')!!}
                                    </div>
                                    <div class="flex-grow-1" style="line-height: 16px;">
                                        {{$name}}
                                        <div class="small text-muted">{{$email}}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{$since ? date("d/m/Y H:i", strtotime($since)) : ''}}</td>
                            <td>{{$roleName}}</td>
                            {{--
                            <td>
                                @if (is_array($companies))
                                    <ul class="list-unstyled list-inline text-muted mb-0">
                                        @foreach ($companies as $key => $companyId)
                                            <li class="list-inline-item"><i class="ri-store-3-fill text-theme align-bottom me-1"></i>{{getCompanyNameById($companyId)}}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </td>
                            --}}
                            <td>
                                {!! User::statusTranslationLabel($status, true) !!}
                            </td>
                            {{--
                            <td>
                                @switch($status)
                                    @case('active')
                                        R$ 45
                                        @break

                                    @default
                                        -
                                @endswitch
                            </td>
                            --}}
                            <td>
                                <button type="button" class="btn btn-sm btn-soft-dark btn-edit-user ri-edit-line" data-user-id="{{$userId ?? ''}}" data-user-title="{{$name ?? ''}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Editar"></button>
                                <a href="{{$profileUrl}}" class="btn btn-sm btn-soft-dark ri-eye-line" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Visualizar Tarefas"></a>
                                {{--
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="{{$profileUrl}}" class="btn btn-sm btn-soft-dark ri-eye-line" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Visualizar Tarefas"></a>
                                    </div>
                                    <div class="col">
                                        <div class="form-check form-switch form-switch-success form-switch-lg" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="Status"
                                        @if ($status == 'revoked')
                                            data-bs-content="Este usuário <span class='text-warning'>revogou</span> o acesso. Somente <span class='text-info'>{{$name}}</span> poderá reativar."
                                        @else
                                            data-bs-content="Se Desativado este usuário perderá o acesso ao seu {{appName()}}"
                                        @endif
                                        >
                                            <input type="checkbox" class="form-check-input" name="status" id="user_status_{{$userId }}" value="active" {{ $status == 'active' ? 'checked' : '' }} {{ $status == 'revoked' ? 'disabled': '' }}>
                                            <label class="form-check-label" for="user_status_{{$userId}}">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                --}}

                            </td>
                        </tr>
                    @endforeach
                </tbody>
                {{--
                @if (!empty($users))
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5"></td>
                            <td>{{($countActive > 0 ? brazilianRealFormat($countActive * 45, 2) : '-')}}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
                --}}
            </table>
        </div>
    @else
        @component('components.nothing')
            @slot('text', 'Ainda não há membros na equipe de '.getCurrentConnectionName().'<br><br>Se estiver procurando por <strong class="text-body">Contas Conectadas</strong>, <a class="text-decoration-underline init-loader" href="'.route('settingsConnectionsIndexURL').'">clique aqui</a>.')
        @endcomponent
    @endif

</div>
