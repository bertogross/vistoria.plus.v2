@php
    use App\Models\User;
@endphp
@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('url')
            {{ url('settings') }}
        @endslot
        @slot('li_1')
            @lang('translation.settings')
        @endslot
        @slot('title')
            Minhas Conexões
        @endslot
    @endcomponent

    <p>Aqui estão as Contas nas quais o seu {{ appName() }} possui uma conexão para colaboração</p>

    @include('components.alerts')

    <div class="card">
        <div class="card-body">
            @if($myConnections->isEmpty())
                @component('components.nothing')
                    @slot('text', 'Você ainda não possui conexão entre contas.')
                @endcomponent
            @else
                <div class="table-responsive">
                    <table id="companiesTable" class="table table-bordered mb-0">
                        <thead class="table-light text-uppercase">
                            <tr class="text-uppercase">
                                <th scope="col">Conexão</th>
                                <th scope="col" width="140">Desde</th>
                                <th scope="col">Nível</th>
                                {{--
                                <th scope="col">Unidades Autorizadas</th>
                                --}}
                                <th scope="col" class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($myConnections as $index => $connection)
                            @php
                                $hostId = $connection->user_id;
                                $getHostUserData = getUserData($hostId);
                                $hostAvatar = $getHostUserData->avatar;
                                $hostEmail = $getHostUserData->email;
                                $hostName = $getHostUserData->name;

                                $questStatus = $connection->status;
                                $questInput = $questStatus == 'active' ? 'checked' : '';
                                $questSince = $connection->since ?? null;
                                $questRole = $connection->role ?? null;
                                $questRoleName = User::getRoleName($questRole) ?? null;
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <div class="d-flex gap-2 align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="{{ checkUserAvatar($hostAvatar) }}" alt="{{$hostName}}"
                                                class="avatar-xs rounded-circle" loading="lazy">
                                        </div>
                                        <div class="flex-grow-1" style="line-height: 16px;">
                                            {{$hostName}}
                                            <div class="small text-muted">{{$hostEmail}}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">{{$questSince ? date("d/m/Y H:i", strtotime($questSince)) : ''}}</td>
                                <td class="align-middle">{{$questRoleName}}</td>
                                {{--
                                <td class="align-middle">
                                    @if (is_array($questCompanies))
                                        <ul class="list-unstyled list-inline text-muted mb-0">
                                            @foreach ($questCompanies as $key => $companyId)
                                                <li class="list-inline-item"><i class="ri-store-3-fill text-theme align-bottom me-1"></i>{{getCompanyNameById($companyId)}}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </td>
                                --}}
                                <td class="align-middle text-end" style="max-width: 100px;">
                                    @if ($questStatus == 'waiting')
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-accept-invitation" data-host-id="{{$hostId}}" data-host-name="{{$hostName}}">
                                            Verificar Convite
                                        </button>
                                    @elseif ($questStatus == 'inactive')
                                        <span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Sua conexão foi desativada por <span class='text-info'>{{$hostName}}</span>">
                                            <i class="ri-close-circle-line fs-17 align-middle"></i> Inativo
                                        </span>
                                    @elseif ($questStatus == 'revoked' || $questStatus == 'active')
                                        <div class="form-check form-switch form-switch-md form-switch-success ms-3 float-end">
                                            <input type="checkbox" class="form-check-input toggle-status-connection" id="switch-{{$hostId}}" {{ $questStatus == 'inactive' ? 'disabled readonly' : $questInput }} name="host_id" value="{{$hostId}}" data-quest-status="{{$questStatus}}" data-bs-toggle="tooltip" data-bs-placement="left" title="Ativar/Revogar">
                                            <label class="form-check-label" for="switch-{{$hostId}}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $questStatus == 'revoked' ? 'Você revogou esta conexão' : 'Conexão Ativa' }}">
                                                {{ $questStatus == 'revoked' ? 'Revogado' : 'Ativo' }}
                                            </label>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        var revokeConnectionURL = "{{ route('revokeConnectionURL') }}";
    </script>
    <script src="{{ URL::asset('build/js/settings-connections.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
