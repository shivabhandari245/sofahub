@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Sale #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h1>
            <div class="btn-group">
                <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-print"></i> Print Invoice
                </a>
                <a href="{{ route('user.invoices.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Sales
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- SALE ITEMS --}}
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sale Items</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                                <th>Profit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->subtotal, 2) }}</td>
                                <td class="{{ $item->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($item->profit, 2) }}
                                </td>
                                <td>{{ ucfirst($item->status ?? 'sold') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SALE INFO --}}
        <div class="col-md-4">
            <!-- General Sale Info -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sale Information</h5>
                

                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $sale->date?->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Cashier:</th>
                            <td>{{ $sale->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                        </tr>
                        <tr>
                            <th>Sale Status:</th>
                            <td>
                                <span class="badge {{ $sale->returned ? 'bg-danger' : 'bg-success' }}">
                                    {{ $sale->returned ? 'Returned' : 'Completed' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                <span class="badge 
                                    {{ $sale->payment_status === 'paid' ? 'bg-success' :
                                       ($sale->payment_status === 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="openModal()">
                                    Change
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- PAYMENT SUMMARY --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profit Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-end">{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td class="text-end">-{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>After Discount:</th>
                            <td class="text-end">{{ number_format($sale->afterdiscount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Tax ({{ $sale->tax_rate }}%):</th>
                            <td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold table-active">
                            <th>Total:</th>
                            <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Profit:</th>
                            <td class="text-end {{ $sale->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($sale->profit, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                        <td class="text-end">
                            {{ !empty($sale->payment_method) ? ucfirst(implode(', ', $sale->payment_method)) : 'N/A' }}
                        </td>                        </tr>
                        @if($sale->payment_remarks)
                        <tr>
                            <th>Remarks:</th>
                            <td class="text-end">{{ $sale->payment_remarks }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- PAYMENT STATUS MODAL --}}
<div id="paymentStatusModal" class="custom-modal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5>Update Payment Status</h5>
            <button class="close-btn" onclick="closeModal()">×</button>
        </div>

        <form method="POST" action="{{ url('user/invoices/paymentstatus', $sale->id) }}">
            @csrf
            @method('PATCH')
            <div class="custom-modal-body">
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="paid" {{ $sale->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ $sale->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="unpaid" {{ $sale->payment_status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>

                    <div class="form-group">
                        <label class="mb-2">Payment Method</label>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input payment-check"
                                        type="checkbox"
                                        name="payment_method[]"     
                                        value="cash"
                                        id="payCash"
                                        {{ in_array('cash', $sale->payment_method ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payCash">💵 Cash</label>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input payment-check"
                                        type="checkbox"
                                        name="payment_method[]"
                                        value="qr"
                                        id="payQR"
                                        {{ in_array('qr', $sale->payment_method ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payQR">📱 QR</label>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input payment-check"
                                        type="checkbox"
                                        name="payment_method[]"
                                        value="cheque"
                                        id="payCheque"
                                        {{ in_array('cheque', $sale->payment_method ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payCheque">🧾 Cheque</label>
                                </div>
                            </div>
                        </div>
                    </div>


                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="payment_remarks" class="form-control" rows="2">{{ $sale->payment_remarks }}</textarea>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>
<script>
    function openModal() {
    document.getElementById('paymentStatusModal').style.display = 'flex';
 }
 function closeModal() {
    document.getElementById('paymentStatusModal').style.display = 'none';
 }

 
</script>

<style>
   /* Full-screen overlay */
 .custom-modal {
    display: none; /* hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000; /* above sidebar/top-nav */
    justify-content: center;
    align-items: center;
 }

 /* Modal box */
 .custom-modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    position: relative;
 }

 /* Modal header */
 .custom-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
 }

 .custom-modal-header h5 {
    margin: 0;
 }

 /* Close button */
 .close-btn {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
 }

 /* Body & footer */
 .custom-modal-body {
    margin-bottom: 16px;
 }

 .custom-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
 }

 /* Form styling */
 .form-group {
    margin-bottom: 12px;
 }

 .form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
 }

 .form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 0.95rem;
 }
 
</style>
@endsection


