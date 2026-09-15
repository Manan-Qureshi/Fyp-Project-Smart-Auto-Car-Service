@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header row with title + Book New Service button --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
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
                            <th class="ps-3">Service ID</th>
                            <th>Name</th>
                            <th>Provider Name</th>
                            <th>Service Time</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                    @include("customer.partials.bookings_table_body")
                </tbody>
                </table>
            </div>
        </div>
</div>

{{-- Rating Modals (outside the table) --}}
@foreach($bookings as $booking)
    @if($booking->status === 'completed' && !$booking->rating)
    <div class="modal fade" id="rateModal{{ $booking->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Rate Service</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form action="{{ route('bookings.rate', $booking) }}" method="POST">
                        @csrf
                        <p class="text-muted small mb-3">
                            {{ optional($booking->service)->name }} at
                            {{ optional($booking->serviceProvider)->business_name }}
                        </p>
                        <style>
                        .star-rating-select {
                            display: inline-flex;
                            flex-direction: row-reverse;
                            align-items: center;
                        }
                        .star-rating-select input[type="radio"] {
                            display: none !important;
                        }
                        .star-rating-select label.star-btn {
                            font-size: 2rem;
                            color: #d1d5db;
                            cursor: pointer;
                            transition: color 0.15s ease-in-out;
                            margin-right: 4px;
                            user-select: none;
                        }
                        .star-rating-select label.star-btn:hover,
                        .star-rating-select label.star-btn:hover ~ label.star-btn,
                        .star-rating-select input[type="radio"]:checked ~ label.star-btn {
                            color: #ffc107;
                        }
                        </style>

                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">Rating & Feedback</label>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="star-rating-select">
                                    @for($i=5; $i>=1; $i--)
                                    <input type="radio"
                                           name="rating"
                                           id="r{{ $booking->id }}_{{ $i }}"
                                           value="{{ $i }}"
                                           {{ $i==5 ? 'checked' : '' }}>
                                    <label class="star-btn"
                                           for="r{{ $booking->id }}_{{ $i }}"
                                           title="{{ $i }} Stars">★</label>
                                    @endfor
                                </div>

                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="feedback_type" id="ft_cmp_{{ $booking->id }}" value="complaint">
                                    <label class="form-check-label fw-semibold text-danger" for="ft_cmp_{{ $booking->id }}">
                                        Complaint
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Review / Details</label>
                            <textarea name="review" class="form-control rounded-3" rows="3" placeholder="Please provide your feedback or describe any issue experienced..."></textarea>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-bold shadow-sm">
                                <i class="fas fa-check me-2"></i>Submit Rating
                            </button>
                        </div>
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
    }, 5000);
</script>
@endpush
@endsection

