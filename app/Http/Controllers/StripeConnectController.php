<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;

class StripeConnectController extends Controller
{
    private function getProvider()
    {
        $user = Auth::user();
        if (!$user || !$user->isProvider()) {
            return null;
        }
        return $user->serviceProvider;
    }

    /**
     * Start Stripe Connect Express Onboarding.
     */
    public function connect()
    {
        $provider = $this->getProvider();
        if (!$provider) {
            return redirect()->route('provider.dashboard')->with('error', 'Provider profile not found.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Create Express Account if not already created
            if (empty($provider->stripe_account_id)) {
                $account = Account::create([
                    'type' => 'express',
                    'country' => 'US', // Standard country for Stripe Connect test simulation
                    'email' => Auth::user()->email,
                    'capabilities' => [
                        'transfers' => ['requested' => true],
                    ],
                    'business_profile' => [
                        'name' => $provider->business_name,
                        'product_description' => 'Automotive repair and car maintenance services',
                    ],
                ]);

                $provider->update([
                    'stripe_account_id' => $account->id,
                    'stripe_onboarding_completed' => false,
                ]);
            }

            // Create Onboarding Account Link
            $accountLink = AccountLink::create([
                'account' => $provider->stripe_account_id,
                'refresh_url' => route('provider.stripe.refresh'),
                'return_url' => route('provider.stripe.return'),
                'type' => 'account_onboarding',
            ]);

            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Unable to initiate Stripe Connect: ' . $e->getMessage());
        }
    }

    /**
     * Return URL after Stripe Onboarding.
     */
    public function return()
    {
        $provider = $this->getProvider();
        if (!$provider || empty($provider->stripe_account_id)) {
            return redirect()->route('provider.dashboard')->with('error', 'Invalid provider account.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $account = Account::retrieve($provider->stripe_account_id);

            // If onboarding details submitted or payouts enabled
            if ($account->details_submitted || $account->payouts_enabled) {
                $provider->update(['stripe_onboarding_completed' => true]);
                return redirect()->route('provider.dashboard')
                    ->with('success', 'Stripe Payout Account successfully connected! Auto-payouts are now enabled.');
            }

            return redirect()->route('provider.dashboard')
                ->with('info', 'Stripe onboarding was not completed. Please connect again to receive payouts.');
        } catch (\Exception $e) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Error verifying Stripe connection: ' . $e->getMessage());
        }
    }

    /**
     * Refresh URL if onboarding session expired.
     */
    public function refresh()
    {
        return $this->connect();
    }

    /**
     * Redirect Provider to their personal Stripe Express Dashboard.
     */
    public function dashboard()
    {
        $provider = $this->getProvider();
        if (!$provider || empty($provider->stripe_account_id)) {
            return redirect()->route('provider.dashboard')->with('error', 'Stripe account is not connected.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $loginLink = Account::createLoginLink($provider->stripe_account_id);
            return redirect($loginLink->url);
        } catch (\Exception $e) {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Unable to open Stripe Dashboard: ' . $e->getMessage());
        }
    }
}
