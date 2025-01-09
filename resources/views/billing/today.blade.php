@extends('layouts.app')

@section('title', 'Today\'s Bills')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5>Total Bills Today</h5>
                                <h3 class="text-primary">{{ $totalBills }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5>Today's Date</h5>
                                <h3>{{ now()->format('Y-m-d') }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5>Total Revenue</h5>
                                <h3 class="text-success">LKR {{ number_format($totalRevenue, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Today's Bills</h4>
        </div>
        <div class="card-body">
            @if($todayBills->count() > 0)
                @foreach($todayBills as $bill)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col">Bill #{{ $bill->id }}</div>
                                <div class="col">Time: {{ \Carbon\Carbon::parse($bill->bill_time)->format('H:i') }}</div>
                                <div class="col text-end">
                                    Total: LKR {{ number_format($bill->total_amount, 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
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
                @endforeach
            @else
                <div class="alert alert-info">
                    No bills have been created today.
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