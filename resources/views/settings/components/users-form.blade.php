@php
    use App\Models\UserConnections;

    $currentUserId = auth()->id();

    $getActiveCompanies = getActiveCompanies();

    $getUserDataFromConnectedAccountId = $user ? UserConnections::getUserDataFromConnectedAccountId($user->id, $currentUserId) : null;

    $getUserRole = $user && isset($getUserDataFromConnectedAccountId->role) ? intval($getUserDataFromConnectedAccountId->role) : null;

    $getUserStatus = $user && isset($getUserDataFromConnectedAccountId->status) ? $getUserDataFromConnectedAccountId->status : null;

    $getAuthorizedCompanies = $user && isset($getUserDataFromConnectedAccountId->companies) ? $getUserDataFromConnectedAccountId->companies : null;

    //appSendEmail('bertogross@gmail.com', 'customer name here', 'subject here', 'content here with <strong>strong</strong>', 'welcome');
@endphp
<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-right">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary-subtle bg-gradient p-3">
                <h5 class="card-title mb-0" id="modalUserTitle"></h5>

                <button type="button" class="btn-close btn-destroy" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="alert alert-info rounded-0 mb-0 small pt-1 pb-1">
                R$45/mês por cada usuário ativo
            </div>
            <div class="modal-body bg-primary-subtle bg-gradient">
                <form autocomplete="off" id="userForm" class="needs-validation" autocomplete="off" data-id="{{ $user ? $user->id : '' }}" novalidate>
                    @csrf

                    <div class="row">
                        <div class="col-lg-12">
                            <input type="hidden" name="user_id" value="{{ $user ? $user->id : '' }}" class="form-control">

                            @if (!$user)
                                <p>
                                    @if($origin && $origin == 'survey')
                                        Convide uma pessoa para colaborar com esta tarefa.
                                    @else
                                        Adicione o usuário colaborador que irá realizar atividades concedendo a atribuição para Vistoria ou Auditoria.
                                    @endif
                                </p>
                            @endif
                            <div class="form-group mb-4">
                                @if ($user)
                                    E-mail: <span class="text-theme">{{$user->email}}</span>
                                @else
                                    <i class="ri-question-line text-info non-printable align-top float-end" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="E-mail" data-bs-content="O endereço de e-mail que receberá o convite para acesso ao seu {{env('APP_NAME')}}"></i>
                                    <label for="teamMemberEmail" class="form-label">
                                        Endereço de E-mail
                                    </label>
                                    <input type="email" class="form-control" id="teamMemberEmail" name="email" placeholder="Digite o e-mail aqui" value="{{ $user ? $user->email : '' }}" required>
                                @endif
                            </div>

                            <div class="form-group mb-4 {{ $origin && $origin == 'survey' ? 'd-none' : '' }}">
                                <label for="select-role" class="form-label">
                                    Nível
                                    <i class="ri-question-line text-primary non-printable align-top" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="Níveis e Permissões" data-bs-content="<ul class='list-unstyled mb-0'><li>Saiba mais visualizando ao final desta página a tabela contendo o grid de Níveis e Permissões</li></ul>"></i>
                                </label>
                                <select class="form-control form-select" name="role" id="select-role" required>
                                    <option
                                    disabled
                                    @if ( !$origin && !$getUserRole)
                                        selected
                                    @endif
                                    class="text-body">- Selecione -</option>
                                    @foreach(\App\Models\User::USER_ROLES as $key => $role)
                                        @if ($key != 1)
                                            <option class="text-muted"
                                            @if ($origin && $origin == 'survey' && $key == 3)
                                                selected
                                            @else
                                                {{ $key === $getUserRole ? 'selected' : '' }}
                                            @endif
                                            value="{{ $key }}">{{ \App\Models\User::getRoleName($key) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            @if ($user)
                                <div class="form-group mb-4">
                                    <div class="form-check form-switch form-switch-success form-switch-lg">
                                        <input type="checkbox" class="form-check-input" name="status" id="user_status_1" value="active" {{ $getUserStatus == 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user_status_1">Status
                                            <i class="ri-question-line text-primary non-printable align-top" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Se Desativado este usuário não terá acesso ao seu {{appName()}}"></i>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-4 {{ $origin && $origin == 'survey' ? 'd-none' : '' }}">
                                <label class="form-label">Unidades Relacionadas <i class="ri-question-line text-primary non-printable align-top" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Selecione a{{ count($getActiveCompanies) > 1 ? 's' : ''}} Unidade{{ count($getActiveCompanies) > 1 ? 's' : ''}} Corporativa{{ count($getActiveCompanies) > 1 ? 's' : ''}} em que este usuário contribuíra"></i></label>
                                @if(isset($getActiveCompanies) && count($getActiveCompanies) > 0)
                                    <div class="row">
                                        @foreach($getActiveCompanies as $company)
                                            <div class="col-md-6">
                                                <div class="form-check form-switch form-switch-success form-switch-md mb-3">
                                                    <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    role="switch"
                                                    @if ($user)
                                                        {{ !empty($getAuthorizedCompanies) && is_array($getAuthorizedCompanies) && in_array(intval($company->id), $getAuthorizedCompanies) ? 'checked' : '' }}
                                                    @else
                                                        checked
                                                    @endif
                                                    id="company-{{ $company->id }}"
                                                    name="companies[]"
                                                    value="{{ $company->id }}">
                                                    <label class="form-check-label" for="company-{{ $company->id }}">{{ $company->name ?? 'Empresa '.$company->id }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">Empresas ainda não foram cadastradas/ativadas</div>
                                @endif
                            </div>

                            <div class="hstack gap-2 justify-content-end">
                                <button
                                id="btn-save-user"
                                type="button"
                                @if ($origin && $origin == 'survey')
                                    data-origin="{{$origin}}"
                                @endif
                                class="btn btn-theme"
                                ></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

