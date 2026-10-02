@extends('layouts.app')

@section('content')
    <div class="container fade-in-up">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="glass-card p-4 bg-white">
                    <h4 class="mb-4 text-primary fw-bold">Edit Service</h4>

                    <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label text-dark">Service Name</label>
                            <input type="text" name="name" value="{{ $service->name }}" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="standard" {{ $service->type == 'standard' ? 'selected' : '' }}>Standard
                                </option>
                                <option value="custom" {{ $service->type == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Time Slot Category</label>
                            <select name="duration_minutes" class="form-select" required>
                                <option value="45" {{ $service->duration_minutes == 45 ? 'selected' : '' }}>Short (45 min gap)
                                </option>
                                <option value="90" {{ $service->duration_minutes == 90 ? 'selected' : '' }}>Medium (1 hr 30
                                    min gap)</option>
                                <option value="135" {{ $service->duration_minutes == 135 ? 'selected' : '' }}>Large (2 hr 15
                                    min gap)</option>
                                <option value="210" {{ $service->duration_minutes == 210 ? 'selected' : '' }}>XL (3 hr 30 min
                                    gap)</option>
                                <option value="300" {{ $service->duration_minutes == 300 ? 'selected' : '' }}>XXL (5 hr gap)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Base Price (PKR)</label>
                            <input type="number" step="0.01" name="base_price" value="{{ $service->base_price }}"
                                class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-dark">Description</label>
                            <textarea name="description" class="form-control"
                                rows="3">{{ $service->description }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-dark">Service Image</label>
                            @if($service->image)
                                <div class="mb-2">
                                    <img src="{{ \Illuminate\Support\Str::startsWith($service->image, ['http://', 'https://']) ? $service->image : asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload a new image to replace the current one.</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.services.index') }}" class="btn btn-light text-muted">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">Update Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection