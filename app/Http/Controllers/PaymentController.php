<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Commission;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    /**
     * Create a Stripe Checkout session for a booking.
     */
    public function checkoutBooking(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) abort(403);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $carLabel = $booking->carModel ? ' (' . $booking->carModel->name . ')' : '';

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'pkr',
                    'product_data' => [
                        'name'        => $booking->service->name . $carLabel,
                        'description' => 'Provider: ' . $booking->serviceProvider->business_name,
                    ],
                    'unit_amount'  => (int)($booking->final_price * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
            'cancel_url'  => route('payment.cancel', ['booking_id' => $booking->id]),
        ]);

        // Store payment record
        Payment::create([
            'booking_id'        => $booking->id,
            'stripe_session_id' => $session->id,
            'amount'            => $booking->final_price,
            'currency'          => 'pkr',
            'status'            => 'pending',
        ]);

        return redirect($session->url);
    }

    /**
     * Stripe success redirect.
     */
    public function success(Request $request)
    {
        $stripe  = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->get('session_id'));

        $booking = Booking::with(['service', 'serviceProvider'])->findOrFail($request->get('booking_id'));

        if ($session->payment_status === 'paid') {
            $intentId = is_object($session->payment_intent)
                ? $session->payment_intent->id
                : $session->payment_intent;

            // Update payment record
            $booking->payment()->update([
                'stripe_payment_intent' => $intentId,
                'status'                => 'paid',
            ]);

            // Confirm booking
            $booking->update([
                'status'         => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Create commission record (10% default)
            $rate       = 10.00;
            $commission = round($booking->final_price * $rate / 100, 2);
            Commission::create([
                'booking_id'          => $booking->id,
                'service_provider_id' => $booking->service_provider_id,
                'total_amount'        => $booking->final_price,
                'commission_rate'     => $rate,
                'commission_amount'   => $commission,
                'provider_earning'    => $booking->final_price - $commission,
            ]);

            session()->forget(['cart', 'selected_car_model']);

            // Notify the provider of the new booking
            if ($booking->serviceProvider && $booking->serviceProvider->user) {
                $booking->serviceProvider->user->notify(
                    new \App\Notifications\ServiceStatusUpdated($booking, 'booking_received')
                );
            }

            return redirect()->route('bookings.confirmation', $booking)
                ->with('success', 'Payment successful! Your booking is confirmed.');
        }

        return redirect()->route('dashboard')
            ->with('error', 'Payment verification failed. Please contact support.');
    }

    /**
     * Stripe cancel redirect — delete pending booking.
     */
    public function cancel(Request $request)
    {
        if ($request->has('booking_id')) {
            $booking = Booking::find($request->booking_id);
            if ($booking && $booking->user_id === Auth::id() && $booking->status === 'payment_pending') {
                $booking->payment()->delete();
                $booking->delete();
            }
        }

        return redirect()->route('welcome')
            ->with('error', 'Payment was cancelled. Your booking was not confirmed.');
    }

    // Old method compatibility
    public function checkout(Request $request) { return $this->checkoutBooking(Booking::find(0)); }
    public function assignWorker(Booking $booking) {} // no-op, now providers assign workers
}
