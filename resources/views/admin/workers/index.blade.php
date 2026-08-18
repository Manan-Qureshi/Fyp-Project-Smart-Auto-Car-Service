@extends('layouts.app')

@section('content')
<div class="container fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary fw-bold">Worker Management</h2>
        <a href="{{ route('admin.workers.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i> Add Worker
        </a>
    </div>



    <div class="glass-card">
        <table class="table text-dark mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0">#</th>
                    <th class="border-0">Name</th>
                    <th class="border-0">Email</th>
                    <th class="border-0">Joined</th>
                    <th class="border-0 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workers as $worker)
                <tr>
                    <td class="align-middle text-muted">{{ $loop->iteration }}</td>
                    <td class="align-middle fw-bold">{{ $worker->name }}</td>
                    <td class="align-middle">{{ $worker->email }}</td>
                    <td class="align-middle text-muted">{{ $worker->created_at->format('M d, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.workers.edit', $worker) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.workers.destroy', $worker) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
