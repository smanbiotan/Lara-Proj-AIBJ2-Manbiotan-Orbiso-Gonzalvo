@if($paginator->hasPages())
    <nav class="pagination-wrap" aria-label="Pagination">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="muted">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
            <span class="pager">
                @if($paginator->onFirstPage())<span class="a-btn a-btn-sm" aria-disabled="true" style="opacity:.5">← Previous</span>@else<a class="a-btn a-btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a>@endif
                <span class="muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
                @if($paginator->hasMorePages())<a class="a-btn a-btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>@else<span class="a-btn a-btn-sm" aria-disabled="true" style="opacity:.5">Next →</span>@endif
            </span>
        </div>
    </nav>
@endif
