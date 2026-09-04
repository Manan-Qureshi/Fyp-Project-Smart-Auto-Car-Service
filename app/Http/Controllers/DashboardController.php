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

        try {
            $providers    = ServiceProvider::with('owner')->withCount('bookings')->latest()->get();
            $totalRevenue = Commission::sum('commission_amount') ?? 0;
            $totalEarning = Commission::sum('provider_earning') ?? 0;
            $totalBookings = Booking::where('status', '!=', 'payment_pending')->count();

            $filterDate     = $request->get('filter_date');
            $filterProvider = $request->get('filter_provider');
            $showAll        = $request->boolean('show_all');

            $filtered     = $filterDate || $filterProvider;
            $bookings     = collect();
            $bookingTotal = 0;

            if ($filtered) {
                $baseQuery = Booking::with(['user', 'service', 'serviceProvider', 'carModel'])
                    ->where('status', '!=', 'payment_pending')
                    ->latest();

                if ($filterDate) {
                    $baseQuery->whereDate('appointment_time', $filterDate);
                }
                if ($filterProvider) {
                    $baseQuery->where('service_provider_id', $filterProvider);
                }

                $bookingTotal = (clone $baseQuery)->count();
                $bookings = $showAll ? $baseQuery->get() : $baseQuery->limit(10)->get();
            } else {
                $baseQuery = Booking::with(['user', 'service', 'serviceProvider', 'carModel'])
                    ->where('status', '!=', 'payment_pending')
                    ->latest();
                $bookingTotal = (clone $baseQuery)->count();
                $bookings = $baseQuery->limit(5)->get();
            }
        } catch (\Exception $e) {
            $providers = collect();
            $totalRevenue = 0;
            $totalEarning = 0;
            $totalBookings = 0;
            $filterDate = null;
            $filterProvider = null;
            $filtered = false;
            $bookings = collect();
            $bookingTotal = 0;
            $showAll = false;
        }

        if (request()->ajax()) {
            return response()->json([
                'html'  => view('admin.partials.bookings_table_body', compact('bookings'))->render(),
                'stats' => [
                    'providers'     => number_format($providers->count()),
                    'totalBookings' => number_format($totalBookings),
                    'totalRevenue'  => 'PKR ' . number_format($totalRevenue),
                    'totalEarning'  => 'PKR ' . number_format($totalEarning),
                ]
            ]);
        }

        return view('admin.dashboard', compact(
            'providers', 'bookings', 'totalRevenue', 'totalEarning',
            'totalBookings', 'filterDate', 'filterProvider', 'filtered', 'bookingTotal', 'showAll'
        ));
    }

    private function workerDashboard()
    {
        $user = Auth::user();

        $worker = \App\Models\Worker::where('user_id', $user->id)->first();

        if (!$worker) {
            $stats = ['total' => '0', 'assigned' => '0', 'active' => '0', 'completed' => '0'];
            if (request()->ajax()) {
                return response()->json([
                    'html'  => view('worker.partials.bookings_table_body', ['assignedBookings' => collect(), 'firstActionableId' => null])->render(),
                    'stats' => $stats,
                ]);
            }
            return view('worker.dashboard', [
                'assignedBookings'  => collect(),
                'firstActionableId' => null,
                'stats'             => $stats,
            ]);
        }

        $assignedBookings = Booking::with(['user', 'service', 'serviceProvider', 'carModel'])
            ->where('provider_worker_id', $worker->id)
            ->whereIn('status', ['assigned', 'in_progress', 'completed'])
            ->orderBy('appointment_time', 'asc')
            ->get();

        $firstActionable = $assignedBookings->first(fn($b) => in_array($b->status, ['assigned', 'in_progress']));
        $firstActionableId = $firstActionable?->id;

        $stats = [
            'total'     => number_format($assignedBookings->count()),
            'assigned'  => number_format($assignedBookings->where('status', 'assigned')->count()),
            'active'    => number_format($assignedBookings->where('status', 'in_progress')->count()),
            'completed' => number_format($assignedBookings->where('status', 'completed')->count()),
        ];

        if (request()->ajax()) {
            return response()->json([
                'html'  => view('worker.partials.bookings_table_body', compact('assignedBookings', 'firstActionableId'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('worker.dashboard', [
            'assignedBookings'  => $assignedBookings,
            'firstActionableId' => $firstActionableId,
            'stats'             => $stats,
        ]);
    }

    private function customerDashboard()
    {
        $bookings = Booking::with(['service', 'serviceProvider', 'worker', 'payment', 'rating'])
            ->where('user_id', Auth::user()->id)
            ->where('status', '!=', 'payment_pending')
            ->latest()
            ->get();

        $lastProvider = $bookings
            ->whereNotNull('service_provider_id')
            ->first()
            ?->serviceProvider;

        $stats = [
            'total'     => number_format($bookings->count()),
            'active'    => number_format($bookings->whereIn('status', ['confirmed', 'accepted', 'assigned', 'in_progress'])->count()),
            'completed' => number_format($bookings->where('status', 'completed')->count()),
            'cancelled' => number_format($bookings->where('status', 'cancelled')->count()),
        ];

        if (request()->ajax()) {
            return response()->json([
                'html'  => view('customer.partials.bookings_table_body', compact('bookings'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('customer.dashboard', compact('bookings', 'lastProvider', 'stats'));
    }

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

    public function markNotificationsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}

