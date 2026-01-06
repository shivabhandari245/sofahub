<div class="btn-group btn-group-sm">
    <a href="{{ route('user.sales.show', $sale->id) }}" class="btn btn-info" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-secondary" target="_blank" title="Print">
        <i class="fas fa-print"></i>
    </a>
</div>
