@extends('layouts.master')
@section('title')
    @lang('translation.api-keys')
@endsection
@section('css')
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
            @lang('translation.api-keys')
        @endslot
    @endcomponent

    @include('components.alerts')

    <div class="table-responsive border border-1 border-light rounded">
        <table class="table align-middle table-hover table-striped table-nowrap mb-0">
            <thead class="table-light text-uppercase">
                <tr>
                    <th>Origem</th>
                    <th>Conta de</th>
                    <th>Conectada por</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="list form-check-all">
                <tr>
                    <td>Dropbox API</td>
                    <td>{{ !empty($DropBoxUserAccountInfo) ? $DropBoxUserAccountInfo['name']['display_name'] : '-' }}</td>
                    <td>
                        {{ !empty($DropBoxUserAccountInfo) ? $DropBoxUserAccountInfo['email'] : '-' }}
                    </td>
                    <td>
                        @if ( empty($DropBoxUserAccountInfo) || $DropBoxUserAccountInfo['disabled'] )
                            <span class="badge bg-danger-subtle text-danger">Desativado</span>
                        @else
                            <span class="badge bg-success-subtle text-success">Ativo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if ( getDropboxToken() && !empty($DropBoxUserAccountInfo))
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('DropboxDeauthorizeURL') }}">
                                Desconectar Dropbox
                            </a>

                            <a href="{{route('DropboxIndexURL')}}" class="btn btn-sm btn-outline-theme" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Visualizar os Arquivos"><i class="ri-folder-open-line"></i></a>
                        @else
                            <a class="btn btn-sm btn-primary" target="_blank" href="{{ route('DropboxAuthorizeURL') }}">
                                Conectar ao Dropbox
                            </a>
                        @endif
                    </td>
                </tr>
                {{--
                <tr>
                    <td>Google Drive API</td>
                    <td>
                        -
                    </td>
                    <td>
                        -
                    </td>
                    <td><span class="badge bg-danger-subtle text-danger">Disable</span></td>
                    <td class="text-end">
                        @if (getGoogleToken())
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('GoogleDriveDeauthorizeURL') }}">
                                Deauthorize Google Drive
                            </a>
                        @else
                            <a class="btn btn-sm btn-primary" href="{{ route('GoogleDriveRedirectURL') }}" target="_blank">
                                Authorize Google Drive
                            </a>
                        @endif
                    </td>
                </tr>
                --}}
            </tbody>
        </table>
    </div>

@endsection
@section('script')

@endsection
