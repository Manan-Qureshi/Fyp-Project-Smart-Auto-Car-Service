@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:600px">
    <div class="glass-card p-4 rounded-4 shadow">
        <h4 class="fw-bold mb-4"><i class="fas fa-user-plus text-primary me-2"></i>Add Worker</h4>

        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('provider.workers.store') }}" method="POST">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                       required value="{{ old('name') }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- CNIC --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">CNIC <span class="text-danger">*</span></label>
                <input type="text" name="cnic" class="form-control rounded-3 @error('cnic') is-invalid @enderror"
                       placeholder="e.g. 3520212345671" maxlength="15" required value="{{ old('cnic') }}">
                @error('cnic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">National ID number (up to 15 digits).</div>
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Address</label>
                <textarea name="address" class="form-control rounded-3 @error('address') is-invalid @enderror"
                          rows="2">{{ old('address') }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control rounded-3 @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                       required value="{{ old('email') }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Experience --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Experience (years)</label>
                <input type="number" name="experience_years" class="form-control rounded-3 @error('experience_years') is-invalid @enderror"
                       min="0" value="{{ old('experience_years', 0) }}">
                @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password"
                           class="form-control rounded-3 @error('password') is-invalid @enderror"
                           placeholder="Min 6 characters" required autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="form-control rounded-3" required autocomplete="new-password">
                </div>
            </div>



            <div class="d-flex gap-2">
                <a href="{{ route('provider.workers.index') }}" class="btn btn-outline-secondary rounded-pill flex-grow-1">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">Add Worker</button>
            </div>
        </form>
    </div>
</div>
@endsection
