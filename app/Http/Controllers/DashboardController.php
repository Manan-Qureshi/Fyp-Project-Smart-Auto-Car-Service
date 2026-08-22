<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\ServiceProvider;
use App\Models\Commission;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isProvider()) {
            return redirect()->route('provider.dashboard');
        } elseif ($user->role === 'worker') {
            return $this->workerDashboard();
        } else {
            return $this->customerDashboard();
        }
    }

    private function adminDashboard()
    {
        $request = request();

        $providers    = ServiceProvider::with('owner')->withCount('bookings')->latest()->get();
        $totalRevenue = Commission::sum('commission_amount');
        $totalEarning = Commission::sum('provider_earning');
        $totalBookings = Booking::count();

        $filterDate     = $request->get('filter_date');
        $filterProvider = $request->get('filter_provider');
        $showAll        = $request->boolean('show_all');

        $filtered     = $filterDate || $filterProvider;
        $bookings     = collect();
        $bookingTotal = 0;

        if ($filtered) {
            // Build base query with filters
            $baseQuery = Booking::with(['user', 'service', 'serviceProvider', 'carModel'])
                ->latest();

            if ($filterDate) {
                $baseQuery->whereDate('appointment_time', $filterDate);
            }
            if ($filterProvider) {
                $baseQuery->where('service_provider_id', $filterProvider);
            }

            // Count BEFORE applying limit — clone so the builder stays clean
            $bookingTotal = (clone $baseQuery)->count();

            // Fetch the page
            $bookings = $showAll
                ? $baseQuery->get()
                : $baseQuery->limit(10)->get();
        } else {
            // Default view: fetch 5 most recent bookings
            $baseQuery = Booking::with(['user', 'service', 'serviceProvider', 'carModel'])
                ->latest();
            $bookingTotal = (clone $baseQuery)->count();
            $bookings = $baseQuery->limit(5)->get();
        }

        if (request()->ajax()) {
            return response()->json(['html' => view('admin.partials.bookings_table_body', compact('bookings'))->render()]);
        }

        return view('admin.dashboard', compact(
            'providers', 'bookings', 'totalRevenue', 'totalEarning',
            'totalBookings', 'filterDate', 'filterProvider', 'filtered', 'bookingTotal', 'showAll'
        ));
    }

    private function workerDashboard()
    {
        $user = Auth::user();

        // Find the Worker row that was created for this user account
        $worker = \App\Models\Worker::where('user_id', $user->id)->first();

        if (!$worker) {
            return view('worker.dashboard', [
                'assignedBookings' => collect(),
                'firstActionableId' => null,
            ]);
        }

        // Load all bookings assigned to this worker, oldest first (FCFS)
        $assignedBookings = Booking::with(['user', 'service', 'serviceProvider', 'carModel'])
            ->where('provider_worker_id', $worker->id)
            ->whereIn('status', ['assigned', 'in_progress', 'completed'])
            ->orderBy('appointment_time', 'asc')
            ->get();

        // The FIRST booking that is not yet completed is the only one the worker can act on
        $firstActionable = $assignedBookings->first(fn($b) => in_array($b->status, ['assigned', 'in_progress']));

        if (request()->ajax()) {
            return response()->json(['html' => view('worker.partials.bookings_table_body', compact('assignedBookings', 'firstActionableId'))->render()]);
        }

        return view('worker.dashboard', [
            'assignedBookings'   => $assignedBookings,
            'firstActionableId'  => $firstActionable?->id,
        ]);
    }

    private function customerDashboard()
    {
        $bookings = Booking::with(['service', 'serviceProvider', 'worker', 'payment', 'rating'])
            ->where('user_id', Auth::user()->id)
            ->latest()
            ->get();

        // Last provider the customer booked with — used for "Book New Service" shortcut
        $lastProvider = $bookings
            ->whereNotNull('service_provider_id')
            ->first()
            ?->serviceProvider;

        if (request()->ajax()) {
            return response()->json(['html' => view('customer.partials.bookings_table_body', compact('bookings'))->render()]);
        }

        return view('customer.dashboard', compact('bookings', 'lastProvider'));
    }

    /**
     * AJAX: Return the current user's latest unread database notifications.
     */
    public function fetchNotifications()
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'      => $n->id,
                'data'    => $n->data,
                'created' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count'         => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * AJAX: Mark all notifications as read.
     */
    public function markNotificationsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}

