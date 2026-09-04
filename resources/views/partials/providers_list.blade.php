@if($lat && $lng)
    <h2 class="sacs-providers__heading">
        <i class="fas fa-location-arrow me-2"></i> Nearest Service Providers
        <span class="sacs-providers__coords">near {{ number_format((float) $lat, 4) }},
            {{ number_format((float) $lng, 4) }}</span>
    </h2>
@else
    <h2 class="sacs-providers__heading">
        <i class="fas fa-store me-2"></i> All Service Providers
    </h2>
@endif

@if($providers->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-search fa-3x mb-3" style="color:#1a56db;opacity:.4;"></i>
        <h5 class="text-muted">No providers found.</h5>
        <p class="text-muted small">Click <strong>Find Nearest Provider</strong> above to allow location access.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($providers->sortBy('distance') as $loop_index => $provider)
            @php $isNearest = ($loop_index === $providers->sortBy('distance')->keys()->first() && $lat && $lng && isset($provider->distance) && $provider->distance !== null); @endphp
            <div class="col-md-6 col-lg-4">
                <div class="sacs-card hover-lift {{ $isNearest ? 'sacs-card--nearest' : '' }}">
                    @if($isNearest)
                        <div class="sacs-card__nearest-badge">
                            <i class="fas fa-trophy me-1"></i> Nearest to You
                        </div>
                    @endif
                    <div class="sacs-card__header {{ $isNearest ? 'sacs-card__header--nearest' : '' }}">
                        @if($provider->logo)
                            <img src="{{ asset('storage/' . $provider->logo) }}" height="70"
                                class="rounded-circle bg-white p-1 shadow-sm">
                        @else
                            <div class="sacs-card__icon-wrap {{ $isNearest ? 'sacs-card__icon-wrap--nearest' : '' }}">
                                <i class="fas fa-store-alt fa-2x"
                                    style="color:{{ $isNearest ? '#059669' : '#1a56db' }};"></i>
                            </div>
                        @endif
                    </div>
                    <div class="sacs-card__body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="sacs-card__title">{{ $provider->business_name }}</h5>
                            @if(isset($provider->distance) && $provider->distance !== null)
                                <span class="sacs-card__distance {{ $isNearest ? 'sacs-card__distance--nearest' : '' }}">
                                    <i class="fas fa-location-arrow me-1"></i>{{ number_format($provider->distance, 1) }} km
                                </span>
                            @endif
                        </div>
                        {{-- Rating --}}
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($provider->avg_rating) ? 'text-warning' : 'text-muted' }}"
                                    style="font-size:.78rem;"></i>
                            @endfor
                            <small class="text-muted ms-1">{{ $provider->avg_rating }}/5
                                ({{ $provider->rating_count }})</small>
                        </div>
                        <p class="sacs-card__meta"><i
                                class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $provider->address }}</p>
                        @if($provider->phone)
                            <p class="sacs-card__meta"><i class="fas fa-phone me-1"
                                    style="color:#1a56db;"></i>{{ $provider->phone }}</p>
                        @endif
                        @if($provider->description)
                            <p class="sacs-card__desc">{{ Str::limit($provider->description, 80) }}</p>
                        @endif
                    </div>
                    <div class="sacs-card__footer">
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <div class="d-flex gap-2 w-100">
                                <a href="{{ route('admin.providers.edit', $provider) }}" class="btn btn-sm btn-outline-warning flex-fill rounded-pill">
                                    <i class="fas fa-edit me-1"></i> Edit Provider
                                </a>
                                <a href="{{ route('providers.show', $provider) }}" class="btn btn-sm btn-outline-primary flex-fill rounded-pill">
                                    <i class="fas fa-eye me-1"></i> View Services
                                </a>
                            </div>
                        @else
                            <a href="{{ route('providers.show', $provider) }}"
                                class="sacs-card__btn {{ $isNearest ? 'sacs-card__btn--nearest' : '' }}">
                                <i class="fas fa-eye me-2"></i> View Services &amp; Book
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
