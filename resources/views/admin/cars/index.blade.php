@extends('layouts.app')

@section('content')
    <div class="container fade-in-up">
        <h2 class="text-primary fw-bold mb-4">Car Management</h2>

        <div class="row mb-4">
            <!-- Add Car Company -->
            <div class="col-md-5 mb-3 mb-md-0">
                <div class="glass-card p-4 h-100">
                    <h4 class="fw-bold mb-3">Add Car Company (Type)</h4>
                    <form action="{{ route('admin.cars.storeType') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Toyota" required>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill w-100">Add Company</button>
                    </form>
                </div>
            </div>

            <!-- Add Car Model -->
            <div class="col-md-7">
                <div class="glass-card p-4 h-100">
                    <h4 class="fw-bold mb-3">Add Car Model</h4>
                    <form action="{{ route('admin.cars.storeModel') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Company</label>
                                <select name="car_type_id" class="form-select" required>
                                    <option value="">Choose...</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Model Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Corolla" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Price Modifier (Multiplier)</label>
                                <input type="number" step="0.01" name="price_modifier" class="form-control" value="1.0"
                                    required>
                                <small class="text-muted">1.0 = Standard Price, 1.2 = +20%, etc.</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill w-100">Add Model</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="glass-card p-4">
            <h4 class="fw-bold mb-4">Existing Cars</h4>
            @foreach($types as $type)
                <div class="mb-4 border-bottom pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="m-0 text-primary fw-bold">
                            {{ $type->name }}
                        </h5>
                        <form action="{{ route('admin.cars.destroyType', $type) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete Company</button>
                        </form>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach($type->models as $model)
                            <span class="badge bg-light text-dark border p-2 d-flex align-items-center">
                                <div>
                                    {{ $model->name }}
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">x{{ $model->price_modifier }}</small>
                                </div>
                                <form action="{{ route('admin.cars.destroyModel', $model) }}" method="POST" class="ms-2">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link p-0 text-danger" style="font-size: 0.8rem;"><i
                                            class="fas fa-times"></i></button>
                                </form>
                            </span>
                        @endforeach
                        @if($type->models->isEmpty())
                            <span class="text-muted small fst-italic">No models added yet.</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>

</script>
@endpush