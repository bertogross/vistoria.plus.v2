
@php
    use App\Models\User;
    $countActive = 0;
    //appPrintR($users);
@endphp
<button type="button" id="btn-add-user" class="btn btn-sm btn-label right btn-outline-theme float-end waves-effect" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" title="Adicionar Usuário">
    <i class="ri-user-add-line label-icon align-middle fs-16 ms-2"></i>Adicionar
</button>

<h4 class="mb-0">Usuários Conectados</h4>

<p>Expanda sua equipe no {{ appName() }} adicionando novos membros para potencializar a colaboração e a produtividade</p>

<div class="row mt-4">

    @if ($users->isNotEmpty())
        <div class="table-responsive">
            <table class="table align-middle table-nowrap table-bordered table-striped">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th scope="col">Usuário</th>
                        <th scope="col" width="140">Desde</th>
                        <th scope="col">Nível</th>
                        <th scope="col">Unidades Autorizadas</th>
                        <th scope="col">Status</th>
                        {{--
                        <th scope="col">Valor</th>
                        --}}
                        <th scope="col" width="82"></th>
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
                            $companies = $user->companies ?? getActiveCompanieIds();
                            $profileUrl = route('profileShowURL', ['id' => $userId]) . '?d=' . now()->timestamp;
                            $getUserData = getUserData($userId);
                            $avatar = $getUserData->avatar;
                            $name = $getUserData->name;
                            $email = $getUserData->email;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="{{ checkUserAvatar($avatar) }}" alt=""
                                            class="avatar-xs rounded-circle">
                                    </div>
                                    <div class="flex-grow-1" style="line-height: 16px;">
                                        {{$name}}
                                        <div class="small text-muted">{{$email}}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{$since ? date("d/m/Y H:i", strtotime($since)) : ''}}</td>
                            <td>{{$roleName}}</td>
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
                            <td>
                                @switch($status)
                                    @case('active')
                                        <span class="text-success">
                                            <i class="ri-checkbox-circle-line fs-17 align-middle"></i> Ativo
                                        </span>
                                        @break

                                    @case('inactive')
                                        <span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Inoperante pois foi por você desativado">
                                            <i class="ri-close-circle-line fs-17 align-middle"></i> Inativo
                                        </span>
                                        @break

                                    @case('revoked')
                                        <span class="text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Quando o usuário revogou a conexão">
                                            <i class="ri-alert-line fs-17 align-middle"></i> Desconectado
                                        </span>
                                        @break

                                    @default
                                        <span class="text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Aguardando o aceite de seu convite">
                                            <i class="ri-information-line fs-17 align-middle"></i> Aguardando
                                        </span>
                                @endswitch
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
                                <button type="button" class="btn btn-sm btn-soft-dark waves-effect btn-edit-user ri-edit-line" data-user-id="{{$userId ?? ''}}" data-user-title="{{$name ?? ''}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Editar"></button>

                                <a href="{{$profileUrl}}" class="btn btn-sm btn-soft-dark waves-effect ri-eye-line" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Visualizar Tarefas"></a>
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
            @slot('text', 'Ainda não há membros na equipe de '.getCurrentConnectionName().'')
        @endcomponent
    @endif

</div>
