@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:620px">
    <div class="text-center py-3">
        <h3 class="fw-bold text-success mb-1"><i class="fas fa-check-circle me-2"></i>Booking Confirmed!</h3>
        <p class="text-muted mb-3">Your payment was successful and your booking is confirmed.</p>
    </div>

    <div class="glass-card p-4 rounded-4 shadow mb-4">
        <h5 class="fw-bold mb-3"><i class="fas fa-receipt me-2 text-primary"></i>Booking Details</h5>
        <table class="table table-borderless mb-0">
            <tr><th class="text-muted fw-normal" style="width:40%">Booking ID</th><td class="fw-semibold">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
            <tr><th class="text-muted fw-normal">Service</th><td class="fw-semibold">{{ $booking->service->name }}</td></tr>
            <tr><th class="text-muted fw-normal">Provider</th><td class="fw-semibold">{{ $booking->serviceProvider->business_name }}</td></tr>
            <tr><th class="text-muted fw-normal">Address</th><td>{{ $booking->serviceProvider->address }}</td></tr>
            <tr><th class="text-muted fw-normal">Appointment</th><td class="fw-semibold">{{ $booking->appointment_time->format('D, d M Y \a\t h:i A') }}</td></tr>
            <tr><th class="text-muted fw-normal">Amount Paid</th><td class="fw-bold text-primary fs-5">PKR {{ number_format($booking->final_price) }}</td></tr>
            <tr>
                <th class="text-muted fw-normal">Status</th>
                <td><span class="badge bg-success rounded-pill px-3 py-2">Confirmed</span></td>
            </tr>
            @if($booking->notes)
            <tr><th class="text-muted fw-normal">Notes</th><td>{{ $booking->notes }}</td></tr>
            @endif
        </table>
    </div>

    <div class="alert alert-info rounded-3">
        <i class="fas fa-info-circle me-2"></i>
        The service provider will review your booking and assign a worker shortly.
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill flex-grow-1">
            <i class="fas fa-th-large me-2"></i>View My Bookings
        </a>
        <a href="{{ route('welcome') }}" class="btn btn-outline-secondary rounded-pill flex-grow-1">
            <i class="fas fa-home me-2"></i>Home
        </a>
    </div>
</div>
@endsection
