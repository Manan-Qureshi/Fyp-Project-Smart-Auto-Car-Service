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

        $request = request();
        $filterDate = $request->get('filter_date');
        $showAll    = $request->boolean('show_all');

        $filtered = (bool) $filterDate;

        // Base query for overall provider stats (excluding payment_pending)
        $allBookings = Booking::where('service_provider_id', $provider->id)
            ->where('status', '!=', 'payment_pending')
            ->get();

        $stats = [
            'total'     => $allBookings->count(),
            'pending'   => $allBookings->where('status', 'confirmed')->count(),
            'active'    => $allBookings->whereIn('status', ['accepted', 'assigned', 'in_progress'])->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
        ];

        // Fetch bookings for table view (excluding payment_pending)
        $baseQuery = Booking::with(['user', 'service', 'carModel', 'worker'])
            ->where('service_provider_id', $provider->id)
            ->where('status', '!=', 'payment_pending')
            ->latest();

        if ($filterDate) {
            $baseQuery->whereDate('appointment_time', $filterDate);
        }

        $bookingTotal = (clone $baseQuery)->count();

        if ($filtered) {
            $bookings = $showAll
                ? $baseQuery->get()
                : $baseQuery->limit(10)->get();
        } else {
            $bookings = $baseQuery->limit(5)->get();
        }

        $workers = $provider->workers()->get();

        if (request()->ajax()) {
            return response()->json(['html' => view('provider.partials.bookings_table_body', compact('bookings', 'workers'))->render()]);
        }

        return view('provider.dashboard', compact('provider', 'bookings', 'workers', 'stats', 'filterDate', 'filtered', 'bookingTotal', 'showAll'));
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
