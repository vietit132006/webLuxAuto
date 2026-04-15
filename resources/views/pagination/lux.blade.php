@if ($paginator->hasPages())
    <nav class="lux-pag" role="navigation" aria-label="Phân trang">
        <div class="lux-pag__inner">
            @if ($paginator->onFirstPage())
                <span class="lux-pag__btn lux-pag__btn--disabled">‹ Trước</span>
            @else
                <a class="lux-pag__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Trước</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="lux-pag__dots">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="lux-pag__num lux-pag__num--current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="lux-pag__num" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="lux-pag__btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau ›</a>
            @else
                <span class="lux-pag__btn lux-pag__btn--disabled">Sau ›</span>
            @endif
        </div>
    </nav>
@endif
