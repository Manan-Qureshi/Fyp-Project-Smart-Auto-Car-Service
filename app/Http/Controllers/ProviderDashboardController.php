<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Worker;
use App\Models\ServiceProvider;

class ProviderDashboardController extends Controller
{
    private function getProvider()
    {
        $sp = Auth::user()->serviceProvider;
        if (! $sp) {
            return null;
        }
        return $sp;
    }

    public function index()
    {
        $provider = $this->getProvider();

        // Account exists but admin hasn't set up the provider profile yet
        if (! $provider) {
            return view('provider.pending');
        }

        $bookings = Booking::with(['user', 'service', 'carModel', 'worker'])
            ->where('service_provider_id', $provider->id)
            ->latest()
            ->get();

        $workers = $provider->workers()->get();

        $stats = [
            'total'     => $bookings->count(),
            'pending'   => $bookings->whereIn('status', ['confirmed', 'payment_pending'])->count(),
            'active'    => $bookings->whereIn('status', ['accepted', 'assigned', 'in_progress'])->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
        ];

        if (request()->ajax()) {
            return response()->json(['html' => view('provider.partials.bookings_table_body', compact('bookings', 'workers'))->render()]);
        }

        return view('provider.dashboard', compact('provider', 'bookings', 'workers', 'stats'));
    }

    public function assign(Request $request, Booking $booking)
    {
        $provider = $this->getProvider();
        abort_unless($booking->service_provider_id === $provider->id, 403);

        $request->validate(['worker_id' => 'required|exists:workers,id']);

        $worker = Worker::where('id', $request->worker_id)
            ->where('service_provider_id', $provider->id)
            ->firstOrFail();

        // Validation for today assignment only
        $appointmentDate = \Carbon\Carbon::parse($booking->appointment_time)->toDateString();
        $today = now()->toDateString();
        if ($appointmentDate > $today) {
            return back()->with('error', 'You cannot assign a worker to a service scheduled for a future date. Assignments can only be made on the day of the service.');
        }

        $booking->update([
            'provider_worker_id' => $worker->id,
            'status'             => 'assigned',
        ]);

        if ($booking->user) {
            $booking->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'assigned'));
        }

        return back()->with('success', 'Worker "' . $worker->name . '" assigned to booking.');
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $provider = $this->getProvider();
        abort_unless($booking->service_provider_id === $provider->id, 403);

        $request->validate(['status' => 'required|in:accepted,assigned,in_progress,completed,cancelled']);

        $booking->update(['status' => $request->status]);

        return back()->with('success', 'Booking updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }
}
