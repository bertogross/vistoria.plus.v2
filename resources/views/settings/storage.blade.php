@extends('layouts.master')
@section('title')
    @lang('translation.file-manager')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
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
            @lang('translation.storage')
        @endslot
    @endcomponent
    <p>Aquivos anexados em vistorias</p>
    <div class="chat-wrapper d-lg-flex gap-1 mx-n4 p-1 mb-4">
        <div class="file-manager-content minimal-border w-100 p-3 py-0">
            <div class="mx-n3 pt-4 px-4 file-manager-content-scroll" data-simplebar>
                <div>
                    <div class="row mb-3">
                        <div class="col">
                            <h5 class="flex-grow-1 fs-16 mb-0" id="filetype-title">{{$totalFiles > 0 ? $totalFiles : 'Nenhum'}} Arquivo{{$totalFiles > 1 ? 's' : '';}}</h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center" style="width: 200px;">
                                <div class="flex-shrink-0">
                                    <i class="ri-database-2-line fs-17"></i>
                                </div>
                                <div class="flex-grow-1 ms-3 overflow-hidden">
                                    <div class="progress mb-2 progress-sm">
                                        <div class="progress-bar bg-{{getProgressBarClassStorage($percentageUsed)}}" role="progressbar" style="width: {{number_format($percentageUsed, 0)}}%" aria-valuenow="{{number_format($percentageUsed, 0)}}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="text-muted fs-12 d-block text-truncate"><b data-bs-toggle="tooltip" data-bs-placement="top" title="{{$totalUsage}} utilizados">{{$totalUsage}}</b>GB de <b data-bs-toggle="tooltip" data-bs-placement="top" title="{{$diskQuota}}GB contratados">{{$diskQuota}}</b>GB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($data->isEmpty() && $totalFiles == 0)
                        @component('components.nothing')
                            @slot('text', 'Arquivos ainda não foram anexados')
                        @endcomponent
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap">
                                <thead class="table-active">
                                    <tr class="text-uppercase">
                                        <th scope="col" class="text-center" width="30"></th>
                                        <th scope="col" width="135"></th>
                                        <th scope="col"></th>
                                        <th scope="col">Peso</th>
                                        <th scope="col">Data</th>
                                    </tr>
                                </thead>
                                <tbody id="file-list">
                                @foreach ($data as $file)
                                    @php
                                        $attachmentId = App\Models\Attachments::getAttachmentIdByPath($file['path']);
                                    @endphp
                                    <tr id="element-attachment-{{$attachmentId}}">
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-dark ri-delete-bin-2-line btn-delete-photo" data-attachment-id="{{$attachmentId}}" data-attachment-path="{{$file['path']}}"></button>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 fs-17 me-2">
                                                    <a href="{{ $file['url'] }}" class="image-single">
                                                        <img class="rounded" src="{{ $file['url'] }}" height="90" alt="{{ $file['name'] }}" loading="lazy">
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $file['name'] }}</td>
                                        <td class="filelist-size">{{ number_format($file['size'] / 1024 / 1024, 2) }} MB</td>
                                        <td class="filelist-create">{{ date('d/m/Y', $file['lastModified']) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-center mb-3 p-3 bg-light">
                {!! $data->links('layouts.custom-pagination') !!}
            </div>
        </div>

    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

    <script>
        var settingsAccountShowURL = "{{ route('settingsAccountShowURL') }}";
        var uploadPhotoURL = "{{ route('uploadPhotoURL') }}";
        var deletePhotoURL = "{{ route('deletePhotoURL') }}";
        var deleteAttachmentByPathURL = "{{ route('deleteAttachmentByPathURL') }}";
        var assetURL = "{{ URL::asset('/') }}";
    </script>
    <script src="{{ URL::asset('build/js/surveys-attachments.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
