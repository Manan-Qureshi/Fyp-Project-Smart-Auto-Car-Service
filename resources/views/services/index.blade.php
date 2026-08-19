@extends('layouts.frontend')

@section('content')
    <div class="container py-4">
        <div class="text-center mb-5">
            <h5 class="text-primary fw-bold text-uppercase ls-1">Our Services</h5>
            <h2 class="fw-bold">Everything Your Car Needs</h2>
            <p class="text-muted">
                @if(session('selected_car_model'))
                    Showing services for your <strong>{{ session('selected_car_model.type_name') }}
                        {{ session('selected_car_model.name') }}</strong>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#carSelectionModal" class="btn btn-outline-primary btn-sm ms-3 rounded-pill px-3">
                        <i class="fas fa-exchange-alt me-1"></i> Change Car
                    </button>
                @else
                    Choose from our wide range of professional services.
                    <button type="button" data-bs-toggle="modal" data-bs-target="#carSelectionModal" class="btn btn-primary btn-sm ms-2 rounded-pill px-4">
                        Select your car
                    </button>
                    for accurate pricing.
                @endif
            </p>
        </div>

        <div class="row g-4">
            <!-- Services Column -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Available Services</h4>
                    @php $categories = $services->pluck('category')->filter()->unique()->sort(); @endphp
                    @if($categories->count() > 0)
                    <div style="min-width: 200px;">
                        <select class="form-select rounded-pill" id="customerCategoryFilter">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ strtolower(trim($cat)) }}">{{ $cat }}</option>
                            @endforeach
                            <option value="uncategorized">Uncategorized</option>
                        </select>
                    </div>
                    @endif
                </div>

                <div class="row g-4">
                    @forelse($services as $service)
                        <div class="col-md-4 service-card-wrapper" data-category="{{ $service->category ? strtolower(trim($service->category)) : 'uncategorized' }}">
                            <div class="glass-card h-100 text-center hover-up transition-all border-0 shadow-sm bg-white overflow-hidden d-flex flex-column">
                                @if($service->image)
                                    <div style="height: 200px; width: 100%; overflow: hidden;">
                                        <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                    </div>
                                    <div class="p-4 flex-grow-1 d-flex flex-column">
                                @else
                                    <div class="p-4 flex-grow-1 d-flex flex-column">
                                        <div class="icon-box mb-4 mx-auto bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 80px; height: 80px; flex-shrink: 0;">
                                            <i class="fas {{ $service->type == 'custom' ? 'fa-star' : 'fa-wrench' }} fa-2x text-primary"></i>
                                        </div>
                                @endif
                                <h4 class="fw-bold mb-3">{{ $service->name }}</h4>
                                <p class="text-muted mb-4 small flex-grow-1">
                                    {{ Str::limit($service->description ?? 'Professional service for your vehicle.', 80) }}
                                </p>

                                <h3 class="text-primary fw-bold mb-3">
                                    @if(session('selected_car_model'))
                                        PKR {{ number_format($service->base_price * session('selected_car_model.price_modifier', 1)) }}
                                    @else
                                        <div class="fs-6 text-muted fw-normal">Starts from</div>
                                        PKR {{ number_format($service->base_price) }}
                                    @endif
                                </h3>

                                <button type="button" class="btn btn-outline-primary rounded-pill px-4 w-100 add-to-cart-btn mt-auto"
                                    data-service-id="{{ $service->id }}">
                                    <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                </button>
                                </div> <!-- Close .p-4 content wrapper -->
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No services found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Cart Sidebar -->
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 7rem; z-index: 10;">
                    <div class="bg-white p-4 rounded-4 shadow-sm cart-sidebar" style="position: static; max-height: calc(100vh - 120px); overflow-y: auto;">
                        <h5 class="fw-bold mb-4 d-flex justify-content-between align-items-center">
                            Your Cart
                            <span
                                class="badge bg-primary rounded-pill">{{ session('cart') ? count(session('cart')) : 0 }}</span>
                        </h5>

                        @if(session('cart') && count(session('cart')) > 0)
                            <div class="d-flex flex-column gap-3 mb-4">
                                @php $total = 0; @endphp
                                @foreach(session('cart') as $id => $item)
                                    @php $total += $item['price']; @endphp
                                    <div class="d-flex justify-content-between align-items-start border-bottom pb-3">
                                        <div>
                                            <h6 class="mb-1 text-dark fw-bold">{{ $item['name'] }}</h6>
                                            <small class="text-primary fw-bold">PKR {{ number_format($item['price']) }}</small>
                                        </div>
                                        <form action="{{ route('cart.remove', $id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger border-0 p-0"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-top pt-3 mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="fw-bold">${{ number_format($total) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total</span>
                                    <span class="fw-bold fs-5 text-primary">${{ number_format($total) }}</span>
                                </div>
                            </div>

                            <a href="{{ route('bookings.create') }}" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                                Checkout <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-shopping-cart fa-3x mb-3 opacity-25"></i>
                                <p>Your cart is currently empty.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Custom Request Shortcut Button -->
                    <a href="#custom-request-form" class="btn btn-outline-primary w-100 rounded-pill py-3 mt-4 shadow-sm fw-bold d-flex align-items-center justify-content-center" style="border-width: 2px;">
                        <i class="fas fa-hammer me-2"></i> Custom Request Form
                    </a>
                </div>
            </div>
        </div>
        <!-- Custom Service Request Form -->
        <div id="custom-request-form" class="row justify-content-center mt-5 pt-4">
            <div class="col-lg-8">
                <div class="glass-card p-5 border-0 shadow-lg">
                    <div class="text-center mb-4">
                        <i class="fas fa-hammer fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Need Something Custom?</h3>
                        <p class="text-muted">Describe your issue, and we'll propose a solution.</p>
                    </div>

                    <form action="{{ route('contact.store') }}" method="POST">
                        @csrf
                        @guest
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                        @endguest

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control"
                                placeholder="e.g., Engine Noise, Modification Request" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="message" class="form-control" rows="4"
                                placeholder="Please describe the service you need..." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill btn-lg shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Send Custom Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Car Selection Modal -->
    <div class="modal fade" id="carSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary">Select Your Vehicle</h5>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Please select your car company and model to view available services.</p>
                    <form action="{{ route('select-car') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Car Company / Type</label>
                            <select class="form-select form-select-lg" id="carTypeSelect" required>
                                <option value="">Choose Company...</option>
                                @foreach($allCarTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Car Model</label>
                            <select class="form-select form-select-lg" name="car_model_id" id="carModelSelect" disabled
                                required>
                                <option value="">Select Model</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill" id="confirmCarBtn">Show
                                Services</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var carModal = new bootstrap.Modal(document.getElementById('carSelectionModal'), {
                backdrop: 'static',
                keyboard: false
            });

            // Auto-open modal if no car selected
            @if(!session('selected_car_model'))
                carModal.show();
            @endif

            // Allow manual trigger
            // Check if triggers exist
            const triggers = document.querySelectorAll('[data-bs-target="#carSelectionModal"]');
            triggers.forEach(t => t.addEventListener('click', () => carModal.show()));


            // Dynamic Car Model Loading
            const carTypeSelect = document.getElementById('carTypeSelect');
            const carModelSelect = document.getElementById('carModelSelect');

            const categoryFilter = document.getElementById('customerCategoryFilter');
            if(categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    const selected = this.value;
                    document.querySelectorAll('.service-card-wrapper').forEach(card => {
                        card.style.display = (selected === 'all' || card.dataset.category === selected) ? '' : 'none';
                    });
                });
            }

            if (carTypeSelect) {
                carTypeSelect.addEventListener('change', function () {
                    const typeId = this.value;
                    carModelSelect.innerHTML = '<option value="">Loading...</option>';
                    carModelSelect.disabled = true;

                    fetch(`/api/car-models?car_type_id=${typeId}`)
                        .then(response => response.json())
                        .then(data => {
                            carModelSelect.innerHTML = '<option value="">Select Model</option>';
                            data.forEach(model => {
                                carModelSelect.innerHTML += `<option value="${model.id}">${model.name}</option>`;
                            });
                            carModelSelect.disabled = false;
                        });
                });
            }

            // Add to Cart Logic
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Visual feedback
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Adding...';
                    this.disabled = true;

                    const serviceId = this.dataset.serviceId;

                    fetch('{{ route("cart.add") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ service_id: serviceId })
                    })
                        .then(response => {
                            // Handle explicit 401 JSON/Redirect
                            if (response.status === 401) {
                                // Reset button immediately
                                this.innerHTML = originalText;
                                this.disabled = false;
                                
                                window.location.href = '{{ route("login") }}';
                                return null;
                            }
                            // Handle opaque redirects (e.g., standard auth middleware returning login page HTML)
                            if (response.redirected && response.url.includes('login')) {
                                this.innerHTML = originalText;
                                this.disabled = false;
                                
                                window.location.href = response.url;
                                return null;
                            }

                            if (!response.ok && response.status !== 422) {
                                throw new Error('Network response was not ok: ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Reset button
                            this.innerHTML = originalText;
                            this.disabled = false;

                            if (data.success) {
                                location.reload();
                            } else if (data.error) {
                                if (data.error.includes('select a car')) {
                                    carModal.show();
                                } else {
                                    alert(data.error);
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            // Reset button on error
                            this.innerHTML = originalText;
                            this.disabled = false;
                        });
                });
            });
        });
    </script>
@endsection