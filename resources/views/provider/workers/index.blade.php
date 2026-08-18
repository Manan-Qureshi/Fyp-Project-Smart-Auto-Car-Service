@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="fas fa-hard-hat text-warning me-2"></i>Workers</h3>
        <a href="{{ route('provider.workers.create') }}" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-1"></i> Add Worker
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($workers->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i><h5 class="text-muted">No workers added yet.</h5>
        </div>
    @else
    <div class="row g-3">
        @foreach($workers as $w)
        <div class="col-md-4 col-lg-3">
            <div class="glass-card p-3 rounded-4 text-center">
                <div class="rounded-circle bg-primary mx-auto d-flex align-items-center justify-content-center text-white fw-bold mb-2"
                     style="width:55px;height:55px;font-size:1.3rem">{{ strtoupper(substr($w->name,0,1)) }}</div>
                <div class="fw-bold">{{ $w->name }}</div>
                @if($w->phone)<div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $w->phone }}</div>@endif
                @if($w->cnic)<div class="text-muted small"><i class="fas fa-id-card me-1"></i>{{ $w->cnic }}</div>@endif
                @if($w->address)<div class="text-muted small text-truncate" title="{{ $w->address }}"><i class="fas fa-map-marker-alt me-1"></i>{{ $w->address }}</div>@endif
                @if($w->experience_years)
                    <div class="text-muted small"><i class="fas fa-briefcase me-1"></i>{{ $w->experience_years }} yr{{ $w->experience_years != 1 ? 's' : '' }} exp.</div>
                @endif

                <div class="mt-2 d-flex gap-2 justify-content-center">
                    <a href="{{ route('provider.workers.edit', $w) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                    <form action="{{ route('provider.workers.destroy', $w) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger rounded-pill">Remove</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
