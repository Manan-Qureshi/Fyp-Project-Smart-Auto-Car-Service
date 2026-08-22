@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:680px">
    <div class="glass-card p-4 rounded-4 shadow">
        {{-- Header --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <i class="fas fa-shopping-cart text-primary" style="font-size:1.8rem"></i>
            <h4 class="fw-bold mb-0">Checkout</h4>
        </div>

        {{-- Car Model --}}
        @if($selectedCar)
            <div class="text-center text-primary fw-semibold mb-3">
                {{ $selectedCar['type_name'] }} {{ $selectedCar['name'] }}
            </div>
        @endif

        {{-- Services Detail Table --}}
        <div class="mb-4">
            <table class="table table-borderless mb-0" style="border-bottom:1px solid #dee2e6">
                <thead>
                    <tr style="border-bottom:1px solid #dee2e6">
                        <th class="ps-0 text-muted fw-semibold small">Service</th>
                        <th class="text-muted fw-semibold small text-center">Duration</th>
                        <th class="pe-0 text-muted fw-semibold small text-end">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $srv)
                        @php
                            $modifier = (isset($carModel) && $carModel) ? $carModel->price_modifier : 1;
                            $srvPrice = round($srv->base_price * $modifier, 2);
                        @endphp
                        <tr>
                            <td class="ps-0">{{ $srv->name }}</td>
                            <td class="text-center text-muted">{{ $srv->duration_minutes }} min</td>
                            <td class="pe-0 text-end">PKR {{ number_format($srvPrice) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center pt-2">
                <strong>Total</strong>
                <div class="text-end">
                    <span class="text-muted small me-3">{{ $totalDuration }} min</span>
                    <span class="fs-5 fw-bold text-primary">PKR {{ number_format($finalPrice) }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
            @csrf
            @foreach($services as $srv)
                <input type="hidden" name="service_ids[]" value="{{ $srv->id }}">
            @endforeach
            <input type="hidden" name="service_provider_id" value="{{ $provider->id }}">
            @if($selectedCar)
                <input type="hidden" name="car_model_id" value="{{ $selectedCar['id'] }}">
            @endif
            <input type="hidden" name="appointment_date" id="hiddenDate">
            <input type="hidden" name="appointment_time" id="hiddenTime">

            {{-- Date Selector Dropdown --}}
            @php
                $today    = \Carbon\Carbon::today();
                $tomorrow = \Carbon\Carbon::tomorrow();
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <select id="dateSelect" class="form-select rounded-3">
                        <option value="">Select Date</option>
                        <option value="{{ $today->format('Y-m-d') }}">Today — {{ $today->format('D, d M') }}</option>
                        <option value="{{ $tomorrow->format('Y-m-d') }}">Tomorrow — {{ $tomorrow->format('D, d M') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <select id="timeSelect" class="form-select rounded-3" disabled>
                        <option value="">Select Date First</option>
                    </select>
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Notes <span class="text-muted">(optional)</span></label>
                <textarea name="notes" class="form-control rounded-3" rows="3"
                          placeholder="Any special instructions...">{{ old('notes') }}</textarea>
            </div>

            <hr class="my-4">



            <div class="d-flex gap-2">
                <a href="{{ route('providers.show', $provider) }}" class="btn btn-outline-secondary rounded-pill flex-grow-1">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1" id="submitBtn" disabled>
                    <i class="fas fa-lock me-1"></i> Proceed to Payment — PKR {{ number_format($finalPrice) }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const PROVIDER_ID = {{ $provider->id }};
const DURATION    = {{ $totalDuration }};
let selectedDate  = null;
let selectedTime  = null;

const dateSelect = document.getElementById('dateSelect');
const timeSelect = document.getElementById('timeSelect');

dateSelect.addEventListener('change', function () {
    selectedDate = this.value;
    document.getElementById('hiddenDate').value = selectedDate;

    // Reset time
    selectedTime = null;
    document.getElementById('hiddenTime').value = '';
    document.getElementById('submitBtn').disabled = true;

    if (!selectedDate) {
        timeSelect.innerHTML = '<option value="">Select Date First</option>';
        timeSelect.disabled = true;
        return;
    }

    loadSlots(selectedDate);
});

timeSelect.addEventListener('change', function () {
    selectedTime = this.value;
    document.getElementById('hiddenTime').value = selectedTime;
    document.getElementById('submitBtn').disabled = !selectedTime;
});

function loadSlots(date) {
    timeSelect.innerHTML = '<option value="">Loading...</option>';
    timeSelect.disabled = true;

    fetch(`/api/timeslots?provider_id=${PROVIDER_ID}&date=${date}&duration=${DURATION}`)
        .then(r => r.json())
        .then(slots => {
            if (!slots.length) {
                timeSelect.innerHTML = '<option value="">No slots available</option>';
                timeSelect.disabled = true;
                return;
            }
            timeSelect.innerHTML = '<option value="">Select Time</option>';
            slots.forEach(slot => {
                const opt = document.createElement('option');
                opt.value = slot;
                opt.textContent = formatTime(slot);
                timeSelect.appendChild(opt);
            });
            timeSelect.disabled = false;
        })
        .catch(() => {
            timeSelect.innerHTML = '<option value="">Error loading slots</option>';
            timeSelect.disabled = true;
        });
}

function formatTime(t) {
    const [h, m] = t.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hh   = h % 12 || 12;
    return `${hh}:${String(m).padStart(2,'0')} ${ampm}`;
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if (!selectedDate || !selectedTime) {
        e.preventDefault();
        alert('Please select a date and time slot.');
    }
});
</script>
@endpush
@endsection