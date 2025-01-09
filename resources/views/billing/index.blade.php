@extends('layouts.app')

@section('title', 'Billing List')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Billing List</h2>
        <a href="{{ route('billing.create') }}" class="btn btn-primary">Create New Bill</a>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('billing.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search_date" class="form-label">Search by Date</label>
                    <input type="date" class="form-control" id="search_date" name="search_date" 
                        value="{{ request('search_date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    @if(request('search_date'))
                        <a href="{{ route('billing.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($billings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bill ID</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Total Amount</th>
                                <th>Items</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($billings as $bill)
                                <tr>
                                    <td>{{ $bill->id }}</td>
                                    <td>{{ $bill->bill_date }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill->bill_time)->format('H:i') }}</td>
                                    <td>LKR {{ number_format($bill->total_amount, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#items{{ $bill->id }}">
                                            Show Items
                                        </button>
                                    </td>
                                    <td>{{ $bill->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#items{{ $bill->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="p-0">
                                        <div class="collapse" id="items{{ $bill->id }}">
                                            <div class="card card-body m-2">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Quantity</th>
                                                            <th>Unit Price</th>
                                                            <th>Description</th>
                                                            <th>Extra Price</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($bill->billingItems as $item)
                                                            <tr>
                                                                <td>{{ $item->product->product_name }}</td>
                                                                <td>{{ $item->order_quantity }}</td>
                                                                <td>LKR {{ number_format($item->unit_price, 2) }}</td>
                                                                <td>
                                                                    @if($item->description)
                                                                        <span class="text-muted">{{ $item->description }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>LKR {{ number_format($item->extra_price, 2) }}</td>
                                                                <td>LKR {{ number_format($item->total_price, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" class="text-end">
                                                                <strong>Total Amount:</strong>
                                                            </td>
                                                            <td>
                                                                <strong>LKR {{ number_format($bill->total_amount, 2) }}</strong>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    No bills found for the selected criteria.
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table > :not(caption) > * > * {
        padding: 0.5rem;
    }
    .card-header {
        background-color: #f8f9fa;
    }
    .text-muted {
        color: #6c757d !important;
        font-style: italic;
    }
</style>
@endsection