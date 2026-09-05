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

        if (! $provider) {
            return view('provider.pending');
        }

        $request = request();
        $filterDate = $request->get('filter_date');
        $showAll    = $request->boolean('show_all');

        $filtered = (bool) $filterDate;

        $allBookings = Booking::where('service_provider_id', $provider->id)
            ->where('status', '!=', 'payment_pending')
            ->get();

        $stats = [
            'total'     => $allBookings->count(),
            'pending'   => $allBookings->where('status', 'confirmed')->count(),
            'active'    => $allBookings->whereIn('status', ['accepted', 'assigned', 'in_progress'])->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
        ];

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
            return response()->json([
                'html'  => view('provider.partials.bookings_table_body', compact('bookings', 'workers'))->render(),
                'stats' => [
                    'total'     => number_format($stats['total']),
                    'pending'   => number_format($stats['pending']),
                    'active'    => number_format($stats['active']),
                    'completed' => number_format($stats['completed']),
                ],
            ]);
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

        $appointmentDate = \Carbon\Carbon::parse($booking->appointment_time)->toDateString();
        $today = now()->toDateString();
        if ($appointmentDate > $today) {
            return back()->with('error', 'You cannot assign a worker to a service scheduled for a future date. Assignments can only be made on the day of the service.');
        }

        $availableWorkerIds = $booking->getAvailableWorkers()->pluck('id')->toArray();
        if (!in_array($worker->id, $availableWorkerIds)) {
            return back()->with('error', 'Worker "' . $worker->name . '" is already assigned to another service during this time slot.');
        }

        $booking->update([
            'provider_worker_id' => $worker->id,
            'status'             => 'assigned',
        ]);

        if ($booking->user) {
            $booking->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'assigned'));
        }
        if ($worker && $worker->user) {
            $worker->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'assigned_to_worker'));
        }

        return back()->with('success', 'Worker "' . $worker->name . '" assigned to booking.');
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $provider = $this->getProvider();
        abort_unless($booking->service_provider_id === $provider->id, 403);

        $request->validate(['status' => 'required|in:accepted,assigned,in_progress,completed,cancelled']);

        $booking->update(['status' => $request->status]);

        if ($request->status === 'completed' && $booking->user) {
            $booking->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'completed'));
        }

        return back()->with('success', 'Booking updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }
}
