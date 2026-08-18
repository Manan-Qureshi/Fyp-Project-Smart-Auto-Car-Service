@extends('layouts.frontend')

@section('content')
    <div class="container py-4">
        {{-- Provider Header --}}
        <div class="glass-card p-4 rounded-4 mb-4 d-flex align-items-center gap-4 flex-wrap">
            <div class="flex-shrink-0">
                @if($provider->logo)
                    <img src="{{ asset('storage/' . $provider->logo) }}" class="rounded-circle border border-3 border-primary"
                        width="90" height="90" style="object-fit:cover">
                @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                        style="width:90px;height:90px;font-size:2rem">
                        {{ strtoupper(substr($provider->business_name, 0, 2)) }}
                    </div>
                @endif
            </div>
            <div class="flex-grow-1">
                <h2 class="fw-bold mb-1">{{ $provider->business_name }}</h2>
                <p class="text-muted mb-1"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $provider->address }}
                </p>
                @if($provider->phone)
                    <p class="text-muted mb-1"><i class="fas fa-phone text-success me-1"></i>{{ $provider->phone }}</p>
                @endif
                <div class="mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($provider->avg_rating) ? 'text-warning' : 'text-secondary' }}"></i>
                    @endfor
                    <span class="ms-1 text-muted small">{{ $provider->avg_rating }}/5 · {{ $provider->rating_count }}
                        reviews</span>
                </div>
            </div>
            @if($provider->description)
                <div class="w-100">
                    <p class="text-muted mb-0">{{ $provider->description }}</p>
                </div>
            @endif
        </div>

        {{-- Car Selection Reminder --}}
        @if(!$selectedCar)
            <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3">
                <i class="fas fa-car-side fa-lg"></i>
                <span>Select your car to see personalized pricing.</span>
                <button type="button" data-bs-toggle="modal" data-bs-target="#carModal"
                    class="btn btn-sm btn-warning ms-auto">Select Car</button>
            </div>
        @else
            <div class="alert alert-info d-flex align-items-center gap-2 rounded-3">
                <i class="fas fa-car fa-lg"></i>
                <span>Prices shown for <strong>{{ $selectedCar['type_name'] }} — {{ $selectedCar['name'] }}</strong></span>
                <button type="button" data-bs-toggle="modal" data-bs-target="#carModal"
                    class="ms-auto btn btn-sm btn-outline-primary">Change Car</button>
            </div>
        @endif

        {{-- Layout Split: Services on left, Cart on right --}}
        <div class="row g-4">
            {{-- Left Column: Services --}}
            <div class="col-lg-8">
                {{-- Services by Category --}}
                @if($servicesByCategory->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">This provider has not listed any services yet.</h4>
                    </div>
                @else
                    @foreach($servicesByCategory as $category => $psList)
                        <h5 class="fw-semibold text-uppercase text-muted mb-3 mt-4">
                            <i class="fas fa-tag me-2"></i>{{ $category }}
                        </h5>
                        <div class="row g-3 mb-4">
                            @foreach($psList as $ps)
                                @php
                                    $service = $ps->service;
                                @endphp
                                @if(!$service) @continue @endif
                                @php
                                    $modifier = $carModel ? $carModel->price_modifier : 1;
                                    $price = round($service->base_price * $modifier, 2);
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border-0 shadow-sm rounded-4 h-100">
                                        @if($service->image)
                                            <img src="{{ asset('storage/' . $service->image) }}" class="card-img-top rounded-top-4"
                                                style="height:140px;object-fit:cover">
                                        @endif
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-1">{{ $service->name }}</h6>
                                            @if($service->description)
                                                <p class="text-muted small mb-2">{{ Str::limit($service->description, 80) }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                                <div>
                                                    <span class="fs-5 fw-bold text-primary">PKR {{ number_format($price) }}</span>
                                                    <span class="text-muted small ms-1">· {{ $service->duration_minutes }} min</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                                            @auth
                                                <button class="btn btn-outline-primary w-100 rounded-pill btn-sm btn-add-cart"
                                                    data-id="{{ $service->id }}">
                                                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 rounded-pill btn-sm">
                                                    <i class="fas fa-sign-in-alt me-1"></i> Login to Add
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div> {{-- End col-lg-8 --}}

            {{-- Right Column: Shopping Cart --}}
            <div class="col-lg-4">
                <div class="glass-card p-4 rounded-4 shadow-sm position-sticky" style="top:2rem">
                    <h5 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-shopping-cart text-primary me-2"></i>Cart</span>
                        <span class="badge bg-primary rounded-pill" id="cartItemCount">0</span>
                    </h5>

                    <div id="cartItemsList" class="mb-3 d-flex flex-column gap-2"
                        style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center text-muted py-3 small empty-cart-msg">
                            <i class="fas fa-cart-arrow-down fa-2x mb-2 opacity-50"></i><br>Your cart is empty.
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between text-muted fw-semibold small mb-1">
                        <span>Total Duration:</span>
                        <span id="cartTotalDuration">0 min</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 text-primary mb-3">
                        <span>Total:</span>
                        <span id="cartTotalPrice">PKR 0</span>
                    </div>

                    <a href="{{ route('bookings.create', $provider) }}" id="checkoutBtn"
                        class="btn btn-primary w-100 rounded-pill disabled" style="display:none">
                        <i class="fas fa-calendar-check me-2"></i>Proceed to Checkout
                    </a>
                    <button id="emptyCheckoutBtn" class="btn btn-secondary w-100 rounded-pill disabled">
                        Cart empty
                    </button>
                </div>
            </div> {{-- End col-lg-4 --}}
        </div> {{-- End row --}}

        {{-- Reviews --}}
        @if($provider->ratings->count() > 0)
            <h5 class="fw-semibold mt-5 mb-3"><i class="fas fa-comments text-warning me-2"></i>Customer Reviews</h5>
            <div class="row g-3">
                @foreach($provider->ratings->take(6) as $rating)
                    <div class="col-md-6">
                        <div class="glass-card p-3 rounded-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($rating->customer->name ?? 'User') }}&background=667eea&color=fff"
                                    class="rounded-circle" width="32" height="32">
                                <div>
                                    <div class="fw-semibold small">{{ $rating->customer->name ?? 'Customer' }}</div>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $rating->rating ? 'text-warning' : 'text-secondary' }}"
                                                style="font-size:.75rem"></i>
                                        @endfor
                                    </div>
                                </div>
                                <span class="ms-auto text-muted small">{{ $rating->created_at->diffForHumans() }}</span>
                            </div>
                            @if($rating->review)
                                <p class="small text-muted mb-0">{{ $rating->review }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>

    {{-- Car Selection Modal --}}
    @php $allCarTypes = \App\Models\CarType::with('models')->get(); @endphp
    <div class="modal fade" id="carModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-car-side me-2" style="color:#1a56db;"></i>Select Your
                        Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Prices are calculated based on your car model.</p>
                    <form action="{{ route('select-car') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Car Type</label>
                            <select id="carTypeSelect" class="form-select rounded-3">
                                <option value="">Choose type...</option>
                                @foreach($allCarTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Car Model</label>
                            <select name="car_model_id" id="carModelSelect" class="form-select rounded-3" required>
                                <option value="">Select type first...</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                            <i class="fas fa-check me-2"></i>Confirm Selection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── Car Modal Logic ──
            @if(!$selectedCar)
                document.addEventListener('DOMContentLoaded', function () {
                    var carModal = new bootstrap.Modal(document.getElementById('carModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    carModal.show();
                });
            @endif

            const carTypeSelect = document.getElementById('carTypeSelect');
            if (carTypeSelect) {
                carTypeSelect.addEventListener('change', function () {
                    const typeId = this.value;
                    const modelSelect = document.getElementById('carModelSelect');
                    modelSelect.innerHTML = '<option>Loading...</option>';
                    if (!typeId) { modelSelect.innerHTML = '<option value="">Select type first...</option>'; return; }
                    fetch('/api/car-models?car_type_id=' + typeId)
                        .then(r => r.json())
                        .then(models => {
                            modelSelect.innerHTML = '<option value="">Choose model...</option>';
                            models.forEach(m => modelSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`);
                        });
                });
            }

            const providerId = {{ $provider->id }};
            const cartItemsList = document.getElementById('cartItemsList');
            const checkoutBtn = document.getElementById('checkoutBtn');
            const emptyCheckoutBtn = document.getElementById('emptyCheckoutBtn');

            // Initial fetch
            fetchCart();

            // Attach click listeners to Add to Cart buttons
            document.querySelectorAll('.btn-add-cart').forEach(btn => {
                btn.addEventListener('click', function () {
                    const serviceId = this.dataset.id;
                    addToCart(serviceId, this);
                });
            });

            function addToCart(serviceId, btnElement) {
                btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btnElement.disabled = true;

                fetch('/api/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ provider_id: providerId, service_id: serviceId })
                })
                    .then(r => r.json())
                    .then(data => {
                        btnElement.innerHTML = '<i class="fas fa-check text-success"></i> Added';
                        setTimeout(() => {
                            btnElement.innerHTML = '<i class="fas fa-cart-plus me-1"></i> Add to Cart';
                            btnElement.disabled = false;
                        }, 1500);
                        fetchCart();
                    });
            }

            function removeCartItem(serviceId) {
                fetch('/api/cart/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ provider_id: providerId, service_id: serviceId })
                })
                    .then(r => r.json())
                    .then(data => fetchCart());
            }

            function fetchCart() {
                fetch(`/api/cart/get?provider_id=${providerId}`)
                    .then(r => r.json())
                    .then(data => {
                        renderCart(data);
                    });
            }

            function renderCart(data) {
                let itemsHTML = '';
                if (data.items.length === 0) {
                    itemsHTML = `
                        <div class="text-center text-muted py-3 small empty-cart-msg">
                            <i class="fas fa-cart-arrow-down fa-2x mb-2 opacity-50"></i><br>Your cart is empty.
                        </div>`;
                    checkoutBtn.style.display = 'none';
                    checkoutBtn.classList.add('disabled');
                    emptyCheckoutBtn.style.display = 'block';
                } else {
                    data.items.forEach(item => {
                        let displayPrice = item.price;
                        // Front-end modifier visual only, exact price calculated backend
                        @if($selectedCar && isset($carModel))
                            displayPrice = displayPrice * {{ $carModel->price_modifier }};
                        @endif

                        itemsHTML += `
                            <div class="d-flex justify-content-between align-items-center p-2 rounded border" style="background:#fff">
                                <div class="text-truncate me-2" style="max-width:180px">
                                    <div class="fw-bold small text-truncate">${item.name}</div>
                                    <div class="text-muted" style="font-size:.7rem">PKR ${Math.round(displayPrice)} · ${item.duration}m</div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger border-0" onclick="removeCartItem(${item.id})" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });
                    checkoutBtn.style.display = 'block';
                    checkoutBtn.classList.remove('disabled');
                    emptyCheckoutBtn.style.display = 'none';
                }

                cartItemsList.innerHTML = itemsHTML;
                document.getElementById('cartItemCount').innerText = data.items.length;
                document.getElementById('cartTotalDuration').innerText = data.total_duration + ' min';

                let tp = data.total_base_price;
                @if($selectedCar && isset($carModel))
                    tp = tp * {{ $carModel->price_modifier }};
                @endif
                document.getElementById('cartTotalPrice').innerText = 'PKR ' + Math.round(tp);
            }
        </script>
    @endpush

@endsection