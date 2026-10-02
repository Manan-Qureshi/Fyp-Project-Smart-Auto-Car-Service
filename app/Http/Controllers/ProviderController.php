<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use App\Models\Service;
use App\Models\CarModel;

class ProviderController extends Controller
{
    /**
     * Public provider profile page with services list.
     */
    public function show(ServiceProvider $provider)
    {
        $provider->load(['workers', 'ratings.customer']);
        $provider->avg_rating = round($provider->ratings()->avg('rating') ?? 0, 1);
        $provider->rating_count = $provider->ratings()->count();

        // Get services this provider offers (available only)
        $providerServices = $provider->providerServices()
            ->where('is_available', true)
            ->with('service.carType')
            ->get();

        // Group by service category
        $servicesByCategory = $providerServices->groupBy(function ($ps) {
            return $ps->service->category ?? 'General';
        });

        $selectedCar = session('selected_car_model');
        $carModel = $selectedCar ? CarModel::find($selectedCar['id']) : null;

        return view('providers.show', compact('provider', 'providerServices', 'servicesByCategory', 'selectedCar', 'carModel'));
    }

    /**
     * API: return providers sorted by distance.
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $providers = ServiceProvider::nearbyProviders(
            (float)$request->lat,
            (float)$request->lng
        );

        return response()->json($providers->map(function ($p) {
            return [
                'id'            => $p->id,
                'business_name' => $p->business_name,
                'address'       => $p->address,
                'distance_km'   => $p->distance ? round($p->distance, 1) : null,
                'logo'          => $p->logo ? (\Illuminate\Support\Str::startsWith($p->logo, ['http://', 'https://']) ? $p->logo : asset('storage/' . $p->logo)) : null,
                'avg_rating'    => round($p->ratings()->avg('rating') ?? 0, 1),
            ];
        }));
    }
}
