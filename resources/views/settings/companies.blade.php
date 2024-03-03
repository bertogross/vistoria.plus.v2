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
            Unidades Corporativas
        @endslot
    @endcomponent

    @include('components.alerts')

    @php
        $i = 0;
    @endphp
    <div class="card">
        <div class="card-body">
            <form class="needs-validation" novalidate method="POST" action="{{ route('settingsCompaniesUpdateURL') }}">
                @csrf

                <div class="table-responsive">
                    <table id="companiesTable" class="table table-bordered mb-0">
                    <thead class="table-light text-uppercase">
                        <tr>
                        <th scope="col" width="75" data-bs-toggle="tooltip" data-bs-placement="top" title="Quando desabilitado, esta Unidade não terá dados exibidos em relatórios">Status</th>
                        <th scope="col"  width="75">ID</th>
                        <th scope="col">Nome</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $index => $company)
                            @php
                                $i++;
                                $companyId = $company->id;
                                $companyStatus = $company->status == 1 ? 'checked' : '' ; // If checkbox is checked, status is 1; otherwise, it's 0.
                                $companyName = $company->name;
                            @endphp
                        <tr>
                            <td class="align-middle">
                                <div class="form-check form-switch form-switch-md form-switch-theme ms-3" data-bs-toggle="tooltip" data-bs-placement="top" title="{{$companyId == 1 ? 'Não é possível desativar Unidade ID 1' : 'Ativar/Desativar'}}">
                                    <input type="checkbox" {{$companyId == 1 ? 'readonly disabled checked' : ''}} class="form-check-input" id="switch-{{$companyId}}" name="companies[{{ $index }}][status]" {{$companyStatus}}>
                                </div>
                            </td>
                            <td class="align-middle">
                                {{$companyId}}
                                <input type="hidden" name="companies[{{ $index }}][id]" value="{{$companyId}}">
                            </td>
                            <td class="align-middle">
                                <input type="text" class="form-control form-control-sm" name="companies[{{ $index }}][name]" value="{{$companyName}}" maxlength="50" tabindex="{{$i}}" required>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="align-top">
                                <button type="button" class="btn btn-sm btn-label left btn-outline-theme waves-effect float-start" id="btn-add-company-row" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" data-bs-original-title="Adicionar Unidade">
                                    <i class="ri-add-line label-icon align-middle fs-16 me-2"></i>Unidade
                                </button>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-label right btn-outline-theme float-end waves-effect" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="left" data-bs-original-title="Atualizar Listagem de Unidades">
                                    <i class="ri-save-line label-icon align-middle fs-16 ms-2"></i>Atualizar
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                    </table>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/settings-companies.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
