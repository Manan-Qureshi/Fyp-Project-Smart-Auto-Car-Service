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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Available Services</h4>
                    @php $categories = $services->pluck('category')->filter()->unique()->sort(); @endphp
                    @if($categories->count() > 0)
                    <div style="min-width: 200px;">
                        <select class="form-select rounded-pill" id="customerCategoryFilter">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ strtolower(trim($cat)) }}">{{ $cat }}</option>
                            @endforeach
                            <option value="general services">General Services</option>
                        </select>
                    </div>
                    @endif
                </div>

                @php
                    $groupedServices = $services->groupBy(function($s) {
                        return $s->category ? trim($s->category) : 'General Services';
                    });
                @endphp

                @forelse($groupedServices as $categoryName => $catServices)
                    @php $slug = Str::slug($categoryName); @endphp
                    <div class="category-block mb-5" data-category="{{ strtolower(trim($categoryName)) }}">
                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-wrench text-primary me-2"></i>{{ $categoryName }}</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary ms-2 rounded-pill px-3 py-1">{{ $catServices->count() }} {{ Str::plural('service', $catServices->count()) }}</span>
                        </div>

                        <div class="row g-4">
                            @foreach($catServices as $index => $service)
                                <div class="col-md-4 service-card-wrapper {{ $index >= 3 ? 'd-none cat-extra-' . $slug : '' }}" data-category="{{ strtolower(trim($categoryName)) }}">
                                    <div class="glass-card h-100 text-center hover-up transition-all border-0 shadow-sm bg-white overflow-hidden d-flex flex-column">
                                        @if($service->image)
                                            <div style="height: 180px; width: 100%; overflow: hidden;">
                                                <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                            </div>
                                            <div class="p-4 flex-grow-1 d-flex flex-column">
                                        @else
                                            <div class="p-4 flex-grow-1 d-flex flex-column">
                                                <div class="icon-box mb-3 mx-auto bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 70px; height: 70px; flex-shrink: 0;">
                                                    <i class="fas {{ $service->type == 'custom' ? 'fa-star' : 'fa-wrench' }} fa-2x text-primary"></i>
                                                </div>
                                        @endif
                                        <h5 class="fw-bold mb-2">{{ $service->name }}</h5>
                                        <p class="text-muted mb-3 small flex-grow-1">
                                            {{ Str::limit($service->description ?? 'Professional service for your vehicle.', 75) }}
                                        </p>

                                        <h4 class="text-primary fw-bold mb-3">
                                            @if(session('selected_car_model'))
                                                PKR {{ number_format($service->base_price * session('selected_car_model.price_modifier', 1)) }}
                                            @else
                                                <div class="fs-6 text-muted fw-normal">Starts from</div>
                                                PKR {{ number_format($service->base_price) }}
                                            @endif
                                        </h4>

                                        <button type="button" class="btn btn-outline-primary rounded-pill px-4 w-100 add-to-cart-btn mt-auto"
                                            data-service-id="{{ $service->id }}">
                                            <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                        </button>
                                        </div> <!-- Close content wrapper -->
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($catServices->count() > 3)
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-4 py-2 toggle-cat-btn" data-target="cat-extra-{{ $slug }}">
                                    <span class="btn-text">Show All {{ $catServices->count() }} Services</span>
                                    <i class="fas fa-chevron-down ms-1 toggle-icon"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No services found.</p>
                    </div>
                @endforelse
            </div>

            <!-- Cart Sidebar -->
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 7rem; z-index: 10;">
                    <div class="bg-white p-4 rounded-4 shadow-sm cart-sidebar" style="max-height: calc(100vh - 120px); overflow-y: auto;">
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
                                    <span class="fw-bold">PKR {{ number_format($total) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total</span>
                                    <span class="fw-bold fs-5 text-primary">PKR {{ number_format($total) }}</span>
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
                    document.querySelectorAll('.category-block').forEach(block => {
                        const blockCat = block.dataset.category;
                        block.style.display = (selected === 'all' || blockCat === selected) ? '' : 'none';
                    });
                });
            }

            // Expand / Collapse Extra Services per Category
            document.querySelectorAll('.toggle-cat-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetClass = this.dataset.target;
                    const extraCards = document.querySelectorAll('.' + targetClass);
                    const icon = this.querySelector('.toggle-icon');
                    const textSpan = this.querySelector('.btn-text');

                    const isHidden = extraCards[0] && extraCards[0].classList.contains('d-none');

                    extraCards.forEach(card => {
                        if (isHidden) {
                            card.classList.remove('d-none');
                        } else {
                            card.classList.add('d-none');
                        }
                    });

                    if (isHidden) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                        if (textSpan) textSpan.textContent = 'Show Less';
                    } else {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                        const totalCount = extraCards.length + 3;
                        if (textSpan) textSpan.textContent = `Show All ${totalCount} Services`;
                    }
                });
            });

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