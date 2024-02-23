<div class="d-flex justify-content-end mt-2">
    <div class="pagination-wrap hstack gap-2">
        @if ($paginator->onFirstPage())
            <span class="page-item pagination-prev disabled">
                Anterior
            </span>
        @else
            <a class="page-item pagination-prev" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                Anterior
            </a>
        @endif

        <ul class="pagination listjs-pagination mb-0">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a class="page-item pagination-next" href="{{ $paginator->nextPageUrl() }}" rel="next">
                Próxima
            </a>
        @else
            <span class="page-item pagination-next disabled">
                Próxima
            </span>
        @endif
    </div>
</div>
