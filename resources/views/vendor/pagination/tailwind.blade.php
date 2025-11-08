@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between pagination-nav">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn ms-2">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="pagination-btn disabled ms-2">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div class="pagination-summary">
                <p>
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span>{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span>{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span>{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div class="pagination-controls">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="pagination-icon disabled" aria-hidden="true">
                        &lsaquo;
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-icon" aria-label="{{ __('pagination.previous') }}">
                        &lsaquo;
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-gap">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-page is-active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-page" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-icon" aria-label="{{ __('pagination.next') }}">
                        &rsaquo;
                    </a>
                @else
                    <span class="pagination-icon disabled" aria-hidden="true">
                        &rsaquo;
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
