@extends('layouts.master')
@section('title')
    @lang('translation.file-manager')
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
            @lang('translation.file-manager')
        @endslot
    @endcomponent

    @include('components.alerts')

    <div class="card">
        <div class="card-body">
            @if ( getDropboxToken() )
                <div id="folder-list" class="mb-2">
                    <div class="row justify-content-beetwen g-2 mb-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2 d-block d-lg-none">
                                    <button type="button" class="btn btn-soft-success btn-icon btn-sm fs-16 file-menu-btn">
                                        <i class="ri-menu-2-fill align-bottom"></i>
                                    </button>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ri-database-2-line fs-17"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3 overflow-hidden">
                                            <div class="progress mb-2 progress-sm animated-progress custom-progress">
                                                <div class="progress-bar {{ $storageInfo['percentageUsed'] <= 80 ? 'bg-info' : 'bg-danger' }}" role="progressbar" style="width: {{ $storageInfo['percentageUsed'] ?? '' }}%" aria-valuenow="{{ $storageInfo['percentageUsed'] ?? '' }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted fs-12 d-block text-truncate"><b>{{ formatSize($storageInfo['used']) ?? '' }}</b> utilizados de <b>{{ $storageInfo['total'] ?? '' }}</b>GB alocados</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col"></div>
                        <div class="col-auto text-end">
                            <form id="upload-form" method="post" enctype="multipart/form-data" autocomplete="off">
                                @csrf
                                <div class="input-group text-theme">
                                    <input type="file" name="file[]" required class="form-control" id="inputGroupFile" multiple>
                                    <label class="input-group-text btn-theme" for="inputGroupFile">Enviar Arquivos</label>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end row-->
                    <div class="row" id="folderlist-data">
                        <div class="row" id="folderlist-data">
                            @foreach ($folders as $key => $folder)
                                <div class="col-lg-3 col-xxl-2 col-6 folder-card">
                                    <div class="card bg-light shadow-none" id="folder-{{ $folder['id'] ?? '' }}">
                                        <div class="card-body">
                                            <div class="d-flex mb-1">
                                                <div class="form-check form-check-danger mb-3 fs-15 flex-grow-1">

                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-ghost-theme btn-icon btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-2-fill fs-16 align-bottom"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('DropboxBrowseFolderURL', ['path' => ltrim($folder['path_display'], '/')]) }}">
                                                                Abrir
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('DropboxDeleteFolderURL', ['path' => ltrim($folder['path_display'], '/')]) }}" onclick="return confirm('Are you sure you want to delete this folder and all its contents?')">
                                                                Deletar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="text-center">
                                                <div class="mb-2">
                                                    <a class="dropdown-item" href="{{ route('DropboxBrowseFolderURL', ['path' => ltrim($folder['path_display'], '/')]) }}">
                                                        <i class="ri-folder-2-fill align-bottom text-warning display-5"></i>
                                                    </a>
                                                </div>
                                                <h6 class="fs-15 folder-name">
                                                    <a href="{{ route('DropboxBrowseFolderURL', ['path' => ltrim($folder['path_display'], '/')]) }}">
                                                        {{ $folder['name'] ?? '' }}
                                                    </a>
                                                </h6>
                                            </div>

                                            <div class="row">
                                                <div class="col">
                                                    <span class="me-auto"><b>{{ $folder['file_count'] ?? '' }}</b> Arquivo{{ intval($folder['file_count']) > 1 ? 's' : '' }}</span>
                                                </div>
                                                <div class="col text-end">
                                                    <span><b>{{ $folder['size'] ?? '' }}</b>GB</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!--end row-->
                </div>
                <div>

                    <div class="row mb-3">
                        <h5 class="col fs-16 mb-0" id="filetype-title">
                            @if($currentFolderPath && $currentFolderPath != '/')
                                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-theme"><i class="ri-arrow-go-back-fill align-middle"></i> Voltar</a>
                            @else
                                {{ isset($files) && is_array($files) && count($files) > 1 ? 'Arquivos' : 'Listagem' }}
                            @endif
                        </h5>
                        <div class="col-auto text-end">
                            <div class="d-flex gap-2 align-items-start">
                                <select class="form-control form-select" id="file-type" onchange="filterByFileType()">
                                    <option value="" disabled>Tipos de Arquivo</option>
                                    <option value="All" selected>Todos</option>
                                    <option value="Video">Vídeo</option>
                                    <option value="Images">Imagens</option>
                                    <option value="Music">Múscia</option>
                                    <option value="Documents">Documentos</option>
                                </select>
                                {{--
                                <button class="btn btn-success w-sm create-folder-modal flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createFolderModal"><i class="ri-add-line align-bottom me-1"></i> Create Folders</button>
                                --}}
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive table-card mt-2">
                        <table class="table align-middle table-hover table-striped table-nowrap mb-0">
                            <thead class="table-active text-uppercase">
                                <tr>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Peso</th>
                                    <th scope="col">Data</th>
                                    <th scope="col" class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="file-list">
                                @foreach ($files as $key => $file)
                                    <tr data-file-id="{{$file['id']}}">
                                        <td>
                                            @php
                                            $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);

                                            $serverModified = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $file['server_modified']);
                                            $formattedDate = $serverModified->format('d/m/Y');

                                            @endphp

                                            @if ($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'png' || $fileType == 'gif')
                                                <i class="ri-gallery-fill align-bottom text-success"></i>
                                            @elseif ($fileType == 'pdf')
                                                <i class="ri-file-pdf-fill align-bottom text-danger"></i>
                                            @elseif ($fileType == 'txt' || $fileType == 'doc' || $fileType == 'docx')
                                                <i class="ri-file-text-fill align-bottom text-secondary"></i>
                                            @else
                                                <i class="ri-file-fill align-bottom text-primary"></i>
                                            @endif
                                            {{ $file['name'] ?? '' }}
                                        </td>
                                        <td>{{ formatSize($file['size']) ?? '' }}</td>
                                        <td>{{ $formattedDate ?? '' }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ $file['link'] ?? '#' }}" download class="btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Download"><i class="ri-download-2-line"></i></a>

                                                <button class="btn btn-sm btn-outline-dark btn-delete-file" data-path="{{ $file['path_display'] }}" data-id="{{$file['id']}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Deletar">
                                                    <i class="ri-delete-bin-5-line text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (intval($totalFiles) > 1 && $perPage < intval($totalFiles))
                        <div class="align-items-center mt-2 row g-3 text-center text-sm-start">
                            <div class="col-sm">
                                <div class="text-muted">
                                    Exibindo de <span class="fw-semibold">{{ $page * $perPage - $perPage + 1 }}</span> até <span class="fw-semibold">{{ min($page * $perPage, $totalFiles) }}</span> {{ intval($totalFiles) > 1 ? 'dos' : 'de' }} <span class="fw-semibold">{{ $totalFiles }}</span>
                                </div>
                            </div>
                            <div class="col-sm-auto">
                                <ul class="pagination pagination-separated pagination-sm justify-content-center justify-content-sm-start mb-0">
                                    <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                                        <a href="{{ route('DropboxIndexURL', ['page' => $page - 1]) }}" class="page-link">←</a>
                                    </li>
                                    @for ($i = 1; $i <= ceil($totalFiles / $perPage); $i++)
                                        <li class="page-item {{ $page == $i ? 'active' : '' }}">
                                            <a href="{{ route('DropboxIndexURL', ['page' => $i]) }}" class="page-link">{{ $i }}</a>
                                        </li>
                                    @endfor
                                    <li class="page-item {{ $page == ceil($totalFiles / $perPage) ? 'disabled' : '' }}">
                                        <a href="{{ route('DropboxIndexURL', ['page' => $page + 1]) }}" class="page-link">→</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show">
                    <i class="ri-alert-line label-icon"></i> Necessário estabelecer a conexão com seu Driver. Acesse as configurações em <a href="{{ route('settingsApiKeysURL') }}" class="text-decoration-underline" title="Acessar configurações @lang('translation.api-keys')">@lang('translation.api-keys')</a>.
                </div>
            @endif
        </div>
    </div>

@endsection
@section('script')
    @if (getDropboxToken())
        <script>
            var DropboxAccessToken = '{{ getDropboxToken() }}';
            var DropboxUploadURL = "{{ route('DropboxUploadURL') }}";
            var DropboxDeleteURL = "{{ route('DropboxDeleteURL') }}";
            var DropboxCurrentFolderPath = "{{ $currentFolderPath }}";
        </script>
        <script src="{{URL::asset('build/js/settings-storage.js')}}" type="module"></script>
    @endif
@endsection
