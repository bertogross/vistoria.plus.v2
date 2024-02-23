<div class="text-center m-4">
    @if (isset($_REQUEST['filter']))
        <h2 class="text-uppercase">&#129488;</h2>
        <p class="text-muted mb-4">Os parâmetros desta pesquisa não retornaram dados!</p>
    @else
        <h5 class="text-uppercase">Ainda não há dados 😭</h5>
        {{--
        <p class="text-muted mb-4 d-none">Você deverá registrar informações!</p>
        --}}
        @if (isset($text))
            <p class="text-muted mb-4">{!! $text !!}</p>
        @endif
        @if (isset($url))
            <a class="btn btn-outline-theme" href="{{ $url }}"><i class="ri-add-line"></i></a>
        @endif
        @if (isset($warning))
            <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="ri-alert-line label-icon"></i> {!! $warning !!}
            </div>
        @endif
    @endif
</div>
