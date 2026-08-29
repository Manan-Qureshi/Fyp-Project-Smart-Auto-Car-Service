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
    public function checkoutBooking(array $bookingData)
    {
        if ($bookingData['user_id'] !== Auth::id()) abort(403);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $service = \App\Models\Service::find($bookingData['service_id']);
        $provider = \App\Models\ServiceProvider::find($bookingData['service_provider_id']);
        $carModel = $bookingData['car_model_id'] ? \App\Models\CarModel::find($bookingData['car_model_id']) : null;
        $carLabel = $carModel ? ' (' . $carModel->name . ')' : '';

        // Stripe metadata values must be strings
        $metadata = [
            'user_id'             => (string)$bookingData['user_id'],
            'service_id'          => (string)$bookingData['service_id'],
            'service_ids'         => json_encode($bookingData['service_ids']),
            'service_provider_id' => (string)$bookingData['service_provider_id'],
            'car_model_id'        => (string)($bookingData['car_model_id'] ?? ''),
            'appointment_time'    => (string)$bookingData['appointment_time'],
            'duration_minutes'    => (string)$bookingData['duration_minutes'],
            'notes'               => (string)($bookingData['notes'] ?? ''),
            'final_price'         => (string)$bookingData['final_price'],
        ];

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'pkr',
                    'product_data' => [
                        'name'        => $service->name . $carLabel,
                        'description' => 'Provider: ' . $provider->business_name,
                    ],
                    'unit_amount'  => (int)($bookingData['final_price'] * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('payment.cancel'),
            'metadata'    => $metadata,
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

        if ($session->payment_status === 'paid') {
            $metadata = $session->metadata->toArray();

            // Create Booking in database now
            $booking = Booking::create([
                'user_id'             => (int)$metadata['user_id'],
                'service_id'          => (int)$metadata['service_id'],
                'service_ids'         => json_decode($metadata['service_ids'], true),
                'service_provider_id' => (int)$metadata['service_provider_id'],
                'car_model_id'        => $metadata['car_model_id'] ? (int)$metadata['car_model_id'] : null,
                'appointment_time'    => $metadata['appointment_time'],
                'duration_minutes'    => (int)$metadata['duration_minutes'],
                'notes'               => $metadata['notes'] ?: null,
                'final_price'         => (float)$metadata['final_price'],
                'status'              => 'confirmed',
                'payment_status'      => 'paid',
            ]);

            $intentId = is_object($session->payment_intent)
                ? $session->payment_intent->id
                : $session->payment_intent;

            // Store payment record
            Payment::create([
                'booking_id'            => $booking->id,
                'stripe_session_id'     => $session->id,
                'stripe_payment_intent' => $intentId,
                'amount'                => $booking->final_price,
                'currency'              => 'pkr',
                'status'                => 'paid',
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
     * Stripe cancel redirect — no booking was created.
     */
    public function cancel(Request $request)
    {
        return redirect()->route('welcome')
            ->with('error', 'Payment was cancelled. Your booking was not confirmed.');
    }

    // Old method compatibility
    public function checkout(Request $request) { return $this->checkoutBooking([]); }
    public function assignWorker(Booking $booking) {} // no-oprs
}
