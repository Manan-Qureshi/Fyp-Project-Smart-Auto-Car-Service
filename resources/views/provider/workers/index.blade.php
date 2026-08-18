@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="fas fa-hard-hat text-warning me-2"></i>Workers</h3>
        <a href="{{ route('provider.workers.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add Worker
        </a>
    </div>



    @if($workers->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i><h5 class="text-muted">No workers added yet.</h5>
        </div>
    @else
    <div class="glass-card p-4 rounded-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 border-0">Worker</th>
                        <th class="border-0">Phone</th>
                        <th class="border-0">CNIC</th>
                        <th class="border-0">Experience</th>
                        <th class="border-0">Address</th>
                        <th class="text-end pe-3 border-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($workers as $w)
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width:40px;height:40px;font-size:1.1rem">
                                {{ strtoupper(substr($w->name,0,1)) }}
                            </div>
                            <span class="fw-bold">{{ $w->name }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">
                        @if($w->phone)<i class="fas fa-phone me-1 text-primary"></i>{{ $w->phone }}@else—@endif
                    </td>
                    <td class="text-muted small">
                        @if($w->cnic)<i class="fas fa-id-card me-1 text-primary"></i>{{ $w->cnic }}@else—@endif
                    </td>
                    <td class="text-muted small">
                        @if($w->experience_years)
                            <i class="fas fa-briefcase me-1 text-primary"></i>{{ $w->experience_years }} yr{{ $w->experience_years != 1 ? 's' : '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-muted small">
                        @if($w->address)
                            <div class="text-truncate" style="max-width:200px" title="{{ $w->address }}">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i>{{ $w->address }}
                            </div>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-2 justify-content-end align-items-center">
                            <a href="{{ route('provider.workers.edit', $w) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('provider.workers.destroy', $w) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
