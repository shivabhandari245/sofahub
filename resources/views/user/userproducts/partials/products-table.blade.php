<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Quality</th>
                <th>Quantity</th>
                <th>Cost</th>
                <th>Total Cost</th>
                <th>ShowRoom</th>
                <th>Source</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($products as $product)
            @php
            $status = 'Available';
            if ($product->quantity == 0) $status = 'Out of Stock';
            elseif ($product->quantity <= 5) $status='Low Stock' ; @endphp <tr>
                <td>{{ $product->name ?? '-' }}</td>
                <td>{{ $product->category ?? '-' }}</td>
                <td>{{ $product->quality ?? '-' }}</td>
                <td>{{ $product->quantity }}</td>
                <td>${{ number_format($product->cost_per_product,2) }}</td>
                <td>${{ number_format($product->total_cost,2) }}</td>
                <td>{{ Auth::user()->name }}</td>
                <td>{{ $product->source }}</td>
                <td>
                    <span class="badge
                        @if($status=='Available') bg-success
                        @elseif($status=='Low Stock') bg-warning
                        @else bg-danger @endif">
                        {{ $status }}
                    </span>
                </td>
                <td>
                    @if($product->quantity > 0)
                    <a href="{{ route('sales.index') }}?product_id={{ $product->id }}" class="btn btn-sm btn-success">
                        Sale
                    </a>
                    @endif

                    <button class="btn btn-sm btn-danger delete-product" data-id="{{ $product->id }}">
                        Delete
                    </button>
                </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">No products found.</td>
                </tr>
                @endforelse
        </tbody>
        <tfoot>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $products->count() }} of {{ $products->total() }} products
                </div>

                <div>
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </tfoot>
    </table> @if ($products->lastPage() >= 1)
    <div class="pagination-container">



        {{-- Page 1 --}}
        <span class="page-btn btn-primary">1</span>



    </div>
    @endif


</div>