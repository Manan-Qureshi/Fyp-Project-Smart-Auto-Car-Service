<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\User;
use App\Models\Worker;
use App\Notifications\ServiceStatusUpdated;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Refund;

class ProcessNoShowBookings extends Command
{
    protected $signature   = 'bookings:process-no-shows';
    protected $description = 'Auto-cancel and refund no-show bookings 15 minutes after appointment end time.';

    public function handle(): void
    {
        $now = Carbon::now();

        $bookings = Booking::with(['payment', 'commission', 'serviceProvider', 'user'])
            ->whereIn('status', ['confirmed', 'assigned', 'accepted', 'pending'])
            ->get();

        foreach ($bookings as $booking) {
            $duration = $booking->duration_minutes ?? 60;
            $slotEnd = Carbon::parse($booking->appointment_time)->addMinutes($duration);
            $cutoffTime = $slotEnd->copy()->addMinutes(15);

            if ($now->greaterThanOrEqualTo($cutoffTime)) {
                $payment = $booking->payment;
                $refundProcessed = false;

                if ($payment && $payment->status === 'paid') {
                    if ($payment->stripe_payment_intent) {
                        try {
                            Stripe::setApiKey(env('STRIPE_SECRET'));
                            $pkrRefundAmount = round($booking->final_price * 0.90, 2);
                            $usdRefundCents = (int) round(($pkrRefundAmount / \App\Http\Controllers\PaymentController::PKR_TO_USD) * 100);

                            Refund::create([
                                'payment_intent' => $payment->stripe_payment_intent,
                                'amount'         => $usdRefundCents,
                            ]);
                            $payment->update(['status' => 'refunded']);
                            $refundProcessed = true;
                        } catch (\Exception $e) {
                            $payment->update(['status' => 'refunded']);
                            $refundProcessed = true;
                        }
                    } else {
                        $payment->update(['status' => 'refunded']);
                        $refundProcessed = true;
                    }

                    $adminCommission = round($booking->final_price * 0.02, 2);
                    $providerEarning = round($booking->final_price * 0.08, 2);

                    if ($booking->commission) {
                        $booking->commission->update([
                            'total_amount'      => $booking->final_price,
                            'commission_rate'   => 2.00,
                            'commission_amount' => $adminCommission,
                            'provider_earning'  => $providerEarning,
                        ]);
                    } else {
                        Commission::create([
                            'booking_id'          => $booking->id,
                            'service_provider_id' => $booking->service_provider_id,
                            'total_amount'        => $booking->final_price,
                            'commission_rate'     => 2.00,
                            'commission_amount'   => $adminCommission,
                            'provider_earning'    => $providerEarning,
                        ]);
                    }
                }

                $booking->update(['status' => 'cancelled']);

                if ($refundProcessed) {
                    $admins = User::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new ServiceStatusUpdated($booking, 'payment_refunded'));
                    }
                }

                if ($booking->serviceProvider && $booking->serviceProvider->user) {
                    $booking->serviceProvider->user->notify(new ServiceStatusUpdated($booking, 'cancelled_by_customer'));
                }

                if ($booking->provider_worker_id) {
                    $worker = Worker::find($booking->provider_worker_id);
                    if ($worker && $worker->user) {
                        $worker->user->notify(new ServiceStatusUpdated($booking, 'cancelled_by_customer'));
                    }
                }

                $this->info("Auto-cancelled no-show booking #{$booking->id}.");
            }
        }
    }
}
