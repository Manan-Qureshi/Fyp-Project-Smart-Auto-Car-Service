<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(route('google.callback'))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('google.callback'))
                ->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // User exists, login
                Auth::login($user);
                return redirect()->intended('dashboard');
            } else {
                // Check if email already exists
                $existingUser = User::where('email', $googleUser->email)->first();

                if ($existingUser) {
                    // Link Google account to existing user
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar
                    ]);
                    Auth::login($existingUser);
                } else {
                    // Create new user
                    // Note: Phone and Address are missing. We might need to prompt for them later.
                    // For now, we'll create the user and they can update profile.
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'password' => Hash::make(uniqid()), // Random password
                        'avatar' => $googleUser->avatar,
                        'role' => 'user' // Default to user
                    ]);

                    Auth::login($newUser);
                }

                return redirect()->intended('dashboard');
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google Login Failed');
        }
    }
}
