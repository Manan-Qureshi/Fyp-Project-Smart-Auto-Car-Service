<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceProvider;
use App\Models\CarModel;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Customer dashboard: list own bookings.
     */
    public function index()
    {
        $bookings = Booking::with(['service', 'serviceProvider', 'worker', 'payment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('customer.dashboard', compact('bookings'));
    }

    /**
     * Show booking form for a provider's service.
     */
    public function create(Request $request, ServiceProvider $provider)
    {
        $cartIds = session('cart_provider_' . $provider->id, []);
        if (empty($cartIds)) {
            return redirect()->route('providers.show', $provider)->with('error', 'Your cart is empty. Please add services first.');
        }

        $services = Service::whereIn('id', $cartIds)->get();
        $service = $services->first(); // Primary service for legacy template logic

        // Verify provider offers this primary service (sanity check)
        $providerService = $provider->providerServices()
            ->where('service_id', $service->id)
            ->where('is_available', true)
            ->firstOrFail();

        $selectedCar = session('selected_car_model');
        $carModel = $selectedCar ? CarModel::find($selectedCar['id']) : null;
        $modifier = $carModel ? $carModel->price_modifier : 1;

        $finalPrice = 0;
        $totalDuration = 0;

        foreach ($services as $srv) {
            $finalPrice += ($srv->base_price * $modifier);
            $totalDuration += $srv->duration_minutes;
        }

        return view('bookings.create', compact('provider', 'service', 'services', 'providerService', 'selectedCar', 'finalPrice', 'totalDuration'));
    }

    /**
     * Store booking (online payment only → redirect to Stripe).
     */
    public function store(Request $request)
    {
        $today    = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        $request->validate([
            'service_ids'         => 'required|array|min:1',
            'service_ids.*'       => 'exists:services,id',
            'service_provider_id' => 'required|exists:service_providers,id',
            'car_model_id'        => 'nullable|exists:car_models,id',
            'appointment_date'    => ['required', 'date', function($attr, $val, $fail) use ($today, $tomorrow) {
                if (!in_array($val, [$today, $tomorrow])) {
                    $fail('Appointment must be today or tomorrow.');
                }
            }],
            'appointment_time'    => 'required',
            'notes'               => 'nullable|string|max:500',
        ]);

        $services = Service::whereIn('id', $request->service_ids)->get();
        $primaryService = $services->first();
        
        $carModel  = $request->car_model_id ? CarModel::find($request->car_model_id) : null;
        $modifier  = $carModel ? $carModel->price_modifier : 1;
        
        $totalPrice = 0;
        $totalDuration = 0;
        foreach ($services as $srv) {
            $totalPrice += ($srv->base_price * $modifier);
            $totalDuration += $srv->duration_minutes;
        }

        $dateTime  = $request->appointment_date . ' ' . $request->appointment_time;

        // Server-side capacity check
        $totalWorkers = \App\Models\Worker::where('service_provider_id', $request->service_provider_id)->count();
        $slotStart = \Carbon\Carbon::parse($dateTime);
        $slotEnd   = $slotStart->copy()->addMinutes($totalDuration);

        $activeBookings = Booking::where('service_provider_id', $request->service_provider_id)
            ->whereDate('appointment_time', $request->appointment_date)
            ->whereIn('status', ['confirmed', 'payment_pending', 'accepted', 'assigned', 'in_progress'])
            ->get();

        $overlapCount = 0;
        foreach ($activeBookings as $b) {
            $bStart = \Carbon\Carbon::parse($b->appointment_time);
            $bEnd   = $bStart->copy()->addMinutes($b->duration_minutes ?? $totalDuration);

            // Overlap check
            if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) {
                $overlapCount++;
            }
        }

        if ($overlapCount >= $totalWorkers || $totalWorkers === 0) {
            return back()
                ->withErrors(['appointment_time' => 'This time slot is fully booked. Please choose another.'])
                ->withInput();
        }

        $bookingData = [
            'user_id'             => Auth::id(),
            'service_id'          => $primaryService->id,
            'service_ids'         => $request->service_ids,
            'service_provider_id' => $request->service_provider_id,
            'car_model_id'        => $request->car_model_id,
            'appointment_time'    => $dateTime,
            'duration_minutes'    => $totalDuration,
            'notes'               => $request->notes,
            'final_price'         => round($totalPrice, 2),
        ];

        // Clear cart
        session()->forget('cart_provider_' . $request->service_provider_id);

        return app(PaymentController::class)->checkoutBooking($bookingData);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:accepted,assigned,in_progress,completed,cancelled',
        ]);

        $user = auth()->user();

        if ($user->role === 'worker') {
            $worker = \App\Models\Worker::where('user_id', $user->id)->firstOrFail();
            abort_unless($booking->provider_worker_id === $worker->id, 403);

            $newStatus = $request->status;
            $current   = $booking->status;

            $allowed = [
                'assigned'    => 'in_progress',
                'in_progress' => 'completed',
            ];

            if (!isset($allowed[$current]) || $allowed[$current] !== $newStatus) {
                return back()->with('error', 'Invalid status transition. Allowed: Assigned → In-Progress → Completed only.');
            }

            if ($current === 'assigned' && $newStatus === 'in_progress') {
                $blockedByEarlier = Booking::where('provider_worker_id', $worker->id)
                    ->where('status', 'assigned')
                    ->where('appointment_time', '<', $booking->appointment_time)
                    ->exists();

                if ($blockedByEarlier) {
                    return back()->with('error', 'You must start your earlier assigned booking first (first-come, first-served).');
                }
            }

            $booking->update(['status' => $newStatus]);
            
            if ($newStatus === 'in_progress' && $booking->user) {
                $booking->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'started'));
            } elseif ($newStatus === 'completed') {
                if ($booking->user) {
                    $booking->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'completed'));
                }
                if ($booking->serviceProvider && $booking->serviceProvider->user) {
                    $booking->serviceProvider->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'worker_finished'));
                }
            }

            return back()->with('success', 'Booking status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.');
        }

        if ($user->role === 'provider') {
            $sp = $user->serviceProvider;
            abort_unless($sp && $booking->service_provider_id === $sp->id, 403);
        }

        $booking->update(['status' => $request->status]);

        return back()->with('success', 'Booking status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        if (in_array($booking->status, ['in_progress', 'completed'])) {
            return back()->with('error', 'Cannot cancel a booking that is In-Progress or Completed.');
        }

        if ($booking->created_at->diffInMinutes(now()) > 15) {
            return back()->with('error', 'You can only cancel a booking within 15 minutes of creating it.');
        }

        $payment = $booking->payment;

        if ($payment && $payment->status === 'paid' && $payment->stripe_payment_intent) {
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                \Stripe\Refund::create(['payment_intent' => $payment->stripe_payment_intent]);
                $payment->update(['status' => 'refunded']);
            } catch (\Exception $e) {
                if (!str_contains($e->getMessage(), 'already been refunded')) {
                    return back()->with('error', 'Refund failed: ' . $e->getMessage());
                }
                $payment->update(['status' => 'refunded']);
            }
        }

        $booking->update(['status' => 'cancelled']);

        if ($booking->serviceProvider && $booking->serviceProvider->user) {
            $booking->serviceProvider->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'cancelled_by_customer'));
        }
        if ($booking->provider_worker_id) {
            $worker = \App\Models\Worker::find($booking->provider_worker_id);
            if ($worker && $worker->user) {
                $worker->user->notify(new \App\Notifications\ServiceStatusUpdated($booking, 'cancelled_by_customer'));
            }
        }

        return back()->with('success', 'Booking cancelled. Refund initiated if applicable.');
    }

    public function confirmation(Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        $booking->load(['service', 'serviceProvider', 'payment']);
        return view('bookings.confirmation', compact('booking'));
    }

    public function newService()
    {
        session()->forget(['cart', 'selected_car_model']);
        return redirect()->route('welcome');
    }
}
