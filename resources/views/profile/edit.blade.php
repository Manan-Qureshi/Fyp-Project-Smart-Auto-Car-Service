@extends('layouts.app')

@section('content')
<div class="container fade-in-up">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-4 bg-white">
                <h4 class="mb-4 text-primary fw-bold">My Profile</h4>



                @if(!$user->phone_number || !$user->address)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Please complete your profile to book services.
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                         <div class="col-md-12 text-center mb-3">
                             @if($user->avatar)
                                <img src="{{ $user->avatar }}" class="rounded-circle shadow" width="100" height="100">
                             @else
                                <img src="https://ui-avatars.com/api/?name={{ $user->name }}&background=667eea&color=fff" class="rounded-circle shadow" width="100" height="100">
                             @endif
                         </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Required for booking">
                        @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-dark">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Required for service delivery">{{ old('address', $user->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    <h5 class="mb-3 text-secondary">Security</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark">New Password (Optional)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <small class="text-muted mt-2">Leave blank if you don't want to change your password.</small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
