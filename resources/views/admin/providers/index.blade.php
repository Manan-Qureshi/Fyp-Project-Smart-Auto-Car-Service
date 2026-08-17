@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="fas fa-store text-primary me-2"></i>Service Providers</h3>
        <a href="{{ route('admin.providers.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add Provider
        </a>
    </div>
    <div class="table-responsive glass-card p-4 rounded-4 shadow">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Business</th><th>Owner</th><th>Address</th><th>Lat / Lng</th><th>Workers</th><th>Bookings</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            @forelse($providers as $p)
            <tr>
                <td class="fw-semibold">{{ $p->business_name }}</td>
                <td>{{ optional($p->owner)->email }}</td>
                <td class="small text-muted">{{ $p->address }}</td>
                <td class="small text-muted">{{ $p->latitude }}, {{ $p->longitude }}</td>
                <td>{{ $p->workers_count }}</td>
                <td>{{ $p->bookings_count }}</td>
                <td><span class="badge {{ $p->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    <a href="{{ route('admin.providers.edit', $p) }}" class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
                    <form action="{{ route('admin.providers.destroy', $p) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE') <button class="btn btn-sm btn-outline-danger rounded-pill">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No providers yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $providers->links() }}
    </div>
</div>
@endsection
