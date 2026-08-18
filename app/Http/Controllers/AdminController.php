<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Commission;
use App\Models\Booking;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()->isAdmin(), 403);
            return $next($request);
        });
    }

    // --- Provider Management ---

    public function providers()
    {
        $providers = ServiceProvider::with('owner')->withCount(['bookings', 'workers'])->latest()->paginate(15);
        return view('admin.providers.index', compact('providers'));
    }

    public function createProvider()
    {
        $users = User::whereIn('role', ['provider', 'user', 'customer'])
            ->whereDoesntHave('serviceProvider')
            ->get();
        return view('admin.providers.create', compact('users'));
    }

    public function storeProvider(Request $request)
    {
        $data = $request->validate([
            'business_name'     => 'required|string|max:255',
            'description'       => 'nullable|string',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'required|string',
            'latitude'          => 'required|numeric|between:-90,90',
            'longitude'         => 'required|numeric|between:-180,180',
            'service_radius_km' => 'nullable|numeric|min:1|max:500',

            'email'             => 'required|email|unique:users,email',
            'name'              => 'required|string',
            'password'          => 'required|string|min:8',
            'open_time'         => 'required',
            'close_time'        => 'required',
        ]);

        // Create the user account for provider
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => 'provider',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('providers', 'public');
        }

        ServiceProvider::create([
            'user_id'           => $user->id,
            'business_name'     => $data['business_name'],
            'description'       => $data['description'] ?? null,
            'phone'             => $data['phone'] ?? null,
            'address'           => $data['address'],
            'latitude'          => $data['latitude'],
            'longitude'         => $data['longitude'],
            'service_radius_km' => $data['service_radius_km'] ?? 20,
            'logo'              => $data['logo'] ?? null,
            'is_active'         => true,
            'open_time'         => $data['open_time'],
            'close_time'        => $data['close_time'],
        ]);

        return redirect()->route('admin.providers.index')->with('success', 'Service provider created successfully.');
    }

    public function editProvider(ServiceProvider $provider)
    {
        return view('admin.providers.edit', compact('provider'));
    }

    public function updateProvider(Request $request, ServiceProvider $provider)
    {
        $data = $request->validate([
            'business_name'     => 'required|string|max:255',
            'description'       => 'nullable|string',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'required|string',
            'latitude'          => 'required|numeric|between:-90,90',
            'longitude'         => 'required|numeric|between:-180,180',
            'service_radius_km' => 'nullable|numeric|min:1|max:500',
            'open_time'         => 'required',
            'close_time'        => 'required',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('providers', 'public');
        }

        $provider->update(array_merge($data, [

            'open_time'  => $data['open_time'],
            'close_time' => $data['close_time'],
        ]));

        return redirect()->route('admin.providers.index')->with('success', 'Provider updated.');
    }

    public function destroyProvider(ServiceProvider $provider)
    {
        $hasActiveBookings = \App\Models\Booking::where('service_provider_id', $provider->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'A service provider with active bookings cannot be deleted.');
        }

        $provider->delete();
        return redirect()->route('admin.providers.index')->with('success', 'Provider removed.');
    }

    // --- Financial Reports ---

    public function financial()
    {
        $commissions    = Commission::with(['booking.service', 'serviceProvider'])->latest()->paginate(20);
        $totalRevenue   = Commission::sum('commission_amount');
        $totalEarnings  = Commission::sum('provider_earning');
        $totalBookings  = Booking::where('status', 'completed')->count();

        return view('admin.financial', compact('commissions', 'totalRevenue', 'totalEarnings', 'totalBookings'));
    }
}
