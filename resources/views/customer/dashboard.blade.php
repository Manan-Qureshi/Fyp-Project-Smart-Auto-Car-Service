@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header row with title + Book New Service button --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-calendar-check text-primary me-2"></i>My Bookings
        </h3>

        @if($lastProvider)
            <a href="{{ route('providers.show', $lastProvider) }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>Book New Service
            </a>
        @else
            <a href="{{ route('welcome') }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-search-location me-2"></i>Find a Provider
            </a>
        @endif
    </div>

        <div class="glass-card p-4 rounded-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Car</th>
                            <th>Appointment</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                    @include("customer.partials.bookings_table_body")
                </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Rating Modals (outside the table) --}}
@foreach($bookings as $booking)
    @if($booking->status === 'completed' && !$booking->rating)
    <div class="modal fade" id="rateModal{{ $booking->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-star text-warning me-2"></i>Rate Service</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form action="{{ route('bookings.rate', $booking) }}" method="POST">
                        @csrf
                        <p class="text-muted small mb-3">
                            {{ optional($booking->service)->name }} at
                            {{ optional($booking->serviceProvider)->business_name }}
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rating</label>
                            <div class="d-flex gap-3">
                                @for($i=1; $i<=5; $i++)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                           name="rating"
                                           id="r{{ $booking->id }}_{{ $i }}"
                                           value="{{ $i }}"
                                           {{ $i==5 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold"
                                           for="r{{ $booking->id }}_{{ $i }}">{{ $i }}★</label>
                                </div>
                                @endfor
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Review <span class="text-muted">(optional)</span>
                            </label>
                            <textarea name="review" class="form-control rounded-3" rows="3"
                                      placeholder="Share your experience..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold">
                            <i class="fas fa-check me-2"></i>Submit Rating
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach


@push('scripts')
<script>
    setInterval(function(){
        fetch(window.location.href, {
            headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(r=>r.json())
        .then(d=>{if(d.html) document.getElementById('bookings-tbody').innerHTML=d.html;})
        .catch(e=>console.error('Polling error', e));
    }, 10000); // 10 seconds
</script>
@endpush
@endsection

