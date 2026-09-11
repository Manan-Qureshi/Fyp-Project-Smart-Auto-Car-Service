<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Rating;
use App\Models\Booking;

class RatingController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_unless($booking->status === 'completed', 422, 'Can only rate completed bookings.');
        abort_if($booking->rating()->exists(), 422, 'You have already rated this booking.');

        $request->validate([
            'rating' => 'required|integer|between:1,5',
        ]);

        Rating::create([
            'booking_id'          => $booking->id,
            'user_id'             => Auth::id(),
            'service_provider_id' => $booking->service_provider_id,
            'rating'              => $request->rating,
        ]);

        return back()->with('success', 'Thank you for your rating!');
    }
}
