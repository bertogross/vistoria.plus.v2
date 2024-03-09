@if(session('success'))
    <!-- Success Alert -->
    <div id="success-alert" class="alert alert-theme alert-dismissible alert-label-icon label-arrow fade show" role="alert">
        <i class="ri-check-double-line label-icon"></i>{!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss=" alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
        <i class="ri-alert-line label-icon"></i> {!! session('warning') !!}
        <button type="button" class="btn-close" data-bs-dismiss=" alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
        <i class="ri-error-warning-fill label-icon"></i> {!! session('error') !!}
        <button type="button" class="btn-close" data-bs-dismiss=" alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="list-unstyled mb-0">
            @foreach ($errors->all() as $error)
                <li><i class="ri-close-fill align-bottom me-1"></i> {!! $error !!}</li>
            @endforeach
        </ul>
    </div>
@endif
