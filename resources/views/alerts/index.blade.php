@extends('layouts.app')

@section('title', 'Inventory Alerts')

@section('content')
<h2>Inventory Alerts</h2>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Inventory</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alerts as $alert)
                    <tr class="{{ $alert->is_read ? 'table-light' : 'table-warning' }}">
                        <td>{{ $alert->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ ucfirst($alert->alert_type) }}</td>
                        <td>{{ $alert->inventory->inventory_name }}</td>
                        <td>{{ $alert->alert_message }}</td>
                        <td>
                            @if(!$alert->is_read)
                                <form action="{{ route('alerts.mark-read', $alert) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Mark as Read</button>
                                </form>
                            @else
                                <span class="badge bg-secondary">Read</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection