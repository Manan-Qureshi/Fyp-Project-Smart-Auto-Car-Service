@extends('layouts.frontend')

@section('content')

    {{-- ── HERO SECTION ── --}}
    <section class="sacs-hero">
        <div class="sacs-hero__inner">

            {{-- Left Text Column --}}
            <div class="sacs-hero__text">

                <h1 class="sacs-hero__title">
                    Best Car Repair &amp;<br>Maintenance Services
                </h1>

                <p class="sacs-hero__subtitle">
                    Connect with certified mechanics near you. Fast, transparent and reliable car care. Exactly when you
                    need it.
                </p>

            </div>

            {{-- Right Car Image Column --}}
            <div class="sacs-hero__visual">
                {{-- Decorative blob --}}
                <div class="sacs-hero__blob"></div>

                {{-- Car image --}}
                <img src="{{ asset('images/car_exploded.png') }}"
                    onerror="this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=700&q=80'; this.style.objectFit='cover';"
                    alt="Car" class="sacs-hero__car-img">

                {{-- FIND NEAREST PROVIDER BUTTON --}}
                <div class="sacs-hero__geo-wrap">
                    <button id="findNearestBtn" class="sacs-hero__geo-btn">
                        <i class="fas fa-location-crosshairs me-2"></i>
                        Find Nearest Provider
                    </button>
                    <p id="geoStatus" class="sacs-hero__geo-status"></p>
                </div>
            </div>

        </div>
    </section>

    {{-- ── PROVIDERS SECTION ── --}}
    <section class="sacs-providers" id="providersSection">
        <div class="sacs-providers__inner" id="providersContainer">
            @include('partials.providers_list')
        </div>
    </section>

    {{-- Car Selection Modal --}}
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

@endsection

@push('scripts')
    <script>
        // Car type -> model cascade
        document.getElementById('carTypeSelect')?.addEventListener('change', function () {
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

        // Helper function to fetch nearest providers via AJAX without reloading page
        function loadNearestProviders(lat, lng, scrollIntoView = false) {
            const btn = document.getElementById('findNearestBtn');
            const status = document.getElementById('geoStatus');

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Finding nearest providers...';
            }
            if (status) status.textContent = '';

            return fetch('/?lat=' + lat + '&lng=' + lng, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-crosshairs me-2"></i> Find Nearest Provider';
                }
                if (data.html) {
                    const container = document.getElementById('providersContainer');
                    if (container) container.innerHTML = data.html;
                    if (scrollIntoView) {
                        document.getElementById('providersSection')?.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-crosshairs me-2"></i> Find Nearest Provider';
                }
                if (status) {
                    status.textContent = 'Failed to fetch providers. Please try again.';
                    status.style.color = '#dc3545';
                }
            });
        }

        // Request browser geolocation
        function requestUserLocation(scrollIntoView = true) {
            const btn = document.getElementById('findNearestBtn');
            const status = document.getElementById('geoStatus');

            if (!navigator.geolocation) {
                if (status) {
                    status.textContent = 'Geolocation is not supported by your browser.';
                    status.style.color = '#dc3545';
                }
                return;
            }

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Finding nearest providers...';
            }
            if (status) status.textContent = '';

            navigator.geolocation.getCurrentPosition(
                pos => {
                    loadNearestProviders(pos.coords.latitude, pos.coords.longitude, scrollIntoView);
                },
                err => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-location-crosshairs me-2"></i> Find Nearest Provider';
                    }
                    if (status) {
                        status.textContent = 'Location access denied. Please allow location in your browser settings.';
                        status.style.color = '#dc3545';
                    }
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        // Button click trigger
        document.getElementById('findNearestBtn')?.addEventListener('click', function () {
            requestUserLocation(true);
        });

        // Auto-detect granted location permission on page load or state change
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(permissionStatus => {
                if (permissionStatus.state === 'granted') {
                    requestUserLocation(false);
                }
                permissionStatus.onchange = function () {
                    if (this.state === 'granted') {
                        requestUserLocation(true);
                    }
                };
            }).catch(() => {});
        }
    </script>
@endpush