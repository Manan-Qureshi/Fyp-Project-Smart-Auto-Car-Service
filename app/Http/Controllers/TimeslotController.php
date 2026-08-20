<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use App\Models\Booking;
use Carbon\Carbon;

class TimeslotController extends Controller
{
    /**
     * GET /api/timeslots
     * params: provider_id, date (Y-m-d), duration (minutes)
     */
    public function available(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:service_providers,id',
            'date'        => 'required|date_format:Y-m-d',
            'duration'    => 'required|integer|min:15',
        ]);

        $provider = ServiceProvider::findOrFail($request->provider_id);
        $date     = $request->date;
        $duration = (int) $request->duration;

        // Only today or tomorrow
        $today    = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        if (!in_array($date, [$today, $tomorrow])) {
            return response()->json(['error' => 'Date must be today or tomorrow.'], 422);
        }

        $openTime  = $provider->open_time  ?? '08:00:00';
        $closeTime = $provider->close_time ?? '18:00:00';

        // Build all possible slots
        $slots   = [];
        $current = Carbon::parse($date . ' ' . $openTime);
        $close   = Carbon::parse($date . ' ' . $closeTime);
        $now     = Carbon::now()->addMinutes(30);

        while ($current->copy()->addMinutes($duration)->lte($close)) {
            // Skip past slots for today
            if ($date === $today && $current->lte($now)) {
                $current->addMinutes($duration);
                continue;
            }

            $slots[] = $current->format('H:i');
            $current->addMinutes($duration);
        }

        $totalWorkers = \App\Models\Worker::where('service_provider_id', $provider->id)->count();

        if ($totalWorkers === 0) {
            return response()->json([]);
        }

        // Remove slots that conflict with existing confirmed bookings
        $booked = Booking::where('service_provider_id', $provider->id)
            ->whereDate('appointment_time', $date)
            ->whereIn('status', ['confirmed', 'payment_pending', 'accepted', 'assigned', 'in_progress'])
            ->get(['appointment_time', 'duration_minutes']);

        $available = array_filter($slots, function ($slot) use ($date, $duration, $booked, $totalWorkers) {
            $slotStart = Carbon::parse($date . ' ' . $slot);
            $slotEnd   = $slotStart->copy()->addMinutes($duration);

            $overlapCount = 0;
            foreach ($booked as $b) {
                $bookingDuration = $b->duration_minutes ?? $duration;
                $bStart = Carbon::parse($b->appointment_time);
                $bEnd   = $bStart->copy()->addMinutes($bookingDuration);

                // Overlap check
                if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) {
                    $overlapCount++;
                }
            }
            return $overlapCount < $totalWorkers;
        });

        return response()->json(array_values($available));
    }
}
